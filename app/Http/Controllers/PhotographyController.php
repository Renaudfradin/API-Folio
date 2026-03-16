<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhotographyDetailResource;
use App\Http\Resources\PhotographyResource;
use App\Models\Photography;
use OpenApi\Annotations as OA;

class PhotographyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/photographies",
     *     summary="Get active photographies",
     *     tags={"Photographies"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Get active photographies"
     *     )
     * )
     */
    public function index()
    {
        return PhotographyResource::collection(Photography::active()->get());
    }

    /**
     * @OA\Get(
     *     path="/api/photography/{photography}",
     *     summary="Get a photography",
     *     tags={"Photographies"},
     *
     *     @OA\Parameter(
     *         name="photography",
     *         in="path",
     *         required=true,
     *         description="Slug of photography to return",
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Get a photography"
     *     )
     * )
     */
    public function show(Photography $photography)
    {
        return PhotographyDetailResource::make($photography);
    }
}
