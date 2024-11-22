<?php

namespace App\Helpers;

use DB;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\MT5\MTEnDealAction;

class AccountHelper
{
    public static function updateLiveAndDemoAccounts($email = "", $api = new MTWebAPI())
    {
        if ($email == "") {
            $email = session("clogin");
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
        $liveAccounts = DB::table('liveaccount')
            ->where('email', $email)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($liveAccounts as $account) {
            $apiResponse = $api->UserAccountGet($account->trade_id, $accountData);
            if ($apiResponse === MTRetCode::MT_RET_OK) {
                DB::table('liveaccount')
                    ->where('trade_id', $account->trade_id)
                    ->update([
                        'Balance' => $accountData->Balance,
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
        $demoAccounts = DB::table('demoaccount')
            ->where('email', $email)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($demoAccounts as $account) {
            $apiResponse = $api->UserAccountGet($account->trade_id, $accountData);

            if ($apiResponse === MTRetCode::MT_RET_OK) {
                DB::table('demoaccount')
                    ->where('trade_id', $account->trade_id)
                    ->update([
                        'Balance' => $accountData->Balance,
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
        $liveAccount = DB::table('liveaccount')
            ->where(DB::raw('(trade_id)'), $id)
            ->first();

        // dd($liveAccount,$id);
        $accountData = NULL;

        $apiResponse = $api->UserAccountGet($liveAccount->trade_id, $accountData);

        if ($apiResponse === MTRetCode::MT_RET_OK) {
            DB::table('liveaccount')
                ->where('trade_id', $liveAccount->trade_id)
                ->update([
                    'Balance' => $accountData->Balance,
                    'credit' => $accountData->Credit,
                    'margin_free' => $accountData->MarginFree,
                    'margin_level' => $accountData->MarginLevel,
                    'equity' => $accountData->Equity,
                ]);
        }
        return $accountData;
    }
}
