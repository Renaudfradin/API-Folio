<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Cameras\CameraResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Article;
use App\Models\Camera;
use App\Models\Category;
use App\Models\Document;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentStatsOverview extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Contenu';

    protected ?string $description = 'Articles, catégories, appareils et documents';

    protected int|string|array $columnSpan = 1;

    protected function getColumns(): int|array|null
    {
        return [
            'default' => 1,
            'sm' => 2,
        ];
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Articles', number_format(Article::query()->count()))
                ->description('Textes publiés')
                ->icon(Heroicon::OutlinedDocumentText)
                ->url(ArticleResource::getUrl()),
            Stat::make('Catégories', number_format(Category::query()->count()))
                ->description('Classement')
                ->icon(Heroicon::OutlinedTag)
                ->url(CategoryResource::getUrl()),
            Stat::make('Appareils', number_format(Camera::query()->count()))
                ->description('Matériel')
                ->icon(Heroicon::OutlinedCamera)
                ->url(CameraResource::getUrl()),
            Stat::make('Documents', number_format(Document::query()->count()))
                ->description('Fichiers liés')
                ->icon(Heroicon::OutlinedFolder)
                ->url(DocumentResource::canViewAny() ? DocumentResource::getUrl() : null),
        ];
    }
}
