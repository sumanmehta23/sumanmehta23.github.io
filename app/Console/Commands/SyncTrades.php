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
    protected $description = 'Sync MT5 trade history for demo competition accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Starting sync trades process');
        $totalAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->count();
        Log::info("Found {$totalAccounts} accounts to process");

        Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->chunk(1000, function ($accounts) {
                Log::info("Processing chunk of " . count($accounts) . " accounts");
                foreach ($accounts as $account) {
                    Log::info("Dispatching sync job for account: {$account->code}");
                    SyncTradesJob::dispatch($account)->onQueue('sync-trades');
                }
        });

        Log::info('Completed dispatching sync trade jobs');
    }
}
