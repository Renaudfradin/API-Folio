<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the rate limiters that will be applied to all
    | requests to your application. You can configure different limiters
    | for different routes or groups of routes.
    |
    */

    'api' => [
        'limit' => 60, // 60 requests per minute
        'decay_minutes' => 1,
    ],

    'strict' => [
        'limit' => 20, // 20 requests per minute
        'decay_minutes' => 1,
    ],

    'loose' => [
        'limit' => 1000, // 1000 requests per minute
        'decay_minutes' => 1,
    ],

    // Configure global rate limiters
    'limiter' => function (Request $request) {
        return RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    },
];
