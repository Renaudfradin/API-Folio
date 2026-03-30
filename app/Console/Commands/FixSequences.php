<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPostgresSequences extends Command
{
    protected $signature = 'db:fix-sequences {--dry-run : Affiche les corrections sans les appliquer}';

    protected $description = 'Corrige les séquences PostgreSQL désynchronisées sur toutes les tables';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 Mode dry-run activé — aucune modification ne sera appliquée.');
        }

        // Récupère toutes les séquences liées à une colonne "id" dans le schéma public
        $sequences = DB::select("
            SELECT
                t.relname  AS table_name,
                a.attname  AS column_name,
                s.relname  AS sequence_name
            FROM pg_class s
            JOIN pg_depend d  ON d.objid = s.oid
            JOIN pg_class t   ON d.refobjid = t.oid
            JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = d.refobjsubid
            WHERE s.relkind = 'S'
              AND t.relnamespace = (SELECT oid FROM pg_namespace WHERE nspname = 'public')
            ORDER BY t.relname
        ");

        if (empty($sequences)) {
            $this->info('Aucune séquence trouvée.');
            return self::SUCCESS;
        }

        $headers = ['Table', 'Colonne', 'Séquence', 'Max ID', 'Valeur actuelle', 'Statut'];
        $rows    = [];
        $fixed   = 0;
        $ok      = 0;

        foreach ($sequences as $seq) {
            $table    = $seq->table_name;
            $column   = $seq->column_name;
            $sequence = $seq->sequence_name;

            try {
                $maxId       = DB::table($table)->max($column) ?? 0;
                $currentVal  = DB::selectOne("SELECT last_value FROM \"{$sequence}\"")->last_value;

                if ($currentVal < $maxId) {
                    $status = $dryRun ? '⚠️  À corriger' : '✅ Corrigé';

                    if (!$dryRun) {
                        DB::statement("SELECT setval('\"{$sequence}\"', {$maxId})");
                        $fixed++;
                    }
                } else {
                    $status = '👍 OK';
                    $ok++;
                }

                $rows[] = [$table, $column, $sequence, $maxId, $currentVal, $status];
            } catch (\Throwable $e) {
                $rows[] = [$table, $column, $sequence, '?', '?', '❌ Erreur : ' . $e->getMessage()];
            }
        }

        $this->table($headers, $rows);

        $this->newLine();

        if ($dryRun) {
            $desync = count(array_filter($rows, fn($r) => str_contains($r[5], 'corriger')));
            $this->warn("🔍 {$desync} séquence(s) désynchronisée(s) détectée(s). Relancez sans --dry-run pour corriger.");
        } else {
            $this->info("✅ {$fixed} séquence(s) corrigée(s), {$ok} déjà synchronisée(s).");
        }

        return self::SUCCESS;
    }
}
