<?php

return [
    'enabled' => env('LEGACY_API_ENABLED', env('APP_ENV', 'production') !== 'production'),
];
