<?php

namespace App\Filament\Resources\PhotographyCollections\Pages;

use App\Filament\Resources\PhotographyCollections\PhotographyCollectionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPhotographyCollection extends EditRecord
{
    protected static string $resource = PhotographyCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
