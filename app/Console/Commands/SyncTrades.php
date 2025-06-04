<?php

namespace App\Console\Commands;

use App\Models\Trade;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Jobs\SyncTrades as SyncTradesJob;
use App\Services\MT5Service;
use App\Services\MailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Throwable;
use App\Models\User;

class SyncTrades extends Command
{
    protected $api;
    protected $mailService;
    protected $mt5Service;

    public function __construct(MT5Service $mt5Service, MailService $mailService)
    {
        parent::__construct();
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-trades';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync MT5 trade history for accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchSize = 500; // Process accounts per batch

        Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->chunk(100, function ($accounts) use ($batchSize) {
                $jobs = [];
                foreach ($accounts as $account) {
                    // Check if user exists for this account
                    if ($account->user_id && User::where('id', $account->user_id)->exists()) {
                        $jobs[] = new SyncTradesJob($account);
                    }
                }

                // Only proceed if there are valid jobs
                if (!empty($jobs)) {
                    // Create batches of jobs each
                    $jobBatches = array_chunk($jobs, $batchSize);

                    foreach ($jobBatches as $batch) {
                        Bus::batch($batch)
                            ->allowFailures()
                            ->onConnection('redis')
                            ->onQueue('sync-trades')
                            ->then(function (Batch $batch) {
                                // Log::info("Batch {$batch->id} completed successfully");
                            })
                            ->catch(function (Batch $batch, Throwable $e) {
                                // Log::error("Batch {$batch->id} failed: " . $e->getMessage());
                            })
                            ->finally(function (Batch $batch) {
                                // Log::info("Batch {$batch->id} finished processing");
                            })
                            ->dispatch();
                    }
                }
            });
    }
}
