<?php

namespace App\Filament\Widgets;

use App\Services\GitHub\GitHubService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use RuntimeException;
use Throwable;

class GitHubStatsOverview extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        try {
            $stats = app(GitHubService::class)->getAccountStats();
        } catch (RuntimeException|Throwable) {
            return [
                Stat::make('Repos publics', '—')
                    ->description('Configurer GITHUB_TOKEN'),
                Stat::make('Stars totales', '—'),
                Stat::make('Followers', '—'),
                Stat::make('Following', '—'),
            ];
        }

        return [
            Stat::make('Repos publics', number_format($stats['public_repos']))
                ->description($stats['login'])
                ->url($stats['html_url'] ?: null)
                ->openUrlInNewTab(),
            Stat::make('Stars totales', number_format($stats['total_stars'])),
            Stat::make('Followers', number_format($stats['followers'])),
            Stat::make('Following', number_format($stats['following'])),
        ];
    }
}
