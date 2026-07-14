<?php

namespace App\Filament\Resources\InstagramMedia\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InstagramMediaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('account.username')
                    ->label('Compte'),
                TextEntry::make('media_type')
                    ->label('Type'),
                TextEntry::make('media_product_type')
                    ->label('Produit'),
                TextEntry::make('caption')
                    ->label('Légende')
                    ->columnSpanFull(),
                TextEntry::make('like_count')
                    ->label('Likes'),
                TextEntry::make('comments_count')
                    ->label('Commentaires'),
                TextEntry::make('view_count')
                    ->label('Vues'),
                TextEntry::make('timestamp')
                    ->label('Publié le')
                    ->dateTime(),
                TextEntry::make('permalink')
                    ->label('Lien')
                    ->url()
                    ->openUrlInNewTab()
                    ->columnSpanFull(),
            ]);
    }
}
