<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentArticlesTable extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Derniers articles')
            ->query(fn (): Builder => Article::query()->with('category')->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->limit(36),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Ajout')
                    ->since(),
            ])
            ->recordUrl(fn (Article $record): string => ArticleResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('all')
                    ->label('Tout voir')
                    ->icon(Heroicon::OutlinedArrowRight)
                    ->url(ArticleResource::getUrl())
                    ->link(),
            ])
            ->emptyStateHeading('Aucun article')
            ->emptyStateDescription('Les articles ajoutés apparaîtront ici.');
    }
}
