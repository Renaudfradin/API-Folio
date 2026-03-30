<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetPostgresSequences extends Command
{
    protected $signature = 'db:reset-sequences
                            {--dry-run : Affiche les séquences sans les modifier}
                            {--force : Force la resynchronisation de toutes les séquences}
                            {--production : Mode production (Railway) : détection via information_schema/pg_get_serial_sequence}
                            {--debug : Affiche les informations de débogage}';

    protected $description = 'Resynchronise toutes les séquences PostgreSQL avec le MAX(id) de chaque table';

    public function handle(): int
    {
        $this->info('Récupération des tables avec séquences...');

        $isDebug = $this->option('debug');
        $isProduction = (bool) $this->option('production');

        if ($isDebug) {
            $this->info('Mode debug activé - Affichage des informations de débogage');
            $this->info('Base de données: ' . DB::connection()->getDatabaseName());
            $this->info('Driver: ' . DB::connection()->getDriverName());
            $this->info('Mode production: ' . ($isProduction ? 'oui' : 'non'));
        }

        if ($isProduction) {
            // Mode compatible Railway/production : on liste les tables qui ont une colonne "id"
            // auto-incrémentée, puis on récupère la séquence associée via pg_get_serial_sequence.
            // Cela évite de dépendre de certains catalogues pg_* parfois restreints selon les rôles.
            $tables = DB::select("
                SELECT
                    c.table_name,
                    pg_get_serial_sequence('public.' || quote_ident(c.table_name), 'id') AS sequence_name
                FROM information_schema.columns c
                WHERE c.table_schema = 'public'
                  AND c.column_name = 'id'
                  AND (
                        c.column_default LIKE 'nextval%'
                     OR c.is_identity = 'YES'
                  )
                ORDER BY c.table_name
            ");
        } else {
            // Comportement local (Sail/Docker) : approche directe via catalogues pg_*.
            $tables = DB::select("
                SELECT
                    seq.sequencename AS sequence_name,
                    tab.relname      AS table_name
                FROM pg_sequences seq
                JOIN pg_class seq_class
                    ON seq_class.relname = seq.sequencename
                JOIN pg_depend dep
                    ON dep.objid = seq_class.oid
                   AND dep.deptype = 'a'
                JOIN pg_class tab
                    ON tab.oid = dep.refobjid
                JOIN pg_attribute attr
                    ON attr.attrelid = tab.oid
                   AND attr.attnum = dep.refobjsubid
                   AND attr.attname = 'id'
                JOIN pg_namespace ns
                    ON ns.oid = tab.relnamespace
                   AND ns.nspname = 'public'
                ORDER BY tab.relname
            ");
        }

        if (empty($tables)) {
            $this->warn('Aucune table avec séquence trouvée.');

            return self::SUCCESS;
        }

        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        if ($isDryRun) {
            $this->warn('Mode dry-run : aucune modification ne sera effectuée.');
        }

        if ($isForce) {
            $this->warn('Mode force : toutes les séquences seront resynchronisées.');
        }

        $rows = [];
        $fixed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($tables as $table) {
            $tableName = $table->table_name;
            $sequenceName = $table->sequence_name;

            try {
                if ($isProduction) {
                    if (empty($sequenceName)) {
                        if ($isDebug) {
                            $this->warn("Séquence introuvable (pg_get_serial_sequence=NULL) pour la table {$tableName}");
                        }
                        $skipped++;
                        continue;
                    }

                    $quotedTable = $this->quoteQualifiedIdentifier('public', $tableName);
                    $maxResult = DB::selectOne("SELECT COALESCE(MAX(id), 0) AS max_id FROM {$quotedTable}");
                } else {
                    $maxResult = DB::selectOne("SELECT COALESCE(MAX(id), 0) AS max_id FROM \"{$tableName}\"");
                }
                $maxId = (int) $maxResult->max_id;

                if ($isProduction) {
                    $quotedSequence = $this->quoteQualifiedIdentifierFromRegclass($sequenceName);
                    $seqResult = DB::selectOne("SELECT last_value, is_called FROM {$quotedSequence}");
                    $currentValue = (int) $seqResult->last_value;
                } else {
                    $seqResult = DB::selectOne("SELECT last_value FROM \"{$sequenceName}\"");
                    $currentValue = (int) $seqResult->last_value;
                }

                // Si la table est vide, on remet la séquence à 1
                $targetValue = $maxId > 0 ? $maxId : 1;
                $isCalled = $maxId > 0;

                if ($isDebug) {
                    if ($isProduction) {
                        $calledInfo = $maxId > 0 ? 'true' : 'false';
                        $this->line("Table: {$tableName}, Séquence: {$sequenceName}, Actuel: {$currentValue}, Max: {$maxId}, Cible: {$targetValue}, is_called: {$calledInfo}");
                    } else {
                        $this->line("Table: {$tableName}, Séquence: {$sequenceName}, Actuel: {$currentValue}, Max: {$maxId}, Cible: {$targetValue}");
                    }
                }

                if ($isForce || $currentValue < $maxId) {
                    $status = $isForce ? 'Forcee' : 'Desynchronisee';
                    if (! $isDryRun) {
                        if ($isProduction) {
                            DB::statement('SELECT setval(?, ?, ?)', [$sequenceName, $targetValue, $isCalled]);
                        } else {
                            DB::statement("SELECT setval('\"{$sequenceName}\"', {$targetValue})");
                        }
                        $status = 'Corrigee';
                    }
                    $fixed++;
                } else {
                    $status = 'OK';
                    $skipped++;
                }

                $rows[] = [$tableName, $sequenceName, $currentValue, $maxId, $status];
            } catch (\Exception $e) {
                $this->error("Erreur sur la table {$tableName}: " . $e->getMessage());
                if ($isDebug) {
                    $this->error("Stack trace: " . $e->getTraceAsString());
                }
                $errors++;
            }
        }

        $this->table(
            ['Table', 'Sequence', 'Valeur actuelle', 'MAX(id)', 'Statut'],
            $rows
        );

        $this->newLine();
        $this->info("{$fixed} sequence(s) corrigee(s), {$skipped} deja synchronisee(s), {$errors} erreur(s).");

        if ($errors > 0) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function quoteQualifiedIdentifier(string $schema, string $name): string
    {
        return '"' . str_replace('"', '""', $schema) . '"."' . str_replace('"', '""', $name) . '"';
    }

    private function quoteQualifiedIdentifierFromRegclass(string $regclass): string
    {
        $parts = explode('.', $regclass, 2);
        if (count($parts) === 2) {
            return $this->quoteQualifiedIdentifier($parts[0], $parts[1]);
        }

        // Fallback (sans schéma) : on quote uniquement le nom.
        return '"' . str_replace('"', '""', $regclass) . '"';
    }
}
