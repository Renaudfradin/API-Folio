<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\ResolvesScalewayUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CameraResource extends JsonResource
{
    use ResolvesScalewayUrl;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->documentImageUrl(),
            'serie' => $this->serie,
        ];
    }
}
