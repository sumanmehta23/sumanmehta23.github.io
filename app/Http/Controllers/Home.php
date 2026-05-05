<?php

namespace App\Http\Controllers;


use App\Models\Ib1;
use App\Models\Account;
use App\Models\DemoAccount;
use App\Models\LiveAccount;
use App\Models\TotalBalance;
use Illuminate\Http\Request;
use App\Models\WalletDeposit;
use App\Helpers\AccountHelper;
use App\Models\WalletWithdraw;
use App\Models\User;

use App\Http\Controllers\Controller;
use App\Models\TradeDeposit;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Home extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function dashboard()
    {
        $user = auth()->user();

        // Check if 2FA is enabled and not yet verified
        if ($user->two_factor_secret && $user->two_factor_confirmed_at && !Session::has('2fa:verified')) {
            return redirect()->route('two_factor_auth');
        }

        $userId = $user->id;
        AccountHelper::updateLiveAndDemoAccounts($userId);
        $walletBalance = $this->getWalletBalance($userId);
        $totalDeposit = $this->getTotalDeposit($userId);
        $totalWithdrawal = $this->getTotalWithdrawal($userId);
        $liveAccounts = $this->getLiveAccountCount($userId);
        $liveAccountDetails = $this->getLiveAccountDetails($userId);
        $demoAccountDetails = $this->getDemoAccountDetails($userId);
        $ibResult = $this->getIb1Details($userId);
        return view('dashboard', [
            'walletBalance' => $walletBalance,
            'totalDeposit' => $totalDeposit,
            'totalWithdrawal' => $totalWithdrawal,
            'liveAccounts' => $liveAccounts,
            'liveAccountDetails' => $liveAccountDetails,
            'demoAccountDetails' => $demoAccountDetails,
            'ibResult' => $ibResult
        ]);
    }
    public function getWalletBalance($userId)
    {
        $totalDeposit = WalletDeposit::where('user_id', $userId)
            ->where('status', 1)
            ->sum('deposit_amount');
        $totalWithdraw = WalletWithdraw::where('user_id', $userId)
            ->whereNotIn('status',[2,3])
            ->sum('withdraw_amount');
        $totalWithdrawFee = WalletWithdraw::where('user_id', $userId)
            ->whereNotIn('status',[2,3])
            ->sum('withdraw_transaction_fee');
        $walletBalance = $totalDeposit - ($totalWithdraw + $totalWithdrawFee);
        return round($walletBalance,2);
    }
    public function getTotalDeposit($userId)
    {
        // $totalDeposit = TotalBalance::where('user_id', $userId)
        //     ->sum('trading_deposited');
        $totalDeposit1 = WalletDeposit::where('user_id', $userId)
            ->whereIn('deposit_type', ['CryptoChill', 'CreditCardPayissa', 'RagaPay'])
            ->where('status', 1)
            ->sum('deposit_amount');
        $totalDeposit2 = TradeDeposit::where('user_id', $userId)
            ->whereIn('deposit_type', ['CryptoChill', 'CreditCardPayissa', 'RagaPay'])
            ->where('status', 1)
            ->sum('deposit_amount');

        $totalDeposit = ($totalDeposit1 + $totalDeposit2) ?: 0;
        return $totalDeposit;
    }
    public function getTotalWithdrawal($userId)
    {
        // $totalWithdrawal = TotalBalance::where('user_id', $userId)
        //     ->sum('trading_withdrawal');
        $totalWithdrawal1 = WalletWithdraw::where('user_id', $userId)
            ->where('withdraw_type', 'Wallet Withdrawal')
            ->where('status', 1)
            ->selectRaw('SUM(withdraw_amount + COALESCE(withdraw_transaction_fee, 0)) as total')
            ->value('total');
        $totalWithdrawal2 = TradeWithdrawals::where('user_id', $userId)
            ->where('withdraw_type', 'Trade Withdrawal')
            ->where('status', 1)
            ->selectRaw('SUM(withdrawal_amount + COALESCE(transaction_fee, 0)) as total')
            ->value('total');
        return ($totalWithdrawal1 + $totalWithdrawal2) ?: 0;
    }
    public function getLiveAccountCount($userId)
    {
       return auth()->user()->liveAccounts()
           ->whereNotNull('code')
           ->where('code', '!=', 'Rejected')
           ->count();
    }
    public function getLiveAccountDetails($email)
    {
        $liveaccount_details = auth()->user()->liveAccounts()
            ->with('accountType')
            ->whereNull('competition_status')
            ->whereNull('competition_start_date')
            ->whereNull('competition_end_date')
            ->orderByRaw('CASE WHEN account_request_status = 0 THEN 1 ELSE 0 END, id DESC')
            ->paginate(
                5,
                ['leverage', 'currency', 'balance', 'equity', 'id', 'user_id', 'code', 'registered_date', 'account_nick_name', 'account_type_id', 'platform', 'created_from'],
                'live_page'
            );

        return $liveaccount_details;
    }
    public function getDemoAccountDetails($email)
    {
        $demoaccount_details = auth()->user()->demoAccounts()
            ->with('accountType')
            ->whereNull('competition_start_date')
            ->whereNull('competition_end_date')
            ->orderBy('id', 'desc')
            ->paginate(
                5,
                ['leverage', 'currency', 'balance', 'equity', 'id', 'code', 'trade_platform', 'registered_date', 'platform', 'account_type_id', 'account_nick_name'],
                'demo_page'
            );
        return $demoaccount_details;
    }
    public function getIb1Details($userId)
    {
        $ibResult = Ib1::where('user_id', $userId)
            ->where('status', 1)
            ->first();
        return $ibResult;
    }
}
