<?php

namespace App\Filament\Resources\Blocks;

use App\Filament\Resources\Blocks\Pages\CreateBlock;
use App\Filament\Resources\Blocks\Pages\EditBlock;
use App\Filament\Resources\Blocks\Pages\ListBlocks;
use App\Filament\Resources\Blocks\Pages\ViewBlock;
use App\Filament\Resources\Blocks\Schemas\BlockForm;
use App\Filament\Resources\Blocks\Schemas\BlockInfolist;
use App\Filament\Resources\Blocks\Tables\BlocksTable;
use App\Models\Block;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BlockResource extends Resource
{
    protected static ?string $model = Block::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Block';

    public static function form(Schema $schema): Schema
    {
        return BlockForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BlockInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlocksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBlocks::route('/'),
            'create' => CreateBlock::route('/create'),
            'view' => ViewBlock::route('/{record}'),
            'edit' => EditBlock::route('/{record}/edit'),
        ];
    }
}
