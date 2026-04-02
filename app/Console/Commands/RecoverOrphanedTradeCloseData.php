<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Trade;
use App\Services\QueueSafeMT5Service;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RecoverOrphanedTradeCloseData extends Command
{
    protected $signature = 'app:recover-orphaned-trade-close-data 
                            {--account-code= : Recover specific account by code}
                            {--limit=50 : Maximum trades to process (default: 50)}
                            {--dry-run : Show what would be recovered without making changes}';

    protected $description = 'Recover actual close data for trades that were closed as orphaned with null close_price and close_time';

    protected $mt5Service;

    public function handle(): void
    {
        $dryRun = $this->option('dry-run');
        $accountCode = $this->option('account-code');
        $limit = (int) $this->option('limit');

        if ($dryRun) {
            $this->warn('DRY-RUN MODE: No changes will be made');
        }

        $this->mt5Service = app(QueueSafeMT5Service::class);

        // Find trades that were closed as orphaned (null close data)
        $query = Trade::where('status', 'closed')
            ->whereNull('close_price')
            ->whereNull('close_time');

        if ($accountCode) {
            $account = Account::where('code', $accountCode)->first();
            if (!$account) {
                $this->error("Account with code '{$accountCode}' not found");
                return;
            }
            $query->where('account_id', $account->id);
            $this->info("Filtering to account: {$accountCode}");
        }

        $trades = $query->limit($limit)->get();
        $totalFound = $query->count();

        if ($trades->isEmpty()) {
            $this->info('No orphaned trades found to recover');
            return;
        }

        $this->warn("Found {$totalFound} orphaned closed trades with null close data");
        $this->info("Processing first {$trades->count()} trades...\n");

        $recovered = 0;
        $notFound = 0;
        $failed = 0;

        foreach ($trades as $trade) {
            $account = $trade->account;
            $positionId = $trade->position_id;

            $this->line("Position {$positionId} (Account: {$account->code}):");

            try {
                $closeData = $this->getPositionCloseData($account, $positionId);

                if ($closeData) {
                    $this->line("  ✓ Found close data: price={$closeData['close_price']}, time={$closeData['close_time']}");

                    if (!$dryRun) {
                        $trade->update([
                            'close_price' => $closeData['close_price'],
                            'close_time' => $closeData['close_time'],
                            'updated_at' => now(),
                        ]);
                        $this->line("  ✓ Updated in database");
                    }
                    $recovered++;
                } else {
                    $this->line("  ✗ No close event found in MT5 history - keeping as orphaned");
                    $notFound++;
                }
            } catch (Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
                $failed++;
            }

            $this->line("");
        }

        $this->newLine();
        $this->info("Summary:");
        $this->line("  Recovered: {$recovered}");
        $this->line("  Not found in history: {$notFound}");
        $this->line("  Failed: {$failed}");

        if ($totalFound > $limit) {
            $this->warn("Showing {$limit} of {$totalFound} trades. Run again or increase --limit to process more.");
        }
    }

    /**
     * Try to fetch actual close data for a position from MT5 history
     * 
     * @param Account $account The account
     * @param int $positionId The position ID to find
     * @return array|null Array with close_price and close_time, or null if not found
     */
    private function getPositionCloseData(Account $account, int $positionId): ?array
    {
        try {
            // Query a wide date range to find the close event
            $fromDate = 'September 01,2024';
            $toDate = now()->format('F d,Y');

            $total = 0;
            $this->mt5Service->executeOperation(function ($api) use ($account, $fromDate, $toDate, &$total) {
                return $api->HistoryGetTotal($account->code, $fromDate, $toDate, $total);
            });

            if ($total === 0) {
                return null;
            }

            // Fetch orders in batches, looking for this position
            $pageSize = 100;
            for ($position = 0; $position < $total; $position += $pageSize) {
                $pageOrders = [];
                $this->mt5Service->executeOperation(function ($api) use ($account, $fromDate, $toDate, $position, $pageSize, &$pageOrders) {
                    return $api->HistoryGetPage($account->code, $fromDate, $toDate, $position, $pageSize, $pageOrders);
                });

                if (empty($pageOrders)) {
                    break;
                }

                // Search for close event (latest event for this position with 2+ orders)
                $positionOrders = collect($pageOrders)
                    ->where('ExpertPositionID', $positionId)
                    ->sortByDesc('TimeDone')
                    ->toArray();

                if (count($positionOrders) >= 2) {
                    // Found both open and close events - get the close
                    $closeOrder = array_values($positionOrders)[0]; // First (latest) is close

                    return [
                        'close_price' => (float)$closeOrder->PriceCurrent,
                        'close_time' => date('Y-m-d H:i:s', $closeOrder->TimeDone),
                    ];
                }
            }

            return null;
        } catch (Exception $e) {
            Log::warning("Failed to fetch close data for position {$positionId}", [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
