<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Photographies\PhotographyResource;
use App\Models\Photography;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentPhotographiesTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Dernières photos')
            ->query(fn (): Builder => Photography::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('scaleway')
                    ->visibility('public')
                    ->checkFileExistence(false)
                    ->square()
                    ->imageHeight(40),
                TextColumn::make('name')
                    ->label('Nom')
                    ->limit(32),
                TextColumn::make('created_at')
                    ->label('Ajout')
                    ->since(),
            ])
            ->recordUrl(fn (Photography $record): string => PhotographyResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('all')
                    ->label('Tout voir')
                    ->icon(Heroicon::OutlinedArrowRight)
                    ->url(PhotographyResource::getUrl())
                    ->link(),
            ])
            ->emptyStateHeading('Aucune photo')
            ->emptyStateDescription('Les photos ajoutées apparaîtront ici.');
    }
}
