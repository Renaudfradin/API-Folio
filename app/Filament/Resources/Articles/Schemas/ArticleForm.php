<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Article;
use App\Models\Category;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Placeholder::make('translation_progress')
                    ->hiddenLabel()
                    ->content('Le service choisi traduit l’article à partir du français. Reste sur cette page : le résultat s’affichera ici.')
                    ->visible(fn (mixed $livewire): bool => is_object($livewire) && filled($livewire->translationAgentId ?? null))
                    ->columnSpanFull(),
                TextInput::make('title')
                    ->label('Titre')
                    ->live(onBlur: true)
                    ->helperText(function (mixed $livewire): ?string {
                        $locale = is_object($livewire) ? ($livewire->activeLocale ?? null) : null;

                        if (! is_string($locale) || $locale === Article::DEFAULT_LOCALE) {
                            return null;
                        }

                        return 'Les blocs français restent affichés. Le bouton Traduire les passe dans les autres langues.';
                    })
                    ->afterStateUpdated(function (Set $set, ?string $state, mixed $livewire): void {
                        $locale = is_object($livewire) ? ($livewire->activeLocale ?? null) : null;

                        if ($locale !== Article::DEFAULT_LOCALE) {
                            return;
                        }

                        $set('slug', Str::slug((string) $state));
                    })
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                Select::make('category_id')
                    ->label('Catégorie')
                    ->options(Category::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Builder::make('content')
                    ->label('Contenu')
                    ->blocks([
                        Block::make('heading')
                            ->label('Titre')
                            ->icon(Heroicon::OutlinedH1)
                            ->schema([
                                TextInput::make('content')
                                    ->label('Titre')
                                    ->required(),
                                Select::make('level')
                                    ->label('Niveau')
                                    ->options([
                                        'h2' => 'Titre 2',
                                        'h3' => 'Titre 3',
                                        'h4' => 'Titre 4',
                                    ])
                                    ->required(),
                            ])
                            ->columns(2),
                        Block::make('paragraph')
                            ->label('Paragraphe')
                            ->icon(Heroicon::OutlinedBars3BottomLeft)
                            ->schema([
                                RichEditor::make('content')
                                    ->label('Paragraphe')
                                    ->required()
                                    ->json(false)
                                    ->fileAttachments(false)
                                    ->toolbarButtons([
                                        ['bold', 'italic', 'underline', 'link'],
                                        ['h2', 'h3'],
                                        ['bulletList', 'orderedList', 'blockquote'],
                                    ])
                                    ->columnSpanFull(),
                            ]),
                        Block::make('image')
                            ->label('Image')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                FileUpload::make('url')
                                    ->label('Image')
                                    ->disk('scaleway')
                                    ->directory('articles')
                                    ->visibility('public')
                                    ->image()
                                    ->required(),
                                TextInput::make('alt')
                                    ->label('Texte alternatif')
                                    ->required(),
                            ]),
                        Block::make('file')
                            ->label('Fichier')
                            ->icon(Heroicon::OutlinedPaperClip)
                            ->schema([
                                FileUpload::make('url')
                                    ->label('Fichier')
                                    ->disk('scaleway')
                                    ->directory('articles/files')
                                    ->visibility('public')
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'application/zip',
                                        'application/x-zip-compressed',
                                    ])
                                    ->maxSize(10240)
                                    ->required(),
                                TextInput::make('label')
                                    ->label('Libellé')
                                    ->required(),
                            ]),
                        Block::make('text')
                            ->label('Texte')
                            ->icon(Heroicon::OutlinedPencil)
                            ->schema([
                                TextInput::make('content')
                                    ->label('Texte')
                                    ->required(),
                            ]),
                    ])
                    ->blockIcons()
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->label('Image de l\'article')
                    ->disk('scaleway')
                    ->directory('articles')
                    ->visibility('public')
                    ->image()
                    ->columnSpanFull(),
                Toggle::make('active')
                    ->label('Actif')
                    ->onColor('success')
                    ->offColor('danger')
                    ->inline(false)
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
