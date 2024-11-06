<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveAccount;
use App\Models\User;
use App\Models\ClientBankDetails;
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
        $liveaccount_details = LiveAccount::with('accountType')
            ->where('email', $email)
            ->get();
        $walletenabled = User::where('email', $email)->value('wallet_enabled') ?? false;
        $bank_details = ClientBankDetails::where('email', $email)->first() ?? [];
        $totals = LiveAccount::where('email', $email)
            ->selectRaw('SUM(equity) as equity, SUM(credit) as credit, SUM(balance) as balance')
            ->first();
        return view('trade_withdrawal', compact('liveaccount_details', 'walletenabled', 'bank_details', 'totals'));
    }
    public function withdraw(Request $request)
    {
        $settings = settings();
        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );
        $email = session('clogin');
        $liveaccount_details = LiveAccount::with('accountType')
            ->where('email', $email)
            ->get()->toArray();
        $trade_id = $request->input('trade_id');
        $withdraw_type = $request->input('withdraw_type');
        $amount = $request->input('withdraw_amount');
        $withdraw_to = $request->input('withdraw_to', '');

        $request->validate([
            'withdraw_amount' => 'required|numeric|min:1'
        ]);
        // Get the account balance
        $account = array_filter($liveaccount_details, function ($obj) use ($trade_id) {
            return $obj['trade_id'] == $trade_id;
        });
        // Check for sufficient balance
        if (isset($account[0]) && $amount > $account[0]['Balance']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance',
            ], 400);
        }
        if ($withdraw_type == "Wallet Withdrawal") {
            $balance = abs((float)$amount) * -1;
            $comment = 'Withdraw';
            $ticket = NULL;
            $login = $trade_id;
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
                        'email' => $email,
                        'trade_id' => $trade_id,
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
