<?php

namespace App\Filament\Resources\Photographies\Pages;

use App\Filament\Resources\Photographies\PhotographyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPhotography extends ViewRecord
{
    protected static string $resource = PhotographyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
