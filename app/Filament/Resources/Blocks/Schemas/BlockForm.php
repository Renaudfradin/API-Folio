<?php

namespace App\Filament\Resources\Blocks\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titre')
                    ->required(),
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
                MarkdownEditor::make('content')
                    ->label('Contenu')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
