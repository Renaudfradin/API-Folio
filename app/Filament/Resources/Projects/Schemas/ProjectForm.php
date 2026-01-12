<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Models\Stack;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('description')
                    ->required(),

                Select::make('stack')
                    ->required()
                    ->options(fn () => Stack::all()->pluck('name', 'id'))
                    ->multiple()
                    ->preload()
                    ->searchable(),
                FileUpload::make('image')
                    ->image()
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('url')
                    ->url()
                    ->prefix('https://'),
                TextInput::make('url_github')
                    ->url()
                    ->prefix('https://'),

            ]);
    }
}
