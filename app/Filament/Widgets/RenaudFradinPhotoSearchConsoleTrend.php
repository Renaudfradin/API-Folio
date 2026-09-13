<?php

namespace App\Filament\Widgets;

class RenaudFradinPhotoSearchConsoleTrend extends SearchConsolePerformanceChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Evolution Search Console';

    protected ?string $description = 'Clics et impressions des 28 derniers jours';

    protected function getSiteKey(): string
    {
        return 'renaudfradinphoto';
    }
}
