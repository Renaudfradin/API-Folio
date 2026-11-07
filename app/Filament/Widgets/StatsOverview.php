<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Employments\EmploymentResource;
use App\Filament\Resources\Experiences\ExperienceResource;
use App\Filament\Resources\Photographies\PhotographyResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Employment;
use App\Models\Experience;
use App\Models\Photography;
use App\Models\Project;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Aperçu';

    protected ?string $description = 'Photographies, projets, expériences et candidatures';

    protected function getColumns(): int|array|null
    {
        return [
            'default' => 1,
            'sm' => 2,
            'xl' => 4,
        ];
    }

    protected function getStats(): array
    {
        $canViewEmployments = EmploymentResource::canViewAny();
        $pendingEmployments = Employment::query()->where('responce', 'pending')->count();

        return [
            Stat::make('Photographies', number_format(Photography::query()->count()))
                ->description('Galerie')
                ->icon(Heroicon::OutlinedPhoto)
                ->url(PhotographyResource::getUrl()),
            Stat::make('Projets', number_format(Project::query()->count()))
                ->description('Portfolio')
                ->icon(Heroicon::OutlinedRectangleStack)
                ->url(ProjectResource::getUrl()),
            Stat::make('Expériences', number_format(Experience::query()->count()))
                ->description('Parcours')
                ->icon(Heroicon::OutlinedBuildingOffice)
                ->url(ExperienceResource::getUrl()),
            Stat::make('Candidatures', number_format(Employment::query()->count()))
                ->description($canViewEmployments ? number_format($pendingEmployments).' en attente' : 'Suivi des réponses')
                ->descriptionIcon(Heroicon::OutlinedClock, IconPosition::Before)
                ->descriptionColor($canViewEmployments && $pendingEmployments > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->url($canViewEmployments ? EmploymentResource::getUrl() : null),
        ];
    }
}
