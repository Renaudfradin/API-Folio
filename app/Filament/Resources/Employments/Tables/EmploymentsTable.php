<?php

namespace App\Filament\Resources\Employments\Tables;

use App\Enums\Platform;
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

class EmploymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
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
                    ->sortable()
                    ->searchable(),
                IconColumn::make('responce')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('platform')
                    ->options(Platform::class),
                SelectFilter::make('responce')
                    ->options([true => 'Oui', false => 'Non']),
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
