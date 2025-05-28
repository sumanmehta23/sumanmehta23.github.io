<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Account;
use App\Models\DailyReport;
use App\Services\MT5Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\MT5\MTEnDealAction;

class SyncHistoricalDailyReports extends Command
{
    protected $signature = 'sync:historical-daily-reports {--days=30 : Number of days to sync}';
    protected $description = 'Sync historical daily equity and balance reports from MT5';

    protected $mt5Service;

    public function __construct(MT5Service $mt5Service)
    {
        parent::__construct();
        $this->mt5Service = $mt5Service;
    }

    public function handle()
    {
        $days = $this->option('days');
        $this->info("Starting historical daily reports sync for the last {$days} days...");

        try {
            $this->mt5Service->connect();
            $api = $this->mt5Service->getApi();

            $startDate = now()->subDays($days);

            Account::whereNotNull('code')->chunk(100, function ($accounts) use ($api, $startDate) {
                foreach ($accounts as $account) {
                    try {
                        // Get historical deals for the account
                        $from = $startDate->timestamp;
                        $to = now()->timestamp;

                        $deals = $api->HistoryGet($account->code, $from, $to);

                        if ($deals === false) {
                            continue;
                        }

                        // Process deals to calculate daily balances
                        $dailyBalances = [];
                        $currentBalance = $account->balance ?? 0;
                        $currentEquity = $currentBalance;

                        // Sort deals by time
                        usort($deals, function($a, $b) {
                            return $a->Time - $b->Time;
                        });

                        foreach ($deals as $deal) {
                            $dealDate = Carbon::createFromTimestamp($deal->Time)->format('Y-m-d');

                            if (!isset($dailyBalances[$dealDate])) {
                                $dailyBalances[$dealDate] = [
                                    'balance' => $currentBalance,
                                    'equity' => $currentEquity
                                ];
                            }

                            // Update balance and equity based on deal type
                            if ($deal->Action == MTEnDealAction::DEAL_BALANCE) {
                                $currentBalance += $deal->Profit;
                                $currentEquity += $deal->Profit;
                            } else {
                                $currentEquity += $deal->Profit;
                            }

                            $dailyBalances[$dealDate] = [
                                'balance' => $currentBalance,
                                'equity' => $currentEquity
                            ];
                        }

                        // Create daily reports
                        foreach ($dailyBalances as $date => $values) {
                            DailyReport::updateOrCreate(
                                [
                                    'account_code' => $account->code,
                                    'report_date' => $date
                                ],
                                [
                                    'balance' => $values['balance'],
                                    'equity' => $values['equity']
                                ]
                            );
                        }

                        $this->info("Processed historical data for account {$account->code}");
                    } catch (\Exception $e) {
                        Log::error("Error syncing historical data for account {$account->code}: " . $e->getMessage());
                        continue;
                    }
                }
            });

            $this->info('Historical daily reports sync completed successfully.');
        } catch (\Exception $e) {
            $this->error('Error syncing historical daily reports: ' . $e->getMessage());
            Log::error('Historical daily reports sync error: ' . $e->getMessage());
        }
    }
}
