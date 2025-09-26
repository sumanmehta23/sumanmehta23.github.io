<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\MT5RestAPIService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Carbon\Carbon;

/**
 * Batch Balance Sync Job
 * 
 * Processes balance synchronization for multiple accounts in batches
 * using the new MT5 REST API service with connection pooling.
 */
class BatchBalanceSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $accountIds;
    protected $forceSync;
    public $timeout = 300; // 5 minutes
    public $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(array $accountIds, bool $forceSync = false)
    {
        $this->accountIds = $accountIds;
        $this->forceSync = $forceSync;
        $this->onQueue('account-sync'); // Use dedicated account sync queue
    }

    /**
     * Execute the job.
     */
    public function handle(MT5RestAPIService $restApiService)
    {
        $startTime = microtime(true);
        $batchResults = [
            'processed' => 0,
            'updated' => 0,
            'no_change' => 0,
            'errors' => 0,
            'not_found' => 0
        ];

        Log::info('BatchBalanceSyncJob: Starting batch balance sync', [
            'account_count' => count($this->accountIds),
            'force_sync' => $this->forceSync
        ]);

        try {
            // Get accounts for this batch
            $accounts = Account::whereIn('id', $this->accountIds)
                ->whereNotNull('code')
                ->whereNotIn('sync_status', ['not_found_in_mt5'])
                ->get();

            if ($accounts->isEmpty()) {
                Log::info('BatchBalanceSyncJob: No valid accounts in batch');
                return $batchResults;
            }

            // Extract login codes for batch API call
            $logins = $accounts->pluck('code')->map(fn($code) => (int)$code)->toArray();

            // Log::debug('BatchBalanceSyncJob: Fetching balances for logins', ['logins' => $logins]);

            // Use REST API to get all balances in one call
            $balanceData = $restApiService->getBatchBalances($logins);

            if (empty($balanceData)) {
                Log::warning('BatchBalanceSyncJob: No balance data received from REST API');
                $batchResults['errors'] = count($accounts);
                $batchResults['processed'] = count($accounts);
                return $batchResults;
            }

            // Process each account with the received balance data
            foreach ($accounts as $account) {
                $result = $this->processSingleAccount($account, $balanceData);
                $batchResults[$result]++;
                $batchResults['processed']++;
            }
        } catch (\Exception $e) {
            Log::error('BatchBalanceSyncJob: Failed to process batch', [
                'error' => $e->getMessage(),
                'account_ids' => $this->accountIds
            ]);

            // Mark all accounts as errored
            $batchResults['errors'] = count($this->accountIds);
            $batchResults['processed'] = count($this->accountIds);
        }

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        Log::info('BatchBalanceSyncJob: Batch completed', array_merge($batchResults, [
            'duration_ms' => $duration,
            'avg_per_account_ms' => $batchResults['processed'] > 0 ? round($duration / $batchResults['processed'], 2) : 0
        ]));

        return $batchResults;
    }

    /**
     * Process a single account with batch balance data
     */
    private function processSingleAccount(Account $account, array $balanceData): string
    {
        $accountCode = $account->code;

        // Check if we have balance data for this account
        if (!isset($balanceData[$accountCode])) {
            Log::warning("BatchBalanceSyncJob: No balance data for account {$accountCode} - marking as not_found_in_mt5");

            $account->update([
                'sync_status' => 'not_found_in_mt5',
                'sync_error' => 'Account not found in MT5 server during batch sync',
                'sync_flagged_at' => now(),
                'sync_flag_reason' => 'Account not found during batch balance sync'
            ]);

            return 'not_found';
        }

        try {
            $currentBalance = (float) $balanceData[$accountCode]['balance'];
            $currentEquity = (float) $balanceData[$accountCode]['equity'];

            $previousBalance = $account->last_known_balance;
            $previousEquity = $account->last_known_equity;

            // Check if balance or equity changed
            $balanceChanged = abs($currentBalance - ($previousBalance ?? 0)) > 0.01;
            $equityChanged = abs($currentEquity - ($previousEquity ?? 0)) > 0.01;

            if ($balanceChanged || $equityChanged || $this->forceSync) {
                // Update account with new balance data
                $updateData = [
                    'balance' => $currentBalance,           // Update main balance field
                    'equity' => $currentEquity,             // Update main equity field
                    'last_known_balance' => $currentBalance, // Update tracking field
                    'last_known_equity' => $currentEquity,   // Update tracking field
                    'last_balance_sync_at' => now(),
                    'sync_status' => 'completed'
                ];

                // Mark balance activity if there was a change
                if ($balanceChanged || $equityChanged) {
                    $updateData['has_balance_activity'] = true;
                    $updateData['last_balance_changed_at'] = now();
                }

                $account->update($updateData);

                // Log::debug("BatchBalanceSyncJob: Updated account {$accountCode}", [
                //     'balance' => $currentBalance,
                //     'equity' => $currentEquity,
                //     'balance_changed' => $balanceChanged,
                //     'equity_changed' => $equityChanged
                // ]);

                return 'updated';
            } else {
                // No change, just update sync timestamp
                $account->update([
                    'last_balance_sync_at' => now(),
                    'sync_status' => 'completed'
                ]);

                return 'no_change';
            }
        } catch (\Exception $e) {
            Log::error("BatchBalanceSyncJob: Error processing account {$accountCode}", [
                'error' => $e->getMessage()
            ]);

            $account->update([
                'sync_status' => 'failed',
                'sync_error' => 'Error during batch balance sync: ' . $e->getMessage(),
                'last_sync_attempt_at' => now()
            ]);

            return 'errors';
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error('BatchBalanceSyncJob: Job failed', [
            'account_ids' => $this->accountIds,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Mark all accounts as failed
        Account::whereIn('id', $this->accountIds)->update([
            'sync_status' => 'failed',
            'sync_error' => 'Batch balance sync job failed: ' . $exception->getMessage(),
            'last_sync_attempt_at' => now()
        ]);
    }
}
