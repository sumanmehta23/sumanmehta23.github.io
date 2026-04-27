<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\MT5RestAPIService;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Pre-fetches deal counts for accounts without actually syncing deals.
 * This populates the `pending_deal_count` column so SyncDeals command can make
 * intelligent batching decisions (group similar-volume accounts together).
 *
 * Runs daily or on-demand. Much faster than SyncDealsJob since it only fetches
 * counts, not the actual deal data.
 */
class StoreDealCountsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $timeout = 300; // 5 minutes
    public $tries = 2;
    public $backoff = [30, 120];

    protected $accountIds;

    public function __construct(array $accountIds = [])
    {
        $this->accountIds = $accountIds;
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $jobStart = microtime(true);
        $restService = null;

        try {
            $restService = app(MT5RestAPIService::class);
        } catch (Exception $e) {
            Log::warning('StoreDealCountsJob: REST service unavailable', [
                'error' => $e->getMessage(),
            ]);
            return;
        }

        // Load accounts to get pending deal counts for
        if (empty($this->accountIds)) {
            // If no IDs provided, refresh counts for accounts that haven't been updated recently
            $accounts = Account::where('type', 'live')
                ->whereNotNull('code')
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    // Accounts with no count yet, or count is stale (>24 hours)
                    $q->whereNull('pending_deal_count_at')
                        ->orWhere('pending_deal_count_at', '<', now()->subDay());
                })
                ->limit(50) // Update max 50 per job
                ->get();
        } else {
            $accounts = Account::whereIn('id', $this->accountIds)->get();
        }

        if ($accounts->isEmpty()) {
            Log::info('StoreDealCountsJob: No accounts to update');
            return;
        }

        Log::info('StoreDealCountsJob: Fetching deal counts', [
            'account_count' => $accounts->count(),
        ]);

        $updated = 0;
        $failed = 0;

        foreach ($accounts as $account) {
            try {
                $login = (int) $account->code;
                $from = $account->deals_synced_to
                    ? Carbon::parse($account->deals_synced_to)->subHour()->timestamp
                    : Carbon::parse('2024-09-01')->timestamp;
                $to = Carbon::now()->addYear()->timestamp;

                // Lightweight API call to just get deal count
                $totals = $restService->getDealTotals([$login], $from, $to);
                $dealCount = $totals[(string) $login] ?? 0;

                $account->update([
                    'pending_deal_count' => $dealCount,
                    'pending_deal_count_at' => now(),
                ]);

                $updated++;
                Log::debug("StoreDealCountsJob: Account {$account->code} has {$dealCount} pending deals");
            } catch (Exception $e) {
                $failed++;
                Log::warning("StoreDealCountsJob: Failed to fetch count for {$account->code}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $duration = round(microtime(true) - $jobStart, 2);
        Log::info('StoreDealCountsJob completed', [
            'updated' => $updated,
            'failed' => $failed,
            'duration_seconds' => $duration,
        ]);
    }
}
