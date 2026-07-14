<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LinkedInImportedContentOverview;
use App\Filament\Widgets\LinkedInSyncOverview;
use App\Models\LinkedinConnection;
use App\Models\User;
use App\Services\LinkedIn\LinkedInSyncService;
use App\Traits\HasRoleBasedVisibility;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class LinkedInDashboard extends Page
{
    use HasRoleBasedVisibility;

    protected static string|UnitEnum|null $navigationGroup = 'LinkedIn';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Synchronisation';

    protected string $view = 'filament.pages.linkedin-dashboard';

    protected ?string $heading = 'Synchronisation LinkedIn';

    public ?LinkedinConnection $connection = null;

    public static function canAccess(): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function mount(): void
    {
        $this->refreshConnection();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connect')
                ->label('Connecter LinkedIn')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(route('linkedin.redirect')),
            Action::make('sync')
                ->label('Synchroniser')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn (): bool => filled($this->connection?->access_token))
                ->action(function (): void {
                    $user = Auth::user();

                    if (! $user instanceof User) {
                        return;
                    }

                    app(LinkedInSyncService::class)->syncCurrentUser($user);
                    $this->refreshConnection();

                    Notification::make()
                        ->title('Synchronisation terminée')
                        ->success()
                        ->send();
                }),
            Action::make('disconnect')
                ->label('Déconnecter')
                ->icon('heroicon-o-link-slash')
                ->color('danger')
                ->visible(fn (): bool => filled($this->connection))
                ->requiresConfirmation()
                ->action(function (): void {
                    $user = Auth::user();

                    if (! $user instanceof User) {
                        return;
                    }

                    app(LinkedInSyncService::class)->disconnect($user);
                    $this->refreshConnection();

                    Notification::make()
                        ->title('Connexion LinkedIn supprimée')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LinkedInSyncOverview::class,
            LinkedInImportedContentOverview::class,
        ];
    }

    public function refreshConnection(): void
    {
        $user = Auth::user();

        $this->connection = $user instanceof User
            ? $user->linkedinConnection()->first()
            : null;
    }
}
