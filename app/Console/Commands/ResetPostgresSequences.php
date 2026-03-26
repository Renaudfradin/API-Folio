<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetPostgresSequences extends Command
{
    protected $signature = 'db:reset-sequences
                            {--dry-run : Affiche les séquences sans les modifier}
                            {--force : Force la resynchronisation de toutes les séquences}
                            {--debug : Affiche les informations de débogage}';

    protected $description = 'Resynchronise toutes les séquences PostgreSQL avec le MAX(id) de chaque table';

    public function handle(): int
    {
        $this->info('Récupération des tables avec séquences...');

        $isDebug = $this->option('debug');

        if ($isDebug) {
            $this->info('Mode debug activé - Affichage des informations de débogage');
            $this->info('Base de données: ' . DB::connection()->getDatabaseName());
            $this->info('Driver: ' . DB::connection()->getDriverName());
        }

        // Approche directe : récupère les séquences via pg_sequences + pg_depend
        // pour trouver uniquement les séquences liées à une colonne 'id'
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
                $maxResult = DB::selectOne("SELECT COALESCE(MAX(id), 0) AS max_id FROM \"{$tableName}\"");
                $maxId = (int) $maxResult->max_id;

                $seqResult = DB::selectOne("SELECT last_value FROM \"{$sequenceName}\"");
                $currentValue = (int) $seqResult->last_value;

                // Si la table est vide, on remet la séquence à 1
                $targetValue = $maxId > 0 ? $maxId : 1;

                if ($isDebug) {
                    $this->line("Table: {$tableName}, Séquence: {$sequenceName}, Actuel: {$currentValue}, Max: {$maxId}, Cible: {$targetValue}");
                }

                if ($isForce || $currentValue < $maxId) {
                    $status = $isForce ? 'Forcee' : 'Desynchronisee';
                    if (! $isDryRun) {
                        DB::statement("SELECT setval('\"{$sequenceName}\"', {$targetValue})");
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
}
