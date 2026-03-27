<?php

namespace App\Jobs;

use App\Models\Ib1;
use App\Models\Ib1Commission;
use App\Models\IbPlanDetails;
use App\Models\IbWallet;
use App\Models\Symbol;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
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
        $jobStart = microtime(true);
        try {
            Log::debug('DistributeIbCommissionJob: processing referral_code ' . $this->referral_code . ' userId ' . $this->userId . ' accountId ' . $this->accountId);

            // Safety check: if there are more than 100k unprocessed commissions for this referral code,
            // this indicates a stuck account with too many trades. Log and skip to prevent infinite loop.
            $unprocessedCount = Ib1Commission::whereHas('user', function ($query) {
                $query->where('ib1', $this->referral_code)
                    ->orWhere('ib2', $this->referral_code)
                    ->orWhere('ib3', $this->referral_code)
                    ->orWhere('ib4', $this->referral_code)
                    ->orWhere('ib5', $this->referral_code)
                    ->orWhere('ib6', $this->referral_code)
                    ->orWhere('ib7', $this->referral_code)
                    ->orWhere('ib8', $this->referral_code)
                    ->orWhere('ib9', $this->referral_code)
                    ->orWhere('ib10', $this->referral_code)
                    ->orWhere('ib11', $this->referral_code)
                    ->orWhere('ib12', $this->referral_code)
                    ->orWhere('ib13', $this->referral_code)
                    ->orWhere('ib14', $this->referral_code)
                    ->orWhere('ib15', $this->referral_code);
            })->where('orderstate', 4)->count();

            if ($unprocessedCount > 100000) {
                Log::warning('DistributeIbCommissionJob: Stuck account detected with excessive commissions', [
                    'referral_code' => $this->referral_code,
                    'unprocessed_count' => $unprocessedCount,
                    'account_id' => $this->accountId,
                ]);
                // Skip processing but don't fail - this prevents infinite retries
                return;
            }

            // Find all parent Ib of current account owner and distribute commission , change status of commission to 1
            DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");

            $levelTimings = [];
            for ($i = 1; $i <= 15; $i++) {
                $levelStart = microtime(true);
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

                    $levelTimings[$i] = round(microtime(true) - $levelStart, 2);
                } catch (Exception $e) {
                    Log::error('Error processing IB commissions for level ' . $i . ': ' . $e->getMessage(), [
                        'level' => $i,
                        'referral_code' => $this->referral_code,
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $levelTimings[$i] = round(microtime(true) - $levelStart, 2);
                    continue;
                }
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

            Log::info('DistributeIbCommissionJob completed', [
                'referral_code' => $this->referral_code,
                'total_duration_seconds' => round(microtime(true) - $jobStart, 2),
                'level_timings' => $levelTimings,
                'processed_trades' => count($this->processedtrades),
                'discarded_ids' => count($this->discardedIds),
            ]);

            // Clean up: Remove referral_code from queued set to allow future jobs for this code
            try {
                $redisPrefix = env('HORIZON_PREFIX', 'laravel_horizon:');
                $queuedSetKey = $redisPrefix . "queued_referral_codes:distributeibcommission";
                Redis::srem($queuedSetKey, $this->referral_code);
                Redis::del("$queuedSetKey:{$this->referral_code}");
            } catch (\Exception $e) {
                Log::warning("Failed to clean up queued referral code: " . $e->getMessage(), [
                    'referral_code' => $this->referral_code,
                ]);
            }
        } catch (Exception $e) {
            Log::error('Critical error in DistributeIbCommissionJob: ' . $e->getMessage(), [
                'referral_code' => $this->referral_code,
                'user_id' => $this->userId,
                'account_id' => $this->accountId,
                'trace' => $e->getTraceAsString(),
            ]);

            // Clean up on error too, to prevent permanent blocking
            try {
                $redisPrefix = env('HORIZON_PREFIX', 'laravel_horizon:');
                $queuedSetKey = $redisPrefix . "queued_referral_codes:distributeibcommission";
                Redis::srem($queuedSetKey, $this->referral_code);
            } catch (\Exception $cleanupError) {
                // Silently ignore cleanup errors
            }

            throw $e;
        }
    }

    /**
     * Handle job failure - cleanup queued referral code
     */
    public function failed(\Throwable $exception): void
    {
        try {
            $redisPrefix = env('HORIZON_PREFIX', 'laravel_horizon:');
            $queuedSetKey = $redisPrefix . "queued_referral_codes:distributeibcommission";
            Redis::srem($queuedSetKey, $this->referral_code);
            Redis::del("$queuedSetKey:{$this->referral_code}");
        } catch (\Exception $e) {
            Log::warning("Failed to clean up queued referral code on job failure: " . $e->getMessage(), [
                'referral_code' => $this->referral_code,
            ]);
        }

        Log::error('DistributeIbCommissionJob failed permanently', [
            'referral_code' => $this->referral_code,
            'user_id' => $this->userId,
            'account_id' => $this->accountId,
            'exception' => $exception->getMessage(),
        ]);
    }

    protected function getSymbolMappings()
    {
        return Cache::remember('symbol_mappings', now()->addMinutes(30), function () {
            return Symbol::pluck('path', 'symbol')->toArray();
        });
    }
    protected function processTrades($trades, $i): void
    {
        try {
            $symbolMappings = $this->getSymbolMappings();
            $processStart = microtime(true);

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
            $planDetailsMap = []; // Cache plan details by plan_id
            if (!empty($referralCodesToLookup)) {
                $ibUsers = Ib1::with('planDetails')
                    ->whereIn('referral_code', array_keys($referralCodesToLookup))
                    ->get()
                    ->keyBy('referral_code')
                    ->toArray();

                // Batch load all plan IDs we need
                $planIds = [];
                foreach ($ibUsers as $ib1) {
                    if (isset($ib1['plan_details']['ib_category_id'])) {
                        $planIds[$ib1['plan_details']['ib_category_id']] = true;
                    }
                }

                // Batch load all IbPlanDetails for these plan IDs
                if (!empty($planIds)) {
                    $plans = IbPlanDetails::whereIn('ib_category_id', array_keys($planIds))
                        ->where('status', 1)
                        ->whereNull('deleted_at')
                        ->get();

                    foreach ($plans as $plan) {
                        if (!isset($planDetailsMap[$plan->ib_category_id])) {
                            $planDetailsMap[$plan->ib_category_id] = [];
                        }
                        // Store by account_type_id and level_id for quick lookup
                        $planDetailsMap[$plan->ib_category_id][$plan->account_type_id][$plan->level_id] = [];
                        for ($k = 1; $k <= $plan->level_id; $k++) {
                            $planDetailsMap[$plan->ib_category_id][$plan->account_type_id][$plan->level_id]["d$k"] = $plan->{"d$k"};
                        }
                    }
                }
            }

            foreach ($trades as $ca) {
                try {
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

                        // Use pre-loaded $ibUsers array instead of Cache::remember()
                        if (!isset($ibUsers[$referralCode])) {
                            continue;
                        }

                        $ib1 = $ibUsers[$referralCode];
                        if (!isset($ib1['plan_details'])) {
                            continue;
                        }

                        $planId = $ib1['plan_details']['ib_category_id'] ?? null;
                        if (! $planId) {
                            continue;
                        }

                        // Use pre-loaded plan details instead of querying again
                        $ibAccPlans = $planDetailsMap[$planId] ?? [];
                        if (empty($ibAccPlans)) {
                            continue;
                        }

                        $ibLevel = $j;

                        $commission = in_array($this->referral_code, ['sensei', 'wealthytrades', 'fxalexg'])
                            ? 3
                            : ($ibAccPlans[$accountTypeId][$ibLevel]["d$i"] ?? null);
                        if ($commission) {
                            $commission = in_array($this->referral_code, ['K08EjL', 'EzHMpw', 'dhMKco', '4uStWn', 'ZiVehO', 'ubFUp7', 'HGvsS1', 'JV4a0Q', 'hvzla', 'zOhX4z', 'jDZVem', 'g6ofHI', 'zzLXS5', 'jMKn9O', 'W0V2I5', 'MPE8QF', 'bNiFv5', 'viQJWM', 'B0AG0Q', '2uDAEC', 'n8veXm', 'MREUR', 'bonus', 'LoTDGy', 'r5rY60', 'l1ILDq', '0D7QTR', 'NfMdsB', '5I6KMP', 'BnqfyN', 'aAWtvV', 'n19Nvf', 'NMdvcb', 'hlS4W0', 'Chinner', 'zym6oK', 'xh8Ule', 'FmL7M0', 'IvkCZH', 'o7Bzs5', 'fpate08','EIz0Oy', 'jbz0sX', 'xJpgdd', 'yWFOZc', 'tLnCex', 'jKRjpD'])
                                ? 6
                                : $commission;
                        } else {
                            continue;
                        }
                        //TEMP FIX: Allocate commission for forex and metal only and set 0 commission for any other . Determine if forex or metal by checking the path in symbols table for the current symbol and if it contains "forex" or "metal" then allocate commission otherwise set to 0
                        $symbolWithoutP = $ca->symbol;
                        if (!isset($symbolMappings[$symbolWithoutP])) {
                            try {
                                $symbol = Symbol::where('symbol', $symbolWithoutP)->first();
                                $symbolMappings[$symbolWithoutP] = $symbol ? $symbol->path : 'default/path';
                            } catch (Exception $e) {
                                Log::error('Error fetching symbol: ' . $e->getMessage(), [
                                    'symbol' => $symbolWithoutP,
                                    'error' => $e->getMessage(),
                                ]);
                                $symbolMappings[$symbolWithoutP] = 'error/path';
                            }
                        }

                        $symbolpath = $symbolMappings[$symbolWithoutP];
                        $commission = preg_match('/Forex|Metals/', $symbolpath) ? $commission : 0;
                        // if(in_array($this->referral_code, ['xyB6LV'])){
                        //     $commission = preg_match('/Forex|Energy/', $symbolpath) ? .01 : $commission;
                        // }

                        if($commission && preg_match('/Forex|Metals/', $symbolpath)){
                            $commission = in_array($this->referral_code, ['W0V2I5'])
                                ? 7
                                : $commission;
                        }

                        log::info('Calculated commission for trade', [
                            'trade_id' => $ca->id,
                            'symbol' => $ca->symbol,
                            'symbol_path' => $symbolpath,
                            'commission' => $commission,
                            'referral_code' => $this->referral_code,
                            'level' => $i,
                        ]);

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
                    Log::debug('Successfully inserted ' . count($walletsToCreate) . ' IB wallet records for level ' . $i, [
                        'referral_code' => $this->referral_code,
                        'level' => $i,
                        'count' => count($walletsToCreate),
                        'duration_seconds' => round(microtime(true) - $processStart, 2),
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
                'trade_count' => count($trades) ?? 0,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
