<?php

namespace App\Filament\Resources\Stacks\Pages;

use App\Filament\Resources\Stacks\StackResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStack extends EditRecord
{
    protected static string $resource = StackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
