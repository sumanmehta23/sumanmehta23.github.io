<?php

namespace App\Console\Commands;

use App\Jobs\SyncDealsJob;
use App\Models\Account;
use App\Models\Ib1;
use App\Enums\PlatformEnum;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SyncDeals extends Command
{
    protected $signature = 'app:sync-deals-v2
        {--batch-size= : Number of accounts per job (default from config)}
        {--max-jobs=0 : Maximum number of jobs to create per cycle (0=unlimited)}
        {--max-pages=20 : Maximum pages per account per sync}
        {--active-only : Only sync accounts with recent activity}
        {--email= : Sync only accounts under a specific IB email}
        {--code= : Sync only for a specific account code}
        {--accounts= : Sync specific accounts only (comma-separated IDs or codes)}
        {--daemon : Run continuously as daemon}
        {--cycle-delay= : Delay between sync cycles in seconds (default from config)}
        {--max-pending-jobs= : Maximum pending SyncDealsJob jobs allowed (default from config)}
        {--stale-threshold= : Sync accounts not synced for X hours (default: 3)}
        {--min-sync-interval= : Minimum minutes between syncs for same account (default from config)}
        {--status : Show current sync status}
        {--unflag-account= : Manually unflag a problematic account by code}
        {--clear-stuck-cache : Clear all stuck account cache markers}';

    protected $description = 'Unified deal-based sync: fetches MT5 deals → upserts deals table → syncs trades table → distributes commissions. Replaces priority-sync with a single pipeline.';

    public function handle()
    {
        // ── Config defaults ──
        $batchSize = (int) ($this->option('batch-size') ?: config('sync-all-trades.deals_sync.batch_size', 10));
        $maxJobs = (int) $this->option('max-jobs');
        $maxPages = max(1, (int) $this->option('max-pages'));
        $cycleDelay = (int) ($this->option('cycle-delay') ?: config('sync-all-trades.deals_sync.cycle_delay', 30));
        $maxPendingJobs = (int) ($this->option('max-pending-jobs') ?: config('sync-all-trades.deals_sync.max_pending_jobs', 100));
        $staleThreshold = (int) ($this->option('stale-threshold') ?: config('sync-all-trades.deals_sync.stale_threshold', 3));
        $minSyncInterval = (int) ($this->option('min-sync-interval') ?: config('sync-all-trades.deals_sync.min_sync_interval', 20));
        $activeOnly = $this->option('active-only');
        $email = $this->option('email');
        $code = $this->option('code');
        $isDaemon = $this->option('daemon');

        // Parse specific accounts
        $specificAccounts = null;
        if ($this->option('accounts')) {
            $specificAccounts = array_map('trim', explode(',', $this->option('accounts')));
            $this->info("Syncing specific accounts: " . implode(', ', $specificAccounts));
        }

        // ── Subcommands ──
        if ($this->option('status')) {
            $this->showSyncStatus();
            return;
        }
        if ($this->option('unflag-account')) {
            $this->unflagAccount($this->option('unflag-account'));
            return;
        }
        if ($this->option('clear-stuck-cache')) {
            $this->clearAllStuckCache();
            return;
        }

        $this->info("Starting unified deal sync (deals → trades → commissions) with:");
        $this->info("- Batch size: {$batchSize}");
        $this->info("- Cycle delay: {$cycleDelay}s");
        $this->info("- Max pending jobs: {$maxPendingJobs}");
        $this->info("- Stale threshold: {$staleThreshold}h");
        $this->info("- Min sync interval: {$minSyncInterval}m");
        $this->info("- Max pages/account: {$maxPages}");
        if ($email) $this->info("- IB email filter: {$email}");
        if ($code) $this->info("- Account code filter: {$code}");

        if ($isDaemon) {
            $this->runDaemonMode($batchSize, $maxJobs, $maxPages, $cycleDelay, $maxPendingJobs, $staleThreshold, $minSyncInterval, $activeOnly, $email, $code, $specificAccounts);
        } else {
            $this->runSingleCycle($batchSize, $maxJobs, $maxPages, $maxPendingJobs, $staleThreshold, $minSyncInterval, $activeOnly, $email, $code, $specificAccounts);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Daemon Mode
    // ─────────────────────────────────────────────────────────

    protected function runDaemonMode(int $batchSize, int $maxJobs, int $maxPages, int $cycleDelay, int $maxPendingJobs, int $staleThreshold, int $minSyncInterval, bool $activeOnly, ?string $email, ?string $code, ?array $specificAccounts): void
    {
        $this->info("Running in daemon mode. Press Ctrl+C to stop.");

        $cycleCount = 0;
        while (true) {
            try {
                $cycleCount++;
                $this->info("=== Sync Cycle #{$cycleCount} at " . now()->format('Y-m-d H:i:s') . " ===");

                $pendingJobs = $this->getPendingJobsCount();
                $this->info("Current pending SyncDealsJob jobs: {$pendingJobs}");

                if ($pendingJobs >= $maxPendingJobs) {
                    $this->warn("Queue limit reached ({$pendingJobs}/{$maxPendingJobs}). Skipping dispatch cycle.");
                    sleep($cycleDelay);
                    continue;
                }

                $processed = $this->runSingleCycle($batchSize, $maxJobs, $maxPages, $maxPendingJobs, $staleThreshold, $minSyncInterval, $activeOnly, $email, $code, $specificAccounts);

                if ($processed === 0) {
                    $this->info("No accounts needed syncing. Waiting {$cycleDelay}s...");
                } else {
                    $this->info("Dispatched jobs for {$processed} accounts. Waiting {$cycleDelay}s...");
                }
                sleep($cycleDelay);
            } catch (\Exception $e) {
                $this->error("Daemon cycle error: " . $e->getMessage());
                Log::error("SyncDeals daemon error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                sleep(60);
            }
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Single Cycle (dispatches SyncDealsJob batches)
    // ─────────────────────────────────────────────────────────

    protected function runSingleCycle(int $batchSize, int $maxJobs, int $maxPages, int $maxPendingJobs, int $staleThreshold, int $minSyncInterval, bool $activeOnly, ?string $email, ?string $code, ?array $specificAccounts): int
    {
        $startTime = microtime(true);
        $totalJobsCreated = 0;

        // Handle stuck accounts from previous cycles
        $this->handleStuckAccounts();

        // Check queue capacity
        $pendingJobs = $this->getPendingJobsCount();
        if ($pendingJobs >= $maxPendingJobs) {
            $this->warn("Queue limit reached ({$pendingJobs}/{$maxPendingJobs}). Skipping sync cycle.");
            return 0;
        }

        // ── Build account query with priority selection ──
        $accounts = $this->getAccountsToSync(
            $batchSize,
            $maxPendingJobs,
            $staleThreshold,
            $minSyncInterval,
            $activeOnly,
            $email,
            $code,
            $specificAccounts
        );

        if ($accounts->isEmpty()) {
            $this->info("No accounts need syncing.");
            return 0;
        }

        $this->info("Found {$accounts->count()} accounts needing sync");
        $this->showPriorityBreakdown($accounts);

        // ── Group accounts by deal volume, then batch accordingly ──
        // This prevents high-volume accounts from timing out during REST calls
        $allBatches = $this->buildVolumeLevelBatches($accounts, $batchSize);

        // ── Dispatch SyncDealsJob batches ──
        foreach ($allBatches as $batchIndex => $batch) {
            // Re-check queue capacity before each dispatch
            $currentPending = $this->getPendingJobsCount();
            if ($currentPending >= $maxPendingJobs) {
                $this->warn("Queue limit reached ({$currentPending}/{$maxPendingJobs}). Stopping further dispatches.");
                // Mark remaining accounts for retry
                $remaining = $allBatches->slice($batchIndex)->flatten()->pluck('id');
                if ($remaining->isNotEmpty()) {
                    Account::whereIn('id', $remaining)->update([
                        'sync_status' => 'needs_retry',
                        'sync_error' => 'Queue limit reached - will retry next cycle',
                    ]);
                    $this->info("Marked {$remaining->count()} accounts for retry in next cycle");
                }
                break;
            }

            if ($maxJobs > 0 && $totalJobsCreated >= $maxJobs) {
                $this->info("Reached maximum job limit of {$maxJobs}.");
                break;
            }

            $batchIds = $batch->pluck('id')->values()->toArray();
            $batchCodes = $batch->pluck('code')->join(', ');

            Account::whereIn('id', $batchIds)->update([
                'last_sync_attempt_at' => now(),
                'sync_status' => 'pending',
            ]);

            SyncDealsJob::dispatch($batchIds, $maxPages)
                ->onQueue('syncaccountstrades');

            $totalJobsCreated++;
            $this->info("Batch " . ($batchIndex + 1) . ": {$batchCodes} (Queue: {$currentPending}/{$maxPendingJobs})");

            // Small delay between dispatches to avoid overwhelming
            if ($batchIndex < $allBatches->count() - 1) {
                usleep(200_000); // 200ms
            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        $this->info("Dispatched {$totalJobsCreated} SyncDealsJob(s) for {$accounts->count()} accounts in {$duration}s.");
        $this->info("Pipeline per job: MT5 DealGetPage → deals table → trades table → commission distribution.");

        Log::info('SyncDeals cycle completed', [
            'sync_jobs' => $totalJobsCreated,
            'total_accounts' => $accounts->count(),
            'duration_seconds' => $duration,
        ]);

        return $accounts->count();
    }

    // ─────────────────────────────────────────────────────────
    //  Priority-Based Account Selection
    // ─────────────────────────────────────────────────────────

    protected function getAccountsToSync(int $batchSize, int $maxPendingJobs, int $staleThreshold, int $minSyncInterval, bool $activeOnly, ?string $email, ?string $code, ?array $specificAccounts)
    {
        $cutoffTime = now()->subMinutes($minSyncInterval);

        // ── Base query: eligible accounts ──
        $query = Account::query()
            ->join('aspnetusers as u', 'accounts.user_id', '=', 'u.id')
            ->select('accounts.*')
            ->whereNotNull('accounts.code')
            ->whereNull('accounts.deleted_at')
            ->where('accounts.account_request_status', 1)
            ->whereNull('deletion_type')
            ->where('u.status', 1)
            ->where('accounts.platform', PlatformEnum::MT5->value)
            ->whereRaw("( (accounts.competition_product_id IS NULL AND accounts.demo = 0)
                    OR (accounts.competition_product_id IS NOT NULL AND accounts.demo = 1) )");

        // ── Specific accounts bypass most restrictions ──
        if ($specificAccounts !== null) {
            $this->info("Forcing sync for specific accounts - bypassing timing restrictions");
            $query->whereNotIn('accounts.sync_status', ['not_found_in_mt5'])
                ->where(function ($q) use ($specificAccounts) {
                    $q->whereIn('accounts.code', $specificAccounts)
                        ->orWhereIn('accounts.id', array_filter($specificAccounts, fn($v) => preg_match('/^[0-9a-f-]{36}$/i', $v)));
                });
        } else {
            // ── Normal restrictions ──
            // Only skip: flagged, not_found_in_mt5, pending (already processing), and accounts synced within minSyncInterval
            $query->whereNotIn('accounts.sync_status', ['flagged', 'not_found_in_mt5', 'pending'])
                ->where(function ($q) use ($cutoffTime) {
                    $q->whereIn('accounts.sync_status', ['needs_retry'])
                        ->orWhereNull('accounts.last_sync_attempt_at')
                        ->orWhereNull('accounts.deals_synced_to')
                        ->orWhere('accounts.last_sync_attempt_at', '<', $cutoffTime);
                });

            // ── IB email filter ──
            if ($email) {
                $ib = Ib1::where('email', $email)->where('status', 1)->first();
                if (!$ib) {
                    $this->error("No active IB found with email: {$email}");
                    return collect();
                }
                $referralCode = $ib->referral_code ?: $ib->email;
                $query->where(function ($q) use ($referralCode) {
                    for ($i = 1; $i <= 15; $i++) {
                        $q->orWhere("u.ib{$i}", $referralCode);
                    }
                });
                $this->info("Filtering accounts under IB: {$referralCode} ({$email})");
            }

            // ── Account code filter ──
            if ($code) {
                $query->where('accounts.code', $code);
            }

            // ── Active-only filter ──
            if ($activeOnly) {
                $query->where(function ($q) {
                    $q->where('accounts.last_trade_at', '>=', now()->subDays(30))
                        ->orWhereNull('accounts.last_trade_at');
                });
            }
        }

        // ── Priority ordering: needs_retry → never synced → stale ──
        $query->orderByRaw(
            "CASE
                WHEN accounts.sync_status = 'needs_retry' THEN 0
                WHEN accounts.deals_synced_to IS NULL THEN 1
                ELSE 2
            END,
            COALESCE(accounts.deals_synced_to, '2000-01-01') ASC"
        );

        // Limit to a reasonable number per cycle
        $limit = $batchSize * 10;
        return $query->limit($limit)->get();
    }

    protected function showPriorityBreakdown($accounts): void
    {
        $retryCount = $accounts->where('sync_status', 'needs_retry')->count();
        $balanceChanged = $accounts->where('has_balance_activity', true)
            ->filter(fn($a) => $a->last_balance_changed_at && (!$a->last_sync_attempt_at || $a->last_balance_changed_at > $a->last_sync_attempt_at))
            ->count();
        $neverSynced = $accounts->filter(fn($a) => is_null($a->deals_synced_to) && $a->sync_status !== 'needs_retry')->count();
        $stale = $accounts->count() - $retryCount - $balanceChanged - $neverSynced;

        $this->info("Priority: {$retryCount} retry, {$balanceChanged} balance changed, {$neverSynced} never synced, {$stale} stale");
    }

    /**
     * Group accounts by deal volume and determine optimal batch sizes.
     *
     * High-volume accounts are batched smaller (or alone) to avoid REST timeouts.
     * Low-volume accounts can be batched larger.
     */
    protected function buildVolumeLevelBatches($accounts, int $defaultBatchSize): \Illuminate\Support\Collection
    {
        $brackets = config('deals_volume_profile.deals_volume_profile.brackets', [
            5000 => 20,
            20000 => 10,
            100000 => 5,
            200000 => 2,
            PHP_INT_MAX => 1,
        ]);
        $minDealsForIndividual = config('deals_volume_profile.deals_volume_profile.min_deals_for_individual_sync', 50000);
        $fallbackBatchSize = config('deals_volume_profile.deals_volume_profile.fallback_batch_size', 5);
        $maxPerJob = config('deals_volume_profile.deals_volume_profile.max_accounts_per_job', 20);

        // Group accounts by volume bracket
        $volumeGroups = collect();

        foreach ($accounts as $account) {
            // Determine batch size based on pending deal count
            $dealCount = $account->pending_deal_count ?? null;
            $batchSize = $fallbackBatchSize;

            if ($dealCount !== null) {
                // Find the bracket for this account's deal count
                foreach ($brackets as $maxDeals => $bracketBatchSize) {
                    if ($dealCount <= $maxDeals) {
                        $batchSize = $bracketBatchSize;
                        break;
                    }
                }

                // For already-synced accounts with high volume, force individual
                if (!is_null($account->deals_synced_to) && $dealCount >= $minDealsForIndividual) {
                    $batchSize = 1;
                }
            } else {
                // No count yet, use conservative size for first-time sync
                $batchSize = is_null($account->deals_synced_to) ? 3 : $defaultBatchSize;
            }

            // Cap batch size at the global maximum
            $batchSize = min($batchSize, $maxPerJob);

            $groupKey = "{$batchSize}_per_job";
            if (!$volumeGroups->has($groupKey)) {
                $volumeGroups[$groupKey] = collect();
            }
            $volumeGroups[$groupKey]->push($account);
        }

        // Now batch each volume group and return all batches
        $allBatches = collect();

        foreach ($volumeGroups as $groupKey => $group) {
            preg_match('/(\d+)_per_job/', $groupKey, $matches);
            $batchSize = (int) ($matches[1] ?? $fallbackBatchSize);

            $this->info("Volume group: batch size {$batchSize}, {$group->count()} accounts");

            foreach ($group->chunk($batchSize) as $batch) {
                $allBatches->push($batch);
            }
        }

        return $allBatches;
    }



    // ─────────────────────────────────────────────────────────
    //  Queue Management
    // ─────────────────────────────────────────────────────────

    protected function getPendingJobsCount(): int
    {
        try {
            $queueName = 'queues:syncaccountstrades';
            $pending = Redis::llen($queueName);
            $delayed = Redis::zcard($queueName . ':delayed');
            $reserved = Redis::zcard($queueName . ':reserved');
            return $pending + $delayed + $reserved;
        } catch (\Exception $e) {
            Log::warning("Could not get queue count: " . $e->getMessage());
            return 0;
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Stuck Account Detection & Recovery
    // ─────────────────────────────────────────────────────────

    protected function handleStuckAccounts(): void
    {
        $stuckThreshold = now()->subMinutes(45);

        $stuckAccounts = Account::where('sync_status', 'pending')
            ->where('last_sync_attempt_at', '<', $stuckThreshold)
            ->get();

        if ($stuckAccounts->isEmpty()) {
            return;
        }

        $this->warn("Found {$stuckAccounts->count()} stuck accounts, clearing and flagging...");

        foreach ($stuckAccounts as $account) {
            $account->increment('sync_stuck_count');
            $account->refresh();

            if ($account->sync_stuck_count >= 3) {
                $account->update([
                    'sync_status' => 'flagged',
                    'sync_error' => "Account repeatedly gets stuck in sync (stuck {$account->sync_stuck_count} times) - requires manual review",
                    'sync_flag_reason' => 'repeated_stuck_jobs',
                    'sync_flagged_at' => now(),
                ]);
                $this->error("Flagged account {$account->code} - stuck {$account->sync_stuck_count} times");
            } else {
                $account->update([
                    'sync_status' => 'needs_retry',
                    'sync_error' => "Job stuck #{$account->sync_stuck_count} - cleared for retry",
                ]);
                $this->info("Reset stuck account {$account->code} for retry (stuck count: {$account->sync_stuck_count})");
            }
        }
    }



    // ─────────────────────────────────────────────────────────
    //  Status & Management Subcommands
    // ─────────────────────────────────────────────────────────

    protected function showSyncStatus(): void
    {
        $this->info("=== Unified Deal Sync Status Overview ===");

        $pendingJobs = $this->getPendingJobsCount();
        $this->info("Current SyncDealsJob queue (syncaccountstrades): {$pendingJobs} pending jobs");
        $this->info("");

        $baseQuery = fn() => Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('code', '<>', 'Rejected')
            ->whereRaw("( (competition_product_id IS NULL AND demo = 0)
                    OR (competition_product_id IS NOT NULL AND demo = 1) )");

        $totalAccounts = $baseQuery()->count();
        $neverSynced = $baseQuery()->whereNull('deals_synced_to')->count();
        $syncedToday = $baseQuery()->where('deals_synced_to', '>=', now()->subDay())->count();
        $stale6h = $baseQuery()->whereNotNull('deals_synced_to')->where('deals_synced_to', '<', now()->subHours(6))->count();
        $stale24h = $baseQuery()->whereNotNull('deals_synced_to')->where('deals_synced_to', '<', now()->subDay())->count();
        $retryAccounts = $baseQuery()->where('sync_status', 'needs_retry')->count();
        $flaggedAccounts = $baseQuery()->where('sync_status', 'flagged')->count();

        $this->table(['Status', 'Count'], [
            ['Current Queue Jobs (SyncDealsJob)', $pendingJobs],
            ['Needs Retry', $retryAccounts],
            ['Flagged Problematic Accounts', $flaggedAccounts],
            ['Total Eligible Accounts', $totalAccounts],
            ['Never Synced (deals_synced_to NULL)', $neverSynced],
            ['Synced in last 24h', $syncedToday],
            ['Stale (>6 hours)', $stale6h],
            ['Very Stale (>24 hours)', $stale24h],
        ]);

        if ($flaggedAccounts > 0) {
            $this->info("\n=== Flagged Accounts Details ===");
            $flagged = $baseQuery()->where('sync_status', 'flagged')
                ->select('code', 'sync_flag_reason', 'sync_flagged_at', 'sync_stuck_count', 'sync_error')
                ->get();

            $rows = $flagged->map(fn($a) => [
                $a->code,
                $a->sync_flag_reason,
                $a->sync_stuck_count,
                $a->sync_flagged_at ? Carbon::parse($a->sync_flagged_at)->format('Y-m-d H:i') : 'N/A',
                substr($a->sync_error ?? '', 0, 50) . '...',
            ])->toArray();

            $this->table(['Account', 'Flag Reason', 'Stuck Count', 'Flagged At', 'Error'], $rows);
            $this->info("Use --unflag-account=CODE to manually unflag an account");
        }

        // Sync status distribution
        $statusCounts = $baseQuery()
            ->selectRaw('sync_status, COUNT(*) as count')
            ->groupBy('sync_status')
            ->get();

        if ($statusCounts->isNotEmpty()) {
            $this->info("\n=== Sync Status Distribution ===");
            $this->table(['Status', 'Count'], $statusCounts->map(fn($i) => [$i->sync_status ?: 'null', $i->count])->toArray());
        }
    }

    protected function unflagAccount(string $accountCode): void
    {
        $account = Account::where('code', $accountCode)->first();

        if (!$account) {
            $this->error("Account {$accountCode} not found");
            return;
        }

        if ($account->sync_status !== 'flagged') {
            $this->info("Account {$accountCode} is not flagged (current status: {$account->sync_status})");
            return;
        }

        $account->update([
            'sync_status' => 'needs_retry',
            'sync_error' => 'Manually unflagged - cleared for retry',
            'sync_flag_reason' => null,
            'sync_flagged_at' => null,
            'sync_stuck_count' => 0,
        ]);
        $this->info("Account {$accountCode} has been unflagged and reset for sync");
    }

    protected function clearAllStuckCache(): void
    {
        $updated = Account::whereIn('sync_status', ['pending', 'flagged'])
            ->where('last_sync_attempt_at', '<', now()->subMinutes(45))
            ->update([
                'sync_status' => 'needs_retry',
                'sync_error' => 'Manually cleared stuck state',
                'sync_stuck_count' => 0,
                'sync_flag_reason' => null,
                'sync_flagged_at' => null,
            ]);
        $this->info("Reset {$updated} stuck/flagged accounts to needs_retry");
    }
}
