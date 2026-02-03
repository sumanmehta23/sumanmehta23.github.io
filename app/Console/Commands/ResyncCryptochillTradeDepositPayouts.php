<?php

namespace App\Console\Commands;

use App\Models\TradeWithdrawals;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ResyncCryptochillTradeDepositPayouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cryptochill:resync-trade-deposits {--limit=100} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resync cryptochill trade deposit payouts where payout_resp has status = new or draft, get new response from API and update payout_resp column';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        $this->info('Starting Cryptochill Trade Deposit Payout Resync...');
        $this->line("Limit: {$limit} | Dry Run: " . ($dryRun ? 'Yes' : 'No'));

        // Find trade withdrawals where payout_res has "status": "new" or "status": "draft" string
        $withdrawals = TradeWithdrawals::where('payout_callback_status', 'complete')
                        ->where('payout_res', 'like', '%complete%')
                        ->where('status', 1)
                        // ->where('id','352172133081247744')
                        ->limit($limit)
                        ->get();

        if ($withdrawals->isEmpty()) {
            $this->info('No pending payouts found.');
            return Command::SUCCESS;
        }
        $this->line("\nFound {$withdrawals->count()} pending payouts to resync.");
        $progressBar = $this->output->createProgressBar($withdrawals->count());
        $progressBar->start();

        $successCount = 0;
        $failureCount = 0;
        $errors = [];

        foreach ($withdrawals as $withdrawal) {
            try {
                $resp = json_decode($withdrawal->payout_res);
                $payoutId = $resp->result->id;
                $transactionpayload = [
                    'profile_id' => config('services.cryptochill.profileid'),
                    'request' => '/v1/transactions/',
                    'nonce' => time() * 1000,
                ];
                $response = Http::withHeaders([
                    'X-CC-KEY' => config('services.cryptochill.key'),
                    'X-CC-PAYLOAD' => base64_encode(json_encode($transactionpayload)),
                    'X-CC-SIGNATURE' => hash_hmac('sha256', base64_encode(json_encode($transactionpayload)), config('services.cryptochill.secret')),
                ])->get('https://api.cryptochill.com/v1/payouts/' . $payoutId . "/", $transactionpayload);
                if (!$response->successful()) {
                    throw new \Exception("API returned status {$response->status()}: {$response->body()}");
                }
                $responseData = $response->json();
                if (!$dryRun) {
                    DB::transaction(function () use ($withdrawal, $response, $responseData) {
                        // Update the payout_res with the new response

                        $withdrawal->update([
                            'payout_res' => $response->body(),
                            'admin_remark' => $responseData['result']['status'] ?? 'unknown',
                            'payout_callback_status' => $responseData['result']['status'] ?? null,
                            'transaction_id' => $responseData['result']['txid'] ?? null,
                        ]);
                    });
                }

                $successCount++;
            } catch (\Exception $e) {
                $failureCount++;
                $errorMsg = "Withdrawal ID: {$withdrawal->id} (Payout ID: {$withdrawal->transaction_id}) - {$e->getMessage()}";
                $errors[] = $errorMsg;
                Log::error("Cryptochill Resync Error: {$errorMsg}");
            } finally {
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine();

        // Display summary
        $this->info("\n=== Resync Summary ===");
        $this->line("Total Processed: {$withdrawals->count()}");
        $this->info("Successful: {$successCount}");
        $this->error("Failed: {$failureCount}");

        if (!empty($errors)) {
            $this->error("\n=== Errors ===");
            foreach ($errors as $error) {
                $this->line($error);
            }
        }

        if ($dryRun) {
            $this->warn("\n*** DRY RUN MODE - No changes were made ***");
        } else {
            Log::channel('payouts')->info("Cryptochill Trade Deposit Resync Complete | Success: {$successCount} | Failed: {$failureCount}");
        }

        return $failureCount === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
