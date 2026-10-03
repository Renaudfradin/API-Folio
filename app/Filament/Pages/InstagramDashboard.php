<?php

namespace App\Filament\Pages;

use App\Filament\Resources\InstagramMedia\InstagramMediaResource;
use App\Filament\Widgets\Instagram\InstagramAccountSnapshotOverview;
use App\Filament\Widgets\Instagram\InstagramPeriodStatsOverview;
use App\Filament\Widgets\Instagram\InstagramRecentPostsTable;
use App\Models\InstagramAccount;
use App\Services\Instagram\InstagramDashboardDateRange;
use App\Services\Instagram\InstagramSyncService;
use App\Traits\HasRoleBasedVisibility;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Support\Collection;
use UnitEnum;

class InstagramDashboard extends Page
{
    use HasRoleBasedVisibility;

    protected static string|UnitEnum|null $navigationGroup = 'Social';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $slug = 'instagram-dashboard';

    protected static ?string $title = 'Instagram';

    protected static ?int $navigationSort = -2;

    protected ?string $heading = 'Tableau de bord Instagram';

    protected ?string $subheading = 'Statistiques du compte et performances des publications';

    protected string $view = 'filament.pages.instagram-dashboard';

    public ?int $instagramAccountId = null;

    public string $period = '30d';

    public static function canAccess(): bool
    {
        return self::isCurrentUserAdminOrDemo();
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

        if ($this->instagramAccountId !== null) {
            return;
        }

        $this->instagramAccountId = InstagramAccount::query()
            ->where('is_active', true)
            ->orderByDesc('last_synced_at')
            ->orderByDesc('id')
            ->value('id');
    }

    public function getAccount(): ?InstagramAccount
    {
        if ($this->instagramAccountId === null) {
            return null;
        }

        return InstagramAccount::query()->find($this->instagramAccountId);
    }

    /**
     * @return Collection<int, string>
     */
    public function getAccountOptions(): Collection
    {
        return InstagramAccount::query()
            ->orderBy('username')
            ->pluck('username', 'id');
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
        return InstagramDashboardDateRange::forPeriod($this->period);
    }

    public function updatedInstagramAccountId(): void
    {
        $this->broadcastDashboardFilters();
    }

    public function updatedPeriod(): void
    {
        $this->broadcastDashboardFilters();
    }

    protected function broadcastDashboardFilters(): void
    {
        unset($this->cachedHeaderWidgetsSchemaComponents, $this->cachedFooterWidgetsSchemaComponents);

        $this->dispatch(
            'instagram-dashboard-filters-updated',
            accountId: $this->instagramAccountId,
            period: $this->period,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            self::applyAdminVisibility(
                Action::make('connect')
                    ->label('Connecter Instagram')
                    ->icon('heroicon-o-link')
                    ->action(fn () => redirect()->route('instagram.oauth.redirect')),
            ),
            self::applyAdminVisibility(
                Action::make('sync')
                    ->label('Synchroniser')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (): void {
                        $account = $this->getAccount();

                        if ($account === null) {
                            app(InstagramSyncService::class)->syncAllActive();

                            Notification::make()
                                ->title('Synchronisation lancée')
                                ->success()
                                ->send();

                            return;
                        }

                        app(InstagramSyncService::class)->syncAccount($account);

                        Notification::make()
                            ->title('Compte synchronisé')
                            ->success()
                            ->send();
                    }),
            ),
            Action::make('posts')
                ->label('Posts')
                ->icon('heroicon-o-photo')
                ->url(InstagramMediaResource::getUrl()),
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
            $this->makeWidgetConfiguration(InstagramAccountSnapshotOverview::class),
            $this->makeWidgetConfiguration(InstagramPeriodStatsOverview::class),
            $this->makeWidgetConfiguration(InstagramRecentPostsTable::class),
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
            'instagramAccountId' => $this->instagramAccountId,
            'period' => $this->period,
        ]);
    }
}
