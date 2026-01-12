<?php

namespace App\Filament\Resources\Photographies\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PhotographyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                DatePicker::make('date')
                    ->required(),
                TextInput::make('series')
                    ->required(),
                TextInput::make('city')
                    ->required(),
                FileUpload::make('image')
                    ->image()
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
