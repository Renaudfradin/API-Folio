<?php

namespace App\Filament\Widgets\Instagram;

use App\Filament\Widgets\Instagram\Concerns\InteractsWithInstagramDashboardPage;
use App\Services\Instagram\InstagramAccountDashboardMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InstagramAccountSnapshotOverview extends StatsOverviewWidget
{
    use InteractsWithInstagramDashboardPage;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $account = $this->resolveDashboardAccount();

        if ($account === null) {
            return [];
        }

        $snapshot = app(InstagramAccountDashboardMetrics::class)->accountSnapshot($account);
        $syncLabel = $account->last_synced_at?->diffForHumans() ?? 'jamais';

        return [
            Stat::make('Followers', number_format($snapshot['followers_count']))
                ->description('Profil synchronisé'),
            Stat::make('Abonnements', number_format($snapshot['follows_count']))
                ->description('Profil synchronisé'),
            Stat::make('Posts (profil)', number_format($snapshot['media_count']))
                ->description('Compteur Instagram'),
            Stat::make('Impressions', $this->formatInsight($snapshot['impressions']))
                ->description('Insights compte · '.$syncLabel),
            Stat::make('Portée compte', $this->formatInsight($snapshot['reach']))
                ->description('Dernière synchro'),
            Stat::make('Vues profil', $this->formatInsight($snapshot['profile_views']))
                ->description('Dernière synchro'),
            Stat::make('Clics site', $this->formatInsight($snapshot['website_clicks']))
                ->description('Dernière synchro'),
            Stat::make('Comptes engagés', $this->formatInsight($snapshot['accounts_engaged']))
                ->description('Dernière synchro'),
        ];
    }

    protected function formatInsight(?int $value): string
    {
        return $value === null ? '-' : number_format($value);
    }
}
