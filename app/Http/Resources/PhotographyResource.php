<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PhotographyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => Storage::disk('scaleway')->url($this->image),
            'date' => $this->date,
            'series' => $this->series,
            'city' => $this->city,
            'camera_id' => $this->camera_id,
        ];
    }
}
