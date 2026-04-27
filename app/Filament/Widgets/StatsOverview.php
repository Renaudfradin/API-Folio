<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Camera;
use App\Models\Category;
use App\Models\Document;
use App\Models\Employment;
use App\Models\Experience;
use App\Models\Photography;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Photography', Photography::count()),
            Stat::make('Total Project', Project::count()),
            Stat::make('Total Experience', Experience::count()),
            Stat::make('Total Camera', Camera::count()),
            Stat::make('Total Document', Document::count()),
            Stat::make('Total Employment', Employment::count()),
            Stat::make('Total Article', Article::count()),
            Stat::make('Total Category', Category::count()),
        ];
    }
}
