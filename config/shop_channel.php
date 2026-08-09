<?php

$isNonProduction = env('APP_ENV', 'production') !== 'production';

return [
    'seed_demo_data' => env('SHOP_CHANNEL_SEED_DEMO_DATA', $isNonProduction),
    'show_demo_credentials' => env('SHOP_CHANNEL_SHOW_DEMO_CREDENTIALS', $isNonProduction),
    'storyboard_test_enabled' => env('STORYBOARD_TEST_ENABLED', $isNonProduction),
];
