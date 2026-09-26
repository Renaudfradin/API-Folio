<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LinkedIn\LinkedInConnectionSnapshotOverview;
use App\Filament\Widgets\LinkedIn\LinkedInPeriodStatsOverview;
use App\Filament\Widgets\LinkedIn\LinkedInRecentPostsTable;
use App\Models\LinkedinConnection;
use App\Models\User;
use App\Services\LinkedIn\LinkedInDashboardDateRange;
use App\Services\LinkedIn\LinkedInSyncService;
use App\Traits\HasRoleBasedVisibility;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class LinkedInDashboard extends Page
{
    use HasRoleBasedVisibility;

    protected static string|UnitEnum|null $navigationGroup = 'LinkedIn';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $slug = 'linkedin-dashboard';

    protected static ?string $title = 'LinkedIn';

    protected static ?int $navigationSort = -2;

    protected string $view = 'filament.pages.linkedin-dashboard';

    protected ?string $heading = 'Tableau de bord LinkedIn';

    protected ?string $subheading = 'Statistiques du profil et performances des publications';

    public ?int $linkedinConnectionId = null;

    public string $period = '30d';

    public static function canAccess(): bool
    {
        return self::isCurrentUserAdmin();
    }

    public function mount(): void
    {
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

        if ($this->linkedinConnectionId !== null) {
            return;
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $this->linkedinConnectionId = $user->linkedinConnection?->id;
    }

    public function getConnection(): ?LinkedinConnection
    {
        if ($this->linkedinConnectionId === null) {
            return null;
        }

        return LinkedinConnection::query()->find($this->linkedinConnectionId);
    }

    /**
     * @return array{
     *     start: Carbon,
     *     end: Carbon,
     *     previousStart: Carbon,
     *     previousEnd: Carbon,
     *     label: string
     * }
     */
    public function getDateRange(): array
    {
        return LinkedInDashboardDateRange::forPeriod($this->period);
    }

    public function updatedPeriod(): void
    {
        $this->broadcastDashboardFilters();
    }

    protected function broadcastDashboardFilters(): void
    {
        unset($this->cachedHeaderWidgetsSchemaComponents, $this->cachedFooterWidgetsSchemaComponents);

        $this->dispatch(
            'linkedin-dashboard-filters-updated',
            connectionId: $this->linkedinConnectionId,
            period: $this->period,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connect')
                ->label('Connecter LinkedIn')
                ->icon('heroicon-o-link')
                ->action(fn () => redirect()->route('linkedin.oauth.redirect')),
            Action::make('sync')
                ->label('Synchroniser')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->visible(fn (): bool => filled($this->getConnection()?->access_token))
                ->action(function (): void {
                    $connection = $this->getConnection();

                    if ($connection === null) {
                        app(LinkedInSyncService::class)->syncAllActive();

                        Notification::make()
                            ->title('Synchronisation lancée')
                            ->success()
                            ->send();

                        return;
                    }

                    app(LinkedInSyncService::class)->syncConnection($connection);

                    Notification::make()
                        ->title('Connexion synchronisée')
                        ->success()
                        ->send();
                }),
            Action::make('disconnect')
                ->label('Déconnecter')
                ->icon('heroicon-o-link-slash')
                ->color('danger')
                ->visible(fn (): bool => filled($this->getConnection()))
                ->requiresConfirmation()
                ->action(function (): void {
                    $user = Auth::user();

                    if (! $user instanceof User) {
                        return;
                    }

                    app(LinkedInSyncService::class)->disconnect($user);
                    $this->linkedinConnectionId = null;

                    Notification::make()
                        ->title('Connexion LinkedIn supprimée')
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * @return array<class-string|WidgetConfiguration>
     */
    protected function getHeaderWidgets(): array
    {
        return [];
    }

    /**
     * @return array<class-string|WidgetConfiguration>
     */
    protected function getFooterWidgets(): array
    {
        return [
            $this->makeWidgetConfiguration(LinkedInConnectionSnapshotOverview::class),
            $this->makeWidgetConfiguration(LinkedInPeriodStatsOverview::class),
            $this->makeWidgetConfiguration(LinkedInRecentPostsTable::class),
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 4;
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 4;
    }

    /**
     * @param  class-string  $widget
     */
    protected function makeWidgetConfiguration(string $widget): WidgetConfiguration
    {
        return new WidgetConfiguration($widget, [
            'linkedinConnectionId' => $this->linkedinConnectionId,
            'period' => $this->period,
        ]);
    }
}
