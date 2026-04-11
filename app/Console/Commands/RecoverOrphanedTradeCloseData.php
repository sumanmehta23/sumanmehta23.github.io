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

    protected $description = 'Recover close data for trades marked as closed but with null close_price/close_time. Processes history page-by-page with early exit optimization.';

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
        $totalEarlyExits = 0;

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

            if ($debug) {
                $this->line("   📍 Position IDs we're searching for:");
                $orphanedTrades->each(function ($trade) {
                    $this->line("      - Position: {$trade->position_id}, open_time: {$trade->open_time}, open_price: {$trade->open_price}");
                });
            }

            // OPTIMIZATION: Get minimum open_time across all orphaned trades
            $minOpenTime = $orphanedTrades->min('open_time');
            $minOpenDateTime = new Carbon($minOpenTime);
            $searchFromDate = $minOpenDateTime->format('F d,Y');
            $searchToDate = now()->format('F d,Y');

            $daysToScan = $minOpenDateTime->diffInDays(now());
            $this->line("📅 Optimized date range: {$searchFromDate} to {$searchToDate} ({$daysToScan} days)");
            $this->line("   Min open_time: " . $minOpenDateTime->format('Y-m-d H:i:s'));

            // SMART OPTIMIZATION: Process history page-by-page with early exit
            // Fetch one page, search orphaned trades, update matches, repeat
            // Exit when all trades are fixed or no more history
            $this->line("🔍 Processing history pages (smart early exit enabled)...");
            $result = $this->processHistoryWithEarlyExit(
                $account,
                $orphanedTrades->keyBy('position_id'),
                $searchFromDate,
                $searchToDate,
                $debug,
                $dryRun
            );

            $totalRecovered += $result['recovered'];
            $totalNotFound += $result['not_found'];
            $totalErrors += $result['errors'];

            if ($result['early_exit']) {
                $totalEarlyExits++;
                $this->line("✓ All orphaned trades recovered! Exiting early after {$result['pages_scanned']} pages.");
            }
        }

        $this->line("\n" . str_repeat('=', 60));
        $this->info("Recovery Summary:");
        $this->line("✓ Recovered: {$totalRecovered}");
        $this->line("✗ Not found: {$totalNotFound}");
        $this->line("✗ Errors: {$totalErrors}");
        $this->line("🚀 Early exits: {$totalEarlyExits}");
        $this->line(str_repeat('=', 60));

        if (!$dryRun) {
            Log::info("RecoverOrphanedTradeCloseData completed", [
                'total_recovered' => $totalRecovered,
                'not_found' => $totalNotFound,
                'errors' => $totalErrors,
                'early_exits' => $totalEarlyExits,
            ]);
        }
    }

    /**
     * Process history page-by-page with early exit when all trades are recovered
     * This is much smarter than loading all pages first
     * 
     * @param Account $account
     * @param \Illuminate\Support\Collection $orphanedTrades Keyed by position_id
     * @param string $searchFromDate
     * @param string $searchToDate
     * @param bool $debug
     * @param bool $dryRun
     * @return array Result with recovered/not_found/errors/early_exit counts
     */
    private function processHistoryWithEarlyExit(Account $account, $orphanedTrades, string $searchFromDate, string $searchToDate, bool $debug = false, bool $dryRun = false): array
    {
        $login = $account->code;
        $recovered = 0;
        $errors = 0;
        $pagesScanned = 0;
        $earlyExit = false;

        // Get total records in the date range
        $total = 0;
        $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, $searchFromDate, $searchToDate, &$total) {
            return $api->HistoryGetTotal($login, $searchFromDate, $searchToDate, $total);
        });

        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::error("Failed to get history total for {$login}", [
                'error_code' => $error_code,
                'error' => MTRetCode::GetError($error_code),
            ]);
            return [
                'recovered' => 0,
                'not_found' => count($orphanedTrades),
                'errors' => 0,
                'early_exit' => false,
                'pages_scanned' => 0,
            ];
        }

        if ($debug) {
            $this->line("   Total history records available: {$total}");
        }

        $pageSize = 100;
        $position = 0;
        $remainingOrphans = $orphanedTrades->count();

        // SMART PAGINATION: Process one page at a time
        while ($position < $total && $remainingOrphans > 0) {
            $pagesScanned++;
            $pageHistory = [];
            $recordsToFetch = min($pageSize, $total - $position);

            if ($debug) {
                $this->line("   📄 Page {$pagesScanned}: position={$position}, size={$recordsToFetch}, remaining_orphans={$remainingOrphans}");
            }

            // Fetch one page from MT5
            $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, $searchFromDate, $searchToDate, $position, $recordsToFetch, &$pageHistory) {
                return $api->HistoryGetPage($login, $searchFromDate, $searchToDate, $position, $recordsToFetch, $pageHistory);
            });

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("Failed to get history page for {$login}", [
                    'page' => $pagesScanned,
                    'position' => $position,
                    'error_code' => $error_code,
                    'error' => MTRetCode::GetError($error_code),
                ]);
                break;
            }

            if (empty($pageHistory)) {
                if ($debug) {
                    $this->line("   Empty page response, stopping pagination");
                }
                break;
            }

            // DEBUG: Show first record structure
            if ($debug && $pagesScanned === 1 && !empty($pageHistory)) {
                $this->line("\n   📊 First history record structure:");
                $firstRecord = $pageHistory[0];
                foreach ((array)$firstRecord as $field => $value) {
                    $valueStr = is_object($value) ? get_class($value) : (is_array($value) ? 'array' : (string)$value);
                    $this->line("      {$field}: {$valueStr}");
                }
                $this->line("");
            }

            // PROCESS THIS PAGE: Search for matches among orphaned trades
            $recordIndex = 0;
            foreach ($pageHistory as $historyRecord) {
                $recordIndex++;
                if (!is_object($historyRecord)) {
                    continue;
                }

                // Try to match this record to one of our orphaned trades
                $positionId = $this->extractPositionId($historyRecord);

                // Show first few extractions if debug mode
                if ($debug && $pagesScanned === 1 && $recordIndex <= 3) {
                    $this->line("      History record #{$recordIndex}: extracted positionId={$positionId}");
                }

                if ($positionId === null || !$orphanedTrades->has($positionId)) {
                    continue; // Not relevant to our orphaned trades
                }

                $trade = $orphanedTrades->get($positionId);

                // Check if this is a close event
                $isClosed = $this->isCloseEvent($historyRecord);
                if (!$isClosed) {
                    continue; // Not the close event
                }

                // Extract close data
                $closePrice = null;
                $closeTime = null;

                if (property_exists($historyRecord, 'ClosePrice')) {
                    $closePrice = $historyRecord->ClosePrice;
                } elseif (property_exists($historyRecord, 'Price')) {
                    $closePrice = $historyRecord->Price;
                }

                if (property_exists($historyRecord, 'TimeDone')) {
                    $closeTime = $historyRecord->TimeDone;
                } elseif (property_exists($historyRecord, 'CloseTime')) {
                    $closeTime = $historyRecord->CloseTime;
                }

                if ($closePrice === null || $closeTime === null) {
                    continue; // Incomplete data
                }

                // Found a match! Update it
                if ($debug) {
                    $this->line("   ✓ Found close data for position {$positionId}: price={$closePrice}, time={$closeTime}");
                }

                if ($dryRun) {
                    $this->line("     [DRY-RUN] Would update position {$positionId}");
                    $recovered++;
                } else {
                    try {
                        $trade->update([
                            'close_price' => $closePrice,
                            'close_time' => $closeTime,
                            'updated_at' => now(),
                        ]);
                        $this->line("     ✓ Updated position {$positionId}");
                        $recovered++;
                    } catch (\Exception $e) {
                        $this->error("     ✗ Failed to update position {$positionId}: {$e->getMessage()}");
                        $errors++;
                    }
                }

                // Remove from orphaned list since we found it
                $orphanedTrades->forget($positionId);
                $remainingOrphans--;

                // EARLY EXIT: All trades recovered!
                if ($remainingOrphans === 0) {
                    $earlyExit = true;
                    if ($debug) {
                        $this->line("   🚀 EARLY EXIT: All orphaned trades found!");
                    }
                    break 2; // Break both loops
                }
            }

            $position += count($pageHistory);
        }

        return [
            'recovered' => $recovered,
            'not_found' => $remainingOrphans,
            'errors' => $errors,
            'early_exit' => $earlyExit,
            'pages_scanned' => $pagesScanned,
        ];
    }

    /**
     * Extract position ID from history record (try multiple field names)
     * 
     * @param object $historyRecord
     * @return int|null Position ID or null if not found
     */
    private function extractPositionId($historyRecord): ?int
    {
        $fields = ['ExpertPositionID', 'Order', 'Position', 'Ticket', 'ID'];

        foreach ($fields as $field) {
            if (property_exists($historyRecord, $field)) {
                $value = $historyRecord->$field;
                if (!empty($value)) {
                    return (int)$value;
                }
            }
        }

        return null;
    }

    /**
     * Check if a history record represents a close event
     * 
     * @param object $historyRecord
     * @return bool
     */
    private function isCloseEvent($historyRecord): bool
    {
        if (!property_exists($historyRecord, 'State')) {
            return false;
        }

        $state = $historyRecord->State;

        if (is_string($state)) {
            return strtolower($state) === 'closed';
        }

        if (is_numeric($state)) {
            $stateValue = (int)$state;
            // State 3 = closed, State 4 = done/completed trade
            return $stateValue === 3 || $stateValue === 4;
        }

        return false;
    }
}
