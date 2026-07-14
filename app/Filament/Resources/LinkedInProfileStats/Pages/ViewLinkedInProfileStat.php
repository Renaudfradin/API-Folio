<?php

namespace App\Filament\Resources\LinkedInProfileStats\Pages;

use App\Filament\Resources\LinkedInProfileStats\LinkedInProfileStatResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLinkedInProfileStat extends ViewRecord
{
    protected static string $resource = LinkedInProfileStatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
