<?php

namespace App\Filament\Resources\Cameras\Schemas;

use App\Enums\Serie;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                RichEditor::make('content')
                    ->label('Contenu')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->label('Image')
                    ->disk('scaleway')
                    ->directory('camera')
                    ->image()
                    ->columnSpanFull(),
                Select::make('serie')
                    ->label('Série')
                    ->options(Serie::class),
            ]);
    }
}
