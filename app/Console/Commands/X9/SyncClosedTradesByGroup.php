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

                    // Find the account in local database
                    $account = Account::where('platform', Account::PLATFORM_X9)
                        ->where('code', $accountNumber)
                        ->first();

                    if (!$account) {
                        $skipped++;
                        if ($enableLogging) {
                            Log::warning("X9 Account not found locally: {$accountNumber}");
                        }
                        $bar->advance();
                        continue;
                    }

                    // Check if trade already exists
                    $existingTrade = Trade::where('account_id', $account->id)
                        ->where('ticket', $ticketNumber)
                        ->first();

                    if ($existingTrade) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    // Create new trade record
                    Trade::create([
                        'account_id' => $account->id,
                        'ticket' => $ticketNumber,
                        'symbol' => $tradeData['symbol'] ?? null,
                        'type' => $this->mapTradeType($tradeData['ticket_open_as'] ?? null),
                        'volume' => $tradeData['order_volume'] ?? 0,
                        'open_price' => $tradeData['open_price'] ?? 0,
                        'close_price' => $tradeData['close_price'] ?? 0,
                        'profit' => $tradeData['profit_loss'] ?? 0,
                        'swap' => $tradeData['swap'] ?? 0,
                        'commission' => $tradeData['commission'] ?? 0,
                        'open_time' => isset($tradeData['open_time']) ? Carbon::parse($tradeData['open_time']) : null,
                        'close_time' => isset($tradeData['close_time']) ? Carbon::parse($tradeData['close_time']) : null,
                        'comment' => $tradeData['comment'] ?? null,
                    ]);

                    $saved++;

                    if ($enableLogging) {
                        Log::info("X9 Trade saved: {$accountNumber} - Ticket: {$ticketNumber}");
                    }
                } catch (\Exception $e) {
                    $errors++;
                    if ($enableLogging) {
                        Log::error("X9 Trade save error", [
                            'account' => $accountNumber ?? 'unknown',
                            'ticket' => $ticketNumber ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
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
            'BUY' => 0,
            'SELL' => 1,
        ];

        return $typeMap[$x9Type] ?? 0;
    }
}
