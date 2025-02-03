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
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;


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
        // $liveaccount_details = Account::with('accountType','BonusTransaction')
        //     ->where('user_id', $user->id)
        //     ->where('demo', false)
        //     ->get();
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

        $walletenabled = $user->wallet_enabled ?? false;
        $bank_details = ClientBankDetail::where('user_id', $user->id)->first() ?? [];
        $walletBalance = round(auth()->user()->wallet_balance, 2);
        $totals = Account::where('user_id', $user->id)
            ->where('demo', false)
            ->selectRaw('SUM(equity) as equity, SUM(credit) as credit, SUM(balance) as balance')
            ->first();
        return view('trade_withdrawal', compact('liveaccount_details', 'walletenabled', 'bank_details', 'totals','walletBalance'));
    }
    public function withdraw(Request $request)
    {
        // dd($request->account_id);
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
            ->where('admin_remark', 'NOT LIKE', '%Credit%')
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
                    // RateLimiter::clear($key);
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

    public function deleteAccounts(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required',
            'email' => 'required|email',
        ]);

        $account = Account::with('user')->where('id', $request->id)->first();

        $settings = settings();
        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );

        try {
            $login = $account->code;

            if($account->balance > 0) {
                $balance = abs((float)$account->balance) * -1;
                $comment = 'Withdraw';
                $ticket = NULL;
                $errorCode = $this->api->TradeBalance($login, $typed = MTEnDealAction::DEAL_BALANCE, $balance, $comment, $ticket, $margin_check = true);
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
                            'email' => $account->user->email,
                            'user_id' => $account->user->id,
                            'account_id' => $account->id,
                            'withdrawal_amount' => $account->balance ,
                            'withdraw_type' => 'Wallet Withdrawal',
                            // 'withdraw_to' => $to_account_id,
                            'wallet_qr' => '',
                            'Status' => 1
                        ]);
                        TotalBalance::create([
                            'account_id' => $account->id,
                            'email' => $account->user->email,
                            'user_id' => $account->user->id,
                            'deposit_amount' => $account->balance ,
                        ]);
                        WalletDeposit::create([
                            'email' => $account->user->email,
                            'user_id' => $account->user->id,
                            'deposit_amount' => $account->balance ,
                            'deposit_type' => 'Internal Transfer',
                            'status' => 1,
                        ]);
                        DB::commit();
                        // RateLimiter::clear($key);
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

            if (($error_code = $this->api->UserDelete($login)) != MTRetCode::MT_RET_OK) {

                $error = MTRetCode::GetError($error_code);
                dd('ssssssss');
                Log::error('MT5 live account create error : ' . $error.' for user '.json_encode($login));
                return ["status" => false, "message" => $error];
            } else {
                Log::info('MT5 account deleted successfully'.json_encode($login).' with server response ');
            }

            if ($account) {
                $account->delete(); // Soft delete the account

                // Refresh the model to include the `deleted_at` timestamp
                $account->refresh();

                $email = $validatedData['email'];
                $type = $account->demo == "1" ? "Demo account" : "Live account";

                $from = $settings['email_from_address'];
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $emailSubject = $settings['admin_title'] . ' - Account Deleted';
                $content = '<div>We are pleased to inform you that your account has been deleted.</div>
                            <div><b>Account code: </b>' . $account->code . '</div>
                            <div><b>Account type: </b>' . $type . '</div>
                            <div><b>Created Date: </b>' . $account->created_at . '</div>
                            <div><b>Deleted Date: </b>' . $account->deleted_at . '</div>';
                $templateVars = [
                    'name' => $account->name,
                    'site_link' => $settings['copyright_site_name_text'],
                    'email' => $settings['email_from_address'],
                    'content' => $content,
                    'title_right' => 'Account',
                    'subtitle_right' => 'Deleted',
                    'btn_text' => 'Go To Dashboard',
                ];
                $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);

                return redirect()->back()->with('success', 'Account deleted successfully.');
            } else {
                return redirect()->back()->with('error', 'Account not found.');
            }
        } catch (\Exception $e) {
            Log::error('Exception: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            session()->flash('error', 'Exception: ' . $e->getMessage());
        }
    }
}
