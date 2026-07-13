<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryDetailResource;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    #[OA\Get(path: '/api/categories', summary: 'Get active categories', tags: ['Categories'])]
    #[OA\Response(response: 200, description: 'Get active categories')]
    public function index()
    {
        return CategoryResource::collection(Category::active()->get());
    }

    #[OA\Get(path: '/api/category/{category}', summary: 'Get a category', tags: ['Categories'])]
    #[OA\Parameter(
        name: 'category',
        in: 'path',
        required: true,
        description: 'Slug of category to return',
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Response(response: 200, description: 'Get a category')]
    public function show(Category $category)
    {
        return CategoryDetailResource::make($category);
    }
}
