<?php

namespace App\Filament\Resources\PhotographyCollections;

use App\Filament\Resources\PhotographyCollections\Pages\CreatePhotographyCollection;
use App\Filament\Resources\PhotographyCollections\Pages\EditPhotographyCollection;
use App\Filament\Resources\PhotographyCollections\Pages\ListPhotographyCollections;
use App\Filament\Resources\PhotographyCollections\Pages\ViewPhotographyCollection;
use App\Filament\Resources\PhotographyCollections\RelationManagers\PhotographiesRelationManager;
use App\Filament\Resources\PhotographyCollections\Schemas\PhotographyCollectionForm;
use App\Filament\Resources\PhotographyCollections\Schemas\PhotographyCollectionInfolist;
use App\Filament\Resources\PhotographyCollections\Tables\PhotographyCollectionsTable;
use App\Models\PhotographyCollection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PhotographyCollectionResource extends Resource
{
    protected static ?string $model = PhotographyCollection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\UnitEnum|null $navigationGroup = 'renaudfradinphoto';

    public static function getNavigationLabel(): string
    {
        return __('Collections');
    }

    public static function getModelLabel(): string
    {
        return __('Collection');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Collections');
    }

    public static function form(Schema $schema): Schema
    {
        return PhotographyCollectionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PhotographyCollectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PhotographyCollectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PhotographiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPhotographyCollections::route('/'),
            'create' => CreatePhotographyCollection::route('/create'),
            'view' => ViewPhotographyCollection::route('/{record}'),
            'edit' => EditPhotographyCollection::route('/{record}/edit'),
        ];
    }
}
