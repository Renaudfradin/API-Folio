<?php

namespace App\Filament\Resources\InstagramAccounts\Pages;

use App\Filament\Resources\InstagramAccounts\InstagramAccountResource;
use App\Services\Instagram\InstagramSyncService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListInstagramAccounts extends ListRecords
{
    protected static string $resource = InstagramAccountResource::class;

    public function mount(): void
    {
        parent::mount();

        if (session()->has('error')) {
            Notification::make()
                ->title((string) session('error'))
                ->danger()
                ->send();
        }

        if (session()->has('success')) {
            Notification::make()
                ->title((string) session('success'))
                ->success()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connect')
                ->label('Connecter Instagram')
                ->icon('heroicon-o-link')
                ->action(fn () => redirect()->route('instagram.oauth.redirect')),
            Action::make('syncAll')
                ->label('Synchroniser tout')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(fn () => app(InstagramSyncService::class)->syncAllActive()),
        ];
    }
}
