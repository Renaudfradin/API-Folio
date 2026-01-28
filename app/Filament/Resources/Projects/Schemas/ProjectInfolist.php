<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nom'),
                TextEntry::make('slug')
                    ->label('Slug'),
                TextEntry::make('description')
                    ->label('Description'),
                ImageEntry::make('image')
                    ->label('Image')
                    ->disk('scaleway'),
                TextEntry::make('url')
                    ->label('Url')
                    ->placeholder('-'),
                TextEntry::make('url_github')
                    ->label('Url GitHub')
                    ->placeholder('-'),
                TextEntry::make('stack')
                    ->label('Stack')
                    ->numeric(),
            ]);
    }
}
