<?php

namespace App\Filament\Widgets;

class RenaudFradinSearchConsoleStatsOverview extends SearchConsoleStatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getSiteKey(): string
    {
        return 'renaudfradin';
    }
}
