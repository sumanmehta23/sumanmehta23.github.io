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

                    "josephfavaleforex@gmail.com",
                    "broker@moneymarketrading.com",
                    "boggysophia@hotmail.com",
                    "vewtrades@icloud.com",
                    "sheridan005jamie@gmail.com",
                    "wilbertalvarez013@gmail.com",
                    "dapo.oladipo@gmail.com",
                    "unemat419@icloud.com",
                    "lin.xin1997@hotmail.com",
                    "diaza2626@gmail.com",
                    "kelmilline7@outlook.com",
                    "fonfonyteam@gmail.com",
                    "ronis5000@hotmail.com",
                    "contact@evsconnect.net",
                    "alex.forextradingtp@gmail.com",
                    "v.grunberg@live.nl",
                    "kenzo.schmit@icloud.com",
                    "armanvirk9@gmail.com",
                    "toneyhrpr@gmail.com",
                    "srujith6@gmail.com",
                    "nasseraljazmi0@gmail.com",
                    "tararsufyan3@gmail.com",
                    "nasimautomation@gmail.com",
                    "gymfreakamit@gmail.com",
                    "anhmoc@hotmail.com",
                    "Chaudhary.robbie@gmail.com",
                    "faria.officiel@outlook.com",
                    "zacharyarmani@hotmail.com",
                    "michaelkouloumos@gmail.com",
                    "mikkirosy@gmail.com",
                    "ptacniktobias@gmail.com",
                    "dassognomathias@gmail.com",
                    "tim.daltonbuilding@hotmail.com",
                    "aryansoni6@icloud.com",
                    "denisjakus@icloud.com",
                    "ckrakul@gmail.com",
                    "Skllznextup@gmail.com",
                    "jake.mcleod00@gmail.com",
                    "tslkonrad@gmail.com",
                    "tristent52@gmail.com",
                    "etonko199@gmail.com",
                    "marius.barkenhuizen@gmail.com",
                    "obuene@gmail.com",
                    "jorgeoffice01@gmail.com",
                    "csavvas2003@gmail.com",
                    "mithuneshbellam29@gmail.com",
                    "jose.andreshernandez@hotmail.com",
                    "jerekr28@gmail.com",
                    "uyangany@gmail.com",
                    "samuelrobertss22@outlook.com",
                    "navi2077@icloud.com",
                    "nkosininowa95@gmail.com",
                    "baryestate@gmail.com",
                    "Akshay.sood.1996@gmail.com",
                    "Edgeychris@gmail.com",
                    "abrahamjose5503@gmail.com",
                    "sebastian.markussen@hotmail.com",
                    "grzegorz.bloniarz@gmail.com",
                    "Taymiller097@gmail.com",
                    "trade.pm207@proton.me",
                    "th.35jayrose@outlook.com",
                    "binamybeng@gmail.com",
                    "drake_dre@hotmail.com",
                    "alexrayon2006@gmail.com",
                    "mrczfx@gmail.com",
                    "danielc2013@yahoo.ca",
                    "filippovoconi@gmail.com",
                    "megafiremedia@gmail.com",
                    "rlbootcamp2024@gmail.com",
                    "elkysime@gmail.com",
                    "lucaseitner04@gmail.com",
                    "zyguy114@gmail.com",
                    "tranphi1031@Gmail.com",
                    "dylan.yancey@proton.me"
        ];
        $amounts = [
            0.19, 0.820111, 0.62, 0.37, 0.54, 0.24, 0.62, 0.07, 0.18, 0.56, 0.146, 0.39, 0.28, 0.08, 0.04, 0.51, 0.16, 0.84, 0.63, 0.8, 0.85, 0.44, 0.0009999999997, 0.65, 0.704037, 0.56, 0.02, 0.23, 0.16, 0.39, 0.72, 0.3, 0.98, 0.725974, 0.56, 0.11, 0.2, 0.16, 0.51, 0.9, 0.6, 0.55, 0.55, 0.9, 0.91, 0.03, 0.99, 0.72, 0.99, 0.52, 0.45, 0.009999999998, 0.83, 0.23, 0.89, 0.6, 0.2, 0.11, 0.91, 0.09, 0.82, 0.53, 0.8, 0.92, 0.991415, 0.55, 0.61, 0.78, 0.05, 0.34, 0.607477, 0.74, 0.05, 0.198
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
        // dd('fffffffff');

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
