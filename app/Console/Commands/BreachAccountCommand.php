<?php

namespace App\Console\Commands;

use Carbon\Carbon;
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
    protected $dealerapi;

    protected $signature = 'app:breach-account';
    protected $description = 'Handles expired competition accounts from previous months';

    public function __construct(MT5Service $mt5Service, MailService $mailService)
    {
        parent::__construct();
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
        $this->mt5Service->dealerConnect();
        $this->dealerapi = $this->mt5Service->dealerConnect();
    }

    public function handle()
    {
        $api = $this->api;
        $settings = settings();

        Log::info('Starting breach account command');

        if (!$api->IsConnected()) {
            Log::info('MT5 API not connected. Attempting to connect...');
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

            Log::info('Connected to MT5 API successfully.');
        }

        try {

            $currentDate = Carbon::today();

            $expiredAccounts = Account::where('demo', true)
                ->whereNotNull('competition_start_date')
                ->whereNotNull('competition_end_date')
                ->where('competition_status', 'active')
                // ->whereDate('competition_start_date', '<=', $currentDate)
                ->whereDate('competition_end_date', '<=', $currentDate)
                ->whereNotNull('code')
                // ->where('code', 322152)
                ->where('account_request_status', 1)
                ->get();

            Log::info("Found " . $expiredAccounts->count() . " expired competition accounts to breach.");

            foreach ($expiredAccounts as $account) {
                DB::beginTransaction();

                try {
                    Log::info("Processing account {$account->code}");

                    // Get MT5 user
                    $mt5_user = null;
                    $error_code = $api->UserGet($account->code, $mt5_user);
                    if ($error_code != MTRetCode::MT_RET_OK || !$mt5_user) {
                        Log::error("MT5 user not found for account {$account->code}: " . MTRetCode::GetError($error_code));
                        DB::rollBack();
                        continue;
                    }

                    Log::info("MT5 user {$account->code} retrieved successfully.");

                    // Get and close open positions
                    if (($error_code = $this->api->PositionGetTotal($account->code, $total)) != MTRetCode::MT_RET_OK) {
                        Log::error("Failed to get total positions for account {$account->code}: " . MTRetCode::GetError($error_code));
                        DB::rollBack();
                        continue;
                    }

                    $offset = 0;
                    $positions = [];
                    if (($error_code = $this->api->PositionGetPage($account->code, $offset, $total, $positions)) != MTRetCode::MT_RET_OK) {
                        Log::error("Failed to fetch positions for account {$account->code}: " . MTRetCode::GetError($error_code));
                        DB::rollBack();
                        continue;
                    }

                    Log::info("Found {$total} open positions for account {$account->code}");

                    if($positions){
                        foreach ($positions as $position) {
                            // dd($positions);
                            // Determine opposite order type for closing:
                            // BUY positions need SELL to close and vice versa
                            $oppositeType = $position->Action === 0 ? 1 : 0; // 0=BUY, 1=SELL (Confirm with your MT5 API)


                            $trade_result = [];
                            $error_code = $this->api->TradeCloseRequest($position, $trade_result, $this->dealerapi);

                            if ($error_code != MTRetCode::MT_RET_OK) {
                                Log::error("TradeRequest API error for account {$account->code}, position {$position->Position}: " . MTRetCode::GetError($error_code));
                                continue;
                            }

                            if ($trade_result['retcode'] != MTRetCode::MT_RET_OK) {
                                Log::error("Trade execution failed for account {$account->code}, position {$position->Position}: " . MTRetCode::GetError($trade_result['retcode']));
                                continue;
                            }

                            Log::info("Position {$position->Position} closed successfully for account {$account->code}");
                        }
                    }

                    // Disable trading rights
                    $mt5_user->Rights |= MTEnUsersRights::USER_RIGHT_TRADE_DISABLED;
                    $new_mt5_user = null;
                    $error_code = $api->UserUpdate($mt5_user, $new_mt5_user);

                    if ($error_code != MTRetCode::MT_RET_OK) {
                        Log::error("Failed to update MT5 user rights for account {$account->code}: " . MTRetCode::GetError($error_code));
                        DB::rollBack();
                        continue;
                    }

                    Log::info("Trading disabled for MT5 account {$account->code}");

                    // Update database record
                    $account->update([
                        'competition_status' => 'inactive',
                    ]);

                    Log::info("Account {$account->code} updated in database as breached.");

                    // Send notification email


                    Log::info("Breach notification email sent to user {$account->user_id} for account {$account->code}");

                    DB::commit();
                    Log::info("Successfully breached account {$account->code}");
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Exception while breaching account {$account->code}: {$e->getMessage()}");
                    continue;
                }
            }

            Log::info("Competition account breach process completed.");
        } catch (\Exception $e) {
            //log full error stack trace
            Log::error('Fatal error in breaching competition accounts: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            $this->error('Failed to breach competition accounts: ' . $e->getMessage());
        }
    }
}
