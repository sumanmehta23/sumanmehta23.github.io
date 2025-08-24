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
    'ip_geolocation' => [
        'url' => env('IP_GEOLOCATION_URL'),
        'key' => env('IP_GEOLOCATION_API_KEY')
    ],
    'cryptochill' => [
        'callbacktoken' => env('CRYPTOCHILL_CALLBACK_TOKEN'),
        'key' => env('CRYPTOCHILL_API_KEY'),
        'secret' => env('CRYPTOCHILL_API_SECRET'),
        'profileid' => env('CRYPTOCHILL_PROFILE_ID'),
        'accountid' => env('CRYPTOCHILL_ACCOUNT_ID')
    ],
    'payissa' => [
        'url' => env('PAYISSA_URL', 'https://api.payissa.com'),
        'checkouturl' => env('PAYISSA_CHECKOUT_URL', 'https://multi.payissa.com/'),
        'address' => env('PAYISSA_WALLET_ADDRESS'),
        'valid_coins' => json_decode(env('PAYISSA_VALID_COINS', '[]'), true),
        'payment_issue_email' => env('PAYISSA_PAYMENT_ISSUE_EMAIL', ''),
    ],
    'brevo' => [
        'url' => env('BREVO_URL', 'https://api.brevo.com/v3/'),
        'api_key' => env('BREVO_API_KEY')
    ],
    'pamm' => [
        'url' => env('PAMM_URL'),
    ],
    'sales' => [
        'promotion' => env('SALES_PROMOTION', false),
        'promotiontext' => env('SALES_PROMOTION_TEXT'),
    ],
    'sumsub' => [
        'api_token' => env('SUMSUB_API_TOKEN', ''),
        'api_secret' => env('SUMSUB_API_SECRET', ''),
        'webhook_secret' => env('SUMSUB_WEBHOOK_SECRET', ''),
        'clientId' => env('SUMSUB_CLIENT_ID', ''),
    ],
    'klaviyo' => [
        'list_ids' => json_decode(env('KLAVIYO_LIST_IDS', '[]'), true),
    ],
    '1forge' => [
        'api_key' => env('FORGE_API_KEY', ''),
    ],
    'x9' => [
        'base_url' => env('X9_BASE_URL', ''),
        'access_token' => env('X9_ACCESS_TOKEN', ''),
    ]
];
