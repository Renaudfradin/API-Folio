<?php

namespace App\Filament\Resources\Photographies\Pages;

use App\Filament\Resources\Photographies\PhotographyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPhotography extends EditRecord
{
    protected static string $resource = PhotographyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
