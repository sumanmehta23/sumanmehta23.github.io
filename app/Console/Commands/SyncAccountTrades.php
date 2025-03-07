<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Ib1;
use App\Models\User;
use App\Models\Symbol;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\IbWallet;
use Illuminate\Support\Str;
use App\Services\MT5Service;
use App\Models\Ib1Commission;
use App\Models\IbPlanDetails;
use Illuminate\Console\Command;
use App\Jobs\SyncAccountTradesJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SyncAccountTrades extends Command
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
    protected $signature = 'app:sync-account-trades';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
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
        //Get all IB1 accounts

        $ib_wallet = 0.00;
        Ib1::with('planDetails')
            ->where('status', 1)
            ->whereNotNull('ib_plan_details_id')
            ->chunk(100, function ($ib1s) {
                foreach ($ib1s as $ib1) {
                    $plan_id = $ib1->planDetails->ib_category_id;
                    if ($plan_id) {
                        $userId = $ib1->user_id;
                        info('Syncing account trades for IB1: ' . $ib1->id);
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
                        for ($i = 1; $i <= 15; $i++) {
                            Account::select('id', 'code', 'user_id', 'account_type_id')
                                ->where('demo', false)
                                ->where('account_request_status', 1)
                                ->whereHas('user', function ($query) use ($referral_code, $i) {
                                    $query->where("ib$i", $referral_code)->where('status', 1);
                                })
                                ->chunk(100, function ($clientLiveAccs) use ($referral_code, $i, $ib_acc_plans, $userId) {
                                    foreach ($clientLiveAccs as $client) {
                                        // ($accountId, $referral_code, $ib_user_id, $ib_acc_plans)
                                        SyncAccountTradesJob::dispatch($client->id, $referral_code, $userId, $ib_acc_plans)->onQueue('syncaccountstrades');
                                    }
                                });
                        }
                    }
                }
            });
    }
    protected function distribute()
    {
        for ($i = 1; $i <= 15; $i++) {
            // DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");

            $ibCommissions = Ib1Commission::with(['user:id,email,ib1,ib2,ib3,ib4,ib5,ib6,ib7,ib8,ib9,ib10,ib11,ib12,ib13,ib14,ib15', 'account:id,account_type_id', 'ibWallet'])
                ->whereHas('user', function ($query) use ($i) {
                    $query->where("ib$i", $this->referral_code)->where('status', 1);
                })
                ->whereDoesntHave('ibWallet', function ($query) {
                    $query->where('user_id', $this->userId);
                })
                ->where('status', 0)

                ->chunk(5, function ($ibcommissions) use ($i) {
                    $walletsToCreate = [];
                    foreach ($ibcommissions as $ca) {
                        // dump($ca->user);
                        for ($j = 1; $j <= 15; $j++) {
                            if ($ca->user->{'ib' . $j}) {
                                $ib1 = Cache::remember('ib1user:' . $ca->user->{'ib' . $j}, 60 * 60, function () use ($ca, $j) {
                                    return Ib1::with('planDetails')->where('referral_code', $ca->user->{'ib' . $j})->first();
                                });

                                $plan_id = $ib1->planDetails->ib_category_id;
                                if ($plan_id) {
                                    $ib_acc_plans = $this->getIbPlanDetails($ib1->user_id, $plan_id);
                                    // dump($ib_acc_plans);
                                    // dd($ca->account);
                                    $ib_level = $j;
                                    $commission = $ib_acc_plans[$ca->account->account_type_id][$ib_level]["d$i"] ?? null;
                                    // dd($commission);
                                    if ($commission) {
                                        info('Distributing commission for IB1: ' . $ca->id);

                                        $ib_level_name = "IB Level $ib_level - D$i";
                                        $ib_wallet = ((float)$commission / 2) * $ca->volume;

                                        $formatted_ib_wallet = number_format($ib_wallet, 10, '.', '');

                                        if ($formatted_ib_wallet < 0.0000001) {
                                            $formatted_ib_wallet = '0.0000000000'; // Handle small values
                                        }
                                        $existingWallet = IbWallet::where('user_id', $this->userId)
                                            ->where('order_id', $ca->order_id)
                                            ->exists();

                                        if (!$existingWallet) {
                                            $walletsToCreate[] = [
                                                'id' => (string)Str::orderedUuid(),
                                                'ib_wallet' => $formatted_ib_wallet,
                                                'email' => $this->referral_code,
                                                'code' => $ca->code,
                                                'user_id' => $this->userId,
                                                'account_id' => $ca->account->id,
                                                'order_id' => $ca->order_id,
                                                'ib1_commission_id' => $ca->id,
                                                'ib_level' => $ib_level_name,
                                                'created_at' => now(),
                                                'updated_at' => now(),
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                        // dd([12, 4]);
                        // $ib_level = collect(range(1, 15))->takeWhile(fn($iter) => $ca->user->{'ib' . $iter} !== null)->count();
                        // $commission = $this->ib_acc_plans[$ca->account->account_type_id][$ib_level]["d$i"] ?? null;
                        // if ($commission) {
                        //     $ib_level_name = "IB Level $ib_level - D$i";
                        //     $ib_wallet = ((float)$commission / 2) * $ca->volume;

                        //     $formatted_ib_wallet = number_format($ib_wallet, 10, '.', '');

                        //     if ($formatted_ib_wallet < 0.0000001) {
                        //         $formatted_ib_wallet = '0.0000000000'; // Handle small values
                        //     }
                        //     $existingWallet = IbWallet::where('user_id', $this->userId)
                        //         ->where('order_id', $ca->order_id)
                        //         ->exists();

                        //     if (!$existingWallet) {
                        //         $walletsToCreate[] = [
                        //             'id' => (string)Str::orderedUuid(),
                        //             'ib_wallet' => $formatted_ib_wallet,
                        //             'email' => $this->referral_code,
                        //             'code' => $ca->code,
                        //             'user_id' => $this->userId,
                        //             'account_id' => $ca->account->id,
                        //             'order_id' => $ca->order_id,
                        //             'ib1_commission_id' => $ca->id,
                        //             'ib_level' => $ib_level_name,
                        //             'created_at' => now(),
                        //             'updated_at' => now(),
                        //         ];
                        //     }
                        // }
                        $ca->status = 1;
                        $ca->save();
                    }

                    if (count($walletsToCreate) > 0) {
                        try {
                            // dd($walletsToCreate);
                            IbWallet::insert($walletsToCreate);
                        } catch (Exception $e) {
                            logger()->error('Error inserting IB wallet records: ' . $e->getMessage());
                        }
                    }
                });
        }
    }

    private function getIbPlanDetails($user, $plan_id)
    {
        $ibPlans = Cache::remember('ibPlans:' . $user, 60 * 60, function () use ($plan_id) {
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
        return $ib_acc_plans;
    }
    protected function getIbTree($userId)
    {
        $ibTree = [];

        $user = User::find($userId);
        for ($i = 1; $i <= 15; $i++) {
            if ($user->{'ib' . $i}) {
                $ibTree[$i] = $user->{'ib' . $i};
            }
        }
        return $ibTree;
    }
}
