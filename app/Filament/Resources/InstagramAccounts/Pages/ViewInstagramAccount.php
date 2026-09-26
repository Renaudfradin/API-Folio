<?php

namespace App\Filament\Resources\InstagramAccounts\Pages;

use App\Filament\Resources\InstagramAccounts\InstagramAccountResource;
use App\Services\Instagram\InstagramStoryImportService;
use App\Services\Instagram\InstagramSyncService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewInstagramAccount extends ViewRecord
{
    protected static string $resource = InstagramAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importStories')
                ->label('Importer des stories (CSV)')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalDescription('L’API Instagram ne permet pas de récupérer les stories passées. Importez un export Metricool ou un CSV (modèle : storage/app/examples/instagram-stories-import.csv).')
                ->schema([
                    FileUpload::make('csv')
                        ->label('Fichier CSV')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                        ->disk('local')
                        ->directory('instagram-story-imports')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $uploaded = $data['csv'] ?? null;
                    $relativePath = is_array($uploaded) ? ($uploaded[0] ?? null) : $uploaded;

                    if (! filled($relativePath)) {
                        Notification::make()
                            ->title('Fichier CSV manquant')
                            ->danger()
                            ->send();

                        return;
                    }

                    $path = Storage::disk('local')->path((string) $relativePath);
                    $result = app(InstagramStoryImportService::class)->importFromPath($this->record, $path);

                    Notification::make()
                        ->title('Import des stories terminé')
                        ->body(sprintf(
                            '%d créées, %d mises à jour, %d lignes ignorées.',
                            $result['imported'],
                            $result['updated'],
                            $result['skipped'],
                        ))
                        ->success()
                        ->send();
                }),
            Action::make('sync')
                ->label('Synchroniser')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(fn () => app(InstagramSyncService::class)->syncAccount($this->record)),
            Action::make('edit')
                ->label('Modifier')
                ->icon('heroicon-o-pencil-square')
                ->url(InstagramAccountResource::getUrl('edit', ['record' => $this->record])),
        ];
    }
}
