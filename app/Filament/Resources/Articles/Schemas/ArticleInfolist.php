<?php

namespace App\Filament\Resources\Articles\Schemas;

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
