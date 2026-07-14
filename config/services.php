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

    'instagram' => [
        'client_id' => env('INSTAGRAM_CLIENT_ID', env('META_APP_ID')),
        'client_secret' => env('INSTAGRAM_CLIENT_SECRET', env('META_APP_SECRET')),
        'redirect_uri' => env('INSTAGRAM_REDIRECT_URI'),
        'graph_version' => env('INSTAGRAM_GRAPH_VERSION', 'v25.0'),
        'scopes' => array_filter(array_map('trim', explode(',', env('INSTAGRAM_SCOPES', 'instagram_basic,instagram_manage_insights,pages_show_list,pages_read_engagement')))),
    ],

];
