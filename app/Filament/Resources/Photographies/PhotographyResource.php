<?php

namespace App\Filament\Resources\Photographies;

use App\Filament\Resources\Photographies\Pages\CreatePhotography;
use App\Filament\Resources\Photographies\Pages\EditPhotography;
use App\Filament\Resources\Photographies\Pages\ListPhotographies;
use App\Filament\Resources\Photographies\Pages\ViewPhotography;
use App\Filament\Resources\Photographies\Schemas\PhotographyForm;
use App\Filament\Resources\Photographies\Schemas\PhotographyInfolist;
use App\Filament\Resources\Photographies\Tables\PhotographiesTable;
use App\Models\Photography;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PhotographyResource extends Resource
{
    protected static ?string $model = Photography::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Photography';

    protected static string|\UnitEnum|null $navigationGroup = 'renaudfradinphoto';

    public static function form(Schema $schema): Schema
    {
        return PhotographyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PhotographyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PhotographiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPhotographies::route('/'),
            'create' => CreatePhotography::route('/create'),
            'view' => ViewPhotography::route('/{record}'),
            'edit' => EditPhotography::route('/{record}/edit'),
        ];
    }
}
