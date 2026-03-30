<?php

namespace App\Filament\Resources\Photographies\Schemas;

use App\Enums\Serie;
use App\Models\Camera;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PhotographyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
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
                    ->maxSize(10240)
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('active')
                    ->label('Actif')
                    ->onColor('success')
                    ->offColor('danger')
                    ->inline(false),
            ]);
    }
}
