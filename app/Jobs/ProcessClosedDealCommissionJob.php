<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Deal;
use App\Models\Ib1;
use App\Models\Ib1Commission;
use App\Models\IbPlanDetails;
use App\Models\IbWallet;
use App\Models\Symbol;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Processes commission for a batch of closed positions.
 *
 * Instead of scanning millions of ib1_commission rows per IB,
 * this job receives specific closed positions from SyncDealsJob
 * and calculates commission only for those positions.
 *
 * Each position is self-contained: one open deal + one close deal
 * → duration check → IB chain lookup → commission calculation → wallet insert.
 */
class ProcessClosedDealCommissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $maxExceptions = 3;
    public $timeout = 300; // 5 min max

    /**
     * Array of closed position data:
     * [['account_id' => ..., 'position_id' => ..., 'deal_id' => ..., 'symbol' => ..., 'volume' => ..., 'time_done' => ...], ...]
     */
    protected array $closedPositions;

    public function __construct(array $closedPositions)
    {
        $this->closedPositions = $closedPositions;
        $this->onQueue('distributeibcommission');
    }

    public function handle(): void
    {
        if (empty($this->closedPositions)) {
            return;
        }

        $jobStart = microtime(true);
        $walletsCreated = 0;
        $positionsProcessed = 0;
        $positionsSkipped = 0;

        // Pre-cache symbols for Forex/Metals check
        $symbolPaths = Symbol::pluck('path', 'symbol')->toArray();

        // Group positions by account for efficient batch lookups
        $byAccount = collect($this->closedPositions)->groupBy('account_id');

        foreach ($byAccount as $accountId => $positions) {
            try {
                $result = $this->processAccountPositions($accountId, $positions->toArray(), $symbolPaths);
                $walletsCreated += $result['wallets_created'];
                $positionsProcessed += $result['processed'];
                $positionsSkipped += $result['skipped'];
            } catch (Exception $e) {
                Log::error("ProcessClosedDealCommissionJob: Failed account {$accountId}", [
                    'error' => $e->getMessage(),
                    'positions' => $positions->count(),
                ]);
            }
        }

        Log::info("ProcessClosedDealCommissionJob completed", [
            'positions_processed' => $positionsProcessed,
            'positions_skipped' => $positionsSkipped,
            'wallets_created' => $walletsCreated,
            'duration_seconds' => round(microtime(true) - $jobStart, 2),
        ]);
    }

    protected function processAccountPositions(string $accountId, array $positions, array $symbolPaths): array
    {
        $account = Account::find($accountId);
        if (!$account) {
            return ['wallets_created' => 0, 'processed' => 0, 'skipped' => count($positions)];
        }

        $user = User::find($account->user_id);
        if (!$user) {
            return ['wallets_created' => 0, 'processed' => 0, 'skipped' => count($positions)];
        }

        // Get the IB referral chain for this trader
        $ibChain = [];
        for ($level = 1; $level <= 15; $level++) {
            $code = $user->{'ib' . $level};
            if ($code) {
                $ibChain[$level] = $code;
            }
        }

        if (empty($ibChain)) {
            return ['wallets_created' => 0, 'processed' => 0, 'skipped' => count($positions)];
        }

        // Batch-load IB users and their plans for all codes in the chain
        $ibUsers = Ib1::with('planDetails')
            ->whereIn('referral_code', array_values($ibChain))
            ->where('status', 1)
            ->get()
            ->keyBy('referral_code');

        // Batch-load plan details for all plan categories
        $planCategoryIds = $ibUsers->map(fn($ib) => $ib->planDetails?->ib_category_id)->filter()->unique();
        $planDetailsMap = [];
        if ($planCategoryIds->isNotEmpty()) {
            $plans = IbPlanDetails::whereIn('ib_category_id', $planCategoryIds->toArray())
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->get();
            foreach ($plans as $plan) {
                $planDetailsMap[$plan->ib_category_id][$plan->account_type_id][$plan->level_id] = $plan;
            }
        }
        // Collect position_ids to batch-check for existing wallets and commissions
        $positionIds = array_column($positions, 'position_id');
        $dealIds = array_column($positions, 'deal_id');
        $orderIds = array_column($positions, 'order_id'); // MT5 Order IDs for commission matching

        // Check which positions already have wallets (prevent duplicates)
        // IbWallet uses order_id which corresponds to MT5 Order ID
        $existingWalletPositions = IbWallet::where('account_id', $accountId)
            ->whereIn('order_id', $orderIds)
            ->pluck('order_id')
            ->flip()
            ->toArray();

        // Batch-load open deals for all positions (eliminate per-position queries)
        $openDeals = Deal::where('account_id', $accountId)
            ->whereIn('position_id', $positionIds)
            ->where('entry', 0) // entry=in
            ->whereIn('action', [0, 1]) // buy or sell
            ->orderBy('time_done', 'asc')
            ->get()
            ->keyBy('position_id');

        // Fallback: for positions missing open deals, check trades table for open_time
        $missingOpenPositionIds = collect($positionIds)->diff($openDeals->keys())->values();
        $tradeOpenTimes = [];
        if ($missingOpenPositionIds->isNotEmpty()) {
            $tradeOpenTimes = DB::table('trades')
                ->where('account_id', $accountId)
                ->whereIn('position_id', $missingOpenPositionIds->toArray())
                ->pluck('open_time', 'position_id')
                ->toArray();
        }

        // Batch-check existing commissions for all order_ids (MT5 Order IDs)
        $existingCommissions = Ib1Commission::where('code', $account->code)
            ->whereIn('order_id', $orderIds)
            ->get()
            ->keyBy('order_id');

        $walletsCreated = 0;
        $processed = 0;
        $skipped = 0;
        $walletsToInsert = [];
        $commissionsToInsert = [];

        foreach ($positions as $pos) {
            $positionId = $pos['position_id'];
            $closeDealId = $pos['deal_id'];
            $orderIdMt5 = $pos['order_id']; // MT5 Order ID - this is what ib1_commission.order_id stores

            // Skip if wallet already exists for this deal
            if (isset($existingWalletPositions[$closeDealId])) {
                $skipped++;
                continue;
            }

            // Get the open deal from batch-loaded data
            $openDeal = $openDeals->get($positionId);

            if (!$openDeal && !isset($tradeOpenTimes[$positionId])) {
                $skipped++;
                continue;
            }

            // Duration check: must be >= 10 seconds
            $openTime = $openDeal ? $openDeal->time_done : $tradeOpenTimes[$positionId];
            $closeTime = $pos['time_done'];
            $duration = abs(strtotime($closeTime) - strtotime($openTime instanceof \Carbon\Carbon ? $openTime->toDateTimeString() : $openTime));

            if ($duration < 10) {
                $skipped++;
                continue;
            }

            // Symbol must be Forex or Metals
            $symbolPath = $symbolPaths[$pos['symbol']] ?? 'default/path';
            $isEligible = (bool)preg_match('/Forex|Metals/', $symbolPath);

            // If ineligible symbol, mark commission as status=11 (ineligible) and continue
            // This prevents re-processing the same deal repeatedly
            if (!$isEligible && !$existingCommissions->has($orderIdMt5)) {
                $commissionId = (string)Str::orderedUuid();
                $commissionsToInsert[] = [
                    'id' => $commissionId,
                    'user_id' => $account->user_id,
                    'account_id' => $accountId,
                    'order_id' => $orderIdMt5,
                    'expert_position_id' => $positionId,
                    'code' => $account->code,
                    'symbol' => $pos['symbol'],
                    'volume' => $pos['volume'],
                    'init_volume' => 0,
                    'time_closed' => $closeTime,
                    'orderstate' => 4,
                    'status' => 11, // Ineligible - symbol not Forex/Metals
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $skipped++;
                continue;
            }

            // Calculate volume in the same way SyncAccountTradesJob does
            $b = preg_match('/Energy|Indices|Cryptocurrencies/', $symbolPath) ? 0.00001 : 0.0001;
            // Volume from deals is already in lots, but ib1_commission.volume uses the b factor on init_volume
            // The close deal volume is what we use
            $commissionVolume = $pos['volume'];

            // Check for existing commission from batch-loaded data
            $existingCommission = $existingCommissions->get($orderIdMt5);

            $commissionId = null;
            if (!$existingCommission) {
                $commissionId = (string)Str::orderedUuid();
                $commissionsToInsert[] = [
                    'id' => $commissionId,
                    'user_id' => $account->user_id,
                    'account_id' => $accountId,
                    'order_id' => $orderIdMt5,
                    'expert_position_id' => $positionId,
                    'code' => $account->code,
                    'symbol' => $pos['symbol'],
                    'volume' => $commissionVolume,
                    'init_volume' => 0,
                    'time_closed' => $closeTime,
                    'orderstate' => 4,
                    'status' => 0, // Will be marked 1 after wallet creation
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                if ($existingCommission->status == 1) {
                    $skipped++; // Already processed
                    continue;
                }
                $commissionId = $existingCommission->id;
            }

            // Now distribute commission across the IB chain
            // ib1's plan determines ALL rates (d1 for level 1, d2 for level 2, etc.)
            $ib1Code = $ibChain[1] ?? null;
            if (!$ib1Code || !$ibUsers->has($ib1Code)) {
                $skipped++;
                continue;
            }

            $ib1 = $ibUsers->get($ib1Code);
            $planCategoryId = $ib1->planDetails?->ib_category_id;
            if (!$planCategoryId || !isset($planDetailsMap[$planCategoryId])) {
                $skipped++;
                continue;
            }

            $accountTypeId = $account->account_type_id;
            $plansForAccountType = $planDetailsMap[$planCategoryId][$accountTypeId] ?? [];
            if (empty($plansForAccountType)) {
                $skipped++;
                continue;
            }

            // Get the plan (keyed by level_id — the max level the plan covers)
            $planLevelId = array_key_first($plansForAccountType);
            $plan = $plansForAccountType[$planLevelId];

            // Distribute to each level in the IB chain
            foreach ($ibChain as $level => $referralCode) {
                $ibUser = $ibUsers->get($referralCode);
                if (!$ibUser) {
                    continue;
                }

                // Get rate for this level from d{level} column
                $dColumn = "d{$level}";
                $rate = $plan->{$dColumn} ?? null;

                // Hardcoded overrides (matching existing DistributeIbCommissionJob logic)
                $overrideCodes3 = ['sensei', 'wealthytrades', 'fxalexg'];
                $overrideCodes6 = ['K08EjL', 'EzHMpw', 'dhMKco', '4uStWn', 'ZiVehO', 'ubFUp7', 'HGvsS1', 'JV4a0Q', 'hvzla', 'zOhX4z', 'jDZVem', 'g6ofHI', 'zzLXS5', 'jMKn9O', 'W0V2I5', 'MPE8QF', 'bNiFv5', 'viQJWM', 'B0AG0Q', '2uDAEC', 'n8veXm', 'MREUR', 'bonus', 'LoTDGy', 'r5rY60', 'l1ILDq', '0D7QTR', 'NfMdsB', '5I6KMP', 'BnqfyN', 'aAWtvV', 'n19Nvf', 'NMdvcb', 'hlS4W0', 'Chinner', 'zym6oK', 'xh8Ule', 'FmL7M0', 'IvkCZH', 'o7Bzs5', 'fpate08', 'EIz0Oy', 'jbz0sX', 'xJpgdd', 'yWFOZc', 'tLnCex', 'jKRjpD','P1OvW1', 'waCJXU', 'Veedmj', 'RHF2N0', 'dV2STG', 'FzomIK', 'yaUWBg', 'mV7z7o', 'hAvjby', '7WhWdD', 'kRDJN3', 'sWNb7n'];

                if (in_array($referralCode, $overrideCodes3)) {
                    $rate = 3;
                } elseif (in_array($referralCode, $overrideCodes6)) {
                    $rate = 6;
                }
                if ($referralCode === 'W0V2I5') {
                    $rate = 8;
                }

                if (!$rate || $rate <= 0) {
                    continue;
                }

                // Only Forex/Metals get commission
                if (!$isEligible) {
                    $rate = 0;
                }

                if ($rate <= 0) {
                    continue;
                }

                $walletAmount = $rate * $commissionVolume;
                $formattedWallet = number_format($walletAmount, 10, '.', '');

                $walletsToInsert[] = [
                    'id' => (string)Str::orderedUuid(),
                    'user_id' => $ibUser->user_id,
                    'account_id' => $accountId,
                    'ib1_commission_id' => $commissionId,
                    'ib_wallet' => $formattedWallet,
                    'email' => $referralCode,
                    'code' => $account->code,
                    'order_id' => $orderIdMt5,
                    'ib_level' => "IB Level {$level} - D{$level}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $processed++;
        }

        // Batch insert all commissions (insertOrIgnore handles concurrent/legacy duplicates)
        if (!empty($commissionsToInsert)) {
            Ib1Commission::insertOrIgnore($commissionsToInsert);
        }

        // Batch insert all wallets
        if (!empty($walletsToInsert)) {
            // Deduplicate by order_id + user_id
            $walletsToInsert = collect($walletsToInsert)
                ->unique(fn($w) => $w['order_id'] . '_' . $w['user_id'])
                ->values()
                ->toArray();

            $walletsCreated = IbWallet::insertOrIgnore($walletsToInsert);
        }

        // Mark all processed commissions as status=1
        // This includes both newly inserted commissions and any pre-existing ones we processed
        if ($processed > 0) {
            $processedOrderIds = collect($positions)->pluck('order_id')->toArray();
            Ib1Commission::where('code', $account->code)
                ->whereIn('order_id', $processedOrderIds)
                ->where('status', 0)
                ->update(['status' => 1]);
        }

        return [
            'wallets_created' => $walletsCreated,
            'processed' => $processed,
            'skipped' => $skipped,
        ];
    }
}
