<?php

namespace App\Jobs;

use Exception;
use App\Models\Ib1;
use App\Models\IbWallet;
use Illuminate\Support\Str;
use App\Models\Ib1Commission;
use App\Models\IbPlanDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class DistributeIbCommissionJob implements ShouldQueue
{
    use Queueable;
    protected $referral_code;
    protected $userId;
    protected $ib_acc_plans;
    protected $accountId;
    /**
     * Create a new job instance.
     */
    public function __construct($referral_code, $userId, $ib_acc_plans, $accountId)
    {
        $this->referral_code = $referral_code;
        $this->userId = $userId;
        $this->ib_acc_plans = $ib_acc_plans;
        $this->accountId = $accountId;
        $this->onQueue('distributeibcommission');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        //Find all parent Ib of current account owner and distribute commission , change status of commission to 1
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");
        for ($i = 1; $i <= 15; $i++) {


            Ib1Commission::with(['user:id,email,ib1,ib2,ib3,ib4,ib5,ib6,ib7,ib8,ib9,ib10,ib11,ib12,ib13,ib14,ib15', 'account:id,account_type_id', 'ibWallet'])
                ->whereHas('user', function ($query) use ($i) {
                    $query->where("ib$i", $this->referral_code)->where('status', 1);
                })
                ->whereDoesntHave('ibWallet', function ($query) {
                    $query->where('user_id', $this->userId);
                })
                ->where('status', 0)
                ->where('orderstate', 4)
                ->chunk(10, function ($ibcommissions) use ($i) {
                    $walletsToCreate = [];
                    foreach ($ibcommissions as $ca) {
                        for ($j = 1; $j <= 15; $j++) {
                            if ($ca->user->{'ib' . $j}) {
                                $ib1 = Cache::remember('ib1user:' . $ca->user->{'ib' . $j}, 60 * 60, function () use ($ca, $j) {
                                    return Ib1::with('planDetails')->where('referral_code', $ca->user->{'ib' . $j})->first();
                                });

                                $plan_id = $ib1->planDetails->ib_category_id;
                                if ($plan_id) {
                                    $ib_acc_plans = $this->getIbPlanDetails($ib1->user_id, $plan_id);
                                    $ib_level = $j;
                                    if (in_array($this->referral_code, ['sensei', 'wealthytrades', '66H5XC'])) {
                                        $commission = 3;
                                    } else {
                                        $commission = $ib_acc_plans[$ca->account->account_type_id][$ib_level]["d$i"] ?? null;
                                    }
                                    if ($commission) {

                                        // if ($ca->order_id == 311606) {
                                        //     info($this->referral_code);
                                        //     info(json_encode([$commission]));
                                        //     info(json_encode($ca));
                                        //     info(json_encode($ib_acc_plans));
                                        // }
                                        $ib_level_name = "IB Level $ib_level - D$i";
                                        $ib_wallet = ((float)$commission) * $ca->volume;

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
                            // dump($walletsToCreate);
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
}
