<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Article;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;

class ArticleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('title')
                    ->label('Titre'),
                TextEntry::make('slug')
                    ->label('Slug'),
                TextEntry::make('category.name')
                    ->label('Catégorie'),
                RepeatableEntry::make('content')
                    ->label('Contenu')
                    ->schema([
                        TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'heading' => 'Titre',
                                'paragraph' => 'Paragraphe',
                                'image' => 'Image',
                                'file' => 'Fichier',
                                'text' => 'Texte',
                                default => $state ?? '-',
                            }),
                        TextEntry::make('data.level')
                            ->label('Niveau')
                            ->visible(fn (Get $get): bool => $get('type') === 'heading')
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'h2' => 'Titre 2',
                                'h3' => 'Titre 3',
                                'h4' => 'Titre 4',
                                default => $state ?? '-',
                            }),
                        TextEntry::make('data.content')
                            ->label('Contenu')
                            ->weight(fn (Get $get): ?FontWeight => $get('type') === 'heading'
                                ? FontWeight::Bold
                                : null)
                            ->html(fn (Get $get): bool => $get('type') === 'paragraph')
                            ->prose(fn (Get $get): bool => $get('type') === 'paragraph')
                            ->visible(fn (Get $get): bool => in_array($get('type'), ['heading', 'paragraph', 'text'], true))
                            ->columnSpanFull(),
                        ImageEntry::make('image_url')
                            ->label('Image')
                            ->disk('scaleway')
                            ->state(fn (Get $get): mixed => $get('data.url'))
                            ->visible(fn (Get $get): bool => $get('type') === 'image')
                            ->columnSpanFull(),
                        TextEntry::make('data.alt')
                            ->label('Texte alternatif')
                            ->visible(fn (Get $get): bool => $get('type') === 'image'),
                        TextEntry::make('data.label')
                            ->label('Libellé')
                            ->visible(fn (Get $get): bool => $get('type') === 'file'),
                        TextEntry::make('file_url')
                            ->label('Fichier')
                            ->state(fn (Get $get): mixed => $get('data.url'))
                            ->visible(fn (Get $get): bool => $get('type') === 'file')
                            ->url(fn (?string $state): ?string => filled($state)
                                ? Storage::disk('scaleway')->url($state)
                                : null)
                            ->openUrlInNewTab()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                ImageEntry::make('image')
                    ->label('Image de l\'article')
                    ->disk('scaleway')
                    ->placeholder('-')
                    ->columnSpan(2),
                TextEntry::make('views_count')
                    ->label('Vues')
                    ->state(fn (Article $record): int => (int) $record->readingStats->sum('views_count')),
                TextEntry::make('reads_count')
                    ->label('Lectures')
                    ->state(fn (Article $record): int => (int) $record->readingStats->sum('reads_count')),
                TextEntry::make('last_viewed_at')
                    ->label('Dernière vue')
                    ->state(fn (Article $record) => $record->readingStats->max('last_viewed_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_read_at')
                    ->label('Dernière lecture')
                    ->state(fn (Article $record) => $record->readingStats->max('last_read_at'))
                    ->dateTime()
                    ->placeholder('-'),
                RepeatableEntry::make('reading_stats')
                    ->label('Lectures par langue')
                    ->state(fn (Article $record): array => $record->readingStats
                        ->map(fn ($stat): array => [
                            'locale' => Article::LOCALE_LABELS[$stat->locale] ?? $stat->locale,
                            'views_count' => $stat->views_count,
                            'reads_count' => $stat->reads_count,
                            'last_viewed_at' => $stat->last_viewed_at,
                            'last_read_at' => $stat->last_read_at,
                        ])
                        ->all())
                    ->schema([
                        TextEntry::make('locale')
                            ->label('Langue'),
                        TextEntry::make('views_count')
                            ->label('Vues'),
                        TextEntry::make('reads_count')
                            ->label('Lectures'),
                        TextEntry::make('last_viewed_at')
                            ->label('Dernière vue')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('last_read_at')
                            ->label('Dernière lecture')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->visible(fn (Article $record): bool => $record->readingStats->isNotEmpty())
                    ->columns(2)
                    ->columnSpanFull(),
                IconEntry::make('active')
                    ->label('Actif')
                    ->boolean(),
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
