<?php

namespace App\Filament\Resources\PhotographyCollections\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PhotographyCollectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nom'),
                TextEntry::make('slug')
                    ->label('Slug'),
                TextEntry::make('description')
                    ->label('Description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('active')
                    ->label('Actif')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
