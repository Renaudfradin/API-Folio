<?php

namespace App\Filament\Resources\Employments\Pages;

use App\Filament\Imports\EmploymentImporter;
use App\Filament\Resources\Employments\EmploymentResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployments extends ListRecords
{
    protected static string $resource = EmploymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(EmploymentImporter::class),

            CreateAction::make(),
        ];
    }
}
