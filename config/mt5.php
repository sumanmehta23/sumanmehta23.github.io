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
    'max_connections' => env('MT5_MAX_CONNECTIONS', 3),

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

    // Enable Redis coordination for cross-process connection sharing
    'use_redis_coordination' => env('MT5_USE_REDIS_COORDINATION', true),

    // Maximum connections globally (across all processes/servers)
    'max_global_connections' => env('MT5_MAX_GLOBAL_CONNECTIONS', 20),

    // Maximum connections per process
    'max_local_connections' => env('MT5_MAX_LOCAL_CONNECTIONS', 5),

    /*
    |--------------------------------------------------------------------------
    | MT5 REST API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the modern MT5 REST API service which provides
    | better performance and connection management than the legacy protocol.
    |
    */

    // REST API Connection Pool Settings
    'rest_api_max_connections' => env('MT5_REST_API_MAX_CONNECTIONS', 5),
    'rest_api_timeout' => env('MT5_REST_API_TIMEOUT', 30),
    'rest_api_connect_timeout' => env('MT5_REST_API_CONNECT_TIMEOUT', 10),

    // REST API server port (usually 443 for HTTPS)
    'rest_port' => env('MT5_REST_PORT', 443),

    // Retry configuration
    'max_retries' => env('MT5_MAX_RETRIES', 3),
    'retry_delay' => env('MT5_RETRY_DELAY', 1),

    // Rate limiting
    'requests_per_minute' => env('MT5_REQUESTS_PER_MINUTE', 600),
    'burst_limit' => env('MT5_BURST_LIMIT', 100),

    // Redis coordination configuration
    'redis' => [
        'connection' => env('MT5_REDIS_CONNECTION', 'default'),
        'key_prefix' => env('MT5_REDIS_KEY_PREFIX', 'mt5_connections'),
        'lock_timeout' => env('MT5_REDIS_LOCK_TIMEOUT', 10),
        'process_cleanup_interval' => env('MT5_PROCESS_CLEANUP_INTERVAL', 7200), // 2 hours
    ],

    // Coordination strategy
    'coordination' => [
        'wait_timeout' => env('MT5_WAIT_TIMEOUT', 30), // seconds to wait for connection
        'retry_interval' => env('MT5_RETRY_INTERVAL', 100), // milliseconds between retries
    ],

    // Monitoring and statistics
    'monitoring' => [
        'enable_detailed_logging' => env('MT5_DETAILED_LOGGING', false),
        'stats_cache_ttl' => env('MT5_STATS_CACHE_TTL', 60), // seconds
    ],

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
