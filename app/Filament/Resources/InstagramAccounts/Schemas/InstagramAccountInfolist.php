<?php

namespace App\Filament\Resources\InstagramAccounts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InstagramAccountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('username')
                    ->label('Username'),
                TextEntry::make('name')
                    ->label('Nom'),
                TextEntry::make('page_name')
                    ->label('Page Facebook'),
                TextEntry::make('followers_count')
                    ->label('Followers'),
                TextEntry::make('follows_count')
                    ->label('Following'),
                TextEntry::make('media_count')
                    ->label('Posts'),
                TextEntry::make('last_synced_at')
                    ->label('Dernière synchro')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_synced_status')
                    ->label('Statut')
                    ->placeholder('-'),
                TextEntry::make('last_synced_error')
                    ->label('Dernière erreur')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ]);
    }
}
