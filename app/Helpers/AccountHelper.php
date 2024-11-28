<?php

namespace App\Helpers;

use DB;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\MT5\MTEnDealAction;

class AccountHelper
{
    public static function updateLiveAndDemoAccounts($userId = "", $api = new MTWebAPI())
    {
        if(!auth()->check()) {
            return;
        }
        if ($userId == "") {
            $userId = auth()->user()->id;
        }

        $settings = settings();

        $api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );
        $liveAccounts = auth()->user()->liveAccounts;

        foreach ($liveAccounts as $account) {
            $apiResponse = $api->UserAccountGet($account->code, $accountData);
            if ($apiResponse === MTRetCode::MT_RET_OK) {
                $account->update([
                        'balance' => $accountData->Balance,
                        'credit' => $accountData->Credit,
                        'margin_free' => $accountData->MarginFree,
                        'margin_level' => $accountData->MarginLevel,
                        'equity' => $accountData->Equity,
                    ]);
            } else {
                // Logger::error
            }
        }

        // Update Demo Accounts
        $demoAccounts = auth()->user()->demoAccounts;
        foreach ($demoAccounts as $account) {
            $apiResponse = $api->UserAccountGet($account->code, $accountData);

            if ($apiResponse === MTRetCode::MT_RET_OK) {
                $account->update([
                        'balance' => $accountData->Balance,
                        'credit' => $accountData->Credit,
                        'margin_free' => $accountData->MarginFree,
                        'margin_level' => $accountData->MarginLevel,
                        'equity' => $accountData->Equity,
                    ]);
            } else {
                // Handle API error
                // You can log the error here if needed
            }
        }
    }

    public static function getAccount($id, $api = new MTWebAPI())
    {
        $settings = settings();

        $api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );
        $liveAccount = Account::where('code',$id)->first();

        // dd($liveAccount,$id);
        $accountData = NULL;

        $apiResponse = $api->UserAccountGet($liveAccount->code, $accountData);

        if ($apiResponse === MTRetCode::MT_RET_OK) {
            $liveAccount->update([
                    'balance' => $accountData->Balance,
                    'credit' => $accountData->Credit,
                    'margin_free' => $accountData->MarginFree,
                    'margin_level' => $accountData->MarginLevel,
                    'equity' => $accountData->Equity,
                ]);
        }
        return $accountData;
    }
}
