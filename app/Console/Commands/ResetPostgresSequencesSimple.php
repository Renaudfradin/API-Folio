<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetPostgresSequencesSimple extends Command
{
    protected $signature = 'db:reset-sequences-simple
                            {--dry-run : Affiche les séquences sans les modifier}
                            {--force : Force la resynchronisation de toutes les séquences}';

    protected $description = 'Version simplifiée pour Railway - Resynchronise les séquences PostgreSQL';

    public function handle(): int
    {
        $this->info('Récupération des tables avec séquences (version simplifiée)...');
        
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        if ($isDryRun) {
            $this->warn('Mode dry-run : aucune modification ne sera effectuée.');
        }

        if ($isForce) {
            $this->warn('Mode force : toutes les séquences seront resynchronisées.');
        }

        try {
            // Version plus simple et compatible
            $tables = DB::select("
                SELECT table_name, column_name 
                FROM information_schema.columns 
                WHERE table_schema = 'public' 
                AND column_default LIKE 'nextval%'
                AND column_name = 'id'
                ORDER BY table_name
            ");

            if (empty($tables)) {
                $this->warn('Aucune table avec séquence trouvée.');
                return self::SUCCESS;
            }

            $rows = [];
            $fixed = 0;
            $skipped = 0;

            foreach ($tables as $table) {
                $tableName = $table->table_name;
                
                try {
                    // Récupérer le nom de la séquence
                    $sequenceName = $tableName . '_id_seq';
                    
                    $maxResult = DB::selectOne("SELECT COALESCE(MAX(id), 0) AS max_id FROM \"{$tableName}\"");
                    $maxId = (int) $maxResult->max_id;

                    // Vérifier si la séquence existe
                    $seqExists = DB::selectOne("SELECT 1 FROM information_schema.sequences WHERE sequence_name = '{$sequenceName}'");
                    
                    if (!$seqExists) {
                        $this->warn("Séquence {$sequenceName} non trouvée pour la table {$tableName}");
                        continue;
                    }

                    $currentValue = 0;
                    try {
                        $seqResult = DB::selectOne("SELECT last_value FROM \"{$sequenceName}\"");
                        $currentValue = (int) $seqResult->last_value;
                    } catch (\Exception $e) {
                        $this->warn("Impossible de lire la séquence {$sequenceName}: " . $e->getMessage());
                    }

                    $targetValue = $maxId > 0 ? $maxId : 1;

                    if ($isForce || $currentValue < $maxId) {
                        $status = $isForce ? 'Forcee' : 'Desynchronisee';
                        if (! $isDryRun) {
                            DB::statement("SELECT setval('\"{$sequenceName}\"', {$targetValue}, true)");
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
                    Log::error("ResetPostgresSequencesSimple error", [
                        'table' => $tableName,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->table(
                ['Table', 'Sequence', 'Valeur actuelle', 'MAX(id)', 'Statut'],
                $rows
            );

            $this->newLine();
            $this->info("{$fixed} sequence(s) corrigee(s), {$skipped} deja synchronisee(s).");

            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Erreur générale: ' . $e->getMessage());
            Log::error('ResetPostgresSequencesSimple fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return self::FAILURE;
        }
    }
}
