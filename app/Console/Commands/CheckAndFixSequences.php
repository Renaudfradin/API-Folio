<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckAndFixSequences extends Command
{
    protected $signature = 'db:check-fix-sequences
                            {--force : Applique les corrections même en production}
                            {--dry-run : Affiche les corrections sans les appliquer}';

    protected $description = 'Vérifie et corrige définitivement les problèmes de séquences PostgreSQL';

    public function handle(): int
    {
        $this->info('Vérification complète des séquences PostgreSQL...');

        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        if ($isDryRun) {
            $this->warn('Mode dry-run : aucune modification ne sera effectuée.');
        }

        if (!$isForce) {
            $this->warn('Utilisez --force pour appliquer les corrections en production.');
            return self::SUCCESS;
        }

        try {
            // Obtenir toutes les tables avec leur MAX(id)
            $tables = DB::select("
                SELECT 
                    t.table_name,
                    COALESCE(CASE 
                        WHEN c.column_default LIKE 'nextval%' THEN 
                            REGEXP_REPLACE(c.column_default, 'nextval''([^'']+)''::regclass')::bigint + 1
                        ELSE 0 
                    END, 0) as current_seq_value,
                    COALESCE((SELECT MAX(id) FROM t.table_name WHERE id IS NOT NULL), 0) as max_id
                FROM information_schema.tables t
                JOIN information_schema.columns c ON c.table_name = t.table_name
                WHERE t.table_schema = 'public' 
                AND c.column_name = 'id'
                AND c.column_default LIKE 'nextval%'
                ORDER BY t.table_name
            ");

            if (empty($tables)) {
                $this->warn('Aucune table trouvée.');
                return self::SUCCESS;
            }

            $problems = [];
            $fixed = 0;

            foreach ($tables as $table) {
                $tableName = $table->table_name;
                $currentSeqValue = (int) $table->current_seq_value;
                $maxId = (int) $table->max_id;

                // Problème : séquence à 1 alors que MAX(id) > 1
                $isProblem = $currentSeqValue <= 1 && $maxId > 1;

                if ($isProblem) {
                    $problems[] = [
                        'table' => $tableName,
                        'current_seq' => $currentSeqValue,
                        'max_id' => $maxId,
                        'problem' => 'Sequence réinitialisée à 1',
                        'solution' => 'Réinitialiser avec MAX(id) + 1'
                    ];

                    try {
                        if (!$isDryRun) {
                            // Trouver le nom exact de la séquence
                            $sequenceName = $this->findSequenceName($tableName);

                            if ($sequenceName) {
                                // Solution permanente : réinitialiser avec MAX(id) + 1
                                $newValue = $maxId + 1;

                                // Forcer la réinitialisation complète
                                DB::statement("ALTER SEQUENCE \"{$sequenceName}\" RESTART WITH {$newValue}");

                                $this->info("  ✓ {$tableName} : séquence {$sequenceName} réinitialisée {$currentSeqValue} -> {$newValue}");
                                $fixed++;
                            } else {
                                $this->error("  ✗ {$tableName} : séquence non trouvée");
                            }
                        }
                    } catch (\Exception $e) {
                        $this->error("  ✗ Erreur correction {$tableName}: " . $e->getMessage());
                        Log::error("Sequence correction error", [
                            'table' => $tableName,
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    $this->info("  ✓ {$tableName} : OK (seq: {$currentSeqValue}, max: {$maxId})");
                }
            }

            // Afficher le résumé
            if (!empty($problems)) {
                $this->newLine();
                $this->error('Problèmes détectés :');
                $this->table(
                    ['Table', 'Séquence actuelle', 'MAX(id)', 'Problème', 'Solution'],
                    array_map(function ($p) {
                        return [
                            $p['table'],
                            $p['current_seq'],
                            $p['max_id'],
                            $p['problem'],
                            $p['solution']
                        ];
                    }, $problems)
                );
            }

            $this->newLine();
            $this->info("Vérification terminée : {$fixed} séquence(s) corrigée(s).");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erreur générale: ' . $e->getMessage());
            Log::error('CheckAndFixSequences fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return self::FAILURE;
        }
    }

    private function findSequenceName(string $tableName): ?string
    {
        // Essayer plusieurs formats de noms de séquences
        $possibleNames = [
            $tableName . '_id_seq',
            $tableName . '_id_seq1',
            strtolower($tableName) . '_id_seq',
            strtolower($tableName) . '_id_seq1'
        ];

        foreach ($possibleNames as $seqName) {
            $exists = DB::selectOne("SELECT 1 FROM information_schema.sequences WHERE sequence_name = '{$seqName}'");
            if ($exists) {
                return $seqName;
            }
        }

        return null;
    }
}
