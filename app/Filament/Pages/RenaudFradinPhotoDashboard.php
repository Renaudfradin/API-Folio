<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Cameras\CameraResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Photographies\PhotographyResource;
use App\Filament\Widgets\RenaudFradinPhotoGeneralStatsOverview;
use App\Filament\Widgets\RenaudFradinPhotoSearchConsoleStatsOverview;
use App\Filament\Widgets\RenaudFradinPhotoSearchConsoleTrend;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use UnitEnum;

class RenaudFradinPhotoDashboard extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'renaudfradinphoto';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $slug = 'renaudfradinphoto-dashboard';

    protected static ?string $title = 'renaudfradinphoto';

    protected ?string $heading = 'Tableau de bord renaudfradinphoto';

    protected ?string $subheading = 'Vue SEO et contenu pour l univers photo';

    protected static ?int $navigationSort = -10;

    protected string $view = 'filament.pages.renaudfradinphoto-dashboard';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewPhotographies')
                ->label('Voir les photos')
                ->url(PhotographyResource::getUrl())
                ->icon('heroicon-o-photo'),
            Action::make('viewArticles')
                ->label('Voir les articles')
                ->url(ArticleResource::getUrl())
                ->icon('heroicon-o-document-text'),
            Action::make('viewCategories')
                ->label('Voir les categories')
                ->url(CategoryResource::getUrl())
                ->icon('heroicon-o-tag'),
            Action::make('viewCameras')
                ->label('Voir les cameras')
                ->url(CameraResource::getUrl())
                ->icon('heroicon-o-camera'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RenaudFradinPhotoSearchConsoleStatsOverview::class,
            RenaudFradinPhotoGeneralStatsOverview::class,
            RenaudFradinPhotoSearchConsoleTrend::class,
        ];
    }
}
