<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\Stack;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                    ->label('Nom')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
                TextInput::make('description')
                    ->label('Description')
                    ->required(),
                Select::make('stack')
                    ->label('Stack')
                    ->required()
                    ->options(Stack::class)
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Select::make('source')
                    ->label('Source')
                    ->options([
                        'manual' => 'Manuel',
                        'linkedin' => 'LinkedIn',
                    ])
                    ->default('manual')
                    ->required(),
                TextInput::make('external_id')
                    ->label('External ID')
                    ->placeholder('Identifiant LinkedIn'),
                TextInput::make('linkedin_url')
                    ->label('LinkedIn URL')
                    ->url()
                    ->prefix('https://'),
                DateTimePicker::make('synced_at')
                    ->label('Synchronisé le'),
                TextInput::make('url')
                    ->label('Url')
                    ->prefix('https://'),
                TextInput::make('url_github')
                    ->label('Url GitHub')
                    ->url()
                    ->prefix('https://'),
                Toggle::make('active')
                    ->label('Actif')
                    ->onColor('success')
                    ->offColor('danger')
                    ->inline(false),
            ]);
    }
}
