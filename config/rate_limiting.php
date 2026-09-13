<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Limiters registered in AppServiceProvider. Use middleware aliases:
    | throttle:api (60/min), throttle:strict (20/min), throttle:login (5/min).
    |
    */

    'api' => [
        'limit' => 60,
    ],

    'strict' => [
        'limit' => 20,
    ],

    'login' => [
        'limit' => 5,
    ],
];
