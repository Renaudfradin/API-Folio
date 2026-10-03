<?php

namespace App\Filament\Resources\InstagramAccounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InstagramAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->label('Username')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('name')
                    ->label('Nom')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('business_account_id')
                    ->label('Instagram user ID')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('biography')
                    ->label('Bio')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                TextInput::make('website')
                    ->label('Site web')
                    ->disabled()
                    ->dehydrated(false),
                DateTimePicker::make('token_expires_at')
                    ->label('Expiration du token')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('followers_count')
                    ->label('Followers')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('follows_count')
                    ->label('Following')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('media_count')
                    ->label('Nombre de posts')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                Toggle::make('is_active')
                    ->label('Actif'),
            ]);
    }
}
