<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProjectInfolist
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
                    ->label('Description'),
                TextEntry::make('url')
                    ->label('Url')
                    ->placeholder('-'),
                TextEntry::make('url_github')
                    ->label('Url GitHub')
                    ->placeholder('-'),
                TextEntry::make('stack')
                    ->label('Stack')
                    ->numeric(),
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
