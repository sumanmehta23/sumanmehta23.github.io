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
use App\Models\BonusTransaction;
use Illuminate\Support\Facades\RateLimiter;

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
        // $liveaccount_details =auth()->user()->liveAccounts;
        $liveaccount_details = Account::with([
            'accountType',
            'BonusTransaction' => function ($query) {
                $query->where('bonus_type', 'Bonus In')
                      ->orWhere('bonus_type', 'Bonus Out');
            }
        ])
        ->where('user_id', $user->id)
        ->where('account_request_status', 1)
        ->where('demo', false)
        ->get();
        $walletenabled = User::where('id', $user->id)->value('wallet_enabled') ?? false;
        $bank_details = ClientBankDetail::where('user_id', $user->id)->first() ?? [];
        $totals = Account::where('user_id', $user->id)
            ->where('demo', false)
            ->selectRaw('SUM(equity) as equity, SUM(credit) as credit, SUM(balance) as balance')
            ->first();
        $totalWd = WalletDeposit::where('user_id', $user->id)->where('status', 1)->sum('deposit_amount');
        $totalWw = WalletWithdraw::where('user_id', $user->id)->whereNotIn('status',[2,3])->sum('withdraw_amount');
        $totalWwf = WalletWithdraw::where('user_id', $user->id)->whereNotIn('status',[2,3])->sum('withdraw_transaction_fee');
        $wallet_balance = round($totalWd - ($totalWw + $totalWwf), 2);


        return view('trade_deposit', compact('liveaccount_details', 'walletenabled', 'bank_details', 'totals','wallet_balance'));
    }
    public function deposit(Request $request)
    {
        // Generate a unique rate-limiting key based on user or IP
        $key = 'deposit:' . (auth()->id() ?: $request->ip());

        // Check if the user has exceeded the rate limit
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retryAfter = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => 'Too many requests',
                'error' => "Please wait {$retryAfter} seconds before trying again.",
            ], 429); // HTTP 429 Too Many Requests
        }

        // Increment the rate limiter
        RateLimiter::hit($key, 10); // Lock for 10 seconds

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

        $depositdata = $request->input('user');
        $email = session('clogin');
        $depositamount = $depositdata['deposit'];
        $email = $depositdata['email'];
        $account_id = $depositdata['account_id'];
        $user=auth()->user();
        $account = Account::where('user_id', $user->id)->where('id', $account_id)->firstOrFail();
        $deposit_type = $depositdata['deposit_type']??$request['user']['deposit_type'];
        $deposit_from = NULL;


        $comment = "Deposit";
        $ticket = NULL;

        // Calculate wallet balance
        $totalDeposits = WalletDeposit::where('user_id', $user->id)
        ->where('status', 1)
        ->sum('deposit_amount');

        $totalWithdrawals = WalletWithdraw::where('user_id', $user->id)
            ->whereNotIn('status', [2,3])
            ->sum('withdraw_amount');
        $totalWithdrawalsFee = WalletWithdraw::where('user_id', $user->id)
            ->whereNotIn('status', [2,3])
            ->sum('withdraw_transaction_fee');

        $walletBalance = (float) $totalDeposits - ((float) $totalWithdrawals + (float) $totalWithdrawalsFee);
        // Check if there's enough balance
        if ($depositdata['deposit_type'] === 'Wallet Transfer' && $walletBalance < $depositdata['deposit']) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => "Insufficient wallet balance!",
            ], 402);
        }
        // Handle file upload for deposit proof
        $depositProofPath = null;
        if ($request->hasFile('deposit_proof')) {
            $depositProofPath = $request->file('deposit_proof')->store('deposit_proofs', 'public');
        }
        activity()->causedBy($user->id)
            ->withProperties(
                [
                    'ip' => $request->ip(),
                    'email' => $user->email,
                    'code' => $account->code,
                    'deposit_amount' => $depositamount,
                    'remark' => 'Account Deposit'
                ])
        ->event('create')
        ->log('Account Deposit');
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
                    'code' => $tradeId,
                    'deposit_amount' => $depositamount,
                    'deposit_type' => $deposit_type,
                    'deposit_from' => ($deposit_type == 'CRM') ? 'CRM' : $deposit_type,
                    'deposit_proof' => $depositProofPath,
                    'status' => 1,
                ]);
            });
            AccountHelper::updateLiveAndDemoAccounts();
            // RateLimiter::clear($key);
            return response()->json(['success' => 'Funds Successfully Deposited']);
        }
    }
}
