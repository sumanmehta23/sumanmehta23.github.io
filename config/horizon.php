<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Horizon will be accessible from. If this
    | setting is null, Horizon will reside under the same domain as the
    | application. Otherwise, this value will serve as the subdomain.
    |
    */

    'domain' => env('HORIZON_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Horizon will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that aren't exposed to users.
    |
    */

    'path' => env('HORIZON_PATH', 'horizon'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection where Horizon will store the
    | meta information required for it to function. It includes the list
    | of supervisors, failed jobs, job metrics, and other information.
    |
    */

    'use' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when storing all Horizon data in Redis. You
    | may modify the prefix when you are running multiple installations
    | of Horizon on the same server so that they don't have problems.
    |
    */

    'prefix' => env(
        'HORIZON_PREFIX',
        Str::slug(env('APP_NAME', 'laravel'), '_') . '_horizon:'
    ),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will get attached onto each Horizon route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds
    |--------------------------------------------------------------------------
    |
    | This option allows you to configure when the LongWaitDetected event
    | will be fired. Every connection / queue combination may have its
    | own, unique threshold (in seconds) before this event is fired.
    |
    */

    'waits' => [
        'redis:default' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    |
    | Here you can configure for how long (in minutes) you desire Horizon to
    | persist the recent and failed jobs. Typically, recent jobs are kept
    | for one hour while all failed jobs are stored for an entire week.
    |
    */

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],

    /*
    |--------------------------------------------------------------------------
    | Silenced Jobs
    |--------------------------------------------------------------------------
    |
    | Silencing a job will instruct Horizon to not place the job in the list
    | of completed jobs within the Horizon dashboard. This setting may be
    | used to fully remove any noisy jobs from the completed jobs list.
    |
    */

    'silenced' => [
        // App\Jobs\ExampleJob::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics
    |--------------------------------------------------------------------------
    |
    | Here you can configure how many snapshots should be kept to display in
    | the metrics graph. This will get used in combination with Horizon's
    | `horizon:snapshot` schedule to define how long to retain metrics.
    |
    */

    'metrics' => [
        'trim_snapshots' => [
            'job' => 24,
            'queue' => 24,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, Horizon's "terminate" command will not
    | wait on all of the workers to terminate unless the --wait option
    | is provided. Fast termination can shorten deployment delay by
    | allowing a new instance of Horizon to start while the last
    | instance will continue to terminate each of its workers.
    |
    */

    'fast_termination' => false,

    /*
    |--------------------------------------------------------------------------
    | Memory Limit (MB)
    |--------------------------------------------------------------------------
    |
    | This value describes the maximum amount of memory the Horizon master
    | supervisor may consume before it is terminated and restarted. For
    | configuring these limits on your workers, see the next section.
    |
    */

    'memory_limit' => 64,

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the queue worker settings used by your application
    | in all environments. These supervisors and settings handle all your
    | queued jobs and will be provisioned by Horizon during deployment.
    |
    */

    'defaults' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default', 'emails'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'minProcesses' => 1,  // OPTIMIZATION STEP 4: Always maintain at least 1 worker
            'maxProcesses' => env('SYNC_MAX_DEFAULT_PROCESSES', 3),
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => 256,
            'tries' => 1,
            'timeout' => 600,
            'nice' => 0,
        ],
        'supervisor-2' => [
            'connection' => 'redis',
            'queue' => ['syncaccountstrades'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'size',  // Use queue size-based scaling (better than time-based)
            'minProcesses' => 5,  // Keep at least 5 workers for this critical queue
            'maxProcesses' => env('SYNC_MAX_TRADES_PROCESSES', 25),
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => 512,
            'tries' => 0,      // Defer to job's $tries (SyncDealsJob: 3)
            'timeout' => 660,  // Slightly above job's 600s to let it exit cleanly
            'nice' => 0,
        ],
        'supervisor-3' => [
            'connection' => 'redis',
            'queue' => ['distributeibcommission'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'size',  // Size-based scaling for better queue handling
            'minProcesses' => 3,  // Minimum workers for commission distribution
            'maxProcesses' => env('SYNC_MAX_IB_DISTRIBUTION_PROCESSES', 30),
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => 512,
            'tries' => 1,
            'timeout' => 1200,
            'nice' => 0,
        ],
        'supervisor-4' => [
            'connection' => 'redis',
            'queue' => ['sync-trades', 'sync-all-trades',],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'minProcesses' => 1,
            'maxProcesses' => env('SYNC_TRADES_MAX_PROCESSES', 8),
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => 256,
            'tries' => 1,
            'timeout' => 120,
            'nice' => 0,
        ],
        'supervisor-optimized-sync' => [
            'connection' => 'redis',
            'queue' => ['optimized-sync-trades', 'enhanced-batch-sync-trades'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'size',  // Queue size-based scaling
            'minProcesses' => 2,  // Keep minimum workers running
            'maxProcesses' => env('SYNC_ALL_TRADES_MAX_PROCESSES', 15),
            'maxTime' => 0,
            'maxJobs' => 100,
            'memory' => 256,
            'tries' => 2,
            'timeout' => 600,
            'nice' => 0,
        ],
        'supervisor-account-sync' => [
            'connection' => 'redis',
            'queue' => ['account-sync'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'minProcesses' => 1,
            'maxProcesses' => env('ACCOUNT_SYNC_MAX_PROCESSES', 3),
            'maxTime' => 0,
            'maxJobs' => 50,
            'memory' => 256,
            'tries' => 2,
            'timeout' => 600,
            'nice' => 0,
        ],
        'supervisor-priority-sync' => [
            'connection' => 'redis',
            'queue' => ['priority-sync-trades'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'size',  // Size-based scaling for priority work
            'minProcesses' => 2,  // Always maintain at least 2 for priority work
            'maxProcesses' => env('PRIORITY_SYNC_MAX_QUEUE_PROCESSES', 20),
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => 512,
            'tries' => 1,
            'timeout' => 1200,
            'nice' => 0,
        ],
        'supervisor-high-volume-sync' => [
            'connection' => 'redis',
            'queue' => ['high-volume-sync'],
            'balance' => 'auto',
            'minProcesses' => 1,
            'maxProcesses' => env('HIGH_VOLUME_SYNC_MAX_QUEUE_PROCESSES', 1),
            'memory' => 256,
            'tries' => 1,
            'timeout' => 1800,
        ],
        'supervisor-demo-sync' => [
            'connection' => 'redis',
            'queue' => ['demo-sync-trades'],
            'balance' => 'simple',
            'minProcesses' => 1,
            'maxProcesses' => 1,
            'memory' => 256,
            'tries' => 1,
            'timeout' => 900,
            'nice' => 10,
        ],
        'supervisor-deal-sync' => [
            'connection' => 'redis',
            'queue' => ['deal-sync'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'minProcesses' => 1,
            'maxProcesses' => env('DEAL_SYNC_MAX_PROCESSES', 3),
            'maxTime' => 0,
            'maxJobs' => 50,
            'memory' => 256,
            'tries' => 3,
            'timeout' => 900,
            'nice' => 5,
        ],


    ],

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'balanceMaxShift' => 1,
                'balanceCooldown' => 5,
            ],
            'supervisor-2' => [
                // OPTIMIZATION STEP 4: Aggressive auto-scaling for critical queue
                'minProcesses' => 5,  // Always keep 5+ workers for large backlogs
                'maxProcesses' => env('SYNC_MAX_TRADES_PROCESSES', 5),
                'balanceMaxShift' => 3,  // Allow faster scaling up
                'balanceCooldown' => 2,  // Quick response to queue changes
            ],
            'supervisor-3' => [
                // OPTIMIZATION STEP 4: Tuned for commission distribution
                'minProcesses' => 3,
                'maxProcesses' => env('SYNC_MAX_IB_DISTRIBUTION_PROCESSES', 5),
                'balanceMaxShift' => 2,  // Allow moderate scaling
                'balanceCooldown' => 3,  // Moderate response time
            ],
            'supervisor-4' => [
                'balanceMaxShift' => 1,
                'balanceCooldown' => 5,
            ],
            'supervisor-optimized-sync' => [
                'minProcesses' => 2,
                'maxProcesses' => env('SYNC_ALL_TRADES_MAX_PROCESSES', 15),
                'balanceMaxShift' => 2,
                'balanceCooldown' => 3,
            ],
            'supervisor-account-sync' => [
                'minProcesses' => 1,
                'maxProcesses' => env('ACCOUNT_SYNC_MAX_PROCESSES', 3),
                'balanceMaxShift' => 1,
                'balanceCooldown' => 5,
            ],
            'supervisor-priority-sync' => [
                // OPTIMIZATION STEP 4: Aggressive scaling for priority work
                'minProcesses' => 2,
                'maxProcesses' => env('PRIORITY_SYNC_MAX_QUEUE_PROCESSES', 20),
                'balanceMaxShift' => 3,  // Fast scaling for priority
                'balanceCooldown' => 2,  // Quick response
            ],
            'supervisor-demo-sync' => [
                'maxProcesses' => 1,
                'balanceMaxShift' => 0,
                'balanceCooldown' => 30,
            ],
            'supervisor-deal-sync' => [
                'minProcesses' => 1,
                'maxProcesses' => env('DEAL_SYNC_MAX_PROCESSES', 2),
                'balanceMaxShift' => 1,
                'balanceCooldown' => 10,
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'minProcesses' => 1,
            ],
            'supervisor-2' => [
                'minProcesses' => 1,
                'maxProcesses' => 5,
            ],
            'supervisor-3' => [
                'minProcesses' => 1,
                'maxProcesses' => 5,
            ],
            'supervisor-4' => [
                'minProcesses' => 1,
                'maxProcesses' => 2,
            ],
            'supervisor-optimized-sync' => [
                'minProcesses' => 1,
                'maxProcesses' => env('SYNC_ALL_TRADES_MAX_PROCESSES', 5),
            ],
            'supervisor-account-sync' => [
                'minProcesses' => 1,
                'maxProcesses' => 1,
            ],
            'supervisor-priority-sync' => [
                'minProcesses' => 1,
                'maxProcesses' => 2,
            ],
            'supervisor-demo-sync' => [
                'minProcesses' => 1,
                'maxProcesses' => 1,
            ],
            'supervisor-deal-sync' => [
                'minProcesses' => 1,
                'maxProcesses' => env('DEAL_SYNC_MAX_PROCESSES', 1),
            ],
        ],
        'development' => [
            'supervisor-1' => [
                // 'maxProcesses' => 1,
            ],
            'supervisor-2' => [
                'maxProcesses' => 2,
            ],
            'supervisor-3' => [
                'maxProcesses' => 1,
            ],
            'supervisor-4' => [
                'maxProcesses' => 2,
            ],
            'supervisor-optimized-sync' => [
                'maxProcesses' => env('SYNC_ALL_TRADES_MAX_PROCESSES', 5), // Reduced for connection management
            ],
            'supervisor-demo-sync' => [
                'maxProcesses' => 1, // Only 1 process for demos in local too
            ],
            'supervisor-deal-sync' => [
                'maxProcesses' => env('DEAL_SYNC_MAX_PROCESSES', 1), // Single process for local development
            ],
        ],
    ],
];
