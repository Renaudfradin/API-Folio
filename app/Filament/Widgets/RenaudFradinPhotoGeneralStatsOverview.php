<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Camera;
use App\Models\Category;
use App\Models\Photography;

class RenaudFradinPhotoGeneralStatsOverview extends GeneralContentStatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStatItems(): array
    {
        return [
            [
                'label' => 'Photographies',
                'value' => Photography::count(),
                'description' => 'Galerie photo',
            ],
            [
                'label' => 'Articles',
                'value' => Article::count(),
                'description' => 'Articles publies',
            ],
            [
                'label' => 'Categories',
                'value' => Category::count(),
                'description' => 'Organisation du contenu',
            ],
            [
                'label' => 'Cameras',
                'value' => Camera::count(),
                'description' => 'Materiel reference',
            ],
        ];
    }
}
