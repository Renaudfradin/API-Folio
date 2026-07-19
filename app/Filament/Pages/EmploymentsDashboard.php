<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Employments\EmploymentResource;
use App\Filament\Widgets\EmploymentsByCity;
use App\Filament\Widgets\EmploymentsByPlatform;
use App\Filament\Widgets\EmploymentsOverview;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use UnitEnum;

class EmploymentsDashboard extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Employments';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Tableau de bord';

    protected string $view = 'filament.pages.employments-dashboard';

    protected ?string $heading = 'Tableau de bord candidatures';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewEmployments')
                ->label('Voir les candidatures')
                ->url(EmploymentResource::getUrl())
                ->icon('heroicon-o-briefcase'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmploymentsOverview::class,
            EmploymentsByCity::class,
            EmploymentsByPlatform::class,
        ];
    }
}
