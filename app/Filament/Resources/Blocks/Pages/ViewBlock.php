<?php

namespace App\Filament\Resources\Blocks\Pages;

use App\Filament\Resources\Blocks\BlockResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBlock extends ViewRecord
{
    protected static string $resource = BlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
