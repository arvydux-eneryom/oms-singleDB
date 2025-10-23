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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'telegram' => [
        'api_id' => env('TELEGRAM_API_ID'),
        'api_hash' => env('TELEGRAM_API_HASH'),
        'session_dir' => env('TELEGRAM_SESSION_DIR', 'telegram/sessions'),
        'session_expires_days' => env('TELEGRAM_SESSION_EXPIRES_DAYS', 30),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
        'sms_common_url' => env('TWILIO_SMS_COMMON_URL', ''),
        'outgoing_sms_status_callback_url_path' => env('TWILIO_OUTGOING_SMS_STATUS_CALLBACK_URL_PATH', ''),
        'outgoing_sms_status_callback_url' => env('TWILIO_SMS_COMMON_URL', '') . env('TWILIO_OUTGOING_SMS_STATUS_CALLBACK_URL_PATH', ''),
        'incoming_sms_url_path' => env('TWILIO_INCOMING_SMS_URL_PATH', ''),
    ],

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],
];
