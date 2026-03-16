<?php



namespace App\Console\Commands;

use App\Models\Ib1;
use App\Models\Account;
use App\Models\IbPlanDetails;
use Illuminate\Console\Command;
use App\Jobs\SyncAccountTradesJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

class SyncAccountsCommand extends Command
{
    protected $referral_code;
    protected $userId;
    protected $ib_acc_plans;
    protected $accountId;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:accounts-sync {--batch-size=10 : Number of accounts per job} {--max-jobs=50 : Maximum number of jobs to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync account trades for all IB accounts (optimized)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchSize = (int) $this->option('batch-size');
        $maxJobs = (int) $this->option('max-jobs');
        $totalJobsCreated = 0;
        // $data = json_decode('["wealthytrades","9dc911a4-93fa-44dc-94dc-ebd7958d2c07",{"9dc9119c-b2f9-4e8e-bda4-506cab114129":{"1":{"d1":"0.01"}},"9dc9119c-b2d3-49df-977e-a52df7e6f2be":{"1":{"d1":"0.01"}},"9dc9119c-b2ac-4568-a04b-b32c5a37c2a5":{"1":{"d1":"0.01"}},"9dc9119c-b285-40fe-94ce-21e004a4dd98":{"1":{"d1":"0.03"}},"9dc9119c-b25f-48d3-81b6-cfb5190f4fb9":{"1":{"d1":"0.01"}},"9dc9119c-b23b-47cf-b2c5-386ff30c2fef":{"1":{"d1":"0.01"}}},"9dc911ac-4671-42a6-9e8e-a7ef5be1b77f"]');

        // $this->referral_code = $data[0];
        // $this->userId = $data[1];
        // $this->ib_acc_plans = $data[2];
        // $this->accountId = $data[3];
        // $this->distribute();
        // return;
        // dump($this->getIbTree('9e5fd963-a1b3-4036-91b5-b15a73e47c34'));
        // return;
        // SyncAccountsTrades::dispatch('9dc911ac-4671-42a6-9e8e-a7ef5be1b77f');
        // return;
        // Get all IB1 accounts
        $ib_wallet = 0.00;
        Ib1::with('planDetails')
            ->where('status', 1)
            // ->where('referral_code', 'wealthytrades')
            ->whereNotNull('ib_plan_details_id')
            ->chunk(500, function ($ib1s) use ($batchSize, $maxJobs, &$totalJobsCreated) {

                foreach ($ib1s as $ib1) {
                    if ($totalJobsCreated >= $maxJobs) {
                        $this->info("Reached maximum jobs limit ({$maxJobs}). Stopping.");
                        return false; // Stop processing
                    }

                    $plan_id = isset($ib1->planDetails->ib_category_id) ? $ib1->planDetails->ib_category_id : '';
                    if ($plan_id) {
                        $userId = $ib1->user_id;
                        // info('Syncing account trades for IB1: ' . $ib1->id);
                        $ibPlans = Cache::remember('ibPlans:' . $userId, 60 * 60, function () use ($plan_id) {
                            return IbPlanDetails::where('ib_category_id', $plan_id)->where('status', 1)
                                ->whereNull('deleted_at')
                                ->get()
                                ->toArray();
                        });

                        $ib_acc_plans = [];
                        foreach ($ibPlans as $plan) {
                            $ib_acc_plans[$plan['account_type_id']][$plan['level_id']] = [];

                            for ($i = 1; $i <= $plan['level_id']; $i++) {
                                $ib_acc_plans[$plan['account_type_id']][$plan['level_id']]["d$i"] = $plan["d$i"];
                            }
                        }
                        $referral_code = $ib1->referral_code;
                        if (!$referral_code) {
                            $referral_code = $ib1->email;
                        }

                        // Collect all accounts for this IB across all levels
                        $allClientAccounts = collect();

                        for ($i = 1; $i <= 15; $i++) {
                            $accounts = Account::select('id', 'code', 'user_id', 'account_type_id')
                                ->where('demo', false)
                                // ->where('code', 637279)
                                ->where('account_request_status', 1)
                                ->where(function ($query) {
                                    $query->whereNull('sync_status')
                                        ->orWhereNotIn('sync_status', ['not_found_in_mt5', 'flagged']);
                                })
                                ->whereHas('user', function ($query) use ($referral_code, $i) {
                                    $query->where("ib$i", $referral_code)->where('status', 1);
                                })
                                ->get();

                            $allClientAccounts = $allClientAccounts->merge($accounts);
                        }

                        // Remove duplicates based on account ID
                        $allClientAccounts = $allClientAccounts->unique('id');

                        // Process accounts in batches
                        $accountChunks = $allClientAccounts->chunk($batchSize);
                        $jobs = [];

                        foreach ($accountChunks as $accountChunk) {
                            if ($totalJobsCreated >= $maxJobs) {
                                break;
                            }

                            $accountIds = $accountChunk->pluck('id')->toArray();
                            if (!empty($accountIds)) {
                                $jobs[] = new SyncAccountTradesJob($accountIds, $referral_code, $userId, $ib_acc_plans);
                                $totalJobsCreated++;
                            }
                        }

                        // Dispatch jobs in batches
                        if (!empty($jobs)) {
                            Bus::batch($jobs)->onQueue('syncaccountstrades')->dispatch();
                            $this->info("Created " . count($jobs) . " jobs for IB: {$referral_code} (Total: {$totalJobsCreated})");
                        }
                    }
                }
            });

        $this->info("Completed! Total jobs created: {$totalJobsCreated}");
    }
}
