<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\MT5\MTEnOrderAction;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\MT5\MTEnUsersRights;
use App\Services\MT5Service;
use App\Services\MailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BreachAccountCommand extends Command
{
    protected $mailService;
    protected $mt5Service;
    protected $api;

    protected $signature = 'app:breach-account';
    protected $description = 'Handles expired competition accounts from previous months';

    public function __construct(MT5Service $mt5Service, MailService $mailService)
    {
        parent::__construct();
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
    }

    public function handle()
    {
        $api = $this->api;
        $settings = settings();

        if (!$api->IsConnected()) {
            $error_code = $api->Connect(
                $settings['mt5_server_ip'],
                $settings['mt5_server_port'],
                300,
                $settings['mt5_server_web_login'],
                $settings['mt5_server_web_password']
            );

            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error("Failed to connect to MT5 server: " . MTRetCode::GetError($error_code));
                return;
            }
        }

        try {
            $currentDate = Carbon::now();
            $currentMonth = $currentDate->format('F');
            $currentYear = $currentDate->year;

            $expiredAccounts = Account::where('demo', true)
                ->whereNotNull('competition_month')
                ->whereNotNull('competition_year')
                ->where('code', '!=', null)
                ->where('account_request_status', 1)
                ->where(function ($query) use ($currentMonth, $currentYear) {
                    $query->where(function ($q) use ($currentMonth, $currentYear) {
                        $q->where('competition_year', $currentYear)
                          ->where('competition_month', '!=', $currentMonth);
                    })->orWhere(function ($q) use ($currentYear) {
                        $q->where('competition_year', '<', $currentYear);
                    });
                })
                ->get();

            foreach ($expiredAccounts as $account) {
                DB::beginTransaction();

                try {
                    // Get MT5 user
                    $mt5_user = null;
                    $error_code = $api->UserGet($account->code, $mt5_user);
                    if ($error_code != MTRetCode::MT_RET_OK || !$mt5_user) {
                        Log::error("MT5 user not found for account {$account->code}: " . MTRetCode::GetError($error_code));
                        continue;
                    }

                    // Disable trading rights
                    $mt5_user->Rights |= MTEnUsersRights::USER_RIGHT_TRADE_DISABLED;
                    $new_mt5_user = null;
                    $error_code = $api->UserUpdate($mt5_user, $new_mt5_user);

                    if ($error_code != MTRetCode::MT_RET_OK) {
                        Log::error("Failed to update MT5 user rights for account {$account->code}: " . MTRetCode::GetError($error_code));
                        continue;
                    }

                    // Close all open positions
                    if (($error_code = $this->api->PositionGetTotal($account->code, $total)) != MTRetCode::MT_RET_OK) {
                        session()->flash('error', 'MT5 ' . $account->code . ': ' . MTRetCode::GetError($error_code));
                    }
                    $offset = 0;
                    $positions = [];
                    if (($error_code = $this->api->PositionGetPage($account->code, $offset, $total, $positions)) != MTRetCode::MT_RET_OK) {
                        session()->flash('error', 'MT5 ' . $account->code . ': ' . MTRetCode::GetError($error_code));
                    }

                    if ($error_code == MTRetCode::MT_RET_OK && $positions) {
                        foreach ($positions as $position) {
                            // Close position by creating opposite order
                            $closeOrder = [
                                'Login'     => $account->code,
                                'Symbol'    => $position->Symbol,
                                'Action'    => MTEnOrderAction::ORDER_EXECUTE,
                                'Volume'    => $position->Volume,
                                'Type'      => $position->Type == \MTEnOrderType::ORDER_BUY ? \MTEnOrderType::ORDER_SELL : \MTEnOrderType::ORDER_BUY,
                                'Position'  => $position->Position,
                            ];

                            $result = null;
                            $error_code = $api->TradeTransaction($closeOrder, $result);

                            if ($error_code != MTRetCode::MT_RET_OK) {
                                Log::error("Failed to close position {$position->Position} for account {$account->code}: " . MTRetCode::GetError($error_code));
                            } else {
                                Log::info("Closed position {$position->Position} for account {$account->code}");
                            }
                        }
                    }

                    // Update database record
                    $account->update([
                        'breached' => true,
                        'breached_at' => now(),
                        'status' => 'breached',
                        'balance' => 0
                    ]);

                    // Send notification email
                    $this->mailService->sendBreachNotification($account->user, [
                        'account_number' => $account->code,
                        'breach_date' => now()->format('Y-m-d H:i:s'),
                        'competition_month' => $account->competition_month,
                        'competition_year' => $account->competition_year
                    ]);

                    Log::info('Competition account breached', [
                        'account_id' => $account->id,
                        'user_id' => $account->user_id,
                        'mt5_code' => $account->code,
                        'competition_month' => $account->competition_month,
                        'competition_year' => $account->competition_year
                    ]);

                    DB::commit();
                    $this->info("Successfully breached account {$account->code}");

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Failed to breach account {$account->code}: " . $e->getMessage());
                    continue;
                }
            }

            $this->info("Competition account breach process completed");

        } catch (\Exception $e) {
            Log::error('Error in breaching competition accounts: ' . $e->getMessage());
            $this->error('Failed to breach competition accounts: ' . $e->getMessage());
        }
    }
}
