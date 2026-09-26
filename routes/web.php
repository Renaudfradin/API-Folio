<?php

use App\Http\Controllers\InstagramOAuthController;
use App\Http\Controllers\LinkedInAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });
});

Route::middleware('auth')->group(function (): void {
    Route::get('/instagram/connect', [InstagramOAuthController::class, 'redirect'])
        ->name('instagram.oauth.redirect');
});

Route::get('/instagram/callback', [InstagramOAuthController::class, 'callback'])
    ->name('instagram.oauth.callback');

Route::middleware('auth')->group(function (): void {
    Route::get('/linkedin/connect', [LinkedInAuthController::class, 'redirect'])
        ->name('linkedin.oauth.redirect');
    Route::get('/linkedin/redirect', [LinkedInAuthController::class, 'redirect'])
        ->name('linkedin.redirect');
});

Route::get('/linkedin/callback', [LinkedInAuthController::class, 'callback'])
    ->name('linkedin.oauth.callback');
