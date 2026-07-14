<?php

namespace App\Filament\Resources\InstagramAccounts\Pages;

use App\Filament\Resources\InstagramAccounts\InstagramAccountResource;
use App\Services\Instagram\InstagramSyncService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstagramAccounts extends ListRecords
{
    protected static string $resource = InstagramAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connect')
                ->label('Connecter Instagram')
                ->icon('heroicon-o-link')
                ->url(route('instagram.oauth.redirect')),
            Action::make('syncAll')
                ->label('Synchroniser tout')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(fn () => app(InstagramSyncService::class)->syncAllActive()),
            CreateAction::make(),
        ];
    }
}
