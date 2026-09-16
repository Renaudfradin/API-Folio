<?php

namespace App\Filament\Resources\InstagramMedia\Schemas;

use App\Models\InstagramMedia;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InstagramMediaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Aperçu')
                    ->schema([
                        TextEntry::make('caption')
                            ->label('Légende')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
                Section::make('Médias')
                    ->description('Faites défiler les images et vidéos du post (carrousel inclus).')
                    ->schema([
                        ViewEntry::make('media_slider')
                            ->hiddenLabel()
                            ->view('filament.infolists.instagram-media-slider')
                            ->viewData(fn (InstagramMedia $record): array => [
                                'items' => collect($record->all_media_items)
                                    ->map(fn (array $item): array => [
                                        'preview' => $item['thumbnail_url'] ?? $item['media_url'] ?? null,
                                        'media_url' => $item['media_url'] ?? null,
                                        'media_type' => $item['media_type'] ?? null,
                                    ])
                                    ->filter(fn (array $item): bool => filled($item['preview']) || filled($item['media_url']))
                                    ->values()
                                    ->all(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Commentaires')
                    ->description(function (InstagramMedia $record): string {
                        $syncedCount = count($record->comments ?? []);

                        if ($record->comments_count === 0) {
                            return 'Aucun commentaire sur ce post.';
                        }

                        if ($syncedCount === 0) {
                            return $record->comments_count.' commentaire(s) sur Instagram, mais l’API n’en a renvoyé aucun. Reconnectez le compte (permission commentaires) puis resynchronisez. Consultez storage/logs/laravel.log si le problème persiste.';
                        }

                        return $syncedCount.' commentaire(s) synchronisé(s) sur '.$record->comments_count;
                    })
                    ->schema([
                        RepeatableEntry::make('comments')
                            ->label('Liste')
                            ->schema([
                                TextEntry::make('username')
                                    ->label('Auteur')
                                    ->formatStateUsing(fn (?string $state): string => filled($state) ? '@'.$state : '-'),
                                TextEntry::make('like_count')
                                    ->label('Likes'),
                                TextEntry::make('timestamp')
                                    ->label('Publié le')
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('text')
                                    ->label('Commentaire')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->placeholder('Relancez une synchronisation du compte pour récupérer les commentaires.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Publication')
                    ->schema([
                        TextEntry::make('account.username')
                            ->label('Compte'),
                        TextEntry::make('media_type')
                            ->label('Type')
                            ->badge(),
                        TextEntry::make('media_product_type')
                            ->label('Produit')
                            ->placeholder('-')
                            ->badge(),
                        TextEntry::make('timestamp')
                            ->label('Publié le')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('media_id')
                            ->label('ID média Instagram')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('permalink')
                            ->label('Lien public')
                            ->placeholder('-')
                            ->url(fn (?string $state): ?string => filled($state) ? $state : null)
                            ->openUrlInNewTab()
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Section::make('Statistiques')
                    ->schema([
                        TextEntry::make('like_count')
                            ->label('Likes'),
                        TextEntry::make('comments_count')
                            ->label('Commentaires'),
                        TextEntry::make('view_count')
                            ->label('Vues'),
                        TextEntry::make('insights_impressions')
                            ->label('Impressions')
                            ->state(fn (InstagramMedia $record): mixed => $record->insight('impressions'))
                            ->placeholder('-'),
                        TextEntry::make('insights_reach')
                            ->label('Portée')
                            ->state(fn (InstagramMedia $record): mixed => $record->insight('reach'))
                            ->placeholder('-'),
                        TextEntry::make('insights_engagement')
                            ->label('Engagement (API)')
                            ->state(fn (InstagramMedia $record): mixed => $record->insight('engagement'))
                            ->placeholder('-'),
                        TextEntry::make('insights_saved')
                            ->label('Enregistrements')
                            ->state(fn (InstagramMedia $record): mixed => $record->insight('saved'))
                            ->placeholder('-'),
                        TextEntry::make('engagement_rate')
                            ->label('Taux d’engagement')
                            ->formatStateUsing(fn (?float $state): string => $state !== null ? $state.' %' : '-'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                Section::make('Synchronisation')
                    ->schema([
                        TextEntry::make('synced_at')
                            ->label('Dernière synchro')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
