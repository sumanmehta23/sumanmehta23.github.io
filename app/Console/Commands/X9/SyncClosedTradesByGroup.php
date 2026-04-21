<?php

namespace App\Console\Commands\X9;

use App\Services\X9Service;
use App\Models\Account;
use App\Models\Trade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncClosedTradesByGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'x9:sync-closed-trades
                            {client_group_id : The client group ID to sync trades for}
                            {--date-from= : Start date in YYYY-MM-DD format (default: today)}
                            {--date-to= : End date in YYYY-MM-DD format (default: today)}
                            {--limit=100 : Maximum trades to return (1-1000)}
                            {--offset=0 : Offset for pagination}
                            {--save : Save trades to database}
                            {--log : Enable detailed logging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync closed trades for a specific client group from X9 API';

    protected $x9Service;

    public function __construct(X9Service $x9Service)
    {
        parent::__construct();
        $this->x9Service = $x9Service;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clientGroupId = $this->argument('client_group_id');
        $dateFrom = $this->option('date-from');
        $dateTo = $this->option('date-to');
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');
        $saveTrades = $this->option('save');
        $enableLogging = $this->option('log');

        $this->info('🔄 Starting X9 Closed Trades Sync');
        $this->line('====================================');
        $this->info("Client Group ID: {$clientGroupId}");
        if ($dateFrom) {
            $this->info("Date From: {$dateFrom}");
        }
        if ($dateTo) {
            $this->info("Date To: {$dateTo}");
        }
        $this->newLine();

        // Fetch closed trades from X9
        $result = $this->x9Service->getClosedTradesByGroup(
            $clientGroupId,
            $dateFrom,
            $dateTo,
            $limit,
            $offset
        );

        if (!$result['status']) {
            $this->error('❌ Failed to fetch closed trades: ' . $result['message']);
            if ($enableLogging) {
                Log::error('X9 Sync Closed Trades Failed', [
                    'client_group_id' => $clientGroupId,
                    'error' => $result['message']
                ]);
            }
            return 1;
        }

        $clientGroupName = $result['client_group_name'];
        $accountsCount = $result['accounts_count'];
        $trades = $result['trades'] ?? [];
        $summary = $result['summary'] ?? null;

        $this->info("✅ Retrieved trades for group: {$clientGroupName}");
        $this->info("Accounts in group: {$accountsCount}");
        $this->info("Total trades: " . count($trades));

        if ($summary) {
            $this->newLine();
            $this->info('📊 Summary Statistics:');
            $this->line('  Total Profit/Loss: ' . ($summary['total_profit_loss'] ?? 'N/A'));
            $this->line('  Winning Trades: ' . ($summary['winning_trades'] ?? 0));
            $this->line('  Losing Trades: ' . ($summary['losing_trades'] ?? 0));
            $this->line('  Win Rate: ' . ($summary['win_rate'] ?? 'N/A') . '%');
        }

        if (empty($trades)) {
            $this->info('No trades found for the specified period');
            return 0;
        }

        $saved = 0;
        $skipped = 0;
        $errors = 0;

        if ($saveTrades) {
            $this->newLine();
            $this->info('💾 Saving trades to database...');

            // Pre-load all accounts from the trades data to avoid repeated queries
            $accountNumbers = collect($trades)
                ->pluck('account_number')
                ->unique()
                ->filter()
                ->values()
                ->toArray();

            $accountsMap = Account::where('platform', Account::PLATFORM_X9)
                ->whereIn('code', $accountNumbers)
                ->pluck('id', 'code')
                ->toArray();

            $bar = $this->output->createProgressBar(count($trades));
            $bar->start();

            foreach ($trades as $tradeData) {
                try {
                    $accountNumber = $tradeData['account_number'] ?? null;
                    $ticketNumber = $tradeData['ticket_number'] ?? null;

                    if (!$accountNumber || !$ticketNumber) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    // Look up account from pre-loaded map instead of querying database
                    $accountId = $accountsMap[$accountNumber] ?? null;

                    if (!$accountId) {
                        $skipped++;
                        if ($enableLogging) {
                            Log::warning("X9 Account not found locally: {$accountNumber}");
                        }
                        $bar->advance();
                        continue;
                    }

                    // Check if trade already exists
                    $existingTrade = Trade::where('account_id', $accountId)
                        ->where('order_id', (string) $ticketNumber)
                        ->first();

                    // Use position_ticket from API as position_id
                    $positionId = $tradeData['position_ticket'] ?? abs(crc32($accountId . $ticketNumber)) % 2147483647;
                    $positionId = max($positionId, 1); // Ensure it's at least 1 (not zero)

                    $tradeData_to_save = [
                        'symbol' => $tradeData['symbol'] ?? null,
                        'position_id' => $positionId,
                        'type' => $this->mapTradeType($tradeData['order_type'] ?? null),
                        'volume' => $tradeData['closed_volume'] ?? $tradeData['open_volume'] ?? 0,
                        'open_price' => $tradeData['open_price'] ?? 0,
                        'close_price' => $tradeData['close_price'] ?? 0,
                        'profit' => $tradeData['profit_loss'] ?? 0,
                        'swap' => $tradeData['swap'] ?? 0,
                        'open_time' => isset($tradeData['open_time']) ? Carbon::parse($tradeData['open_time']) : now(),
                        'close_time' => isset($tradeData['close_time']) ? Carbon::parse($tradeData['close_time']) : null,
                        'status' => 'closed',
                        'comment' => $tradeData['comment'] ?? null,
                    ];

                    if ($existingTrade) {
                        // Update existing trade if it was open, now it's closed
                        if ($existingTrade->status !== 'closed') {
                            $existingTrade->update($tradeData_to_save);
                            $saved++;
                            if ($enableLogging) {
                                Log::info("X9 Trade updated to closed: {$accountNumber} - Ticket: {$ticketNumber}");
                            }
                        } else {
                            $skipped++;
                        }
                        $bar->advance();
                        continue;
                    }

                    try {
                        // Create new trade record
                        Trade::create(array_merge([
                            'account_id' => $accountId,
                            'order_id' => (string) $ticketNumber,
                            'code' => (string) $accountNumber,
                        ], $tradeData_to_save));

                        $saved++;

                        if ($enableLogging) {
                            Log::info("X9 Trade saved: {$accountNumber} - Ticket: {$ticketNumber}");
                        }
                    } catch (\Illuminate\Database\QueryException $qe) {
                        $errors++;
                        Log::error("X9 Trade save query error", [
                            'account' => $accountNumber ?? 'unknown',
                            'ticket' => $ticketNumber ?? 'unknown',
                            'error' => $qe->getMessage(),
                            'trace' => $qe->getTraceAsString()
                        ]);
                        if ($enableLogging) {
                            $this->error("Failed to save trade (query error): {$qe->getMessage()}");
                        }
                    } catch (\Exception $e) {
                        $errors++;
                        Log::error("X9 Trade save error", [
                            'account' => $accountNumber ?? 'unknown',
                            'ticket' => $ticketNumber ?? 'unknown',
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        if ($enableLogging) {
                            $this->error("Failed to save trade: {$e->getMessage()}");
                        }
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::error("X9 Trade save error", [
                        'account' => $accountNumber ?? 'unknown',
                        'ticket' => $ticketNumber ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    if ($enableLogging) {
                        $this->error("Failed to save trade: {$e->getMessage()}");
                    }
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
        }

        $this->info('✅ Sync Complete');
        $this->line('================');
        if ($saveTrades) {
            $this->info("Saved: {$saved}");
            $this->warn("Skipped: {$skipped}");
            if ($errors > 0) {
                $this->error("Errors: {$errors}");
            }
        }

        return 0;
    }

    /**
     * Map X9 trade type to internal format
     */
    private function mapTradeType($x9Type)
    {
        $typeMap = [
            'BUY' => 'buy',
            'SELL' => 'sell',
        ];

        return $typeMap[$x9Type] ?? 'buy';
    }
}
