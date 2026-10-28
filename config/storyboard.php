<?php

return [
    'enabled' => env('STORYBOARD_TEST_ENABLED', false),
    'front_url' => env('STORYBOARD_FRONT_URL', 'http://front.replyer.co.kr'),
    'admin_url' => env('STORYBOARD_ADMIN_URL', env('APP_URL', 'http://admin.replyer.co.kr')),
    'test_password' => env('STORYBOARD_TEST_PASSWORD'),
    'passwords' => [
        'member' => env('STORYBOARD_MEMBER_PASSWORD') ?: env('STORYBOARD_TEST_PASSWORD'),
        'channel' => env('STORYBOARD_CHANNEL_MEMBER_PASSWORD') ?: env('STORYBOARD_TEST_PASSWORD'),
        'distributor' => env('STORYBOARD_DISTRIBUTOR_PASSWORD') ?: env('STORYBOARD_TEST_PASSWORD'),
        'superadmin' => env('STORYBOARD_ADMIN_PASSWORD') ?: env('STORYBOARD_TEST_PASSWORD'),
    ],
];
