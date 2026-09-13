<?php

namespace App\Filament\Widgets;

use App\Models\Experience;
use App\Models\Project;

class RenaudFradinGeneralStatsOverview extends GeneralContentStatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStatItems(): array
    {
        return [
            [
                'label' => 'Projets',
                'value' => Project::count(),
                'description' => 'Contenu portfolio dev',
            ],
            [
                'label' => 'Experiences',
                'value' => Experience::count(),
                'description' => 'Parcours professionnel',
            ],
        ];
    }
}
