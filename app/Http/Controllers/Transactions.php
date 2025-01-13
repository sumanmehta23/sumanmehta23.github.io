<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Models\TradeDeposit;
use App\Models\TradeWithdrawals;
use App\Models\InternalTransfer;
use App\Models\WalletDeposit;
use App\Models\WalletWithdraw;
use App\Services\MailService as MailService;


class Transactions extends Controller
{
    protected $mailService;
    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }
    public function index()
    {
        $email = $email = auth()->user()->email;

        $deposit_history = WalletDeposit::where('user_id',  auth()->user()->id)
            ->whereIn('deposit_type', ['CryptoChill','CreditCardPayissa'])
            ->orderBy('id', 'desc')
            ->get();
            // dd($wallet_deposit_history);
        // Fetching withdrawal history
        $withdrawal_history = WalletWithdraw::where('email', $email)
            ->where('withdraw_type', 'Wallet Withdrawal')
            ->orderBy('id', 'desc')
            ->get();

        // Fetching internal transfers

        $internal_transfer = InternalTransfer::where('email', $email)
            ->with('accountTo','accountFrom')
            ->whereIn('type', ['Internal Transfer','Wallet Withdrawal','Wallet Transfer', 'CRM','IB Withdraw'])
            ->where('status', 1)
            ->orderBy('date', 'desc')
            ->get();
        // dd($internal_transfer);
        // $tradeWithdrawals = TradeWithdrawals::with('account')->whereIn('withdraw_type', ['Internal Transfer','Wallet Withdrawal','Wallet Transfer', 'CRM'])
        //     ->select('id','withdrawal_amount', 'withdraw_type','withdraw_date','email','status','withdraw_to','account_id')
        //     ->where('user_id', auth()->user()->id)
        //     ->get()
        //     ->map(function ($withdrawal) {
        //         // if($withdrawal->withdraw_to){
        //         //     $acc = Account::where('id',$withdrawal->withdraw_to)->first();
        //         // }
        //         return [
        //             'type' => $withdrawal->withdraw_to ? 'Internal Transfer' : 'Withdrawal',
        //             'amount' => $withdrawal->withdrawal_amount,
        //             'transaction_type' => $withdrawal->withdraw_type,
        //             'email' => $withdrawal->email,
        //             'status' => $withdrawal->status,
        //             'it_to' => $withdrawal->withdraw_to ?? 'Wallet',
        //             'it_from' => optional($withdrawal->account)->code ?? 'Wallet',
        //             'source' => 'TDID',
        //             'raw_id' => $withdrawal->id,
        //             'date' => $withdrawal->withdraw_date,
        //         ];
        //     });
        //     // dd($tradeWithdrawals);
        // // Fetch filtered data from TradeDeposit with deposit_amount
        // $tradeDeposits = TradeDeposit::whereIn('deposit_type', ['Internal Transfer','Wallet Withdrawal','Wallet Transfer', 'CRM'])
        //     ->select('id', 'deposit_amount','deposted_date','deposit_type','email','status','code','deposit_from')
        //     ->where('user_id', auth()->user()->id)
        //     ->with('account')
        //     ->get()
        //     ->map(function ($deposit) {
        //         // dd($deposit);
        //         if(optional($deposit->account)->code){
        //             $it_from = optional($deposit->account)->code;
        //         }elseif($deposit->deposit_type == 'Wallet Transfer'){
        //             $it_from = 'Wallet';
        //         }else{
        //             $it_from = 'CRM';
        //         }
        //         return [
        //             'type' => $deposit->deposit_type,
        //             'amount' => $deposit->deposit_amount,
        //             'transaction_type' => $deposit->deposit_type,
        //             'email' => $deposit->email,
        //             'status' => $deposit->status,
        //             'it_to' => $deposit->code,
        //             'it_from' => $it_from, // Safe access
        //             'source' => 'TDID',
        //             'raw_id' => $deposit->id,
        //             'date' => $deposit->deposted_date,
        //         ];
        //     });
        //     // dd($tradeDeposits);
        // if($tradeDeposits->isEmpty() && $tradeWithdrawals->isEmpty()){
        //     $internal_transfer = [];
        // }elseif($tradeDeposits->isEmpty()){
        //     $internal_transfer = $tradeWithdrawals;
        // }else{
        //     $internal_transfer = $tradeDeposits->merge($tradeWithdrawals);
        // }


        // Combine data into a single variable
        // $internal_transfer = $tradeDeposits->merge($tradeWithdrawals);
        // Optional: Sort by date
        $internal_transfer = !empty($internal_transfer) ? $internal_transfer->sortBy('date') : collect();
        // dd($internal_transfer);
        // foreach ($internal_transfer as $key => $value) {
        //     echo ($value).'/n';
        // }
        // die;
        return view('transactions', compact('deposit_history', 'withdrawal_history', 'internal_transfer'));
    }
    public function updateTransaction(Request $request){
        // dd($request->all());
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
        $did = $request->input('transaction_id');
        // dd($did);
        $transaction_id = $request->input('id');
        $transaction = WalletWithdraw::whereRaw('id = ?', [$did])->first();
        // dd($transaction);
        if ($transaction) {
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
            }
            return redirect()->back()->with('status', 'Transaction Rejected Successfully');
        } else {
            return redirect()->back()->with('error', 'Transaction Not Found');
        }
    }
    // public function history()
    // {
    //     $email = auth()->user()->email;
    //     $deposit_history = TradeDeposit::with('liveAccount.accountType') // Eager loading relationships
    //         ->where('email', $email)
    //         ->whereNotIn('deposit_type', ['Internal Transfer'])
    //         ->orderBy('id', 'desc')
    //         ->get();
    //     $withdrawal_history = [];
    //     $internal_transfer = [];
    //     return view('transaction-history', compact('deposit_history', 'withdrawal_history', 'internal_transfer'));
    // }
}
