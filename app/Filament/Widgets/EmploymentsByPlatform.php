<?php

namespace App\Filament\Widgets;

use App\Models\Employment;
use Filament\Widgets\ChartWidget;

class EmploymentsByPlatform extends ChartWidget
{
    protected ?string $heading = 'Employments by Platform';

    protected function getData(): array
    {
        $platformCounts = Employment::query()
            ->whereNotNull('platform')
            ->groupBy('platform')
            ->selectRaw('platform, count(*) as count')
            ->orderBy('count', 'desc')
            ->get();

        $data = $platformCounts->pluck('count')->toArray();
        $labels = $platformCounts->pluck('platform')->toArray();

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
