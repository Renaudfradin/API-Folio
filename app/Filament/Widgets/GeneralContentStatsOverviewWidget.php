<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

abstract class GeneralContentStatsOverviewWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = null;

    /**
     * @return array<int, array{label: string, value: int|string, description?: string}>
     */
    abstract protected function getStatItems(): array;

    protected function getStats(): array
    {
        return collect($this->getStatItems())
            ->map(fn (array $item): Stat => Stat::make($item['label'], number_format((int) $item['value']))
                ->description($item['description'] ?? 'Donnees internes'))
            ->all();
    }
}
