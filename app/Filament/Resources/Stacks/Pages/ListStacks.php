<?php

namespace App\Filament\Resources\Stacks\Pages;

use App\Filament\Resources\Stacks\StackResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStacks extends ListRecords
{
    protected static string $resource = StackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
