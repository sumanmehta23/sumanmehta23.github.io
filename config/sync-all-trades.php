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

    /*
    |--------------------------------------------------------------------------
    | Balance Sync Configuration
    |--------------------------------------------------------------------------
    */
    'balance_sync' => [
        'interval_minutes' => env('BALANCE_SYNC_INTERVAL', 20), // Minutes between daemon cycles
        'batch_size' => env('BALANCE_SYNC_BATCH_SIZE', 20), // Accounts per batch job
    ],

    /*
    |--------------------------------------------------------------------------
    | Priority Sync Configuration (LEGACY — use deals_sync instead)
    |--------------------------------------------------------------------------
    */
    'priority_sync' => [
        'batch_size' => env('PRIORITY_SYNC_BATCH_SIZE', 10),
        'max_concurrent' => env('PRIORITY_SYNC_MAX_CONCURRENT', 5),
        'cycle_delay' => env('PRIORITY_SYNC_CYCLE_DELAY', 30), // Seconds
        'min_sync_interval' => env('PRIORITY_SYNC_MIN_INTERVAL', 60), // Minutes
        'max_pending_jobs' => env('PRIORITY_SYNC_MAX_PENDING_JOBS', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Unified Deal Sync Configuration (app:sync-deals-v2)
    |--------------------------------------------------------------------------
    | Single pipeline: MT5 DealGetPage → deals table → trades table → commissions
    */
    'deals_sync' => [
        'batch_size' => env('DEALS_SYNC_BATCH_SIZE', 10),
        'cycle_delay' => env('DEALS_SYNC_CYCLE_DELAY', 30), // Seconds between daemon cycles
        'min_sync_interval' => env('DEALS_SYNC_MIN_INTERVAL', 20), // Minutes between syncs for same account
        'max_pending_jobs' => env('DEALS_SYNC_MAX_PENDING_JOBS', 100),
        'stale_threshold' => env('DEALS_SYNC_STALE_THRESHOLD', 3), // Hours before forcing re-sync
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch Trade Sync Pagination Configuration
    |--------------------------------------------------------------------------
    |
    | Controls pagination and fairness for BatchSyncTradesJob
    | Limits the number of pages fetched per sync to ensure queue fairness
    | and prevent high-volume accounts from blocking the queue
    |
    */
    'batch_sync' => [
        // Page limit per sync job with fair queue distribution
        // OPTIMIZATION: Increased from 100 → 500 pages (April 7, 2026)
        // Rationale: Job timeout = 2700s (45 min) can safely handle 500 pages (~50k trades)
        // Benefit: Reduces AUTO_REQUEUE frequency by 5x, prevents queue monopolization
        // Large accounts: Auto-requeue handles overflow, other accounts interleave
        // Small accounts: Process in single job
        // Expected: No single account starves the queue
        'max_pages_per_sync' => env('BATCH_SYNC_MAX_PAGES', 500),

        // Enable automatic re-queueing of partial syncs
        'auto_requeue_partial' => env('BATCH_SYNC_AUTO_REQUEUE', true),

        // Trade count limits for high-volume account handling
        'max_trades_limit' => env('BATCH_SYNC_MAX_TRADES', 5000),      // Skip if > 5000 trades
        'min_trades_limit' => env('BATCH_SYNC_MIN_TRADES', 10),        // Skip if < 10 trades

        // Batch processing
        'accounts_per_batch' => env('BATCH_SYNC_ACCOUNTS_PER_BATCH', 10),
        'batch_delay_ms' => env('BATCH_SYNC_BATCH_DELAY_MS', 100),     // Delay between accounts
    ],
];
