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
        'checkouturl' => env('PAYISSA_CHECKOUT_URL', 'https://api.payissa.com'),
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

    'veriff' => [
        'api_key' => env('VERIFF_API_KEY', ''),
        'api_secret' => env('VERIFF_API_SECRET', ''),
        'webhook_secret' => env('VERIFF_WEBHOOK_SECRET', ''),
        'base_url' => env('VERIFF_BASE_URL', 'https://stationapi.veriff.com/v1'),
    ],
    'omnisend' => [
        'api_url' => env('OMNISEND_API_URL', 'https://api.omnisend.com/v5'),
        'api_key' => env('OMNISEND_API_KEY'),
    ],
    '1forge' => [
        'api_key' => env('FORGE_API_KEY', ''),
    ],
    'x9' => [
        'base_url' => env('X9_BASE_URL', 'https://webapi.x9trader.com'),
        'access_token' => env('X9_ACCESS_TOKEN', ''),
        'v2_base_url' => env('X9_V2_BASE_URL', ''),
        'v2_access_token' => env('X9_V2_ACCESS_TOKEN', ''),
    ],

    'raga_pay' => [
        'api_url' => env('RAGAPAY_API_URL', 'https://api.ragapay.com/api/v1'),
        'merchant_key' => env('RAGAPAY_MERCHANT_KEY', ''),
        'password' => env('RAGAPAY_PASSWORD', ''),
    ],

    'gohighlevel' => [
        'api_url' => env('GHL_API_URL', 'https://services.leadconnectorhq.com'),
        'api_key' => env('GHL_API_KEY', ''),
        'location_id' => env('GHL_LOCATION_ID', ''),
    ],

    'fxstreet' => [
        'rss_url' => env('FXSTREET_RSS_URL', 'https://www.fxstreet.com/rss/news'),
        'cache_ttl' => (int) env('FXSTREET_CACHE_TTL', 900),
        'rss2json_url' => env('FXSTREET_RSS2JSON_URL', 'https://api.rss2json.com/v1/api.json'),
        'rss2json_api_key' => env('FXSTREET_RSS2JSON_API_KEY', ''),
    ],
    'turnstile' => [
        'enabled' => env('TURNSTILE_ENABLED', false),
        'site_key' => env('TURNSTILE_SITE_KEY', ''),
        'secret_key' => env('TURNSTILE_SECRET_KEY', ''),
    ],
];
