<?php

namespace App\Console\Commands;

use App\Models\Trade;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Services\MT5Service;
use App\Services\MailService;
use App\Helpers\AccountHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAccounts extends Command
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
    protected $signature = 'app:sync-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync MT5 accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Account::where('demo', 1)
            ->whereNotNull('competition_month')
            ->whereNotNull('competition_year')
            ->whereNotNull('code')
            ->chunk(100, function ($accounts) {
                $settings = settings();
                foreach ($accounts as $account) {
                    $login = $account->code; // Assuming `login` column exists
                    // Connect to MT5 server
                    AccountHelper::getAccount($login);
                }
            });
    }
}
