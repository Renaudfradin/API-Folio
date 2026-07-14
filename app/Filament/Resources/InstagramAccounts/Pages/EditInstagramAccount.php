<?php

namespace App\Filament\Resources\InstagramAccounts\Pages;

use App\Filament\Resources\InstagramAccounts\InstagramAccountResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditInstagramAccount extends EditRecord
{
    protected static string $resource = InstagramAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Synchroniser')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(fn () => app(\App\Services\Instagram\InstagramSyncService::class)->syncAccount($this->record)),
            Action::make('connect')
                ->label('Reconnecter')
                ->icon('heroicon-o-link')
                ->url(route('instagram.oauth.redirect')),
        ];
    }
}
