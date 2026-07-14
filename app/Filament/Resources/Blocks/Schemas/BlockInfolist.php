<?php

namespace App\Filament\Resources\Blocks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BlockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Titre'),
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
                TextEntry::make('content')
                    ->label('Contenu')
                    ->columnSpanFull(),
            ]);
    }
}
