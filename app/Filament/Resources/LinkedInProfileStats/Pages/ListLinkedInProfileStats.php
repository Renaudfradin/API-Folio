<?php

namespace App\Filament\Resources\LinkedInProfileStats\Pages;

use App\Filament\Resources\LinkedInProfileStats\LinkedInProfileStatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLinkedInProfileStats extends ListRecords
{
    protected static string $resource = LinkedInProfileStatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
