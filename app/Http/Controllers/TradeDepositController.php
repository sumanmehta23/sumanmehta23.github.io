<?php

namespace App\Http\Controllers;

use App\Models\User;
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
use App\Services\UniversalMT5Service;
use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Facades\Location;

class TradeDepositController extends Controller
{
    protected $api;
    protected $settings;
    protected $mt5Service;

    public function __construct()
    {
        $this->settings = settings();
        // MT5 service will be initialized on demand to avoid startup hangs
    }

    /**
     * Ensure MT5 connection is established
     */
    private function ensureMT5Connection(): bool
    {
        if (!$this->api) {
            // Initialize MT5 service on demand to avoid startup hangs
            if (!$this->mt5Service) {
                $this->mt5Service = app(UniversalMT5Service::class);
            }

            if (!$this->mt5Service->connect()) {
                Log::error('Failed to connect to MT5 via pool.');
                return false;
            }
            $this->api = $this->mt5Service->getApi();
        }
        return $this->api !== null;
    }

    public function index(Request $request)
    {
        $email = auth()->user()->email;
        $user = auth()->user();
        AccountHelper::updateLiveAndDemoAccounts(auth()->user()->id, $this->api);
        // $liveaccount_details =auth()->user()->liveAccounts;
        //Get live accounts along with number of tradeDeposits with status=1 and 'callback_code' => "success"

        $liveaccount_details = Account::with([
            'accountType',

            'BonusTransaction' => function ($query) {
                $query->where('bonus_type', 'Bonus In')
                    ->orWhere('bonus_type', 'Bonus Out');
            }
        ])->withCount(['tradeDeposits as successful_trade_deposits_count' => function ($query) {
            $query->where('status', 1);
        }])
            ->where('user_id', $user->id)
            ->where('account_request_status', 1)
            ->where('demo', false)
            ->where(function($query) {
                $query->whereNull('created_from')
                      ->orWhere('created_from', '!=', 'zapier');
            })
            ->get()
            ->reject(function ($account) {
                // return $account->accountType->ac_group === 'LM\\B-Book\\10x\\DF-B' && $account->successful_trade_deposits_count > 0;
            });

        $walletenabled = User::where('id', $user->id)->value('wallet_enabled') ?? false;
        $bank_details = ClientBankDetail::where('user_id', $user->id)->first() ?? [];
        $totals = Account::where('user_id', $user->id)
            ->where('demo', false)
            ->selectRaw('SUM(equity) as equity, SUM(credit) as credit, SUM(balance) as balance')
            ->first();
        $totalWd = WalletDeposit::where('user_id', $user->id)->where('status', 1)->sum('deposit_amount');
        $totalWw = WalletWithdraw::where('user_id', $user->id)->whereNotIn('status', [2, 3])->sum('withdraw_amount');
        $totalWwf = WalletWithdraw::where('user_id', $user->id)->whereNotIn('status', [2, 3])->sum('withdraw_transaction_fee');
        $wallet_balance = round($totalWd - ($totalWw + $totalWwf), 2);

        // Check if user is from UK based on IP
        $userIp = $request->ip();
        $isUkUser = false;

        try {
            $location = Location::get($userIp);
            if ($location && $location->countryCode === 'GB') {
                $isUkUser = true;
            }
        } catch (\Exception $e) {
            // Log error but don't break the page
            Log::info('Location detection failed for IP: ' . $userIp . ' - ' . $e->getMessage());
        }
        // return view('trade_deposit', compact('liveaccount_details', 'walletenabled', 'bank_details', 'totals','wallet_balance'));
        return view('new_trade_deposit', compact('liveaccount_details', 'walletenabled', 'bank_details', 'totals', 'wallet_balance', 'user', 'isUkUser'));
    }

    public function sync_amount(Request $request)
    {
        set_time_limit(6000);

        if (!$this->ensureMT5Connection()) {
            return response()->json(['error' => 'MT5 connection failed'], 500);
        }

        $results = [];
        $emails = [];
        $amounts = [];

        $user_ids = [];
        $accounts_code = [];

        $batchSize = 5;
        $emailChunks = array_chunk($emails, $batchSize);

        foreach ($emailChunks as $emailChunk) {
            foreach ($emailChunk as $email) {

                $user = User::where('email', $email)->first();
                if ($user) {
                    $user_ids[] = $user->id;
                }

                $accounts = Account::where('email', $email)->where('demo', 0)->get();
                $foundValidAccount = false;

                foreach ($accounts as $account) {
                    $login = $account->code;
                    $mt5account = null;

                    $error_code = $this->mt5Service->userAccountGet($login, $mt5account);
                    if ($error_code != MTRetCode::MT_RET_OK) {
                        session()->flash('error', 'MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
                        continue;
                    }

                    if ($mt5account && $mt5account->Balance >= 0) {
                        $accounts_code[] = $account->code;
                        $foundValidAccount = true;
                        break; // Stop checking other accounts once a valid one is found
                    }
                }

                if (!$foundValidAccount) {
                    $accounts_code[] = null; // No valid account with non-negative balance
                }
            }

            // Process the batch here if needed
            // For example, you can add a delay or log the batch processing
            // sleep(1); // Uncomment this line if you want to add a delay between batches
        }



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
                        ]
                    )
                    ->event('create')
                    ->log('Account Deposit');
                $errorCode = $this->mt5Service->tradeBalance($account->code, $type = MTEnDealAction::DEAL_BALANCE, $depositamount, $comment, $ticket, $margin_check = true);

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
                    DB::transaction(function () use ($user, $email, $account, $depositProofPath, $depositamount, $deposit_type) {
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

        if (!$this->ensureMT5Connection()) {
            return response()->json(['error' => 'MT5 connection failed'], 500);
        }

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
                ]
            )
            ->event('create')
            ->log('Account Deposit');
        $errorCode = $this->mt5Service->tradeBalance($account->code, $type = MTEnDealAction::DEAL_BALANCE, $depositamount, $comment, $ticket, $margin_check = true);

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
            DB::transaction(function () use ($user, $email, $account, $depositProofPath, $depositamount, $deposit_type) {
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



    // TODO: Integrate RagaPay service endpoint for payment processing
    public function deposit(Request $request)
    {
        $request->validate(
            [
                'confirmcryptoCheckbox' => [
                    'required' // Ensures this checkbox is checked
                ],
                'confirmusdcCheckbox' => [
                    'required' // Ensures this checkbox is checked
                ],
                'user.code' => [
                    'required' // Ensures 'code' exists and is a valid UUID
                ],
            ],
            [
                'confirmcryptoCheckbox.required' => 'The correct wallet address and network confirmation checkbox is required.',
                'confirmusdcCheckbox.required' => 'The UDSC confirmation checkbox is required.',
                'user.code.required' => 'Please select account.',
            ]
        );
        $user = auth()->user();
        $promocode = $request->cc_promocode ?? '';
        try {
            $trading_deposited1 = $request->input('deposit');
            $deposit_type = $request->input('deposit_type');

            if ($deposit_type == "RagaPay") {
                $promocode = $request->raga_promocode ?? '';
                $data = [
                    "payment_amount" => $trading_deposited1,
                    "payment_type" => "RagaPay",
                    "payment_reference_id" => "TradeAccount", // Changed from "Wallet" to "TradeAccount"
                    "user_id" => $user->id,
                    "payment_status" => "Initiated",
                    "initiated_by" => $user->email,
                    "account_id" => $request['user']['code'],
                    "promocode" => $promocode,
                ];
                $paymentLog = PaymentLog::create($data);
                $orderId = 'ragaPay' . $paymentLog->id;
                $currency = 'USD';
                $payment = $this->createRagaPayment($trading_deposited1, $currency, $orderId, $paymentLog->id, $promocode);
                if ($payment) {
                    return redirect()->away($payment['invoice_url']);
                } else {
                    return redirect()->back()->with('error', 'Something went wrong with RagaPay. Please try again or use other payment methods.');
                }
            } elseif ($deposit_type == "CreditCardPayissa") {
                $data = [
                    "payment_amount" => $trading_deposited1,
                    "payment_type" => "CreditCardPayissa",
                    "payment_reference_id" => "TradeAccount", // Changed from "Wallet" to "TradeAccount"
                    "user_id" => $user->id,
                    "payment_status" => "Initiated",
                    "initiated_by" => $user->email,
                    "account_id" => $request['user']['code'],
                    "promocode" => $promocode,
                ];
                $paymentLog = PaymentLog::create($data);
                $orderId = 'ccPayissa' . $paymentLog->id;
                $currency = 'USD';
                $payment = $this->createCCPayment($trading_deposited1, $currency, $orderId, $paymentLog->id, $promocode);
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
                $payment = $this->createPayment($trading_deposited1, $currency, $orderId, $paymentLog->payment_id, $promocode);
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


    // Integrate RagaPay service endpoint for payment processing
    private function createRagaPayment($amount, $currency, $orderId, $paymentId, $promocode)
    {
        $user = auth()->user();
        // Local callback URLs for testing
        $success_url = $this->settings['copyright_site_name_text'] . "/payment-response?amount=" . $amount . "&payment_id=" . $paymentId . "&status=success";
        $cancel_url = $this->settings['copyright_site_name_text'] . "/payment-response?amount=" . $amount . "&payment_id=" . $paymentId . "&status=cancel";
        $error_url = $this->settings['copyright_site_name_text'] . "/payment-response?amount=" . $amount . "&payment_id=" . $paymentId . "&status=error";

        try {
            $ragaPayService = app(\App\Services\RagaPayService::class);

            // Prepare order data for RagaPay
            $order = [
                'number' => $orderId,
                'amount' => number_format((float) $amount, 2, '.', ''),
                'currency' => $currency,
                'description' => 'Trading Account Deposit - ' . $orderId,
            ];

            // Prepare customer data for RagaPay
            $customer = [
                'first_name' => $user->firstname ?? 'Customer',
                'last_name' => $user->lastname ?? '',
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'country' => $user->country ?? 'US',
                'city' => $user->city ?? '',
                'address' => $user->address ?? '',
                'zip' => $user->zip ?? '',
            ];

            // Prepare callback URLs
            $urls = [
                'success_url' => $success_url,
                'cancel_url' => $cancel_url,
                'error_url' => $error_url,
            ];

            // Create checkout session with RagaPay
            $response = $ragaPayService->createCheckoutSession($order, $customer, $urls);

            // Log the payment request
            PaymentLog::where('id', $paymentId)->update([
                'payment_req' => json_encode([
                    'order' => $order,
                    'customer' => $customer,
                    'success_url' => $success_url,
                    'cancel_url' => $cancel_url,
                    'error_url' => $error_url,
                ]),
                'payment_url' => $response['redirect_url'] ?? null,
                'remarks' => 'RagaPay payment initiated - Order ID: ' . $orderId,
            ]);

            // Return the redirect URL from RagaPay response
            if (isset($response['redirect_url'])) {
                return [
                    'invoice_url' => $response['redirect_url']
                ];
            }

            Log::error('RagaPay payment creation: No redirect_url in response', ['response' => $response]);
            return null;

        } catch (\Exception $e) {
            Log::error('RagaPay payment creation failed: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'amount' => $amount,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    private function createCCPayment($amount, $currency, $orderId, $paymentId, $promocode)
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
            $url = config("services.payissa.checkouturl") . '/pay.php?address=' . $responsdata['address_in'] . "&amount=" . $amount . "&email=" . $user->email . "&currency=" . $currency;

            return ['invoice_url' => $url];
        }
        return null;
    }
    private function createPayment($amount, $currency, $orderId, $paymentId, $promocode)
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
