<?php

namespace App\Filament\Resources\LinkedInProfileStats\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class LinkedInProfileStatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Utilisateur')
                    ->options(User::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('metric_key')
                    ->label('Clé')
                    ->required(),
                TextInput::make('metric_label')
                    ->label('Libellé')
                    ->required(),
                TextInput::make('value')
                    ->label('Valeur numérique')
                    ->numeric(),
                TextInput::make('value_text')
                    ->label('Valeur texte'),
                DatePicker::make('period_start')
                    ->label('Début de période'),
                DatePicker::make('period_end')
                    ->label('Fin de période'),
                Select::make('source')
                    ->label('Source')
                    ->options([
                        'manual' => 'Manuel',
                        'linkedin' => 'LinkedIn',
                    ])
                    ->default('manual')
                    ->required(),
                DateTimePicker::make('synced_at')
                    ->label('Synchronisé le'),
                Textarea::make('payload')
                    ->label('Payload JSON')
                    ->rows(8)
                    ->columnSpanFull(),
            ]);
    }
}
