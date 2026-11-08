<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Enums\Stack;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->reorderable('position')
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering): Action => $action
                    ->button()
                    ->label($isReordering ? 'Terminer' : 'Changer l’ordre'),
            )
            ->columns([
                TextColumn::make('position')
                    ->label('Ordre')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('url')
                    ->label('Url')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stack')
                    ->label('Stack')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('stack')
                    ->label('Stack')
                    ->options(Stack::class)
                    ->multiple()
                    ->searchable()
                    ->native(false)
                    ->query(function (Builder $query, array $data): Builder {
                        $values = array_values(array_filter(
                            $data['values'] ?? [],
                            fn (mixed $value): bool => filled($value),
                        ));

                        if ($values === []) {
                            return $query;
                        }

                        return $query->where(function (Builder $query) use ($values): void {
                            foreach ($values as $value) {
                                $query->orWhereJsonContains('stack', $value);
                            }
                        });
                    }),
                SelectFilter::make('active')
                    ->options([
                        1 => 'Actif',
                        0 => 'Inactif',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
