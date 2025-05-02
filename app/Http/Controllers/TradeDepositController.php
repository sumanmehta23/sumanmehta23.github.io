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
use App\Models\PaymentLog;
use Illuminate\Support\Facades\Http;

class TradeDepositController extends Controller
{
    protected $api;
    protected $settings;

    public function __construct(MTWebAPI $api)
    {
        $this->settings = settings();
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

        // return view('trade_deposit', compact('liveaccount_details', 'walletenabled', 'bank_details', 'totals','wallet_balance'));
        return view('new_trade_deposit', compact('liveaccount_details', 'walletenabled', 'bank_details', 'totals','wallet_balance'));
    }

    public function sync_amount(Request $request)
    {
        set_time_limit(6000);
        $settings = settings();
        $results = [];
        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );
        $emails = [
            "jenniferteran2493@gmail.com",
            "federicoruizmosquera@gmail.com",
            "alebracamontec@gmail.com",
            "wiltonpayano78@gmail.com",
            "buzz994@gmail.com",
            "ba.rojascastro@gmail.com",
            "luisanhe@gmail.com",
            "danielmontenegroroseiro1988@gmail.com",
            "ar.04tallerarquitectos@gmail.com",
            "barrientosreneau@gmail.com",
            "edicsonvicente@hotmail.com",
            "ronnyfari96@hotmail.com",
            "dany.fermartinez@gmail.com",
            "marioescandon723@gmail.com",
            "rusbelenlinea@gmail.com",
            "asafmc17@gmail.com",
            "jack.santana.perez@gmail.com",
            "heyder1818@gmail.com",
            "ronalrojasflorez25@gmail.com",
            "samuelortizemp@gmail.com",
            "freddylluilema2005@gmail.com",
            "pietropuglisi3@gmail.com",
            "dunnia.trade24@gmail.com",
            "abdelrif31@gmail.com",
            "giullianobp05@hotmail.com",
            "aliciagc@live.com",
            "menendezrios@gmail.com",
            "alejandrofraile101@gmail.com",
            "segdidac@gmail.com",
            "aleexgutierrez2910@gmail.com",
            "edsouzaveras@gmail.com",
            "mintyandfresh@proton.me",
            "alyazegia@gmail.com",
            "konvalte@gmail.com",
            "labhanshsharma199@gmail.com",
            "floritrattfinance@gmail.com",
            "f.hoffmann176@gmail.com",
            "shivammorya0011@gmail.com",
            "dani.chmeit.dc@gmail.com",
            "vabalieras@gmail.com",
            "mpak66@gmail.com",
            "kudriasjovalex@gmail.com",
            "lukasbdv@gmail.com",
            "hamed_javadi20@hotmail.com",
            "wicksrus100@gmail.com",
            "jobasohan20@yahoo.com",
            "nathalynoy@gmail.com",
            "allengarcia73@icloud.com",
            "mia@rhodesoffice.co.uk",
            "chahinthereal@gmail.com",
            "drieslambrechts4@gmail.com",
            "neftaly.000@gmail.com",
            "giron2695@gmail.com",
            "rodrigo92royg@gmail.com",
            "carldingo@gmail.com",
            "adid48@hotmail.com",
            "pfelipefly@gmail.com",
            "torrma@hotmail.com",
            "gdc10218@gmail.com",
            "kurikirimfura@gmail.com",
            "salemfrancis09@gmail.com",
            "ahmedabdulahi343@gmail.com",
            "yogesh21ahir@gmail.com",
            "kyleffx10@gmail.com",
            "guraliuciustin9@gmail.com",
            "nathan@ast.co.uk"
        ];
        $amounts = [
            1, 9.08, 5.8, 3.3, 2.02, 9.72, 5.67, 1.13, 12.83, 1.05, 4.83, 13.95, 9.27, 1.35, 8.98, 5.28, 17.2, 3, 2.08, 5.06, 6.93, 16.94, 8.92, 1.26, 2.99, 4.6, 7.58, 2.26, 16.3, 3.5, 8.62, 1.04, 1, 1.598256, 19, 9.979934, 3.117548, 13, 1, 3, 9.05, 9.9, 2.022938, 9.29, 1.5, 10.1, 1.36, 1.65, 2.37, 4.79, 8.53, 1.19, 2.57, 13.21, 10.6, 1.040833, 1.98, 5.39, 4.75, 15.42, 4.02, 7.47, 9.04, 5.27, 8.94, 3.499001
        ];

        $user_ids = [];
        $accounts_code = [];

        foreach ($emails as $email) {

            $user = User::where('email', $email)->first();
            if ($user) {
                $user_ids[] = $user->id;
            }

            $accounts = Account::where('email', $email)->where('demo',0)->get();
            $foundValidAccount = false;

            foreach ($accounts as $account) {
                $login = $account->code;

                $error_code = $this->api->UserAccountGet($login, $mt5account);
                if ($error_code != MTRetCode::MT_RET_OK) {
                    session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
                    continue;
                }

                if ($mt5account->Balance >= 0) {
                    $accounts_code[] = $account->code;
                    $foundValidAccount = true;
                    break; // Stop checking other accounts once a valid one is found
                }
            }

            if (!$foundValidAccount) {
                $accounts_code[] = null; // No valid account with non-negative balance
            }
        }

        // dump($emails);
        dump($user_ids);
        dump($amounts);
        dump($accounts_code);
        dd('fffffffff');

        foreach ($user_ids as $index => $user_id) {
            if ($user_id !== null) {
                $amount = $amounts[$index];
                $account = $accounts_code[$index];

                // return redirect()->route('trade_deposit_manually', [
                //     'user_id' => $user_id,
                //     'amount' => $amount,
                //     'account' => $account
                // ])->with('status', 'Manual deposit triggered successfully.');

                $settings = settings();


                $user = User::findOrFail($user_id);
                $depositamount = $amount;
                $email = $user->email;
                $account = Account::where('user_id', $user->id)->where('code', $account)->firstOrFail();

                $deposit_type = 'Wallet Transfer';
                $deposit_from = NULL;

                $comment = "Deposit";
                $ticket = NULL;

                $depositProofPath = null;

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
                    // return response()->json(['success' => 'Funds Successfully Deposited']);
                }

            }
        }
    }

    public function deposit_manually(Request $request,  $user_id, $amount, $account)
    {
        // Check the values coming from the route

        $settings = settings();

        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );

        $user = User::findOrFail($user_id);
        $depositamount = $amount;
        $email = $user->email;
        $account = Account::where('user_id', $user->id)->where('code', $account)->firstOrFail();

        $deposit_type = 'Wallet Transfer';
        $deposit_from = NULL;

        $comment = "Deposit";
        $ticket = NULL;

        $depositProofPath = null;

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
            // return response()->json(['success' => 'Funds Successfully Deposited']);
        }

        // return back()->with('success', 'Deposit added successfully.');
    }



    public function deposit(Request $request)
    {
        $request->validate(
            [
                'confirmcryptoCheckbox' => [
                    'required' // Ensures this checkbox is checked
                ],
            ],
            [
                'confirmcryptoCheckbox.required' => 'The correct wallet address and network confirmation checkbox is required.',
            ]
        );
        $user = auth()->user();

        try {
            $trading_deposited1 = $request->input('deposit');
            $deposit_type = $request->input('deposit_type');

            if ($deposit_type == "CreditCardPayissa") {
                $data = [
                    "payment_amount" => $trading_deposited1,
                    "payment_type" => "CreditCardPayissa",
                    "payment_reference_id" => "Wallet",
                    "user_id" => $user->id,
                    "payment_status" => "Initiated",
                    "initiated_by" => $user->email,
                    "account_id" => $request['user']['code']
                ];

                $paymentLog = PaymentLog::create($data);
                $orderId = 'ccPayissa' . $paymentLog->id;
                $currency = 'USD';
                $payment = $this->createCCPayment($trading_deposited1, $currency, $orderId, $paymentLog->id);
                if ($payment) {
                    return redirect($payment['invoice_url']);
                } else {
                    return redirect()->back()->with('error', 'Something went wrong in NowPayment. Please try again other Payment methods or try again later.');
                }
            } elseif ($deposit_type == "Now Payment") {
                $data = [
                    "payment_amount" => $trading_deposited1,
                    "payment_type" => "NowPayment",
                    "user_id" => $user->id,
                    "payment_reference_id" => "Wallet",
                    "payment_status" => "Initiated",
                    "initiated_by" => $user->email
                ];
                $paymentLog = PaymentLog::create($data);
                $orderId = 'nowPay' . $paymentLog->id;
                $currency = 'USD';
                $payment = $this->createPayment($trading_deposited1, $currency, $orderId, $paymentLog->payment_id);
                if ($payment) {
                    return redirect($payment['invoice_url']);
                } else {
                    return redirect()->back()->with('error', 'Something went wrong in NowPayment. Please try again other Payment methods or try again later.');
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            return redirect()->back()->with('error', $error);
        }

        // // Generate a unique rate-limiting key based on user or IP
        // $key = 'deposit:' . (auth()->id() ?: $request->ip());

        // // Check if the user has exceeded the rate limit
        // if (RateLimiter::tooManyAttempts($key, 1)) {
        //     $retryAfter = RateLimiter::availableIn($key);
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Too many requests',
        //         'error' => "Please wait {$retryAfter} seconds before trying again.",
        //     ], 429); // HTTP 429 Too Many Requests
        // }

        // // Increment the rate limiter
        // RateLimiter::hit($key, 10); // Lock for 10 seconds
        // dd($request->all());
        // $request->validate(
        //     [
        //         'user.account_id' => 'required',
        //         'user.deposit' => 'required|numeric',
        //         'user.deposit_type' => 'required',
        //         'deposit_proof' => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:2048',
        //     ],
        //     [
        //         'user.account_id.required' => 'You have to select an account to proceed.'
        //     ]
        // );
        // $settings = settings();

        // $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        // $this->api->Connect(
        //     $settings['mt5_server_ip'],
        //     $settings['mt5_server_port'],
        //     300,
        //     $settings['mt5_server_web_login'],
        //     $settings['mt5_server_web_password']
        // );

        // $depositdata = $request->input('user');
        // $email = session('clogin');
        // $depositamount = $depositdata['deposit'];
        // $email = $depositdata['email'];
        // $account_id = $depositdata['account_id'];
        // $user=auth()->user();
        // $account = Account::where('user_id', $user->id)->where('id', $account_id)->firstOrFail();
        // $deposit_type = $depositdata['deposit_type']??$request['user']['deposit_type'];
        // $deposit_from = NULL;

        // dd($account);

        // $comment = "Deposit";
        // $ticket = NULL;

        // $depositProofPath = null;
        // if ($request->hasFile('deposit_proof')) {
        //     $depositProofPath = $request->file('deposit_proof')->store('deposit_proofs', 'public');
        // }
        // activity()->causedBy($user->id)
        //     ->withProperties(
        //         [
        //             'ip' => $request->ip(),
        //             'email' => $user->email,
        //             'code' => $account->code,
        //             'deposit_amount' => $depositamount,
        //             'remark' => 'Account Deposit'
        //         ])
        // ->event('create')
        // ->log('Account Deposit');
        // $errorCode = $this->api->TradeBalance($account->code, $type = MTEnDealAction::DEAL_BALANCE, $depositamount, $comment, $ticket, $margin_check=true);

        // if ($errorCode != MTRetCode::MT_RET_OK) {
        //     $error = MTRetCode::GetError($errorCode);
        //     // Return a JSON response with the error
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Something went wrong',
        //         'error' => $error,
        //     ], 400); // 400 Bad Request
        // } else {

        //     // Start a database transaction
        //     DB::transaction(function () use ($user, $email,$account, $depositProofPath,$depositamount,$deposit_type) {
        //         $tradeId = $account->code;

        //         // Insert into wallet withdraw
        //         WalletWithdraw::create([
        //             'user_id' => $user->id,
        //             'email' => $email,
        //             'withdraw_amount' => $depositamount,
        //             'withdraw_type' => "Internal Transfer",
        //             'transaction_id' => $tradeId,
        //             'status' => 1,
        //         ]);
        //         // Insert into total balance
        //         TotalBalance::create([
        //             'user_id' => $user->id,
        //             'account_id' => $account->id,
        //             'email' => $email,
        //             'withdraw_amount' => $depositamount,
        //             'status' => 1,
        //         ]);
        //         // Insert into trade deposit
        //         TradeDeposit::create([
        //             'user_id' => $user->id,
        //             'account_id' => $account->id,
        //             'email' => $email,
        //             'code' => $tradeId,
        //             'deposit_amount' => $depositamount,
        //             'deposit_type' => $deposit_type,
        //             'deposit_from' => ($deposit_type == 'CRM') ? 'CRM' : $deposit_type,
        //             'deposit_proof' => $depositProofPath,
        //             'status' => 1,
        //         ]);
        //     });
        //     AccountHelper::updateLiveAndDemoAccounts();
        //     // RateLimiter::clear($key);
        //     return response()->json(['success' => 'Funds Successfully Deposited']);
        // }
    }


    private function createCCPayment($amount, $currency, $orderId, $paymentId)
    {
        $user = auth()->user();
        $success_url = $this->settings['copyright_site_name_text'] . "/payment-response?amount=" . $amount . "&payment_id=" . $paymentId . "&status=success";
        $cancel_url = $this->settings['copyright_site_name_text'] . "/payment-response?amount=" . $amount . "&payment_id=" . $paymentId . "&status=cancel";
        $url = config("services.payissa.url") . '/control/wallet.php?address=' . config("services.payissa.address") . '&callback=' . urlencode($success_url);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get($url);
        if ($response->successful()) {

            $responsdata = $response->json();
            // Log::channel("creditcardpayissa")->info("Payment link response ".json_encode($responsdata));
            PaymentLog::where('id', $paymentId)->update([
                'payment_req' => json_encode($responsdata),
                'payment_url' => $responsdata['ipn_token'],
                'remarks' => $success_url,
            ]);
            $amount += (4 / 100) * $amount;
            $url = config("services.payissa.checkouturl") . '/process-payment.php?address=' . $responsdata['address_in'] . "&amount=" . $amount . "&provider=wert&email=" . $user->email . "&currency=" . $currency;

            return ['invoice_url' => $url];
        }
        return null;
    }
    private function createPayment($amount, $currency, $orderId, $paymentId)
    {
        $success_url = $this->settings['copyright_site_name_text'] . "/payment-response?amount=" . $amount . "&payment_id=" . $paymentId . "&status=success";
        $cancel_url = $this->settings['copyright_site_name_text'] . "/payment-response?amount=" . $amount . "&payment_id=" . $paymentId . "&status=cancel";
        $url = 'https://api.nowpayments.io/v1/invoice';
        $data = [
            'price_amount' => $amount,
            'price_currency' => $currency,
            'order_id' => $orderId,
            'success_url' => $success_url,
            'ipn_callback_url' => $success_url . "&forceToLoad=true",
            'cancel_url' => $cancel_url,
        ];
        $apiKey = $this->settings['now_payment_api_key'];
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-api-key' => $apiKey,
        ])->post($url, $data);
        if ($response->successful()) {
            PaymentLog::where('payment_id', $paymentId)->update([
                'payment_req' => json_encode($data),
                'payment_url' => $response['invoice_url'],
                'remarks' => $success_url,
            ]);
            return $response->json();
        }
        return null;
    }
}
