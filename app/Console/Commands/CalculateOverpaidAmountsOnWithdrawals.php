<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class CalculateOverpaidAmountsOnWithdrawals extends Command
{
    protected $signature = 'commissions:calculate-overpaid-on-withdrawals 
                            {--user_id= : Calculate for specific user only}
                            {--sync : Process synchronously instead of queueing}';

    protected $description = 'Calculate and track overpaid amounts on each IB withdrawal';

    public function handle()
    {
        $this->info('🔍 Calculating overpaid amounts on IB withdrawals...');

        $userId = $this->option('user_id');

        // Get all withdrawals that need overpaid amount calculation
        $query = DB::table('wallet_withdraw as ww')
            ->leftJoin('ib_wallet as iw', 'iw.user_id', '=', 'ww.user_id')
            ->whereNotNull('iw.deleted_at') // Only soft-deleted (flagged) entries
            ->where('iw.overpayment_flag', 'flagged')
            ->select(
                'ww.id',
                'ww.user_id',
                DB::raw('SUM(CAST(iw.ib_wallet AS DECIMAL(20,10))) as total_overpaid'),
                DB::raw('COUNT(DISTINCT iw.id) as overpaid_entry_count')
            )
            ->groupBy('ww.id', 'ww.user_id')
            ->orderBy('ww.created_at', 'DESC');

        if ($userId) {
            $query->where('ww.user_id', $userId);
            $this->info("Filtering for user: {$userId}");
        }

        $withdrawals = $query->get();

        if ($withdrawals->isEmpty()) {
            $this->info('✓ No withdrawals to process');
            return 0;
        }

        $this->info("Found {$withdrawals->count()} withdrawals to process");

        $processed = 0;
        $totalOverpaid = 0;

        foreach ($withdrawals as $withdrawal) {
            // Update withdrawal with overpaid amount
            DB::table('wallet_withdraw')
                ->where('id', $withdrawal->id)
                ->update([
                    'overpaid_amount' => $withdrawal->total_overpaid,
                    'has_overpaid' => 1,
                ]);

            $this->line("✓ user_id={$withdrawal->user_id} withdrawal overpaid={$withdrawal->total_overpaid} entries={$withdrawal->overpaid_entry_count}");
            $processed++;
            $totalOverpaid += $withdrawal->total_overpaid;
        }

        $this->info("\n✅ Completed!");
        $this->info("Processed: {$processed} withdrawals");
        $this->info("Total overpaid tracked: \${$totalOverpaid}");

        return 0;
    }
}
