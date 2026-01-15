<?php

namespace App\Filament\Resources\Blocks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BlockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Titre'),
                TextEntry::make('content')
                    ->label('Contenu')
                    ->columnSpanFull(),
            ]);
    }
}
