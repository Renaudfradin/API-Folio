<?php

namespace App\Filament\Resources\Blocks\Pages;

use App\Filament\Resources\Blocks\BlockResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBlock extends EditRecord
{
    protected static string $resource = BlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
