<?php

namespace App\Filament\Widgets\LinkedIn;

use App\Filament\Widgets\LinkedIn\Concerns\InteractsWithLinkedInDashboardPage;
use App\Services\LinkedIn\LinkedInConnectionDashboardMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LinkedInConnectionSnapshotOverview extends StatsOverviewWidget
{
    use InteractsWithLinkedInDashboardPage;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $connection = $this->resolveDashboardConnection();

        if ($connection === null) {
            return [];
        }

        $snapshot = app(LinkedInConnectionDashboardMetrics::class)->accountSnapshot($connection);
        $syncLabel = $connection->last_synced_at?->diffForHumans() ?? 'jamais';

        return [
            Stat::make('Total followers', $this->formatCount($snapshot['followers_count']))
                ->description('Profil synchronisé'),
            Stat::make('Posts', $this->formatCount($snapshot['posts_count']))
                ->description('Posts importés'),
            Stat::make('Réactions', $this->formatCount($snapshot['reactions']))
                ->description('Sur les posts sync · '.$syncLabel),
            Stat::make('Commentaires', $this->formatCount($snapshot['comments']))
                ->description('Sur les posts sync'),
            Stat::make('Taux d\'eng.', $this->formatPercent($snapshot['engagement_rate']))
                ->description('Calculé sur les impressions'),
            Stat::make('Impressions', $this->formatCount($snapshot['impressions']))
                ->description('Posts synchronisés'),
            Stat::make('Partages', $this->formatCount($snapshot['shares']))
                ->description('Posts synchronisés'),
            Stat::make('Portée', $this->formatCount($snapshot['reach']))
                ->description('Posts synchronisés'),
            Stat::make('Vues vidéo', $this->formatCount($snapshot['video_views']))
                ->description('Posts synchronisés'),
        ];
    }

    protected function formatCount(int $value): string
    {
        return number_format($value);
    }

    protected function formatPercent(?float $value): string
    {
        return $value === null ? '-' : number_format($value, 1).' %';
    }
}
