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

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retryAfter = RateLimiter::availableIn($key);
            return redirect()->back()->with([
                'error' => "Too many requests. Please wait {$retryAfter} seconds before trying again."
            ]);
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
            'withdraw_amount' => 'required|numeric|min:10',
        ], [
            'account_id.required' => 'Account is not selected.',
        ]);

        $account = Account::with('accountType', 'tradeDeposits', 'BonusTransaction')
            ->where('id', $account_id)
            ->where('user_id', $user_id)
            ->firstOrFail();

        // dd($account->tradeDeposits[0]->deposit_amount );
        $bonus = BonusTransaction::where('account_id', $request->account_id)
            ->where(function ($query) {
                $query->where('bonus_type', 'Bonus In')
                    ->orWhere('bonus_type', 'Bonus Out');
            })
            ->selectRaw("
                            SUM(CASE
                                WHEN admin_remark NOT LIKE '%Credit%'
                                AND admin_remark NOT LIKE '%10x Trader Leverage%'
                                AND admin_remark NOT LIKE '%Promo Bonus%'
                                AND admin_remark NOT LIKE '%Promo Deduction%'
                                AND admin_remark NOT LIKE '%Promo Addition%'
                                THEN bonus_amount
                                ELSE 0
                            END) AS total_bonus,

                            SUM(CASE
                                WHEN admin_remark LIKE '%Promo Bonus%'
                                THEN bonus_amount
                                ELSE 0
                            END) AS total_promo_bonus_amount,

                            SUM(CASE
                                WHEN admin_remark LIKE '%Promo Bonus%'
                                THEN bonus_used
                                ELSE 0
                            END) AS total_promo_bonus_used,

                            SUM(CASE
                                WHEN admin_remark LIKE '%Promo Deduction%'
                                THEN bonus_amount
                                ELSE 0
                            END) AS total_promo_deduction
                        ")
            ->first();

        $total_bonus = $bonus->total_bonus;
        $total_promo_bonus = $bonus->total_promo_bonus_amount;
        $total_promo_bonus_used = $bonus->total_promo_bonus_used;
        $promo_left = $total_promo_bonus - $total_promo_bonus_used;
        //Calculate deposit/profit other then promo bonus 
        $withdraw_type = $request->input('withdraw_type');
        $amount = $request->input('withdraw_amount');
        $to_account_id = $request->input('withdraw_to', '');

        // Get the account balance
        Log::alert("promo_left " . ($promo_left));
        Log::alert("amount " . (float) ($amount));
        Log::alert("account->balance " . (float) ($account->balance));
        Log::alert("account->credit " . (float) ($account->credit));
        Log::alert("total_bonus " . (float) ($total_bonus));
        Log::alert("sadasdsaaaaaa " . (float) ((float) ($amount) > (((float) $account->balance + (float) $account->credit) - (float) $total_bonus)));
        $totalBonusDepositValue = BonusTransaction::select(DB::raw('SUM(bonus_amount / (promocode.promo_percentage / 100)) as total'))
            ->leftJoin('promocode', 'bonus_transactions.promocode_id', '=', 'promocode.id')
            ->where('bonus_transactions.account_id', $request->account_id)
            ->value('total');
        Log::alert("threshold " . $totalBonusDepositValue);
        // return redirect()->back()->with('error', 'Withdrawal disabled at the moment . Please contact support for assistance.');

        // Check for sufficient balance
        if ((float) ($amount) > (((float) $account->balance))) {
            return redirect()->back()->with('error', 'Insufficient balance');
        }
        if ((float)$amount > (float) $account->balance) {
            $balance = abs((float)$amount - ((float)$amount - (float) $account->balance)) * -1;
        } else {
            $balance = abs((float)$amount) * -1;
        }
        $mt5account = new \stdClass();
        $comment = 'Withdraw';
        $ticket = NULL;
        $ticket1 = NULL;
        $login = $account->code;
        $email = $account->email;
        // activity()->causedBy($user_id)
        //     ->withProperties(
        //         [
        //             'ip' => $request->ip(),
        //             'email' => $user_email,
        //             'code' => $login,
        //             'withdraw_amount' => $balance,
        //             'remark' => 'Account Withdraw'
        //         ]
        //     )
        //     ->event('create')
        //     ->log('Account Withdraw');

        $clientWalletId = $request->input('client_wallet_id');
        $clientWallet = ClientWallet::where('id', $clientWalletId)->where('user_id', $user_id)->firstOrFail();

        if ($account->accountType->ac_group == 'LM\B-Book\10x\DF-B') {
            $total_deposit_amount = $account->tradeDeposits->sum('deposit_amount');
            $account_balance = $account->balance;

            // if ($account_balance >= $total_deposit_amount) {
            //     $multiple_value = $total_deposit_amount - $account_balance + (-$balance);
            // } elseif ($account_balance < $total_deposit_amount) {
            //     $multiple_value = $total_deposit_amount - $account_balance - ($balance);
            // }
            //Cehck current withdrawal request amount. If current withdrawal amount is less then his total profit , we don't deduct bonus .
            $accountProfit = $account_balance - $total_deposit_amount;


            if ($amount > $accountProfit) {
                if ($accountProfit < 0) {
                    $multiplier = $amount;
                } else {
                    $multiplier = $amount - $accountProfit;
                }

                if ($multiplier > 250) {
                    $multiplier = 250;
                }
                $bonusamount = -abs(-9 * $multiplier);

                // if($account->code==817752){
                //     dump($account_balance);
                //     dump($total_deposit_amount);
                //     dump($accountProfit);
                //     dump($multiplier);
                //     dump($bonusamount);
                // }

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

        Log::alert("balance withdraw " . (float) ($balance));
        Log::alert("account " . ($login));

        // $errorCode1 = $this->api->TradeBalance($login, $type = MTEnDealAction::DEAL_BALANCE, $balance, $comment, $ticket1, $margin_check = true);
        if (1 == 2) {
            // if ($errorCode1 != MTRetCode::MT_RET_OK) {
            $error = MTRetCode::GetError($errorCode1);
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

            $total_promo_deducted = 0;
            $deductableAmount = 0; //Amount from withdrwal request which will be used to calculate promo deductions . So this must be sum of deposits without bonus +/- profit/loss on the account
            if ($promo_left) {
                $tradedeposits = $account->tradeDeposits->where('deposit_amount', '>', 0)->sum('deposit_amount');
                Log::alert("tradedeposits " . $tradedeposits);
                $tradewithdrawals = $account->tradeWithdrawals->where('withdrawal_amount', '>', 0)->sum('withdrawal_amount');
                Log::alert("tradewithdrawals " . $tradewithdrawals);
                $depositswithoutpromo = $account->tradeDeposits->whereNull('promocode_code')->sum('deposit_amount');
                Log::alert("depositswithoutpromo " . $depositswithoutpromo);
                $depositswithpromo = $account->tradeDeposits->whereNotNull('promocode_code')->sum('deposit_amount');
                Log::alert("depositswithpromo " . $depositswithpromo);
                $pnl = $account->balance - $tradedeposits + $tradewithdrawals;
                Log::alert("PNL " . $pnl);
                $amountForDeductions = $amount - $depositswithoutpromo - $pnl;
                Log::alert("amountForDeductions " . $amountForDeductions);
                die();
                $promos = $account->BonusTransaction()
                    ->where('admin_remark', 'Promo Bonus')
                    ->with('promocode') // assuming 'promocode' is the relation name
                    ->where('bonus_amount', '>', 'bonus_used')
                    ->get()
                    ->sortByDesc(function ($transaction) {
                        return optional($transaction->promocode)->promo_percentage;
                    });

                $i = 0;
                if (($error_code2 = $this->api->UserAccountGet($account->code, $mt5account)) != MTRetCode::MT_RET_OK) {
                    session()->flash('error', 'MT5 ' . $account->code . ': ' . MTRetCode::GetError($error_code2));
                    // break;
                    die();
                }
                $mt5account->Balance = $mt5account->Balance - $amount;
                $deductionThreshold = $mt5account->Balance - $totalBonusDepositValue - $tradewithdrawals;
                Log::alert("deductionThreshold " . $mt5account->Balance . "-" . $totalBonusDepositValue . "=" . $deductionThreshold);
                echo $pnl = $account->balance - $tradedeposits + $tradewithdrawals;
                Log::alert("PNL " . $pnl);
                die();

                foreach ($promos as $promo) {
                }



                while ($promo_left > 0 && isset($promos[$i])) {
                    $promo = $promos[$i];
                    $promo_percentage_value = $promo->promocode->promo_percentage;
                    $x = $promo->bonus_amount / ($promo_percentage_value / 100);

                    if ($promo->bonus_amount == $promo->bonus_used) {
                        $i++;
                        continue; // Skip to next promo if old deduction exceeds available bonus
                    }

                    if (($error_code2 = $this->api->UserAccountGet($account->code, $mt5account)) != MTRetCode::MT_RET_OK) {
                        session()->flash('error', 'MT5 ' . $account->code . ': ' . MTRetCode::GetError($error_code2));
                        break;
                    }
                    $mt5account->Balance = $mt5account->Balance - $amount;
                    $deductionThreshold = $mt5account->Balance - $totalBonusDepositValue - $tradewithdrawals;
                    Log::alert("deductionThreshold " . $mt5account->Balance . "-" . $totalBonusDepositValue . "=" . $deductionThreshold);
                    $pnl = $account->balance - $tradedeposits + $tradewithdrawals;
                    Log::alert("PNL " . $pnl);
                    // if ($mt5account->Balance < $promo_left) {
                    if ($mt5account->Balance < $totalBonusDepositValue) {

                        // Start deduction only when balance reaches promo_left
                        // if ($promo_percentage_value < 100) {
                        //     $amount_to_deduct = - ($amount);
                        // }
                        echo $account->balance . "=" . $totalBonusDepositValue . "<br>\n";
                        // $amount_to_deduct=??;
                        // if ($amount_to_deduct < $amount) {
                        //     $amount_to_deduct = - ($amount);
                        // }
                        //Need to make 10
                        echo $amount_to_deduct;
                        die();
                        //90-100-10
                        // if ($account->balance >= $promo_left) {

                        //     if ($amount > $account->balance) {
                        //         $amount_to_deduct = -$account->credit;
                        //     } else {
                        //         $amount_to_deduct = ($account->balance - $amount) - $promo_left;
                        //     }
                        // } elseif ($account->balance < $promo_left) {
                        //     if ($amount >= $account->balance) {
                        //         $amount_to_deduct = -$account->credit;
                        //     } else {
                        //         $amount_to_deduct = - ($amount);
                        //     }
                        // }
                        // $amount_to_deduct = - ($amount);
                        Log::alert("amount_to_deduct " . $amount_to_deduct);
                        if ($amount_to_deduct < 0) {
                            $threshold = -$amount_to_deduct;
                            Log::alert("threshold " . $threshold);
                            $promo_deduction = ($threshold * ($promo_percentage_value / 100)) - $promo->bonus_used;

                            Log::alert("promo_deduction " . $promo_deduction);
                            // Ensure we do not deduct more than available in this promo bucket
                            $max_deductible = $promo->bonus_amount - $promo->bonus_used;
                            if ($mt5account->Balance == 0) {

                                $promo_deduction = $max_deductible;

                                // Updating leverage
                                $trade_user = NULL;
                                $this->api->UserGet($account->code, $trade_user);
                                if (($error_code = $this->api->UserGet($account->code, $trade_user)) != MTRetCode::MT_RET_OK) {
                                    return redirect()->back()->with('error', 'Something went wrong on Updating leverage' . MTRetCode::GetError($error_code));
                                }

                                $leverage = round($account->leverage * (100 / ($trade_user->Balance + $trade_user->Credit)), 2);
                                $trade_user->Leverage = $account->leverage;

                                $updated_user = "";
                                if (($error_code = $this->api->UserUpdate($trade_user, $updated_user)) != MTRetCode::MT_RET_OK) {
                                    return redirect()->back()->with("error", "Something went wrong on Updating leverage" . MTRetCode::GetError($error_code));
                                }
                            }
                            Log::alert("promo_percentage_value " . $promo_percentage_value);
                            Log::alert("promo_deduction " . $promo_deduction);
                            die();
                            if ($promo_deduction > 0) {
                                $deduction = abs((float)$promo_deduction) * -1;
                                if (($error_code3 = $this->api->TradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $deduction, 'Promo Deduction', $ticket1, true)) !== MTRetCode::MT_RET_OK) {
                                    $balance = abs((float)$balance) * -1;
                                    $errorCode1 = $this->api->TradeBalance($login, $type = MTEnDealAction::DEAL_BALANCE, $balance, $comment, $ticket1, $margin_check = true);
                                    if ($errorCode1 != MTRetCode::MT_RET_OK) {
                                        $error = MTRetCode::GetError($errorCode1);
                                        return response()->json([
                                            'success' => false,
                                            'message' => 'Something went wrong',
                                            'error' => $error,
                                        ], 400);
                                    }
                                    return redirect()->back()->with('error', MTRetCode::GetError($error_code3));
                                }

                                $promo->bonus_used += $promo_deduction;
                                $promo->save();
                                $total_promo_deducted += $promo_deduction;

                                // Record the deduction
                                BonusTransaction::create([
                                    'email' => $account->email,
                                    'user_id' => $user_id,
                                    'account_id' => $account->id,
                                    'code' => $account->code,
                                    'bonus_amount' => $deduction,
                                    'bonus_type' => 'Bonus Out',
                                    'status' => 1,
                                    'admin_remark' => 'Promo Deduction',
                                    'bonus_currency' => 'USD',
                                ]);

                                $promo_left -= $promo_deduction;
                                if ($promo->bonus_used <= $promo->bonus_amount) {
                                    break; // All promo used up
                                } else {
                                    $i++; // Move to next promo if more left
                                }
                            } else {
                                // No deduction possible, go to next promo
                                $i++;
                            }
                        } else {
                            // No deduction needed yet
                            break;
                        }
                    } else {
                        // No deduction needed
                        break;
                    }
                }
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
                    'promo_deduction' => $total_promo_deducted
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
                    '<p>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</p>' .
                    '<p></p>' .
                    '<p>You are receiving this email because you have requested a withdrawal of amount $' . $withdrawal_amount . ' from your account ' . $account->code . '</p>' .
                    '<p></p>' .
                    '<p>Click the link below to activate your Account Withdrawal</p>';

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
                return redirect()->route('trade-withdrawal')->with('success', 'Verification email sent successfully.');
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
