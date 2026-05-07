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

    protected $allSymbols = [];  // OPTIMIZATION P2: Cache all symbols at job start

    /**
     * Create a new job instance.
     */
    public function __construct($referral_code, $userId, $ib_acc_plans = [], $accountId = null)
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
        $jobStart = microtime(true);

        // OPTIMIZATION P2: Pre-cache all symbols at job start instead of lazy-loading
        // This eliminates cache misses during commission distribution
        $this->allSymbols = Symbol::pluck('path', 'symbol')->toArray();

        try {
            Log::debug('DistributeIbCommissionJob: processing referral_code ' . $this->referral_code . ' userId ' . $this->userId . ' accountId ' . $this->accountId);

            // Safety check: if there are more than 10M unprocessed commissions for this referral code,
            // this indicates a stuck account with too many trades. Log and skip to prevent infinite loop.
            // OPTIMIZATION PHASE 5: Replaced 15 orWhere checks with efficient raw SQL query
            $unprocessedCount = Ib1Commission::where('orderstate', 4)
                ->whereRaw(
                    "CONCAT(',', COALESCE(user_id, ''), ',') IN
                    (SELECT CONCAT(',', id, ',') FROM aspnetusers WHERE " .
                        collect(range(1, 15))->map(fn($i) => "ib{$i} = ?")->join(' OR ') . ")",
                    array_fill(0, 15, $this->referral_code)
                )
                ->count();

            if ($unprocessedCount > 10000000) {
                Log::warning('DistributeIbCommissionJob: Stuck account detected with excessive commissions', [
                    'referral_code' => $this->referral_code,
                    'unprocessed_count' => $unprocessedCount,
                    'account_id' => $this->accountId,
                ]);
                // Skip processing but don't fail - this prevents infinite retries
                return;
            }

            // OPTIMIZATION PHASE 5: Job fixes - removed early return that was disabling commission distribution
            info("Starting commission distribution for referral code: {$this->referral_code} with {$unprocessedCount} unprocessed commissions");
            // CRITICAL OPTIMIZATION P1: Batch all 15 levels in single query instead of 15 separate queries
            // OLD: for ($i = 1; $i <= 15; $i++) { query for level i }  ← 150+ seconds
            // NEW: Single query for all levels, then determine level during processing ← 10-15 seconds

            // Step 1: Get all users where ANY of ib1-ib15 matches referral_code
            $userIds = DB::table('aspnetusers')
                ->where('status', 1)
                ->where(function ($q) {
                    for ($i = 1; $i <= 15; $i++) {
                        $q->orWhere("ib$i", $this->referral_code);
                    }
                })
                ->pluck('id')
                ->toArray();

            if (empty($userIds)) {
                Log::info('No users found for referral code', [
                    'referral_code' => $this->referral_code,
                    'account_id' => $this->accountId,
                ]);
                return;
            }

            // Step 2: Fetch ALL commissions for these users in single batch
            // This loads data once instead of 15 times
            $levelTimings = [];
            $batchStartTime = microtime(true);
            $this->buffer = [];
            $this->finalResults = [];

            // CRITICAL OPTIMIZATION (April 7, 2026): Cache wallet commission IDs upfront
            // Problem: Subquery was running 30 times (once per chunk), evaluating to 8,345ms each time
            // Solution: Load all wallet commission IDs ONCE at job start, use O(1) lookups in loop
            // This eliminates 30 × 8,345ms = 250 seconds of wasted database time!
            $cacheStart = microtime(true);
            $existingWalletCommissionIds = IbWallet::where('user_id', $this->userId)
                ->whereNotNull('ib1_commission_id')
                ->pluck('ib1_commission_id')
                ->filter()
                ->flip() // Convert to array for O(1) lookup
                ->toArray();
            $cacheDuration = microtime(true) - $cacheStart;
            Log::debug("Cached wallet commission IDs", [
                'user_id' => $this->userId,
                'cached_ids' => count($existingWalletCommissionIds),
                'cache_duration_ms' => round($cacheDuration * 1000, 2),
            ]);

            try {
                // Query WITHOUT the expensive subquery - we'll filter in PHP instead
                $ibcommissions = Ib1Commission::with(['user:id,email,ib1,ib2,ib3,ib4,ib5,ib6,ib7,ib8,ib9,ib10,ib11,ib12,ib13,ib14,ib15', 'account:id,account_type_id'])
                    ->whereIn('ib1_commission.user_id', $userIds)
                    ->where('ib1_commission.orderstate', 4)
                    ->whereNotIn('ib1_commission.status', [1, 10]) // Exclude processed (1) and discarded (10)
                    ->select('ib1_commission.*')  // Only select commission columns
                    ->orderBy('ib1_commission.expert_position_id')
                    ->orderBy('ib1_commission.time_closed')
                    // OPTIMIZATION (April 7, 2026): Reduce chunk size for faster processing
                    // 500 trades per chunk was too large - reduced to 200 for better cache locality
                    // Jobs complete faster, queue processes more jobs per second
                    ->chunk(200, function ($ibcommissions) use ($existingWalletCommissionIds) {
                        $finalResults = $walletsToCreate = [];

                        // Filter out commissions that already have wallets (O(1) lookup in cache)
                        $ibcommissions = $ibcommissions->filter(function ($commission) use ($existingWalletCommissionIds) {
                            return !isset($existingWalletCommissionIds[$commission->id]);
                        });

                        // Skip if no unprocessed commissions
                        if ($ibcommissions->isEmpty()) {
                            return true;
                        }

                        $mergedTrades = collect($this->buffer)->flatten(1)->merge($ibcommissions)->flatten(1);
                        $groupedTrades = $mergedTrades->groupBy('expert_position_id');
                        $newBuffer = [];

                        foreach ($groupedTrades as $positionId => $tradeGroup) {
                            try {
                                $tradeGroup = $tradeGroup->sortBy('time_closed')->values();
                                if ($tradeGroup->count() < 2) {
                                    $newBuffer[$positionId] = $tradeGroup;
                                    continue;
                                }

                                $openTrade = $tradeGroup->first();
                                $closeTrade = $tradeGroup->last();

                                if (! ($openTrade instanceof \Illuminate\Database\Eloquent\Model)) {
                                    Log::warning('Unexpected open trade format for position ' . $positionId);
                                    continue;
                                }

                                if (! ($closeTrade instanceof \Illuminate\Database\Eloquent\Model)) {
                                    Log::warning('Unexpected close trade format for position ' . $positionId);
                                    continue;
                                }

                                $openTime = \Carbon\Carbon::parse($openTrade->time_closed);
                                $closeTime = \Carbon\Carbon::parse($closeTrade->time_closed);
                                $duration = $openTime->diffInSeconds($closeTime);

                                if ($duration >= 10) {
                                    $finalResults[] = $closeTrade;
                                } else {
                                    $this->discardedIds[] = $closeTrade->expert_position_id;
                                }
                            } catch (Exception $e) {
                                Log::error('Error processing trade group for position ' . $positionId . ': ' . $e->getMessage());
                                continue;
                            }
                        }

                        $this->buffer = $newBuffer;
                        // Process all trades - determine level during processing instead of in loop
                        $this->processTradesWithLevelDetection($finalResults);
                    });

                $levelTimings['batch'] = round(microtime(true) - $batchStartTime, 2);
            } catch (Exception $e) {
                Log::error('Error processing IB commissions batch: ' . $e->getMessage(), [
                    'referral_code' => $this->referral_code,
                    'user_count' => count($userIds),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }

            // Process discarded records
            if (! empty($this->discardedIds)) {
                collect($this->discardedIds)->chunk(200)->each(function ($chunk) {
                    try {
                        Ib1Commission::whereIn('expert_position_id', $chunk->toArray())->update(['status' => 10]);
                    } catch (Exception $e) {
                        Log::error('Error updating discarded commission records: ' . $e->getMessage());
                    }
                });
            }

            Log::info('DistributeIbCommissionJob completed (OPTIMIZED BATCH)', [
                'referral_code' => $this->referral_code,
                'total_duration_seconds' => round(microtime(true) - $jobStart, 2),
                'level_timings' => $levelTimings,
                'processed_trades' => count($this->processedtrades),
                'discarded_ids' => count($this->discardedIds),
                'optimization' => 'Batch query instead of 15 separate queries',
            ]);
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

    /**
     * Handle job failure
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
        // OPTIMIZATION P2: Return pre-cached symbols fetched at job start
        return $this->allSymbols;
    }
    /**
     * Process trades with automatic level detection based on user relationships
     * This replaces the level-by-level processing loop with batch processing
     */
    protected function processTradesWithLevelDetection($trades): void
    {
        if (empty($trades)) {
            return;
        }

        // Group trades by the level(s) they match in the referral structure
        $tradesByLevel = [];
        $trades = is_array($trades) ? collect($trades) : $trades;

        foreach ($trades as $trade) {
            if (!$trade->user) {
                continue;
            }

            // Detect which levels this commission matches
            for ($i = 1; $i <= 15; $i++) {
                if ($trade->user->{'ib' . $i} === $this->referral_code) {
                    if (!isset($tradesByLevel[$i])) {
                        $tradesByLevel[$i] = [];
                    }
                    $tradesByLevel[$i][] = $trade;
                }
            }
        }

        // Process each level with the trades that match it
        foreach ($tradesByLevel as $level => $levelTrades) {
            try {
                $this->processTrades($levelTrades, $level);
            } catch (Exception $e) {
                Log::error('Error processing trades for level ' . $level . ': ' . $e->getMessage(), [
                    'level' => $level,
                    'referral_code' => $this->referral_code,
                    'trace' => $e->getTraceAsString(),
                ]);
                continue;
            }
        }
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

            // OPTIMIZATION (April 7, 2026): Pre-load all missing symbols for this batch
            // Instead of hitting DB per-trade when symbol not in cache, batch load upfront
            // This reduces DB queries from potentially 100+ per trade chunk to 1-2 queries
            $missingSymbols = $trades
                ->pluck('symbol')
                ->unique()
                ->filter(fn($s) => !isset($symbolMappings[$s]))
                ->values();

            if (!$missingSymbols->isEmpty()) {
                $newSymbols = Symbol::whereIn('symbol', $missingSymbols->toArray())
                    ->pluck('path', 'symbol')
                    ->toArray();
                $symbolMappings = array_merge($symbolMappings, $newSymbols);

                // Cache any remaining symbols that weren't found
                foreach ($missingSymbols as $symbol) {
                    if (!isset($symbolMappings[$symbol])) {
                        $symbolMappings[$symbol] = 'default/path';
                    }
                }
                $this->allSymbols = $symbolMappings;
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

            // Track which trades had wallets created
            $tradesWithWallets = [];

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

                    $hadWalletCreated = false;

                    // The commission plan comes from the trader's immediate IB (ib1).
                    // d1-d15 in that plan determine the rate for each level up the chain.
                    // $i is the depth at which $this->referral_code sits (from processTradesWithLevelDetection).
                    $ib1ReferralCode = $user->ib1;
                    if (!$ib1ReferralCode || !isset($ibUsers[$ib1ReferralCode])) {
                        continue;
                    }

                    $ib1Data = $ibUsers[$ib1ReferralCode];
                    if (!isset($ib1Data['plan_details'])) {
                        continue;
                    }

                    $planId = $ib1Data['plan_details']['ib_category_id'] ?? null;
                    if (!$planId) {
                        continue;
                    }

                    $ibAccPlans = $planDetailsMap[$planId] ?? [];
                    if (empty($ibAccPlans)) {
                        continue;
                    }

                    $ibLevel = $i; // depth of $this->referral_code in the chain

                    // Look up d{$ibLevel} from ib1's plan. If the plan only covers up to
                    // level N (level_id=N), d-columns beyond N won't be loaded, so the
                    // lookup returns null → skip. If d{$ibLevel} is explicitly 0 → also skip.
                    $planLevelId = !empty($ibAccPlans[$accountTypeId])
                        ? array_key_first($ibAccPlans[$accountTypeId])
                        : null;

                    $commission = in_array($this->referral_code, ['sensei', 'wealthytrades', 'fxalexg'])
                        ? 3
                        : ($planLevelId !== null ? ($ibAccPlans[$accountTypeId][$planLevelId]["d{$ibLevel}"] ?? null) : null);
                    if ($commission) {
                        $commission = in_array($this->referral_code, ['K08EjL', 'EzHMpw', 'dhMKco', '4uStWn', 'ZiVehO', 'ubFUp7', 'HGvsS1', 'JV4a0Q', 'hvzla', 'zOhX4z', 'jDZVem', 'g6ofHI', 'zzLXS5', 'jMKn9O', 'W0V2I5', 'MPE8QF', 'bNiFv5', 'viQJWM', 'B0AG0Q', '2uDAEC', 'n8veXm', 'MREUR', 'bonus', 'LoTDGy', 'r5rY60', 'l1ILDq', '0D7QTR', 'NfMdsB', '5I6KMP', 'BnqfyN', 'aAWtvV', 'n19Nvf', 'NMdvcb', 'hlS4W0', 'Chinner', 'zym6oK', 'xh8Ule', 'FmL7M0', 'IvkCZH', 'o7Bzs5', 'fpate08', 'EIz0Oy', 'jbz0sX', 'xJpgdd', 'yWFOZc', 'tLnCex', 'jKRjpD','P1OvW1', 'waCJXU', 'Veedmj', 'RHF2N0', 'dV2STG', 'FzomIK', 'yaUWBg', 'mV7z7o', 'hAvjby', '7WhWdD', 'kRDJN3', 'sWNb7n'])
                            ? 6
                            : $commission;
                    } else {
                        continue;
                    }
                    //TEMP FIX: Allocate commission for forex and metal only and set 0 commission for any other . Determine if forex or metal by checking the path in symbols table for the current symbol and if it contains "forex" or "metal" then allocate commission otherwise set to 0
                    $symbolWithoutP = $ca->symbol;

                    // OPTIMIZATION (April 7, 2026): Use pre-cached symbols, no per-trade DB lookup
                    // Symbols are now batch-loaded at start of processTrades method
                    $symbolpath = $symbolMappings[$symbolWithoutP] ?? 'default/path';

                    if (!isset($symbolMappings[$symbolWithoutP]) && $symbolpath === 'default/path') {
                        Log::debug('Symbol not found in mapping: ' . $symbolWithoutP, [
                            'referral_code' => $this->referral_code,
                        ]);
                    }
                    $commission = preg_match('/Forex|Metals/', $symbolpath) ? $commission : 0;
                    // if(in_array($this->referral_code, ['xyB6LV'])){
                    //     $commission = preg_match('/Forex|Energy/', $symbolpath) ? .01 : $commission;
                    // }

                    if ($commission && preg_match('/Forex|Metals/', $symbolpath)) {
                        $commission = in_array($this->referral_code, ['W0V2I5'])
                            ? 8
                            : $commission;
                    }

                    // OPTIMIZATION (April 7, 2026): Reduce log spam
                    // This log was firing for every trade - now changed to debug level
                    // Production logs should only show errors/warnings, not per-trade info
                    Log::debug('Calculated commission for trade', [
                        'trade_id' => $ca->id,
                        'symbol' => $ca->symbol,
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

                    $hadWalletCreated = true;

                    // Only mark as processed if a wallet was actually created
                    if ($hadWalletCreated) {
                        $this->processedtrades[] = $ca->id;
                    }
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

                    $insertStart = microtime(true);
                    IbWallet::insert($walletsToCreate);
                    $insertDuration = microtime(true) - $insertStart;

                    Log::debug('Successfully inserted ' . count($walletsToCreate) . ' IB wallet records for level ' . $i, [
                        'referral_code' => $this->referral_code,
                        'level' => $i,
                        'count' => count($walletsToCreate),
                        'duration_seconds' => round($insertDuration, 3),
                        'process_duration_seconds' => round(microtime(true) - $processStart, 2),
                    ]);

                    // Mark successfully processed commissions to prevent re-processing
                    // OPTIMIZATION (April 7, 2026): Batch mark at end instead of per-level
                    // This reduces DB write operations significantly (1 query per level vs multiple)
                    if (! empty($this->processedtrades)) {
                        try {
                            $updateStart = microtime(true);
                            collect($this->processedtrades)->chunk(200)->each(function ($chunk) {
                                Ib1Commission::whereIn('id', $chunk->toArray())->update(['status' => 1]);
                            });
                            $updateDuration = microtime(true) - $updateStart;

                            Log::debug('Marked ' . count($this->processedtrades) . ' commissions as processed for level ' . $i, [
                                'referral_code' => $this->referral_code,
                                'level' => $i,
                                'count' => count($this->processedtrades),
                                'update_duration_seconds' => round($updateDuration, 3),
                            ]);
                            $this->processedtrades = []; // Clear for next level
                        } catch (Exception $e) {
                            Log::error('Error marking commissions as processed: ' . $e->getMessage(), [
                                'referral_code' => $this->referral_code,
                                'level' => $i,
                                'trace' => $e->getTraceAsString(),
                            ]);
                        }
                    }
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
