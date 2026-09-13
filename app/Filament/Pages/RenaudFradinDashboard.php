<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Experiences\ExperienceResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Widgets\RenaudFradinGeneralStatsOverview;
use App\Filament\Widgets\RenaudFradinSearchConsoleStatsOverview;
use App\Filament\Widgets\RenaudFradinSearchConsoleTrend;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use UnitEnum;

class RenaudFradinDashboard extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'renaudfradin';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $slug = 'renaudfradin-dashboard';

    protected static ?string $title = 'renaudfradin';

    protected ?string $heading = 'Tableau de bord renaudfradin';

    protected ?string $subheading = 'Vue SEO et contenu pour l univers developpement';

    protected static ?int $navigationSort = -10;

    protected string $view = 'filament.pages.renaudfradin-dashboard';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewProjects')
                ->label('Voir les projets')
                ->url(ProjectResource::getUrl())
                ->icon('heroicon-o-rectangle-stack'),
            Action::make('viewExperiences')
                ->label('Voir les experiences')
                ->url(ExperienceResource::getUrl())
                ->icon('heroicon-o-briefcase'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RenaudFradinSearchConsoleStatsOverview::class,
            RenaudFradinGeneralStatsOverview::class,
            RenaudFradinSearchConsoleTrend::class,
        ];
    }
}
