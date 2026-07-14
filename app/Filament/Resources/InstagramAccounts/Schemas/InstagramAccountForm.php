<?php

namespace App\Filament\Resources\InstagramAccounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InstagramAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
                TextInput::make('page_id')
                    ->label('Facebook Page ID')
                    ->maxLength(255),
                TextInput::make('page_name')
                    ->label('Facebook Page')
                    ->maxLength(255),
                TextInput::make('business_account_id')
                    ->label('Instagram Business Account ID')
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->label('Username')
                    ->maxLength(255),
                TextInput::make('name')
                    ->label('Nom')
                    ->maxLength(255),
                TextInput::make('biography')
                    ->label('Bio')
                    ->columnSpanFull(),
                TextInput::make('website')
                    ->label('Site web')
                    ->url()
                    ->maxLength(255),
                TextInput::make('profile_picture_url')
                    ->label('Photo de profil')
                    ->url()
                    ->maxLength(255),
                TextInput::make('access_token')
                    ->label('Access token')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(65535),
                DateTimePicker::make('token_expires_at')
                    ->label('Expiration du token'),
                TextInput::make('followers_count')
                    ->label('Followers')
                    ->numeric()
                    ->default(0),
                TextInput::make('follows_count')
                    ->label('Following')
                    ->numeric()
                    ->default(0),
                TextInput::make('media_count')
                    ->label('Nombre de posts')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true),
            ]);
    }
}
