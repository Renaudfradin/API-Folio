<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProjectDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->documents->map(function ($document) {
                return Storage::disk('scaleway')->url($document->image);
            }),
            'url' => $this->url,
            'url_github' => $this->url_github,
            'stack' => $this->stack,
        ];
    }
}
