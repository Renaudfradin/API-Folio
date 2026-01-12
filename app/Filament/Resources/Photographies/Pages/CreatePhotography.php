<?php

namespace App\Filament\Resources\Photographies\Pages;

use App\Filament\Resources\Photographies\PhotographyResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePhotography extends CreateRecord
{
    protected static string $resource = PhotographyResource::class;
}
