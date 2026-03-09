<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image')
                    ->disk('scaleway')
                    ->image()
                    ->required(),
                TextInput::make('documentable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('documentable_type')
                    ->required(),
            ]);
    }
}
