<?php

use App\Http\Controllers\CameraController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PhotographyController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'database' => DB::connection('pgsql')->getPdo() ? 'connected' : 'disconnected',
    ]);
});
Route::get('/cameras', [CameraController::class, 'index']);
Route::get('/camera/{camera:slug}', [CameraController::class, 'show']);
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/project/{project:slug}', [ProjectController::class, 'show']);
Route::get('/photographies', [PhotographyController::class, 'index']);
Route::get('/photography/{photography:slug}', [PhotographyController::class, 'show']);
Route::get('/experiences', [ExperienceController::class, 'index']);
