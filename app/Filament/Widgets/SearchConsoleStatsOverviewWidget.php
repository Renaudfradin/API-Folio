<?php

namespace App\Filament\Widgets;

use App\Services\Google\GoogleSearchConsoleService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use RuntimeException;
use Throwable;

abstract class SearchConsoleStatsOverviewWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = null;

    abstract protected function getSiteKey(): string;

    protected function getStats(): array
    {
        try {
            $stats = app(GoogleSearchConsoleService::class)->getSiteStats($this->getSiteKey());

            return [
                Stat::make('Clics SEO', number_format($stats['clicks']))
                    ->description('28 derniers jours'),
                Stat::make('Impressions SEO', number_format($stats['impressions']))
                    ->description($stats['property']),
                Stat::make('CTR moyen', number_format($stats['ctr'] * 100, 2).' %'),
                Stat::make('Position moyenne', number_format($stats['position'], 2)),
            ];
        } catch (RuntimeException|Throwable $exception) {
            return [
                Stat::make('Clics SEO', '—')
                    ->description($this->fallbackDescription($exception)),
                Stat::make('Impressions SEO', '—'),
                Stat::make('CTR moyen', '—'),
                Stat::make('Position moyenne', '—'),
            ];
        }
    }

    protected function fallbackDescription(Throwable $exception): string
    {
        return str($exception->getMessage())
            ->limit(80)
            ->toString();
    }
}
