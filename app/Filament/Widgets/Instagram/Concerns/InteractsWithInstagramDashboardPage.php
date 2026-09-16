<?php

namespace App\Filament\Widgets\Instagram\Concerns;

use App\Models\InstagramAccount;
use App\Services\Instagram\InstagramDashboardDateRange;
use Carbon\Carbon;
use Livewire\Attributes\On;

trait InteractsWithInstagramDashboardPage
{
    public ?int $instagramAccountId = null;

    public string $period = '30d';

    #[On('instagram-dashboard-filters-updated')]
    public function syncDashboardFilters(?int $accountId = null, string $period = '30d'): void
    {
        $this->instagramAccountId = $accountId;
        $this->period = $period;
    }

    protected function resolveDashboardAccount(): ?InstagramAccount
    {
        if ($this->instagramAccountId === null) {
            return null;
        }

        return InstagramAccount::query()->find($this->instagramAccountId);
    }

    /**
     * @return array{
     *     start: Carbon,
     *     end: Carbon,
     *     previousStart: Carbon,
     *     previousEnd: Carbon,
     *     label: string
     * }|null
     */
    protected function resolveDashboardDateRange(): ?array
    {
        return InstagramDashboardDateRange::forPeriod($this->period);
    }
}
