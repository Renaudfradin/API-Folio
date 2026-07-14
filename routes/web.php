<?php

use App\Http\Controllers\LinkedInAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/phpinfo', function () {
    return [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
    ];
});

Route::middleware('auth')->group(function () {
    Route::get('/linkedin/redirect', [LinkedInAuthController::class, 'redirect'])
        ->name('linkedin.redirect');

    Route::get('/linkedin/callback', [LinkedInAuthController::class, 'callback'])
        ->name('linkedin.callback');
});
