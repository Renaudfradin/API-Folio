<?php

namespace App\Filament\Resources\Employments\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EmploymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('company'),
                TextEntry::make('title'),
                TextEntry::make('date')
                    ->date(),
                TextEntry::make('platform'),
                TextEntry::make('location'),
                TextEntry::make('link_job')
                    ->url(fn ($record) => $record->link_job)
                    ->openUrlInNewTab(),
                IconEntry::make('responce')
                    ->boolean(),
                TextEntry::make('response_date')
                    ->date(),
                TextEntry::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
