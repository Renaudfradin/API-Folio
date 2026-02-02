<?php

namespace App\Filament\Resources\Cameras\Pages;

use App\Filament\Resources\Cameras\CameraResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCamera extends ViewRecord
{
    protected static string $resource = CameraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
