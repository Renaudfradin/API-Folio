<?php

namespace App\Http\Controllers;

use App\Http\Resources\CameraDetailResource;
use App\Http\Resources\CameraResource;
use App\Models\Camera;
use OpenApi\Annotations as OA;

class CameraController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cameras",
     *     summary="Get all cameras",
     *     tags={"Cameras"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Get all cameras"
     *     )
     * )
     */
    public function index()
    {
        return CameraResource::collection(Camera::all());
    }

    /**
     * @OA\Get(
     *     path="/api/camera/{camera}",
     *     summary="Get a camera",
     *     tags={"Cameras"},
     *
     *     @OA\Parameter(
     *         name="camera",
     *         in="path",
     *         required=true,
     *         description="ID of camera to return",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Get a camera"
     *     )
     * )
     */
    public function show(Camera $camera)
    {
        return CameraDetailResource::make($camera);
    }
}
