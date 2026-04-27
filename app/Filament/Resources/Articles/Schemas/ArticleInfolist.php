<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ArticleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Titre'),
                TextEntry::make('slug')
                    ->label('Slug'),
                IconEntry::make('active')
                    ->boolean(),
                TextEntry::make('category.name')
                    ->label('Catégorie'),
                TextEntry::make('content')
                    ->label('Contenu')
                    ->markdown()
                    ->columnSpanFull(),
                ImageEntry::make('image')
                    ->label('Image')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
