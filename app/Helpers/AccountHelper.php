<?php

namespace App\Helpers;

use DB;
use App\Models\Account;
use App\MT5\MTRetCode;
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

        // Create a unified interface regardless of input type
        $mt5Interface = self::createMT5Interface($mt5Service);
        if (!$mt5Interface) {
            Log::error("AccountHelper: Could not create MT5 interface");
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
                $accountData = $mt5Interface->getAccountBalance((int)$account->code);
                if ($account->code == 443128) {
                    Log::info("AccountHelper: Debug - Fetched data for account 443128: " . json_encode($accountData));
                }
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
            $accountData = $mt5Interface->getAccountBalance((int)$account->code);
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

    /**
     * Create a unified MT5 interface that handles both raw API and service instances
     */
    private static function createMT5Interface($mt5Service)
    {
        if ($mt5Service === null) {
            // Default case - create new service and connect
            $service = app(UniversalMT5Service::class);
            $connectResult = $service->dealerConnect();
            if ($connectResult !== \App\MT5\MTRetCode::MT_RET_OK) {
                Log::error("AccountHelper: Failed to connect new service to MT5 server");
                return null;
            }
            return $service;
        } elseif ($mt5Service instanceof UniversalMT5Service) {
            // Preferred case - service instance, just ensure it's connected
            $connectResult = $mt5Service->dealerConnect();
            if ($connectResult !== \App\MT5\MTRetCode::MT_RET_OK) {
                Log::error("AccountHelper: Failed to connect provided service to MT5 server");
                return null;
            }
            return $mt5Service;
        } else {
            Log::error("AccountHelper: Invalid mt5Service parameter type: " . get_class($mt5Service));
            return null;
        }
    }

    public static function getAccount($id, $mt5Service = null)
    {
        // Create a unified interface regardless of input type
        $mt5Interface = self::createMT5Interface($mt5Service);
        if (!$mt5Interface) {
            Log::error("AccountHelper: Could not create MT5 interface for account {$id}");
            return null;
        }

        $liveAccount = Account::where('code', $id)->first();
        if (!$liveAccount) {
            return null;
        }

        // Fetch total trades from MT5 API using HistoryGetTotal
        $login = $liveAccount->code;
        $from = 'September 01,2024';
        $to = 'March 31,2080';
        $total = 0;

        $error_code = $mt5Interface->executeOperation(function ($api) use ($login, $from, $to, &$total) {
            return $api->HistoryGetTotal($login, $from, $to, $total);
        });

        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::warning("AccountHelper: Failed to get total trades for account {$login}");
            $totalTrades = 0;
        } else {
            $totalTrades = $total;
        }

        $accountData = $mt5Interface->getAccountBalance((int)$liveAccount->code);
        $accountData['total_trades'] = $totalTrades;

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
