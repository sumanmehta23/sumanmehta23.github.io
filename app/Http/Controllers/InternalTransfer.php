<?php

namespace App\Http\Controllers;

use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\LiveAccount;
use App\MT5\MTEnDealAction;
use App\Models\TotalBalance;
use Illuminate\Http\Request;
use App\Models\TradeDeposit;
use App\Helpers\AccountHelper;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\BonusTransaction;
use Illuminate\Support\Facades\RateLimiter;

class InternalTransfer extends Controller
{
    protected $api;

    public function __construct(MTWebAPI $api)
    {
        $this->api = $api;
    }
    public function index()
    {
        $email = auth()->user()->email;
        AccountHelper::updateLiveAndDemoAccounts(auth()->user()->id, $this->api);
        $liveaccount_details = auth()->user()->liveAccounts()->with([
            'BonusTransaction' => function ($query) {
                $query->where('bonus_type', 'Bonus In')
                      ->orWhere('bonus_type', 'Bonus Out');
            }
        ])
        ->where('account_request_status', "!=", "0")
        ->get();
        // dd($liveaccount_details[8]->BonusTransaction->sum('bonus_amount'));
        return view('internal-transfer', compact('liveaccount_details'));
    }
    public function processTransfer(Request $request)
    {
        // dd($request->all());
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

        $settings = settings();
        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );
        $validated = $request->validate([
            'fromAccount' => 'required',
            'toAccount' => 'required|different:fromAccount',
            'transferable_amount' => 'required|numeric|min:1',
        ]);

        $fromAccountId = $request->input('fromAccount');
        $toAccountId = $request->input('toAccount');
        $userId=auth()->user()->id;
        $fromAccount = Account::where(['id'=> $fromAccountId,'user_id'=>$userId])->firstOrFail();
        $toAccount = Account::where(['id'=> $toAccountId,'user_id'=>$userId])->firstOrFail();
        // dump($fromAccount);
        // dd($toAccount);
        $total_bonus = BonusTransaction::where('account_id', $fromAccount->id)
            ->where(function($query) {
                $query->where('bonus_type', 'Bonus In')
                      ->orWhere('bonus_type', 'Bonus Out');
            })
            ->sum('bonus_amount');


        $transferable_amount = $request->input('transferable_amount');

        if((float)$transferable_amount > (float)$fromAccount->balance - (float)$total_bonus) {
            return redirect()->back()->with('error', 'Insufficient balance');
        }
        $email = auth()->user()->email;
        $ticket = NULL;
        activity()->causedBy(auth()->user()->id)
            ->withProperties(
                [
                    'ip' => $request->ip(),
                    'email' => auth()->user()->email,
                    'from' => $fromAccount->code,
                    'to' => $toAccount->code,
                    'transfer_amount' => $transferable_amount,
                    'remark' => 'Internal Transfer'
                ])
        ->event('create')
        ->log('Internal Transfer');
        // Withdraw from the first account
        $errorCode = $this->api->TradeBalance($fromAccount->code, $type = MTEnDealAction::DEAL_BALANCE, -$transferable_amount, 'withdraw', $ticket, true);
        if ($errorCode != MTRetCode::MT_RET_OK) {
            $error = MTRetCode::GetError($errorCode);
            return redirect()->back()->with('error', 'Failed to withdraw from the account.');
        } else {

            DB::transaction(function () use ($email, $fromAccount, $toAccount, $transferable_amount) {
                TradeWithdrawals::create([
                    'email' => $email,
                    'user_id' => auth()->user()->id,
                    'account_id' => $fromAccount->id,
                    'withdrawal_amount' => $transferable_amount,
                    'withdraw_type' => 'Internal Transfer',
                    'withdraw_to' => $toAccount->id,
                    'withdraw_date' => now(),
                    'status' => 1
                ]);
                // Deposit to the second account
                $errorCode = $this->api->TradeBalance($toAccount->code, $type = MTEnDealAction::DEAL_BALANCE, $transferable_amount, 'deposit', $ticket, true);
                if ($errorCode != MTRetCode::MT_RET_OK) {
                    $error = MTRetCode::GetError($errorCode);
                    return redirect()->back()->with('error', 'Deposit Failed.');
                } else {
                    // Log deposit
                    TradeDeposit::create([
                        'user_id' => auth()->user()->id,
                        'account_id' => $toAccount->id,
                        'email' => $email,
                        'code' => $toAccount->code,
                        'deposit_amount' => $transferable_amount,
                        'deposit_type' => 'Internal Transfer',
                        'deposit_from' => $fromAccount->id,
                        'status' => 1,
                    ]);
                    TotalBalance::create([
                        'user_id' => auth()->user()->id,
                        'account_id' => $toAccount->id,
                        'email' => $email,
                        'code' => $toAccount->code,
                        'trading_deposited' => $transferable_amount,
                        'deposit_type' => 'Internal Transfer',
                    ]);
                }
            });
        }
        RateLimiter::clear($key);
        return redirect()->back()->with('success', 'Internal Transfer Successfully Done');
    }
}
