<?php

namespace App\Filament\Resources\Stacks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StackInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
