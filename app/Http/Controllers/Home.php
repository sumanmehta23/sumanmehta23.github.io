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
use App\Models\Ib1;

class Home extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function dashboard()
    {
        $email = $email = auth()->user()->email;
        $walletBalance = $this->getWalletBalance($email);
        $totalDeposit = $this->getTotalDeposit($email);
        $totalWithdrawal = $this->getTotalWithdrawal($email);
        $liveAccounts = $this->getLiveAccountCount($email);
        $liveAccountDetails = $this->getLiveAccountDetails($email);
        $demoAccountDetails = $this->getDemoAccountDetails($email);
        $ibResult = $this->getIb1Details($email);
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
    public function getWalletBalance($email)
    {
        $totalDeposit = WalletDeposit::where('email', $email)
            ->where('status', 1)
            ->sum('deposit_amount');
        $totalWithdraw = WalletWithdraw::where('email', $email)
            ->where('status','<>', 2)
            ->sum('withdraw_amount');
        $walletBalance = $totalDeposit - $totalWithdraw;
        return $walletBalance;
    }
    public function getTotalDeposit($email)
    {
        $totalDeposit = TotalBalance::where('email', $email)
            ->sum('trading_deposited');
        $totalDeposit = $totalDeposit ?: 0;
        return $totalDeposit;
    }
    public function getTotalWithdrawal($email)
    {
        $totalWithdrawal = TotalBalance::where('email', $email)
            ->sum('trading_withdrawal');
        return $totalWithdrawal ?: 0;
    }
    public function getLiveAccountCount($email)
    {
        $liveAccountsCount = LiveAccount::where('email', $email)->count();
        return $liveAccountsCount;
    }
    public function getLiveAccountDetails($email)
    {
        $liveaccount_details = LiveAccount::with('accountType')
            ->where('email', $email)
            ->orderBy('id', 'desc')
            ->get(['leverage', 'currency', 'balance', 'equity', 'id as id', 'trade_id', 'trade_platform', 'registered_date']);
        return $liveaccount_details;
    }
    public function getDemoAccountDetails($email)
    {
        $demoaccount_details = DemoAccount::with('accountType')
            ->where('email', $email)
            ->orderBy('id', 'desc')
            ->get(['leverage', 'currency', 'balance', 'equity', 'id as id', 'trade_id', 'trade_platform', 'registered_date']);
        return $demoaccount_details;
    }
    public function getIb1Details($email)
    {
        $ibResult = Ib1::where('email', $email)
            ->where('status', 1)
            ->first();
        return $ibResult;
    }
}
