<?php

namespace App\Console\Commands;

use App\Jobs\DistributeIbCommissionJob;
use App\Jobs\SyncDealsJob;
use App\Models\Ib1;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncAccountTrades extends Command
{
    protected $signature = 'app:sync-account-trades
        {--batch-size=10 : Number of accounts per job}
        {--max-jobs=0 : Maximum number of jobs to create (0=unlimited)}
        {--active-only : Only sync accounts with recent activity}
        {--email= : Sync only accounts under a specific IB email}
        {--code= : Sync only for a specific account code}
        {--backfill-commissions : Also dispatch legacy DistributeIbCommissionJob for unprocessed commissions}';

    protected $description = 'Sync account deals from MT5 and distribute IB commissions';

    public function handle()
    {
        $batchSize = max(1, (int) $this->option('batch-size'));
        $maxJobs = (int) $this->option('max-jobs');
        $activeOnly = $this->option('active-only');
        $email = $this->option('email');
        $code = $this->option('code');
        $totalJobsCreated = 0;

        Log::info('SyncAccountTrades command started (deal-based)', compact('batchSize', 'maxJobs', 'activeOnly', 'email', 'code'));

        // ── STEP 1: Query accounts ──
        $accountQuery = DB::table('accounts as a')
            ->join('aspnetusers as u', 'a.user_id', '=', 'u.id')
            ->select('a.id', 'a.code', 'a.user_id')
            ->where('a.demo', false)
            ->where('a.account_request_status', 1)
            ->whereNull('a.deleted_at')
            ->where('u.status', 1)
            ->where(function ($q) {
                // Exclude accounts marked as not found in MT5
                $q->whereNull('a.deletion_type')
                    ->orWhere('a.deletion_type', 'not like', '%not_found%');
            })
            ->where(function ($q) {
                $q->whereNotNull('u.ib1');
            });

        if ($email) {
            $ib = Ib1::where('email', $email)->where('status', 1)->first();
            if (!$ib) {
                $this->error("No active IB found with email: {$email}");
                return;
            }
            $referralCode = $ib->referral_code ?: $ib->email;
            $accountQuery->where(function ($q) use ($referralCode) {
                for ($i = 1; $i <= 15; $i++) {
                    $q->orWhere("u.ib{$i}", $referralCode);
                }
            });
            $this->info("Filtering accounts under IB: {$referralCode} ({$email})");
        }

        if ($code) {
            $accountQuery->where('a.code', $code);
        }

        if ($activeOnly) {
            $accountQuery->where(function ($q) {
                $q->where('a.last_trade_at', '>=', now()->subDays(30))
                    ->orWhereNull('a.last_trade_at');
            });
        }

        // Oldest-synced first so stale accounts get priority
        $accountQuery->orderByRaw('COALESCE(a.deals_synced_to, "2000-01-01") ASC');

        $totalAccounts = $accountQuery->count();
        $this->info("Total accounts to sync: {$totalAccounts}");

        // ── STEP 2: Dispatch SyncDealsJob batches ──
        // SyncDealsJob handles: MT5 deal fetch → deals table → detect closes → commission distribution
        // No separate commission dispatch step needed.
        $accountQuery->chunk($batchSize * 50, function ($accounts) use ($batchSize, $maxJobs, &$totalJobsCreated) {
            $batched = $accounts->pluck('id')->chunk($batchSize);

            foreach ($batched as $accountIdBatch) {
                if ($maxJobs > 0 && $totalJobsCreated >= $maxJobs) {
                    $this->info("Reached maximum job limit of {$maxJobs}.");
                    return false;
                }

                SyncDealsJob::dispatch($accountIdBatch->values()->toArray())
                    ->onQueue('syncaccountstrades');
                $totalJobsCreated++;
            }
        });

        $this->info("Dispatched {$totalJobsCreated} SyncDealsJob(s) for {$totalAccounts} accounts.");

        // ── STEP 3 (Optional): Backfill legacy unprocessed commissions ──
        // Only needed during transition period while old ib1_commission records still have status=0
        if ($this->option('backfill-commissions')) {
            $this->dispatchLegacyCommissionJobs($email, $code);
        }

        Log::info('SyncAccountTrades command completed', [
            'sync_jobs' => $totalJobsCreated,
            'total_accounts' => $totalAccounts,
        ]);
    }

    /**
     * Dispatch DistributeIbCommissionJob for legacy unprocessed ib1_commission records.
     * This is only needed during the transition period from order-based to deal-based sync.
     */
    protected function dispatchLegacyCommissionJobs(?string $email, ?string $code): void
    {
        $this->info("Checking for legacy unprocessed commissions...");

        $unprocessedCount = DB::table('ib1_commission')
            ->where('orderstate', 4)
            ->whereNotIn('status', [1, 10])
            ->count();

        if ($unprocessedCount === 0) {
            $this->info("No legacy unprocessed commissions. Backfill not needed.");
            return;
        }

        $this->info("Found {$unprocessedCount} legacy unprocessed commissions. Dispatching jobs...");

        // Find distinct IB codes with unprocessed work
        $unprocessedQuery = DB::table('ib1_commission as c')
            ->join('aspnetusers as u', 'c.user_id', '=', 'u.id')
            ->where('c.orderstate', 4)
            ->whereNotIn('c.status', [1, 10]);

        if ($code) {
            $unprocessedQuery->where('c.code', $code);
        }

        $ibCodesWithWork = collect();
        for ($i = 1; $i <= 15; $i++) {
            $codes = (clone $unprocessedQuery)
                ->whereNotNull("u.ib{$i}")
                ->where("u.ib{$i}", '!=', '')
                ->distinct()
                ->pluck("u.ib{$i}");
            $ibCodesWithWork = $ibCodesWithWork->merge($codes);
        }
        $ibCodesWithWork = $ibCodesWithWork->unique()->values();

        if ($ibCodesWithWork->isEmpty()) {
            $this->info("No unprocessed commissions found. Skipping.");
            return;
        }

        $ibQuery = Ib1::where('status', 1)
            ->whereNotNull('ib_plan_details_id')
            ->whereNotNull('user_id');

        if ($email) {
            $ibQuery->where('email', $email);
        }

        $ibQuery->where(function ($q) use ($ibCodesWithWork) {
            $q->whereIn('referral_code', $ibCodesWithWork)
                ->orWhereIn('email', $ibCodesWithWork);
        });

        $commissionJobsCreated = 0;
        $ibQuery->cursor()->each(function ($ib) use (&$commissionJobsCreated) {
            DistributeIbCommissionJob::dispatch(
                $ib->referral_code ?: $ib->email,
                $ib->user_id
            )->onQueue('distributeibcommission');
            $commissionJobsCreated++;
        });

        $this->info("Dispatched {$commissionJobsCreated} legacy DistributeIbCommissionJob(s).");
    }
}
