<?php

namespace App\Filament\Resources\Cameras\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CameraInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nom'),
                TextEntry::make('slug')
                    ->label('Slug'),
                TextEntry::make('content')
                    ->label('Content')
                    ->columnSpanFull(),
                TextEntry::make('serie')
                    ->label('Serie'),
                IconEntry::make('active')
                    ->label('Actif')
                    ->boolean(),
            ]);
    }
}
