<?php

namespace App\Filament\Resources\Stacks\Pages;

use App\Filament\Resources\Stacks\StackResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStack extends ViewRecord
{
    protected static string $resource = StackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
