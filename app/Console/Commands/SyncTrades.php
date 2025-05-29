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
         Account::whereNotNull('code')
            // ->whereNotNull('competition_month')
            // ->whereNotNull('competition_year')
            ->whereNull('deleted_at')
            ->chunk(1000, function ($accounts) {
                foreach ($accounts as $account) {
                    SyncTradesJob::dispatch($account)->onQueue('sync-trades');
                }
        });
    }
}
