<?php

namespace App\Console\Commands;

use App\Models\Trade;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Services\UniversalMT5Service;
use App\Services\MailService;
use App\Helpers\AccountHelper;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAccounts extends Command
{
    protected $api;
    protected $mailService;
    protected $mt5Service;

    public function __construct(UniversalMT5Service $mt5Service, MailService $mailService)
    {
        parent::__construct();
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        // No direct connect here; use connection pool in handle()
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
        // Connect to MT5 using connection pool
        if (!$this->mt5Service->connect()) {
            $this->error('Failed to connect to MT5 via pool.');
            return 1;
        }
        $this->api = $this->mt5Service->getApi();

        // Use connection pool
        if (!$this->mt5Service->connect()) {
            $this->error('Failed to connect to MT5 via pool.');
            return 1;
        }
        $this->api = $this->mt5Service->getApi();
        Account::where('demo', 1)
            ->whereNotNull('competition_start_date')
            ->whereNotNull('competition_end_date')
            ->where('competition_status', 'active')
            ->whereDate('competition_start_date', '<=', Carbon::today())
            ->whereDate('competition_end_date', '>=', Carbon::today())
            ->whereNotNull('code')
            ->chunk(100, function ($accounts) {
                $settings = settings();
                foreach ($accounts as $account) {
                    $login = $account->code; // Assuming `login` column exists
                    AccountHelper::getAccount($login);
                }
            });
    }
}
