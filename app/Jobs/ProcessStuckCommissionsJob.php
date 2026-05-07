<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Ib1;
use App\Models\Ib1Commission;
use App\Models\IbPlanDetails;
use App\Models\IbWallet;
use App\Models\Symbol;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessStuckCommissionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800; // 30 minutes
    public int $tries = 1;

    protected string $cacheKey;

    public function __construct(string $cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }

    public function handle(): void
    {
        Cache::put($this->cacheKey . ':status', 'running', 3600);
        $results = [];

        try {
            // ── Step 1: Fix wrong-status (has wallets but status=0 → 1) ──
            Cache::put($this->cacheKey . ':progress', 'Step 1/5: Fixing wrong-status commissions...', 3600);
            $results['wrong_status'] = $this->fixWrongStatus();

            // ── Step 2: Discard unprocessable commissions (no IB chain, no deal, etc.) ──
            Cache::put($this->cacheKey . ':progress', 'Step 2/5: Discarding unprocessable commissions...', 3600);
            $results['discarded'] = $this->discardUnprocessable();

            // ── Step 3: Create wallets for valid stuck commissions ──
            Cache::put($this->cacheKey . ':progress', 'Step 3/5: Creating wallets for eligible commissions...', 3600);
            $results['wallets_created'] = $this->createWalletsForStuck();

            // ── Step 4: Fix any newly-walleted commissions to status=1 ──
            Cache::put($this->cacheKey . ':progress', 'Step 4/5: Updating commission statuses...', 3600);
            $results['status_updated'] = $this->fixWrongStatus();

            // ── Step 5: Summary ──
            $remaining = DB::selectOne("
                SELECT COUNT(*) as cnt FROM ib1_commission c
                WHERE c.status = 0 AND c.deleted_at IS NULL
                AND NOT EXISTS (SELECT 1 FROM ib_wallet w WHERE w.ib1_commission_id = c.id)
            ")->cnt;
            $results['remaining_stuck'] = $remaining;

            Cache::put($this->cacheKey . ':result', $results, 3600);
            Cache::put($this->cacheKey . ':status', 'completed', 3600);
            Cache::put($this->cacheKey . ':progress', 'All steps complete', 3600);

            Log::info('ProcessStuckCommissionsJob completed', $results);
        } catch (\Throwable $e) {
            Cache::put($this->cacheKey . ':status', 'failed', 3600);
            Cache::put($this->cacheKey . ':progress', 'Error: ' . $e->getMessage(), 3600);
            Log::error('ProcessStuckCommissionsJob failed', [
                'error' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 2000),
            ]);
        }
    }

    /**
     * Fix commissions that already have wallets but are stuck at status=0.
     */
    protected function fixWrongStatus(): array
    {
        $updated = DB::update("
            UPDATE ib1_commission c
            SET c.status = 1, c.updated_at = NOW()
            WHERE c.status = 0
              AND c.deleted_at IS NULL
              AND EXISTS (SELECT 1 FROM ib_wallet w WHERE w.ib1_commission_id = c.id)
        ");

        Log::info("ProcessStuckCommissionsJob: Fixed {$updated} wrong-status commissions");

        return ['fixed' => $updated];
    }

    /**
     * Discard stuck commissions that can never produce wallets:
     * - No IB chain (user.ib1 IS NULL)
     * - Open deal only (no close deal)
     * - No matching deal or trade
     * - Trade-only match (no deal in deals table)
     */
    protected function discardUnprocessable(): array
    {
        $results = [];

        // 1. No IB chain (user has no ib1 referral)
        $noIb = DB::update("
            UPDATE ib1_commission c
            INNER JOIN accounts a ON a.id = c.account_id
            LEFT JOIN aspnetusers u ON u.id = a.user_id
            SET c.status = 10, c.updated_at = NOW()
            WHERE c.status = 0
              AND c.deleted_at IS NULL
              AND NOT EXISTS (SELECT 1 FROM ib_wallet w WHERE w.ib1_commission_id = c.id)
              AND (u.ib1 IS NULL OR u.status != 1 OR u.id IS NULL)
        ");
        $results['no_ib_chain'] = $noIb;
        Log::info("ProcessStuckCommissionsJob: Discarded {$noIb} commissions with no IB chain");

        // 2. Open deal only (no close deal for the position)
        $openOnly = DB::update("
            UPDATE ib1_commission c
            INNER JOIN deals d ON d.deal_id = c.order_id AND d.entry = 0
            SET c.status = 10, c.updated_at = NOW()
            WHERE c.status = 0
              AND c.deleted_at IS NULL
              AND NOT EXISTS (SELECT 1 FROM ib_wallet w WHERE w.ib1_commission_id = c.id)
              AND NOT EXISTS (
                  SELECT 1 FROM deals d2
                  WHERE d2.account_id = d.account_id
                    AND d2.position_id = d.position_id
                    AND d2.entry = 1
              )
        ");
        $results['open_deal_only'] = $openOnly;

        // 3. Open deal with close deal existing (the close deal's commission handles it)
        $openWithClose = DB::update("
            UPDATE ib1_commission c
            INNER JOIN deals d ON d.deal_id = c.order_id AND d.entry = 0
            SET c.status = 10, c.updated_at = NOW()
            WHERE c.status = 0
              AND c.deleted_at IS NULL
              AND NOT EXISTS (SELECT 1 FROM ib_wallet w WHERE w.ib1_commission_id = c.id)
              AND EXISTS (
                  SELECT 1 FROM deals d2
                  WHERE d2.account_id = d.account_id
                    AND d2.position_id = d.position_id
                    AND d2.entry = 1
              )
        ");
        $results['open_deal_with_close'] = $openWithClose;

        // 4. No deal match at all (trades-only or orphaned)
        $noMatch = DB::update("
            UPDATE ib1_commission c
            SET c.status = 10, c.updated_at = NOW()
            WHERE c.status = 0
              AND c.deleted_at IS NULL
              AND NOT EXISTS (SELECT 1 FROM ib_wallet w WHERE w.ib1_commission_id = c.id)
              AND NOT EXISTS (SELECT 1 FROM deals d WHERE d.deal_id = c.order_id AND d.entry = 1 AND d.action IN (0, 1))
        ");
        $results['no_close_deal'] = $noMatch;

        $total = $noIb + $openOnly + $openWithClose + $noMatch;
        $results['total_discarded'] = $total;
        Log::info("ProcessStuckCommissionsJob: Discarded {$total} unprocessable commissions", $results);

        return $results;
    }

    /**
     * Create wallets for stuck commissions that have:
     * - A matching close deal (entry=1)
     * - An active user with IB chain
     * - An eligible symbol (Forex/Metals)
     *
     * This replicates ProcessClosedDealCommissionJob logic but works in bulk.
     */
    protected function createWalletsForStuck(): array
    {
        // Pre-load reference data
        $symbolPaths = Symbol::pluck('path', 'symbol')->toArray();

        $overrideCodes3 = ['sensei', 'wealthytrades', 'fxalexg'];
        $overrideCodes6 = ['K08EjL', 'EzHMpw', 'dhMKco', '4uStWn', 'ZiVehO', 'ubFUp7', 'HGvsS1', 'JV4a0Q', 'hvzla', 'zOhX4z', 'jDZVem', 'g6ofHI', 'zzLXS5', 'jMKn9O', 'W0V2I5', 'MPE8QF', 'bNiFv5', 'viQJWM', 'B0AG0Q', '2uDAEC', 'n8veXm', 'MREUR', 'bonus', 'LoTDGy', 'r5rY60', 'l1ILDq', '0D7QTR', 'NfMdsB', '5I6KMP', 'BnqfyN', 'aAWtvV', 'n19Nvf', 'NMdvcb', 'hlS4W0', 'Chinner', 'zym6oK', 'xh8Ule', 'FmL7M0', 'IvkCZH', 'o7Bzs5', 'fpate08', 'EIz0Oy', 'jbz0sX', 'xJpgdd', 'yWFOZc', 'tLnCex', 'jKRjpD','P1OvW1', 'waCJXU', 'Veedmj', 'RHF2N0', 'dV2STG', 'FzomIK', 'yaUWBg', 'mV7z7o', 'hAvjby', '7WhWdD', 'kRDJN3', 'sWNb7n'];

        // Load all active IBs with plans
        $ibUsers = Ib1::with('planDetails')
            ->where('status', 1)
            ->get()
            ->keyBy('referral_code');

        // Build plan details map: [category_id][account_type_id][level_id] => plan
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

        $totalProcessed = 0;
        $totalWalletsCreated = 0;
        $totalSkipped = 0;
        $chunkSize = 500;
        $lastId = 0;

        // Process in chunks using cursor-based pagination (no OFFSET)
        while (true) {
            $commissions = DB::select("
                SELECT c.id, c.order_id, c.code, c.symbol, c.volume, c.account_id,
                       d.position_id, d.symbol as deal_symbol, d.volume as deal_volume, d.time_done,
                       d.account_id as deal_account_id
                FROM ib1_commission c
                INNER JOIN deals d ON d.deal_id = c.order_id AND d.entry = 1 AND d.action IN (0, 1)
                WHERE c.status = 0
                  AND c.deleted_at IS NULL
                  AND c.id > ?
                  AND NOT EXISTS (SELECT 1 FROM ib_wallet w WHERE w.ib1_commission_id = c.id)
                ORDER BY c.id
                LIMIT ?
            ", [$lastId, $chunkSize]);

            if (empty($commissions)) {
                break;
            }

            $lastId = end($commissions)->id;

            // Batch-load accounts and users for this chunk
            $accountIds = array_unique(array_column($commissions, 'account_id'));
            $accounts = Account::whereIn('id', $accountIds)->get()->keyBy('id');
            $userIds = $accounts->pluck('user_id')->unique()->toArray();
            $users = User::whereIn('id', $userIds)->get()->keyBy('id');

            // Batch-load open deals for duration check
            $positionIds = array_unique(array_map(fn($c) => $c->position_id, $commissions));
            $accountIdList = array_unique(array_map(fn($c) => $c->deal_account_id, $commissions));
            $openDeals = DB::table('deals')
                ->whereIn('account_id', $accountIdList)
                ->whereIn('position_id', $positionIds)
                ->where('entry', 0)
                ->whereIn('action', [0, 1])
                ->get(['account_id', 'position_id', 'time_done'])
                ->groupBy(fn($d) => $d->account_id . '_' . $d->position_id);

            // Fallback open times from trades table
            $tradeOpenTimes = DB::table('trades')
                ->whereIn('account_id', $accountIdList)
                ->whereIn('position_id', $positionIds)
                ->get(['account_id', 'position_id', 'open_time'])
                ->groupBy(fn($t) => $t->account_id . '_' . $t->position_id);

            $walletsToInsert = [];
            $processedCommissionIds = [];

            foreach ($commissions as $comm) {
                $account = $accounts->get($comm->account_id);
                if (!$account) {
                    $totalSkipped++;
                    continue;
                }

                $user = $users->get($account->user_id);
                if (!$user || !$user->ib1 || $user->status != 1) {
                    $totalSkipped++;
                    continue;
                }

                // Symbol eligibility (use deal symbol if commission symbol is empty)
                $symbol = ($comm->symbol && $comm->symbol !== '') ? $comm->symbol : $comm->deal_symbol;
                $symbolPath = $symbolPaths[$symbol] ?? 'default/path';
                $isEligible = (bool)preg_match('/Forex|Metals/', $symbolPath);
                if (!$isEligible) {
                    $totalSkipped++;
                    continue;
                }

                // Duration check (>= 10 seconds)
                $key = $comm->deal_account_id . '_' . $comm->position_id;
                $openDeal = $openDeals->get($key)?->first();
                $openTime = $openDeal?->time_done ?? $tradeOpenTimes->get($key)?->first()?->open_time;
                if ($openTime) {
                    $duration = abs(strtotime($comm->time_done) - strtotime($openTime));
                    if ($duration < 10) {
                        $totalSkipped++;
                        continue;
                    }
                }

                // Get IB chain
                $ibChain = [];
                for ($level = 1; $level <= 15; $level++) {
                    $code = $user->{'ib' . $level};
                    if ($code) {
                        $ibChain[$level] = $code;
                    }
                }

                if (empty($ibChain)) {
                    $totalSkipped++;
                    continue;
                }

                // Get plan from ib1
                $ib1Code = $ibChain[1] ?? null;
                $ib1 = $ib1Code ? $ibUsers->get($ib1Code) : null;
                if (!$ib1) {
                    $totalSkipped++;
                    continue;
                }

                $planCategoryId = $ib1->planDetails?->ib_category_id;
                if (!$planCategoryId || !isset($planDetailsMap[$planCategoryId])) {
                    $totalSkipped++;
                    continue;
                }

                $plansForAccountType = $planDetailsMap[$planCategoryId][$account->account_type_id] ?? [];
                if (empty($plansForAccountType)) {
                    $totalSkipped++;
                    continue;
                }

                $planLevelId = array_key_first($plansForAccountType);
                $plan = $plansForAccountType[$planLevelId];
                $volume = ($comm->volume > 0) ? (float)$comm->volume : (float)$comm->deal_volume;

                // Distribute to each level
                foreach ($ibChain as $level => $referralCode) {
                    $ibUser = $ibUsers->get($referralCode);
                    if (!$ibUser) {
                        continue;
                    }

                    $dColumn = "d{$level}";
                    $rate = $plan->{$dColumn} ?? null;

                    // Hardcoded overrides
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

                    $walletAmount = $rate * $volume;
                    $formattedWallet = number_format($walletAmount, 10, '.', '');

                    $walletsToInsert[] = [
                        'id' => (string)Str::orderedUuid(),
                        'user_id' => $ibUser->user_id,
                        'account_id' => $comm->account_id,
                        'ib1_commission_id' => $comm->id,
                        'ib_wallet' => $formattedWallet,
                        'email' => $referralCode,
                        'code' => $account->code,
                        'order_id' => $comm->order_id,
                        'ib_level' => "IB Level {$level} - D{$level}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $processedCommissionIds[] = $comm->id;
                $totalProcessed++;
            }

            // Batch insert wallets (deduped)
            if (!empty($walletsToInsert)) {
                $walletsToInsert = collect($walletsToInsert)
                    ->unique(fn($w) => $w['order_id'] . '_' . $w['user_id'])
                    ->values()
                    ->toArray();

                foreach (array_chunk($walletsToInsert, 200) as $chunk) {
                    IbWallet::insertOrIgnore($chunk);
                }
                $totalWalletsCreated += count($walletsToInsert);
            }

            // Mark processed commissions status=1
            if (!empty($processedCommissionIds)) {
                foreach (array_chunk($processedCommissionIds, 500) as $chunk) {
                    Ib1Commission::whereIn('id', $chunk)
                        ->where('status', 0)
                        ->update(['status' => 1, 'updated_at' => now()]);
                }
            }

            Cache::put(
                $this->cacheKey . ':progress',
                "Step 3/5: Processed {$totalProcessed} commissions, {$totalWalletsCreated} wallets created...",
                3600
            );

            Log::info("ProcessStuckCommissionsJob: Chunk processed", [
                'chunk_size' => count($commissions),
                'total_processed' => $totalProcessed,
                'total_wallets' => $totalWalletsCreated,
                'total_skipped' => $totalSkipped,
            ]);
        }

        Log::info("ProcessStuckCommissionsJob: Wallet creation complete", [
            'processed' => $totalProcessed,
            'wallets_created' => $totalWalletsCreated,
            'skipped' => $totalSkipped,
        ]);

        return [
            'processed' => $totalProcessed,
            'wallets_created' => $totalWalletsCreated,
            'skipped' => $totalSkipped,
        ];
    }
}
