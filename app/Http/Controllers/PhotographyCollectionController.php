<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhotographyCollectionDetailResource;
use App\Http\Resources\PhotographyCollectionResource;
use App\Models\PhotographyCollection;
use OpenApi\Attributes as OA;

class PhotographyCollectionController extends Controller
{
    #[OA\Get(path: '/api/collections', summary: 'Get active photography collections', tags: ['Collections'])]
    #[OA\Response(response: 200, description: 'Get active photography collections')]
    public function index()
    {
        return PhotographyCollectionResource::collection(
            PhotographyCollection::query()
                ->active()
                ->withCount('photographies')
                ->orderBy('name')
                ->get(),
        );
    }

    #[OA\Get(path: '/api/collection/{photographyCollection}', summary: 'Get a photography collection', tags: ['Collections'])]
    #[OA\Parameter(
        name: 'photographyCollection',
        in: 'path',
        required: true,
        description: 'Slug of collection to return',
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Response(response: 200, description: 'Get a photography collection')]
    public function show(PhotographyCollection $photographyCollection)
    {
        $photographyCollection->load([
            'activePhotographies.camera',
        ]);

        return PhotographyCollectionDetailResource::make($photographyCollection);
    }
}
