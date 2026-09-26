<?php

namespace App\Filament\Widgets\LinkedIn\Concerns;

use App\Models\LinkedinConnection;
use App\Services\LinkedIn\LinkedInDashboardDateRange;
use Carbon\Carbon;
use Livewire\Attributes\On;

trait InteractsWithLinkedInDashboardPage
{
    public ?int $linkedinConnectionId = null;

    public string $period = '30d';

    #[On('linkedin-dashboard-filters-updated')]
    public function syncDashboardFilters(?int $connectionId = null, string $period = '30d'): void
    {
        $this->linkedinConnectionId = $connectionId;
        $this->period = $period;
    }

    protected function resolveDashboardConnection(): ?LinkedinConnection
    {
        if ($this->linkedinConnectionId === null) {
            return null;
        }

        return LinkedinConnection::query()->find($this->linkedinConnectionId);
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
        return LinkedInDashboardDateRange::forPeriod($this->period);
    }
}
