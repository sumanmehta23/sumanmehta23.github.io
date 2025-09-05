<?php

namespace App\Console\Commands;

use Throwable;
use App\Models\User;
use App\Models\Trade;
use App\MT5\MTRetCode;
use App\Models\Account;
use Illuminate\Bus\Batch;
use App\Services\UniversalMT5Service;
use App\Services\MailService;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use App\Jobs\SyncTrades as SyncTradesJob;

class SyncTrades extends Command
{
    protected $api;
    protected $mailService;
    protected $mt5Service;

    public function __construct(UniversalMT5Service $mt5Service, MailService $mailService)
    {
        parent::__construct();
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        // Defer connection until handle() method
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
        // Connect to MT5 using connection pool
        if (!$this->mt5Service->connect()) {
            $this->error('Failed to connect to MT5 via pool.');
            return 1;
        }
        $this->api = $this->mt5Service->getApi();

        $batchSize = 10; // Increased from 1 to reduce single-job batches

        Account::with('accountType')->whereNotNull('code')
            ->whereNotNull('competition_start_date')
            ->whereNotNull('competition_end_date')
            ->where('competition_status', 'active')
            ->whereNull('deleted_at')
            ->whereHas('accountType', function ($query) {
                $query->where('competition_start_date', '<=', Carbon::now('UTC'));
                $query->where('competition_end_date', '>=', Carbon::now('UTC'));
            })
            ->chunk(500, function ($accounts) use ($batchSize) {
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
                    $this->info("Processing " . count($jobs) . " sync jobs");

                    // For efficiency: dispatch directly if few jobs, use batches for many
                    if (count($jobs) <= 3) {
                        // Direct dispatch for small numbers - no batch overhead
                        foreach ($jobs as $job) {
                            $job->onQueue('sync-trades')->dispatch();
                        }
                        $this->info("Dispatched " . count($jobs) . " jobs directly (no batch overhead)");
                    } else {
                        // Use batches only when beneficial (multiple jobs)
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
                        $this->info("Created " . count($jobBatches) . " batches for processing");
                    }
                }
            });
    }
}
