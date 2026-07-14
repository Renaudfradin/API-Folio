<?php

namespace App\Filament\Resources\InstagramMedia\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstagramMediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('timestamp', 'desc')
            ->columns([
                TextColumn::make('account.username')
                    ->label('Compte')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('media_type')
                    ->label('Type')
                    ->sortable(),
                TextColumn::make('caption')
                    ->label('Légende')
                    ->limit(70)
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('like_count')
                    ->label('Likes')
                    ->sortable(),
                TextColumn::make('comments_count')
                    ->label('Commentaires')
                    ->sortable(),
                TextColumn::make('view_count')
                    ->label('Vues')
                    ->sortable(),
                TextColumn::make('timestamp')
                    ->label('Publié le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                ]),
            ]);
    }
}
