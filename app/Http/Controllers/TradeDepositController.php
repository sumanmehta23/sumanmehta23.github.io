<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\LiveAccount;
use App\MT5\MTEnDealAction;
use App\Models\TotalBalance;
use Illuminate\Http\Request;
use App\Models\TradeDeposit;
use App\Models\WalletDeposit;
use App\Helpers\AccountHelper;
use App\Models\WalletWithdraw;
use App\Models\ClientBankDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TradeDepositController extends Controller
{
    protected $api;

    public function __construct(MTWebAPI $api)
    {
        $this->api = $api;
    }
    public function index()
    {
        $email = auth()->user()->email;
        $user = auth()->user();
        AccountHelper::updateLiveAndDemoAccounts(auth()->user()->id, $this->api);
        $liveaccount_details =auth()->user()->liveAccounts;
        $walletenabled = User::where('id', $user->id)->value('wallet_enabled') ?? false;
        $bank_details = ClientBankDetail::where('user_id', $user->id)->first() ?? [];
        $totals = Account::where('user_id', $user->id)
            ->where('demo', false)
            ->selectRaw('SUM(equity) as equity, SUM(credit) as credit, SUM(balance) as balance')
            ->first();
        $totalWd = WalletDeposit::where('user_id', $user->id)->where('status', 1)->sum('deposit_amount');
        $totalWw = WalletWithdraw::where('user_id', $user->id)->where('status','<>', 2)->sum('withdraw_amount');
        $wallet_balance = $totalWd - $totalWw;

        return view('trade_deposit', compact('liveaccount_details', 'walletenabled', 'bank_details', 'totals','wallet_balance'));
    }
    public function deposit(Request $request)
    {
        // var_dump($request->all());
        // die;
        $request->validate(
            [
                'user.account_id' => 'required',
                'user.deposit' => 'required|numeric',
                'user.deposit_type' => 'required',
                'deposit_proof' => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:2048',
            ],
            [
                'user.account_id.required' => 'You have to select an account to proceed.'
            ]
        );
        $settings = settings();
        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );

        $user = $request->input('user');
        $email = session('clogin');
        $depositamount = $user['deposit'];
        $email = $user['email'];
        $account_id = $user['account_id'];
        $user=auth()->user();
        $account = Account::where('user_id', $user->id)->where('id', $account_id)->firstOrFail();
        $deposit_type = $user['deposit_type'];
        $deposit_from = NULL;


        $comment = "Deposit";
        $ticket = NULL;

        // Calculate wallet balance
        
        $walletBalance = $user->wallet_balance ;
        // Check if there's enough balance
        if ($user['deposit_type'] === 'Wallet Transfer' && $walletBalance < $user['deposit']) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => "Insufficient wallet balance!!!!",
            ], 402);
        }
        // Handle file upload for deposit proof
        $depositProofPath = null;
        if ($request->hasFile('deposit_proof')) {
            $depositProofPath = $request->file('deposit_proof')->store('deposit_proofs', 'public');
        }
        $errorCode = $this->api->TradeBalance($account->code, $type = MTEnDealAction::DEAL_BALANCE, $depositamount, $comment, $ticket, $margin_check=true);

        if ($errorCode != MTRetCode::MT_RET_OK) {
            $error = MTRetCode::GetError($errorCode);
            // Return a JSON response with the error
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $error,
            ], 400); // 400 Bad Request
        } else {

            // Start a database transaction
            DB::transaction(function () use ($user, $email,$account, $depositProofPath,$depositamount,$deposit_type) {
                $tradeId = $account->code;
                
                // Insert into wallet withdraw
                WalletWithdraw::create([
                    'user_id' => $user->id,
                    'email' => $email,
                    'withdraw_amount' => $depositamount,
                    'withdraw_type' => "Internal Transfer",
                    'transaction_id' => $tradeId,
                    'status' => 1,
                ]);
                // Insert into total balance
                TotalBalance::create([
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'email' => $email,
                    'withdraw_amount' => $depositamount,
                    'status' => 1,
                ]);
                // Insert into trade deposit
                TradeDeposit::create([
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'email' => $email,
                    'trade_id' => $tradeId,
                    'deposit_amount' => $depositamount,
                    'deposit_type' => $deposit_type,
                    'deposit_from' => ($deposit_type == 'CRM') ? $deposit_type : null,
                    'deposit_proof' => $depositProofPath,
                    'status' => 1,
                ]);
            });
            AccountHelper::updateLiveAndDemoAccounts();
            return response()->json(['success' => 'Funds Successfully Deposited']);
        }
    }
}
