<?php

namespace App\Filament\Resources\Cameras\Schemas;

use App\Enums\Serie;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CameraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                MarkdownEditor::make('content')
                    ->label('Contenu')
                    ->columnSpanFull(),
                Select::make('serie')
                    ->label('Série')
                    ->options(Serie::class),
                Toggle::make('active')
                    ->label('Actif')
                    ->onColor('success')
                    ->offColor('danger')
                    ->inline(false),
            ]);
    }
}
