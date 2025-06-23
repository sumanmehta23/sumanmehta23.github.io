<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\Promocode;
use App\Models\PaymentLog;
use App\MT5\MTEnDealAction;
use App\Models\TotalBalance;
use App\Models\TradeDeposit;
use App\Services\MT5Service;
use Illuminate\Http\Request;
use App\Models\WalletDeposit;
use App\Models\BonusTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Actions\SubscribeToKlaviyoList;
use App\Services\MailService as MailService;

class Payment extends Controller
{
    protected $mailService;
    protected $api;
    protected $mt5Service;
    public function __construct(MTWebAPI $api, MailService $mailService, MT5Service $mt5Service)
    {
        $this->settings = settings();
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
    }
    public function handlePaymentResponse(Request $request, SubscribeToKlaviyoList $subscribeToKlaviyoList)
    {
        $status = $request->input('status');
        $payment_id = $request->input('payment_id');
        $address_in = $request->input('address_in');
        $responsedata = $request->all();
        $transactionId = $responsedata['txid_in'];
        if (!empty($address_in)) {

            Log::channel("creditcardpayissa")->info('Payment callback Response: ' . json_encode($responsedata));


            $paymentLog = PaymentLog::where('id', $payment_id)->with('user')->first();
            if (!$paymentLog) {
                return response()->json(['error' => 'Invalid Payment ID'], 400);
            }
            $paymentlinkresponse = json_decode($paymentLog->payment_req);
            $validationToken = $paymentlinkresponse->polygon_address_in;
            // && $responsedata['value_coin']==$paymentLog->payment_amount can't compare as it will never be same as intial input
            if ($responsedata['address_in'] == $validationToken) {
                $validcoins = config("services.payissa.valid_coins");
                $coinString = strtolower($responsedata['coin']);
                $matches = array_filter($validcoins, function ($coin) use ($coinString) {
                    return stripos($coinString, $coin) !== false;
                });
                if (empty($matches)) {
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
                      <div><b>User Email: </b>' . $paymentLog->initiated_by . '</div>';
                    $templateVars = [
                        'name' => 'Admin',
                        'site_link' => $settings['copyright_site_name_text'],
                        'email' => $settings['email_from_address'],
                        "content" => $content,
                        "title_right" => "Invalid Coin Payment",
                        "subtitle_right" => "Alert",
                        "btn_text" => "Go To Dashboard",
                    ];
                    $this->mailService->sendEmail($settings['admin_email'], $emailSubject, $headers, '', $templateVars);
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
                $settings = settings();
                $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
                $this->api->Connect(
                    $settings['mt5_server_ip'],
                    $settings['mt5_server_port'],
                    300,
                    $settings['mt5_server_web_login'],
                    $settings['mt5_server_web_password']
                );

                $account = Account::where('id', $paymentLog->account_id)->first();

                $ticket1=NULL;
                if ($account->accountType->ac_group == 'LM\B-Book\10x\DF-B' && $account->successful_trade_deposits_count == 0) {
                    $existingTransaction = BonusTransaction::where('transaction_id', $transactionId)->first();
                    if (!$existingTransaction) {
                        if ($amount > 250) {
                            $bonusamount = 9 * 250;
                        } else {
                            $bonusamount = 9 * $amount;
                        }

                        if (($error_code1 = $this->api->TradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $bonusamount, '10x Trader Leverage', $ticket1, true)) !== MTRetCode::MT_RET_OK) {
                            return redirect()->back()->with('error', MTRetCode::GetError($error_code1));
                        } else {
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

                if(isset($paymentLog->promocode) && $paymentLog->promocode !=''){
                    $ticket2 = NULL;
                    $promo = Promocode::where('code', $paymentLog->promocode)->first();
                    if(isset($promo->max_deposit) && $amount >= $promo->max_deposit){
                        $bonus_amount = ($promo->promo_percentage/100) * $promo->max_deposit;
                    }else{
                        $bonus_amount = ($promo->promo_percentage/100) * $amount;
                    }
                    if($promo){
                        if (($error_code2 = $this->api->TradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $bonus_amount, 'Promo Bonus', $ticket2, true)) !== MTRetCode::MT_RET_OK) {
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
                        }
                    }
                }

                $comment = 'CreditCardPayissa';
                $ticket3 = NULL;

                $errorCode3 = $this->api->TradeBalance($account->code, $typed = MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket3, $margin_check = true);
                if ($errorCode3 != MTRetCode::MT_RET_OK) {
                    $error = MTRetCode::GetError($errorCode3);
                    return response()->json([
                        'success' => false,
                        'message' => 'Something went wrong',
                        'error' => $error,
                    ], 400);
                } else {
                    try {
                        DB::beginTransaction();

                        // $walletDeposit=  WalletDeposit::create([
                        //     'user_id' => $paymentLog->user_id,
                        //     'email' => $email,
                        //     'deposit_type' => "CreditCardPayissa",
                        //     'deposit_amount' => $amount,
                        //     'company_bank' => "CreditCardPayissa",
                        //     'transaction_id' => $transactionId,
                        //     'status' => 1,
                        //     'currency_type' => 'USD',
                        //     'callback_data' => json_encode($responsedata),
                        //     'callback_code' => "success",
                        // ]);

                        $tradeDeposit = TradeDeposit::create([
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
                        ]);

                        // Update total balance
                        TotalBalance::create(
                            ['email' => $email, 'user_id' => $paymentLog->user_id, 'deposit_amount' => $amount]
                        );

                        DB::commit();
                        $this->subscribeToKlaviyoList($paymentLog->user, $amount, $subscribeToKlaviyoList);
                        Cache::forget("user:{$paymentLog->user_id}:wallet_balance");
                        Log::channel("creditcardpayissa")->info('Transaction confirmed successfully.');
                        // $this->sendSuccessEmail($email, $amount, $paymentLog,$walletDeposit->id);
                        $this->sendSuccessEmail($email, $amount, $paymentLog, $tradeDeposit->id);
                        return response()->json(['status' => 'true']);
                    } catch (Exception $e) {
                        DB::rollBack();
                        Log::channel("creditcardpayissa")->error('Transaction failed: ' . $e->getMessage());
                        return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
                    }
                }
            }

            return ["ok"];
            // return redirect('/wallet_deposit')->with('error', "Payment in progress: We are processing your payment request. Please wait for a while.");
        } else {

            $payment_res = json_encode($request->all());
            $paymentLog = PaymentLog::where(DB::raw('payment_id'), $payment_id)->with('user')->first();
            $account = Account::where('id', $paymentLog->account_id)->first();

            if ($status == "success" && $account) {
                // Get the payment log
                if ($paymentLog && strtolower($paymentLog->payment_status) != "success") {
                    // Update payment log
                    $paymentLog->update([
                        'payment_res' => $payment_res,
                        'payment_status' => $status,
                    ]);
                    $email = $paymentLog->initiated_by;
                    $amount = $paymentLog->payment_amount;
                    // Create a new wallet deposit
                    // $walletDeposit = WalletDeposit::create([
                    //     'email' => $email,
                    //     'deposit_amount' => $amount,
                    //     'deposit_type' => "Now Payment",
                    //     'currency_type' => "USD",
                    //     'status' => 1,
                    // ]);
                    $errorCode = $this->api->TradeBalance($account->code, $typed = MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, $margin_check = true);
                    if ($errorCode != MTRetCode::MT_RET_OK) {
                        $error = MTRetCode::GetError($errorCode);
                        return response()->json([
                            'success' => false,
                            'message' => 'Something went wrong',
                            'error' => $error,
                        ], 400);
                    } else {
                        $tradeDeposit = TradeDeposit::create([
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
                        ]);
                    }

                    if ($tradeDeposit) {
                        $this->sendSuccessEmail($email, $amount, $paymentLog, $tradeDeposit->id);
                        $this->subscribeToKlaviyoList($paymentLog->user, $amount, $subscribeToKlaviyoList);
                        return redirect('/trade-deposit')->with('success', "Successfully Deposited \$$amount To Your Wallet");
                    } else {
                        return redirect('/trade-deposit')->with('error', "Something went wrong. Please Try Again");
                    }
                } else {
                    return redirect('trade-deposit')->with('error', "Payment already processed or invalid.");
                }
            } else {
                // Update payment log for failed payment
                $paymentLog->update([
                    'payment_res' => $payment_res,
                    'payment_status' => $status,
                ]);
                return redirect('/trade-deposit')->with('error', "Payment Failed: Something Went Wrong. Please try again");
            }
        }
    }
    protected function getKlaviyoListId($amount)
    {
        $lists = [
            'DEPOSIT_10_200' => ['min' => 10, 'max' => 200, 'id' => config('services.klaviyo.list_ids.DEPOSIT_10_200')],
            'DEPOSIT_200_2000' => ['min' => 200, 'max' => 2000, 'id' => config('services.klaviyo.list_ids.DEPOSIT_200_2000')],
            'DEPOSIT_2000_5000' => ['min' => 2000, 'max' => 5000, 'id' => config('services.klaviyo.list_ids.DEPOSIT_2000_5000')],
            'DEPOSIT_5000_PLUS' => ['min' => 5000, 'max' => PHP_INT_MAX, 'id' => config('services.klaviyo.list_ids.DEPOSIT_5000_PLUS')],
        ];
        foreach ($lists as $list) {
            if ($amount >= $list['min'] && $amount < $list['max']) {
                return $list['id'];
            }
        }
        return null;
    }
    protected function subscribeToKlaviyoList(User $user, $amount, SubscribeToKlaviyoList $subscribeToKlaviyoList)
    {
        $listId = $this->getKlaviyoListId($amount);
        if ($listId) {
            $subscribeToKlaviyoList->handle($user, $listId);
        }
    }

    public function sendSuccessEmail($toEmail, $amount, $paymentLog, $lastInsertId)
    {
        $paymentLog->payment_type = $paymentLog->payment_type == "CreditCardPayissa" ? "Credit Card" : $paymentLog->payment_type;
        $settings = settings();
        $from = $settings['email_from_address'];
        $transid = "WDID" . $lastInsertId;
        $emailSubject = $settings['admin_title'] . ' - Transaction Successful';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content = '<div>We are pleased to inform you that your transaction has been successful.</div>
          <div>The approved amount has been deposited into your wallet.</div>
          <div><b>Transaction Details</b></div>
          <div><b>Approved Amount: </b>$' . $paymentLog->payment_amount . '</div>
          <div><b>Reference ID: </b>' . $paymentLog->payment_reference_id . '</div>
          <div><b>Transaction ID: </b>' . $transid . '</div>
          <div><b>Deposited Date: </b>' . $paymentLog->created_at . '</div>
          <div><b>Payment Type: </b>' . $paymentLog->payment_type . '</div>';
        $templateVars = [
            'name' => $paymentLog->fullname,
            'site_link' => $settings['copyright_site_name_text'],
            'email' => $settings['email_from_address'],
            "content" => $content,
            "title_right" => "Transaction",
            "subtitle_right" => "Successful",
            "btn_text" => "Go To Dashboard",
        ];
        $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);
    }
}
