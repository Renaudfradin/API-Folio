<?php

namespace App\Filament\Resources\Articles\Tables;

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

class ArticlesTable
{
    use HasRoleBasedVisibility;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
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
