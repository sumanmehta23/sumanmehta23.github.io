<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\WalletDeposit;
use App\Models\WalletWithdraw;
use App\Models\TotalBalance;
use App\Models\LiveAccount;
use App\Models\DemoAccount;
use App\Models\Account;

use App\Models\Ib1;

class Home extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function dashboard()
    {
        $userId= auth()->user()->id;
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
            ->where('status','<>', 2)
            ->sum('withdraw_amount');
        $walletBalance = $totalDeposit - $totalWithdraw;
        return $walletBalance;
    }
    public function getTotalDeposit($userId)
    {
        $totalDeposit = TotalBalance::where('user_id', $userId)
            ->sum('trading_deposited');
        $totalDeposit = $totalDeposit ?: 0;
        return $totalDeposit;
    }
    public function getTotalWithdrawal($userId)
    {
        $totalWithdrawal = TotalBalance::where('user_id', $userId)
            ->sum('trading_withdrawal');
        return $totalWithdrawal ?: 0;
    }
    public function getLiveAccountCount($userId)
    {
       return auth()->user()->liveAccounts()->count();
    }
    public function getLiveAccountDetails($email)
    {
        $liveaccount_details = auth()->user()->liveAccounts()
            ->orderBy('id', 'desc')
            ->get(['leverage', 'currency', 'balance', 'equity', 'id', 'code', 'trade_platform', 'registered_date']);
        return $liveaccount_details;
    }
    public function getDemoAccountDetails($email)
    {
        $demoaccount_details = auth()->user()->demoAccounts()
            ->orderBy('id', 'desc')
            ->get(['leverage', 'currency', 'balance', 'equity', 'id', 'code', 'trade_platform', 'registered_date']);
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
