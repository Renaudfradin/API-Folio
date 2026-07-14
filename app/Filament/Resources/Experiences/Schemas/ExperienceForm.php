<?php

namespace App\Filament\Resources\Experiences\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ExperienceForm
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
                Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('start_date')
                    ->label('Date de début')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Date de fin'),
                Select::make('type')
                    ->label('Type')
                    ->required()
                    ->options([
                        'stage' => 'Stage',
                        'alternance' => 'Alternance',
                        'cdi' => 'CDI',
                    ]),
                TextInput::make('company')
                    ->label('Entreprise'),
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
                Toggle::make('active')
                    ->label('Actif')
                    ->onColor('success')
                    ->offColor('danger')
                    ->inline(false),
            ]);
    }
}
