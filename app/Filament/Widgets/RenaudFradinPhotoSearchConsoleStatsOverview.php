<?php

namespace App\Filament\Widgets;

class RenaudFradinPhotoSearchConsoleStatsOverview extends SearchConsoleStatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getSiteKey(): string
    {
        return 'renaudfradinphoto';
    }
}
