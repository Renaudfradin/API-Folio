<?php

namespace App\Filament\Widgets;

use App\Models\Block;
use App\Models\Experience;
use App\Models\Project;
use Filament\Widgets\ChartWidget;

class LinkedInImportedContentOverview extends ChartWidget
{
    protected ?string $heading = 'Contenu importé depuis LinkedIn';

    protected function getData(): array
    {
        $labels = ['Expériences', 'Projets', 'Sélections'];
        $data = [
            Experience::query()->where('source', 'linkedin')->count(),
            Project::query()->where('source', 'linkedin')->count(),
            Block::query()->where('source', 'linkedin')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Éléments LinkedIn',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                    ],
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
