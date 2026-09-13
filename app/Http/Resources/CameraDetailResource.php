<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesScalewayUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CameraDetailResource extends JsonResource
{
    use ResolvesScalewayUrl;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'content' => $this->content,
            'image' => $this->documentImageUrl(),
            'serie' => $this->serie,
        ];
    }
}
