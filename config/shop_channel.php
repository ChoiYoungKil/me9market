<?php

return [
    'payment_driver' => env('SHOP_PAYMENT_DRIVER', in_array(env('APP_ENV'), ['local', 'testing'], true) ? 'mock' : 'disabled'),
    'terms_url' => env('SHOP_TERMS_URL'),
    'privacy_url' => env('SHOP_PRIVACY_URL'),
    'third_party_url' => env('SHOP_THIRD_PARTY_URL'),
    'terms_version' => env('SHOP_TERMS_VERSION'),
    'marketing_url' => env('SHOP_MARKETING_URL'),
    'notification_url' => env('SHOP_NOTIFICATION_URL'),
];
