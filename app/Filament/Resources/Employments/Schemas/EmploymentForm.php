<?php

namespace App\Filament\Resources\Employments\Schemas;

use App\Enums\Platform;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmploymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                DatePicker::make('date')
                    ->required(),
                Select::make('platform')
                    ->options(Platform::class)
                    ->required(),
                TextInput::make('location')
                    ->required(),
                TextInput::make('link_job')
                    ->prefix('https://')
                    ->required(),
                Select::make('responce')
                    ->options([
                        'yes' => 'Oui',
                        'no' => 'Non',
                        'pending' => 'En attente',
                    ]),
                DatePicker::make('response_date'),
                Textarea::make('notes')
                    ->rows(9)
                    ->columnSpanFull(),
            ]);
    }
}
