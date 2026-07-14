<?php

namespace App\Console\Commands;

use App\Models\InstagramAccount;
use App\Services\Instagram\InstagramSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncInstagram extends Command
{
    protected $signature = 'instagram:sync
                            {account? : ID, username ou business account id du compte Instagram}
                            {--all : Synchroniser tous les comptes actifs}';

    protected $description = 'Synchronise les comptes Instagram professionnels connectés';

    public function handle(InstagramSyncService $syncService): int
    {
        $accountIdentifier = $this->argument('account');

        $accounts = match (true) {
            (bool) $this->option('all') => InstagramAccount::query()->where('is_active', true)->get(),
            filled($accountIdentifier) => InstagramAccount::query()
                ->where('id', $accountIdentifier)
                ->orWhere('username', $accountIdentifier)
                ->orWhere('business_account_id', $accountIdentifier)
                ->get(),
            default => InstagramAccount::query()->where('is_active', true)->get(),
        };

        if ($accounts->isEmpty()) {
            $this->warn('Aucun compte Instagram à synchroniser.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($accounts as $account) {
            try {
                $syncService->syncAccount($account);

                $rows[] = [
                    $account->id,
                    $account->username ?? '-',
                    'OK',
                    (string) $account->followers_count,
                    (string) $account->media_count,
                ];
            } catch (Throwable $throwable) {
                $rows[] = [
                    $account->id,
                    $account->username ?? '-',
                    'ERREUR',
                    $throwable->getMessage(),
                    '-',
                ];
            }
        }

        $this->table(
            ['ID', 'Username', 'Statut', 'Followers / Erreur', 'Posts'],
            $rows
        );

        return self::SUCCESS;
    }
}
