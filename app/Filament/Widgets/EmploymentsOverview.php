<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class EmploymentsOverview extends ChartWidget
{
    protected ?string $heading = 'Employments Overview';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'data' => [10, 20, 30],
                    'backgroundColor' => [
                        'red',
                        'green',
                        'blue',
                    ],
                ],
            ],
            'labels' => [
                'Red',
                'Green',
                'Blue',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
