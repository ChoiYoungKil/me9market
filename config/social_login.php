<?php

return [
    'google' => [
        'enabled' => (bool) env('GOOGLE_LOGIN_ENABLED', false),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
    ],
    'naver' => [
        'enabled' => (bool) env('NAVER_LOGIN_ENABLED', false),
        'client_id' => env('NAVER_CLIENT_ID'),
        'client_secret' => env('NAVER_CLIENT_SECRET'),
        'redirect_uri' => env('NAVER_REDIRECT_URI'),
    ],
];
