<?php

use App\Http\Controllers\InstagramOAuthController;
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

Route::middleware('auth')->group(function (): void {
    Route::get('/instagram/connect', [InstagramOAuthController::class, 'redirect'])
        ->name('instagram.oauth.redirect');

    Route::get('/instagram/callback', [InstagramOAuthController::class, 'callback'])
        ->name('instagram.oauth.callback');
});
