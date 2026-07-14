<?php

namespace App\Filament\Resources\LinkedInProfileStats\Pages;

use App\Filament\Resources\LinkedInProfileStats\LinkedInProfileStatResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLinkedInProfileStat extends EditRecord
{
    protected static string $resource = LinkedInProfileStatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
