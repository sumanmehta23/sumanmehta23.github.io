<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\BonusTransaction;
use App\Models\User;
use App\Models\ClientBankDetail;
use App\Models\TotalBalance;
use App\Models\WalletDeposit;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\DB;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\MT5\MTEnDealAction;
use App\Helpers\AccountHelper;


class TradeWithdrawal extends Controller
{
    protected $api;

    public function __construct(MTWebAPI $api)
    {
        $this->api = $api;
        $email = session('clogin');
        AccountHelper::updateLiveAndDemoAccounts($email, $api);
    }
    public function index()
    {
        $email = auth()->user()->email;
        $user=auth()->user();
        AccountHelper::updateLiveAndDemoAccounts($user->id, $this->api);
        $liveaccount_details = Account::with('accountType')
            ->where('user_id', $user->id)
            ->where('demo', false)
            ->get();
        $walletenabled = $user->wallet_enabled ?? false;
        $bank_details = ClientBankDetail::where('user_id', $user->id)->first() ?? [];
        $walletBalance=auth()->user()->wallet_balance;
        $totals = Account::where('user_id', $user->id)
            ->where('demo', false)
            ->selectRaw('SUM(equity) as equity, SUM(credit) as credit, SUM(balance) as balance')
            ->first();
        return view('trade_withdrawal', compact('liveaccount_details', 'walletenabled', 'bank_details', 'totals','walletBalance'));
    }
    public function withdraw(Request $request)
    {
        // dd($request->account_id);
        // TODO: 'Implement Policy to check ownership of the account';
        $settings = settings();
        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );
        $user_id = auth()->user()->id;
        $user_email = auth()->user()->email;
        $account_id = $request->account_id;
        $request->validate([
            'account_id' => 'required',
        ], [
            'account_id.required' => 'Account is not selected.',
        ]);

        $account = Account::with('accountType')
            ->where('id', $account_id)
            ->where('user_id', $user_id)
            ->firstOrFail();

        $total_bonus = BonusTransaction::where('account_id', $request->account_id)
            ->where(function($query) {
                $query->where('bonus_type', 'Bonus In')
                      ->orWhere('bonus_type', 'Bonus Out');
            })
            ->sum('bonus_amount');

        $withdraw_type = $request->input('withdraw_type');
        $amount = $request->input('withdraw_amount');
        $to_account_id = $request->input('withdraw_to', '');

        $request->validate([
            'withdraw_amount' => 'required|numeric|min:1'
        ]);

        // Get the account balance

        // Check for sufficient balance
        if ((float) ($amount) > ((float) $account->balance - (float) $total_bonus)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance',
            ], 400);
        }
        if ($withdraw_type == "Wallet Withdrawal") {
            $balance = abs((float)$amount) * -1;
            $comment = 'Withdraw';
            $ticket = NULL;
            $login = $account->code;
            $email = $account->email;
            $errorCode = $this->api->TradeBalance($login, $type = MTEnDealAction::DEAL_BALANCE, $balance, $comment, $ticket, $margin_check = true);
            if ($errorCode != MTRetCode::MT_RET_OK) {
                $error = MTRetCode::GetError($errorCode);
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong',
                    'error' => $error,
                ], 400);
            } else {
                DB::beginTransaction();
                try {
                    TradeWithdrawals::create([
                        'email' => $user_email,
                        'user_id' => $user_id,
                        'account_id' => $account->id,
                        'withdrawal_amount' => $amount,
                        'withdraw_type' => $withdraw_type,
                        // 'withdraw_to' => $to_account_id,
                        'wallet_qr' => '',
                        'Status' => 1
                    ]);
                    TotalBalance::create([
                        'account_id' => $account->id,
                        'email' => $email,
                        'user_id' => $user_id,
                        'deposit_amount' => $amount,
                    ]);
                    WalletDeposit::create([
                        'email' => $email,
                        'user_id' => $user_id,
                        'deposit_amount' => $amount,
                        'deposit_type' => 'Internal Transfer',
                        'status' => 1,
                    ]);
                    DB::commit();
                    return response()->json(['success' => "Your Wallet Was Credited $" . $amount]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    echo "<pre>";
                    print_r($e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Something Went Wrong !!! Please Try Again'
                    ], 400);
                }
            }
        }
    }
}
