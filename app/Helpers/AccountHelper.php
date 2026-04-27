<?php

namespace App\Helpers;

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

        // Fetch total trades using MT5 API - using deals for both open and closed trades
        $login = $liveAccount->code;
        $from = 'September 01,2024';
        $to = 'March 31,2080';
        $totalOpen = 0;
        $totalDeals = 0;
        $totalClose = 0;

        // Get open positions/trades count from MT5 API
        $error_code = $mt5Interface->executeOperation(function ($api) use ($login, &$totalOpen) {
            return $api->PositionGetTotal($login, $totalOpen);
        });
        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::warning("AccountHelper: Failed to get open positions for account {$login}");
            $totalOpen = 0;
        }
        // Get closed trades/deals from MT5 API with commission
        $deals = [];
        $totalCommission = 0;

        $error_code = $mt5Interface->executeOperation(function ($api) use ($login, $from, $to, &$totalDeals, &$deals) {
            // First get total count of deals
            $error_code = $api->DealGetTotal($login, $from, $to, $totalDeals);
            if ($error_code != MTRetCode::MT_RET_OK) {
                return $error_code;
            }
            // Then get the actual deals with their details
            return $api->DealGetPage($login, $from, $to, 0, $totalDeals, $deals);
        });

        // Ensure $deals is an array before using array_filter
        if (!is_array($deals)) {
            $deals = [];
        }
        $filteredDeals = array_filter($deals, fn($deal) => $deal->Symbol !== "");

        // Ensure filteredDeals is an array before using array_filter
        $filteredDeals = is_array($filteredDeals) ? $filteredDeals : [];
        $closeTrades = array_filter($filteredDeals, fn($deal) => $deal->Entry === 1);

        $closeTradesCount = $closeTrades ? count($closeTrades) : 0;
        $totalCommission = $filteredDeals ? array_sum(array_column($filteredDeals, 'Commission')) : 0;

        // Total trades = open positions + closed deals
        $totalTrades = $totalOpen + $closeTradesCount;

        // dd((int)$liveAccount->code);
        $accountData = $mt5Interface->getAccountBalance((int)$liveAccount->code);
        $accountData['total_trades'] = $totalTrades;
        $accountData['total_commission'] = $totalCommission;

        if ($accountData) {
            $liveAccount->update([
                'balance' => $accountData['balance'] ?? 0,
                'credit' => $accountData['credit'] ?? 0,
                'margin_free' => $accountData['margin_free'] ?? 0,
                'margin_level' => $accountData['margin_level'] ?? 0,
                'equity' => $accountData['equity'] ?? 0,
            ]);
        }

        return $accountData;
    }
}
