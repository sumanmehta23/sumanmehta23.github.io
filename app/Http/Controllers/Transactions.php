<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TradeDeposits;
use App\Models\TradeWithdrawals;
use App\Models\InternalTransfer;
use App\Models\WalletWithdraw;


class Transactions extends Controller
{
    public function index()
    {
        $email = $email = auth()->user()->email;
        $deposit_history = TradeDeposits::with('liveAccount.accountType')
            ->where('email', $email)
            ->where('deposit_type', 'CryptoChill')
            ->orderBy('id', 'desc')
            ->get();

        // Fetching withdrawal history
        $withdrawal_history = WalletWithdraw::where('email', $email)
            ->where('withdraw_type', 'Wallet Withdrawal')
            ->orderBy('id', 'desc')
            ->get();

        // Fetching internal transfers
        $internal_transfer = TradeDeposits::where('email', $email)
            ->whereIn('deposit_type', ['Wallet Transfer','Internal Transfer'])
            ->where('status', 1)
            ->orderBy('deposted_date', 'desc')
            ->get();
        // foreach ($internal_transfer as $key => $value) {
        //     echo ($value).'/n';
        // }
        // die;
        return view('transactions', compact('deposit_history', 'withdrawal_history', 'internal_transfer'));
    }
    // public function history()
    // {
    //     $email = auth()->user()->email;
    //     $deposit_history = TradeDeposit::with('liveAccount.accountType') // Eager loading relationships
    //         ->where('email', $email)
    //         ->whereNotIn('deposit_type', ['Internal Transfer'])
    //         ->orderBy('id', 'desc')
    //         ->get();
    //     $withdrawal_history = [];
    //     $internal_transfer = [];
    //     return view('transaction-history', compact('deposit_history', 'withdrawal_history', 'internal_transfer'));
    // }
}
