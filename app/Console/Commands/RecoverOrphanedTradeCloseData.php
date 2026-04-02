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
                            {--position-id= : Recover specific position by ID (for testing)}
                            {--limit=50 : Maximum trades to process (default: 50)}
                            {--dry-run : Show what would be recovered without making changes}
                            {--debug : Show verbose debug information}';

    protected $description = 'Recover actual close data for trades that were closed as orphaned with null close_price and close_time';

    protected $mt5Service;
    protected bool $debug = false;

    public function handle(): void
    {
        $dryRun = $this->option('dry-run');
        $accountCode = $this->option('account-code');
        $positionIdOption = $this->option('position-id');
        $limit = (int) $this->option('limit');
        $this->debug = $this->option('debug');

        if ($dryRun) {
            $this->warn('DRY-RUN MODE: No changes will be made');
        }

        if ($this->debug) {
            $this->comment('DEBUG MODE: Verbose output enabled');
        }

        $this->mt5Service = app(QueueSafeMT5Service::class);

        // If testing a specific position
        if ($positionIdOption && $accountCode) {
            $account = Account::where('code', $accountCode)->first();
            if (!$account) {
                $this->error("Account with code '{$accountCode}' not found");
                return;
            }
            $this->info("Testing position {$positionIdOption} for account {$accountCode}");
            $closeData = $this->getPositionCloseData($account, (int)$positionIdOption);
            if ($closeData) {
                $this->line("✓ Found close data: price={$closeData['close_price']}, time={$closeData['close_time']}");
            } else {
                $this->error("✗ No close data found for position {$positionIdOption}");
            }
            return;
        }

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

            if ($this->debug) {
                $this->comment("  [DEBUG] Querying from {$fromDate} to {$toDate}");
            }

            $total = 0;
            $this->mt5Service->executeOperation(function ($api) use ($account, $fromDate, $toDate, &$total) {
                return $api->HistoryGetTotal($account->code, $fromDate, $toDate, $total);
            });

            if ($this->debug) {
                $this->comment("  [DEBUG] Total records in history: {$total}");
            }

            if ($total === 0) {
                return null;
            }

            // Fetch orders in batches, looking for this position
            $pageSize = 100;
            $allMatchingOrders = [];
            $firstRecordSeen = false;

            for ($position = 0; $position < $total; $position += $pageSize) {
                $pageOrders = [];
                $this->mt5Service->executeOperation(function ($api) use ($account, $fromDate, $toDate, $position, $pageSize, &$pageOrders) {
                    return $api->HistoryGetPage($account->code, $fromDate, $toDate, $position, $pageSize, $pageOrders);
                });

                if (empty($pageOrders)) {
                    break;
                }

                // Show first record structure for debugging
                if (!$firstRecordSeen && $this->debug) {
                    $firstRecord = $pageOrders[0];
                    $this->comment("  [DEBUG] First record fields: " . implode(", ", array_keys((array)$firstRecord)));
                    $firstRecordSeen = true;
                }

                // Search for orders matching this position ID
                // Try multiple field names that might contain the position ID
                if (is_array($pageOrders)) {
                    foreach ($pageOrders as $order) {
                        if (!is_object($order)) {
                            continue;
                        }

                        $matches = false;
                        $matchedField = null;

                        // Check ExpertPositionID field (primary)
                        if (isset($order->ExpertPositionID) && $order->ExpertPositionID == $positionId) {
                            $matches = true;
                            $matchedField = 'ExpertPositionID';
                        }
                        // Check Order field (might be position ID for single-order positions)
                        elseif (isset($order->Order) && $order->Order == $positionId) {
                            $matches = true;
                            $matchedField = 'Order';
                        }
                        // Check Position field if it exists
                        elseif (isset($order->Position) && $order->Position == $positionId) {
                            $matches = true;
                            $matchedField = 'Position';
                        }

                        if ($matches) {
                            if ($this->debug) {
                                $this->comment("  [DEBUG] Found matching order: {$matchedField}={$positionId}, Time=" . date('Y-m-d H:i:s', $order->TimeDone) . ", Price=" . $order->PriceCurrent);
                            }
                            $allMatchingOrders[] = $order;
                        }
                    }
                }
            }

            if ($this->debug) {
                $this->comment("  [DEBUG] Total matching orders found: " . count($allMatchingOrders));
            }

            if (empty($allMatchingOrders)) {
                if ($this->debug) {
                    $this->comment("  [DEBUG] No matching orders found for position {$positionId}");
                }
                return null;
            }

            // Sort by time descending to get the latest (close)
            $allMatchingOrders = collect($allMatchingOrders)
                ->sortByDesc('TimeDone')
                ->toArray();

            // Try to find close data
            if (count($allMatchingOrders) >= 2) {
                // Multiple orders - get the latest one (close)
                $closeOrder = $allMatchingOrders[0];
                if ($this->debug) {
                    $this->comment("  [DEBUG] Multiple orders found (" . count($allMatchingOrders) . "), using latest as close");
                }
                return [
                    'close_price' => (float)$closeOrder->PriceCurrent,
                    'close_time' => date('Y-m-d H:i:s', $closeOrder->TimeDone),
                ];
            } elseif (count($allMatchingOrders) == 1) {
                // Single order - check if it has close information
                $order = $allMatchingOrders[0];

                if ($this->debug) {
                    $this->comment("  [DEBUG] Single order found, State=" . ($order->State ?? 'N/A'));
                }

                // If this is a closed order, use its data
                if (isset($order->State) && ($order->State == 'closed' || $order->State == 3)) {
                    if ($this->debug) {
                        $this->comment("  [DEBUG] Single order is closed, using its data");
                    }
                    return [
                        'close_price' => (float)$order->PriceCurrent,
                        'close_time' => date('Y-m-d H:i:s', $order->TimeDone),
                    ];
                }
            }

            if ($this->debug) {
                $this->comment("  [DEBUG] No close data extracted from matching orders");
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
