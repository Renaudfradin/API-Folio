<?php

namespace App\Filament\Resources\InstagramMedia;

use App\Filament\Resources\InstagramMedia\Pages\ListInstagramMedia;
use App\Filament\Resources\InstagramMedia\Pages\ViewInstagramMedia;
use App\Filament\Resources\InstagramMedia\Schemas\InstagramMediaInfolist;
use App\Filament\Resources\InstagramMedia\Tables\InstagramMediaTable;
use App\Models\InstagramMedia;
use App\Traits\HasRoleBasedVisibility;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class InstagramMediaResource extends Resource
{
    use HasRoleBasedVisibility;

    protected static ?string $model = InstagramMedia::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|\UnitEnum|null $navigationGroup = 'Social';

    protected static ?string $recordTitleAttribute = 'media_id';

    public static function canViewAny(): bool
    {
        return self::isCurrentUserAdmin();
    }

    public static function getNavigationLabel(): string
    {
        return 'Instagram posts';
    }

    public static function getModelLabel(): string
    {
        return 'Post Instagram';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Posts Instagram';
    }

    public static function infolist(Schema $schema): Schema
    {
        return InstagramMediaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstagramMediaTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstagramMedia::route('/'),
            'view' => ViewInstagramMedia::route('/{record}'),
        ];
    }
}
