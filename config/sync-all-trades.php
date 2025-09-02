<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MT5 Sync Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for syncing all accounts trades from MT5
    |
    */

    // Batch processing settings
    'batch_size' => env('SYNC_ALL_BATCH_SIZE', 3), // Reduced from 5 to handle timeouts
    'max_concurrent_batches' => env('SYNC_ALL_MAX_CONCURRENT', 3), // Reduced from 5
    'delay_between_batches' => env('SYNC_ALL_BATCH_DELAY', 10), // Seconds

    // MT5 connection settings
    'connection_timeout' => env('SYNC_ALL_CONNECTION_TIMEOUT', 300), // 5 minutes
    'max_retries' => env('SYNC_ALL_MAX_RETRIES', 3),
    'retry_delay_base' => env('SYNC_ALL_RETRY_DELAY', 10), // Base seconds for exponential backoff

    // Job queue settings
    'job_timeout' => env('SYNC_ALL_JOB_TIMEOUT', 300), // 5 minutes per job
    'max_exceptions' => env('SYNC_ALL_MAX_EXCEPTIONS', 3),

    // Rate limiting
    'random_delay_min' => env('SYNC_ALL_RANDOM_DELAY_MIN', 2), // Min seconds
    'random_delay_max' => env('SYNC_ALL_RANDOM_DELAY_MAX', 8), // Max seconds
];
