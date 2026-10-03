<?php

namespace App\Console\Commands;

use App\Models\InstagramAccount;
use App\Services\Instagram\InstagramStoryImportService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class ImportInstagramStories extends Command
{
    protected $signature = 'instagram:import-stories
                            {account : ID, username ou business account id}
                            {path : Chemin vers le fichier CSV}';

    protected $description = 'Importe des statistiques de stories Instagram depuis un CSV (historique Metricool, etc.)';

    public function handle(InstagramStoryImportService $importService): int
    {
        $accountIdentifier = (string) $this->argument('account');
        $path = (string) $this->argument('path');

        $account = InstagramAccount::query()
            ->where('id', $accountIdentifier)
            ->orWhere('username', $accountIdentifier)
            ->orWhere('business_account_id', $accountIdentifier)
            ->first();

        if ($account === null) {
            $this->error('Compte Instagram introuvable.');

            return self::FAILURE;
        }

        try {
            $result = $importService->importFromPath($account, $path);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Import terminé pour @%s : %d créées, %d mises à jour, %d ignorées.',
            $account->username ?? $account->id,
            $result['imported'],
            $result['updated'],
            $result['skipped'],
        ));

        return self::SUCCESS;
    }
}
