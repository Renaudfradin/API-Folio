<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesScalewayUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    use ResolvesScalewayUrl;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->documentImageUrl(),
            'url' => $this->url,
            'url_github' => $this->url_github,
            'stack' => $this->stack,
        ];
    }
}
