<?php

namespace App\Http\Resources\Concerns;

use Illuminate\Support\Facades\Storage;

trait ResolvesScalewayUrl
{
    protected function scalewayUrl(?string $path): ?string
    {
        return filled($path) ? Storage::disk('scaleway')->url($path) : null;
    }

    protected function documentImageUrl(): ?string
    {
        return $this->scalewayUrl($this->documents->first()?->image);
    }
}
