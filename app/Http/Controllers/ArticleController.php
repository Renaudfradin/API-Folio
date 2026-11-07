<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleDetailResource;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Services\Articles\ArticleReadingTracker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class ArticleController extends Controller
{
    #[OA\Get(path: '/api/articles', summary: 'Get active articles', tags: ['Articles'])]
    #[OA\Parameter(
        name: 'lang',
        in: 'query',
        required: false,
        description: 'Locale of the returned title and content. Defaults to fr. Unknown values fall back to fr.',
        schema: new OA\Schema(type: 'string', enum: ['fr', 'en', 'de', 'es', 'ru', 'ky']),
    )]
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
    #[OA\Parameter(
        name: 'lang',
        in: 'query',
        required: false,
        description: 'Locale of the returned title and content. A view is counted for this locale. Defaults to fr.',
        schema: new OA\Schema(type: 'string', enum: ['fr', 'en', 'de', 'es', 'ru', 'ky']),
    )]
    #[OA\Response(response: 200, description: 'Get an article')]
    public function show(Request $request, Article $article, ArticleReadingTracker $tracker)
    {
        $tracker->recordView($article, Article::resolveLocale($request->query('lang')));

        $article->loadMissing('category');

        return ArticleDetailResource::make($article);
    }

    #[OA\Post(path: '/api/article/{article}/read', summary: 'Mark an article as fully read', tags: ['Articles'])]
    #[OA\Parameter(
        name: 'article',
        in: 'path',
        required: true,
        description: 'Slug of the article that was read to the end',
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'lang',
        in: 'query',
        required: false,
        description: 'Locale of the completed read. Defaults to fr.',
        schema: new OA\Schema(type: 'string', enum: ['fr', 'en', 'de', 'es', 'ru', 'ky']),
    )]
    #[OA\Response(response: 204, description: 'Read recorded')]
    public function read(Request $request, Article $article, ArticleReadingTracker $tracker): Response
    {
        $tracker->recordRead($article, Article::resolveLocale($request->query('lang')));

        return response()->noContent();
    }
}
