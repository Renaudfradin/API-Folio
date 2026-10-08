<?php

namespace App\Filament\Resources\PhotographyCollections\Pages;

use App\Filament\Resources\PhotographyCollections\PhotographyCollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPhotographyCollections extends ListRecords
{
    protected static string $resource = PhotographyCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
