<?php

namespace App\Console\Commands;

use App\Models\Ib1;
use App\Models\Account;
use App\Models\IbPlanDetails;
use App\Jobs\SyncAccountTradesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Bus;

class SyncAccountTrades extends Command
{
    protected $signature = 'app:sync-account-trades';
    protected $description = 'Sync account trades for IBs';

    public function handle()
    {
        Ib1::with(['planDetails', 'user'])  // Eager load related models
        ->where('status', 1)
            ->whereNotNull('ib_plan_details_id')
            ->cursor()  // More memory efficient for large datasets
            ->each(function ($ib1) {
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

                // Fetch accounts in smaller batches
                Account::select('id', 'code', 'user_id', 'account_type_id')
                    ->where('demo', false)
                    ->where('code',458588)
                    ->where('account_request_status', 1)
                    ->whereHas('user', fn ($query) =>
                    $query->where(function ($q) use ($referral_code) {
                        for ($i = 1; $i <= 15; $i++) {
                            $q->orWhere("ib$i", $referral_code);
                        }
                    })->where('status', 1)
                    )
                    ->chunk(500, function ($accounts) use ($referral_code, $userId, $ib_acc_plans) {
                        $jobs = [];

                        foreach ($accounts as $client) {
                            Log::info('Accounts to sync commission : ' . $client->code);
                            $jobs[] = new SyncAccountTradesJob($client->id, $referral_code, $userId, $ib_acc_plans);
                        }

                        // Dispatch jobs in batches of 500
                        if (!empty($jobs)) {
                            Bus::batch($jobs)->onQueue('syncaccountstrades')->dispatch();
                        }
                    });
            });
    }
}
