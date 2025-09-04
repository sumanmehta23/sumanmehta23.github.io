<?php

namespace App\Helpers;

use DB;
use App\Models\Account;
use App\Services\UniversalMT5Service;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AccountHelper
{
    public static function updateLiveAndDemoAccounts($userId = "", $mt5Service = null)
    {
        if (!Auth::check()) {
            return;
        }

        if ($userId == "") {
            $userId = Auth::user()->id;
        }

        if ($mt5Service === null) {
            $mt5Service = app(UniversalMT5Service::class);
        }

        // Connect to MT5 server
        if (!$mt5Service->dealerConnect()) {
            Log::error("AccountHelper: Failed to connect to MT5 server");
            return;
        }

        // Update Live Accounts
        if (Auth::guard('admin')->check() && $userId != "") {
            $liveAccounts = Account::where('user_id', $userId)->where('demo', false)->get();
        } else {
            $liveAccounts = Auth::user()->liveAccounts;
        }

        if ($liveAccounts) {
            foreach ($liveAccounts as $account) {
                $accountData = $mt5Service->getAccountBalance($account->code);
                if ($accountData) {
                    $account->update([
                        'balance' => $accountData['balance'],
                        'credit' => $accountData['credit'] ?? 0,
                        'margin_free' => $accountData['margin_free'],
                        'margin_level' => $accountData['margin_level'] ?? 0,
                        'equity' => $accountData['equity'],
                    ]);
                } else {
                    Log::warning("AccountHelper: Failed to get balance for live account {$account->code}");
                }
            }
        }

        // Update Demo Accounts
        if (Auth::guard('admin')->check() && $userId != "") {
            $demoAccounts = Account::where('user_id', $userId)->where('demo', true)->get();
        } else {
            $demoAccounts = Auth::user()->demoAccounts;
        }

        foreach ($demoAccounts as $account) {
            $accountData = $mt5Service->getAccountBalance($account->code);
            if ($accountData) {
                $account->update([
                    'balance' => $accountData['balance'],
                    'credit' => $accountData['credit'] ?? 0,
                    'margin_free' => $accountData['margin_free'],
                    'margin_level' => $accountData['margin_level'] ?? 0,
                    'equity' => $accountData['equity'],
                ]);
            } else {
                Log::warning("AccountHelper: Failed to get balance for demo account {$account->code}");
            }
        }
    }

    public static function getAccount($id, $mt5Service = null)
    {
        if ($mt5Service === null) {
            $mt5Service = app(UniversalMT5Service::class);
        }

        // Connect to MT5 server
        if (!$mt5Service->dealerConnect()) {
            Log::error("AccountHelper: Failed to connect to MT5 server for account {$id}");
            return null;
        }

        $liveAccount = Account::where('code', $id)->first();
        if (!$liveAccount) {
            return null;
        }

        $accountData = $mt5Service->getAccountBalance($liveAccount->code);
        if ($accountData) {
            $liveAccount->update([
                'balance' => $accountData['balance'],
                'credit' => $accountData['credit'] ?? 0,
                'margin_free' => $accountData['margin_free'],
                'margin_level' => $accountData['margin_level'] ?? 0,
                'equity' => $accountData['equity'],
            ]);
        }

        return $accountData;
    }
}
