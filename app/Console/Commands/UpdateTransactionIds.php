<?php

namespace App\Console\Commands;

use App\Models\TradeDeposit;
use App\Models\TradeWithdrawals;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateTransactionIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:transaction-ids {--batch-size=100 : Number of records to process in each batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update NULL transaction_id values with unique UUIDs for TradeDeposit and TradeWithdrawals models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchSize = (int) $this->option('batch-size');

        $this->info('Starting transaction ID update process...');

        // Update TradeDeposit records
        $this->updateTradeDeposits($batchSize);

        // Update TradeWithdrawals records  
        $this->updateTradeWithdrawals($batchSize);

        $this->info('Transaction ID update process completed successfully!');

        return Command::SUCCESS;
    }

    private function updateTradeDeposits($batchSize)
    {
        $this->info('Updating TradeDeposit transaction_ids...');

        $totalRecords = DB::table('trade_deposits')->whereNull('transaction_id')->count();
        $this->info("Found {$totalRecords} TradeDeposit records with NULL transaction_id");

        if ($totalRecords === 0) {
            $this->info('No TradeDeposit records to update.');
            return;
        }

        $progressBar = $this->output->createProgressBar($totalRecords);
        $progressBar->start();

        $processed = 0;

        DB::table('trade_deposits')
            ->whereNull('transaction_id')
            ->chunkById($batchSize, function ($deposits) use (&$processed, $progressBar) {
                $updates = [];

                foreach ($deposits as $deposit) {
                    $updates[] = [
                        'id' => $deposit->id,
                        'transaction_id' => (string) Str::uuid(),
                        'updated_at' => now()
                    ];
                }

                // Batch update using raw SQL for better performance
                foreach ($updates as $update) {
                    DB::table('trade_deposits')
                        ->where('id', $update['id'])
                        ->update([
                            'transaction_id' => $update['transaction_id'],
                            'updated_at' => $update['updated_at']
                        ]);

                    $processed++;
                    $progressBar->advance();
                }
            });

        $progressBar->finish();
        $this->newLine();
        $this->info("Updated {$processed} TradeDeposit records");
    }

    private function updateTradeWithdrawals($batchSize)
    {
        $this->info('Updating TradeWithdrawals transaction_ids...');

        $totalRecords = DB::table('trade_withdrawal')->whereNull('transaction_id')->count();
        $this->info("Found {$totalRecords} TradeWithdrawals records with NULL transaction_id");

        if ($totalRecords === 0) {
            $this->info('No TradeWithdrawals records to update.');
            return;
        }

        $progressBar = $this->output->createProgressBar($totalRecords);
        $progressBar->start();

        $processed = 0;

        DB::table('trade_withdrawal')
            ->whereNull('transaction_id')
            ->chunkById($batchSize, function ($withdrawals) use (&$processed, $progressBar) {
                $updates = [];

                foreach ($withdrawals as $withdrawal) {
                    $updates[] = [
                        'id' => $withdrawal->id,
                        'transaction_id' => (string) Str::uuid(),
                        'updated_at' => now()
                    ];
                }

                // Batch update using raw SQL for better performance
                foreach ($updates as $update) {
                    DB::table('trade_withdrawal')
                        ->where('id', $update['id'])
                        ->update([
                            'transaction_id' => $update['transaction_id'],
                            'updated_at' => $update['updated_at']
                        ]);

                    $processed++;
                    $progressBar->advance();
                }
            });

        $progressBar->finish();
        $this->newLine();
        $this->info("Updated {$processed} TradeWithdrawals records");
    }
}
