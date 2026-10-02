<?php

return [
    'enabled' => env('STORYBOARD_TEST_ENABLED', false),
    'front_url' => env('STORYBOARD_FRONT_URL', 'http://front.replyer.co.kr'),
    'admin_url' => env('STORYBOARD_ADMIN_URL', env('APP_URL', 'http://admin.replyer.co.kr')),
    'test_password' => env('STORYBOARD_TEST_PASSWORD'),
];
