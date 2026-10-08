<?php

namespace App\Filament\Resources\PhotographyCollections\RelationManagers;

use App\Filament\Resources\PhotographyCollections\Tables\AttachPhotographyTable;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PhotographiesRelationManager extends RelationManager
{
    protected static string $relationship = 'photographies';

    protected static ?string $title = 'Photographies';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->disk('scaleway'),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
            ])
            ->reorderable('position')
            ->headerActions([
                AttachAction::make()
                    ->label('Attacher')
                    ->modalHeading('Attacher une photographie')
                    ->tableSelect(AttachPhotographyTable::class),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ]);
    }
}
