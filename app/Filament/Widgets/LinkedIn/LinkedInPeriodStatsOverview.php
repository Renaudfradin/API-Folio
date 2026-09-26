<?php

namespace App\Filament\Widgets\LinkedIn;

use App\Filament\Widgets\LinkedIn\Concerns\InteractsWithLinkedInDashboardPage;
use App\Services\LinkedIn\LinkedInConnectionDashboardMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LinkedInPeriodStatsOverview extends StatsOverviewWidget
{
    use InteractsWithLinkedInDashboardPage;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $connection = $this->resolveDashboardConnection();
        $range = $this->resolveDashboardDateRange();

        if ($connection === null || $range === null) {
            return [
                Stat::make('Posts', '-')
                    ->description('Connecte un compte LinkedIn'),
            ];
        }

        $metrics = app(LinkedInConnectionDashboardMetrics::class)->comparePeriods(
            $connection,
            $range['start'],
            $range['end'],
            $range['previousStart'],
            $range['previousEnd'],
        );

        return [
            $this->periodStat('Posts', $metrics['posts'], false),
            $this->periodStat('Réactions', $metrics['reactions'], false),
            $this->periodStat('Commentaires', $metrics['comments'], false),
            $this->periodStat('Partages', $metrics['shares'], false),
            $this->periodStat('Impressions', $metrics['impressions'], false),
            $this->periodStat('Portée', $metrics['reach'], false),
            $this->periodStat('Vues vidéo', $metrics['video_views'], false),
            $this->periodStat('Taux d\'eng.', $metrics['engagement_rate'], true),
        ];
    }

    /**
     * @param  array{value: mixed, previous: mixed, change_percent: float|null}  $metric
     */
    protected function periodStat(string $label, array $metric, bool $isPercent): Stat
    {
        $value = $metric['value'];

        if ($isPercent) {
            $display = $value === null ? '-' : number_format((float) $value, 1).' %';
        } else {
            $display = is_numeric($value) ? number_format((int) $value) : '-';
        }

        $stat = Stat::make($label, $display);

        $change = $metric['change_percent'];

        if ($change === null) {
            return $stat->description('vs période précédente');
        }

        $formattedChange = ($change > 0 ? '+' : '').number_format($change, 1).' %';

        if ($change > 0) {
            return $stat
                ->description($formattedChange.' vs période préc.')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success');
        }

        if ($change < 0) {
            return $stat
                ->description($formattedChange.' vs période préc.')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger');
        }

        return $stat
            ->description('0 % vs période préc.')
            ->descriptionIcon('heroicon-m-minus');
    }
}
