<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MT5 Connection Pool Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MT5 connection pooling to optimize performance
    | and prevent overwhelming the MT5 server with too many connections.
    |
    */

    // Maximum concurrent connections to MT5 server
    'max_connections' => env('MT5_MAX_CONNECTIONS', 8),

    // Health check interval in seconds
    'health_check_interval' => env('MT5_HEALTH_CHECK_INTERVAL', 300),

    // Connection timeout in seconds
    'connection_timeout' => env('MT5_CONNECTION_TIMEOUT', 30),

    // Maximum connection age before forced refresh (seconds)
    'max_connection_age' => env('MT5_MAX_CONNECTION_AGE', 3600),

    // Maximum connection errors before removal
    'max_connection_errors' => env('MT5_MAX_CONNECTION_ERRORS', 3),

    // Enable connection pooling (can be disabled for debugging)
    'enable_pooling' => env('MT5_ENABLE_POOLING', true),

    // Retry configuration
    'max_retries' => env('MT5_MAX_RETRIES', 3),
    'retry_delay' => env('MT5_RETRY_DELAY', 1),

    // Rate limiting
    'requests_per_minute' => env('MT5_REQUESTS_PER_MINUTE', 600),
    'burst_limit' => env('MT5_BURST_LIMIT', 100),

    /*
    |--------------------------------------------------------------------------
    | Production Optimizations
    |--------------------------------------------------------------------------
    |
    | These settings are optimized for production workloads with high
    | concurrency and reliability requirements.
    |
    */

    'production' => [
        'max_connections' => 6, // Conservative limit for production
        'health_check_interval' => 180, // More frequent health checks
        'connection_timeout' => 20, // Shorter timeout for faster failure detection
        'max_connection_age' => 1800, // 30 minutes max age
        'max_connection_errors' => 2, // Less tolerance for errors
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Settings
    |--------------------------------------------------------------------------
    |
    | More aggressive settings for development and testing.
    |
    */

    'development' => [
        'max_connections' => 12,
        'health_check_interval' => 600,
        'connection_timeout' => 45,
        'max_connection_age' => 7200, // 2 hours
        'max_connection_errors' => 5,
    ],
];
