<?php

namespace App\Filament\Widgets;

use App\Models\Employment;
use Filament\Widgets\ChartWidget;

class EmploymentsByCity extends ChartWidget
{
    protected ?string $heading = 'Employments by City';

    protected function getData(): array
    {
        $cityCounts = Employment::query()
            ->whereNotNull('location')
            ->groupBy('location')
            ->selectRaw('location, count(*) as count')
            ->orderBy('count', 'desc')
            ->get();

        $data = $cityCounts->pluck('count')->toArray();
        $labels = $cityCounts->pluck('location')->toArray();

        $colors = collect($labels)->map(function ($_, $index) {
            $hue = ($index * 137) % 360;

            return "hsl({$hue}, 70%, 60%)";
        })->toArray();

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
