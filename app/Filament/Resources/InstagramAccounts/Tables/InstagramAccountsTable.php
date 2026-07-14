<?php

namespace App\Filament\Resources\InstagramAccounts\Tables;

use App\Models\InstagramAccount;
use App\Services\Instagram\InstagramSyncService;
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

class InstagramAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('page_name')
                    ->label('Page Facebook')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('followers_count')
                    ->label('Followers')
                    ->sortable(),
                TextColumn::make('follows_count')
                    ->label('Following')
                    ->sortable(),
                TextColumn::make('media_count')
                    ->label('Posts')
                    ->sortable(),
                TextColumn::make('last_synced_at')
                    ->label('Dernière synchro')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Statut')
                    ->options([
                        1 => 'Actif',
                        0 => 'Inactif',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('sync')
                        ->label('Synchroniser')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function (InstagramAccount $record): void {
                            app(InstagramSyncService::class)->syncAccount($record);
                        }),
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
