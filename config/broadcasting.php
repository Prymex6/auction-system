<?php

return [
    'mode' => env('PUSHER_MODE', 'http'),
    'auth_key' => env('PUSHER_APP_KEY'),
    'auth_secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'host' => env('PUSHER_HOST', 'api-'.env('PUSHER_CLUSTER', 'mt1').'.pusher.com'),
        'port' => env('PUSHER_PORT', 443),
        'cluster' => env('PUSHER_CLUSTER', 'mt1'),
    ],
    // Alternative: Use Reverb for local development
    'reverb' => [
        'host' => env('REVERB_HOST', 'localhost'),
        'port' => env('REVERB_PORT', 8080),
        'scheme' => env('REVERB_SCHEME', 'http'),
    ],
];
