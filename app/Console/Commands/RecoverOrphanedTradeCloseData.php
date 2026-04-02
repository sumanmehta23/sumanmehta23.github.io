<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Trade;
use App\MT5\MTRetCode;
use App\Services\QueueSafeMT5Service;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RecoverOrphanedTradeCloseData extends Command
{
    protected $signature = 'app:recover-orphaned-trade-close-data
                            {--account-code= : Recover orphaned trades for specific account}
                            {--limit=50 : Maximum orphaned trades to process}
                            {--debug : Show detailed debug information}
                            {--dry-run : Show what would be recovered without making changes}';

    protected $description = 'Recover close data for trades marked as closed but with null close_price/close_time. Queries minimal date range (min open_time to now).';

    protected $mt5Service;

    public function handle(): void
    {
        $this->mt5Service = app(QueueSafeMT5Service::class);

        $accountCode = $this->option('account-code');
        $limit = (int)$this->option('limit');
        $debug = $this->option('debug');
        $dryRun = $this->option('dry-run');

        // Get accounts to process
        $query = Account::where('demo', false)
            ->where('account_request_status', 1)
            ->whereNull('deleted_at');

        if ($accountCode) {
            $query->where('code', $accountCode);
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->warn('No accounts found.');
            return;
        }

        $totalRecovered = 0;
        $totalNotFound = 0;
        $totalErrors = 0;

        foreach ($accounts as $account) {
            $this->line("\n" . str_repeat('=', 60));
            $this->info("Processing Account: {$account->code}");

            // Get all orphaned trades for this account (closed but no close price/time)
            $orphanedTrades = Trade::where('account_id', $account->id)
                ->where('status', 'closed')
                ->where(function ($q) {
                    $q->whereNull('close_price')
                        ->orWhereNull('close_time');
                })
                ->limit($limit)
                ->get();

            if ($orphanedTrades->isEmpty()) {
                $this->info("✓ No orphaned trades found for {$account->code}");
                continue;
            }

            $this->info("Found {$orphanedTrades->count()} orphaned trades");

            // OPTIMIZATION: Get minimum open_time across all orphaned trades
            $minOpenTime = $orphanedTrades->min('open_time');
            $minOpenDateTime = new Carbon($minOpenTime);
            $searchFromDate = $minOpenDateTime->format('F d,Y');
            $searchToDate = now()->format('F d,Y');

            $daysToScan = $minOpenDateTime->diffInDays(now());
            $this->line("📅 Optimized date range: {$searchFromDate} to {$searchToDate} ({$daysToScan} days)");
            $this->line("   Min open_time: " . $minOpenDateTime->format('Y-m-d H:i:s'));

            // SINGLE API CALL to get all history for the date range
            $this->line("🔍 Fetching history from MT5...");
            $allHistory = $this->getHistoryForDateRange($account, $searchFromDate, $searchToDate, $debug);

            if (empty($allHistory)) {
                $this->warn("No history data retrieved for date range");
                $totalNotFound += $orphanedTrades->count();
                continue;
            }

            $this->info("Retrieved " . count($allHistory) . " history records from MT5");

            // DEBUG: Show structure of first history record
            if ($debug && !empty($allHistory)) {
                $this->line("\n📊 DEBUG: First history record structure:");
                $firstRecord = $allHistory[0];
                foreach ((array)$firstRecord as $field => $value) {
                    $valueStr = is_object($value) ? get_class($value) : (is_array($value) ? 'array' : (string)$value);
                    $this->line("    {$field}: {$valueStr}");
                }
                $this->line("");
            }

            // BATCH PROCESS: Search all orphaned trades within this single result set
            $searchCount = 0;
            foreach ($orphanedTrades as $trade) {
                $found = false;
                $foundData = null;
                $searchCount++;

                if ($debug) {
                    $this->line("  [{$searchCount}] Searching for position {$trade->position_id} (open: " . Carbon::create($trade->open_time)->format('Y-m-d H:i:s') . ")");
                }

                // Search for this position in the history
                $matchAttempts = 0;
                $potentialMatches = [];

                foreach ($allHistory as $historyRecord) {
                    $matchAttempts++;
                    // Match by multiple field names (ExpertPositionID, Order, Position, Ticket, ID)
                    $matchesPosition = false;
                    $matchField = null;

                    if (isset($historyRecord->ExpertPositionID) && $historyRecord->ExpertPositionID == $trade->position_id) {
                        $matchesPosition = true;
                        $matchField = 'ExpertPositionID';
                    } elseif (isset($historyRecord->Order) && $historyRecord->Order == $trade->position_id) {
                        $matchesPosition = true;
                        $matchField = 'Order';
                    } elseif (isset($historyRecord->Position) && $historyRecord->Position == $trade->position_id) {
                        $matchesPosition = true;
                        $matchField = 'Position';
                    } elseif (isset($historyRecord->Ticket) && $historyRecord->Ticket == $trade->position_id) {
                        $matchesPosition = true;
                        $matchField = 'Ticket';
                    } elseif (isset($historyRecord->ID) && $historyRecord->ID == $trade->position_id) {
                        $matchesPosition = true;
                        $matchField = 'ID';
                    }

                    if (!$matchesPosition) {
                        continue;
                    }

                    // Record this as a potential match before checking state
                    $potentialMatches[] = [
                        'field' => $matchField,
                        'record' => $historyRecord
                    ];

                    // Check if this is a close event (State == 'closed' or State == 3)
                    $isClosed = false;
                    $stateValue = $historyRecord->State ?? null;

                    if (isset($historyRecord->State)) {
                        if (is_string($historyRecord->State)) {
                            $isClosed = strtolower($historyRecord->State) === 'closed';
                        } elseif (is_numeric($historyRecord->State)) {
                            $isClosed = (int)$historyRecord->State === 3; // 3 = closed state
                        }
                    }

                    if ($debug) {
                        $this->line("      match_field={$matchField}, state={$stateValue}, is_closed={$isClosed}");
                    }

                    if (!$isClosed) {
                        continue;
                    }

                    // Found close event - extract close data
                    $closePrice = $historyRecord->ClosePrice ?? $historyRecord->Price ?? null;
                    $closeTime = $historyRecord->TimeDone ?? $historyRecord->CloseTime ?? null;

                    if ($closePrice !== null && $closeTime !== null) {
                        if ($debug) {
                            $this->line("    ✓ Found close event: price={$closePrice}, time={$closeTime}");
                        }

                        $foundData = [
                            'close_price' => $closePrice,
                            'close_time' => $closeTime,
                        ];
                        $found = true;
                        break;
                    } else {
                        if ($debug) {
                            $this->line("      close_price={$closePrice}, close_time={$closeTime} (incomplete)");
                        }
                    }
                }

                // DEBUG: Show why position wasn't found
                if (!$found && $debug && !empty($potentialMatches)) {
                    $matchCount = count($potentialMatches);
                    $this->line("      Found {$matchAttempts} records, {$matchCount} position matches but no closed state");
                }

                if ($found && $foundData) {
                    if ($dryRun) {
                        $this->line("    [DRY-RUN] Would update position {$trade->position_id} with close_price={$foundData['close_price']}, close_time={$foundData['close_time']}");
                        $totalRecovered++;
                    } else {
                        try {
                            $trade->update([
                                'close_price' => $foundData['close_price'],
                                'close_time' => $foundData['close_time'],
                                'updated_at' => now(),
                            ]);
                            $this->line("    ✓ Updated position {$trade->position_id}");
                            $totalRecovered++;
                        } catch (\Exception $e) {
                            $this->error("    ✗ Failed to update position {$trade->position_id}: {$e->getMessage()}");
                            $totalErrors++;
                        }
                    }
                } else {
                    $this->warn("    ✗ Position {$trade->position_id} close event not found in history");
                    $totalNotFound++;
                }
            }
        }

        $this->line("\n" . str_repeat('=', 60));
        $this->info("Recovery Summary:");
        $this->line("✓ Recovered: {$totalRecovered}");
        $this->line("✗ Not found: {$totalNotFound}");
        $this->line("✗ Errors: {$totalErrors}");
        $this->line(str_repeat('=', 60));

        if (!$dryRun) {
            Log::info("RecoverOrphanedTradeCloseData completed", [
                'total_recovered' => $totalRecovered,
                'not_found' => $totalNotFound,
                'errors' => $totalErrors,
            ]);
        }
    }

    /**
     * Get all history records for a date range (single API call)
     * 
     * @param Account $account
     * @param string $fromDate Format: "September 01,2024"
     * @param string $toDate Format: "September 01,2024"
     * @param bool $debug
     * @return array All history records
     */
    private function getHistoryForDateRange(Account $account, string $fromDate, string $toDate, bool $debug = false): array
    {
        try {
            $login = $account->code;
            $allHistory = [];

            // Get total records in range
            $total = 0;
            $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, $fromDate, $toDate, &$total) {
                return $api->HistoryGetTotal($login, $fromDate, $toDate, $total);
            });

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("Failed to get history total for {$login}", [
                    'error_code' => $error_code,
                    'error' => MTRetCode::GetError($error_code),
                ]);
                return [];
            }

            if ($debug) {
                $this->line("    Total history records available: {$total}");
            }

            // Paginate through all history
            $pageSize = 100;
            $pageCount = 0;
            $position = 0;

            while ($position < $total) {
                $pageCount++;
                $pageHistory = [];
                $recordsToFetch = min($pageSize, $total - $position);

                if ($debug) {
                    $this->line("    Fetching page {$pageCount}: position={$position}, size={$recordsToFetch}");
                }

                $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, $fromDate, $toDate, $position, $recordsToFetch, &$pageHistory) {
                    return $api->HistoryGetPage($login, $fromDate, $toDate, $position, $recordsToFetch, $pageHistory);
                });

                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::error("Failed to get history page for {$login}", [
                        'page' => $pageCount,
                        'position' => $position,
                        'error_code' => $error_code,
                        'error' => MTRetCode::GetError($error_code),
                    ]);
                    break;
                }

                if (empty($pageHistory)) {
                    if ($debug) {
                        $this->line("    Empty page response, stopping pagination");
                    }
                    break;
                }

                $allHistory = array_merge($allHistory, $pageHistory);
                $position += count($pageHistory);

                if ($debug) {
                    $this->line("    Retrieved " . count($pageHistory) . " records, total so far: " . count($allHistory));
                }
            }

            Log::info("Retrieved history for recovery", [
                'account' => $login,
                'total_records' => count($allHistory),
                'pages_fetched' => $pageCount,
                'date_range' => "{$fromDate} to {$toDate}",
            ]);

            return $allHistory;
        } catch (\Exception $e) {
            Log::error("Exception in getHistoryForDateRange", [
                'error' => $e->getMessage(),
                'account' => $account->code,
            ]);
            return [];
        }
    }
}
