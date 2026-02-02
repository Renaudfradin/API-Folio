<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

class HomeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/",
     *     summary="Get home",
     *     tags={"Home"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Get home"
     *     )
     * )
     */
    public function index()
    {
        return response()->json([
            'message' => 'Hello World',
        ]);
    }
}
