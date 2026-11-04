<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Models\Article;
use App\Traits\HasRoleBasedVisibility;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticlesTable
{
    use HasRoleBasedVisibility;

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->withSum('readingStats as views_count', 'views_count')
                ->withSum('readingStats as reads_count', 'reads_count'))
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $query) use ($search): void {
                            foreach (Article::LOCALES as $locale) {
                                $query->orWhere("title->{$locale}", 'like', "%{$search}%");
                            }
                        });
                    }),
                TextColumn::make('views_count')
                    ->label('Vues')
                    ->sortable()
                    ->formatStateUsing(fn (mixed $state): int => (int) $state),
                TextColumn::make('reads_count')
                    ->label('Lectures')
                    ->sortable()
                    ->formatStateUsing(fn (mixed $state): int => (int) $state),
                ImageColumn::make('image')
                    ->disk('scaleway')
                    ->visibility('public'),
                IconColumn::make('active')
                    ->boolean(),
                TextColumn::make('category.name')
                    ->label('Catégorie'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
                SelectFilter::make('active')
                    ->options([
                        1 => 'Actif',
                        0 => 'Inactif',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                self::applyAdminVisibility(EditAction::make()),
                self::applyAdminVisibility(DeleteAction::make()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::applyAdminVisibility(DeleteBulkAction::make()),
                ]),
            ]);
    }
}
