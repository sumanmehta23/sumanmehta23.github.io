<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Jobs\BatchSyncTradesJob;
use Illuminate\Console\Command;

class DispatchSyncJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dispatch-sync-job {--account=* : Account ID(s) to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch BatchSyncTradesJob for specific account(s)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountIds = $this->option('account');

        if (empty($accountIds)) {
            $this->error('Please provide at least one account ID using --account=ID');
            $this->info('Examples:');
            $this->info('  php artisan dispatch-sync-job --account=142152');
            $this->info('  php artisan dispatch-sync-job --account=142152 --account=142153');
            return 1;
        }

        // Fetch accounts
        $accounts = Account::whereIn('id', $accountIds)->get();

        if ($accounts->isEmpty()) {
            $this->error('No accounts found with IDs: ' . implode(', ', $accountIds));
            return 1;
        }

        if ($accounts->count() !== count(array_unique($accountIds))) {
            $found = $accounts->pluck('id')->toArray();
            $notFound = array_diff($accountIds, $found);
            $this->warn('Warning: Account IDs not found: ' . implode(', ', $notFound));
        }

        // Dispatch the batch job
        $accountCodes = $accounts->pluck('code')->join(', ');
        $this->info("Dispatching BatchSyncTradesJob for {$accounts->count()} account(s): {$accountCodes}");

        $job = new BatchSyncTradesJob($accounts->toArray(), []);
        dispatch($job);

        $this->info('✅ Job dispatched successfully!');
        $this->info('Monitor with: tail -f storage/logs/laravel-*.log | grep "SYNC_STRATEGY"');

        return 0;
    }
}
