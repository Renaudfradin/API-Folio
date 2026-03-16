<?php

namespace App\Filament\Imports;

use App\Models\Employment;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class EmploymentImporter extends Importer
{
    protected static ?string $model = Employment::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('title')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('platform')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('location')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('link_job')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('responce')
                ->boolean()
                ->rules(['boolean']),
            ImportColumn::make('response_date')
                ->rules(['date']),
            ImportColumn::make('notes'),
        ];
    }

    public function resolveRecord(): Employment
    {
        return new Employment;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your employment import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
