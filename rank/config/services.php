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

    'razorpay' => [
        'key' => env('RAZORPAY_KEY', 'rzp_test_dummy'),
        'secret' => env('RAZORPAY_SECRET', 'dummy_secret'),
    ],

    'exotel' => [
        'account_sid' => env('EXOTEL_ACCOUNT_SID', 'retailcenter1'),
        'api_key' => env('EXOTEL_API_KEY'),
        'api_token' => env('EXOTEL_API_TOKEN'),
        'base_url' => env('EXOTEL_BASE_URL', 'https://api.exotel.com'),
        'voice_analyze_url' => env('EXOTEL_VOICE_ANALYZE_URL'),
        'voice_analyze_format' => env('EXOTEL_VOICE_ANALYZE_FORMAT', 'form'),
        'voice_analyze_method' => env('EXOTEL_VOICE_ANALYZE_METHOD', 'POST'),
    ],

];
