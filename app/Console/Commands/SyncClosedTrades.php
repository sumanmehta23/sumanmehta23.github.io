<?php

namespace App\Console\Commands;

use Throwable;
use App\Models\User;
use App\Models\Trade;
use App\MT5\MTRetCode;
use App\Models\Account;
use Illuminate\Bus\Batch;
use App\Services\MT5Service;
use App\Services\MailService;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use App\Jobs\SyncTrades as SyncTradesJob;

class SyncClosedTrades extends Command
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
    protected $signature = 'app:sync-closed-trades';

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
        $batchSize = 1; // Process accounts per batch

        Account::with('accountType')->whereNotNull('code')
            ->whereNotNull('competition_start_date')
            ->whereNotNull('competition_end_date')
            ->where('competition_status', 'inactive')
            ->whereNull('deleted_at')
            ->whereHas('accountType', function ($query) {
                $query->where('competition_start_date', '<=', Carbon::now('UTC'));
                // $query->where('competition_end_date', '>=', Carbon::now('UTC'));
            })
            ->chunk(500, function ($accounts) use ($batchSize) {
                // Log::info("closed competition accounts ".json_encode($accounts));
                $jobs = [];
                foreach ($accounts as $account) {
                    // dd($accounts);
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
                                Log::error("Batch {$batch->id} failed: " . $e->getMessage());
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
