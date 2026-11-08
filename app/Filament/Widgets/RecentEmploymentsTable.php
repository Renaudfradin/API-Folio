<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Employments\EmploymentResource;
use App\Models\Employment;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentEmploymentsTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return EmploymentResource::canViewAny();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Dernières candidatures')
            ->query(fn (): Builder => Employment::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('company')
                    ->label('Société')
                    ->limit(24),
                TextColumn::make('title')
                    ->label('Poste')
                    ->limit(28),
                IconColumn::make('responce')
                    ->label('')
                    ->icon(fn (?string $state): Heroicon => match ($state) {
                        'no' => Heroicon::OutlinedXMark,
                        'yes' => Heroicon::OutlinedCheckCircle,
                        default => Heroicon::OutlinedClock,
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'no' => 'danger',
                        'yes' => 'success',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')
                    ->label('Ajout')
                    ->since(),
            ])
            ->recordUrl(fn (Employment $record): string => EmploymentResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('all')
                    ->label('Tout voir')
                    ->icon(Heroicon::OutlinedArrowRight)
                    ->url(EmploymentResource::getUrl())
                    ->link(),
            ])
            ->emptyStateHeading('Aucune candidature')
            ->emptyStateDescription('Les candidatures ajoutées apparaîtront ici.');
    }
}
