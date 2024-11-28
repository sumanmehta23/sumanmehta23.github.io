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

        // $internal_transfer = InternalTransfer::where('email', $email)
        //     ->whereIn('type', ['Internal Transfer','Wallet Withdrawal','Wallet Transfer', 'CRM'])
        //     ->where('status', 1)
        //     ->orderBy('date', 'desc')
        //     ->get();

        $tradeWithdrawals = TradeWithdrawals::with('account')->whereIn('withdraw_type', ['Internal Transfer','Wallet Withdrawal','Wallet Transfer', 'CRM'])
            ->select('id','withdrawal_amount', 'withdraw_type','withdraw_date','email','status','to_account_id','account_id') // Select only required columns
            ->get()
            ->map(function ($withdrawal) {
                return [
                    'type' => 'withdrawal',
                    'amount' => $withdrawal->withdrawal_amount,
                    'transaction_type' => $withdrawal->withdraw_type,
                    'email' => $withdrawal->email,
                    'status' => $withdrawal->status,
                    'it_to' => $withdrawal->to_account_id,
                    'it_from' => $withdrawal->account->code,
                    'source' => 'TDID',
                    'raw_id' => $withdrawal->id,
                    'date' => $withdrawal->withdraw_date,
                ];
            })
            ;
        // Fetch filtered data from TradeDeposits with deposit_amount
        $tradeDeposits = TradeDeposits::whereIn('deposit_type', ['Internal Transfer','Wallet Withdrawal','Wallet Transfer', 'CRM'])
            ->select('id', 'deposit_amount','deposted_date','deposit_type','email','status') // Select only required columns
            ->with('account')
            ->get()
            ->map(function ($deposit) {
                return [
                    'type' => 'deposit',
                    'amount' => $deposit->deposit_amount,
                    'transaction_type' => $deposit->deposit_type,
                    'email' => $deposit->email,
                    'status' => $deposit->status,
                    'it_to' => $deposit->trade_id,
                    'it_from' => $deposit->deposit_from,
                    'source' => 'TDID',
                    'raw_id' => $deposit->id,
                    'date' => $deposit->deposted_date,
                ];
            });

        // Combine data into a single variable
        $internal_transfer = $tradeDeposits->merge($tradeWithdrawals);
        // Optional: Sort by date
        $internal_transfer = $internal_transfer->sortBy('date');
        // dd($internal_transfer);
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
