<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\DailyReport;
use App\Services\MT5Service;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\MT5\MTRetCode;

class SyncDailyReports extends Command
{
    protected $signature = 'app:sync-daily-reports';
    protected $description = 'Sync daily equity and balance reports from MT5';

    protected $mt5Service;

    public function __construct(MT5Service $mt5Service)
    {
        parent::__construct();
        $this->mt5Service = $mt5Service;
        $this->api = $this->mt5Service->getApi();
    }

    public function handle()
    {
        $this->info('Starting daily reports sync...');
        Log::info("Starting daily reports sync....");
        try {
            $this->mt5Service->connect();
            $api = $this->api;

            Account::whereNotNull('code')
                    ->whereNull('deleted_at')
                    ->whereNotNull('competition_start_date')
                    ->whereNotNull('competition_end_date')
                    // ->where('competition_status', 'active')
                    // ->whereDate('competition_start_date', '<=', Carbon::now())
                    // ->whereDate('competition_end_date', '>=', Carbon::now())
                    ->where('demo',1)
                    ->chunk(200, function ($accounts) use ($api) {
                    // dd($accounts);
                foreach ($accounts as $account) {
                     Log::info("Account {$account} Daily report sync started.");

                    try {
                        // Get account info from MT5
                        $user_info = null;
                        $error_code = $api->UserGet($account->code, $user_info);
                        if ($error_code != MTRetCode::MT_RET_OK || !$user_info) {
                            Log::error("MT5 user not found for account {$account->code}: " . MTRetCode::GetError($error_code));
                            continue;
                        }

                            Log::info("Account info2: " . print_r($user_info->Balance, true));
                            DailyReport::create([
                                'account_code' => $account->code,
                                'equity'       => $user_info->Balance + ($user_info->Profit ?? 0),
                                'balance'      => $user_info->Balance,
                                'report_date'  => now()->format('Y-m-d'),
                                'created_at'   => now(),
                                'updated_at'   => now(),
                            ]);
                    } catch (\Exception $e) {
                        Log::error("Error syncing daily report for account {$account->code}: " . $e->getMessage());
                        continue;
                    }
                }
            });

            $this->info('Daily reports sync completed successfully.');
        } catch (\Exception $e) {
            $this->error('Error syncing daily reports: ' . $e->getMessage());
            Log::error('Daily reports sync error: ' . $e->getMessage());
        }
    }
}
