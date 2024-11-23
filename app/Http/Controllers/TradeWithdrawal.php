<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
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
        AccountHelper::updateLiveAndDemoAccounts($email, $this->api);
        $liveaccount_details = Account::with('accountType')
            ->where('email', $email)
            ->where('demo', false)
            ->get();
        $walletenabled = User::where('email', $email)->value('wallet_enabled') ?? false;
        $bank_details = ClientBankDetail::where('email', $email)->first() ?? [];
        $totals = Account::where('email', $email)
            ->selectRaw('SUM(equity) as equity, SUM(credit) as credit, SUM(balance) as balance')
            ->first();
        return view('trade_withdrawal', compact('liveaccount_details', 'walletenabled', 'bank_details', 'totals'));
    }
    public function withdraw(Request $request)
    {
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
        $account_id = $request->input('account_id');
        $account = LiveAccount::with('accountType')
            ->where('id', $account_id)
            ->where('user_id', $user_id)
            ->firstOrFail();

        $withdraw_type = $request->input('withdraw_type');
        $amount = $request->input('withdraw_amount');
        $withdraw_to = $request->input('withdraw_to', '');

        $request->validate([
            'withdraw_amount' => 'required|numeric|min:1'
        ]);

        // Get the account balance

        // Check for sufficient balance
        if ($amount > $amount->Balance) {
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
                        'user_id' => $user_id,
                        'account_id' => $account->id,
                        'withdrawal_amount' => $amount,
                        'withdraw_type' => $withdraw_type,
                        'withdraw_to' => $withdraw_to,
                        'wallet_qr' => '',
                        'Status' => 1
                    ]);
                    TotalBalance::create([
                        'email' => $email,
                        'deposit_amount' => $amount,
                    ]);
                    WalletDeposit::create([
                        'email' => $email,
                        'deposit_amount' => $amount,
                        'deposit_type' => 'Internal Transfer',
                        'Status' => 1,
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
