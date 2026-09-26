<?php

namespace App\Filament\Widgets\LinkedIn;

use App\Filament\Widgets\LinkedIn\Concerns\InteractsWithLinkedInDashboardPage;
use App\Models\LinkedinPost;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LinkedInRecentPostsTable extends TableWidget
{
    use InteractsWithLinkedInDashboardPage;

    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Publications récentes';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getTableQuery())
            ->defaultSort('published_at', 'desc')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('text')
                    ->label('Publication')
                    ->limit(60)
                    ->placeholder('-'),
                TextColumn::make('media_type')
                    ->label('Type')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('reactions')
                    ->label('Réactions')
                    ->numeric(),
                TextColumn::make('comments')
                    ->label('Commentaires')
                    ->numeric(),
                TextColumn::make('impressions')
                    ->label('Impressions')
                    ->numeric(),
                TextColumn::make('published_at')
                    ->label('Publié le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->emptyStateHeading('Aucune publication')
            ->emptyStateDescription('Synchronise la connexion LinkedIn pour importer les posts.');
    }

    protected function getTableQuery(): Builder
    {
        $connection = $this->resolveDashboardConnection();

        if ($connection === null) {
            return LinkedinPost::query()->whereRaw('1 = 0');
        }

        return LinkedinPost::query()
            ->where('linkedin_connection_id', $connection->id);
    }
}
