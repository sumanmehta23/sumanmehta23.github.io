<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\ClientGroupSyncStatus;
use App\Services\X9Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncX9GroupTrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:x9-group-trades
                            {--group= : Specific client group ID to sync}
                            {--date-from= : Start date (Y-m-d format)}
                            {--date-to= : End date (Y-m-d format)}
                            {--full-sync : Perform full sync ignoring last sync status}
                            {--limit=100 : Number of trades per API call}
                            {--dry-run : Run without saving data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync X9 closed trades by client group using V2 API endpoint';

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
        $this->info('🚀 Starting X9 Group-based Trade Sync');
        $this->info('─────────────────────────────────────');

        // Get unique client groups from X9 accounts
        $groups = $this->getClientGroups();

        if ($groups->isEmpty()) {
            $this->warn('⚠️  No X9 client groups found in accounts');
            return Command::SUCCESS;
        }

        $this->info("📊 Found {$groups->count()} unique client group(s)");
        $this->newLine();

        $totalTradesSynced = 0;
        $failedGroups = [];

        foreach ($groups as $group) {
            $clientGroupId = $group->client_group_id;
            $accountCount = $group->account_count;

            $this->info("🔄 Processing Group ID: {$clientGroupId} ({$accountCount} accounts)");

            try {
                $synced = $this->syncGroupTrades($clientGroupId);
                $totalTradesSynced += $synced;
                $this->info("   ✅ Synced {$synced} trades");
            } catch (\Exception $e) {
                $this->error("   ❌ Failed: {$e->getMessage()}");
                $failedGroups[] = $clientGroupId;
                Log::error("X9 Group Trade Sync Failed for Group {$clientGroupId}: " . $e->getMessage());
            }

            // Rate limiting: wait between groups
            if ($groups->count() > 1) {
                sleep(1);
            }

            $this->newLine();
        }

        // Summary
        $this->info('─────────────────────────────────────');
        $this->info("✨ Sync Complete!");
        $this->info("   Total Trades Synced: {$totalTradesSynced}");
        $this->info("   Groups Processed: {$groups->count()}");

        if (!empty($failedGroups)) {
            $this->warn("   Failed Groups: " . implode(', ', $failedGroups));
        }

        return Command::SUCCESS;
    }

    /**
     * Get unique client groups from X9 accounts
     */
    protected function getClientGroups()
    {
        $query = Account::where('accounts.platform', Account::PLATFORM_X9)
            ->join('account_types', 'accounts.account_type_id', '=', 'account_types.id')
            ->whereNotNull('account_types.x9_group_id')
            ->select(
                'account_types.x9_group_id as client_group_id',
                DB::raw('COUNT(accounts.id) as account_count')
            )
            ->groupBy('account_types.x9_group_id');

        // Filter by specific group if provided
        if ($groupId = $this->option('group')) {
            $query->where('account_types.x9_group_id', $groupId);
        }

        return $query->get();
    }

    /**
     * Sync trades for a specific client group
     */
    protected function syncGroupTrades($clientGroupId)
    {
        // Get or create sync status
        $syncStatus = ClientGroupSyncStatus::firstOrCreate(
            ['client_group_id' => $clientGroupId],
            [
                'last_sync_from' => null,
                'last_sync_to' => null,
                'sync_status' => 'pending',
                'total_trades_synced' => 0
            ]
        );

        // Determine date range
        [$dateFrom, $dateTo] = $this->getDateRange($syncStatus);

        $this->info("   📅 Date Range: {$dateFrom} to {$dateTo}");

        if ($this->option('dry-run')) {
            $this->warn("   🔍 DRY RUN - No data will be saved");
        }

        $totalTrades = 0;
        $offset = 0;
        $limit = (int) $this->option('limit');
        $hasMoreData = true;
        $apiTotal = null;

        // Mark sync as started
        $syncStatus->markSyncStarted($dateFrom, $dateTo);

        while ($hasMoreData) {
            try {
                $response = $this->x9Service->getClosedTradesByGroup(
                    $clientGroupId,
                    $dateFrom,
                    $dateTo,
                    $limit,
                    $offset
                );

                if (!$response['status']) {
                    throw new \Exception($response['message']);
                }

                $data = $response['data'];
                $trades = $data['trades'] ?? [];

                // Get total count from API response
                if ($apiTotal === null) {
                    $apiTotal = $data['total'] ?? 0;
                    if ($apiTotal > 0) {
                        $this->info("      📊 Total trades available: {$apiTotal}");
                    }
                }

                if (empty($trades)) {
                    $hasMoreData = false;
                    break;
                }

                // Process trades
                if (!$this->option('dry-run')) {
                    $processed = $this->processTrades($trades, $clientGroupId);
                    $totalTrades += $processed;
                } else {
                    $totalTrades += count($trades);
                }

                $this->info("      📦 Batch: {$offset}-" . ($offset + count($trades)) . " (fetched {$totalTrades}/{$apiTotal})");

                // Check if we have more data based on total count
                $offset += count($trades);
                $hasMoreData = $totalTrades < $apiTotal;

                // Rate limiting: wait between pages
                if ($hasMoreData) {
                    usleep(500000); // 0.5 seconds
                }
            } catch (\Exception $e) {
                $syncStatus->markSyncFailed($e->getMessage());
                throw $e;
            }
        }

        // Mark sync as completed
        if (!$this->option('dry-run')) {
            $syncStatus->markSyncCompleted($totalTrades);
        }

        return $totalTrades;
    }

    /**
     * Get date range for sync
     */
    protected function getDateRange($syncStatus)
    {
        // Use command options if provided
        if ($this->option('date-from') && $this->option('date-to')) {
            return [$this->option('date-from'), $this->option('date-to')];
        }

        // Use full sync option
        if ($this->option('full-sync')) {
            return [now()->subYears(2)->format('Y-m-d'), now()->format('Y-m-d')];
        }

        // Use incremental sync
        return $syncStatus->getNextSyncRange();
    }

    /**
     * Process and save trades
     */
    protected function processTrades($trades, $clientGroupId)
    {
        $processed = 0;

        foreach ($trades as $trade) {
            // Here you would implement the logic to save trades to your database
            // This is a placeholder - implement according to your Trade model structure

            // Example:
            // Trade::updateOrCreate(
            //     ['deal_id' => $trade['deal_id'], 'account_number' => $trade['account_number']],
            //     [
            //         'symbol' => $trade['symbol'],
            //         'volume' => $trade['volume'],
            //         'profit' => $trade['profit'],
            //         // ... other fields
            //     ]
            // );

            $processed++;
        }

        return $processed;
    }
}
