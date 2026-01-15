<?php

namespace App\Filament\Resources\Experiences\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExperienceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Titre'),
                TextEntry::make('slug')
                    ->label('Slug'),
                TextEntry::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                TextEntry::make('start_date')
                    ->label('Date de début')
                    ->date(),
                TextEntry::make('end_date')
                    ->label('Date de fin')
                    ->date(),
                TextEntry::make('type')
                    ->label('Type'),
            ]);
    }
}
