<?php

namespace App\Filament\Resources\InstagramMedia\Tables;

use App\Models\InstagramMedia;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class InstagramMediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('timestamp', 'desc')
            ->groups([
                Group::make('timestamp')
                    ->label('Date de publication')
                    ->date(),
            ])
            ->defaultGroup('timestamp')
            ->columns([
                ImageColumn::make('preview_url')
                    ->label('Aperçu')
                    ->checkFileExistence(false)
                    ->square(),
                TextColumn::make('account.username')
                    ->label('Compte')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('media_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('media_product_type')
                    ->label('Produit')
                    ->badge()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                TextColumn::make('insights_reach')
                    ->label('Portée')
                    ->state(fn (InstagramMedia $record): mixed => $record->insight('reach'))
                    ->placeholder('-')
                    ->sortable(false),
                TextColumn::make('insights_saved')
                    ->label('Enregistrements')
                    ->state(fn (InstagramMedia $record): mixed => $record->insight('saved'))
                    ->placeholder('-')
                    ->sortable(false),
                TextColumn::make('engagement_rate')
                    ->label('Eng. rate')
                    ->formatStateUsing(fn (?float $state): string => $state !== null ? $state.' %' : '-')
                    ->sortable(false),
                TextColumn::make('timestamp')
                    ->label('Publié le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    Action::make('openInstagram')
                        ->label('Voir sur Instagram')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (InstagramMedia $record): ?string => $record->permalink)
                        ->openUrlInNewTab()
                        ->visible(fn (InstagramMedia $record): bool => filled($record->permalink)),
                ]),
            ]);
    }
}
