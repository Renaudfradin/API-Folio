<?php

namespace App\Filament\Resources\Photographies\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PhotographyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nom'),
                TextEntry::make('slug')
                    ->label('Slug'),
                TextEntry::make('date')
                    ->date()
                    ->label('Date'),
                TextEntry::make('series')
                    ->label('Série'),
                TextEntry::make('city')
                    ->label('Ville'),
                TextEntry::make('camera.name')
                    ->label('Camera'),
                ImageEntry::make('image')
                    ->label('Image')
                    ->disk('scaleway')
                    ->columnSpanFull(),
            ]);
    }
}
