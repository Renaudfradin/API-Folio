<?php

namespace App\Filament\Widgets;

use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InstagramStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $account = InstagramAccount::query()
            ->where('is_active', true)
            ->latest('last_synced_at')
            ->first();

        return [
            Stat::make('Instagram comptes', InstagramAccount::query()->count()),
            Stat::make('Instagram followers', InstagramAccount::query()->sum('followers_count')),
            Stat::make('Instagram following', InstagramAccount::query()->sum('follows_count')),
            Stat::make('Instagram posts', InstagramMedia::query()->count()),
            Stat::make('Dernière synchro', $account?->last_synced_at?->diffForHumans() ?? 'Jamais'),
        ];
    }
}
