<?php

namespace App\Filament\Widgets\Instagram;

use App\Filament\Resources\InstagramMedia\InstagramMediaResource;
use App\Filament\Widgets\Instagram\Concerns\InteractsWithInstagramDashboardPage;
use App\Models\InstagramMedia;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class InstagramRecentPostsTable extends TableWidget
{
    use InteractsWithInstagramDashboardPage;

    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Publications récentes';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getTableQuery())
            ->defaultSort('timestamp', 'desc')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                ImageColumn::make('preview_url')
                    ->label('Aperçu')
                    ->checkFileExistence(false)
                    ->square(),
                TextColumn::make('media_type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('caption')
                    ->label('Légende')
                    ->limit(50)
                    ->placeholder('-'),
                TextColumn::make('like_count')
                    ->label('Likes')
                    ->numeric(),
                TextColumn::make('comments_count')
                    ->label('Commentaires')
                    ->numeric(),
                TextColumn::make('view_count')
                    ->label('Vues')
                    ->numeric(),
                TextColumn::make('timestamp')
                    ->label('Publié le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (InstagramMedia $record): string => InstagramMediaResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('Aucune publication')
            ->emptyStateDescription('Synchronise le compte pour importer les posts Instagram.');
    }

    protected function getTableQuery(): Builder
    {
        $account = $this->resolveDashboardAccount();

        if ($account === null) {
            return InstagramMedia::query()->whereRaw('1 = 0');
        }

        return InstagramMedia::query()
            ->where('instagram_account_id', $account->id);
    }
}
