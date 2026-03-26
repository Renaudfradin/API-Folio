<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForceResetSequences extends Command
{
    protected $signature = 'db:force-reset-sequences
                            {--dry-run : Affiche les modifications sans les appliquer}
                            {--table= : Table spécifique à traiter (optionnel)}';

    protected $description = 'Force la resynchronisation de TOUTES les séquences PostgreSQL (version agressive pour Railway)';

    public function handle(): int
    {
        $this->info('Force reset de toutes les séquences PostgreSQL...');
        
        $isDryRun = $this->option('dry-run');
        $specificTable = $this->option('table');

        if ($isDryRun) {
            $this->warn('Mode dry-run : aucune modification ne sera effectuée.');
        }

        try {
            $tables = [];
            
            if ($specificTable) {
                // Traiter une table spécifique
                $tables[] = (object) ['table_name' => $specificTable];
            } else {
                // Traiter toutes les tables
                $tables = DB::select("
                    SELECT table_name 
                    FROM information_schema.columns 
                    WHERE table_schema = 'public' 
                    AND column_default LIKE 'nextval%'
                    AND column_name = 'id'
                    ORDER BY table_name
                ");
            }

            if (empty($tables)) {
                $this->warn('Aucune table trouvée.');
                return self::SUCCESS;
            }

            $rows = [];
            $fixed = 0;
            $errors = 0;

            foreach ($tables as $table) {
                $tableName = $table->table_name;
                
                try {
                    $this->line("Traitement forcé de : {$tableName}");

                    // Obtenir le MAX(id) réel
                    $maxResult = DB::selectOne("SELECT COALESCE(MAX(id), 0) AS max_id FROM \"{$tableName}\"");
                    $maxId = (int) $maxResult->max_id;

                    // Nom de la séquence (essayer plusieurs formats)
                    $possibleSequences = [
                        $tableName . '_id_seq',
                        $tableName . '_id_seq1',
                        strtolower($tableName) . '_id_seq',
                        strtolower($tableName) . '_id_seq1'
                    ];

                    $sequenceName = null;
                    foreach ($possibleSequences as $seq) {
                        $seqExists = DB::selectOne("SELECT 1 FROM information_schema.sequences WHERE sequence_name = '{$seq}'");
                        if ($seqExists) {
                            $sequenceName = $seq;
                            break;
                        }
                    }

                    if (!$sequenceName) {
                        $this->warn("  Aucune séquence trouvée pour {$tableName}");
                        continue;
                    }

                    // Obtenir la valeur actuelle
                    $currentValue = 0;
                    try {
                        $seqResult = DB::selectOne("SELECT last_value FROM \"{$sequenceName}\"");
                        $currentValue = (int) $seqResult->last_value;
                    } catch (\Exception $e) {
                        $this->warn("  Impossible de lire la séquence {$sequenceName}: " . $e->getMessage());
                    }

                    // Forcer la resynchronisation : MAX(id) + 1
                    $newValue = $maxId + 1;
                    
                    if (!$isDryRun) {
                        // Réinitialiser complètement la séquence
                        DB::statement("ALTER SEQUENCE \"{$sequenceName}\" RESTART WITH {$newValue}");
                        DB::statement("SELECT setval('\"{$sequenceName}\"', {$newValue}, true)");
                        $status = 'Forcee';
                    } else {
                        $status = 'Sera forcee';
                    }

                    $fixed++;
                    $rows[] = [$tableName, $sequenceName, $currentValue, $maxId, $newValue, $status];
                    
                    $this->info("  ✓ Séquence {$sequenceName} : {$currentValue} -> {$newValue}");
                    
                } catch (\Exception $e) {
                    $this->error("  ✗ Erreur sur {$tableName}: " . $e->getMessage());
                    Log::error("ForceResetSequences error", [
                        'table' => $tableName,
                        'error' => $e->getMessage()
                    ]);
                    $errors++;
                }
            }

            $this->newLine();
            $this->table(
                ['Table', 'Sequence', 'Actuelle', 'MAX(id)', 'Nouvelle', 'Statut'],
                $rows
            );

            $this->newLine();
            $this->info("Traitement terminé : {$fixed} séquence(s) forcée(s), {$errors} erreur(s).");

            return $errors === 0 ? self::SUCCESS : self::FAILURE;
            
        } catch (\Exception $e) {
            $this->error('Erreur générale: ' . $e->getMessage());
            Log::error('ForceResetSequences fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return self::FAILURE;
        }
    }
}
