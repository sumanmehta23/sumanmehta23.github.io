<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\Promocode;
use App\Models\PaymentLog;
use App\MT5\MTEnDealAction;
use App\Models\TotalBalance;
use App\Models\TradeDeposit;
use Illuminate\Http\Request;
use App\Models\WalletDeposit;
use App\Models\BonusTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PendingManualPayment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use App\Services\UniversalMT5Service;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use App\Events\AccountTradesDepositEvent;
use App\Services\MailService as MailService;

class Payment extends Controller
{
    protected $mailService;
    protected $api;
    protected $mt5Service;
    protected $settings;

    public function __construct(MailService $mailService)
    {
        $this->settings = settings();
        $this->mailService = $mailService;
        // MT5 service will be initialized on demand to avoid startup hangs
        // MT5 connection deferred - use ensureMT5Connection() in methods that need it
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

    // Handle RagaPay and CreditCardPayissa payment callbacks
    public function handlePaymentResponse(Request $request)
    {
        if (!$this->ensureMT5Connection()) {
            Log::error('Failed to connect to MT5 server in handlePaymentResponse');
            return response()->json(['error' => 'MT5 connection failed'], 500);
        }

        $status = $request->input('status');
        $payment_id = $request->input('payment_id');
        $address_in = $request->input('address_in');
        $order_id = $request->input('order_id');
        $responsedata = $request->all();
        response()->json(['status' => 'received'], 200)->send();
        try {
            // For RagaPay, extract our payment ID from order_id (format: ragaPay{uuid})
            if (!empty($order_id) && strpos($order_id, 'ragaPay') === 0) {
                $extractedPaymentId = substr($order_id, 7); // Remove 'ragaPay' prefix
                Log::info('Extracted payment ID from RagaPay order_id', [
                    'order_id' => $order_id,
                    'extracted_payment_id' => $extractedPaymentId
                ]);
                $payment_id = $extractedPaymentId;
            }

            // Check if payment_id is provided
            if (empty($payment_id)) {
                Log::error('Payment ID is missing in callback', ['responsedata' => $responsedata]);
                return redirect('/trade-deposit')->with('error', 'Payment ID is missing. Please contact support.');
            }

            // Get payment log
            $paymentLog = PaymentLog::where('id', $payment_id)->with('user', 'account')->first();
            if (!$paymentLog) {
                Log::error('Invalid Payment ID: ' . $payment_id, ['responsedata' => $responsedata]);
                return redirect('/trade-deposit')->with('error', 'Invalid Payment ID. Please contact support with reference: ' . $payment_id);
            }

            // Handle RagaPay success callback (for Wallet deposits)
            if ($paymentLog->payment_type === 'RagaPay' && $status === 'success' && $paymentLog->payment_reference_id === 'Wallet') {
                Log::info('RagaPay wallet deposit callback: ' . json_encode($responsedata));

                // Get RagaPay transaction ID
                $ragapayTransactionId = $responsedata['trans_id'] ?? $responsedata['payment_id'] ?? $payment_id;

                // Check for duplicate transaction using our payment log ID
                $existingDeposit = WalletDeposit::where('transaction_id', $payment_id)
                    ->orWhere('transaction_id', $ragapayTransactionId)
                    ->first();
                if ($existingDeposit) {
                    Log::channel('ragapay')->info('Wallet deposit already exists for payment ID: ' . $payment_id);
                    return redirect('/trade-deposit')->with('info', 'This deposit has already been processed.');
                }

                // Update payment log
                $paymentLog->update([
                    'payment_res' => json_encode($responsedata),
                    'payment_status' => 'success',
                ]);

                $email = $paymentLog->initiated_by;
                $amount = $paymentLog->payment_amount;
                $userId = $paymentLog->user_id;

                try {
                    DB::beginTransaction();

                    // Create wallet deposit record
                    $walletDeposit = WalletDeposit::create([
                        'user_id' => $userId,
                        'email' => $email,
                        'deposit_type' => 'RagaPay',
                        'deposit_amount' => $amount,
                        'company_bank' => 'RagaPay',
                        'transaction_id' => $payment_id, // Store our payment log ID
                        'status' => 1,
                        'currency_type' => 'USD',
                        'callback_data' => json_encode($responsedata),
                        'callback_code' => 'success',
                    ]);

                    // Update total balance
                    TotalBalance::create([
                        'user_id' => $userId,
                        'email' => $email,
                        'deposit_amount' => $amount
                    ]);

                    DB::commit();

                    // Fire Omnisend event for deposit
                    event(new AccountTradesDepositEvent($paymentLog->user, $amount));
                    Cache::forget("user:{$userId}:wallet_balance");

                    Log::channel('ragapay')->info('RagaPay wallet deposit confirmed successfully for payment ID: ' . $payment_id);

                    // Send success email
                    $this->sendSuccessEmail($email, $amount, $paymentLog, $walletDeposit->id);

                    return redirect('/trade-deposit')->with('success', "Successfully Deposited \$$amount To Your Wallet");
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::channel('ragapay')->error('RagaPay wallet deposit failed: ' . $e->getMessage());
                    return redirect('/trade-deposit')->with('error', 'Something went wrong processing your deposit. Please contact support.');
                }
            }

            // Handle RagaPay success callback (for Trade Account deposits)
            if ($paymentLog->payment_type === 'RagaPay' && $status === 'success' && $paymentLog->payment_reference_id === 'TradeAccount') {
                Log::info('RagaPay trade account deposit callback: ' . json_encode($responsedata));

                // Get RagaPay transaction ID
                $ragapayTransactionId = $responsedata['trans_id'] ?? $responsedata['payment_id'] ?? $payment_id;

                // Check for duplicate transaction
                $existingDeposit = TradeDeposit::where('transaction_id', $payment_id)
                    ->orWhere('transaction_id', $ragapayTransactionId)
                    ->first();
                if ($existingDeposit) {
                    Log::channel('ragapay')->info('Trade deposit already exists for payment ID: ' . $payment_id);
                    return redirect('/trade-deposit')->with('info', 'This deposit has already been processed.');
                }

                // Update payment log
                $paymentLog->update([
                    'payment_res' => json_encode($responsedata),
                    'payment_status' => 'success',
                ]);

                $email = $paymentLog->initiated_by;
                $amount = $paymentLog->payment_amount;
                $userId = $paymentLog->user_id;

                // Get account from payment log
                $account = Account::where('id', $paymentLog->account_id)->withCount(['tradeDeposits as successful_trade_deposits_count' => function ($query) {
                    $query->where('status', 1);
                }])->first();

                if (!$account) {
                    Log::error('Account not found for payment log: ' . $payment_id);
                    return redirect('/trade-deposit')->with('error', 'Account not found. Please contact support.');
                }

                try {
                    // Credit the MT5 account
                    $comment = 'RagaPay';
                    $ticket3 = NULL;
                    $errorCode3 = $this->mt5Service->tradeBalance($account->code, MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket3, true);

                    if ($errorCode3 !== MTRetCode::MT_RET_OK) {
                        $error = MTRetCode::GetError($errorCode3);
                        Log::channel('ragapay')->error('RagaPay trade balance failed: ' . $error);
                        return redirect('/trade-deposit')->with('error', 'Something went wrong processing your deposit. Please contact support.');
                    }

                    DB::beginTransaction();

                    // Handle 10x Trader Leverage for first deposit
                    $ticket1 = NULL;
                    if ($account->accountType->ac_group == 'LM\B-Book\10x\DF-B' && $account->successful_trade_deposits_count == 0) {
                        if ($amount > 250) {
                            $bonusamount = 9 * 250;
                        } else {
                            $bonusamount = 9 * $amount;
                        }

                        if (($error_code1 = $this->mt5Service->tradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $bonusamount, '10x Trader Leverage', $ticket1, true)) === MTRetCode::MT_RET_OK) {
                            // Update leverage
                            $trade_user = null;
                            if (($error_code = $this->mt5Service->userGet($account->code, $trade_user)) == MTRetCode::MT_RET_OK) {
                                if ($trade_user) {
                                    $leverage = round($account->leverage * ($amount / ($trade_user->Balance + $trade_user->Credit)), 2);
                                    $trade_user->Leverage = $leverage;

                                    $updated_user = "";
                                    if (($error_code = $this->mt5Service->userUpdate($trade_user, $updated_user)) != MTRetCode::MT_RET_OK) {
                                        Log::warning("Failed to update leverage: " . MTRetCode::GetError($error_code));
                                    }
                                }
                            }

                            BonusTransaction::create([
                                'email' => $email,
                                'user_id' => $userId,
                                'account_id' => $paymentLog->account_id,
                                'code' => $account->code,
                                'bonus_amount' => $bonusamount,
                                'bonus_type' => 'Bonus In',
                                'status' => 1,
                                'admin_remark' => '10x Trader Leverage',
                                'bonus_currency' => 'USD',
                                'transaction_id' => $payment_id,
                            ]);
                        }
                    }

                    // Prepare trade deposit data
                    $data = [
                        'user_id' => $userId,
                        'account_id' => $paymentLog->account_id,
                        'email' => $email,
                        'code' => $account->code,
                        'deposit_amount' => $amount,
                        'deposit_type' => 'RagaPay',
                        'deposit_from' => 'RagaPay',
                        'status' => 1,
                        'deposit_currency' => 'USD',
                        'transaction_id' => $payment_id,
                        'deposted_date' => now(),
                        'callback_data' => json_encode($responsedata),
                        'callback_code' => 'success',
                    ];

                    // Handle promocode if exists
                    if (isset($paymentLog->promocode) && $paymentLog->promocode != '') {
                        $ticket2 = NULL;
                        $promo = Promocode::where('code', $paymentLog->promocode)->first();
                        if ($promo) {
                            $min_deposit = $promo->min_deposit;
                            if ($amount >= $min_deposit) {
                                if (isset($promo->max_deposit) && $amount >= $promo->max_deposit) {
                                    $bonus_amount = ($promo->promo_percentage / 100) * $promo->max_deposit;
                                } else {
                                    $bonus_amount = ($promo->promo_percentage / 100) * $amount;
                                }

                                if (($error_code2 = $this->mt5Service->tradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $bonus_amount, 'Promo Bonus', $ticket2, true)) === MTRetCode::MT_RET_OK) {
                                    BonusTransaction::create([
                                        'email' => $email,
                                        'user_id' => $userId,
                                        'account_id' => $paymentLog->account_id,
                                        'code' => $account->code,
                                        'bonus_amount' => $bonus_amount,
                                        'bonus_type' => 'Bonus In',
                                        'status' => 1,
                                        'admin_remark' => 'Promo Bonus',
                                        'bonus_currency' => 'USD',
                                        'transaction_id' => $payment_id,
                                        'promocode_id' => $promo->id
                                    ]);

                                    // Update leverage
                                    $trade_user = null;
                                    if (($error_code = $this->mt5Service->userGet($account->code, $trade_user)) == MTRetCode::MT_RET_OK) {
                                        if ($trade_user) {
                                            $leverage = round($account->leverage * ($amount / ($trade_user->Balance + $trade_user->Credit)), 2);
                                            $trade_user->Leverage = $leverage;

                                            $updated_user = "";
                                            if (($error_code = $this->mt5Service->userUpdate($trade_user, $updated_user)) != MTRetCode::MT_RET_OK) {
                                                Log::warning("Failed to update leverage: " . MTRetCode::GetError($error_code));
                                            }
                                        }
                                    }
                                }

                                $data['promocode_percentage'] = $promo->promo_percentage;
                                $data['promocode_code'] = $promo->code;
                            }
                        }
                    } else {
                        $data['promocode_percentage'] = null;
                        $data['promocode_code'] = null;
                    }

                    // Create trade deposit record
                    $tradeDeposit = TradeDeposit::create($data);

                    // Update total balance
                    TotalBalance::create([
                        'email' => $email,
                        'user_id' => $userId,
                        'deposit_amount' => $amount
                    ]);

                    DB::commit();

                    // Fire Omnisend event for deposit
                    event(new AccountTradesDepositEvent($paymentLog->user, $amount));
                    Cache::forget("user:{$userId}:wallet_balance");

                    Log::channel('ragapay')->info('RagaPay trade deposit confirmed successfully for payment ID: ' . $payment_id);

                    // Send success email
                    $this->sendSuccessEmail2($email, $amount, $tradeDeposit);

                    return redirect('/trade-deposit')->with('success', "Successfully Deposited \$$amount To Your Trading Account {$account->code}");
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::channel('ragapay')->error('RagaPay trade deposit failed: ' . $e->getMessage());

                    // Reverse the MT5 balance if database transaction failed
                    try {
                        $reverseAmount = abs((float)$amount) * -1;
                        $comment = 'RagaPay - Error Reversal';
                        $ticket = NULL;
                        $this->mt5Service->tradeBalance($account->code, MTEnDealAction::DEAL_BALANCE, $reverseAmount, $comment, $ticket, true);
                    } catch (Exception $reverseException) {
                        Log::error('Failed to reverse MT5 balance: ' . $reverseException->getMessage());
                    }

                    return redirect('/trade-deposit')->with('error', 'Something went wrong processing your deposit. Please contact support.');
                }
            }

            // Handle RagaPay cancel/error callbacks
            if ($paymentLog->payment_type === 'RagaPay' && in_array($status, ['cancel', 'error'])) {
                Log::channel('ragapay')->info('RagaPay payment ' . $status . ': ' . json_encode($responsedata));

                $paymentLog->update([
                    'payment_res' => json_encode($responsedata),
                    'payment_status' => $status,
                ]);

                $message = $status === 'cancel' ? 'Payment was cancelled.' : 'Payment failed. Please try again.';
                return redirect('/trade-deposit')->with('error', $message);
            }

            // Handle CreditCardPayissa callback (existing logic)
            if (!empty($address_in)) {
                if (!$this->ensureMT5Connection()) {
                    Log::error('Failed to connect to MT5 server in handlePaymentResponse');
                    return response()->json(['error' => 'MT5 connection failed'], 500);
                }

                $transactionId = $responsedata['txid_in'];

                Log::channel("creditcardpayissa")->info('Payment callback Response: ' . json_encode($responsedata));

                $paymentlinkresponse = json_decode($paymentLog->payment_req);
                $validationToken = $paymentlinkresponse->polygon_address_in;
                // && $responsedata['value_coin']==$paymentLog->payment_amount can't compare as it will never be same as intial input
//                if ($responsedata['address_in'] == $validationToken) {
                if ($this->validatePaymentWithPayissa($paymentLog)) {
                    $existingPayment = TradeDeposit::where('transaction_id', $transactionId)->first();
                    if ($existingPayment) {
                        Log::channel("creditcardpayissa")->info('Payment already exists for transaction ID: ' . $transactionId);
                        return ['Payment already exists for transaction ID'];
                    }
                    $validcoins = config("services.payissa.valid_coins");
                    $coinString = strtolower($responsedata['coin']);
                    $matches = array_filter($validcoins, function ($coin) use ($coinString) {
                        return stripos($coinString, $coin) !== false;
                    });
                    if (empty($matches)) {

                        // Store invalid coin payment for manual processing instead of just sending email
                        Log::channel("creditcardpayissa")->info('Invalid coin payment detected: ' . json_encode($responsedata));

                        // Update payment log
                        $paymentLog->update([
                            'payment_res' => json_encode($responsedata),
                            'payment_status' => 'failed',
                        ]);

                        // Try to get USD value using the polygon command
                        $usdValue = null;
                        $polygonResponse = null;

                        try {
                            Artisan::call('polygon:usd', [
                                'hash' => $transactionId,
                                '--price' => null,
                            ]);

                            $output = Artisan::output();
                            $polygonResponse = $output;

                            // Try to parse the JSON output
                            if (preg_match('/\{[\s\S]*\}/', $output, $matches)) {
                                $jsonData = json_decode($matches[0], true);
                                if (isset($jsonData['value_usd_at_time'])) {
                                    $usdValue = $jsonData['value_usd_at_time'];
                                }
                            }
                        } catch (\Exception $e) {
                            Log::channel("creditcardpayissa")->error('Failed to fetch USD value: ' . $e->getMessage());
                        }

                        // Get account information
                        $account = Account::where('id', $paymentLog->account_id)->first();

                        // Create pending manual payment record
                        PendingManualPayment::create([
                            'payment_log_id' => $paymentLog->id,
                            'user_id' => $paymentLog->user_id,
                            'account_id' => $paymentLog->account_id,
                            'email' => $paymentLog->initiated_by,
                            'code' => $account ? $account->code : null,
                            'transaction_id' => $transactionId,
                            'coin' => $responsedata['coin'] ?? null,
                            'coin_amount' => $responsedata['value_coin'] ?? null,
                            'usd_value' => $usdValue,
                            'initial_requested_amount' => $paymentLog->payment_amount,
                            'deposit_date' => now(),
                            'polygon_response' => $polygonResponse,
                            'promocode' => $paymentLog->promocode,
                            'status' => 'pending',
                        ]);
                        //Send admin email that invalid coin was used
                        $settings = settings();
                        $from = $settings['email_from_address'];
                        $emailSubject = $settings['admin_title'] . ' - Invalid Coin Payment';
                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                        $content = '<div>We have detected an invalid coin payment attempt.</div>
                        <div><b>Transaction Details</b></div>
                        <div><b>Coin: </b>' . $responsedata['coin'] . '</div>
                        <div><b>Transaction ID: </b>' . $transactionId . '</div>
                        <div><b>Deposited Date: </b>' . now() . '</div>
                        <div><b>User Email: </b>' . $paymentLog->initiated_by . '</div>
                        <div><b>Address In: </b>' . $address_in . '</div>
                        <div><b>Payment Log: </b>' . $paymentLog . '</div>';
                        $templateVars = [
                            'name' => 'Admin',
                            'site_link' => $settings['copyright_site_name_text'],
                            'email' => $settings['email_from_address'],
                            "content" => $content,
                            "title_right" => "Invalid Coin Payment",
                            "subtitle_right" => "Alert",
                            "btn_text" => "Go To Dashboard",
                        ];
                        $admin_email = config('services.payissa.payment_issue_email');
                        $this->mailService->sendEmail($admin_email, $emailSubject, $headers, '', $templateVars);
                        Log::channel("creditcardpayissa")->info('Invalid coin payment detected: ' . json_encode($responsedata));
                        // Update payment log
                        $paymentLog->update([
                            'payment_res' => json_encode($responsedata),
                            'payment_status' => 'failed',
                        ]);
                        return response()->json(['error' => 'Invalid coin payment'], 400);
                    }
                    $paymentLog->update([
                        'payment_res' => json_encode($responsedata),
                        'payment_status' => 'success',
                    ]);
                    $email = $paymentLog->initiated_by;
                    $amount = $responsedata['value_coin'];

                    // Ensure MT5 connection is available
                    if (!$this->ensureMT5Connection()) {
                        return response()->json(['error' => 'MT5 connection failed'], 500);
                    }

                    // $account = Account::where('id', $paymentLog->account_id)->first();

                    $account = Account::where('id', $paymentLog->account_id)->withCount(['tradeDeposits as successful_trade_deposits_count' => function ($query) {
                        $query->where('status', 1);
                    }])->first();


                    $comment = 'CreditCardPayissa';
                    $ticket3 = NULL;

                    $errorCode3 = $this->mt5Service->tradeBalance($account->code, MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket3, true);

                    $ticket1 = NULL;
                    if ($account->accountType->ac_group == 'LM\B-Book\10x\DF-B' && $account->successful_trade_deposits_count == 0) {
                        $existingTransaction = TradeDeposit::where('transaction_id', $transactionId)->first();
                        if (!$existingTransaction) {
                            if ($amount > 250) {
                                $bonusamount = 9 * 250;
                            } else {
                                $bonusamount = 9 * $amount;
                            }

                            if (($error_code1 = $this->mt5Service->tradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $bonusamount, '10x Trader Leverage', $ticket1, true)) !== MTRetCode::MT_RET_OK) {
                                return redirect()->back()->with('error', MTRetCode::GetError($error_code1));
                            } else {

                                // Updating leverage
                                $trade_user = null;
                                $this->mt5Service->userGet($account->code, $trade_user);
                                if (($error_code = $this->mt5Service->userGet($account->code, $trade_user)) != MTRetCode::MT_RET_OK) {
                                    return redirect()->back()->with('error', 'Something went wrong on Updating leverage' . MTRetCode::GetError($error_code));
                                }

                                if ($trade_user) {
                                    $leverage = round($account->leverage * ($amount / ($trade_user->Balance + $trade_user->Credit)), 2);
                                    $trade_user->Leverage = $leverage;

                                    $updated_user = "";
                                    if (($error_code = $this->mt5Service->userUpdate($trade_user, $updated_user)) != MTRetCode::MT_RET_OK) {
                                        return redirect()->back()->with("error", "Something went wrong on Updating leverage" . MTRetCode::GetError($error_code));
                                    }
                                }

                                $deposit_details = BonusTransaction::create([
                                    'email' => $email,
                                    'user_id' => $paymentLog->user_id,
                                    'account_id' => $paymentLog->account_id,
                                    'code' => $account->code,
                                    'bonus_amount' => $bonusamount,
                                    'bonus_type' => 'Bonus In',
                                    'status' => 1,
                                    'admin_remark' => '10x Trader Leverage',
                                    'bonus_currency' => 'USD',
                                    'transaction_id' => $transactionId,
                                ]);
                            }
                        }
                    }


                    if ($errorCode3 != MTRetCode::MT_RET_OK) {
                        $error = MTRetCode::GetError($errorCode3);
                        Log::channel("CreditCardPayissa")->info('Something went wrong: ' . json_encode($paymentLog));
                        return response()->json([
                            'success' => false,
                            'message' => 'Something went wrong',
                            'error' => $error,
                        ], 200);
                    } else {
                        try {
                            DB::beginTransaction();
                            $data = [
                                'user_id' => $paymentLog->user_id,
                                'account_id' => $paymentLog->account_id,
                                'email' => $email,
                                'code' => $account->code,
                                'deposit_amount' => $amount,
                                'deposit_type' => 'CreditCardPayissa',
                                'deposit_from' => 'CreditCardPayissa',
                                'status' => 1,
                                'deposit_currency' => 'USD',
                                'transaction_id' => $transactionId,
                                'deposted_date' => now(),
                                'callback_data' => json_encode($responsedata),
                                'callback_code' => "success",
                            ];

                            if (isset($promo) && isset($promo->promo_percentage) && $promo->promo_percentage > 0) {
                                $data['promocode_percentage'] = $promo->promo_percentage;
                                $data['promocode_code'] = $promo->code;
                            } else {
                                $data['promocode_percentage'] = null;
                                $data['promocode_code'] = null;
                            }

                            $tradeDeposit = TradeDeposit::create($data);

                            // Fire the AccountTradesDepositEvent for Omnisend integration
                            event(new AccountTradesDepositEvent($paymentLog->user, $amount));

                            if (isset($paymentLog->promocode) && $paymentLog->promocode != '') {
                                $ticket2 = NULL;
                                $promo = Promocode::where('code', $paymentLog->promocode)->first();
                                if ($promo) {
                                    $min_depsoit = $promo->min_deposit;
                                    if ($promo && $amount >= $min_depsoit) {
                                        if (isset($promo->max_deposit) && $amount >= $promo->max_deposit) {
                                            $bonus_amount = ($promo->promo_percentage / 100) * $promo->max_deposit;
                                        } else {
                                            $bonus_amount = ($promo->promo_percentage / 100) * $amount;
                                        }
                                        if ($promo) {
                                            if (($error_code2 = $this->mt5Service->tradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $bonus_amount, 'Promo Bonus', $ticket2, true)) !== MTRetCode::MT_RET_OK) {
                                                return redirect()->back()->with('error', MTRetCode::GetError($error_code2));
                                            } else {

                                                BonusTransaction::create([
                                                    'email' => $email,
                                                    'user_id' => $paymentLog->user_id,
                                                    'account_id' => $paymentLog->account_id,
                                                    'code' => $account->code,
                                                    'bonus_amount' => $bonus_amount,
                                                    'bonus_type' => 'Bonus In',
                                                    'status' => 1,
                                                    'admin_remark' => 'Promo Bonus',
                                                    'bonus_currency' => 'USD',
                                                    'transaction_id' => $transactionId,
                                                    'promocode_id' => $promo->id
                                                ]);

                                                // Updating leverage
                                                $trade_user = null;
                                                $this->mt5Service->userGet($account->code, $trade_user);
                                                if (($error_code = $this->mt5Service->userGet($account->code, $trade_user)) != MTRetCode::MT_RET_OK) {
                                                    return redirect()->back()->with('error', 'Something went wrong on Updating leverage' . MTRetCode::GetError($error_code));
                                                }

                                                if ($trade_user) {
                                                    $leverage = round($account->leverage * ($amount / ($trade_user->Balance + $trade_user->Credit)), 2);
                                                    $trade_user->Leverage = $leverage;

                                                    $updated_user = "";
                                                    if (($error_code = $this->mt5Service->userUpdate($trade_user, $updated_user)) != MTRetCode::MT_RET_OK) {
                                                        return redirect()->back()->with("error", "Something went wrong on Updating leverage" . MTRetCode::GetError($error_code));
                                                    }
                                                }
                                                // Updating leverage
                                            }
                                        }
                                    }
                                }
                            }

                            // Update total balance
                            TotalBalance::create(
                                ['email' => $email, 'user_id' => $paymentLog->user_id, 'deposit_amount' => $amount]
                            );

                            DB::commit();
                            // Fire Omnisend event for deposit
                            event(new AccountTradesDepositEvent($paymentLog->user, $amount));
                            Cache::forget("user:{$paymentLog->user_id}:wallet_balance");
                            Log::channel("creditcardpayissa")->info('Transaction confirmed successfully.');
                            // $this->sendSuccessEmail($email, $amount, $paymentLog,$walletDeposit->id);
                            $this->sendSuccessEmail($email, $amount, $paymentLog, $tradeDeposit->id);
                            return response()->json(['status' => 'true']);
                        } catch (Exception $e) {
                            DB::rollBack();
                            Log::channel("creditcardpayissa")->error('Transaction failed: ' . $e->getMessage());
                            $amount = abs((float)$amount) * -1;
                            $comment = 'CreditCardPayissa - Error';
                            $errorCode3 = $this->mt5Service->tradeBalance($account->code, MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket3, true);
                            // return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
                        }
                    }
                }

                return ["ok"];
                // return redirect('/wallet_deposit')->with('error', "Payment in progress: We are processing your payment request. Please wait for a while.");
            }

            // If we reach here, no handler matched - return a helpful error
            Log::warning('No payment handler matched', [
                'payment_type' => $paymentLog->payment_type ?? 'unknown',
                'status' => $status,
                'has_address' => !empty($address_in),
                'payment_reference_id' => $paymentLog->payment_reference_id ?? 'unknown',
                'responsedata' => $responsedata
            ]);

            return redirect('/trade-deposit')->with('error', 'Payment callback received but could not be processed. Payment type: ' . ($paymentLog->payment_type ?? 'unknown') . '. Please contact support with reference: ' . $payment_id);

            // else {

            //     $payment_res = json_encode($request->all());
            //     $paymentLog = PaymentLog::where(DB::raw('payment_id'), $payment_id)->with('user')->first();
            //     $account = Account::where('id', $paymentLog->account_id)->first();
            //     $existingTransaction = TradeDeposit::where('transaction_id', $transactionId)->first();
            //     if ($status == "success" && $account && !$existingTransaction) {
            //         // Get the payment log
            //         if ($paymentLog && strtolower($paymentLog->payment_status) != "success") {
            //             // Update payment log
            //             $paymentLog->update([
            //                 'payment_res' => $payment_res,
            //                 'payment_status' => $status,
            //             ]);
            //             $email = $paymentLog->initiated_by;
            //             $amount = $paymentLog->payment_amount;
            //             // Create a new wallet deposit
            //             // $walletDeposit = WalletDeposit::create([
            //             //     'email' => $email,
            //             //     'deposit_amount' => $amount,
            //             //     'deposit_type' => "Now Payment",
            //             //     'currency_type' => "USD",
            //             //     'status' => 1,
            //             // ]);
            //             $errorCode = $this->api->TradeBalance($account->code, $typed = MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, $margin_check = true);
            //             if ($errorCode != MTRetCode::MT_RET_OK) {
            //                 $error = MTRetCode::GetError($errorCode);
            //                 return response()->json([
            //                     'success' => false,
            //                     'message' => 'Something went wrong',
            //                     'error' => $error,
            //                 ], 400);
            //             } else {
            //                 $tradeDeposit = TradeDeposit::create([
            //                     'user_id' => $paymentLog->user_id,
            //                     'account_id' => $paymentLog->account_id,
            //                     'email' => $email,
            //                     'code' => $account->code,
            //                     'deposit_amount' => $amount,
            //                     'deposit_type' => 'CreditCardPayissa',
            //                     'deposit_from' => 'CreditCardPayissa',
            //                     'status' => 1,
            //                     'deposit_currency' => 'USD',
            //                     'transaction_id' => $transactionId,
            //                     'deposted_date' => now(),
            //                     'callback_data' => json_encode($responsedata),
            //                     'callback_code' => "success",
            //                 ]);
            //             }

            //             if ($tradeDeposit) {
            //                 $this->sendSuccessEmail($email, $amount, $paymentLog, $tradeDeposit->id);
            //                 $this->subscribeToKlaviyoList($paymentLog->user, $amount, $subscribeToKlaviyoList);
            //                 return redirect('/trade-deposit')->with('success', "Successfully Deposited \$$amount To Your Wallet");
            //             } else {
            //                 return redirect('/trade-deposit')->with('error', "Something went wrong. Please Try Again");
            //             }
            //         } else {
            //             return redirect('trade-deposit')->with('error', "Payment already processed or invalid.");
            //         }
            //     } else {
            //         // Update payment log for failed payment
            //         $paymentLog->update([
            //             'payment_res' => $payment_res,
            //             'payment_status' => $status,
            //         ]);
            //         return redirect('/trade-deposit')->with('error', "Payment Failed: Something Went Wrong. Please try again");
            //     }
            // }
        } catch (Exception $e) {
            Log::channel("creditcardpayissa")->error('Payment processing error: ' . $e->getMessage());
            Log::channel("creditcardpayissa")->error($e->getTraceAsString());
            return response()->json(['error' => 'Internal server error'], 200);
        }
    }


    public function handleFailedPaymentResponse(Request $request)
    {

        $filePath = storage_path('logs/callback_errors_export.csv');
        if (!file_exists($filePath)) {
            return response("File not found", 404);
        }
        $file = fopen($filePath, 'r');
        if ($file === false) {
            return response("Failed to open file", 500);
        }
        $headers = fgetcsv($file);
        $data = [];
        while (($row = fgetcsv($file)) !== false) {
            $rowData = [];
            foreach ($headers as $index => $header) {
                $rowData[$header] = $row[$index];
            }
            $data[] = $rowData;
            $responsedata = json_decode($rowData['Callback Data'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $status = $responsedata['status'];
                $payment_id = $responsedata['payment_id'];
                $address_in = $responsedata['address_in'];
                // $responsedata = $request->all();
                $transactionId = $responsedata['txid_in'];

                if (empty($address_in)) {
                    Log::info('Missing address: ' . $transactionId);
                    continue;
                    // return response()->json(['error' => 'Missing address'], 400);
                }

                // Log::channel("creditcardpayissa")->info('Payment callback Response: ' . json_encode($responsedata));

                $paymentLog = PaymentLog::where('id', $payment_id)->with('user')->first();
                if (!$paymentLog) {
                    Log::info('Invalid Payment ID: ' . $payment_id);
                    continue;
                    // return response()->json(['error' => 'Invalid Payment ID'], 400);
                }

                $paymentlinkresponse = json_decode($paymentLog->payment_req);
                $validationToken = $paymentlinkresponse->polygon_address_in;

                if ($responsedata['address_in'] != $validationToken) {
                    Log::info('Address mismatch for transaction ID: ' . $responsedata['address_in']);
                    continue;
                }

                // Check if transaction already exists
                $existingPayment = TradeDeposit::where('transaction_id', $transactionId)->first();
                if ($existingPayment) {
                    // Log::channel("creditcardpayissa")->info('Payment already exists for transaction ID: ' . $transactionId);
                    Log::info("already_processed " . $transactionId);
                    continue;
                    // return response()->json(['status' => 'already_processed']);
                }

                // Check valid coin
                $validcoins = config("services.payissa.valid_coins");
                $coinString = strtolower($responsedata['coin']);
                $matches = array_filter($validcoins, function ($coin) use ($coinString) {
                    return stripos($coinString, $coin) !== false;
                });



                $email = $paymentLog->initiated_by;
                $amount = $responsedata['value_coin'];


                $account = Account::where('id', $paymentLog->account_id)->first();

                try {
                    DB::beginTransaction();

                    $data = [
                        'user_id' => $paymentLog->user_id,
                        'account_id' => $paymentLog->account_id,
                        'email' => $email,
                        'code' => $account->code,
                        'deposit_amount' => $amount,
                        'deposit_type' => 'CreditCardPayissa',
                        'deposit_from' => 'CreditCardPayissa',
                        'status' => 1,
                        'deposit_currency' => 'USD',
                        'transaction_id' => $transactionId,
                        'deposted_date' => now(),
                        'callback_data' => json_encode($responsedata),
                        'callback_code' => "success",
                    ];

                    // Optional promo handling
                    if (isset($promo) && isset($promo->promo_percentage) && $promo->promo_percentage > 0) {
                        $data['promocode_percentage'] = $promo->promo_percentage;
                        $data['promocode_code'] = $promo->code;
                    } else {
                        $data['promocode_percentage'] = null;
                        $data['promocode_code'] = null;
                    }

                    $tradeDeposit = TradeDeposit::create($data);

                    // Fire the AccountTradesDepositEvent for Omnisend integration
                    event(new AccountTradesDepositEvent($paymentLog->user, $amount));

                    TotalBalance::create([
                        'email' => $email,
                        'user_id' => $paymentLog->user_id,
                        'deposit_amount' => $amount,
                    ]);

                    DB::commit();

                    // $this->subscribeToKlaviyoList($paymentLog->user, $amount, $subscribeToKlaviyoList);
                    Cache::forget("user:{$paymentLog->user_id}:wallet_balance");

                    Log::channel("creditcardpayissa")->info('Transaction confirmed successfully.');

                    // $this->sendSuccessEmail($email, $amount, $paymentLog, $tradeDeposit->id);

                    // return response()->json(['status' => 'true']);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::channel("creditcardpayissa")->error("Failed to process payment: " . $e->getMessage());
                    // return response()->json(['error' => 'Internal error'], 500);
                }
            } else {
                Log::error("Invalid JSON data for transaction ID: " . $rowData['TxID In']);
            }
            // exit;
        }
        fclose($file);
        return 'ok';
        $responsedata = $request->input('callback_data');
    }


    public function manuallyPaymentResponse(Request $request)
    {

        if (!$this->ensureMT5Connection()) {
            Log::error('Failed to connect to MT5 server in handlePaymentResponse');
            return response()->json(['error' => 'MT5 connection failed'], 500);
        }

        $code = $request->input('code');
        $responsedata = $request->all();
        $transactionId = $responsedata['txid_in'];
        $email = $request->input('email');
        $amount = $request->input('amount');
        $deposit_date = $request->input('deposit_date');
        $promocode = $request->input('promocode');

        $account = Account::where('code', $code)->first();

        // $existingTransaction = TradeDeposit::where('transaction_id', $transactionId)->first();
        // if ($existingTransaction) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Transaction already exist',
        //         'error' => 'Transaction already exist',
        //     ], 400);
        // }



        if ($account) {
            $comment = 'CreditCardPayissa';
            $ticket = NULL;

            $errorCode = $this->mt5Service->tradeBalance($account->code, $typed = MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, $margin_check = true);
            if ($errorCode != MTRetCode::MT_RET_OK) {
                $error = MTRetCode::GetError($errorCode);
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong',
                    'error' => $error,
                ], 400);
            } else {
                $tradeDeposit = TradeDeposit::create([
                    'user_id' => $account->user_id,
                    'account_id' => $account->id,
                    'email' => $email,
                    'code' => $account->code,
                    'deposit_amount' => $amount,
                    'promocode_code' => $promocode,
                    'deposit_type' => 'CreditCardPayissa',
                    'deposit_from' => 'CreditCardPayissa',
                    'status' => 1,
                    'deposit_currency' => 'USD',
                    'transaction_id' => $transactionId,
                    'deposted_date' => $deposit_date,
                    'callback_data' => 'Polygon Deposit',
                    'callback_code' => "success",
                ]);

                // Fire the AccountTradesDepositEvent for Omnisend integration
                $user = \App\Models\User::find($account->user_id);
                if ($user) {
                    event(new AccountTradesDepositEvent($user, $amount));
                }

                PaymentLog::create([
                    'user_id' => $account->user_id,
                    'account_id' => $account->id,
                    'payment_id' => 0,
                    'promocode' => null,
                    'payment_amount' => $amount,
                    'payment_type' => 'CreditCardPayissa',
                    'payment_req' => 'Polygon Manually Pay',
                    'payment_reference_id' => 'Wallet',
                    'payment_url' => '',
                    'payment_status' => 'success',
                    'payment_res' => 'success',
                    'initiated_by' => $email,
                    'remarks' => 'https://my.lqhmarkets.com/payment-response?amount=' . $amount . '&payment_id=' . $transactionId . '&status=success',
                    'created_at' => $deposit_date,
                    'updated_at' => now()
                ]);
                if (isset($promocode)) {
                    $ticket2 = NULL;
                    $promo = Promocode::where('code', $promocode)->first();
                    if ($promo) {
                        $min_depsoit = $promo->min_deposit;
                        if ($promo && $amount >= $min_depsoit) {
                            if (isset($promo->max_deposit) && $amount >= $promo->max_deposit) {
                                $bonus_amount = ($promo->promo_percentage / 100) * $promo->max_deposit;
                            } else {
                                $bonus_amount = ($promo->promo_percentage / 100) * $amount;
                            }
                            if ($promo) {
                                if (($error_code2 = $this->mt5Service->tradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $bonus_amount, 'Promo Bonus', $ticket2, true)) !== MTRetCode::MT_RET_OK) {
                                    return redirect()->back()->with('error', MTRetCode::GetError($error_code2));
                                } else {

                                    BonusTransaction::create([
                                        'email' => $email,
                                        'user_id' => $account->user_id,
                                        'account_id' => $account->id,
                                        'code' => $account->code,
                                        'bonus_amount' => $bonus_amount,
                                        'bonus_type' => 'Bonus In',
                                        'status' => 1,
                                        'admin_remark' => 'Promo Bonus',
                                        'bonus_currency' => 'USD',
                                        'transaction_id' => $transactionId,
                                        'promocode_id' => $promo->id
                                    ]);

                                    // Updating leverage
                                    $trade_user = NULL;
                                    if (($error_code = $this->mt5Service->userGet($account->code, $trade_user)) != MTRetCode::MT_RET_OK) {
                                        return redirect()->back()->with('error', 'Something went wrong on Updating leverage' . MTRetCode::GetError($error_code));
                                    }
                                    Log::info("account " . $account->code);
                                    Log::info("message" . $trade_user->Leverage);
                                    Log::info("message" . $amount);
                                    Log::info("message" . $trade_user->Balance);
                                    Log::info("message" . $trade_user->Credit);
                                    Log::info(" $trade_user->Leverage * ($amount / ($trade_user->Balance + $trade_user->Credit)) ");
                                    $leverage = round($account->leverage * ($amount / ($trade_user->Balance + $trade_user->Credit)), 2);
                                    Log::info("New Leverage" . $leverage);
                                    $trade_user->Leverage = $leverage;

                                    $updated_user = "";
                                    if (($error_code = $this->mt5Service->userUpdate($trade_user, $updated_user)) != MTRetCode::MT_RET_OK) {
                                        return redirect()->back()->with("error", "Something went wrong on Updating leverage" . MTRetCode::GetError($error_code));
                                    }
                                    // Updating leverage
                                }
                            }
                        }
                    }
                }
            }
            if ($tradeDeposit) {
                $this->sendSuccessEmail2($email, $amount, $tradeDeposit);
                // $this->subscribeToKlaviyoList($paymentLog->user, $amount, $subscribeToKlaviyoList);
                return redirect('/trade-deposit')->with('success', "Successfully Deposited \$$amount To Your Wallet");
            } else {
                return redirect('/trade-deposit')->with('error', "Something went wrong. Please Try Again");
            }
        }
    }




    public function sendSuccessEmail2($toEmail, $amount, $tradedeposit)
    {
        $tradedeposit->deposit_type == "CreditCardPayissa" ? "Credit Card" : '';
        $user = User::where('id', $tradedeposit->user_id)->first();
        $settings = settings();
        $from = $settings['email_from_address'];
        $transid = $tradedeposit->id;
        $emailSubject = $settings['admin_title'] . ' - Fund Deposit';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content = '<p style="font-size: 16px; color: #000000;">
                        We are pleased to inform you that funds have been successfully deposited into your account..
                    </p>';

        $templateVars = [
            'name' => $user->fullname,
            'site_link' => $settings['copyright_site_name_text'],
            'email' => $settings['email_from_address'],
            "content" => $content,
            'amount' => $amount,
            'code' => $tradedeposit->code,
            'date' => now()->format('Y-m-d H:i:s'),
            'type' => $tradedeposit->deposit_type,
            "title_right" => "Transaction",
            "subtitle_right" => "Successful",
            "btn_text" => "Go To Dashboard",
        ];
        $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);
    }

    public function sendSuccessEmail($toEmail, $amount, $paymentLog, $lastInsertId)
    {
        $paymentLog->payment_type = $paymentLog->payment_type == "CreditCardPayissa" ? "Credit Card" : $paymentLog->payment_type;
        $settings = settings();
        $from = $settings['email_from_address'];
        $transid = "WDID" . $lastInsertId;
        $emailSubject = $settings['admin_title'] . ' - Fund Deposit';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content = '<p style="font-size: 16px; color: #000000;">
                        We are pleased to inform you that funds have been successfully deposited into your account..
                    </p>';

        $templateVars = [
            'name' => $paymentLog->fullname,
            'site_link' => $settings['copyright_site_name_text'],
            'email' => $settings['email_from_address'],
            "content" => $content,
            'amount' => $paymentLog->payment_amount,
            'code' => $paymentLog->account ? $paymentLog->account->code : 'N/A',
            'date' => $paymentLog->created_at,
            'type' => $paymentLog->payment_type,
            "title_right" => "Transaction",
            "subtitle_right" => "Successful",
            "btn_text" => "Go To Dashboard",
        ];
        $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);
    }
    /**
     * Validate payment with Payissa API using the stored ipn_token
     */
    private function validatePaymentWithPayissa(PaymentLog $paymentLog): bool
    {
        try {
            // Get ipn_token from payment_req or dedicated field
            $paymentRequest = json_decode($paymentLog->payment_req, true);
            $ipnToken = $paymentRequest['ipn_token'] ?? null;

            if (! $ipnToken) {
                Log::channel('creditcardpayissa')->warning('No ipn_token found in payment log', [
                    'payment_log_id' => $paymentLog->id,
                ]);

                return false;
            }

            // Call Payissa API to validate payment
            $response = Http::get(config('services.payissa.url').'/control/payment-status.php', [
                'ipn_token' => $ipnToken,
            ]);

            if (! $response->successful()) {
                Log::channel('creditcardpayissa')->warning('Payissa API validation failed', [
                    'payment_log_id' => $paymentLog->id,
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                ]);

                return false;
            }

            $responseData = $response->json();

            // Verify response contains confirmation of payment
            if (isset($responseData['status']) && $responseData['status'] === 'paid') {
                Log::channel('creditcardpayissa')->info('Payment validated successfully with Payissa API', [
                    'payment_log_id' => $paymentLog->id,
                ]);

                return true;
            }

            Log::channel('creditcardpayissa')->warning('Payissa API returned non-confirmed status', [
                'payment_log_id' => $paymentLog->id,
                'api_response' => $responseData,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::channel('creditcardpayissa')->error('Exception during Payissa API validation', [
                'payment_log_id' => $paymentLog->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
