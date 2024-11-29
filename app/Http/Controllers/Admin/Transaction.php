<?php

namespace App\Http\Controllers\admin;

use Exception;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\MT5\MTEnDealAction;
use App\Models\ClientWallet;
use App\Models\TotalBalance;
use Illuminate\Http\Request;
use App\Models\WalletWithdraw;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Services\MailService as MailService;
use PDO;

class Transaction extends Controller
{
    protected $mailService;
    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }
    public function index(Request $request)
    {
        if (!isset($request->id)) {
            return redirect('admin/dashboard');
        }
        $id = $request->id;
        return view('admin.transactions', compact('id'));
    }
    public function pending(Request $request)
    {
        if (!isset($request->id)) {
            return redirect('admin/dashboard');
        }
        $id = $request->id;
        return view('admin.pending_transactions', compact('id'));
    }
    public function wallet_deposit_details(Request $request)
    {
        if (request()->has('id') && !empty(request()->id)) {
            $details = DB::table('wallet_deposit as wd')
                ->leftJoin('aspnetusers as u', 'wd.email', '=', 'u.email')
                ->leftJoin('relationship_manager as r', 'wd.email', '=', 'r.user_id')
                ->leftJoin('emplist as emp', 'r.rm_id', '=', 'emp.email')
                ->leftJoin('ib1', 'u.ib1', '=', 'ib1.email')
                ->leftJoin('total_balance as tb', 'u.email', '=', 'tb.email')
                ->when(session('userData.role_id') == 2, function ($query) {
                    $query->join('relationship_manager as rm', 'wd.email', '=', 'rm.user_id')
                        ->where('rm.rm_id', session('alogin'));
                })
                ->where(function ($query) {
                    $id = request()->id;
                    $query->where(DB::raw('wd.id'), $id);
                })
                ->selectRaw("
                    COALESCE(SUM(tb.deposit_amount), 0) as total_wallet_dp,
                    COALESCE(SUM(tb.trading_deposited), 0) as total_trading_dp,
                    COALESCE(SUM(tb.trading_withdrawal), 0) as total_trading_wd,
                    COALESCE(SUM(tb.withdraw_amount), 0) as total_wallet_wd,
                    wd.*, u.fullname, u.number, ib1.name as parent_ib,
                    ib1.email as parent_ib_email, r.rm_id, emp.username as rm_name
                ")
                ->groupBy('u.email')
                ->first();
            return view('admin.wallet_deposit_details', compact('details'));
        }
    }
    public function wallet_withdrawal_details(Request $request)
    {
        if (request()->has('id') && !empty(request()->id)) {
            DB::enableQueryLog();
            $details = DB::table('wallet_withdraw as wd')
                ->leftJoin('clientbankdetails as cbd', 'wd.client_bank', '=', 'cbd.id')
                ->leftJoin('aspnetusers as u', 'wd.email', '=', 'u.email')
                ->leftJoin('total_balance as tb', 'u.email', '=', 'tb.email')
                ->leftJoin('relationship_manager as r', 'wd.email', '=', 'r.user_id')
                ->leftJoin('emplist as emp', 'r.rm_id', '=', 'emp.email')
                ->leftJoin('ib1', 'u.ib1', '=', 'ib1.email')
                ->when(session('userData.role_id') == 2, function ($query) {
                    $query->join('relationship_manager as rm', 'wd.email', '=', 'rm.user_id')
                        ->where('rm.rm_id', session('alogin'));
                })
                ->where(function ($query) {
                    $id = request()->id;
                    $query->where(DB::raw('wd.id'), $id);
                })
                ->selectRaw("
                    cbd.bankName, cbd.branch, cbd.bankDetails, cbd.accountNumber, cbd.code, cbd.swift_code, cbd.ClientName,
                    COALESCE(SUM(tb.deposit_amount), 0) as total_wallet_dp,
                    COALESCE(SUM(tb.trading_deposited), 0) as total_trading_dp,
                    COALESCE(SUM(tb.trading_withdrawal), 0) as total_trading_wd,
                    COALESCE(SUM(tb.withdraw_amount), 0) as total_wallet_wd,
                    wd.*, u.fullname, u.number, ib1.name as parent_ib,
                    ib1.email as parent_ib_email, r.rm_id, emp.username as rm_name, '' as currency_type
                ")
                ->groupBy('u.email')
                ->first();
            if($details->client_wallet_id){
                $client_wallet = ClientWallet::where('id', $details->client_wallet_id)
                ->where('status', 1)
                ->first();
            }else{
                $client_wallet='';
            }
            return view('admin.wallet_withdrawal_details', compact('details','client_wallet'));
        }
    }
    public function trading_deposit_details(Request $request)
    {
        if (request()->has('id')) {
            $details = DB::table('trade_deposit as wd')
                ->leftJoin('aspnetusers as u', 'wd.email', '=', 'u.email')
                ->leftJoin('total_balance as tb', 'u.email', '=', 'tb.email')
                ->leftJoin('relationship_manager as r', 'wd.email', '=', 'r.user_id')
                ->leftJoin('emplist as emp', 'r.rm_id', '=', 'emp.email')
                ->leftJoin('ib1', 'u.ib1', '=', 'ib1.email')
                ->when(session('userData.role_id') == 2, function ($query) {
                    $query->join('relationship_manager as rm', 'wd.email', '=', 'rm.user_id')
                        ->where('rm.rm_id', session('alogin'));
                })
                ->where(function ($query) {
                    $id = request()->id;
                    $query->where(DB::raw('wd.id'), $id);
                })
                ->selectRaw("
                    COALESCE(SUM(tb.deposit_amount), 0) as total_wallet_dp,
                    COALESCE(SUM(tb.trading_deposited), 0) as total_trading_dp,
                    COALESCE(SUM(tb.trading_withdrawal), 0) as total_trading_wd,
                    COALESCE(SUM(tb.withdraw_amount), 0) as total_wallet_wd,
                    wd.*, u.fullname, u.number, u.email, ib1.name as parent_ib,
                    ib1.email as parent_ib_email, r.rm_id, emp.username as rm_name,'' as deposit_currency_amount,'' as deposit_currency_in_usd
                ")
                ->groupBy('u.email')
                ->first();
            return view('admin.trading_deposit_details', compact('details'));
        }
    }
    public function trading_withdrawal_details(Request $request)
    {
        if (request()->has('id') && !empty(request()->id)) {
            $details = DB::table('trade_withdrawal as wd')
                ->leftJoin('aspnetusers as u', 'wd.email', '=', 'u.email')
                ->leftJoin('total_balance as tb', 'u.email', '=', 'tb.email')
                ->leftJoin('relationship_manager as r', 'wd.email', '=', 'r.user_id')
                ->leftJoin('emplist as emp', 'r.rm_id', '=', 'emp.email')
                ->leftJoin('ib1', 'u.ib1', '=', 'ib1.email')
                ->where(function ($query) {
                    $id = request()->id;
                    $query->where(DB::raw('wd.id'), $id);
                })
                ->where(DB::raw('wd.id'), request()->id)
                ->selectRaw("
                    COALESCE(SUM(tb.deposit_amount), 0) as total_wallet_dp,
                    COALESCE(SUM(tb.trading_deposited), 0) as total_trading_dp,
                    COALESCE(SUM(tb.trading_withdrawal), 0) as total_trading_wd,
                    COALESCE(SUM(tb.withdraw_amount), 0) as total_wallet_wd,
                    wd.*, u.fullname, u.number, u.email,
                    ib1.name as parent_ib, ib1.email as parent_ib_email,
                    r.rm_id, emp.username as rm_name, tb.code
                ")
                ->groupBy('u.email')
                ->first();
             
            return view('admin.trading_withdrawal_details', compact('details'));
        }
    }
    public function update_wallet_withdrawal(Request $request)
    {
        $settings = settings();
        $validatedData = $request->validate([
            'description' => 'required|string|max:255',
            'status' => 'required|integer',
            'email' => 'required|email',
            'amount' => 'required|numeric',
        ]);
        $description = $validatedData['description'];
        $status = $validatedData['status'];
        $email = $validatedData['email'];
        $depositAmount = $validatedData['amount'];
        $did = $request->input('id');
        $transaction_id = $request->input('transaction_id');
        $transaction = WalletWithdraw::whereRaw('id = ?', [$did])->first();
        if ($transaction) {
            $transaction->admin_remark = $description;
            $transaction->Status = $status;
            $transaction->transaction_id = $transaction_id;
            $transaction->save();
            if ($status == 1) {
                TotalBalance::create([
                    'email' => $email,
                    'withdraw_amount' => $depositAmount,
                ]);

                if ($transaction && $transaction->withdraw_type == "Wallet Withdrawal" && empty($transaction->payout_req) && $transaction->client_wallet_id) {

                    $walletDetails = ClientWallet::where('id', $transaction->client_wallet_id)->first();
                    $walletNetwork = $walletDetails->wallet_network;
                    $walletCurrency = $walletDetails->wallet_currency;
                    $walletAddress = $walletDetails->wallet_address;
                    $amount = $transaction->withdraw_amount;


                    $payload = [
                        "profile_id" => env('CRYPTOCHILL_PROFILE_ID'),
                        "passthrough" => json_encode(["trans_id" => $did]),
                        "reference_id" => "LQHPRW" . str_pad($transaction->id, 9, '0', STR_PAD_LEFT) . "-" . rand(100, 999),
                        "kind" => $walletNetwork,
                        "recipients" => [
                            [
                                "amount" => $amount,
                                "currency" => $walletCurrency,
                                "address" => $walletAddress,
                                "notes" => $email . " WD# " . $transaction->id
                            ]
                        ],
                        "request" => "/v1/payouts/",
                        "nonce" => time() * 1000
                    ];

                    $response = Http::withHeaders([
                        'X-CC-KEY' => env('CRYPTOCHILL_API_KEY'),
                        'X-CC-PAYLOAD' => base64_encode(json_encode($payload)),
                        'X-CC-SIGNATURE' => hash_hmac('sha256', base64_encode(json_encode($payload)), env('CRYPTOCHILL_API_SECRET')),
                    ])->post('https://api.cryptochill.com/v1/payouts/', $payload);

                    // Log the response
                    Log::channel('payouts')->info("Request Payload: " . json_encode($payload));
                    Log::channel('payouts')->info("API Response: " . $response->body());

                    // Check if there was an error decoding the JSON
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception("Error decoding response payload: " . json_last_error_msg());
                    }

                    $responseData=json_decode($response);

                    // Process the result from the API
                    if (isset($responseData->result) && isset($responseData->result->id)) {
                        $payoutResult = $responseData->result;

                        DB::transaction(function () use ($request, $response, $payoutResult, $transaction) {
                            // Update wallet_withdraw table with transaction_id and status
                            WalletWithdraw::where('id', $transaction->id)
                                ->orWhere(DB::raw('id'), '=', $request->did)
                                ->update([
                                    'transaction_id' => $payoutResult->id,
                                    'payout_res' => $response->body(),
                                    'payout_req' => json_encode($payoutResult->passthrough),
                                    'status' => 1  // Set status to 1 (success)
                                ]);
                        });
                    } else {
                        // Update `wallet_withdraw` and delete the `total_balance` entry in case of error
                        DB::transaction(function () use ($request, $response, $responseData, $transaction) {
                            // Update wallet_withdraw table with response and set status to 0 (error state)
                            WalletWithdraw::where('id', $transaction->id)
                                ->orWhere(DB::raw('id'), '=', $request->did)
                                ->update([
                                    'payout_res' => $response->body(),
                                    'payout_req' => json_encode($responseData),
                                    'status' => 0
                                ]);

                            // Delete total_balance entry
                            TotalBalance::where('id', $transaction->id)->delete();
                        });

                        // Throw an exception with the error message from the response
                        throw new Exception("Error Processing Request: " . $responseData->message);
                    }
                }

                $deposit_details = WalletWithdraw::with('user')
                    ->whereRaw('id = ?', [$did])
                    ->first();
                $from = $settings['email_from_address'];
                $transid = "WDID" . str_pad($deposit_details->id, 4, '0', STR_PAD_LEFT);
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $emailSubject = $settings['admin_title'] . ' - Transaction Approved';
                $content = '<div>We are pleased to inform you that your transaction has been successfully approved.</div>
                            <div>The approved amount has been withdrawn from your wallet.</div>
                            <div><b>Transaction Details</b></div>
                            <div><b>Approved Amount: </b>$' . $deposit_details->withdraw_amount . '</div>
                            <div><b>Transaction ID: </b>' . $transid . '</div>
                            <div><b>Withdrawal Date: </b>' . $deposit_details->withdraw_date . '</div>
                            <div><b>Withdrawal Type: </b>' . $deposit_details->withdraw_type . '</div>';
                $templateVars = [
                    'name' => $deposit_details->user->fullname,
                    'site_link' => $settings['copyright_site_name_text'],
                    'email' => $settings['email_from_address'],
                    'content' => $content,
                    'title_right' => 'Transaction',
                    'subtitle_right' => 'Approved',
                    'btn_text' => 'Go To Dashboard',
                ];
                $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
                return redirect()->back()->with('status', 'Transaction Approved Successfully');
            }
            return redirect()->back()->with('status', 'Transaction Rejected Successfully');
        } else {
            return redirect()->back()->with('error', 'Transaction Not Found');
        }
    }

    public function update_trading_withdrawal(Request $request)
    {   
        $settings = settings();
        $validatedData = $request->validate([
            'description' => 'required|string|max:255',
            'status' => 'required|integer',
            'email' => 'required|email',
            'amount' => 'required|numeric',
        ]);
        $description = $validatedData['description'];
        $status = $validatedData['status'];
        $did = $request->id;
        $email = $validatedData['email'];
        $amount = ((float) $validatedData['amount']) * -1;
        $login = $request->code;
        $transaction_id = $request->transaction_id;
        
        $transaction = TradeWithdrawals::whereRaw('id = ?', [$did])->first();

        dd($request->ALL());
        // echo "<script>console.log('TradingDeposit Started')</script>";
        define("PATH_TO_SCRIPTS", "./mt5_api/");
        include PATH_TO_SCRIPTS . "mt5_api.php";
        define('T_QUOTES', 'EURUSD,GBPUSD,USDJPY,AUDUSD,USDCAD'); //symbols list for publication
        define("MT5_CRYPT_PROTOCOL", true); // enable crypt protocol
        define("IS_WRITE_DEBUG_LOG", true); // Write all in logs
        define("MT5_CONNECTION_TIMEOUT", 3);
        // define("PATH_TO_LOGS", "./logs"); // Write all in logs
        // define("AGENT", "WebAPITesterArt");
        $api = new MTWebAPI(AGENT, PATH_TO_LOGS, MT5_CRYPT_PROTOCOL);
        $api->SetLoggerWriteDebug(IS_WRITE_DEBUG_LOG);
    
        if (($error_code = $api->Connect(MT5_SERVER_IP, MT5_SERVER_PORT, MT5_CONNECTION_TIMEOUT, MT5_SERVER_WEB_LOGIN, MT5_SERVER_WEB_PASSWORD)) != MTRetCode::MT_RET_OK) {
        ?>
        <script>
          console.log("MT5 Connectivity Error: <?= MTRetCode::GetError($error_code) ?>");
          $(document).ready(function() {
            Sweetalert2.fire({
              icon: 'error',
              title: 'Something went wrong.',
              text: 'Please try again after sometime or contact support. '
            }).then((val) => {
              location.href = "<?= $_SERVER['SCRIPT_NAME'] ?>"
            });
          });
        </script>
      <?php
      }
    
      // print_r($api->TradeBalance($login, $type = MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket = null, $margin_check = true));
      // exit();
      // print_r($data);
      $ticket = null;
      $comment = "Withdrawal";
      // echo $login."==>". $type = MTEnDealAction::DEAL_BALANCE."==>". $amount."==>". $comment."==>". $ticket = null."==>". $margin_check = true;
      // echo "<script>console.log('TradingDeposit Inited')</script>";
    
      if ($status == 1) {
        if (($error_code = $api->TradeBalance($login, $type = MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, $margin_check = true)) != MTRetCode::MT_RET_OK) {
          $error = MTRetCode::GetError($error_code);
          echo "<script>console.log('TradingDeposit Error==> ".$error."')</script>";
        } else {
    
          $sql = "update trade_withdrawal set admin_remark=:description,Status=:status where md5(id)=:did";
          $query = $dbh->prepare($sql);
          $query->bindParam(':description', $description, PDO::PARAM_STR);
          $query->bindParam(':status', $status, PDO::PARAM_STR);
          $query->bindParam(':did', $did, PDO::PARAM_STR);
          $query->execute();
    
          $sql = "INSERT INTO total_balance(email,trading_withdrawal) VALUES(:email,:amount)";
          $query = $dbh->prepare($sql);
          $query->bindParam(':email', $email, PDO::PARAM_STR);
          $query->bindParam(':amount', $amount, PDO::PARAM_STR);
          $query->execute();
    
    
    
          $sql = "Select td.id,ap.fullname,td.email,td.code,td.withdrawal_amount as amount, td.withdraw_date as date,td.withdraw_type as type from trade_withdrawal td left join aspnetusers ap on(td.email=ap.email) where (md5(td.id)=:did || td.id=:did)";
          $query = $dbh->prepare($sql);
          $query->bindParam(':did', $did, PDO::PARAM_STR);
          $query->execute();
          $deposit_details = $query->fetch(PDO::FETCH_OBJ);
    
          if ($deposit_details->type == "Wallet Withdrawal") {
            $sql = "INSERT INTO total_balance(email,deposit_amount) VALUES(:email,:amount)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':amount', $amount, PDO::PARAM_STR);
            $query->execute();
          }
    
          $toEmail = $email;
          $from = $email_from_address;
          $transid = "TWID" . str_pad($deposit_details->id, 4, '0', STR_PAD_LEFT);
          $emailSubject = $title . ' - Transaction Approved';
          $htmlContent = "";
          $headers = "MIME-Version: 1.0" . "\r\n";
          $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
          $headers .= 'From:' . $title . '<' . $from . '>' . "\r\n";
          $content = '<div>We are pleased to inform you that your transaction has been successfully approved. </div>
            <div>The approved amount has been withdrawn to your wallet.</div>
            <div><b>Transaction Details</b></div>
            <div><b>Approved Amount: </b>$' . $deposit_details->amount . '</div>
            <div><b>Account ID: </b>' . $deposit_details->code . '</div>
            <div><b>Transaction ID: </b>' . $transid . '</div>
            <div><b>Withdraw Date: </b>' . $deposit_details->date . '</div>
            <div><b>Withdraw Type </b>' . $deposit_details->type . '</div>';
          $templateVars = [
            'name' => $deposit_details->fullname,
            'site_link' => $copyright_site_name_text,
            'email' => $email_from_address,
            "content" => $content,
            "title_right" => "Transaction",
            "subtitle_right" => "Approved",
            "btn_text" => "Go To Dashboard",
          ];
          phpMail($toEmail, $emailSubject, $htmlContent, $headers, 'email-template.php', $templateVars);
        }
      } else {
        $sql = "update trade_withdrawal set admin_remark=:description,Status=:status where md5(id)=:did";
        $query = $dbh->prepare($sql);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':did', $did, PDO::PARAM_STR);
        $query->execute();
    
        $sql = "Select td.id,ap.fullname,td.email,td.code,td.withdrawal_amount as amount, td.withdraw_date as date,td.withdraw_type as type from trade_withdrawal td left join aspnetusers ap on(td.email=ap.email) where md5(td.id)=:did";
        $query = $dbh->prepare($sql);
        $query->bindParam(':did', $did, PDO::PARAM_STR);
        $query->execute();
        $deposit_details = $query->fetch(PDO::FETCH_OBJ);
    
        $toEmail = $email;
        $from = $email_from_address;
        $transid = "TWID" . str_pad($deposit_details->id, 4, '0', STR_PAD_LEFT);
        $emailSubject = $title . ' - Transaction Approved';
        $htmlContent = "";
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $title . '<' . $from . '>' . "\r\n";
        $content = '<div>This email to inform you that your transaction has been Rejected. </div>
          <div><b>Transaction Details</b></div>
          <div><b>Rejected Amount: </b>$' . $deposit_details->amount . '</div>
          <div><b>Account ID: </b>' . $deposit_details->code . '</div>
          <div><b>Transaction ID: </b>' . $transid . '</div>
          <div><b>Withdraw Date: </b>' . $deposit_details->date . '</div>
          <div><b>Withdraw Type </b>' . $deposit_details->type . '</div>
          <div><b>Rejection Remark </b>' . $description . '</div>';
        $templateVars = [
          'name' => $deposit_details->fullname,
          'site_link' => $copyright_site_name_text,
          'email' => $email_from_address,
          "content" => $content,
          "title_right" => "Transaction",
          "subtitle_right" => "Rejected",
          "btn_text" => "Go To Dashboard",
        ];
        phpMail($toEmail, $emailSubject, $htmlContent, $headers, 'email-template.php', $templateVars);
      }
    }
}
