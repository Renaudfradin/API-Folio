<?php

namespace App\Filament\Resources\InstagramAccounts\Pages;

use App\Filament\Resources\InstagramAccounts\InstagramAccountResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateInstagramAccount extends CreateRecord
{
    protected static string $resource = InstagramAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connect')
                ->label('Connecter Instagram')
                ->icon('heroicon-o-link')
                ->url(route('instagram.oauth.redirect')),
        ];
    }
}
