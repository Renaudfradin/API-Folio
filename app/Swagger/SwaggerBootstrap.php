<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/health",
 *     summary="Healthcheck",
 *     tags={"Health"},
 *     @OA\Response(
 *         response=200,
 *         description="OK"
 *     )
 * )
 */
class SwaggerBootstrap {}
