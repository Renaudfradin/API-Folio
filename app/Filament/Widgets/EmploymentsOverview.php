<?php

namespace App\Filament\Widgets;

use App\Models\Employment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmploymentsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = Employment::query()->count();
        $pending = Employment::query()->where('responce', 'pending')->count();
        $accepted = Employment::query()->where('responce', 'yes')->count();
        $rejected = Employment::query()->where('responce', 'no')->count();

        $responseRate = $total > 0
            ? (($accepted + $rejected) / $total) * 100
            : 0;

        return [
            Stat::make('Total candidatures', $total),
            Stat::make('Réponses positives', $accepted),
            Stat::make('En attente', $pending),
            Stat::make('Taux de réponse', number_format($responseRate, 1).' %'),
        ];
    }
}
