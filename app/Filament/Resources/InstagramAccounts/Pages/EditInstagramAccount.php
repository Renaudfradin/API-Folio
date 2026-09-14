<?php

namespace App\Filament\Resources\InstagramAccounts\Pages;

use App\Filament\Resources\InstagramAccounts\InstagramAccountResource;
use App\Services\Instagram\InstagramSyncService;
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
                ->action(fn () => app(InstagramSyncService::class)->syncAccount($this->record)),
            Action::make('connect')
                ->label('Reconnecter')
                ->icon('heroicon-o-link')
                ->action(fn () => redirect()->route('instagram.oauth.redirect')),
        ];
    }
}
