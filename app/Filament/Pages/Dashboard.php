<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Photographies\PhotographyResource;
use App\Filament\Widgets\ContentStatsOverview;
use App\Filament\Widgets\InstagramStatsOverview;
use App\Filament\Widgets\RecentArticlesTable;
use App\Filament\Widgets\RecentEmploymentsTable;
use App\Filament\Widgets\RecentPhotographiesTable;
use App\Filament\Widgets\StatsOverview;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected ?string $subheading = 'Vue d\'ensemble du portfolio';

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 2,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewPhotographies')
                ->label('Photographies')
                ->icon(Heroicon::OutlinedPhoto)
                ->url(fn (): string => PhotographyResource::getUrl()),
            Action::make('viewArticles')
                ->label('Articles')
                ->icon(Heroicon::OutlinedDocumentText)
                ->url(fn (): string => ArticleResource::getUrl()),
            Action::make('createPhotography')
                ->label('Nouvelle photo')
                ->icon(Heroicon::OutlinedPlus)
                ->url(fn (): string => PhotographyResource::getUrl('create'))
                ->visible(fn (): bool => PhotographyResource::canCreate()),
            Action::make('createArticle')
                ->label('Nouvel article')
                ->icon(Heroicon::OutlinedPlus)
                ->url(fn (): string => ArticleResource::getUrl('create'))
                ->visible(fn (): bool => ArticleResource::canCreate()),
        ];
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            ContentStatsOverview::class,
            InstagramStatsOverview::class,
            RecentPhotographiesTable::class,
            RecentArticlesTable::class,
            RecentEmploymentsTable::class,
        ];
    }
}
