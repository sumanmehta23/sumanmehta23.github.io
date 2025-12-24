<?php

namespace App\Console\Commands\X9;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
                            {--log : Enable detailed logging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run X9 sync commands continuously as a long-running process with configurable intervals';

    protected $lastSyncs = [];

    public function __construct()
    {
        parent::__construct();

        // Initialize last sync times
        $this->lastSyncs = [
            'positions' => 0,
            'balances' => 0,
            'all-positions' => 0,
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
        $enableLogging = $this->option('log');

        $this->info('🚀 Starting X9 Sync Daemon');
        $this->line('================================================');
        $this->info("Sync Intervals:");
        $this->info("  • Balances: {$balancesInterval}s");
        $this->info("  • Accounts with Positions: {$positionsInterval}s");
        $this->info("  • All Positions: {$allPositionsInterval}s");
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
     * Sync account balances
     */
    private function syncBalances($enableLogging = false)
    {
        $timestamp = $this->formatTimestamp();

        try {
            $this->line("[$timestamp] 💰 Syncing Account Balances...");

            $options = $enableLogging ? ['--log' => true, '--force' => true] : ['--force' => true];

            $exitCode = $this->call('x9:sync-account-balances', $options);

            if ($exitCode === 0) {
                $this->info("[$timestamp] ✅ Account Balances synced successfully");

                if ($enableLogging) {
                    Log::info('X9 Sync Daemon: Account Balances synced successfully');
                }
            } else {
                $this->warn("[$timestamp] ⚠️  Account Balances sync completed with exit code: {$exitCode}");

                if ($enableLogging) {
                    Log::warning("X9 Sync Daemon: Account Balances sync returned exit code {$exitCode}");
                }
            }
        } catch (\Exception $e) {
            $this->error("[$timestamp] ❌ Error syncing balances: " . $e->getMessage());

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

            $options = $enableLogging ? ['--log' => true, '--update-balances' => true] : ['--update-balances' => true];

            $exitCode = $this->call('x9:sync-accounts-with-positions', $options);

            if ($exitCode === 0) {
                $this->info("[$timestamp] ✅ Accounts with Positions synced successfully");

                if ($enableLogging) {
                    Log::info('X9 Sync Daemon: Accounts with Positions synced successfully');
                }
            } else {
                $this->warn("[$timestamp] ⚠️  Accounts with Positions sync completed with exit code: {$exitCode}");

                if ($enableLogging) {
                    Log::warning("X9 Sync Daemon: Accounts with Positions sync returned exit code {$exitCode}");
                }
            }
        } catch (\Exception $e) {
            $this->error("[$timestamp] ❌ Error syncing accounts with positions: " . $e->getMessage());

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

            $options = $enableLogging ? ['--log' => true] : [];

            $exitCode = $this->call('x9:sync-all-positions', $options);

            if ($exitCode === 0) {
                $this->info("[$timestamp] ✅ All Positions synced successfully");

                if ($enableLogging) {
                    Log::info('X9 Sync Daemon: All Positions synced successfully');
                }
            } else {
                $this->warn("[$timestamp] ⚠️  All Positions sync completed with exit code: {$exitCode}");

                if ($enableLogging) {
                    Log::warning("X9 Sync Daemon: All Positions sync returned exit code {$exitCode}");
                }
            }
        } catch (\Exception $e) {
            $this->error("[$timestamp] ❌ Error syncing all positions: " . $e->getMessage());

            if ($enableLogging) {
                Log::error('X9 Sync Daemon: All Positions sync error', [
                    'error' => $e->getMessage()
                ]);
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
