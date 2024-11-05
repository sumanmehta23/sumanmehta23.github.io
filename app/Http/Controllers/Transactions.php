<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TradeDeposits;
use App\Models\TradeWithdrawals;
use App\Models\InternalTransfer;


class Transactions extends Controller
{
    public function index()
    {
        $email = $email = auth()->user()->email;
        $deposit_history = TradeDeposits::with('liveAccount.accountType')
            ->where('email', $email)
            ->where('deposit_type', '!=', 'Internal Transfer')
            ->orderBy('id', 'desc')
            ->get();

        // Fetching withdrawal history
        $withdrawal_history = TradeWithdrawals::where('email', $email)
            ->where('withdraw_type', '!=', 'Internal Transfer')
            ->orderBy('id', 'desc')
            ->get();

        // Fetching internal transfers
        $internal_transfer = InternalTransfer::where('email', $email)
            ->whereIn('type', ['Internal Transfer'])
            ->orderBy('raw_id', 'desc')
            ->get();

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
