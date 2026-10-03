<?php

namespace App\Filament\Resources\InstagramMedia\Pages;

use App\Filament\Resources\InstagramMedia\InstagramMediaResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInstagramMedia extends ViewRecord
{
    protected static string $resource = InstagramMediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openInstagram')
                ->label('Voir sur Instagram')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): ?string => $this->record->permalink)
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->record->permalink)),
            Action::make('copyPermalink')
                ->label('Copier le lien')
                ->icon('heroicon-o-clipboard-document')
                ->visible(fn (): bool => filled($this->record->permalink))
                ->action(function (): void {
                    $permalink = $this->record->permalink;

                    $this->js('window.navigator.clipboard.writeText('.json_encode($permalink).')');

                    Notification::make()
                        ->title('Lien copié')
                        ->success()
                        ->send();
                }),
        ];
    }
}
