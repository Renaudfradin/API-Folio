<?php

namespace App\Filament\Resources\InstagramAccounts\Pages;

use App\Filament\Resources\InstagramAccounts\InstagramAccountResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewInstagramAccount extends ViewRecord
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
            Action::make('edit')
                ->label('Modifier')
                ->icon('heroicon-o-pencil-square')
                ->url(InstagramAccountResource::getUrl('edit', ['record' => $this->record])),
        ];
    }
}
