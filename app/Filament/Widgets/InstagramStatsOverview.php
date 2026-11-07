<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\InstagramDashboard;
use App\Models\InstagramAccount;
use App\Models\InstagramMedia;
use Filament\Widgets\Widget;

class InstagramStatsOverview extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.widgets.instagram-account-card';

    protected int|string|array $columnSpan = 1;

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $account = InstagramAccount::query()
            ->where('is_active', true)
            ->latest('last_synced_at')
            ->first();

        if (! $account instanceof InstagramAccount) {
            return [
                'account' => null,
                'url' => null,
                'metrics' => [],
                'syncLabel' => null,
                'syncIsStale' => false,
            ];
        }

        return [
            'account' => $account,
            'url' => InstagramDashboard::canAccess() ? InstagramDashboard::getUrl() : null,
            'metrics' => [
                ['label' => 'Abonnés', 'value' => number_format((int) $account->followers_count)],
                ['label' => 'Abonnements', 'value' => number_format((int) $account->follows_count)],
                ['label' => 'Publications', 'value' => number_format(InstagramMedia::query()->where('instagram_account_id', $account->id)->count())],
            ],
            'syncLabel' => $account->last_synced_at === null
                ? 'Jamais synchronisé'
                : 'Synchro '.$account->last_synced_at->diffForHumans(),
            'syncIsStale' => $account->last_synced_at === null || $account->last_synced_at->lt(now()->subDays(14)),
        ];
    }
}
