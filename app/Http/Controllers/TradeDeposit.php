<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClientBankDetails;
use App\Models\TotalBalance;
use App\Models\TradeDeposits;
use Illuminate\Http\Request;
use App\Models\LiveAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\WalletDeposit;
use App\Models\WalletWithdraw;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\MT5\MTEnDealAction;
use App\Helpers\AccountHelper;

class TradeDeposit extends Controller
{
    protected $api;

    public function __construct(MTWebAPI $api)
    {
        $this->api = $api;
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
        return view('trade_deposit', compact('liveaccount_details', 'walletenabled', 'bank_details', 'totals'));
    }
    public function deposit(Request $request)
    {
        $request->validate(
            [
                'user.trade_id' => 'required',
                'user.deposit' => 'required|numeric',
                'user.deposit_type' => 'required',
                'deposit_proof' => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:2048',
            ],
            [
                'user.trade_id.required' => 'You have to select an account to proceed.'
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
        $trading_deposited1 = $user['deposit'];
        $email = $user['email'];
        $trade_id = $user['trade_id'];
        $deposit_type = $user['deposit_type'];
        $deposit_from = NULL;


        $comment = "Deposit";
        $ticket = NULL;

        // Calculate wallet balance
        $totalWd = WalletDeposit::where('email', $email)->where('status', 1)->sum('deposit_amount');
        $totalWw = WalletWithdraw::where('email', $email)->where('status', 1)->sum('withdraw_amount');
        $walletBalance = $totalWd - $totalWw;
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
        $errorCode = $this->api->TradeBalance($trade_id, $type = MTEnDealAction::DEAL_BALANCE, $trading_deposited1, $comment, $ticket, $margin_check=true);

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
            DB::transaction(function () use ($user, $email, $depositProofPath) {
                $tradingDeposited = $user['deposit'];
                $tradeId = $user['trade_id'];
                $depositType = $user['deposit_type'];
                // Insert into wallet withdraw
                WalletWithdraw::create([
                    'email' => $email,
                    'withdraw_amount' => $tradingDeposited,
                    'withdraw_type' => $depositType,
                    'transaction_id' => $tradeId,
                    'status' => 1,
                ]);
                // Insert into total balance
                TotalBalance::create([
                    'email' => $email,
                    'trade_id' => $tradeId,
                    'withdraw_amount' => $tradingDeposited,
                    'status' => 1,
                ]);
                // Insert into trade deposit
                TradeDeposits::create([
                    'email' => $email,
                    'trade_id' => $tradeId,
                    'deposit_amount' => $tradingDeposited,
                    'deposit_type' => $depositType,
                    'deposit_from' => null,
                    'deposit_proof' => $depositProofPath,
                    'status' => 1,
                ]);
            });
            AccountHelper::updateLiveAndDemoAccounts();
            return response()->json(['success' => 'Your Live Account Got Deposited']);
        }
    }
}
