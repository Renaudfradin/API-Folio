<?php

namespace App\Filament\Resources\Stacks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
            ]);
    }
}
