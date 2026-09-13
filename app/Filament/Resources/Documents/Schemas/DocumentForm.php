<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Models\Camera;
use App\Models\Document;
use App\Models\Photography;
use App\Models\Project;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image')
                    ->disk('scaleway')
                    ->image()
                    ->maxSize(10240)
                    ->required(),
                Select::make('documentable_type')
                    ->label('Type')
                    ->options([
                        Project::class => 'Projet',
                        Camera::class => 'Appareil',
                        Photography::class => 'Photographie',
                    ])
                    ->required()
                    ->live()
                    ->rules([Rule::in(Document::ALLOWED_MORPH_TYPES)]),
                Select::make('documentable_id')
                    ->label('Enregistrement')
                    ->options(fn (Get $get): array => match ($get('documentable_type')) {
                        Project::class => Project::query()->pluck('name', 'id')->all(),
                        Camera::class => Camera::query()->pluck('name', 'id')->all(),
                        Photography::class => Photography::query()->pluck('name', 'id')->all(),
                        default => [],
                    })
                    ->searchable()
                    ->required()
                    ->disabled(fn (Get $get): bool => blank($get('documentable_type'))),
            ]);
    }
}
