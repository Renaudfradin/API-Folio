<?php

namespace App\Filament\Resources\LinkedInProfileStats\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LinkedInProfileStatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('metric_label')
                    ->label('Libellé')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('value')
                    ->label('Valeur')
                    ->placeholder('-'),
                TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->sortable(),
                TextColumn::make('synced_at')
                    ->label('Synchronisé le')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->options([
                        'manual' => 'Manuel',
                        'linkedin' => 'LinkedIn',
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
