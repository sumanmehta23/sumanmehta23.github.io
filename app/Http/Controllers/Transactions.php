<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Models\TradeDeposit;
use App\Models\TradeWithdrawals;
use App\Models\InternalTransfer;
use App\Models\WalletWithdraw;


class Transactions extends Controller
{
    public function index()
    {
        $email = $email = auth()->user()->email;
        $deposit_history = TradeDeposit::with('liveAccount.accountType')
            ->where('user_id',  auth()->user()->id)
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
            ->select('id','withdrawal_amount', 'withdraw_type','withdraw_date','email','status','withdraw_to','account_id')
            ->where('user_id', auth()->user()->id)
            ->get()
            ->map(function ($withdrawal) {
                if($withdrawal->withdraw_to){
                    $acc = Account::where('id',$withdrawal->withdraw_to)->first();
                }
                return [
                    'type' => $withdrawal->withdraw_to ? 'Internal Transfer' : 'Withdrawal',
                    'amount' => $withdrawal->withdrawal_amount,
                    'transaction_type' => $withdrawal->withdraw_type,
                    'email' => $withdrawal->email,
                    'status' => $withdrawal->status,
                    'it_to' => ($withdrawal->withdraw_to && $acc)  ? $acc->code : 'Wallet',
                    'it_from' => optional($withdrawal->account)->code ?? 'Wallet',
                    'source' => 'TDID',
                    'raw_id' => $withdrawal->id,
                    'date' => $withdrawal->withdraw_date,
                ];
            });
            // dd($tradeWithdrawals);
        // Fetch filtered data from TradeDeposit with deposit_amount
        $tradeDeposits = TradeDeposit::whereIn('deposit_type', ['Internal Transfer','Wallet Withdrawal','Wallet Transfer', 'CRM'])
            ->select('id', 'deposit_amount','deposted_date','deposit_type','email','status','code')
            ->where('user_id', auth()->user()->id)
            ->with('account')
            ->get()
            ->map(function ($deposit) {
                // dd($deposit);
                if(optional($deposit->account)->code){
                    $it_from = optional($deposit->account)->code;
                }elseif($deposit->deposit_type == 'Wallet Transfer'){
                    $it_from = 'Wallet';
                }else{
                    $it_from = 'CRM';
                }
                return [
                    'type' => 'Deposit',
                    'amount' => $deposit->deposit_amount,
                    'transaction_type' => $deposit->deposit_type,
                    'email' => $deposit->email,
                    'status' => $deposit->status,
                    'it_to' => $deposit->code,
                    'it_from' => $it_from, // Safe access
                    'source' => 'TDID',
                    'raw_id' => $deposit->id,
                    'date' => $deposit->deposted_date,
                ];
            });
            // dd($tradeDeposits);
        if($tradeDeposits->isEmpty() && $tradeWithdrawals->isEmpty()){
            $internal_transfer = [];
        }elseif($tradeDeposits->isEmpty()){
            $internal_transfer = $tradeWithdrawals;
        }else{
            $internal_transfer = $tradeDeposits->merge($tradeWithdrawals);
        }
        // Combine data into a single variable
        // $internal_transfer = $tradeDeposits->merge($tradeWithdrawals);
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
