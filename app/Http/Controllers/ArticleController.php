<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleDetailResource;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use OpenApi\Attributes as OA;

class ArticleController extends Controller
{
    #[OA\Get(path: '/api/articles', summary: 'Get active articles', tags: ['Articles'])]
    #[OA\Response(response: 200, description: 'Get active articles')]
    public function index()
    {
        return ArticleResource::collection(Article::with('category')->active()->get());
    }

    #[OA\Get(path: '/api/article/{article}', summary: 'Get an article', tags: ['Articles'])]
    #[OA\Parameter(
        name: 'article',
        in: 'path',
        required: true,
        description: 'Slug of article to return',
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Response(response: 200, description: 'Get an article')]
    public function show(Article $article)
    {
        return ArticleDetailResource::make($article);
    }
}
