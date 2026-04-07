<?php

namespace App\Console\Commands;

use App\Jobs\SyncAccountTradesJob;
use App\Jobs\DistributeIbCommissionJob;
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
        {--code= : Sync only for a specific account code}';

    protected $description = 'Sync account trades and distribute IB commissions';

    public function handle()
    {
        $batchSize = max(1, (int) $this->option('batch-size'));
        $maxJobs = (int) $this->option('max-jobs');
        $activeOnly = $this->option('active-only');
        $email = $this->option('email');
        $code = $this->option('code');
        $totalJobsCreated = 0;

        Log::info('SyncAccountTrades command started', compact('batchSize', 'maxJobs', 'activeOnly', 'email', 'code'));

        // ── STEP 1: Query accounts directly (account-centric, not IB-centric) ──
        // Instead of looping 4,503 IBs and finding accounts per IB,
        // query the ~25K accounts once and batch them into jobs.
        $accountQuery = DB::table('accounts as a')
            ->join('aspnetusers as u', 'a.user_id', '=', 'u.id')
            ->select('a.id', 'a.code', 'a.user_id')
            ->where('a.demo', false)
            ->where('a.account_request_status', 1)
            ->whereNull('a.deleted_at')
            ->where('u.status', 1)
            // Must have at least one IB in the chain
            ->where(function ($q) {
                $q->whereNotNull('u.ib1');
            });

        // Apply email filter: find the IB's referral_code, then filter accounts
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

        // Oldest-synced first for fairness
        $accountQuery->orderBy('a.last_trade_at', 'ASC');

        $totalAccounts = $accountQuery->count();
        $this->info("Total accounts to sync: {$totalAccounts}");

        // ── STEP 2: Batch accounts into jobs ──
        // Group N accounts per job instead of 1-per-job.
        // 25K accounts ÷ 10 per batch = 2,500 jobs (vs 25K jobs before).
        $accountQuery->chunk($batchSize * 50, function ($accounts) use ($batchSize, $maxJobs, &$totalJobsCreated) {
            $batched = $accounts->pluck('id')->chunk($batchSize);

            foreach ($batched as $accountIdBatch) {
                if ($maxJobs > 0 && $totalJobsCreated >= $maxJobs) {
                    $this->info("Reached maximum job limit of {$maxJobs}.");
                    return false;
                }

                SyncAccountTradesJob::dispatch($accountIdBatch->values()->toArray())
                    ->onQueue('syncaccountstrades');
                $totalJobsCreated++;
            }
        });

        $this->info("Dispatched {$totalJobsCreated} SyncAccountTradesJob(s) for {$totalAccounts} accounts.");

        // ── STEP 3: Dispatch ONE DistributeIbCommissionJob per unique IB ──
        // Instead of 11K duplicate commission jobs, dispatch exactly 1 per IB.
        $this->dispatchCommissionJobs($email, $code);

        Log::info('SyncAccountTrades command completed', [
            'sync_jobs' => $totalJobsCreated,
            'total_accounts' => $totalAccounts,
        ]);
    }

    protected function dispatchCommissionJobs(?string $email, ?string $code): void
    {
        // Step 1: Find all distinct IB referral codes that appear in unprocessed commissions
        // This is ONE query instead of 4,500+ individual exists() checks
        $unprocessedQuery = DB::table('ib1_commission as c')
            ->join('aspnetusers as u', 'c.user_id', '=', 'u.id')
            ->where('c.orderstate', 4)
            ->whereNotIn('c.status', [1, 10]);

        if ($code) {
            $unprocessedQuery->where('c.code', $code);
        }

        // Collect all distinct referral codes from ib1..ib15 columns in one pass
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
            $this->info("No unprocessed commissions found. Skipping commission dispatch.");
            return;
        }

        // Step 2: Match these codes to active IBs and dispatch jobs
        $ibQuery = Ib1::where('status', 1)
            ->whereNotNull('ib_plan_details_id')
            ->whereNotNull('user_id');

        if ($email) {
            $ibQuery->where('email', $email);
        }

        // Match IBs by referral_code or email being in the set of codes with work
        $ibQuery->where(function ($q) use ($ibCodesWithWork) {
            $q->whereIn('referral_code', $ibCodesWithWork)
                ->orWhereIn('email', $ibCodesWithWork);
        });

        $commissionJobsCreated = 0;

        $ibQuery->cursor()->each(function ($ib) use (&$commissionJobsCreated) {
            $referralCode = $ib->referral_code ?: $ib->email;
            $userId = $ib->user_id;

            DistributeIbCommissionJob::dispatch($referralCode, $userId, [], null)
                ->onQueue('distributeibcommission');
            $commissionJobsCreated++;
        });

        $this->info("Dispatched {$commissionJobsCreated} DistributeIbCommissionJob(s).");
    }
}
