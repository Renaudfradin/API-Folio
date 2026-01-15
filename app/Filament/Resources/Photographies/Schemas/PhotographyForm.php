<?php

namespace App\Filament\Resources\Photographies\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\Camera;
use Filament\Schemas\Schema;
use App\Enums\Serie;

class PhotographyForm
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
                DatePicker::make('date')
                    ->label('Date')
                    ->required(),
                Select::make('series')
                    ->options(Serie::class)
                    ->native(false)
                    ->required(),
                TextInput::make('city')
                    ->label('Ville')
                    ->required(),
                Select::make('camera_id')
                    ->label('Caméra')
                    ->options(Camera::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                FileUpload::make('image')
                    ->label('Image')
                    ->disk('scaleway')
                    ->directory('photography')
                    ->image()
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
