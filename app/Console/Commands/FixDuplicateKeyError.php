<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixDuplicateKeyError extends Command
{
    protected $signature = 'db:fix-duplicate-key
                            {table? : Nom de la table à corriger (optionnel)}
                            {--dry-run : Affiche les corrections sans les appliquer}
                            {--force : Force la correction même en production}';

    protected $description = 'Corrige les erreurs de clé dupliquée en resynchronisant les séquences PostgreSQL';

    public function handle(): int
    {
        $tableName = $this->argument('table');
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        $this->info('Correction des erreurs de clé dupliquée...');
        
        if ($isDryRun) {
            $this->warn('Mode dry-run : aucune modification ne sera effectuée.');
        }

        if (!$isForce) {
            $this->warn('Utilisez --force pour appliquer les corrections en production.');
        }

        try {
            // Si une table spécifique est demandée
            if ($tableName) {
                return $this->fixTableSequence($tableName, $isDryRun, $isForce);
            }

            // Sinon, vérifier toutes les tables
            $tables = DB::select("
                SELECT table_name 
                FROM information_schema.columns 
                WHERE table_schema = 'public' 
                AND column_default LIKE 'nextval%'
                AND column_name = 'id'
                ORDER BY table_name
            ");

            $totalFixed = 0;
            $totalErrors = 0;

            foreach ($tables as $table) {
                $result = $this->fixTableSequence($table->table_name, $isDryRun, $isForce);
                if ($result === self::SUCCESS) {
                    $totalFixed++;
                } else {
                    $totalErrors++;
                }
            }

            $this->newLine();
            $this->info("Traitement terminé : {$totalFixed} table(s) corrigée(s), {$totalErrors} erreur(s).");

            return $totalErrors === 0 ? self::SUCCESS : self::FAILURE;

        } catch (\Exception $e) {
            $this->error('Erreur générale: ' . $e->getMessage());
            Log::error('FixDuplicateKeyError fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return self::FAILURE;
        }
    }

    private function fixTableSequence(string $tableName, bool $isDryRun, bool $isForce): int
    {
        try {
            $this->line("Traitement de la table : {$tableName}");

            // Vérifier si la table existe
            $tableExists = DB::selectOne("SELECT 1 FROM information_schema.tables WHERE table_name = '{$tableName}' AND table_schema = 'public'");
            if (!$tableExists) {
                $this->error("Table {$tableName} non trouvée.");
                return self::FAILURE;
            }

            // Récupérer le MAX(id) de la table
            $maxResult = DB::selectOne("SELECT COALESCE(MAX(id), 0) AS max_id FROM \"{$tableName}\"");
            $maxId = (int) $maxResult->max_id;

            // Nom de la séquence (format standard)
            $sequenceName = $tableName . '_id_seq';

            // Vérifier si la séquence existe
            $seqExists = DB::selectOne("SELECT 1 FROM information_schema.sequences WHERE sequence_name = '{$sequenceName}'");
            if (!$seqExists) {
                $this->warn("Séquence {$sequenceName} non trouvée pour la table {$tableName}");
                return self::SUCCESS; // Pas une erreur, juste pas de séquence
            }

            // Récupérer la valeur actuelle de la séquence
            try {
                $seqResult = DB::selectOne("SELECT last_value FROM \"{$sequenceName}\"");
                $currentValue = (int) $seqResult->last_value;
            } catch (\Exception $e) {
                $this->warn("Impossible de lire la séquence {$sequenceName}: " . $e->getMessage());
                $currentValue = 0;
            }

            // Vérifier s'il y a un problème
            $needsFix = $currentValue <= $maxId;

            if ($needsFix) {
                $this->warn("  Séquence désynchronisée : actuelle={$currentValue}, max_id={$maxId}");
                
                if (!$isDryRun && $isForce) {
                    // Corriger la séquence
                    $newValue = $maxId + 1;
                    DB::statement("SELECT setval('\"{$sequenceName}\"', {$newValue}, true)");
                    $this->info("  ✓ Séquence corrigée : {$sequenceName} -> {$newValue}");
                } else {
                    $this->info("  ~ Séquence serait corrigée à : " . ($maxId + 1));
                }
            } else {
                $this->info("  ✓ Séquence OK : actuelle={$currentValue}, max_id={$maxId}");
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Erreur sur la table {$tableName}: " . $e->getMessage());
            Log::error("FixDuplicateKeyError table error", [
                'table' => $tableName,
                'error' => $e->getMessage()
            ]);
            return self::FAILURE;
        }
    }
}
