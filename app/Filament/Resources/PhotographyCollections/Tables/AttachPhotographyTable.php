<?php

namespace App\Filament\Resources\PhotographyCollections\Tables;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttachPhotographyTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Aperçu')
                    ->disk('scaleway')
                    ->imageHeight(56)
                    ->square(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Ville')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('name');
    }
}
