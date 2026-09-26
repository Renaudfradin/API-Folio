<?php

namespace App\Console\Commands;

use App\Models\LinkedinConnection;
use App\Services\LinkedIn\LinkedInSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncLinkedIn extends Command
{
    protected $signature = 'linkedin:sync
                            {user? : ID ou e-mail de l’utilisateur}
                            {--all : Synchroniser toutes les connexions actives}';

    protected $description = 'Synchronise les connexions LinkedIn';

    public function handle(LinkedInSyncService $syncService): int
    {
        $userIdentifier = $this->argument('user');

        $connections = match (true) {
            (bool) $this->option('all') => LinkedinConnection::query()->whereNotNull('access_token')->get(),
            filled($userIdentifier) => LinkedinConnection::query()
                ->whereHas('user', function ($query) use ($userIdentifier): void {
                    $query->where('id', $userIdentifier)->orWhere('email', $userIdentifier);
                })
                ->get(),
            default => LinkedinConnection::query()->whereNotNull('access_token')->get(),
        };

        if ($connections->isEmpty()) {
            $this->warn('Aucune connexion LinkedIn à synchroniser.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($connections as $connection) {
            try {
                $connection = $syncService->syncConnection($connection);

                $rows[] = [
                    $connection->id,
                    $connection->profile_name ?? '-',
                    $connection->last_synced_status ?? 'OK',
                    (string) $connection->followers_count,
                    (string) $connection->posts()->count(),
                ];
            } catch (Throwable $throwable) {
                $rows[] = [
                    $connection->id,
                    $connection->profile_name ?? '-',
                    'ERREUR',
                    $throwable->getMessage(),
                    '-',
                ];
            }
        }

        $this->table(
            ['ID', 'Profil', 'Statut', 'Followers / Erreur', 'Posts'],
            $rows
        );

        return self::SUCCESS;
    }
}
