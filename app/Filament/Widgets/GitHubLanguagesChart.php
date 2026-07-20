<?php

namespace App\Filament\Widgets;

use App\Services\GitHub\GitHubService;
use Filament\Widgets\ChartWidget;
use RuntimeException;
use Throwable;

class GitHubLanguagesChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Technologies';

    protected ?string $description = 'Répartition des langages sur tes dépôts';

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '600px';

    protected int|string|array $columnSpan = 'full';

    /**
     * @var array<string, string>
     */
    protected array $languageColors = [
        'PHP' => '#7C3AED',
        'Python' => '#EAB308',
        'HTML' => '#EF4444',
        'TypeScript' => '#3B82F6',
        'TS' => '#3B82F6',
    ];

    protected function getData(): array
    {
        try {
            $languages = app(GitHubService::class)->getAccountStats()['languages'] ?? [];
        } catch (RuntimeException|Throwable) {
            return [
                'datasets' => [
                    [
                        'data' => [],
                        'backgroundColor' => [],
                    ],
                ],
                'labels' => [],
            ];
        }

        $top = array_slice($languages, 0, 10, true);
        $labels = array_keys($top);
        $data = array_values($top);

        $fallbackIndex = 0;
        $colors = collect($labels)->map(function (string $language) use (&$fallbackIndex): string {
            if (isset($this->languageColors[$language])) {
                return $this->languageColors[$language];
            }

            $hue = ($fallbackIndex * 137) % 360;
            $fallbackIndex++;

            return "hsl({$hue}, 70%, 55%)";
        })->all();

        return [
            'datasets' => [
                [
                    'label' => 'Dépôts',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
