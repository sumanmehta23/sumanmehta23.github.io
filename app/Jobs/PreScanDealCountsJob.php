<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\MT5RestAPIService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Lightweight job that pre-scans deal counts for accounts via MT5 getDealTotals API.
 *
 * Stores the pending deal count per account so the SyncDeals command can
 * group accounts by volume tier and dispatch appropriately-sized SyncDealsJob batches.
 */
class PreScanDealCountsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 2;
    public $backoff = [30];

    protected array $accountIds;

    public function __construct(array $accountIds)
    {
        $this->accountIds = $accountIds;
        $this->onQueue('syncaccountstrades');
    }

    public function handle(): void
    {
        try {
            $restService = app(MT5RestAPIService::class);
        } catch (Exception $e) {
            Log::warning('PreScanDealCountsJob: REST service unavailable', ['error' => $e->getMessage()]);
            return;
        }

        $accounts = Account::whereIn('id', $this->accountIds)->get();
        $scanned = 0;

        foreach ($accounts as $account) {
            try {
                $login = (int) $account->code;
                $lastSync = $account->deals_synced_to;
                $from = $lastSync
                    ? Carbon::parse($lastSync)->subHour()->timestamp
                    : Carbon::parse('2024-09-01')->timestamp;
                $to = Carbon::now()->timestamp;

                $totals = $restService->getDealTotals([$login], $from, $to);
                $count = $totals[(string) $login] ?? 0;

                Account::where('id', $account->id)->update([
                    'pending_deal_count' => $count,
                    'pending_deal_count_at' => now(),
                ]);

                $scanned++;
            } catch (Exception $e) {
                Log::warning("PreScanDealCountsJob: Failed to scan account {$account->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('PreScanDealCountsJob completed', [
            'requested' => count($this->accountIds),
            'scanned' => $scanned,
        ]);
    }
}
