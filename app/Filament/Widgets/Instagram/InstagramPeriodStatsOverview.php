<?php

namespace App\Filament\Widgets\Instagram;

use App\Filament\Widgets\Instagram\Concerns\InteractsWithInstagramDashboardPage;
use App\Services\Instagram\InstagramAccountDashboardMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InstagramPeriodStatsOverview extends StatsOverviewWidget
{
    use InteractsWithInstagramDashboardPage;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $account = $this->resolveDashboardAccount();
        $range = $this->resolveDashboardDateRange();

        if ($account === null || $range === null) {
            return [
                Stat::make('Publications', '-')
                    ->description('Connecte un compte Instagram'),
            ];
        }

        $metrics = app(InstagramAccountDashboardMetrics::class)->comparePeriods(
            $account,
            $range['start'],
            $range['end'],
            $range['previousStart'],
            $range['previousEnd'],
        );

        return [
            $this->periodStat('Publications', $metrics['posts'], false),
            $this->periodStat('Réactions', $metrics['reactions'], false),
            $this->periodStat('Commentaires', $metrics['comments'], false),
            $this->periodStat('Vues', $metrics['views'], false),
            $this->periodStat('Portée', $metrics['reach'], false),
            $this->periodStat('Enregistrements', $metrics['saved'], false),
            $this->periodStat('Engagement', $metrics['engagement'], false),
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
