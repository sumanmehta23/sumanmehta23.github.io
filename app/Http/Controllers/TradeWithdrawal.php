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
use App\Models\ClientWallet;
use App\Services\MailService as MailService;
use App\Services\MT5Service;

class TradeWithdrawal extends Controller
{
    protected $api;
    protected $settings;
    protected $mailService;

    public function __construct(MTWebAPI $api, MailService $mailService, MT5Service $mt5Service)
    {
        $this->settings = settings();
        $this->api = $api;
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $email = session('clogin');
        AccountHelper::updateLiveAndDemoAccounts($email, $api);
    }
    public function index()
    {
        $email = auth()->user()->email;
        $user = auth()->user();
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

        $client_banks = ClientWallet::where('user_id', $user->id)
            ->where('status', 1)
            ->where('verified', 1)
            ->where('wallet_delete_verification', 0)
            ->where('deleted_at', NULL)
            ->get();
        return view('trade_withdrawal', compact('liveaccount_details', 'walletenabled', 'bank_details', 'totals', 'walletBalance', 'client_banks'));
    }
    public function withdraw(Request $request)
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
        $user_fullname = auth()->user()->fullname;

        $account_id = $request->account_id;
        $request->validate([
            'account_id' => 'required',
            'withdraw_amount'=>'required|numeric|min:10',
        ], [
            'account_id.required' => 'Account is not selected.',
        ]);

        $account = Account::with('accountType', 'tradeDeposits')
            ->where('id', $account_id)
            ->where('user_id', $user_id)
            ->firstOrFail();

        $total_bonus = BonusTransaction::where('account_id', $request->account_id)
            ->where(function ($query) {
                $query->where('bonus_type', 'Bonus In')
                    ->orWhere('bonus_type', 'Bonus Out');
            })
            ->where('admin_remark', 'NOT LIKE', '%Credit%')
            ->where('admin_remark', 'NOT LIKE', '%10x Trader Leverage%')
            ->sum('bonus_amount');

        $withdraw_type = $request->input('withdraw_type');
        $amount = $request->input('withdraw_amount');
        $to_account_id = $request->input('withdraw_to', '');



        // Get the account balance

        // Check for sufficient balance
        if ((float) ($amount) > ((float) $account->balance - (float) $total_bonus)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance',
            ], 400);
        }
//        if ($withdraw_type == "Trade Withdrawal") {
            $balance = abs((float)$amount) * -1;
            $comment = 'Withdraw';
            $ticket = NULL;
            $login = $account->code;
            $email = $account->email;
            activity()->causedBy($user_id)
                ->withProperties(
                    [
                        'ip' => $request->ip(),
                        'email' => $user_email,
                        'code' => $login,
                        'withdraw_amount' => $balance,
                        'remark' => 'Account Withdraw'
                    ]
                )
                ->event('create')
                ->log('Account Withdraw');

            $clientWalletId = $request->input('client_wallet_id');
            $clientWallet = ClientWallet::where('id', $clientWalletId)->where('user_id', $user_id)->firstOrFail();

            if ($account->accountType->ac_group == 'LM\B-Book\10x\DF-B') {
                $total_deposit_amount = $account->tradeDeposits->sum('deposit_amount');
                $account_balance = $account->balance;

//                if ($account_balance >= $total_deposit_amount) {
//                    $multiple_value = $total_deposit_amount - $account_balance + (-$balance);
//                } elseif ($account_balance < $total_deposit_amount) {
//                    $multiple_value = $total_deposit_amount - $account_balance - ($balance);
//                }
                //Cehck current withdrawal request amount. If current withdrawal amount is less then his total profit , we don't deduct bonus .
                $accountProfit=$account_balance-$total_deposit_amount;
                if($amount > $accountProfit ){
                    $multiplier=$amount-$accountProfit;
                    if ($multiplier > 250) {
                        $multiplier = 250;
                    }
                    $bonusamount = -abs(-9 * $multiplier);
                    if (($error_code = $this->api->TradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $bonusamount, '10x Trader Leverage', $ticket, true)) !== MTRetCode::MT_RET_OK) {
                        return redirect()->back()->with('error', MTRetCode::GetError($error_code));
                    } else {
                        $deposit_details = BonusTransaction::create([
                            'email' => $account->email,
                            'user_id' => $user_id,
                            'account_id' => $account->id,
                            'code' => $account->code,
                            'bonus_amount' => $bonusamount,
                            'bonus_type' => 'Bonus Out',
                            'status' => 1,
                            'admin_remark' => '10x Trader Leverage',
                            'bonus_currency' => 'USD',
                            // 'created_by' => session('alogin')
                        ]);
                    }

                }

            }

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
                if ($amount >= 100) {
                    $withdrawal_amount = $amount;
                    $withdrawal_fee = 0;
                } else {
                    $withdrawal_fee = 5;
                    $withdrawal_amount = $amount - $withdrawal_fee;
                }

                try {
                    $TradeWithdrawal = TradeWithdrawals::create([
                        'email' => $user_email,
                        'user_id' => $user_id,
                        'account_id' => $account->id,
                        'withdrawal_amount' => $withdrawal_amount,
                        'transaction_fee' => $withdrawal_fee,
                        'withdraw_type' => $withdraw_type,
                        'code' => $account->code,
                        // 'withdraw_to' => $to_account_id,
                        'wallet_qr' => '',
                        'status' => 0,
                        'email_verified' => 0,
                        'client_wallet_id' => $clientWallet->id,
                    ]);

                    // TotalBalance::create([
                    //     'account_id' => $account->id,
                    //     'email' => $email,
                    //     'user_id' => $user_id,
                    //     'deposit_amount' => $amount,
                    // ]);
                    // WalletDeposit::create([
                    //     'email' => $email,
                    //     'user_id' => $user_id,
                    //     'deposit_amount' => $amount,
                    //     'deposit_type' => 'Internal Transfer',
                    //     'status' => 1,
                    // ]);
                    DB::commit();

                    $toEmail = $user_email;
                    $type = 'Withdrawal Details Verification';
                    $from = $settings['email_from_address'];
                    $emailSubject = $settings['admin_title'] . ' - ' . $type;
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";

                    $content =
                        '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
                        '<div>You are receiving this email because you have requested a withdrawal of amount $' . $withdrawal_amount . ' from your account ' . $account->code . '</div>' .
                        '<div>Click the link below to activate your Account Withdrawal</div>';

                    $templateVars = [
                        'name' => $user_fullname,
                        'server_name' => $settings['mt5_company_name'],
                        'site_link' => $settings['copyright_site_name_text'] . "/account_withdrawal_verify?accountWithdrawal_id=$TradeWithdrawal->id",
                        'email' => $from,
                        "content" => $content,
                        "title_right" => "Activate",
                        "subtitle_right" => "Your Account Withdrawal Request",
                        "btn_text" => "Verify"
                    ];
                    $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);
                    // RateLimiter::clear($key);
                    // return response()->json(['success' => "Verification email sent successfully."]);
                    return redirect()->back()->with('success', 'Verification email sent successfully.');
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
//        }
    }


    public function account_withdrawal_verify(Request $request)
    {

        if (!auth()->check()) {
            return redirect('/login');
        }

        $settings = settings();
        $id = auth()->user()->id;
        $accountWithdrawal_id = $request->query('accountWithdrawal_id');

        $new_wallet_Withdrawal = TradeWithdrawals::with('user')->where('user_id', $id)
            ->where('id', $accountWithdrawal_id)
            ->first();

        if ($new_wallet_Withdrawal) {
            if ($new_wallet_Withdrawal->email_verified  == 0) {
                $new_wallet_Withdrawal->email_verified = 1;
                $new_wallet_Withdrawal->save();
                activity()->causedBy(auth()->user())
                    ->withProperties(
                        [
                            'ip' => $request->ip(),
                            'email' => auth()->user()->email,
                            'withdraw_amount' => $new_wallet_Withdrawal->withdrawal_amount,
                            // 'withdraw_transaction_fee' => $new_wallet_Withdrawal->withdraw_transaction_fee,
                            'wallet_withdraw_id' => $new_wallet_Withdrawal->id,
                            'remark' => 'Trade Withdraw'
                        ]
                    )
                    ->event('create')
                    ->log('Account Withdraw');
                $from = $settings['email_from_address'];
                $emailSubject = $settings['admin_title'] . ' - Thank You for Confirming Your Trade Withdrawal';
                $htmlContent = "";
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $content =
                    '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
                    '<div>Your Account withdrawal has been successfully confirmed.</div>';
                $templateVars = [
                    'name' => $new_wallet_Withdrawal->user->fullname,
                    'server_name' => $settings['mt5_company_name'],
                    'email' => $settings['email_from_address'],
                    "content" => $content,
                    "title_right" => "Account Withdrawal Verification",
                    "subtitle_right" => "Successful",
                ];
                $this->mailService->sendEmail($new_wallet_Withdrawal->user->email, $emailSubject, $headers, '', $templateVars);
                return redirect()->route('transactions')->with('status', 'WoW! Your Account Withdrawal is now Verified');
            } else {
                return redirect()->route('dashboard')->with('error', 'Sorry! Account Withdrawal is already Verified');
            }
        } else {
            return redirect()->route('dashboard')->with('error', 'Sorry! No Account Withdrawal Found. Signup here');
        }
    }
}
