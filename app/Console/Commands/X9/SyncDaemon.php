<?php

namespace App\Console\Commands\X9;

use App\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class SyncDaemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'x9:sync-daemon
                            {--positions-interval=60 : Interval in seconds for positions sync (default: 60)}
                            {--balances-interval=30 : Interval in seconds for balances sync (default: 30)}
                            {--all-positions-interval=300 : Interval in seconds for all positions sync (default: 300)}
                            {--closed-trades-interval=3600 : Interval in seconds for closed trades sync (default: 3600/1 hour)}
                            {--log : Enable detailed logging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run X9 sync commands continuously as a long-running process with configurable intervals';

    protected $lastSyncs = [];
    protected $syncTimesFile;
    protected $lastGroupSyncTimes = [];

    public function __construct()
    {
        parent::__construct();
        $this->syncTimesFile = storage_path('app/daemon-sync-times.json');

        // Initialize last sync times
        $this->lastSyncs = [
            'positions' => 0,
            'balances' => 0,
            'all-positions' => 0,
            'closed-trades' => 0,
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $positionsInterval = (int)$this->option('positions-interval');
        $balancesInterval = (int)$this->option('balances-interval');
        $allPositionsInterval = (int)$this->option('all-positions-interval');
        $closedTradesInterval = (int)$this->option('closed-trades-interval');
        $enableLogging = $this->option('log');

        // Load last sync times from storage
        $this->loadSyncTimes();

        // Get all unique X9 account group codes
        $clientGroups = $this->getX9ClientGroups();
        $this->info("  • All Positions: {$allPositionsInterval}s");
        if (!empty($clientGroups)) {
            $this->info("  • Closed Trades: {$closedTradesInterval}s (Groups: " . implode(', ', $clientGroups) . ")");
        } else {
            $this->info("  • Closed Trades: Disabled (no X9 accounts with groups found)");
        }
        $this->info("  • Logging: " . ($enableLogging ? 'Enabled' : 'Disabled'));
        $this->line('================================================');
        $this->newLine();

        // Handle signals for graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, function () {
                $this->info("\n\n✅ Gracefully shutting down...");
                exit(0);
            });

            pcntl_signal(SIGINT, function () {
                $this->info("\n\n✅ Gracefully shutting down...");
                exit(0);
            });
        }

        $currentTime = time();
        $this->lastSyncs = [
            'positions' => $currentTime,
            'balances' => $currentTime,
            'all-positions' => $currentTime,
            'closed-trades' => $currentTime,
        ];

        // Main loop
        while (true) {
            try {
                $currentTime = time();

                // Check if balances sync is due
                if (($currentTime - $this->lastSyncs['balances']) >= $balancesInterval) {
                    $this->syncBalances($enableLogging);
                    $this->lastSyncs['balances'] = $currentTime;
                }

                // Check if positions sync is due
                if (($currentTime - $this->lastSyncs['positions']) >= $positionsInterval) {
                    $this->syncAccountsWithPositions($enableLogging);
                    $this->lastSyncs['positions'] = $currentTime;
                }

                // Check if all positions sync is due
                if (($currentTime - $this->lastSyncs['all-positions']) >= $allPositionsInterval) {
                    $this->syncAllPositions($enableLogging);
                    $this->lastSyncs['all-positions'] = $currentTime;
                }

                // Check if closed trades sync is due
                if (!empty($clientGroups) && ($currentTime - $this->lastSyncs['closed-trades']) >= $closedTradesInterval) {
                    $this->syncClosedTradesForAllGroups($clientGroups, $enableLogging);
                    $this->lastSyncs['closed-trades'] = $currentTime;
                    $this->saveSyncTimes();
                }

                // Sleep for 1 second before next check
                sleep(1);

                // Dispatch pending signals
                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }
            } catch (\Exception $e) {
                $this->error("❌ Error in sync daemon: " . $e->getMessage());
                if ($enableLogging) {
                    Log::error('X9 Sync Daemon Error', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                sleep(5); // Wait before retrying
            }
        }
    }

    /**
     * Get all unique X9 account group codes from database
     */
    private function getX9ClientGroups()
    {
        try {
            $groups = Account::where('platform', Account::PLATFORM_X9)
                ->join('account_types', 'accounts.account_type_id', '=', 'account_types.id')
                ->whereNotNull('account_types.x9_group_id')
                ->distinct('account_types.x9_group_id')
                ->pluck('account_types.x9_group_id')
                ->toArray();

            return array_filter($groups); // Remove empty values
        } catch (\Exception $e) {
            Log::error('Failed to get X9 client groups', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Load last sync times from file
     */
    private function loadSyncTimes()
    {
        if (File::exists($this->syncTimesFile)) {
            try {
                $this->lastGroupSyncTimes = json_decode(File::get($this->syncTimesFile), true) ?? [];
            } catch (\Exception $e) {
                Log::error('Failed to load sync times', ['error' => $e->getMessage()]);
                $this->lastGroupSyncTimes = [];
            }
        }
    }

    /**
     * Save last sync times to file
     */
    private function saveSyncTimes()
    {
        try {
            File::put($this->syncTimesFile, json_encode($this->lastGroupSyncTimes, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            Log::error('Failed to save sync times', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Sync account balances
     */
    private function syncBalances($enableLogging = false)
    {
        $timestamp = $this->formatTimestamp();

        try {
            $this->line("[$timestamp] 💰 Syncing Account Balances...");
            Log::info('X9 Sync Daemon: Starting Account Balances sync');

            $options = $enableLogging ? ['--log' => true, '--force' => true] : ['--force' => true];

            $exitCode = $this->call('x9:sync-account-balances', $options);

            if ($exitCode === 0) {
                $this->info("[$timestamp] ✅ Account Balances synced successfully");
                Log::info('X9 Sync Daemon: Account Balances synced successfully');

                if ($enableLogging) {
                    Log::info('X9 Sync Daemon: Account Balances synced successfully');
                }
            } else {
                $this->warn("[$timestamp] ⚠️  Account Balances sync completed with exit code: {$exitCode}");
                Log::warning("X9 Sync Daemon: Account Balances sync returned exit code {$exitCode}");

                if ($enableLogging) {
                    Log::warning("X9 Sync Daemon: Account Balances sync returned exit code {$exitCode}");
                }
            }
        } catch (\Exception $e) {
            $this->error("[$timestamp] ❌ Error syncing balances: " . $e->getMessage());
            Log::error('X9 Sync Daemon: Balance sync error', [
                'error' => $e->getMessage()
            ]);

            if ($enableLogging) {
                Log::error('X9 Sync Daemon: Balance sync error', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Sync accounts with positions
     */
    private function syncAccountsWithPositions($enableLogging = false)
    {
        $timestamp = $this->formatTimestamp();

        try {
            $this->line("[$timestamp] 📊 Syncing Accounts with Positions...");
            Log::info('X9 Sync Daemon: Starting Accounts with Positions sync');

            $options = $enableLogging ? ['--log' => true, '--update-balances' => true] : ['--update-balances' => true];

            $exitCode = $this->call('x9:sync-accounts-with-positions', $options);

            if ($exitCode === 0) {
                $this->info("[$timestamp] ✅ Accounts with Positions synced successfully");
                Log::info('X9 Sync Daemon: Accounts with Positions synced successfully');

                if ($enableLogging) {
                    Log::info('X9 Sync Daemon: Accounts with Positions synced successfully');
                }
            } else {
                $this->warn("[$timestamp] ⚠️  Accounts with Positions sync completed with exit code: {$exitCode}");
                Log::warning("X9 Sync Daemon: Accounts with Positions sync returned exit code {$exitCode}");

                if ($enableLogging) {
                    Log::warning("X9 Sync Daemon: Accounts with Positions sync returned exit code {$exitCode}");
                }
            }
        } catch (\Exception $e) {
            $this->error("[$timestamp] ❌ Error syncing accounts with positions: " . $e->getMessage());
            Log::error('X9 Sync Daemon: Accounts with Positions sync error', [
                'error' => $e->getMessage()
            ]);

            if ($enableLogging) {
                Log::error('X9 Sync Daemon: Accounts with Positions sync error', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Sync all positions
     */
    private function syncAllPositions($enableLogging = false)
    {
        $timestamp = $this->formatTimestamp();

        try {
            $this->line("[$timestamp] 🔄 Syncing All Positions...");
            Log::info('X9 Sync Daemon: Starting All Positions sync');

            $options = ['--save' => true];
            if ($enableLogging) {
                $options['--log'] = true;
            }

            $exitCode = $this->call('x9:sync-all-positions', $options);

            if ($exitCode === 0) {
                $this->info("[$timestamp] ✅ All Positions synced successfully");
                Log::info('X9 Sync Daemon: All Positions synced successfully');

                if ($enableLogging) {
                    Log::info('X9 Sync Daemon: All Positions synced successfully');
                }
            } else {
                $this->warn("[$timestamp] ⚠️  All Positions sync completed with exit code: {$exitCode}");
                Log::warning("X9 Sync Daemon: All Positions sync returned exit code {$exitCode}");

                if ($enableLogging) {
                    Log::warning("X9 Sync Daemon: All Positions sync returned exit code {$exitCode}");
                }
            }
        } catch (\Exception $e) {
            $this->error("[$timestamp] ❌ Error syncing all positions: " . $e->getMessage());
            Log::error('X9 Sync Daemon: All Positions sync error', [
                'error' => $e->getMessage()
            ]);

            if ($enableLogging) {
                Log::error('X9 Sync Daemon: All Positions sync error', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Sync closed trades for all client groups with incremental date ranges
     */
    private function syncClosedTradesForAllGroups($clientGroups, $enableLogging = false)
    {
        $timestamp = $this->formatTimestamp();

        foreach ($clientGroups as $groupId) {
            try {
                $this->line("[$timestamp] 📋 Syncing Closed Trades for Group {$groupId}...");

                // Get last sync time for this group
                $lastSyncTime = $this->lastGroupSyncTimes[$groupId] ?? null;

                // Determine date range
                if ($lastSyncTime) {
                    // Resume from last sync time
                    $dateFrom = Carbon::createFromTimestamp($lastSyncTime)->format('Y-m-d');
                } else {
                    // First time sync - go back 30 days
                    $dateFrom = Carbon::now()->subDays(30)->format('Y-m-d');
                }

                $dateTo = Carbon::now()->format('Y-m-d');

                // Build command options
                $options = [
                    'client_group_id' => $groupId,
                    '--save' => true,
                    '--date-from' => $dateFrom,
                    '--date-to' => $dateTo,
                ];

                if ($enableLogging) {
                    $options['--log'] = true;
                }

                // Call the sync command with group ID as argument
                $exitCode = $this->call('x9:sync-closed-trades', $options);

                if ($exitCode === 0) {
                    // Update last sync time for this group
                    $this->lastGroupSyncTimes[$groupId] = time();

                    $this->info("[$timestamp] ✅ Closed Trades synced for Group {$groupId} (from {$dateFrom} to {$dateTo})");

                    if ($enableLogging) {
                        Log::info('X9 Sync Daemon: Closed Trades synced successfully', [
                            'client_group_id' => $groupId,
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo
                        ]);
                    }
                } else {
                    $this->warn("[$timestamp] ⚠️  Closed Trades sync for Group {$groupId} completed with exit code: {$exitCode}");

                    if ($enableLogging) {
                        Log::warning("X9 Sync Daemon: Closed Trades sync returned exit code {$exitCode}", [
                            'client_group_id' => $groupId,
                            'date_from' => $dateFrom,
                            'date_to' => $dateTo
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $this->error("[$timestamp] ❌ Error syncing closed trades for Group {$groupId}: " . $e->getMessage());

                if ($enableLogging) {
                    Log::error('X9 Sync Daemon: Closed Trades sync error', [
                        'client_group_id' => $groupId,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Format timestamp for output
     */
    private function formatTimestamp()
    {
        return Carbon::now()->format('Y-m-d H:i:s');
    }
}
