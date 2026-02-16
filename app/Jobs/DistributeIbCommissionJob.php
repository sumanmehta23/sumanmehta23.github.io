<?php

namespace App\Jobs;

use App\Models\Ib1;
use App\Models\Ib1Commission;
use App\Models\IbPlanDetails;
use App\Models\IbWallet;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DistributeIbCommissionJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public $maxExceptions = 3;

    protected $referral_code;

    protected $userId;

    protected $ib_acc_plans;

    protected $accountId;

    protected $buffer = [];

    protected $finalResults = [];

    protected $processedtrades = [];

    protected $discardedIds = [];

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
        // Optimize for faster processing in high-volume scenarios
        $this->onConnection(config('queue.default'));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('referral_code ' . $this->referral_code . ' userId ' . $this->userId . ' accountId ' . $this->accountId);
            // Find all parent Ib of current account owner and distribute commission , change status of commission to 1
            DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");
            for ($i = 1; $i <= 15; $i++) {

                $this->buffer = [];
                $this->finalResults = [];
                try {
                    $ibcommissions = Ib1Commission::with(['user:id,email,ib1,ib2,ib3,ib4,ib5,ib6,ib7,ib8,ib9,ib10,ib11,ib12,ib13,ib14,ib15', 'account:id,account_type_id', 'ibWallet'])
                        ->whereHas('user', function ($query) use ($i) {
                            $query->where("ib$i", $this->referral_code)->where('status', 1);
                        })
                        ->whereDoesntHave('ibWallet', function ($query) {
                            $query->where('user_id', $this->userId);
                        })
                        // ->where('status', 0)
                        ->where('orderstate', 4)
                        ->orderBy('expert_position_id')
                        ->orderBy('time_closed')
                        //                ->cursor();
                        ->chunkById(500, function ($ibcommissions) use ($i) {
                            $finalResults = $walletsToCreate = [];
                            $mergedTrades = collect($this->buffer)->flatten(1)->merge($ibcommissions)->flatten(1);
                            $groupedTrades = $mergedTrades->groupBy('expert_position_id');
                            $newBuffer = [];

                            foreach ($groupedTrades as $positionId => $tradeGroup) {
                                try {
                                    // Ensure each tradeGroup is sorted by time_closed to correctly determine open and close trades
                                    $tradeGroup = $tradeGroup->sortBy('time_closed')->values();
                                    if ($tradeGroup->count() < 2) {
                                        // Store incomplete trades in buffer for the next chunk
                                        $newBuffer[$positionId] = $tradeGroup;

                                        continue;
                                    }

                                    // First trade is open, last trade is close
                                    $openTrade = $tradeGroup->first();
                                    $closeTrade = $tradeGroup->last();

                                    // Ensure they are not collections, just in case
                                    if (! ($openTrade instanceof \Illuminate\Database\Eloquent\Model)) {
                                        Log::warning('Unexpected open trade format for position ' . $positionId . ': ' . json_encode($tradeGroup));

                                        continue;
                                    }

                                    if (! ($closeTrade instanceof \Illuminate\Database\Eloquent\Model)) {
                                        Log::warning('Unexpected close trade format for position ' . $positionId . ': ' . json_encode($tradeGroup));

                                        continue;
                                    }

                                    // Now it should be safe to access properties
                                    $openTime = \Carbon\Carbon::parse($openTrade->time_closed);
                                    $closeTime = \Carbon\Carbon::parse($closeTrade->time_closed);
                                    $duration = $openTime->diffInSeconds($closeTime);

                                    if ($duration >= 10) {
                                        // Store valid trade
                                        $this->processedtrades[] = $closeTrade->expert_position_id;

                                        $finalResults[] = $closeTrade;
                                    } else {
                                        $this->discardedIds[] = $closeTrade->expert_position_id;
                                    }
                                } catch (Exception $e) {
                                    Log::error('Error processing trade group for position ' . $positionId . ': ' . $e->getMessage(), [
                                        'position_id' => $positionId,
                                        'trace' => $e->getTraceAsString(),
                                    ]);
                                    continue;
                                }
                            }

                            $this->buffer = $newBuffer;
                            $this->processTrades($finalResults, $i);
                        });
                } catch (Exception $e) {
                    Log::error('Error processing IB commissions for level ' . $i . ': ' . $e->getMessage(), [
                        'level' => $i,
                        'referral_code' => $this->referral_code,
                        'trace' => $e->getTraceAsString(),
                    ]);
                    continue;
                }
                // if (count($this->buffer) > 0) {
                //     $this->processTrades(collect($this->buffer)->flatten(1), $i);
                // }
            }

            // collect($this->processedtrades)->chunk(200)->each(function ($chunk) {
            //     Ib1Commission::whereIn('expert_position_id', $chunk)->update(['status' => 1]);
            // });

            if (! empty($this->discardedIds)) {
                collect($this->discardedIds)->chunk(200)->each(function ($chunk) {
                    try {
                        Ib1Commission::whereIn('expert_position_id', $chunk->toArray())->update(['status' => 10]);
                    } catch (Exception $e) {
                        Log::error('Error updating discarded commission records: ' . $e->getMessage(), [
                            'record_ids' => $chunk->toArray(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                });
            }
        } catch (Exception $e) {
            Log::error('Critical error in DistributeIbCommissionJob: ' . $e->getMessage(), [
                'referral_code' => $this->referral_code,
                'user_id' => $this->userId,
                'account_id' => $this->accountId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function processTrades($trades, $i): void
    {
        try {
            // Convert array to collection if needed
            if (is_array($trades)) {
                $trades = collect($trades);
            }

            if ($trades->isEmpty()) {
                return;
            }

            $walletsToCreate = [];

            // Batch load all existing wallets at once
            $orderIds = $trades->pluck('order_id')->unique();
            $existingWallets = IbWallet::where('user_id', $this->userId)
                ->whereIn('order_id', $orderIds)
                ->pluck('order_id')
                ->flip();

            // Collect all referral codes we'll need to look up in batch
            $referralCodesToLookup = [];
            foreach ($trades as $trade) {
                if ($trade->user) {
                    for ($j = 1; $j <= 15; $j++) {
                        $code = $trade->user->{'ib' . $j};
                        if ($code) {
                            $referralCodesToLookup[$code] = true;
                        }
                    }
                }
            }

            // Single batch database query for all IB users instead of per-user lookups
            $ibUsers = [];
            if (!empty($referralCodesToLookup)) {
                $ibUsers = Ib1::with('planDetails')
                    ->whereIn('referral_code', array_keys($referralCodesToLookup))
                    ->get()
                    ->keyBy('referral_code')
                    ->toArray();
            }

            foreach ($trades as $ca) {
                try {
                    // Log::info("sync tradess".json_encode($ca));
                    if (! $ca->user || ! $ca->account) {
                        Log::warning('Missing user or account relation for trade', [
                            'trade_id' => $ca->id,
                            'referral_code' => $this->referral_code,
                        ]);
                        continue;
                    }

                    $user = $ca->user;
                    $accountTypeId = $ca->account->account_type_id;
                    $orderId = $ca->order_id;

                    if (isset($existingWallets[$orderId])) {
                        continue; // Skip processing if wallet entry already exists
                    }

                    for ($j = 1; $j <= 15; $j++) {
                        $referralCode = $user->{'ib' . $j};
                        if (! $referralCode) {
                            continue;
                        }

                        // Cache IB user lookup to avoid duplicate DB calls
                        $ib1 = Cache::remember("ib1user:{$referralCode}", 3600, function () use ($referralCode) {
                            return Ib1::with('planDetails')->where('referral_code', $referralCode)->first();
                        });

                        if (! $ib1 || ! $ib1->planDetails) {
                            continue;
                        }

                        $planId = $ib1->planDetails->ib_category_id;
                        if (! $planId) {
                            continue;
                        }

                        $ibAccPlans = $this->getIbPlanDetails($ib1->user_id, $planId);
                        $ibLevel = $j;

                        $commission = in_array($this->referral_code, ['sensei', 'wealthytrades', 'fxalexg'])
                            ? 3
                            : ($ibAccPlans[$accountTypeId][$ibLevel]["d$i"] ?? null);
                        if ($commission) {
                            $commission = in_array($this->referral_code, ['K08EjL', 'EzHMpw', 'dhMKco', '4uStWn', 'ZiVehO', 'ubFUp7', 'HGvsS1', 'JV4a0Q', 'hvzla', 'zOhX4z', 'jDZVem', 'g6ofHI', 'zzLXS5', 'jMKn9O', 'W0V2I5', 'MPE8QF', 'bNiFv5', 'viQJWM', 'B0AG0Q', '2uDAEC', 'n8veXm', 'MREUR', 'bonus', 'LoTDGy', 'r5rY60', 'l1ILDq', '0D7QTR', 'NfMdsB', '5I6KMP', 'BnqfyN', 'aAWtvV', 'n19Nvf', 'NMdvcb', 'hlS4W0'])
                                ? 6
                                : $commission;
                        } else {
                            continue;
                        }

                        $ibWalletAmount = ((float) $commission) * $ca->volume;
                        $formattedIbWallet = number_format($ibWalletAmount, 10, '.', '');

                        if ($formattedIbWallet < 0.0000001) {
                            $formattedIbWallet = '0.0000000000'; // Handle small values
                        }

                        $walletsToCreate[] = [
                            'id' => (string) Str::orderedUuid(),
                            'ib_wallet' => $formattedIbWallet,
                            'email' => $this->referral_code,
                            'code' => $ca->code,
                            'user_id' => $this->userId,
                            'account_id' => $ca->account->id,
                            'order_id' => $orderId,
                            'ib1_commission_id' => $ca->id,
                            'ib_level' => "IB Level $ibLevel - D$i",
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    $this->processedtrades[] = $ca->id;
                } catch (Exception $e) {
                    Log::error('Error processing individual trade in processTrades: ' . $e->getMessage(), [
                        'trade_id' => $ca->id ?? 'unknown',
                        'referral_code' => $this->referral_code,
                        'level' => $i,
                        'trace' => $e->getTraceAsString(),
                    ]);
                    continue;
                }
            }

            if (! empty($walletsToCreate)) {
                try {
                    $walletsToCreate = collect($walletsToCreate)
                        ->unique(fn($wallet) => $wallet['order_id'] . '_' . $wallet['user_id'])
                        ->values()
                        ->toArray();
                    IbWallet::insert($walletsToCreate);
                    Log::info('Successfully inserted ' . count($walletsToCreate) . ' IB wallet records', [
                        'referral_code' => $this->referral_code,
                        'level' => $i,
                    ]);
                } catch (Exception $e) {
                    Log::error('Error inserting IB wallet records: ' . $e->getMessage(), [
                        'referral_code' => $this->referral_code,
                        'level' => $i,
                        'count' => count($walletsToCreate),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('Critical error in processTrades method: ' . $e->getMessage(), [
                'referral_code' => $this->referral_code,
                'level' => $i,
                'trade_count' => count($trades),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function getIbPlanDetails($user, $plan_id)
    {
        $ibPlans = Cache::remember('ibPlans:' . $user, 3600, function () use ($plan_id) {
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
