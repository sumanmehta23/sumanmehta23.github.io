<?php

namespace App\Console\Commands;

use App\Models\Ib1;
use App\Models\Account;
use App\Models\IbPlanDetails;
use Illuminate\Console\Command;
use App\Jobs\SyncAccountTradesJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SyncAccountTrades extends Command
{
    protected $signature = 'app:sync-account-trades {--batch-size=10 : Number of accounts per job} {--max-jobs=50 : Maximum number of jobs to create} {--active-only : Only sync accounts with recent activity}';
    protected $description = 'Sync account trades for IBs';

    public function handle()
    {
        $batchSize = (int) $this->option('batch-size');
        $maxJobs = (int) $this->option('max-jobs');
        $activeOnly = $this->option('active-only');

        $totalJobsCreated = 0;
        Ib1::with(['planDetails', 'user'])  // Eager load related models
            ->where('status', 1)
            // ->where('email', 'zhawk1@protonmail.com')
            ->whereNotNull('ib_plan_details_id')
            ->cursor()  // More memory efficient for large datasets
            ->each(function ($ib1) use ($batchSize, $maxJobs, $activeOnly, &$totalJobsCreated) {
                $plan_id = $ib1->planDetails->ib_category_id ?? null;

                if (!$plan_id) return;

                $userId = $ib1->user_id;
                $referral_code = $ib1->referral_code ?: $ib1->email;

                // Cache IB Plans
                $ibPlans = Cache::remember("ibPlans:$userId", 3600, function () use ($plan_id) {
                    return IbPlanDetails::where('ib_category_id', $plan_id)
                        ->where('status', 1)
                        ->whereNull('deleted_at')
                        ->get()
                        ->toArray();
                });

                // Transform IB Plans for easy access
                $ib_acc_plans = [];
                foreach ($ibPlans as $plan) {
                    for ($i = 1; $i <= $plan['level_id']; $i++) {
                        $ib_acc_plans[$plan['account_type_id']][$plan['level_id']]["d$i"] = $plan["d$i"];
                    }
                }

                // Fetch accounts in smaller batches - only those with recent activity
                $accountQuery = Account::select('id', 'code', 'user_id', 'account_type_id', 'last_trade_at')
                    ->where('demo', false)
                    ->where('account_request_status', 1);

                // Apply activity filter if requested
                if ($activeOnly) {
                    $accountQuery->where(function ($query) {
                        $query->where('last_trade_at', '>=', now()->subDays(30))
                            ->orWhereNull('last_trade_at');
                    });
                }

                $accountQuery->whereHas(
                    'user',
                    fn($query) =>
                    $query->where(function ($q) use ($referral_code) {
                        for ($i = 1; $i <= 15; $i++) {
                            $q->orWhere("ib$i", $referral_code);
                        }
                    })->where('status', 1)
                )
                    ->chunk(500, function ($accounts) use ($referral_code, $userId, $ib_acc_plans, $batchSize, &$totalJobsCreated, $maxJobs) {
                        // Stop creating jobs if we've reached the limit
                        if ($totalJobsCreated >= $maxJobs) {
                            return false; // Stop chunking
                        }

                        // Process accounts in smaller batches within each job
                        $accountChunks = $accounts->chunk($batchSize);
                        $jobs = [];

                        foreach ($accountChunks as $accountChunk) {
                            if ($totalJobsCreated >= $maxJobs) {
                                break;
                            }

                            $accountIds = $accountChunk->pluck('id')->toArray();
                            $jobs[] = new SyncAccountTradesJob($accountIds, $referral_code, $userId, $ib_acc_plans);
                            $totalJobsCreated++;
                        }

                        // Dispatch jobs in batches
                        if (!empty($jobs)) {
                            Bus::batch($jobs)->onQueue('syncaccountstrades')->dispatch();
                        }
                    });
            });
    }
}
