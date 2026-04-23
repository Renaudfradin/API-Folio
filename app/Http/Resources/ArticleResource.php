<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'image' => Storage::disk('scaleway')->url($this->image),
            'active' => $this->active,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
