<?php

namespace App\Filament\Resources\Employments\Tables;

use App\Enums\Platform;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmploymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('company')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('date')
                    ->date('j M Y')
                    ->sortable(),
                TextColumn::make('platform')
                    ->sortable(),
                IconColumn::make('responce')
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        'no' => Heroicon::OutlinedXMark,
                        'yes' => Heroicon::OutlinedCheckCircle,
                        'pending' => Heroicon::OutlinedClock,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'no' => 'danger',
                        'yes' => 'success',
                        'pending' => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('platform')
                    ->options(Platform::class),
                SelectFilter::make('responce')
                    ->options(
                        [
                            'no' => 'Non',
                            'yes' => 'Oui',
                            'pending' => 'En attente',
                        ]
                    ),

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
