<?php

namespace App\Services\Instagram;

use Carbon\Carbon;

class InstagramDashboardDateRange
{
    /**
     * @return array{
     *     start: Carbon,
     *     end: Carbon,
     *     previousStart: Carbon,
     *     previousEnd: Carbon,
     *     label: string
     * }
     */
    public static function forPeriod(string $period, ?Carbon $now = null): array
    {
        $now ??= now();
        $end = $now->copy()->endOfDay();

        $start = match ($period) {
            '7d' => $now->copy()->subDays(6)->startOfDay(),
            'mtd' => $now->copy()->startOfMonth()->startOfDay(),
            default => $now->copy()->subDays(29)->startOfDay(),
        };

        $durationDays = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $previousEnd = $start->copy()->subDay()->endOfDay();
        $previousStart = $previousEnd->copy()->subDays($durationDays - 1)->startOfDay();

        $label = match ($period) {
            '7d' => '7 derniers jours',
            'mtd' => 'Mois en cours',
            default => '30 derniers jours',
        };

        return [
            'start' => $start,
            'end' => $end,
            'previousStart' => $previousStart,
            'previousEnd' => $previousEnd,
            'label' => $label,
        ];
    }
}
