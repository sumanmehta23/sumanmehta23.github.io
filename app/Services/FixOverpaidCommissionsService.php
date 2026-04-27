<?php

namespace App\Services;

use App\Models\IbWallet;
use Illuminate\Support\Facades\DB;

class FixOverpaidCommissionsService
{
    /**
     * Fix overpaid commissions for a specific expert_position and user
     * 
     * Only soft-deletes non-withdrawn overpaid entries
     * Keeps and flags the primary (largest) payment
     * Tracks which entries are problematic
     */
    public function fixForDuplicateGroup(string $expertPositionId, string $userId): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'fixed_count' => 0,
            'fixed_amount' => 0,
            'withdrawn_skipped' => 0,
            'timeline' => [],
            'primary_wallet_id' => null,
            'error' => null,
        ];

        try {
            // Get all wallets for this group
            $wallets = DB::table('ib_wallet as w')
                ->join('ib1_commission as c', 'c.id', '=', 'w.ib1_commission_id')
                ->where('c.expert_position_id', $expertPositionId)
                ->where('w.user_id', $userId)
                ->select(
                    'w.id',
                    'w.ib1_commission_id',
                    'w.order_id',
                    DB::raw('CAST(w.ib_wallet AS DECIMAL(20,10)) as amount'),
                    'w.ib_withdraw',
                    'c.volume as commission_volume',
                    'c.status',
                    'w.created_at'
                )
                ->orderBy('w.ib_wallet', 'DESC')
                ->get();

            if ($wallets->isEmpty()) {
                $result['error'] = 'No wallets found for this expert position and user';
                return $result;
            }

            // Find primary wallet (largest amount, not withdrawn)
            $primaryWallet = null;
            foreach ($wallets as $w) {
                if ($w->ib_withdraw === null) {
                    $primaryWallet = $w;
                    break;
                }
            }

            // If no non-withdrawn wallet, pick the largest overall
            if (!$primaryWallet) {
                $primaryWallet = $wallets->first();
            }

            $result['primary_wallet_id'] = $primaryWallet->id;

            // Build timeline and collect entries to soft-delete
            $toDelete = [];
            $withdrawnCount = 0;
            $totalAmount = 0;

            foreach ($wallets as $w) {
                $timeline = [
                    'wallet_id' => $w->id,
                    'order_id' => $w->order_id,
                    'amount' => $w->amount,
                    'commission_volume' => $w->commission_volume,
                    'created_at' => $w->created_at,
                    'withdrawn' => $w->ib_withdraw !== null,
                    'is_primary' => $w->id === $primaryWallet->id,
                ];

                if ($w->id === $primaryWallet->id) {
                    $timeline['note'] = 'PRIMARY - KEPT';
                } else if ($w->ib_withdraw !== null) {
                    // Already withdrawn - cannot fix
                    $timeline['note'] = 'SKIPPED - Already withdrawn';
                    $withdrawnCount++;
                } else {
                    // Can delete
                    $timeline['note'] = 'TO DELETE - Overpaid, not withdrawn';
                    $toDelete[] = $w->id;
                    $totalAmount += $w->amount;
                }

                $result['timeline'][] = $timeline;
            }

            // Soft-delete the overpaid entries
            if (!empty($toDelete)) {
                DB::table('ib_wallet')
                    ->whereIn('id', $toDelete)
                    ->update([
                        'overpayment_flag' => 'flagged',
                        'primary_wallet_id' => $primaryWallet->id,
                        'deleted_at' => now(),
                        'updated_at' => now(),
                    ]);

                $result['fixed_count'] = count($toDelete);
                $result['fixed_amount'] = round($totalAmount, 4);
            }

            $result['withdrawn_skipped'] = $withdrawnCount;
            $result['success'] = true;
            $fixedAmount = $result['fixed_amount'];
            $result['message'] = "Fixed {$result['fixed_count']} overpaid entries (\${$fixedAmount}) for expert position {$expertPositionId}. {$withdrawnCount} entries skipped (already withdrawn).";

            return $result;
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            return $result;
        }
    }

    /**
     * Get fixable overpaid commissions (non-withdrawn duplicates only)
     */
    public function getFixableDuplicates(): array
    {
        $duplicates = DB::table('ib1_commission as c')
            ->join('ib_wallet as w', 'w.ib1_commission_id', '=', 'c.id')
            ->select(
                'c.expert_position_id',
                'w.user_id',
                DB::raw('COUNT(DISTINCT w.order_id) as order_count'),
                DB::raw('COUNT(CASE WHEN w.ib_withdraw IS NULL THEN 1 END) as recoverable_count'),
                DB::raw('SUM(CASE WHEN w.ib_withdraw IS NULL THEN CAST(w.ib_wallet AS DECIMAL(20,10)) ELSE 0 END) as recoverable_amount')
            )
            ->whereNull('c.deleted_at')
            ->whereNotNull('c.expert_position_id')
            ->whereNull('w.deleted_at')
            ->groupBy('c.expert_position_id', 'w.user_id')
            ->havingRaw('COUNT(DISTINCT w.order_id) > 1')
            ->havingRaw('COUNT(CASE WHEN w.ib_withdraw IS NULL THEN 1 END) > 0')
            ->get();

        return $duplicates->map(fn($d) => [
            'expert_position_id' => $d->expert_position_id,
            'user_id' => $d->user_id,
            'order_count' => $d->order_count,
            'recoverable_count' => $d->recoverable_count,
            'recoverable_amount' => round($d->recoverable_amount, 4),
        ])->toArray();
    }

    /**
     * Get commission timeline for a specific expert position and user
     * Shows what was paid, when, and withdrawal status
     */
    public function getCommissionTimeline(string $expertPositionId, string $userId): array
    {
        $wallets = DB::table('ib_wallet as w')
            ->join('ib1_commission as c', 'c.id', '=', 'w.ib1_commission_id')
            ->where('c.expert_position_id', $expertPositionId)
            ->where('w.user_id', $userId)
            ->select(
                'w.id',
                'w.order_id',
                DB::raw('CAST(w.ib_wallet AS DECIMAL(20,10)) as amount'),
                'w.ib_withdraw',
                'c.volume',
                'c.status',
                'w.created_at',
                'w.overpayment_flag',
                'w.primary_wallet_id'
            )
            ->orderBy('w.created_at')
            ->get();

        // Calculate running total and identify the primary
        $timeline = [];
        $runningTotal = 0;
        $maxAmount = 0;
        $primaryWalletId = null;

        foreach ($wallets as $w) {
            if ($w->amount > $maxAmount) {
                $maxAmount = $w->amount;
                $primaryWalletId = $w->id;
            }
        }

        foreach ($wallets as $idx => $w) {
            $runningTotal += $w->amount;
            $isPrimary = $w->id === $primaryWalletId;

            $timeline[] = [
                'sequence' => $idx + 1,
                'wallet_id' => $w->id,
                'order_id' => $w->order_id,
                'amount' => round($w->amount, 4),
                'commission_volume' => round($w->volume, 4),
                'running_total' => round($runningTotal, 4),
                'withdrawn' => $w->ib_withdraw,
                'withdrawal_date' => $w->ib_withdraw ? 'Yes' : 'No',
                'is_primary' => $isPrimary,
                'overpayment_flag' => $w->overpayment_flag,
                'status' => $w->status,
                'created_at' => is_string($w->created_at) ? $w->created_at : $w->created_at->format('Y-m-d H:i:s'),
                'note' => $isPrimary ? 'PRIMARY' : ($w->ib_withdraw ? 'WITHDRAWN' : 'PENDING'),
            ];
        }

        return [
            'expert_position_id' => $expertPositionId,
            'user_id' => $userId,
            'total_entries' => count($timeline),
            'total_paid' => round($runningTotal, 4),
            'primary_wallet_id' => $primaryWalletId,
            'timeline' => $timeline,
        ];
    }
}
