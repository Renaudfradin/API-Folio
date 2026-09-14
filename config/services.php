<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'github' => [
        'token' => env('GITHUB_TOKEN'),
        'username' => env('GITHUB_USERNAME'),
        'base_url' => env('GITHUB_BASE_URL', 'https://api.github.com'),
        'cache_ttl' => (int) env('GITHUB_CACHE_TTL', 600),
    ],

    'search_console' => [
        'credentials_path' => ($path = env('GOOGLE_SERVICE_ACCOUNT_CREDENTIALS'))
            ? (str_starts_with($path, '/') ? $path : base_path($path))
            : null,
        'credentials_json' => env('GOOGLE_SERVICE_ACCOUNT_JSON'),
        'cache_ttl' => (int) env('SEARCH_CONSOLE_CACHE_TTL', 3600),
        'sites' => [
            'renaudfradin' => [
                'property' => env('SEARCH_CONSOLE_RENAUDFRADIN_PROPERTY'),
            ],
            'renaudfradinphoto' => [
                'property' => env('SEARCH_CONSOLE_RENAUDFRADINPHOTO_PROPERTY'),
            ],
        ],
    ],

    'instagram' => [
        // Instagram App ID / secret (Business login settings), pas l’App ID Facebook général.
        'client_id' => env('INSTAGRAM_APP_ID', env('INSTAGRAM_CLIENT_ID')),
        'client_secret' => env('INSTAGRAM_APP_SECRET', env('INSTAGRAM_CLIENT_SECRET')),
        'redirect_uri' => env('INSTAGRAM_REDIRECT_URI'),
        'graph_version' => env('INSTAGRAM_GRAPH_VERSION', 'v25.0'),
        'graph_host' => rtrim(env('INSTAGRAM_GRAPH_HOST', 'https://graph.instagram.com'), '/'),
        'scopes' => array_filter(array_map('trim', explode(',', env('INSTAGRAM_SCOPES', 'instagram_business_basic,instagram_business_manage_insights')))),
    ],

];
