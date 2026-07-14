<?php

namespace App\Filament\Resources\Experiences\Schemas;

use Filament\Infolists\Components\IconEntry;
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
                TextEntry::make('company')
                    ->label('Entreprise'),
                TextEntry::make('source')
                    ->label('Source'),
                TextEntry::make('external_id')
                    ->label('External ID')
                    ->placeholder('-'),
                TextEntry::make('linkedin_url')
                    ->label('LinkedIn URL')
                    ->url(fn ($record) => $record->linkedin_url)
                    ->openUrlInNewTab()
                    ->placeholder('-'),
                TextEntry::make('synced_at')
                    ->label('Synchronisé le')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('active')
                    ->label('Actif')
                    ->boolean(),
            ]);
    }
}
