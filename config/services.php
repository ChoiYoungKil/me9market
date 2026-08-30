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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'endpoint' => env('SMS_ENDPOINT', 'https://www.fast2sms.com/dev/bulk'),
        'authorization' => env('SMS_AUTHORIZATION'),
        'sender_id' => env('SMS_SENDER_ID'),
        'username' => env('SMS_USERNAME'),
        'password' => env('SMS_PASSWORD'),
        'otp_ttl_minutes' => (int) env('SMS_OTP_TTL_MINUTES', 5),
        'otp_max_attempts' => (int) env('SMS_OTP_MAX_ATTEMPTS', 5),
    ],

];
