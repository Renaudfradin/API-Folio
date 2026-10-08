<?php

namespace App\Filament\Resources\PhotographyCollections\Pages;

use App\Filament\Resources\PhotographyCollections\PhotographyCollectionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPhotographyCollection extends ViewRecord
{
    protected static string $resource = PhotographyCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
