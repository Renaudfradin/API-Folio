<?php

namespace App\Filament\Widgets;

use App\Services\Google\GoogleSearchConsoleService;
use Filament\Widgets\ChartWidget;
use RuntimeException;
use Throwable;

abstract class SearchConsolePerformanceChartWidget extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '320px';

    protected int|string|array $columnSpan = 'full';

    abstract protected function getSiteKey(): string;

    protected function getData(): array
    {
        try {
            $series = app(GoogleSearchConsoleService::class)->getDailySeries($this->getSiteKey());

            return [
                'datasets' => [
                    [
                        'label' => 'Clics',
                        'data' => $series['clicks'],
                        'borderColor' => '#f59e0b',
                        'backgroundColor' => 'rgba(245, 158, 11, 0.12)',
                        'tension' => 0.3,
                    ],
                    [
                        'label' => 'Impressions',
                        'data' => $series['impressions'],
                        'borderColor' => '#14b8a6',
                        'backgroundColor' => 'rgba(20, 184, 166, 0.12)',
                        'tension' => 0.3,
                    ],
                ],
                'labels' => $series['labels'],
            ];
        } catch (RuntimeException|Throwable) {
            return [
                'datasets' => [
                    [
                        'label' => 'Clics',
                        'data' => [],
                    ],
                    [
                        'label' => 'Impressions',
                        'data' => [],
                    ],
                ],
                'labels' => [],
            ];
        }
    }

    protected function getType(): string
    {
        return 'line';
    }
}
