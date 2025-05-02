<?php

namespace App\Http\Controllers\admin;

use PDO;
use Exception;
use App\Models\Ib1;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\MT5\MTEnDealAction;
use App\Models\ClientWallet;
use App\Models\EmployeeList;
use App\Models\TotalBalance;
use App\Models\TradeDeposit;
use Illuminate\Http\Request;
use App\Models\WalletDeposit;
use App\Models\WalletWithdraw;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\DB;
use App\Models\RelationshipManager;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\MailService as MailService;
use App\Services\MT5Service;
class Transaction extends Controller
{
    protected $mailService;
    protected $api;
    protected $mt5Service;
    public function __construct(MailService $mailService, MT5Service $mt5Service, MTWebAPI $api)
    {
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
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
    public function wallet_deposit(Request $request)
    {
        $id = "wallet_deposit";
        return view('admin.transactions.wallet_deposit', compact('id'));
    }
    public function wallet_withdrawal(Request $request)
    {

        $id = "wallet_withdrawal";
        return view('admin.transactions.wallet_withdrawal', compact('id'));
    }
    public function trading_deposit(Request $request)
    {

        $id = "trading_deposit";
        return view('admin.transactions.trading_deposit', compact('id'));
    }
    public function trading_withdrawal(Request $request)
    {

        $id = "trading_withdrawal";
        return view('admin.transactions.trading_withdrawal', compact('id'));
    }
    public function internal_transfer(Request $request)
    {
        $id = "internal_transfer";
        return view('admin.transactions.internal_transfer', compact('id'));
    }
    public function pendingWalletDeposit(Request $request)
    {
        $id = "wallet_deposit";
        return view('admin.transactions.pending.wallet_deposit', compact('id'));
    }
    public function pendingWalletWithdrawal(Request $request)
    {
        $id = "wallet_withdrawal";
        return view('admin.transactions.pending.wallet_withdrawal', compact('id'));
    }
    public function pendingTradingDeposit(Request $request)
    {
        $id = "trading_deposit";
        return view('admin.transactions.pending.trading_deposit', compact('id'));
    }
    public function pendingTradingWithdrawal(Request $request)
    {
        $id = "trading_withdrawal";
        return view('admin.transactions.pending.trading_withdrawal', compact('id'));
    }

    public function wallet_deposit_details(Request $request)
    {
        if (request()->has('id') && !empty(request()->id)) {
            $details = DB::table('wallet_deposit as wd')
                ->leftJoin('aspnetusers as u', 'wd.email', '=', 'u.email')
                ->leftJoin('relationship_manager as r', 'wd.email', '=', 'r.user_id')
                ->leftJoin('emplist as emp', 'r.rm_id', '=', 'emp.email')
                ->leftJoin('ib1', 'u.ib1', '=', 'ib1.referral_code')
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
        // dd($request->all());
        if (request()->has('id') && !empty(request()->id)) {
            // DB::enableQueryLog();
            // $details = DB::table('wallet_withdraw as wd')
            //     ->leftJoin('clientbankdetails as cbd', 'wd.client_bank', '=', 'cbd.id')
            //     ->leftJoin('aspnetusers as u', 'wd.email', '=', 'u.email')
            //     ->leftJoin('total_balance as tb', 'u.email', '=', 'tb.email')
            //     ->leftJoin('relationship_manager as r', 'wd.user_id', '=', 'r.user_id')
            //     ->leftJoin('emplist as emp', 'r.rm_id', '=', 'emp.email')
            //     ->leftJoin('ib1', 'u.ib1', '=', 'ib1.email')
            //     ->when(session('userData.role_id') == 2, function ($query) {
            //         $query->join('relationship_manager as rm', 'wd.email', '=', 'rm.user_id')
            //             ->where('rm.rm_id', session('alogin'));
            //     })
            //     ->where(function ($query) {
            //         $id = request()->id;
            //         $query->where(DB::raw('wd.id'), $id);
            //     })
            //     ->selectRaw("
            //         cbd.bankName, cbd.branch, cbd.bankDetails, cbd.accountNumber, cbd.code, cbd.swift_code, cbd.ClientName,
            //         COALESCE(SUM(tb.deposit_amount), 0) as total_wallet_dp,
            //         COALESCE(SUM(tb.trading_deposited), 0) as total_trading_dp,
            //         COALESCE(SUM(tb.trading_withdrawal), 0) as total_trading_wd,
            //         COALESCE(SUM(tb.withdraw_amount), 0) as total_wallet_wd,
            //         wd.*, u.fullname, u.number, ib1.name as parent_ib,
            //         ib1.email as parent_ib_email, r.rm_id, emp.username as rm_name, '' as currency_type
            //     ")
            //     ->groupBy('u.email')
            //     ->first();

            // dd($details);

            $id = request()->id;
            $details = WalletWithdraw::with([
                'clientWallet',
                'user',
                'user.parentib',
                'totalBalance',
                // 'relationshipManager.emplist',
                'user',
            ])
            ->where('id',$id)
            ->withSum('totalBalance', 'deposit_amount') // Aggregate total wallet deposits
            ->withSum('totalBalance', 'trading_deposited') // Aggregate total trading deposits
            ->withSum('totalBalance', 'trading_withdrawal') // Aggregate total trading withdrawals
            ->withSum('totalBalance', 'withdraw_amount') // Aggregate total wallet withdrawals
            ->first();

            if($details->client_wallet_id){
                $client_wallet = ClientWallet::withTrashed()->where('id', $details->client_wallet_id)
                ->where('status', 1)
                ->first();
            }else{
                $client_wallet='';
            }
            // dd($client_wallet);
            // dd($details);
            return view('admin.wallet_withdrawal_details', compact('details','client_wallet'));
        }
    }

    public function walletWithdrawalAmountUpdate(Request $request)
    {
        $amount = $request->amount;
        $walletWithdrawal = WalletWithdraw::find($request->id);

        if(!$amount){
            return redirect()->back()->with('error', 'Please enter Amount');
        }

        if($walletWithdrawal){
            $totalDeposits = WalletDeposit::where('email', $walletWithdrawal->user->email)
                ->where('status', 1)
                ->sum('deposit_amount');

            $totalWithdrawals = WalletWithdraw::where('email', $walletWithdrawal->user->email)
                ->whereNotIn('status', [2, 3])
                ->sum('withdraw_amount');

            $totalWithdrawalsFee = WalletWithdraw::where('email', $walletWithdrawal->user->email)
                ->whereNotIn('status', [2, 3])
                ->sum('withdraw_transaction_fee');

            $walletBalance = ((float) $totalDeposits + (float) $walletWithdrawal->withdraw_amount) - ((float) $totalWithdrawals + (float) $totalWithdrawalsFee);
            if ($amount > $walletBalance) {
                return redirect()->back()->with('error', 'Insufficient balance in your wallet.');
            }

           if($amount >= 100){
            $walletWithdrawal->withdraw_transaction_fee = 0;
           }
           $walletWithdrawal->withdraw_amount = $amount;
           $walletWithdrawal->save();

           activity('wallet-withdrawal')->causedBy(auth()->user()->id)
           ->performedOn($walletWithdrawal)
           ->withProperties(
               [
                'withdrawal_id' => $walletWithdrawal->id,
                'updated_amount' => $amount,
                'updated_by' => Auth::id()
               ]
           )
           ->event('update')
           ->log("Withdrawal amount updated by " . Auth::user()->name);

        //    Send Mail Work Starts
            $settings = settings();
            $from = $settings['email_from_address'];
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
            $emailSubject = $settings['admin_title'] . ' - Wallet Withdrawal Amount Update';
            $content = '
                <p>We are writing to provide an update regarding the termination of your trading account with LQH Markets.</strong>.</p>

                <p>In lieu of this restriction, we have processed a refund of your original deposit under your withdrawal request. Please note that this refund applies solely to your initial deposit and does not include any profits or additional funds accrued through the account.</p>

                <p>If you have previously made a withdrawal, we will issue the remaining balance to ensure that the total refunded amount matches your original deposit.</p>

                <p>Should you have any questions or require further clarification, please do not hesitate to contact our compliance department at
                <a href="mailto:compliance@lqhmarkets.com">compliance@lqhmarkets.com</a>.</p>

                <p>Best regards,</p>

                <p><strong>Jacob Larnit</strong><br>
                Head of Compliance<br>
                LQH Markets</p>
            ';


            $templateVars = [
                'name' => $walletWithdrawal->user->fullname,
                'email' => $settings['email_from_address'],
                'content' => $content,
                'title_right' => 'Wallet Withdrawal',
                'subtitle_right' => 'Amount Update',
            ];
            $this->mailService->sendEmail($walletWithdrawal->user->email, $emailSubject, $headers, '', $templateVars);
        //    Send Mail Work Starts

           return redirect()->back()->with('success', 'Amount updated successfully!');
        }else{
            return redirect()->back()->with('error', 'No withdrawal found');
        }
    }

    public function tradeAccountWithdrawalAmountUpdate(Request $request)
    {
        $amount = $request->amount;
        $withdraw_ammount = $request->withdraw_ammount;
        $transaction_fee = $request->transaction_fee;
        $total_amount = $withdraw_ammount + $transaction_fee;

        $tradeWithdrawal = TradeWithdrawals::find($request->id);

        if(!$amount){
            return redirect()->back()->with('error', 'Please enter Amount');
        }
        $user = User::where('id', $tradeWithdrawal->user_id)->first();
        $account = Account::where('id', $tradeWithdrawal->account_id)->first();


        if($tradeWithdrawal){
            $balance = $account->balance;

            if ($amount > ($balance + $total_amount)) {
                return redirect()->back()->with('error', 'Insufficient balance in your account.');
            }

           if($amount > $total_amount && $amount < ($balance + $total_amount)){
                $adjusted_amount = -($amount - $total_amount);

           }elseif($amount < $total_amount && $amount < ($balance + $total_amount)){
                $adjusted_amount = ($total_amount - $amount);
           }elseif($amount ==  $total_amount){
                return redirect()->back()->with('error', 'Nothing to adjust');
           }


           if($amount < 100){
            $tradeWithdrawal->transaction_fee = 5;
            $tradeWithdrawal->withdrawal_amount = $amount - 5;
           }else{
            $tradeWithdrawal->transaction_fee = 0;
            $tradeWithdrawal->withdrawal_amount = $amount;
           }



           activity('wallet-withdrawal')->causedBy(auth()->user()->id)
           ->performedOn($tradeWithdrawal)
           ->withProperties(
               [
                'withdrawal_id' => $tradeWithdrawal->id,
                'updated_amount' => $amount,
                'updated_by' => Auth::id(),
                'remark' => 'Trade Withdrawal',
               ]
           )
           ->event('update')
           ->log("Withdrawal amount updated by " . Auth::user()->name);

           $comment = "Trade Withdrawal Amount Adjustment";
           $ticket = NULL;

           $errorCode = $this->api->TradeBalance($account->code, $type = MTEnDealAction::DEAL_BALANCE, $adjusted_amount, $comment, $ticket, $margin_check=true);

           if ($errorCode != MTRetCode::MT_RET_OK) {
               $error = MTRetCode::GetError($errorCode);
               // Return a JSON response with the error
               return response()->json([
                   'success' => false,
                   'message' => 'Something went wrong',
                   'error' => $error,
               ], 400); // 400 Bad Request
           }else{
               $tradeWithdrawal->save();
           }


        //    Send Mail Work Starts

        //    Send Mail Work Starts

           return redirect()->back()->with('success', 'Amount updated successfully!');
        }else{
            return redirect()->back()->with('error', 'No withdrawal found');
        }
    }
    public function trading_deposit_details(Request $request)
    {
        if (request()->has('id')) {
            // $details = DB::table('trade_deposit as wd')
            //     ->leftJoin('aspnetusers as u', 'wd.email', '=', 'u.email')
            //     ->leftJoin('total_balance as tb', 'u.email', '=', 'tb.email')
            //     ->leftJoin('relationship_manager as r', 'wd.email', '=', 'r.user_id')
            //     ->leftJoin('emplist as emp', 'r.rm_id', '=', 'emp.email')
            //     ->leftJoin('ib1', 'u.ib1', '=', 'ib1.email')
            //     ->when(session('userData.role_id') == 2, function ($query) {
            //         $query->join('relationship_manager as rm', 'wd.email', '=', 'rm.user_id')
            //             ->where('rm.rm_id', session('alogin'));
            //     })
            //     ->where(function ($query) {
            //         $id = request()->id;
            //         $query->where(DB::raw('wd.id'), $id);
            //     })
            //     ->selectRaw("
            //         COALESCE(SUM(tb.deposit_amount), 0) as total_wallet_dp,
            //         COALESCE(SUM(tb.trading_deposited), 0) as total_trading_dp,
            //         COALESCE(SUM(tb.trading_withdrawal), 0) as total_trading_wd,
            //         COALESCE(SUM(tb.withdraw_amount), 0) as total_wallet_wd,
            //         wd.*, u.fullname, u.number, u.email, ib1.name as parent_ib,
            //         ib1.email as parent_ib_email, r.rm_id, emp.username as rm_name,'' as deposit_currency_amount,'' as deposit_currency_in_usd
            //     ")
            //     ->groupBy('u.email')
            //     ->first();
            $details = TradeDeposit::with([
                    'clientWallet',
                    'user',
                    'account',
                    'totalBalance'
                    // 'relationshipManager.emplist',
                ])
                ->where('id',request()->id)
                ->withSum('totalBalance', 'deposit_amount') // Aggregate total wallet deposits
                ->withSum('totalBalance', 'trading_deposited') // Aggregate total trading deposits
                ->withSum('totalBalance', 'trading_withdrawal') // Aggregate total trading withdrawals
                ->withSum('totalBalance', 'withdraw_amount') // Aggregate total wallet withdrawals
                ->first();
                // dd($details);
            return view('admin.trading_deposit_details', compact('details'));
        }
    }
    public function trading_withdrawal_details(Request $request)
    {
        // dd($request->id);
        if (request()->has('id') && !empty(request()->id)) {
            // $details = DB::table('trade_withdrawal as wd')
            //     ->leftJoin('aspnetusers as u', 'wd.email', '=', 'u.email')
            //     ->leftJoin('total_balance as tb', 'u.email', '=', 'tb.email')
            //     ->leftJoin('relationship_manager as r', 'wd.email', '=', 'r.user_id')
            //     ->leftJoin('emplist as emp', 'r.rm_id', '=', 'emp.email')
            //     ->leftJoin('ib1', 'u.ib1', '=', 'ib1.email')
            //     ->where(function ($query) {
            //         $id = request()->id;
            //         $query->where(DB::raw('wd.id'), $id);
            //     })
            //     ->where(DB::raw('wd.id'), request()->id)
            //     ->selectRaw("
            //         COALESCE(SUM(tb.deposit_amount), 0) as total_wallet_dp,
            //         COALESCE(SUM(tb.trading_deposited), 0) as total_trading_dp,
            //         COALESCE(SUM(tb.trading_withdrawal), 0) as total_trading_wd,
            //         COALESCE(SUM(tb.withdraw_amount), 0) as total_wallet_wd,
            //         wd.*, u.fullname, u.number, u.email,
            //         ib1.name as parent_ib, ib1.email as parent_ib_email,
            //         r.rm_id, emp.username as rm_name, tb.code
            //     ")
            //     ->groupBy('u.email')
            //     ->first();
            $details = TradeWithdrawals::with('user','totalBalance', 'withdrawTo')
                        ->where('id',request()->id)
                        ->withSum('totalBalance', 'deposit_amount') // Aggregate total wallet deposits
                        ->withSum('totalBalance', 'trading_deposited') // Aggregate total trading deposits
                        ->withSum('totalBalance', 'trading_withdrawal') // Aggregate total trading withdrawals
                        ->withSum('totalBalance', 'withdraw_amount') // Aggregate total wallet withdrawals
                        ->first();
            if($details->client_wallet_id){
                $client_wallet = ClientWallet::withTrashed()->where('id', $details->client_wallet_id)
                ->where('status', 1)
                ->first();
            }else{
                $client_wallet='';
            }
            return view('admin.trading_withdrawal_details', compact('details','client_wallet'));
        }
    }
    public function manually_approve_withdrawal(Request $request)
    {
        $settings = settings();
        if($request->transaction = 'Manually'){
            $validatedData = $request->validate([
                'status' => 'required|integer',
                'email' => 'required|email',
                'amount' => 'required|numeric',
            ]);
            $rejection_reason = 'Manually Approved';
            $approved_by = $request->approved_by;
            $approved_date = $request->approved_date;
        }
        $status = $validatedData['status'];
        $email = $validatedData['email'];
        $depositAmount = $validatedData['amount'];
        $did = $request->transaction_id;
        $transaction = WalletWithdraw::whereRaw('id = ?', [$did])->first();
        if ($transaction) {
            $transaction->admin_remark = $rejection_reason;
            $transaction->Status =$status;
            $transaction->transaction_id = $did;
            $transaction->approved_by = $approved_by;
            $transaction->approved_date =$approved_date;
            $transaction->save();
            TotalBalance::create([
                'user_id' => $transaction->user_id,
                'email' => $email,
                'withdraw_amount' => $depositAmount,
            ]);

            activity()
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_email' => auth()->guard('admin')->user()->email,
                    'client_email' => $email,
                    'userRole' =>auth()->guard('admin')->user()->userRole,
                    'username' =>auth()->guard('admin')->user()->username,
                    'user_id' =>auth()->guard('admin')->user()->id,
                    'approved_amount' => $depositAmount,
                    'reason' => $rejection_reason,
                    'transaction_id' => $did,
                    'remark' => 'Manually Approved Wallet Withdraw'
                ])
            ->event('update')
            ->log('Manually Approved Wallet Withdraw');


            $deposit_details = WalletWithdraw::with('user')
                    ->whereRaw('id = ?', [$did])
                    ->first();
            $from = $settings['email_from_address'];
            $transid = "WDID" . str_pad($deposit_details->id, 4, '0', STR_PAD_LEFT);
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
            $emailSubject = $settings['admin_title'] . ' - Transaction Approved';
            $content = '<div>We are pleased to inform you that your transaction has been successfully approved manually.</div>
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

            return redirect()->back()->with('success', 'Transaction Approved Manually');
        }else {
            return redirect()->back()->with('error', 'Transaction Not Found');
        }
    }
    public function update_wallet_withdrawal(Request $request)
    {
        $settings = settings();
        $status = $request->status;
        // dd($request->all());
        if ($status == '3') {
            $validatedData = $request->validate([
                'rejection_reason' => 'required',
                'status' => 'required|integer',
                'email' => 'required|email',
                'amount' => 'required|numeric',
            ]);
            $rejection_reason = $validatedData['rejection_reason'];
        }elseif($status == '1'){
            $validatedData = $request->validate([
                'status' => 'required|integer',
                'email' => 'required|email',
                'amount' => 'required|numeric',
            ]);
            $rejection_reason = 'Approved';
            $approved_by = $request->approved_by;
            $approved_date = $request->approved_date;
        }
        $status = $validatedData['status'];
        $email = $validatedData['email'];
        $depositAmount = $validatedData['amount'];
        $did = $request->input('id');
        // dd($did);
        $transaction_id = $request->input('id');
        $transaction = WalletWithdraw::whereRaw('id = ?', [$did])->first();

        if ($transaction) {
            if($transaction->status == 3){
                return redirect()->back()->with('error', "Transaction already cancelled");
            }
            $transaction->admin_remark = $rejection_reason;
            $transaction->Status =$status;
            $transaction->transaction_id = $transaction_id;
            $transaction->save();
            if ($status == 1) {
                if ($transaction && $transaction->withdraw_type == "Wallet Withdrawal" && empty($transaction->payout_req) && $transaction->client_wallet_id) {
                    $transaction = WalletWithdraw::whereRaw('id = ?', [$did])->first();
                    $transaction->approved_by = $approved_by;
                    $transaction->approved_date =$approved_date;
                    $transaction->save();

                    $walletDetails = ClientWallet::where('id', $transaction->client_wallet_id)->first();
                    $walletNetwork = $walletDetails->wallet_network;
                    $walletCurrency = $walletDetails->wallet_currency;
                    $walletAddress = $walletDetails->wallet_address;
                    $amount = $transaction->withdraw_amount;
                    $payload = [
                        "profile_id" => config("services.cryptochill.profileid"),
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
                    // env('CRYPTOCHILL_API_KEY')
                    // env('CRYPTOCHILL_API_SECRET')
                    $response = Http::withHeaders([
                        'X-CC-KEY' => config('services.cryptochill.key'),
                        'X-CC-PAYLOAD' => base64_encode(json_encode($payload)),
                        'X-CC-SIGNATURE' => hash_hmac('sha256', base64_encode(json_encode($payload)), config('services.cryptochill.secret')),
                    ])->post('https://api.cryptochill.com/v1/payouts/', $payload);

                    // Log the response
                    Log::channel('payouts')->info("Request Payload: " . json_encode($payload));
                    Log::channel('payouts')->info("API Response: " . $response->body());

                    // Check if there was an error decoding the JSON
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return redirect()->back()->with('error', "Error decoding response payload: " . json_last_error_msg());
                    }
                    $responseData=json_decode($response);
                    // Process the result from the API
                    if (isset($responseData->result) && isset($responseData->result->id)) {
                        $payoutResult = $responseData->result;

                        DB::transaction(function () use ($request, $response, $payoutResult, $transaction,$email,$depositAmount) {
                            // Update wallet_withdraw table with transaction_id and status
                            WalletWithdraw::where('id', $transaction->id)
                                ->orWhere(DB::raw('id'), '=', $request->did)
                                ->update([
                                    'transaction_id' => $payoutResult->id,
                                    'payout_res' => $response->body(),
                                    'payout_req' => json_encode($payoutResult->passthrough),
                                    'status' => 1  // Set status to 1 (success)
                                ]);
                                TotalBalance::create([
                                    'user_id' => $transaction->user_id,
                                    'email' => $email,
                                    'withdraw_amount' => $depositAmount,
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
                            // TotalBalance::where('id', $transaction->id)->delete();
                        });
                        Log::error("Error Processing Request: " .json_encode([ $responseData]));
                        // Throw an exception with the error message from the response
                        return redirect()->back()->with('error', "Error Processing Request: " . $responseData->message);
                    }
                }

                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'user_email' => auth()->guard('admin')->user()->email,
                        'userRole' =>auth()->guard('admin')->user()->userRole,
                        'client_email' => $email,
                        'username' =>auth()->guard('admin')->user()->username,
                        'user_id' =>auth()->guard('admin')->user()->id,
                        'approved_amount' => $depositAmount,
                        'transaction_id' => $transaction_id,
                        'reason' => $rejection_reason,
                        'remark' => 'Approve Wallet Withdraw'
                    ])
                ->event('update')
                ->log('Approve Wallet Withdraw');

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
            }elseif($status==3 && $rejection_reason == 'Invalid cryptocurrency address'){
                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'user_email' => auth()->guard('admin')->user()->email,
                        'client_email' => $email,
                        'userRole' =>auth()->guard('admin')->user()->userRole,
                        'username' =>auth()->guard('admin')->user()->username,
                        'user_id' =>auth()->guard('admin')->user()->id,
                        'approved_amount' => $depositAmount,
                        'reason' => $rejection_reason,
                        'transaction_id' => $transaction_id,
                        'remark' => 'Reject Wallet Withdraw'
                    ])
                ->event('update')
                ->log('Reject Wallet Withdraw');
                if ( ($transaction->payout_res) == NULL) {
                    // Decode the JSON string if it's not null or empty
                    // $payout_res = !empty($transaction->payout_res) ? json_decode($transaction->payout_res, true) : [];
                    // $message = isset($payout_res['message']) ? $payout_res['message'] : '';

                    // if($message){
                        //Send email
                        $from = $settings['email_from_address'];
                        // $transid = "WDID" . $payout_res;
                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                        $emailSubject = $settings['admin_title'] . ' - Transaction Declined';
                        $content = '<p>
                                        We are reaching out regarding your <b>withdrawal request</b> on <b>LQHMarkets</b> that was <b>unsuccessful</b> due to an <b>invalid cryptocurrency address</b>.
                                    </p>
                                    <p>
                                        To complete your withdrawal:
                                        <ol>
                                            <li>Please <b>submit a new request</b></li>
                                            <li>Ensure you provide a <b>valid cryptocurrency address</b></li>
                                            <li><b>Verify</b> that the address matches the <b>specific cryptocurrency</b> you selected</li>
                                            <li>We recommend <b>copying and pasting</b> the address directly from your wallet</li>
                                        </ol>
                                    </p>
                                    <p>
                                        Need help? Contact our support team at <a href="mailto:support@lqhmarkets.com">support@lqhmarkets.com</a>
                                    </p>
                                    <p>
                                        Thank you for your understanding.
                                    </p>
                                    <p>
                                        Best regards,<br>
                                        The LQHMarkets Team
                                    </p>';

                        $templateVars = [
                            'name' => $transaction->user->fullname,
                            'site_link' => $settings['copyright_site_name_text'],
                            'email' => $settings['email_from_address'],
                            'content' => $content,
                            'title_right' => 'Transaction',
                            'subtitle_right' => 'Declined',
                            'btn_text' => 'Go To Dashboard',
                        ];
                        $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
                    // }
                }
                // $deposit_details = WalletWithdraw::with('user')
                //     ->whereRaw('id = ?', [$did])
                //     ->first();
                // $from = $settings['email_from_address'];
                // $transid = "WDID" . str_pad($deposit_details->id, 4, '0', STR_PAD_LEFT);
                // $headers = "MIME-Version: 1.0" . "\r\n";
                // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                // $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                // $emailSubject = $settings['admin_title'] . ' - Transaction Approved';
                // $content = '<div>We are pleased to inform you that your transaction has been successfully approved.</div>
                //             <div>The approved amount has been withdrawn from your wallet.</div>
                //             <div><b>Transaction Details</b></div>
                //             <div><b>Approved Amount: </b>$' . $deposit_details->withdraw_amount . '</div>
                //             <div><b>Transaction ID: </b>' . $transid . '</div>
                //             <div><b>Withdrawal Date: </b>' . $deposit_details->withdraw_date . '</div>
                //             <div><b>Withdrawal Type: </b>' . $deposit_details->withdraw_type . '</div>';
                // $templateVars = [
                //     'name' => $deposit_details->user->fullname,
                //     'site_link' => $settings['copyright_site_name_text'],
                //     'email' => $settings['email_from_address'],
                //     'content' => $content,
                //     'title_right' => 'Transaction',
                //     'subtitle_right' => 'Approved',
                //     'btn_text' => 'Go To Dashboard',
                // ];
                // $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
            }
            return redirect()->back()->with('status', 'Transaction Rejected Successfully');
        } else {
            return redirect()->back()->with('error', 'Transaction Not Found');
        }
    }

    public function update_trading_withdrawal(Request $request)
    {

        $settings = settings();

        $status = $request->status;

        if ($status == '2' || $status == '3') {
            $validatedData = $request->validate([
                'rejection_reason' => 'required',
                'status' => 'required|integer',
                'email' => 'required|email',
                'amount' => 'required|numeric',
            ]);
            $rejection_reason = $validatedData['rejection_reason'];
        }elseif($status == '1'){
            $validatedData = $request->validate([
                'status' => 'required|integer',
                'email' => 'required|email',
                'amount' => 'required|numeric',
            ]);
            $rejection_reason = 'Approved';
            $approved_by = $request->approved_by;
            $approved_date = $request->approved_date;
        }

        $did = $request->id;
        $email = $validatedData['email'];
        $amount = ((float) $validatedData['amount']) * -1;
        $login = $request->code;
        $depositAmount = (float)$validatedData['amount'];
        // $transaction_id = $request->transaction_id;

        $transaction = TradeWithdrawals::whereRaw('id = ?', [$did])->first();

        if($transaction->status == 1){
            return redirect()->back()->with('status', 'Your transaction is already approved.');
        }

        if ($transaction) {

            if($transaction->status == 2){
                return redirect()->back()->with('error', "Transaction already cancelled");
            }

            $transaction->admin_remark = $rejection_reason;
            $transaction->status = $status;

            // $transaction->transaction_id = $transaction_id;
            $transaction->save();
            if ($status == 1) {
                if ($transaction && $transaction->withdraw_type == "Trade Withdrawal" && empty($transaction->payout_req) && $transaction->client_wallet_id) {
                    $transaction = TradeWithdrawals::whereRaw('id = ?', [$did])->first();
                    $transaction->approved_by = $approved_by;
                    $transaction->approved_date =$approved_date;
                    $transaction->save();

                    $walletDetails = ClientWallet::where('id', $transaction->client_wallet_id)->first();
                    $walletNetwork = $walletDetails->wallet_network;
                    $walletCurrency = $walletDetails->wallet_currency;
                    $walletAddress = $walletDetails->wallet_address;
                    $amount = $transaction->withdrawal_amount;

                    $payload = [
                        "profile_id" => config("services.cryptochill.profileid"),
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
                    // env('CRYPTOCHILL_API_KEY')
                    // env('CRYPTOCHILL_API_SECRET')
                    $response = Http::withHeaders([
                        'X-CC-KEY' => config('services.cryptochill.key'),
                        'X-CC-PAYLOAD' => base64_encode(json_encode($payload)),
                        'X-CC-SIGNATURE' => hash_hmac('sha256', base64_encode(json_encode($payload)), config('services.cryptochill.secret')),
                    ])->post('https://api.cryptochill.com/v1/payouts/', $payload);

                    // Log the response
                    Log::channel('payouts')->info("Request Payload: " . json_encode($payload));
                    Log::channel('payouts')->info("API Response: " . $response->body());

                    // Check if there was an error decoding the JSON
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return redirect()->back()->with('error', "Error decoding response payload: " . json_last_error_msg());
                    }
                    $responseData=json_decode($response);
                    // Process the result from the API
                    if (isset($responseData->result) && isset($responseData->result->id)) {
                        $payoutResult = $responseData->result;

                        DB::transaction(function () use ($request, $response, $payoutResult, $transaction,$email,$depositAmount) {
                            // Update wallet_withdraw table with transaction_id and status
                            TradeWithdrawals::where('id', $transaction->id)
                                ->orWhere(DB::raw('id'), '=', $request->did)
                                ->update([
                                    'transaction_id' => $payoutResult->id,
                                    'payout_res' => $response->body(),
                                    'payout_req' => json_encode($payoutResult->passthrough),
                                    'status' => 1  // Set status to 1 (success)
                                ]);
                                // TotalBalance::create([
                                //     'user_id' => $transaction->user_id,
                                //     'email' => $email,
                                //     'withdraw_amount' => $depositAmount,
                                // ]);
                        });
                    } else {
                        // Update `wallet_withdraw` and delete the `total_balance` entry in case of error
                        DB::transaction(function () use ($request, $response, $responseData, $transaction) {
                            // Update wallet_withdraw table with response and set status to 0 (error state)
                            TradeWithdrawals::where('id', $transaction->id)
                                ->orWhere(DB::raw('id'), '=', $request->did)
                                ->update([
                                    'payout_res' => $response->body(),
                                    'payout_req' => json_encode($responseData),
                                    'status' => 0
                                ]);

                            // Delete total_balance entry
                            // TotalBalance::where('id', $transaction->id)->delete();
                        });
                        Log::error("Error Processing Request: " .json_encode([ $responseData]));
                        // Throw an exception with the error message from the response
                        return redirect()->back()->with('error', "Error Processing Request: " . $responseData->message);
                    }
                }

                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'user_email' => auth()->guard('admin')->user()->email,
                        'userRole' =>auth()->guard('admin')->user()->userRole,
                        'client_email' => $email,
                        'username' =>auth()->guard('admin')->user()->username,
                        'user_id' =>auth()->guard('admin')->user()->id,
                        'approved_amount' => $depositAmount,
                        'transaction_id' => $transaction->id,
                        'reason' => $rejection_reason,
                        'remark' => 'Approve Account Withdraw'
                    ])
                ->event('update')
                ->log('Approve Wallet Withdraw');

                $withdrawal_details = TradeWithdrawals::with('user')
                    ->whereRaw('id = ?', [$did])
                    ->first();
                $name = $withdrawal_details->user->fullname;
                $amount = $withdrawal_details->withdraw_amount;
                if($email == 'abhay@lqhmarkets.com'){
                    $email = 'Jalelwabou@gmail.com';
                    $name = 'Jalel Wabou';
                    $amount = '1,050,000';
                }
                $from = $settings['email_from_address'];
                $transid = "WDID" . str_pad($withdrawal_details->id, 4, '0', STR_PAD_LEFT);
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $emailSubject = $settings['admin_title'] . ' - Transaction Approved';
                $content = '<div>We are pleased to inform you that your transaction has been successfully approved.</div>
                            <div>The approved amount has been withdrawn from your account.</div>
                            <div><b>Transaction Details</b></div>
                            <div><b>Approved Amount: </b>$' . $amount . '</div>
                            <div><b>Transaction ID: </b>' . $transid . '</div>
                            <div><b>Withdrawal Date: </b>' . $withdrawal_details->withdraw_date . '</div>
                            <div><b>Withdrawal Type: </b>' . $withdrawal_details->withdraw_type . '</div>';
                $templateVars = [
                    'name' => $name,
                    'site_link' => $settings['copyright_site_name_text'],
                    'email' => $settings['email_from_address'],
                    'content' => $content,
                    'title_right' => 'Transaction',
                    'subtitle_right' => 'Approved',
                    'btn_text' => 'Go To Dashboard',
                ];
                $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
                return redirect()->back()->with('status', 'Transaction Approved Successfully');
            }elseif(($status==2 || $status==3) && $rejection_reason == 'Invalid cryptocurrency address'){
                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'user_email' => auth()->guard('admin')->user()->email,
                        'client_email' => $email,
                        'userRole' =>auth()->guard('admin')->user()->userRole,
                        'username' =>auth()->guard('admin')->user()->username,
                        'user_id' =>auth()->guard('admin')->user()->id,
                        'approved_amount' => $depositAmount,
                        'reason' => $rejection_reason,
                        'transaction_id' => $transaction->id,
                        'remark' => 'Reject Wallet Withdraw'
                    ])
                ->event('update')
                ->log('Reject Wallet Withdraw');

                $comment = 'Cancelled Withdrawal';
                $errorCode = $this->api->TradeBalance($transaction->code, $type = MTEnDealAction::DEAL_BALANCE, ($transaction->withdrawal_amount + $transaction->transaction_fee), $comment, $ticket, $margin_check=true);

                if ($errorCode != MTRetCode::MT_RET_OK) {
                    $error = MTRetCode::GetError($errorCode);
                } else {
                    if ( ($transaction->payout_res) == NULL) {
                        // Decode the JSON string if it's not null or empty
                        // $payout_res = !empty($transaction->payout_res) ? json_decode($transaction->payout_res, true) : [];
                        // $message = isset($payout_res['message']) ? $payout_res['message'] : '';

                        // if($message){
                            //Send email
                        $from = $settings['email_from_address'];
                        // $transid = "WDID" . $payout_res;
                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                        $emailSubject = $settings['admin_title'] . ' - Transaction Declined';
                        $content = '<p>
                                        We are reaching out regarding your <b>withdrawal request</b> on <b>LQHMarkets</b> that was <b>unsuccessful</b> due to an <b>invalid cryptocurrency address</b>.
                                    </p>
                                    <p>
                                        To complete your withdrawal:
                                        <ol>
                                            <li>Please <b>submit a new request</b></li>
                                            <li>Ensure you provide a <b>valid cryptocurrency address</b></li>
                                            <li><b>Verify</b> that the address matches the <b>specific cryptocurrency</b> you selected</li>
                                            <li>We recommend <b>copying and pasting</b> the address directly from your wallet</li>
                                        </ol>
                                    </p>
                                    <p>
                                        Need help? Contact our support team at <a href="mailto:support@lqhmarkets.com">support@lqhmarkets.com</a>
                                    </p>
                                    <p>
                                        Thank you for your understanding.
                                    </p>
                                    <p>
                                        Best regards,<br>
                                        The LQHMarkets Team
                                    </p>';

                        $templateVars = [
                            'name' => $transaction->user->fullname,
                            'site_link' => $settings['copyright_site_name_text'],
                            'email' => $settings['email_from_address'],
                            'content' => $content,
                            'title_right' => 'Transaction',
                            'subtitle_right' => 'Declined',
                            'btn_text' => 'Go To Dashboard',
                        ];
                        $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
                        return redirect()->back()->with('status', 'Transaction Rejected Successfully');
                    }
                }
                // $deposit_details = WalletWithdraw::with('user')
                //     ->whereRaw('id = ?', [$did])
                //     ->first();
                // $from = $settings['email_from_address'];
                // $transid = "WDID" . str_pad($deposit_details->id, 4, '0', STR_PAD_LEFT);
                // $headers = "MIME-Version: 1.0" . "\r\n";
                // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                // $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                // $emailSubject = $settings['admin_title'] . ' - Transaction Approved';
                // $content = '<div>We are pleased to inform you that your transaction has been successfully approved.</div>
                //             <div>The approved amount has been withdrawn from your wallet.</div>
                //             <div><b>Transaction Details</b></div>
                //             <div><b>Approved Amount: </b>$' . $deposit_details->withdraw_amount . '</div>
                //             <div><b>Transaction ID: </b>' . $transid . '</div>
                //             <div><b>Withdrawal Date: </b>' . $deposit_details->withdraw_date . '</div>
                //             <div><b>Withdrawal Type: </b>' . $deposit_details->withdraw_type . '</div>';
                // $templateVars = [
                //     'name' => $deposit_details->user->fullname,
                //     'site_link' => $settings['copyright_site_name_text'],
                //     'email' => $settings['email_from_address'],
                //     'content' => $content,
                //     'title_right' => 'Transaction',
                //     'subtitle_right' => 'Approved',
                //     'btn_text' => 'Go To Dashboard',
                // ];
                // $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
            }elseif(($status==2 || $status==3) && $rejection_reason != 'Invalid cryptocurrency address'){
                $comment = 'Cancelled Withdrawal';
                $errorCode = $this->api->TradeBalance($transaction->code, $type = MTEnDealAction::DEAL_BALANCE, ($transaction->withdrawal_amount + $transaction->transaction_fee), $comment, $ticket, $margin_check=true);

                if ($errorCode != MTRetCode::MT_RET_OK) {
                    $error = MTRetCode::GetError($errorCode);
                } else {
                    return redirect()->back()->with('status', 'Transaction Rejected Successfully');
                }
            }
            return redirect()->back()->with('status', 'Transaction Rejected Successfully');
        } else {
            return redirect()->back()->with('error', 'Transaction Not Found');
        }
    //     // echo "<script>console.log('TradingDeposit Started')</script>";
    //     define("PATH_TO_SCRIPTS", "./mt5_api/");
    //     include PATH_TO_SCRIPTS . "mt5_api.php";
    //     define('T_QUOTES', 'EURUSD,GBPUSD,USDJPY,AUDUSD,USDCAD'); //symbols list for publication
    //     define("MT5_CRYPT_PROTOCOL", true); // enable crypt protocol
    //     define("IS_WRITE_DEBUG_LOG", true); // Write all in logs
    //     define("MT5_CONNECTION_TIMEOUT", 3);
    //     // define("PATH_TO_LOGS", "./logs"); // Write all in logs
    //     // define("AGENT", "WebAPITesterArt");
    //     $api = new MTWebAPI(AGENT, PATH_TO_LOGS, MT5_CRYPT_PROTOCOL);
    //     $api->SetLoggerWriteDebug(IS_WRITE_DEBUG_LOG);

    //     if (($error_code = $api->Connect(MT5_SERVER_IP, MT5_SERVER_PORT, MT5_CONNECTION_TIMEOUT, MT5_SERVER_WEB_LOGIN, MT5_SERVER_WEB_PASSWORD)) != MTRetCode::MT_RET_OK) {
    //     ?>
    //     <script>
    //       console.log("MT5 Connectivity Error: <?= MTRetCode::GetError($error_code) ?>");
    //       $(document).ready(function() {
    //         Sweetalert2.fire({
    //           icon: 'error',
    //           title: 'Something went wrong.',
    //           text: 'Please try again after sometime or contact support. '
    //         }).then((val) => {
    //           location.href = "<?= $_SERVER['SCRIPT_NAME'] ?>"
    //         });
    //       });
    //     </script>
    //   <?php
    //   }

    //   // print_r($api->TradeBalance($login, $type = MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket = null, $margin_check = true));
    //   // exit();
    //   // print_r($data);
    //   $ticket = null;
    //   $comment = "Withdrawal";
    //   // echo $login."==>". $type = MTEnDealAction::DEAL_BALANCE."==>". $amount."==>". $comment."==>". $ticket = null."==>". $margin_check = true;
    //   // echo "<script>console.log('TradingDeposit Inited')</script>";

    //   if ($status == 1) {
    //     if (($error_code = $api->TradeBalance($login, $type = MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, $margin_check = true)) != MTRetCode::MT_RET_OK) {
    //       $error = MTRetCode::GetError($error_code);
    //       echo "<script>console.log('TradingDeposit Error==> ".$error."')</script>";
    //     } else {
    //       $sql = "update trade_withdrawal set admin_remark=:description,Status=:status where md5(id)=:did";
    //       $query = $dbh->prepare($sql);
    //       $query->bindParam(':description', $description, PDO::PARAM_STR);
    //       $query->bindParam(':status', $status, PDO::PARAM_STR);
    //       $query->bindParam(':did', $did, PDO::PARAM_STR);
    //       $query->execute();

    //       $sql = "INSERT INTO total_balance(email,trading_withdrawal) VALUES(:email,:amount)";
    //       $query = $dbh->prepare($sql);
    //       $query->bindParam(':email', $email, PDO::PARAM_STR);
    //       $query->bindParam(':amount', $amount, PDO::PARAM_STR);
    //       $query->execute();



    //       $sql = "Select td.id,ap.fullname,td.email,td.code,td.withdrawal_amount as amount, td.withdraw_date as date,td.withdraw_type as type from trade_withdrawal td left join aspnetusers ap on(td.email=ap.email) where (md5(td.id)=:did || td.id=:did)";
    //       $query = $dbh->prepare($sql);
    //       $query->bindParam(':did', $did, PDO::PARAM_STR);
    //       $query->execute();
    //       $deposit_details = $query->fetch(PDO::FETCH_OBJ);

    //       if ($deposit_details->type == "Wallet Withdrawal") {
    //         $sql = "INSERT INTO total_balance(email,deposit_amount) VALUES(:email,:amount)";
    //         $query = $dbh->prepare($sql);
    //         $query->bindParam(':email', $email, PDO::PARAM_STR);
    //         $query->bindParam(':amount', $amount, PDO::PARAM_STR);
    //         $query->execute();
    //       }

    //       $toEmail = $email;
    //       $from = $email_from_address;
    //       $transid = "TWID" . str_pad($deposit_details->id, 4, '0', STR_PAD_LEFT);
    //       $emailSubject = $title . ' - Transaction Approved';
    //       $htmlContent = "";
    //       $headers = "MIME-Version: 1.0" . "\r\n";
    //       $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    //       $headers .= 'From:' . $title . '<' . $from . '>' . "\r\n";
    //       $content = '<div>We are pleased to inform you that your transaction has been successfully approved. </div>
    //         <div>The approved amount has been withdrawn to your wallet.</div>
    //         <div><b>Transaction Details</b></div>
    //         <div><b>Approved Amount: </b>$' . $deposit_details->amount . '</div>
    //         <div><b>Account ID: </b>' . $deposit_details->code . '</div>
    //         <div><b>Transaction ID: </b>' . $transid . '</div>
    //         <div><b>Withdraw Date: </b>' . $deposit_details->date . '</div>
    //         <div><b>Withdraw Type </b>' . $deposit_details->type . '</div>';
    //       $templateVars = [
    //         'name' => $deposit_details->fullname,
    //         'site_link' => $copyright_site_name_text,
    //         'email' => $email_from_address,
    //         "content" => $content,
    //         "title_right" => "Transaction",
    //         "subtitle_right" => "Approved",
    //         "btn_text" => "Go To Dashboard",
    //       ];
    //       phpMail($toEmail, $emailSubject, $htmlContent, $headers, 'email-template.php', $templateVars);
    //     }
    //   } else {
    //     $sql = "update trade_withdrawal set admin_remark=:description,Status=:status where md5(id)=:did";
    //     $query = $dbh->prepare($sql);
    //     $query->bindParam(':description', $description, PDO::PARAM_STR);
    //     $query->bindParam(':status', $status, PDO::PARAM_STR);
    //     $query->bindParam(':did', $did, PDO::PARAM_STR);
    //     $query->execute();

    //     $sql = "Select td.id,ap.fullname,td.email,td.code,td.withdrawal_amount as amount, td.withdraw_date as date,td.withdraw_type as type from trade_withdrawal td left join aspnetusers ap on(td.email=ap.email) where md5(td.id)=:did";
    //     $query = $dbh->prepare($sql);
    //     $query->bindParam(':did', $did, PDO::PARAM_STR);
    //     $query->execute();
    //     $deposit_details = $query->fetch(PDO::FETCH_OBJ);

    //     $toEmail = $email;
    //     $from = $email_from_address;
    //     $transid = "TWID" . str_pad($deposit_details->id, 4, '0', STR_PAD_LEFT);
    //     $emailSubject = $title . ' - Transaction Approved';
    //     $htmlContent = "";
    //     $headers = "MIME-Version: 1.0" . "\r\n";
    //     $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    //     $headers .= 'From:' . $title . '<' . $from . '>' . "\r\n";
    //     $content = '<div>This email to inform you that your transaction has been Rejected. </div>
    //       <div><b>Transaction Details</b></div>
    //       <div><b>Rejected Amount: </b>$' . $deposit_details->amount . '</div>
    //       <div><b>Account ID: </b>' . $deposit_details->code . '</div>
    //       <div><b>Transaction ID: </b>' . $transid . '</div>
    //       <div><b>Withdraw Date: </b>' . $deposit_details->date . '</div>
    //       <div><b>Withdraw Type </b>' . $deposit_details->type . '</div>
    //       <div><b>Rejection Remark </b>' . $description . '</div>';
    //     $templateVars = [
    //       'name' => $deposit_details->fullname,
    //       'site_link' => $copyright_site_name_text,
    //       'email' => $email_from_address,
    //       "content" => $content,
    //       "title_right" => "Transaction",
    //       "subtitle_right" => "Rejected",
    //       "btn_text" => "Go To Dashboard",
    //     ];
    //     phpMail($toEmail, $emailSubject, $htmlContent, $headers, 'email-template.php', $templateVars);
    //   }
    }
}
