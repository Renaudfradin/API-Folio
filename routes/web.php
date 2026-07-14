<?php

use App\Http\Controllers\InstagramOAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });
});

Route::middleware('auth')->group(function (): void {
    Route::get('/instagram/connect', [InstagramOAuthController::class, 'redirect'])
        ->name('instagram.oauth.redirect');

    Route::get('/instagram/callback', [InstagramOAuthController::class, 'callback'])
        ->name('instagram.oauth.callback');
});
