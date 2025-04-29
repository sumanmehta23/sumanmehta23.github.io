<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\PaymentLog;
use App\Models\LiveAccount;
use App\MT5\MTEnDealAction;
use App\Models\ClientWallet;
use App\Models\TotalBalance;
use App\Models\TradeDeposit;
use App\Services\MT5Service;
use Illuminate\Http\Request;
use App\Models\WalletDeposit;
use App\Models\WalletWithdraw;
use App\Models\BonusTransaction;
use App\Models\TradeWithdrawals;
use App\Http\Controllers\Payment;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Actions\SubscribeToKlaviyoList;
use App\Http\Resources\DepositResource;
use Spatie\Activitylog\Models\Activity;
use App\Http\Resources\DepositCollection;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Http\Resources\WithdrawalCollection;
use App\Services\MailService as MailService;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\TwoFactorAuthenticationProvider;

class Wallet extends Controller
{
    protected $settings;
    protected $paymentController;
    protected $mailService;

    protected $api;
    protected $mt5Service;

    public function __construct(Payment $paymentController, MailService $mailService,MT5Service $mt5Service)
    {
        $this->settings = settings();
        $this->paymentController = $paymentController;
        $this->mailService = $mailService;
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
    }
    public function alldeposits()
    {
        $deposits = WalletDeposit::whereIn('deposit_type',['CryptoChill','CreditCardPayissa','Now Payment'])->paginate();
        return new DepositCollection($deposits);
    }
    public function allwithdrawals()
    {
        $withdrawals = WalletWithdraw::where('withdraw_type','Wallet Withdrawal')->where('status',1)->paginate();
        return new WithdrawalCollection($withdrawals);
    }
    public function index()
    {
        $email = auth()->user()->email;
        $wallet_history = $this->getWalletHistory($email);
        $wallet_balance = round(auth()->user()->wallet_balance, 2);

        // dd($wallet_balance);
        return view('wallet', compact('wallet_balance', 'wallet_history'));
    }
    public function getWalletHistory($email)
    {
        // Fetch deposit history
        $deposit_history = WalletDeposit::where('email', $email)
            ->select('id as raw_id', 'transaction_id', 'deposit_type as transfer_type', 'status', 'deposit_amount as amount', \DB::raw("'deposit' as type"), 'deposted_date as date_added','email')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();
        // Fetch withdrawal history
        $withdrawal_history = WalletWithdraw::where('email', $email)
            ->select('id as raw_id', 'transaction_id', 'withdraw_transaction_fee', 'withdraw_type as transfer_type', 'status', 'withdraw_amount as amount', 'verified', \DB::raw("'withdrawal' as type"), 'withdraw_date as date_added','email')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();
        // Merge and sort
        $wallethistory = $deposit_history->concat($withdrawal_history)->sortByDesc('date_added')->take(10);

        return $wallethistory;
    }

    public function transaction_deposit_manually(Request $request, $trx_id, $amount,$code)
    {


        $settings = settings();

        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );



        $account = Account::where('code', $code)->firstOrFail();
        if(!$account) {
            return response()->json(['error' => 'Account not found'], 404);
        }


        $user = User::findOrFail($account->user_id);
        $depositamount = $amount;
        $email = $user->email;


        $deposit_type = 'CryptoChill';
        $deposit_from = NULL;

        $comment = "Deposit";
        $ticket = NULL;

        $depositProofPath = null;

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
                PaymentLog::create([
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'payment_amount' => $depositamount,
                    'payment_type' => $deposit_type,
                    'payment_reference_id' => $account->id,
                    'payment_status' => $account->id,
                    'payment_res' => $account->id,
                    'initiated_by' => $account->id,
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
                    'status' => 'success',
                ]);
            });
            // RateLimiter::clear($key);
            return response()->json(['success' => 'Funds Successfully Deposited']);
        }

        return back()->with('success', 'Deposit added successfully.');
    }

    public function storeClientWallet(Request $request)
    {
        $request->validate([
            'wallet_name' => 'required|string|max:255',
            'wallet_network' => 'required|string|max:255',
            'wallet_address' => 'required|string|max:255',
            'status' => 'required',
        ]);

        $user = DB::table('aspnetusers')->where('email', auth()->user()->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $settings = settings();
        ClientWallet::create([
            'wallet_name' => $request->wallet_name,
            'wallet_currency' => 'USDT',
            'wallet_network' => $request->wallet_network,
            'wallet_address' => $request->wallet_address,
            'user_id' =>  $user->id,
            'status' => $request->status,
            'verified' => 0,
        ]);

        activity()
            ->causedBy(auth()->user()->id)
            ->withProperties([
                'ip' => request()->ip(),
                'user_email' => auth()->user()->email,
                'username' => auth()->user()->username,
                'user_id' => auth()->user()->id,
                'wallet_name' => $request->wallet_name,
                'wallet_currency' => 'USDT',
                'wallet_network' => $request->wallet_network,
                'wallet_address' => $request->wallet_address,
                'verified' => 0,
                'remark' => 'Created New Wallet Address'
            ])
            ->event('create')
            ->log('Created New Wallet Address');


        $toEmail = $user->email;
        $type = 'Wallet Details Verification';
        $from = $settings['email_from_address'];
        $emailSubject = $settings['admin_title'] . ' - ' . $type;
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $ClientWallet = ClientWallet::where('user_id', $user->id)
            ->latest('created_at') // Specify the column to order by
            ->first();
        $content =
            '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
            '<div>You are receiving this email because you have added a new wallet address to your account.</div>' .
            '<div>Wallet Address: ' . $request->wallet_address . ' </div>' .
            '<div>Click the link below to activate your Wallet Address</div>';

        $templateVars = [
            'name' => $user->fullname,
            'server_name' => $settings['mt5_company_name'],
            'site_link' => $settings['copyright_site_name_text'] . "/wallet_address_verify?id={$user->id}&clientWallet_id=$ClientWallet->id",
            'email' => $from,
            "content" => $content,
            "title_right" => "Activate",
            "subtitle_right" => "Your Wallet Address"
        ];
        $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);

        return response()->json(['success' => true]);
    }

    public function resend_wallet_address_confirmation_email(Request $request)
    {
        $settings = settings();
        $user = auth()->user();

        if (!$user) {
            return redirect()->back()->with('error', 'User not authenticated.');
        }

        $toEmail = $user->email;
        $type = 'Wallet Details Verification';
        $from = $settings['email_from_address'];
        $emailSubject = $settings['admin_title'] . ' - ' . $type;

        // Fetch wallet details
        $ClientWallet = ClientWallet::where('user_id', $user->id)
            ->where('id', $request->wallet_id)
            ->first();

        if (!$ClientWallet) {
            return redirect()->back()->with('error', 'Wallet not found.');
        }

        $walletAddress = htmlspecialchars($request->wallet_address, ENT_QUOTES, 'UTF-8');

        $content = '
            <div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>
            <div>You are receiving this email because you have added a new wallet address to your account.</div>
            <div>Wallet Address: ' . $walletAddress . '</div>
            <div>Click the link below to activate your Wallet Address:</div>
        ';

        $templateVars = [
            'name' => $user->fullname,
            'server_name' => $settings['mt5_company_name'],
            'site_link' => htmlspecialchars($settings['copyright_site_name_text'], ENT_QUOTES, 'UTF-8') .
                "/wallet_address_verify?id={$user->id}&clientWallet_id={$ClientWallet->id}",
            'email' => $from,
            'content' => $content,
            'title_right' => 'Activate',
            'subtitle_right' => 'Your Wallet Address'
        ];

        try {
            $this->mailService->sendEmail($toEmail, $emailSubject, '', '', $templateVars);
            return response()->json(['success' => true, 'message' => 'Verification email sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send verification email: ' . $e->getMessage()], 500);
        }
    }


    public function resend_wallet_address_delete_confirmation_email(Request $request)
    {
        try {
            $settings = settings();
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $wallet_id = $request->input('wallet_id');
            $wallet = ClientWallet::with('user')->where('id', $wallet_id)->first();

            if (!$wallet) {
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            $toEmail = $user->email;
            $from = $settings['email_from_address'];
            $emailSubject = $settings['admin_title'] . ' - Verify Wallet Address Deletion Request';

            $content = '<div>We received a request to delete the following wallet address linked to your wallet:</div>' .
                    '<div>Wallet Address: ' . e($request->input('wallet_address')) . '</div>' .
                    '<div>For security purposes, please verify this request by clicking the link below:</div>';

            $templateVars = [
                'name' => $wallet->user->fullname,
                'server_name' => $settings['mt5_company_name'],
                'site_link' => url("/delete_wallet_address?id={$wallet->user_id}&clientWallet_id={$wallet->id}"),
                'email' => $from,
                'content' => $content,
                'title_right' => 'Verify',
                'subtitle_right' => 'Wallet Address Deletion Request',
                'btn_text' => 'Verify Wallet Address Deletion'
            ];

            $this->mailService->sendEmail($toEmail, $emailSubject, '', '', $templateVars);

            return response()->json(['success' => 'Verification email sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'message' => $e->getMessage()], 500);
        }
    }


    public function verify_delete_wallet_address(Request $request)
    {
        $wallet_id = $request->id;

        // Check for pending withdrawals
        $pendingWithdrawals = WalletWithdraw::where('client_wallet_id', $wallet_id)->where('status', 0)->count();

        if ($pendingWithdrawals > 0) {
            return response()->json([
                'error' => true,
                'message' => 'Cannot delete wallet address with pending withdrawals.'
            ]);
        }

        $settings = settings();
        $wallet = ClientWallet::with('user')->where('id', $wallet_id)->first();

        if ($wallet) {
            $wallet->wallet_delete_verification = true; // or 1, depending on the DB type
            $wallet->save();
            activity()
                ->causedBy($wallet->user->id)
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_email' => $wallet->user->email,
                    'username' => $wallet->user->username,
                    'user_id' => $wallet->user->id,
                    'wallet_name' => $wallet->wallet_name,
                    'wallet_currency' => 'USDT',
                    'wallet_network' => $wallet->wallet_network,
                    'wallet_address' => $wallet->wallet_address,
                    'verified' => 1,
                    'remark' => 'Verify Wallet Deletion'
                ])
                ->event('update')
                ->log('Verify Wallet Deletion');
        }

        $toEmail = $wallet->user->email;
        $type = 'Verify Wallet Address Deletion Request';
        $from = $settings['email_from_address'];
        $emailSubject = $settings['admin_title'] . ' - ' . $type;
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";

        $content =
            '<div>We received a request to delete the following wallet address linked to your wallet</div>' .
            '<div>Wallet Address: ' . $wallet->wallet_address . ' </div>' .
            '<div>For security purposes, please verify this request by clicking the link below</div>';

        $templateVars = [
            'name' => $wallet->user->fullname,
            'server_name' => $settings['mt5_company_name'],
            'site_link' => $settings['copyright_site_name_text'] . "/delete_wallet_address?id={$wallet->user_id}&clientWallet_id=$wallet->id",
            'email' => $from,
            "content" => $content,
            "title_right" => "Verify",
            "subtitle_right" => "Wallet Address Deletion Request",
            "btn_text" => "Verify Wallet Address Deletion"
        ];
        $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);

        return response()->json([
            'success' => true,
            'message' => 'Wallet address deletion email send successfully.'
        ]);
    }

    public function get_editing_wallet_details(Request $request)
    {
        $wallet = ClientWallet::find($request->id);

        if ($wallet) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $wallet->id,
                    'wallet_name' => $wallet->wallet_name,
                    'wallet_network' => $wallet->wallet_network,
                    'wallet_address' => $wallet->wallet_address,
                    'status' => $wallet->status
                ]
            ]);
        }

        return response()->json(['success' => false], 404);
    }

    public function verify_edit_wallet_details(Request $request)
    {

        $wallet_id = $request->id;
        $wallet_address = $request->wallet_address;
        $wallet_network = $request->wallet_network;
        $wallet_name = $request->wallet_name;
        $wallet_status = $request->status;

        // Check for pending withdrawals
        $pendingWithdrawals = WalletWithdraw::where('client_wallet_id', $wallet_id)->where('status', 0)->count();

        if ($pendingWithdrawals > 0) {
            return response()->json([
                'error' => true,
                'message' => 'Cannot edit wallet address with pending withdrawals.'
            ]);
        }

        $settings = settings();
        $wallet = ClientWallet::with('user')->where('id', $wallet_id)->first();

        $toEmail = $wallet->user->email;
        $type = 'Wallet Edit Confirmation Required';
        $from = $settings['email_from_address'];
        $emailSubject = $settings['admin_title'] . ' - ' . $type;
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";

        $content =
            '<div>We received a request to update the details of your saved wallet. Please review the changes below:</div>' .
            '<br>' .
            '<div>Previous Wallet Details:</div>' .
            '<br>' .
            '<div>Wallet Name: ' . $wallet->wallet_name . ' </div>' .
            '<div>Wallet Network: ' . $wallet->wallet_network . ' </div>' .
            '<div>Wallet Address: ' . $wallet->wallet_address . ' </div>' .
            '<br>' .
            '<div>Updated Wallet Details:</div>' .
            '<br>' .
            '<div>Wallet Name: ' . $wallet_name . ' </div>' .
            '<div>Wallet Network: ' . $wallet_network . ' </div>' .
            '<div>Wallet Address: ' . $wallet_address . ' </div>' .
            '<br>' .
            '<div>For security purposes, please verify this request by clicking the link below</div>' .
            '<br>' .
            '<div>If you did not request this change, please contact our support team immediately at support@lqhmarkets.com.</div>' .
            '<br>' .
            '<div>Best regards,</div>' .
            '<div>LQH Markets Team</div>';

        $templateVars = [
            'name' => $wallet->user->fullname,
            'server_name' => $settings['mt5_company_name'],
            'site_link' => $settings['copyright_site_name_text'] . "/edit_wallet_details?id={$wallet_id}&wallet_address={$wallet_address}&wallet_network={$wallet_network}&wallet_name={$wallet_name}&status={$wallet_status}",
            'email' => $from,
            "content" => $content,
            "title_right" => "Verify",
            "subtitle_right" => "Wallet Details",
            "btn_text" => "Confirm Wallet Edit"
        ];
        $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);

        return response()->json([
            'success' => true,
            'message' => 'Wallet address update email send successfully.'
        ]);
    }

    public function edit_wallet_details(Request $request)
    {
        $wallet_details = $request->all();
        $wallet_id = $request->id;
        $wallet = ClientWallet::with('user')->where('id', $wallet_id)->first();
        $pendingWithdrawals = WalletWithdraw::where('client_wallet_id', $wallet_id)->where('status', 0)->count();

        if ($pendingWithdrawals > 0) {
            return response()->json([
                'error' => true,
                'message' => 'Cannot delete wallet address with pending withdrawals.'
            ]);
        }

        if ($wallet) {
            $wallet->wallet_name = $wallet_details['wallet_name'];
            $wallet->wallet_network = $wallet_details['wallet_network'];
            $wallet->wallet_address = $wallet_details['wallet_address'];
            $wallet->status = $wallet_details['status'];
            $wallet->save();

            activity()
                ->causedBy($wallet->user->id)
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_email' => $wallet->user->email,
                    'username' => $wallet->user->username,
                    'user_id' => $wallet->user->id,
                    'wallet_name' => $wallet->wallet_name,
                    'wallet_currency' => 'USDT',
                    'wallet_network' => $wallet->wallet_network,
                    'wallet_address' => $wallet->wallet_address,
                    'verified' => 1,
                    'remark' => 'Edit Wallet Details'
                ])
                ->event('update')
                ->log('Edit Wallet Details');

            $settings = settings();
            $from = $settings['email_from_address'];
            $emailSubject = $settings['admin_title'] . ' - Wallet Address Updated';
            $htmlContent = "";
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
            $content =
                '<div>We’re writing to confirm that your request to update your wallet details has been successfully verified and processed:</div>' .
                '<div>Wallet Address: ' . $wallet->wallet_address . ' </div>' .
                '<div>Wallet Network: ' . $wallet->wallet_network . ' </div>' .
                '<div>Wallet Name: ' . $wallet->wallet_name . ' </div>' .
                '<div>The wallet details is now updated. If this action was not performed by you, please contact our support team immediately at ' . $settings['email_from_address'] . ' for assistance.</div>';
            $templateVars = [
                'name' => $wallet->user->fullname,
                'server_name' => $settings['mt5_company_name'],
                'site_link' => $settings['copyright_site_name_text'] . "/login",
                'email' => $settings['email_from_address'],
                "content" => $content,
                "title_right" => "Wallet Address Updation",
                "subtitle_right" => "Verified",
                "btn_text" => "Login"
            ];
            $this->mailService->sendEmail($wallet->user->email, $emailSubject, $headers, '', $templateVars);
            return redirect()->route('user-profile')->with('status', 'WoW! Your Wallet Details succesfully updated');
        }
        return redirect()->route('user-profile')->with('error', 'Something went wrong');
    }

    public function delete_wallet_address(Request $request)
    {
        $wallet_id = $request->clientWallet_id;
        $wallet = ClientWallet::with('user')->where('id', $wallet_id)->first();
        // Delete the wallet
        $deleted = ClientWallet::where('id', $wallet_id)->update(['deleted_at' => now()]);
        activity()
            ->causedBy($wallet->user->id)
            ->withProperties([
                'ip' => request()->ip(),
                'user_email' => $wallet->user->email,
                'username' => $wallet->user->username,
                'user_id' => $wallet->user->id,
                'wallet_name' => $wallet->wallet_name,
                'wallet_currency' => 'USDT',
                'wallet_network' => $wallet->wallet_network,
                'wallet_address' => $wallet->wallet_address,
                'verified' => 1,
                'remark' => 'Wallet Deleted'
            ])
            ->event('update')
            ->log('Wallet Deleted');
        $settings = settings();
        $from = $settings['email_from_address'];
        $emailSubject = $settings['admin_title'] . ' - Wallet Address Deletion Verified';
        $htmlContent = "";
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content =
            '<div>We’re writing to confirm that your request to delete the following wallet address has been successfully verified and processed:</div>' .
            '<div>Wallet Address: ' . $wallet->wallet_address . ' </div>' .
            '<div>The wallet address is now removed from your wallet. If this action was not performed by you, please contact our support team immediately at ' . $settings['email_from_address'] . ' for assistance.</div>';
        $templateVars = [
            'name' => $wallet->user->fullname,
            'server_name' => $settings['mt5_company_name'],
            'site_link' => $settings['copyright_site_name_text'] . "/login",
            'email' => $settings['email_from_address'],
            "content" => $content,
            "title_right" => "Wallet Address Deletion",
            "subtitle_right" => "Verified",
            "btn_text" => "Login"
        ];
        $this->mailService->sendEmail($wallet->user->email, $emailSubject, $headers, '', $templateVars);
        if ($deleted) {
            return redirect()->route('user-profile')->with('status', 'WoW! Your Wallet Address succesfully deleted');
        } else {
            return redirect()->route('user-profile')->with('error', 'Something went wrong');
        }
    }


    public function wallet_address_verify(Request $request)
    {
        $settings = settings();
        $id = $request->query('id');
        $clientWallet_id = $request->query('clientWallet_id');

        $new_wallet_address = ClientWallet::with('user')->where('user_id', $id)
            ->where('id', $clientWallet_id)
            ->first();

        if ($new_wallet_address) {
            if ($new_wallet_address->verified  == 0) {
                $new_wallet_address->verified = 1;
                $new_wallet_address->save();
                activity()
                    ->causedBy($new_wallet_address->user->id)
                    ->withProperties([
                        'ip' => request()->ip(),
                        'user_email' => $new_wallet_address->user->email,
                        'username' => $new_wallet_address->user->username,
                        'user_id' => $new_wallet_address->user->id,
                        'wallet_name' => $new_wallet_address->wallet_name,
                        'wallet_currency' => 'USDT',
                        'wallet_network' => $new_wallet_address->wallet_network,
                        'wallet_address' => $new_wallet_address->wallet_address,
                        'verified' => 1,
                        'remark' => 'Verified New Wallet Address'
                    ])
                    ->event('update')
                    ->log('Verified New Wallet Address');
                $from = $settings['email_from_address'];
                $emailSubject = $settings['admin_title'] . ' - Thank You for Confirming Your Wallet Address';
                $htmlContent = "";
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $content =
                    '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
                    '<div>Your wallet address has been successfully confirmed, and you’re all set to withdraw your earnings.</div>';
                $templateVars = [
                    'name' => $new_wallet_address->user->fullname,
                    'server_name' => $settings['mt5_company_name'],
                    'site_link' => $settings['copyright_site_name_text'] . "/login",
                    'email' => $settings['email_from_address'],
                    "content" => $content,
                    "title_right" => "Wallet Address Verification",
                    "subtitle_right" => "Successful",
                    "btn_text" => "Login"
                ];
                $this->mailService->sendEmail($new_wallet_address->user->email, $emailSubject, $headers, '', $templateVars);
                return redirect()->route('wallet_withdrawal')->with('status', 'WoW! Your Wallet Address is now Verified');
            } else {
                return redirect()->route('dashboard')->with('error', 'Sorry! Wallet Address is already Verified');
            }
        } else {
            return redirect()->route('dashboard')->with('error', 'Sorry! No Adress Found. Signup here');
        }
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'toggle_wallet' => 'required',
            'id' => 'required|string',
        ]);
        $wallet = ClientWallet::where('id', $request->id)->first();
        if ($wallet) {
            $wallet->status = $wallet->status == 0 ? 1 : 0;
            $wallet->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Wallet not found.'], 404);
    }
    public function showDepositForm()
    {
        return redirect()->route('trade-deposit');
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

        $total_wwf = WalletWithdraw::where('email', $email)
            ->where('Status', 1)
            ->sum('withdraw_transaction_fee');

        $wallet_balance = (float) $total_wd - ((float) $total_ww + (float) $total_wwf);

        return view('wallet_deposit', compact('kyc_user', 'settings', 'liveaccount_details', 'totals', 'wallet_balance'));
    }
    public function showWithdrawalForm()
    {
        $user = auth()->user();
        $email = $user->email;
        $userId = $user->id;
        $client_banks = ClientWallet::where('user_id', $userId)
            ->where('status', 1)
            ->where('verified', 1)
            ->where('wallet_delete_verification', 0)
            ->where('deleted_at', NULL)
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

        $wallet_balance = $user->wallet_balance;
        return view('wallet_withdrawal', compact('client_banks', 'settings', 'liveaccount_details', 'totals', 'wallet_balance'));
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
            // dd($request->all());
            $trading_deposited1 = $request->input('deposit');
            $deposit_type = $request->input('deposit_type');
            if ($deposit_type == "CreditCardPayissa") {
                $data = [
                    "payment_amount" => $trading_deposited1,
                    "payment_type" => "CreditCardPayissa",
                    "payment_reference_id" => "Wallet",
                    "user_id" => $user->id,
                    "payment_status" => "Initiated",
                    "initiated_by" => $user->email
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
            // $tradeId = $request->input('code');
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
    public function secureProcessPayment(Request $request, SubscribeToKlaviyoList $subscribeToKlaviyoList)
    {
        // Get the JSON payload from the request
        $payload = $request->json()->all();
        Log::channel("cryptochillcallback")->info(json_encode($payload));
        // Get signature and callback_id fields from provided data
        $signature = $payload['signature'] ?? null;
        $callback_id = $payload['callback_id'] ?? null;
        $callbackToken = config('services.cryptochill.callbacktoken');
        // Validate the signature
        if ($callback_id !== null) {
            $is_valid = $signature === $this->encodeHmac($callbackToken, $callback_id);
        } else {
            $is_valid = false;
        }

        // Throw an error if the signature does not match
        if (!$is_valid) {
            info('Failed to verify CryptoChill callback signature: ' . $callback_id . " and token is  " . $callbackToken);
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
            $customerID = $passedData['customerID'];
            $customerAccountID = $passedData['clientAccountID'];
            $transactionId = $payload['transaction']['id'];
            $deposit_type = "CryptoChill";



            if ($deposit_to === "wallet") {
                // Check for duplicate transaction
                $existingDeposit = WalletDeposit::where('transaction_id', $transactionId)->first();
                if ($existingDeposit) {
                    return response()->json(['status' => 'true']);
                }

                // Prepare callback data and insert it into the database
                $callback_data = json_encode($payload);
                $callback_code = json_encode($payload['transaction']["status"]);

                try {
                    DB::beginTransaction();

                    WalletDeposit::create([
                        'user_id' => $customerID,
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
                    TotalBalance::create(
                        ['email' => $email, 'user_id' => $customerID, 'deposit_amount' => $amount]
                    );

                    DB::commit();
                    $user = User::where('id', $customerID)->first();
                    $this->subscribeToKlaviyoList($user, $amount, $subscribeToKlaviyoList);
                    Cache::forget("user:{$customerID}:wallet_balance");
                    Log::channel("cryptochillcallback")->info('Transaction confirmed successfully.');

                    return response()->json(['status' => 'true']);
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::channel("cryptochillcallback")->error('Transaction failed: ' . $e->getMessage());
                    return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
                }
            }elseif($deposit_to === "Account"){

                $settings = settings();
                $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
                $this->api->Connect(
                    $settings['mt5_server_ip'],
                    $settings['mt5_server_port'],
                    300,
                    $settings['mt5_server_web_login'],
                    $settings['mt5_server_web_password']
                );

                $account = Account::where('id',$customerAccountID)->first();

                // Check for duplicate transaction
                $existingDeposit = TradeDeposit::where('transaction_id', $transactionId)->first();
                if ($existingDeposit) {
                    return response()->json(['status' => 'true']);
                }
                // Prepare callback data and insert it into the database
                $callback_data = json_encode($payload);
                $callback_code = json_encode($payload['transaction']["status"]);

                $comment = 'Deposit';
                $ticket1 = NULL;

                if($account->accountType->ac_group == 'LM\B-Book\10x\DF-B'){
                    $existingTransaction = BonusTransaction::where('transaction_id', $transactionId)->first();
                    if (!$existingTransaction) {
                        $bonusamount = 9*$amount;
                        if (($error_code1 = $this->api->TradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $bonusamount, '10x Trader Leverage', $ticket1, true)) !== MTRetCode::MT_RET_OK) {
                            return redirect()->back()->with('error', MTRetCode::GetError($error_code1));
                        } else {
                            $deposit_details = BonusTransaction::create([
                                'email' => $email,
                                'user_id' => $customerID,
                                'account_id' => $customerAccountID,
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

                $ticket2 = NULL;
                $error_code2 = $this->api->TradeBalance($account->code, $typed = MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket2, $margin_check = true);

                if ($error_code2 != MTRetCode::MT_RET_OK) {
                    $error = MTRetCode::GetError($error_code2);
                    return response()->json([
                        'success' => false,
                        'message' => 'Something went wrong',
                        'error' => $error,
                    ], 400);
                } else {
                    try {
                        DB::beginTransaction();

                        TradeDeposit::create([
                            'user_id' => $customerID,
                            'account_id' => $customerAccountID,
                            'email' => $email,
                            'code' => $account->code,
                            'deposit_amount' => $amount,
                            'deposit_type' => $deposit_type,
                            'deposit_from' => $deposit_type,
                            'status' => 1,
                            'deposit_currency' => 'USD',
                            'transaction_id' => $transactionId,
                            'deposted_date' => now(),
                            'callback_data' => $callback_data,
                            'callback_code' => $callback_code,
                        ]);

                        // Update total balance
                        TotalBalance::create(
                            ['email' => $email, 'user_id' => $customerID, 'deposit_amount' => $amount]
                        );

                        DB::commit();
                        $user = User::where('id', $customerID)->first();
                        $this->subscribeToKlaviyoList($user, $amount, $subscribeToKlaviyoList);
                        Cache::forget("user:{$customerID}:wallet_balance");
                        Log::channel("cryptochillcallback")->info('Transaction confirmed successfully.');

                        return response()->json(['status' => 'true']);
                    } catch (Exception $e) {
                        DB::rollBack();
                        Log::channel("cryptochillcallback")->error('Transaction failed: ' . $e->getMessage());
                        return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
                    }
                }

            } else {
                // If depositTo is not "wallet", handle other cases
                if (!isset($passedData['accountID'])) {
                    return response()->json(['error' => 'Account ID missing'], 400);
                }

                $logData .= "Credit directly to Account ID: " . $passedData['accountID'] . "\n";
                Log::channel("cryptochillcallback")->info($logData);

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


    public function withdrawal(Request $request, TwoFactorAuthenticationProvider $twoFactorProvider)
    {
        $settings = settings();

        // Generate a unique rate-limiting key based on user or IP
        $key = 'deposit:' . (auth()->id() ?: $request->ip());

        // Check if the user has exceeded the rate limit
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retryAfter = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => 'Too many requests',
                'error' => "Please wait {$retryAfter} seconds before trying again.",
            ], 429); // HTTP 429 Too Many Requests
        }

        // Increment the rate limiter
        RateLimiter::hit($key, 10); // Lock for 10 seconds

        $request->validate([
            'withdraw_amount' => 'required|numeric|min:10',
            'withdraw_type' => 'required|string',
            'client_wallet_id' => 'required'
        ]);
        $user = auth()->user();
        if ($user->two_factor_secret) {
            $isValid = $twoFactorProvider->verify(
                decrypt(auth()->user()->two_factor_secret),
                $request->input('two_factor_code')
            );
            // dump($user);
            // dd($isValid);
            if (!$isValid) {
                throw ValidationException::withMessages([
                    'two_factor_code' => ['Invalid or expired 2FA code.'],
                ]);
            }
        }

        $userEmail = auth()->user()->email;
        $user = auth()->user();
        $withdrawAmount = $request->input('withdraw_amount');
        $request->validate(
            [
                'confirmCheckbox' => [
                    'required_if:withdraw_amount,<,99',
                ],
                'confirmcryptoCheckbox' => [
                    'required' // Ensures this checkbox is checked
                ],
            ],
            [
                'confirmCheckbox.required_if' => 'The confirmation checkbox is required when the withdrawal amount is less than 100.',
                'confirmcryptoCheckbox.required' => 'The correct wallet address and network confirmation checkbox is required.',
            ]
        );
        $withdrawType = str_replace('_', ' ', $request->input('withdraw_type'));
        $clientWalletId = $request->input('client_wallet_id');
        $clientWallet = ClientWallet::where('id', $clientWalletId)->where('user_id', $user->id)->firstOrFail();

        $totalDeposits = WalletDeposit::where('email', $userEmail)
            ->where('status', 1)
            ->sum('deposit_amount');

        $totalWithdrawals = WalletWithdraw::where('email', $userEmail)
            ->whereNotIn('status', [2, 3])
            ->sum('withdraw_amount');

        $totalWithdrawalsFee = WalletWithdraw::where('email', $userEmail)
            ->whereNotIn('status', [2, 3])
            ->sum('withdraw_transaction_fee');

        $walletBalance = (float) $totalDeposits - ((float) $totalWithdrawals + (float) $totalWithdrawalsFee);
        if ($withdrawAmount > $walletBalance) {
            return redirect()->back()->with('error', 'Insufficient balance in your wallet.');
        } else if ($withdrawAmount < 100) {
            $withdrawAmount = $withdrawAmount - 5;
            $withdraw_transaction_fee = 5;
        } else {
            $withdrawAmount = $withdrawAmount;
            $withdraw_transaction_fee = null;
        }
        $WalletWithdrawal = WalletWithdraw::create([
            'client_wallet_id' => $clientWallet->id,
            'email' => $userEmail,
            'user_id' => $user->id,
            'withdraw_amount' => $withdrawAmount,
            'withdraw_transaction_fee' => $withdraw_transaction_fee,
            'withdraw_type' => $withdrawType,
            'status' => 0,
            'verified' => 0
        ]);


        $toEmail = $user->email;
        $type = 'Withdrawal Details Verification';
        $from = $settings['email_from_address'];
        $emailSubject = $settings['admin_title'] . ' - ' . $type;
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";

        $content =
            '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
            '<div>You are receiving this email because you have requested a withdrawal of amount $' . $withdrawAmount . ' from your wallet.</div>' .
            '<div>Click the link below to activate your Wallet Withdrawal</div>';

        $templateVars = [
            'name' => $user->fullname,
            'server_name' => $settings['mt5_company_name'],
            'site_link' => $settings['copyright_site_name_text'] . "/wallet_withdrawal_verify?walletWithdrawal_id=$WalletWithdrawal->id",
            'email' => $from,
            "content" => $content,
            "title_right" => "Activate",
            "subtitle_right" => "Your Wallet Withdrawal Request"
        ];
        $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);

        // RateLimiter::clear($key);
        return redirect()->back()->with('success', 'Withdrawal Request of $' . $withdrawAmount . ' Successfully Submitted!. Please verify your email for withdrawal confirmation.');
    }

    public function resend_wallet_withdrawal_verify_email(Request $request)
    {
        try {
            $request->validate([
                'wallet_withdrawal_id' => 'required',
            ]);
            $user = auth()->user();

            $tradeWithdrawal = TradeWithdrawals::with('user')
                ->where('user_id', $user->id)
                ->find($request->wallet_withdrawal_id);
            // dump($user->id);
            // dd($walletWithdrawal);
            if (!$tradeWithdrawal) {
                return response()->json(['success' => false, 'message' => 'Unauthorized or not found'], 403);
            }

            $settings = settings();
            $withdrawAmount = $tradeWithdrawal->withdrawal_amount;
            $toEmail = $tradeWithdrawal->user->email;
            $toName = $tradeWithdrawal->user->fullname;
            $from = $settings['email_from_address'];

            $type = 'Withdrawal Details Verification';
            $emailSubject = $settings['admin_title'] . ' - ' . $type;
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";

            $content =
                '<div><b>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '</b></div><br>' .
                '<div>You are receiving this email because you have requested a withdrawal of amount $' . $withdrawAmount . ' from your account.</div><br>' .
                '<div>Click the link below to activate your Account Withdrawal</div>';

            $templateVars = [
                'name' => $toName,
                'server_name' => $settings['mt5_company_name'],
                'site_link' => $settings['copyright_site_name_text'] . "/account_withdrawal_verify?accountWithdrawal_id=$tradeWithdrawal->id",
                'email' => $from,
                "content" => $content,
                "title_right" => "Activate",
                "subtitle_right" => "Your Account Withdrawal Request",
                "btn_text" => "Verify"
            ];

            $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);

            return response()->json(['success' => true, 'message' => 'Verification email sent successfully.']);
        } catch (\Exception $e) {
            Log::error('Error sending wallet withdrawal verification email: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to send verification email. Please try again.']);
        }
    }


    public function wallet_withdrawal_verify(Request $request)
    {

        if (!auth()->check()) {
            return redirect('/login');
        }

        $settings = settings();
        $id = auth()->user()->id;
        $walletWithdrawal_id = $request->query('walletWithdrawal_id');

        $new_wallet_Withdrawal = WalletWithdraw::with('user')->where('user_id', $id)
            ->where('id', $walletWithdrawal_id)
            ->first();
        if ($new_wallet_Withdrawal) {
            if ($new_wallet_Withdrawal->verified  == 0) {
                $new_wallet_Withdrawal->verified = 1;
                $new_wallet_Withdrawal->save();
                activity()->causedBy(auth()->user())
                    ->withProperties(
                        [
                            'ip' => $request->ip(),
                            'email' => auth()->user()->email,
                            'withdraw_amount' => $new_wallet_Withdrawal->withdraw_amount,
                            'withdraw_transaction_fee' => $new_wallet_Withdrawal->withdraw_transaction_fee,
                            'wallet_withdraw_id' => $walletWithdrawal_id,
                            'remark' => 'Wallet Withdraw'
                        ]
                    )
                    ->event('create')
                    ->log('Wallet Withdraw');
                $from = $settings['email_from_address'];
                $emailSubject = $settings['admin_title'] . ' - Thank You for Confirming Your Wallet Withdrawal';
                $htmlContent = "";
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $content =
                    '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
                    '<div>Your wallet withdrawal has been successfully confirmed.</div>';
                $templateVars = [
                    'name' => $new_wallet_Withdrawal->user->fullname,
                    'server_name' => $settings['mt5_company_name'],
                    'email' => $settings['email_from_address'],
                    "content" => $content,
                    "title_right" => "Wallet Withdrawal Verification",
                    "subtitle_right" => "Successful",
                ];
                $this->mailService->sendEmail($new_wallet_Withdrawal->user->email, $emailSubject, $headers, '', $templateVars);
                return redirect()->route('wallet_withdrawal')->with('status', 'WoW! Your Wallet Withdrawal is now Verified');
            } else {
                return redirect()->route('dashboard')->with('error', 'Sorry! Wallet Withdrawal is already Verified');
            }
        } else {
            return redirect()->route('dashboard')->with('error', 'Sorry! No Wallet Withdrawal Found. Signup here');
        }
    }
}
