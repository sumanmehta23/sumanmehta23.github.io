<?php

namespace App\Console\Commands;

use App\Jobs\SyncDealsJob;
use App\Models\Ib1;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncDeals extends Command
{
    protected $signature = 'app:sync-deals-v2
        {--batch-size=10 : Number of accounts per job}
        {--max-jobs=0 : Maximum number of jobs to create (0=unlimited)}
        {--max-pages=20 : Maximum pages per account per sync}
        {--active-only : Only sync accounts with recent activity}
        {--email= : Sync only accounts under a specific IB email}
        {--code= : Sync only for a specific account code}';

    protected $description = 'Deal-based sync v2: fetch MT5 deals, detect closes, distribute commission inline';

    public function handle()
    {
        $batchSize = max(1, (int) $this->option('batch-size'));
        $maxJobs = (int) $this->option('max-jobs');
        $maxPages = max(1, (int) $this->option('max-pages'));
        $activeOnly = $this->option('active-only');
        $email = $this->option('email');
        $code = $this->option('code');
        $totalJobsCreated = 0;

        $startTime = microtime(true);
        Log::info('SyncDeals command started', compact('batchSize', 'maxJobs', 'maxPages', 'activeOnly', 'email', 'code'));

        // ── Query accounts that have at least one IB in their referral chain ──
        $accountQuery = DB::table('accounts as a')
            ->join('aspnetusers as u', 'a.user_id', '=', 'u.id')
            ->select('a.id')
            ->where('a.demo', false)
            ->where('a.account_request_status', 1)
            ->whereNull('a.deleted_at')
            ->where('u.status', 1)
            ->whereNotNull('u.ib1');

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

        // Prioritize accounts that haven't been synced recently or at all
        $accountQuery->orderByRaw('COALESCE(a.deals_synced_to, "2000-01-01") ASC');

        $totalAccounts = $accountQuery->count();
        $this->info("Total accounts to sync: {$totalAccounts}");

        // ── Dispatch SyncDealsJob batches ──
        // Each job: fetches MT5 deals → inserts to deals table → detects closes → dispatches commission
        $accountQuery->chunk($batchSize * 50, function ($accounts) use ($batchSize, $maxJobs, $maxPages, &$totalJobsCreated) {
            $batched = $accounts->pluck('id')->chunk($batchSize);

            foreach ($batched as $accountIdBatch) {
                if ($maxJobs > 0 && $totalJobsCreated >= $maxJobs) {
                    $this->info("Reached maximum job limit of {$maxJobs}.");
                    return false;
                }

                SyncDealsJob::dispatch($accountIdBatch->values()->toArray(), $maxPages)
                    ->onQueue('syncaccountstrades');
                $totalJobsCreated++;
            }
        });

        $duration = round(microtime(true) - $startTime, 2);
        $this->info("Dispatched {$totalJobsCreated} SyncDealsJob(s) for {$totalAccounts} accounts in {$duration}s.");
        $this->info("Commission distribution happens inline — no separate commission jobs needed.");

        Log::info('SyncDeals command completed', [
            'sync_jobs' => $totalJobsCreated,
            'total_accounts' => $totalAccounts,
            'duration_seconds' => $duration,
        ]);
    }
}
