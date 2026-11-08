<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\TransformsArticleContent;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    use TransformsArticleContent;

    public function toArray(Request $request): array
    {
        /** @var Article $article */
        $article = $this->resource;
        $locale = $article->translationLocale(Article::resolveLocale($request->query('lang')));

        return [
            'id' => $this->id,
            'title' => $article->translatedTitle($locale),
            'slug' => $this->slug,
            'image' => $this->scalewayUrl($this->image),
            'content' => $this->transformContent($article->translatedContent($locale)),
            'lang' => $locale,
            'available_langs' => $article->availableLocales(),
            'active' => $this->active,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
