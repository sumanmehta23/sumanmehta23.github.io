<?php

namespace App\Http\Controllers;

use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Models\PaymentLog;
use App\MT5\MTEnDealAction;
use Illuminate\Http\Request;
use App\Models\WalletDeposit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use App\Services\MailService as MailService;

class Payment extends Controller
{
    protected $mailService;
    public function __construct(MTWebAPI $api, MailService $mailService)
    {
        $this->mailService = $mailService;
    }
    public function handlePaymentResponse(Request $request)
    {
        $status = $request->input('status');
        $payment_id = $request->input('payment_id');
        $address_in = $request->input('address_in');
        if(!empty($address_in)){
            Log::info('Payment Response: '.json_encode($request->all()));
            return ["ok"];
            // return redirect('/wallet_deposit')->with('error', "Payment in progress: We are processing your payment request. Please wait for a while.");
        }else{
            
                $payment_res = json_encode($request->all());
                $paymentLog = PaymentLog::where(DB::raw('payment_id'), $payment_id)->with('user')->first();
                if ($status == "success") {
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
                        $walletDeposit = WalletDeposit::create([
                            'email' => $email,
                            'deposit_amount' => $amount,
                            'deposit_type' => "Now Payment",
                            'currency_type' => "USD",
                            'status' => 1,
                        ]);

                        if ($walletDeposit) {
                            $this->sendSuccessEmail($email, $amount, $paymentLog,$walletDeposit->id);
                            return redirect('/wallet_deposit')->with('success', "Successfully Deposited \$$amount To Your Wallet");
                        } else {
                            return redirect('/wallet_deposit')->with('error', "Something went wrong. Please Try Again");
                        }
                    } else {
                        return redirect('wallet_deposit')->with('error', "Payment already processed or invalid.");
                    }
                } else {
                    // Update payment log for failed payment
                    $paymentLog->update([
                        'payment_res' => $payment_res,
                        'payment_status' => $status,
                    ]);
                    return redirect('/wallet_deposit')->with('error', "Payment Failed: Something Went Wrong. Please try again");
                }

            }
    }

    public function sendSuccessEmail($toEmail, $amount, $paymentLog,$lastInsertId)
    {
        $settings = settings();
        $from = $settings['email_from_address'];
        $transid = "WDID" . str_pad($lastInsertId, 4, '0', STR_PAD_LEFT);
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
