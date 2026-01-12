<?php

namespace App\Filament\Resources\Photographies\Pages;

use App\Filament\Resources\Photographies\PhotographyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPhotographies extends ListRecords
{
    protected static string $resource = PhotographyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
