<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('linkedin_profile_id')
                    ->label('LinkedIn profile ID')
                    ->placeholder('-'),
                TextEntry::make('linkedin_url')
                    ->label('LinkedIn URL')
                    ->url(fn ($record) => $record->linkedin_url)
                    ->openUrlInNewTab()
                    ->placeholder('-'),
                TextEntry::make('linkedin_headline')
                    ->label('LinkedIn headline')
                    ->placeholder('-'),
                TextEntry::make('linkedin_synced_at')
                    ->label('LinkedIn synced at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('role'),
            ]);
    }
}
