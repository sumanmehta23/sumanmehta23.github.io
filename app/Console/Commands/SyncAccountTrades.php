<?php

namespace App\Console\Commands;

use App\Models\Ib1;
use App\Models\Account;
use App\Models\IbPlanDetails;
use Illuminate\Console\Command;
use App\Jobs\SyncAccountTradesJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SyncAccountTrades extends Command
{
    protected $totalAccountsProcessed = 0;
    protected $signature = 'app:sync-account-trades {--batch-size=10 : Number of accounts per job} {--max-jobs=500 : Maximum number of jobs to create} {--active-only : Only sync accounts with recent activity} {--email= : Sync only for a specific IB email} {--code= : Sync only for a specific account code}';
    protected $description = 'Sync account trades for IBs';

    // private function interpolateQuery($query, $bindings)
    // {
    //     foreach ($bindings as $binding) {
    //         // Quote strings, leave numbers as is
    //         $binding = is_numeric($binding) ? $binding : "'" . addslashes($binding) . "'";
    //         $query = preg_replace('/\?/', $binding, $query, 1);
    //     }
    //     return $query;
    // }
    public function handle()
    {
        // Log::info('Starting SyncAccountTrades command every x minutes');
        // DB::listen(function ($query) {
        //     $fullSql = $this->interpolateQuery($query->sql, $query->bindings);
        //     Log::info("Full SQL: $fullSql");
        // });
        $batchSize = (int) $this->option('batch-size');
        $maxJobs = (int) $this->option('max-jobs');
        $activeOnly = $this->option('active-only');
        $email = $this->option('email');
        $code = $this->option('code');

        $totalJobsCreated = 0;
        $ibQuery = Ib1::with(['planDetails', 'user'])
            ->where('status', 1)
            ->whereNotNull('ib_plan_details_id');

        // Apply email filter only if provided
        if ($email) {
            $ibQuery->where('email', $email);
        }

        $ibQuery->cursor()  // More memory efficient for large datasets
            ->each(function ($ib1) use ($batchSize, $maxJobs, $activeOnly, $code, &$totalJobsCreated) {
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

                // Apply code filter if provided
                if ($code) {
                    // Log::info("synced account for code : $code");
                    $accountQuery->where('code', $code);
                }

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
                    ->chunk(5000, function ($accounts) use ($referral_code, $userId, $ib_acc_plans, $batchSize, &$totalJobsCreated, $maxJobs) {
                        $this->totalAccountsProcessed += $accounts->count();
                        // Stop creating jobs if we've reached the limit
                        if ($totalJobsCreated >= $maxJobs) {
                            $this->info("Reached maximum job limit of $maxJobs. Stopping further job creation.");
                            return false; // Stop chunking
                        }
                        $this->info("Processing accounts for IB: $referral_code, User ID: $userId");
                        // Process accounts in smaller batches within each job
                        $accountChunks = $accounts->chunk($batchSize);
                        $jobs = [];
                        foreach ($accountChunks as $accountChunk) {
                            if ($totalJobsCreated >= $maxJobs) {
                                $this->info("Reached maximum job limits of $maxJobs. Stopping further job creation.");
                                break;
                            }

                            $accountIds = $accountChunk->pluck('id')->toArray();
                            Log::info("sync for ibs".json_encode($accountIds));
                            Log::info("sync for ibs".json_encode($referral_code));
                            Log::info("sync for ibs".json_encode($userId));
                            Log::info("sync for ibs".json_encode($ib_acc_plans));
                            $this->info("Dispatching sync for accounts: " . implode(', ', $accountIds));
                            if (in_array('9fbb706d-e237-488c-a319-16d52d2e36d2', $accountIds)) {
                                $this->info('Dispatching sync for 505255');
                                Log::info('dispaching sync for 505255');
                            }

                            $jobs[] = new SyncAccountTradesJob($accountIds, $referral_code, $userId, $ib_acc_plans);
                            $totalJobsCreated++;
                        }

                        // Dispatch jobs in batches
                        if (!empty($jobs)) {
                            Bus::batch($jobs)->onQueue('syncaccountstrades')->dispatch();
                        }
                    });
            });
        $this->info("Total jobs created: $totalJobsCreated");
        $this->info("Total accounts processed: $this->totalAccountsProcessed");
    }
}
