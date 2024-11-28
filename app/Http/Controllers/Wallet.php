<?php

namespace App\Http\Controllers;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Account;
use App\Models\PaymentLog;
use App\Models\LiveAccount;
use App\Models\ClientWallet;
use App\Models\TotalBalance;
use Illuminate\Http\Request;
use App\Models\WalletDeposit;
use App\Models\WalletWithdraw;
use App\Http\Controllers\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class Wallet extends Controller
{
    protected $settings;
    protected $paymentController;


    public function __construct(Payment $paymentController)
    {
        $this->settings = settings();
        $this->paymentController = $paymentController;

    }
    public function index()
    {
        $email = auth()->user()->email;
        $wallet_history = $this->getWalletHistory($email);
        $wallet_balance = $this->getWalletBalance($email);
        return view('wallet', compact('wallet_balance', 'wallet_history'));
    }
    public function getWalletHistory($email)
    {
        // Fetch deposit history
        $deposit_history = WalletDeposit::where('email', $email)
            ->select('id as raw_id', 'transaction_id', 'deposit_type as transfer_type', 'status', 'deposit_amount as amount', \DB::raw("'deposit' as type"), 'deposted_date as date_added')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();
        // Fetch withdrawal history
        $withdrawal_history = WalletWithdraw::where('email', $email)
            ->select('id as raw_id', 'transaction_id', 'withdraw_type as transfer_type', 'status', 'withdraw_amount as amount', \DB::raw("'withdrawal' as type"), 'withdraw_date as date_added')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();
        // Merge and sort
        $wallethistory = $deposit_history->concat($withdrawal_history)->sortByDesc('date_added')->take(10);

        return $wallethistory;
    }
    public function getWalletBalance($email)
    {
        $totalDeposit = WalletDeposit::where('email', $email)->where('status', 1)->sum('deposit_amount');
        $totalWithdraw = WalletWithdraw::where('email', $email)->where('status','<>', 2)->sum('withdraw_amount');

        $walletBalance = (float) $totalDeposit - (float) $totalWithdraw;
        return $walletBalance;
    }
    public function storeClientWallet(Request $request)
    {
        $request->validate([
            'wallet_name' => 'required|string|max:255',
            'wallet_network' => 'required|string|max:255',
            'wallet_address' => 'required|string|max:255',
            'status' => 'required',
        ]);

        $user = DB::table('aspnetusers')->where('email', session('clogin'))->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        ClientWallet::create([
            'wallet_name' => $request->wallet_name,
            'wallet_currency' => 'USDT',
            'wallet_network' => $request->wallet_network,
            'wallet_address' => $request->wallet_address,
            'user_id' =>  $user->id,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true]);
    }
    public function updateStatus(Request $request)
    {
        $request->validate([
            'toggle_wallet' => 'required',
            'id' => 'required|string',
        ]);
        $wallet = ClientWallet::where(DB::raw('client_wallet_id'), $request->id)->first();
        if ($wallet) {
            $wallet->status = $wallet->status == 0 ? 1 : 0;
            $wallet->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Wallet not found.'], 404);
    }
    public function showDepositForm()
    {
        $email = auth()->user()->email;
        $kyc_user = User::where('email', $email)->first();
        $settings = $this->settings;
        $liveaccount_details = LiveAccount::with('accountType')
            ->where('email', $email)
            ->get();
        $totals = LiveAccount::where('email', $email)
            ->select(DB::raw('SUM(equity) as equity'), DB::raw('SUM(balance) as balance'))
            ->first();

        $total_wd = WalletDeposit::where('email', $email)
            ->where('Status', 1)
            ->sum('deposit_amount');

        $total_ww = WalletWithdraw::where('email', $email)
            ->where('Status', 1)
            ->sum('withdraw_amount');

        $wallet_balance = (float) $total_wd - (float) $total_ww;

        return view('wallet_deposit', compact('kyc_user', 'settings', 'liveaccount_details', 'totals','wallet_balance'));
    }
    public function showWithdrawalForm()
    {
        $email = auth()->user()->email;
        $userId=auth()->user()->id;
        $client_banks = ClientWallet::where('user_id', $userId)
            ->where('status', 1)
            ->get();
        $settings = $this->settings;
        $liveaccount_details = Account::with('accountType')
            ->where('demo', false)
            ->where('user_id', $userId)
            ->get();
        $totals = Account::where('user_id', $userId)
            ->where('demo', false)
            ->select(DB::raw('SUM(equity) as equity'), DB::raw('SUM(balance) as balance'))
            ->first();
        $total_wd = WalletDeposit::where('user_id', $userId)
            ->where('status', 1)
            ->sum('deposit_amount');
        $total_ww = WalletWithdraw::where('user_id', $userId)
            ->where('status','<>', 2)
            ->sum('withdraw_amount');
        $wallet_balance = (float) $total_wd - (float) $total_ww;
        return view('wallet_withdrawal', compact('client_banks', 'settings', 'liveaccount_details', 'totals', 'wallet_balance'));
    }
    public function deposit(Request $request)
    {
        $email = auth()->user()->email;
        try {
            $trading_deposited1 = $request->input('deposit');
            $email = $request->input('email');
            $deposit_type = $request->input('deposit_type');
            if ($deposit_type == "Now Payment") {
                $data = [
                    "payment_amount" => $trading_deposited1,
                    "payment_type" => "NowPayment",
                    "payment_reference_id" => "Wallet",
                    "payment_status" => "Initiated",
                    "initiated_by" => $email
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
    }

    private function createPayment($amount, $currency, $orderId, $paymentId)
    {
        $success_url = $this->settings['copyright_site_name_text'] . "/payment-response?amount=" . $amount . "&payment_id=" .$paymentId . "&status=success";
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
    //function for cryptochill payment
    public function processPayment(Request $request)
    {
        if ($request->has('paymentGateway')) {
            return response()->json(['status' => true, 'message' => 'Deposit successful!'], 200);
            // $depositTo = $request->input('deposit_to');
            // if (!$depositTo) {
            //     return response()->json(['message' => 'Deposit designation missing..!'], 400);
            // }
            // $amount = $request->input('amount');
            // $tradeId = $request->input('trade_id');
            // $time = $request->input('time');
            // $comment = "Deposit";
            // $depositType = $request->input('deposit_type');
            // $email = auth()->user()->email;
            // try {
            //     if ($depositTo == "wallet") {
            //         $callbackData = json_encode($request->input('data'));
            //         $callbackCode = json_encode($request->input('code'));
            //         $walletDeposit = new WalletDeposit();
            //         $walletDeposit->email = $email;
            //         $walletDeposit->deposit_type = $depositType;
            //         $walletDeposit->deposit_amount = $amount;
            //         $walletDeposit->company_bank = $depositType;
            //         $walletDeposit->transaction_id = $time;
            //         $walletDeposit->Status = 1;
            //         $walletDeposit->currency_type = 'USD';
            //         $walletDeposit->callback_data = $callbackData;
            //         $walletDeposit->callback_code = $callbackCode;
            //         $walletDeposit->save();
            //         $totalBalance = TotalBalance::Create(
            //             [
            //                 'email' => $email,
            //                 'deposit_amount' => $amount
            //             ]
            //         );
            //         $mailData=new \stdClass();
            //         $mailData->payment_amount=$amount;
            //         $mailData->fullname=session('user')['fullname'];
            //         $mailData->payment_type=$depositType;
            //         $mailData->created_at=$formattedDate = Carbon::parse($walletDeposit->created_at)->format('Y-m-d H:i:s');
            //         $mailData->payment_reference_id=$time;
            //         $this->paymentController->sendSuccessEmail($email, $amount, $mailData,$walletDeposit->id);
            //         return response()->json(['status' => true, 'message' => 'Deposit successful!'], 200);
            //     }
            // } catch (Exception $e) {
            //     return response()->json(['status' => false, 'message' => 'Something went wrong...!'], 500);
            // }
        }
    }

    public function secureProcessPayment(Request $request)
    {
        // Get the JSON payload from the request
        $payload = $request->json()->all();
        Log::info($payload);
        // Get signature and callback_id fields from provided data
        $signature = $payload['signature'] ?? null;
        $callback_id = $payload['callback_id'] ?? null;
        $callbackToken=config('services.cryptochill.callbacktoken');
        // Validate the signature
        if ($callback_id !== null) {
            $is_valid = $signature === $this->encodeHmac($callbackToken, $callback_id);
        } else {
            $is_valid = false;
        }

        // Throw an error if the signature does not match
        if (!$is_valid) {
            info('Failed to verify CryptoChill callback signature: ' . $callback_id ." and token is  ".$callbackToken);
            throw new Exception('Failed to verify CryptoChill callback signature: ' . $callback_id);
        }

        // Log callback data (you can change log storage if needed)
        $logData = "IP: " . $request->ip() . "\nPayload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";

        // Check if the callback status is transaction confirmed or complete
        if (isset($payload["callback_status"]) && in_array($payload["callback_status"], ['transaction_confirmed', 'transaction_complete'])) {
            $passedData = json_decode($payload['transaction']['invoice']['passthrough'], true);

            if (isset($passedData['customerID'])) {
                $logData .= "Customer ID: " . $passedData['customerID'] . "\n";
            }

            Log::info($logData);

            if (!isset($passedData['depositTo'])) {
                return response()->json(['error' => 'Deposit designation missing'], 400);
            }

            $deposit_to = $passedData['depositTo'];
            $amount = $payload['transaction']['amount']['paid']['quotes']['USD'];
            $email = $passedData['customerEmail'];
            $transactionId = $payload['transaction']['id'];
            $deposit_type = "CryptoChill";

            if ($deposit_to === "wallet") {
                // Check for duplicate transaction
                $existingDeposit = DB::table('wallet_deposit')->where('transaction_id', $transactionId)->first();
                if ($existingDeposit) {
                    return response()->json(['status' => 'true']);
                }

                // Prepare callback data and insert it into the database
                $callback_data = json_encode($payload);
                $callback_code = json_encode($payload['transaction']["status"]);

                try {
                    DB::beginTransaction();

                    DB::table('wallet_deposit')->insert([
                        'email' => $email,
                        'deposit_type' => $deposit_type,
                        'deposit_amount' => $amount,
                        'company_bank' => $deposit_type,
                        'transaction_id' => $transactionId,
                        'status' => 1,
                        'currency_type' => 'USD',
                        'callback_data' => $callback_data,
                        'callback_code' => $callback_code,
                    ]);

                    // Update total balance
                    DB::table('total_balance')->updateOrInsert(
                        ['email' => $email],
                        ['deposit_amount' => DB::raw('deposit_amount + ' . $amount)]
                    );

                    DB::commit();
                    Log::info('Transaction confirmed successfully.');

                    return response()->json(['status' => 'true']);
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error('Transaction failed: ' . $e->getMessage());
                    return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
                }
            } else {
                // If depositTo is not "wallet", handle other cases
                if (!isset($passedData['accountID'])) {
                    return response()->json(['error' => 'Account ID missing'], 400);
                }

                $logData .= "Credit directly to Account ID: " . $passedData['accountID'] . "\n";
                Log::info($logData);

                // Direct credit to account logic goes here, for example:
                // Call external API or perform other operations for direct account credit

                return response()->json(['status' => 'Transaction completed.']);
            }
        }

        return response()->json(['error' => 'Invalid callback status'], 400);
    }

    // Function to generate HMAC signature
    private function encodeHmac($key, $msg)
    {
        return hash_hmac('sha256', $msg, $key);
    }


    public function withdrawal(Request $request)
    {
        $request->validate([
            'withdraw_amount' => 'required|numeric|min:1',
            'withdraw_type' => 'required|string',
            'client_bank' => 'required'
        ]);
        dd($request->all());
        $userEmail = auth()->user()->email;
        $withdrawAmount = $request->input('withdraw_amount');
        $withdrawType = str_replace('_', ' ', $request->input('withdraw_type'));
        $clientBank = $request->input('client_bank');
        $totalDeposits = WalletDeposit::where('email', $userEmail)
            ->where('status', 1)
            ->sum('deposit_amount');

        $totalWithdrawals = WalletWithdraw::where('email', $userEmail)
            ->where('status',"<>", 2)
            ->sum('withdraw_amount');

        $walletBalance = (float) $totalDeposits - (float) $totalWithdrawals;
        if ($withdrawAmount > $walletBalance) {
            return redirect()->back()->with('error', 'Insufficient balance in your wallet.');
        }
        WalletWithdraw::create([
            'email' => $userEmail,
            'withdraw_amount' => $withdrawAmount,
            'withdraw_type' => $withdrawType,
            'client_bank' => $clientBank,
            'status' => 0
        ]);
        return redirect()->back()->with('Withdrawal Request of $' . $withdrawAmount . ' Successfully Submitted!.', 'You’ll receive an email notification once your request is approved and processed');
    }

}
