<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\DailyReport;
use App\Services\MT5Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncDailyReports extends Command
{
    protected $signature = 'sync:daily-reports';
    protected $description = 'Sync daily equity and balance reports from MT5';

    protected $mt5Service;

    public function __construct(MT5Service $mt5Service)
    {
        parent::__construct();
        $this->mt5Service = $mt5Service;
    }

    public function handle()
    {
        $this->info('Starting daily reports sync...');

        try {
            $this->mt5Service->connect();
            $api = $this->mt5Service->getApi();

            Account::whereNotNull('code')->whereNull('deleted_at')->where('demo',1)->whereNotNull('competition_month')->chunk(200, function ($accounts) use ($api) {

                foreach ($accounts as $account) {
                    try {
                        // Get account info from MT5
                        $user_info = null;
                        $api->UserGet($account->code, $user_info);

                        if ($user_info) {
                            DailyReport::create([
                                'account_code' => $account->code,
                                'equity' => $user_info->Balance + ($user_info->Profit ?? 0),
                                'balance' => $user_info->Balance,
                                'report_date' => now()->format('Y-m-d')
                            ]);
                        }
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
