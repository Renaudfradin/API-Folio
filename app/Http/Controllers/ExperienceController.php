<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use OpenApi\Annotations as OA;

class ExperienceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/experiences",
     *     summary="Get all experiences",
     *     tags={"Experiences"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Get all experiences"
     *     )
     * )
     */
    public function index()
    {
        return ExperienceResource::collection(Experience::all());
    }

    /**
     * @OA\Get(
     *     path="/api/experience/{experience}",
     *     summary="Get an experience",
     *     tags={"Experiences"},
     *
     *     @OA\Parameter(
     *         name="experience",
     *         in="path",
     *         required=true,
     *         description="ID of experience to return",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Get an experience"
     *     )
     * )
     */
    public function show(Experience $experience)
    {
        return ExperienceResource::make($experience);
    }
}
