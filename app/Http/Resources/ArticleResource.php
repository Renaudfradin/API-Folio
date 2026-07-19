<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\TransformsArticleContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    use TransformsArticleContent;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'image' => $this->scalewayUrl($this->image),
            'active' => $this->active,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
