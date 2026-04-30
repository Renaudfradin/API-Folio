<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titre')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                Select::make('category_id')
                    ->label('Catégorie')
                    ->options(Category::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Toggle::make('active')
                    ->label('Actif')
                    ->onColor('success')
                    ->offColor('danger')
                    ->inline(false)
                    ->required(),
                RichEditor::make('content')
                    ->label('Contenu')
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                        ['h2', 'h3'],
                        ['alignStart', 'alignCenter', 'alignEnd'],
                        ['blockquote', 'bulletList', 'orderedList'],
                        ['table', 'attachFiles'],
                        ['undo', 'redo'],
                    ])
                    ->required()
                    ->fileAttachmentsDisk('scaleway')
                    ->fileAttachmentsVisibility('public')
                    ->fileAttachmentsDirectory('articles')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->label('Image')
                    ->disk('scaleway')
                    ->directory('articles')
                    ->visibility('public')
                    ->image()
                    ->columnSpanFull(),
            ]);
    }
}
