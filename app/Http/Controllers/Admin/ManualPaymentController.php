<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use App\Models\Account;
use App\Models\Promocode;
use App\Models\PaymentLog;
use App\Models\TotalBalance;
use App\Models\TradeDeposit;
use App\MT5\MTRetCode;
use App\MT5\MTEnDealAction;
use Illuminate\Http\Request;
use App\Models\BonusTransaction;
use App\Models\PendingManualPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use App\Services\UniversalMT5Service;
use App\Services\MailService as MailService;
use App\Helpers\AccountHelper;
use App\Events\AccountTradesDepositEvent;
use App\Exceptions\PlatformConnectionException;

class ManualPaymentController extends Controller
{
    protected $mt5Service;
    protected $mailService;

    public function __construct(UniversalMT5Service $mt5Service, MailService $mailService)
    {
        $this->mt5Service = $mt5Service;
        $this->mailService = $mailService;
    }

    /**
     * Display a listing of pending manual payments.
     */
    public function index(Request $request)
    {
        $query = PendingManualPayment::with(['user', 'account', 'paymentLog', 'processor'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        } else {
            // By default, show only pending and processing
            $query->whereIn('status', ['pending', 'processing']);
        }

        // Filter by email
        if ($request->has('email') && $request->email != '') {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // Filter by transaction ID
        if ($request->has('transaction_id') && $request->transaction_id != '') {
            $query->where('transaction_id', 'like', '%' . $request->transaction_id . '%');
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date != '') {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date != '') {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $pendingPayments = $query->paginate(50);

        // Get counts for each status
        $statusCounts = [
            'pending' => PendingManualPayment::where('status', 'pending')->count(),
            'processing' => PendingManualPayment::where('status', 'processing')->count(),
            'completed' => PendingManualPayment::where('status', 'completed')->count(),
            'rejected' => PendingManualPayment::where('status', 'rejected')->count(),
        ];

        return view('admin.manual-payments.index', compact('pendingPayments', 'statusCounts'));
    }

    /**
     * Show details of a single pending payment.
     */
    public function show($id)
    {
        $payment = PendingManualPayment::with(['user', 'account', 'paymentLog', 'processor'])
            ->findOrFail($id);

        // Parse JSON fields
        $polygonResponse = $payment->polygon_response;

        return view('admin.manual-payments.show', compact('payment', 'polygonResponse'));
    }

    /**
     * Refresh USD value for a pending payment.
     */
    public function refreshUsdValue($id, Request $request)
    {
        try {
            $payment = PendingManualPayment::findOrFail($id);

            // Run the polygon command to get updated USD value
            Artisan::call('polygon:usd', [
                'hash' => $payment->transaction_id,
            ]);

            $output = Artisan::output();
            $payment->polygon_response = $output;

            // Try to parse the JSON output
            if (preg_match('/\{[\s\S]*\}/', $output, $matches)) {
                $jsonData = json_decode($matches[0], true);
                if (isset($jsonData['value_usd_at_time'])) {
                    $payment->usd_value = $jsonData['value_usd_at_time'];
                }
            }

            $payment->save();

            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'USD value refreshed successfully',
                    'usd_value' => $payment->usd_value,
                    'requested_amount' => $payment->initial_requested_amount,
                ]);
            }

            return redirect()->back()->with('success', 'USD value refreshed successfully');
        } catch (Exception $e) {
            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to refresh USD value: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to refresh USD value: ' . $e->getMessage());
        }
    }

    /**
     * Process selected pending payments.
     */
    public function processPayments(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:pending_manual_payments,id',
        ]);

        $processedCount = 0;
        $failedPayments = [];

        foreach ($request->payment_ids as $paymentId) {
            $pendingPayment = PendingManualPayment::with(['paymentLog', 'account', 'user'])->findOrFail($paymentId);

            // Check if already processed
            if (in_array($pendingPayment->status, ['completed', 'rejected'])) {
                continue;
            }

            // Check if transaction already exists in trade_deposits
            $existingTransaction = TradeDeposit::where('transaction_id', $pendingPayment->transaction_id)->first();
            if ($existingTransaction) {
                $failedPayments[] = [
                    'id' => $paymentId,
                    'email' => $pendingPayment->email,
                    'reason' => 'Transaction already exists in trade_deposits',
                ];
                continue;
            }

            // Use USD value if available, otherwise use initial requested amount
            $amount = $pendingPayment->usd_value ?? $pendingPayment->initial_requested_amount;

            if (!$amount || $amount <= 0) {
                $failedPayments[] = [
                    'id' => $paymentId,
                    'email' => $pendingPayment->email,
                    'reason' => 'Invalid amount',
                ];
                continue;
            }

            try {
                // Update payment log status to success (if exists)
                if ($pendingPayment->paymentLog) {
                    $pendingPayment->paymentLog->update([
                        'payment_status' => 'success',
                    ]);
                }

                DB::beginTransaction();

                // CRITICAL: Use database locking to prevent race conditions
                $lockResult = DB::select('SELECT GET_LOCK(?, 10) as lock_acquired', ["manual_payment_{$pendingPayment->transaction_id}"]);
                if (!$lockResult[0]->lock_acquired) {
                    DB::rollBack();
                    $failedPayments[] = [
                        'id' => $paymentId,
                        'email' => $pendingPayment->email,
                        'reason' => 'Could not acquire lock',
                    ];
                    continue;
                }

                // Double-check for duplicate after acquiring lock
                $existingTransaction = TradeDeposit::where('transaction_id', $pendingPayment->transaction_id)->first();
                if ($existingTransaction) {
                    DB::select('SELECT RELEASE_LOCK(?)', ["manual_payment_{$pendingPayment->transaction_id}"]);
                    DB::rollBack();
                    $failedPayments[] = [
                        'id' => $paymentId,
                        'email' => $pendingPayment->email,
                        'reason' => 'Transaction already exists in trade_deposits',
                    ];
                    continue;
                }

                // Create trade deposit record (status 0 initially, will update to 1 after MT5 success)
                $tradeDeposit = TradeDeposit::create([
                    'user_id' => $pendingPayment->user_id,
                    'account_id' => $pendingPayment->account_id,
                    'email' => $pendingPayment->email,
                    'code' => $pendingPayment->code,
                    'deposit_amount' => $amount,
                    'deposit_type' => 'CreditCardPayissa',
                    'deposit_from' => 'CreditCardPayissa',
                    'status' => 0,
                    'deposit_currency' => 'USD',
                    'transaction_id' => $pendingPayment->transaction_id,
                    'deposted_date' => $pendingPayment->deposit_date ?? now(),
                    'callback_data' => $pendingPayment->polygon_response,
                    'callback_code' => 'pending',
                ]);

                // Main deposit to MT5
                $comment = 'Manual Payment Deposit';
                $ticket = NULL;
                $errorCode = $this->mt5Service->tradeBalance($pendingPayment->account->code, MTEnDealAction::DEAL_BALANCE, $amount, $comment, $ticket, true);

                if ($errorCode != MTRetCode::MT_RET_OK) {
                    DB::select('SELECT RELEASE_LOCK(?)', ["manual_payment_{$pendingPayment->transaction_id}"]);
                    DB::rollBack();
                    throw new Exception('MT5 deposit failed: ' . MTRetCode::GetError($errorCode));
                }

                // Process promo code AFTER the main deposit is successful
                if ($pendingPayment->promocode) {
                    $this->processPromoCode($pendingPayment, $amount, $tradeDeposit);
                }

                // Update deposit status to success after all MT5 operations succeed
                $tradeDeposit->update(['status' => 1, 'callback_code' => 'success']);

                // Update total balance
                TotalBalance::create([
                    'email' => $pendingPayment->email,
                    'user_id' => $pendingPayment->user_id,
                    'deposit_amount' => $amount
                ]);

                // Mark pending payment as completed
                $pendingPayment->update([
                    'status' => 'completed',
                    'processed_by' => Auth::id(),
                    'processed_at' => now(),
                ]);

                // Update all other records with same transaction_id
                PendingManualPayment::where('transaction_id', $pendingPayment->transaction_id)
                    ->where('id', '!=', $pendingPayment->id)
                    ->update([
                        'status' => 'completed',
                        'processed_by' => Auth::id(),
                        'processed_at' => now(),
                    ]);

                // Release the lock before committing
                DB::select('SELECT RELEASE_LOCK(?)', ["manual_payment_{$pendingPayment->transaction_id}"]);

                DB::commit();

                // Trigger deposit event
                event(new AccountTradesDepositEvent($pendingPayment->user, $amount));

                // Send success email using MailService
                $this->sendSuccessEmail($pendingPayment->email, $amount, $tradeDeposit);

                // Clear cache
                Cache::forget("user:{$pendingPayment->user_id}:trade_balance");

                Log::channel('creditcardpayissa')->info('Manual payment processed successfully: ' . $pendingPayment->transaction_id);

                $processedCount++;
            } catch (PlatformConnectionException $e) {
                try {
                    DB::select('SELECT RELEASE_LOCK(?)', ["manual_payment_{$pendingPayment->transaction_id}"]);
                } catch (Exception $lockError) {
                    // Ignore lock release errors
                }
                DB::rollBack();
                Log::error('Platform connection error processing manual payment: ' . implode(" ", $e->getErrors()), [
                    'payment_id' => $paymentId,
                ]);

                $failedPayments[] = [
                    'id' => $paymentId,
                    'email' => $pendingPayment->email,
                    'reason' => 'Platform error: ' . implode(" ", $e->getErrors()),
                ];
            } catch (Exception $e) {
                try {
                    DB::select('SELECT RELEASE_LOCK(?)', ["manual_payment_{$pendingPayment->transaction_id}"]);
                } catch (Exception $lockError) {
                    // Ignore lock release errors
                }
                DB::rollBack();
                Log::error('Failed to process manual payment: ' . $e->getMessage(), [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $failedPayments[] = [
                    'id' => $paymentId,
                    'email' => $pendingPayment->email,
                    'reason' => $e->getMessage(),
                ];
            }
        }

        $message = "Successfully processed $processedCount payment(s).";
        if (count($failedPayments) > 0) {
            $message .= " Failed to process " . count($failedPayments) . " payment(s).";
        }

        return redirect()->back()->with('success', $message)->with('failed_payments', $failedPayments);
    }

    /**
     * Reject selected pending payments.
     */
    public function rejectPayments(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:pending_manual_payments,id',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        try {
            $rejectedCount = 0;

            foreach ($request->payment_ids as $paymentId) {
                $pendingPayment = PendingManualPayment::findOrFail($paymentId);

                if (!in_array($pendingPayment->status, ['completed', 'rejected'])) {
                    $pendingPayment->update([
                        'status' => 'rejected',
                        'admin_notes' => $request->rejection_reason,
                        'processed_by' => Auth::id(),
                        'processed_at' => now(),
                    ]);

                    $rejectedCount++;
                }
            }

            return redirect()->back()->with('success', "Successfully rejected $rejectedCount payment(s).");
        } catch (Exception $e) {
            Log::error('Failed to reject manual payments: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reject payments: ' . $e->getMessage());
        }
    }

    /**
     * Update admin notes for a payment.
     */
    public function updateNotes(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        try {
            $payment = PendingManualPayment::findOrFail($id);
            $payment->admin_notes = $request->admin_notes;
            $payment->save();

            return redirect()->back()->with('success', 'Notes updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to update notes: ' . $e->getMessage());
        }
    }

    /**
     * Process promo code for a payment.
     * Note: This method is called AFTER the main deposit is successful (like secureProcessPayment in Wallet.php)
     */
    private function processPromoCode($pendingPayment, $amount, $tradeDeposit = null)
    {
        $promo = Promocode::where('code', $pendingPayment->promocode)->first();

        if (!$promo) {
            return;
        }

        $min_deposit = $promo->min_deposit;
        if ($amount < $min_deposit) {
            return;
        }

        // Calculate bonus amount
        if (isset($promo->max_deposit) && $amount >= $promo->max_deposit) {
            $bonus_amount = ($promo->promo_percentage / 100) * $promo->max_deposit;
        } else {
            $bonus_amount = ($promo->promo_percentage / 100) * $amount;
        }

        try {
            // Apply bonus credit to MT5 account
            $ticket = NULL;
            $errorCode = $this->mt5Service->tradeBalance($pendingPayment->account->code, MTEnDealAction::DEAL_BONUS, $bonus_amount, 'Promo Bonus', $ticket, true);

            if ($errorCode !== MTRetCode::MT_RET_OK) {
                Log::error('Failed to apply promo bonus: ' . MTRetCode::GetError($errorCode));
                // Don't throw here, continue with leverage update
            }

            // Create bonus transaction record
            BonusTransaction::create([
                'email' => $pendingPayment->email,
                'user_id' => $pendingPayment->user_id,
                'account_id' => $pendingPayment->account_id,
                'code' => $pendingPayment->code,
                'bonus_amount' => $bonus_amount,
                'bonus_type' => 'Bonus In',
                'status' => 1,
                'admin_remark' => 'Promo Bonus - Manual Payment',
                'bonus_currency' => 'USD',
                'transaction_id' => $pendingPayment->transaction_id,
                'promocode_id' => $promo->id
            ]);

            // Update trade deposit with promo code info if provided
            if ($tradeDeposit) {
                $tradeDeposit->promocode_percentage = $promo->promo_percentage;
                $tradeDeposit->promocode_code = $promo->code;
                $tradeDeposit->save();
            }

            // Update leverage after promo bonus (using MT5 user data like secureProcessPayment)
            $trade_user = NULL;
            $this->mt5Service->userGet($pendingPayment->account->code, $trade_user);

            if ($trade_user) {
                Log::info("account->leverage " . json_encode($pendingPayment->account->leverage));
                Log::info("balance " . json_encode($trade_user->Balance));
                Log::info("trading_account_balance " . json_encode($trade_user->Credit));

                $leverage = round($trade_user->Leverage * ($amount / ($trade_user->Balance + $trade_user->Credit)), 2);

                Log::info("calculated leverage " . json_encode($leverage));

                try {
                    $trade_user->Leverage = $leverage;
                    $updated_user = "";
                    $error_code = $this->mt5Service->userUpdate($trade_user, $updated_user);
                    if ($error_code != MTRetCode::MT_RET_OK) {
                        Log::error('Failed to update leverage: ' . MTRetCode::GetError($error_code));
                    }
                } catch (\Throwable $th) {
                    Log::error('Failed to update leverage: ' . $th->getMessage());
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to process promo code: ' . $e->getMessage());
            throw $e; // Re-throw to handle in main try-catch
        }
    }

    /**
     * Send success email for manual payment deposit
     */
    private function sendSuccessEmail($toEmail, $amount, $tradeDeposit)
    {
        $user = User::where('id', $tradeDeposit->user_id)->first();
        $settings = settings();
        $from = $settings['email_from_address'];
        $transid = $tradeDeposit->transaction_id;
        $emailSubject = $settings['admin_title'] . ' - Manual Payment Deposit Successful';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";

        $depositType = $tradeDeposit->deposit_type == "CreditCardPayissa" ? "Credit Card" : $tradeDeposit->deposit_type;

        $content = '<p style="font-size: 16px; color: #000000;">
            We are pleased to inform you that your manual payment deposit has been <b>successful</b>.
        </p>
        <p style="font-size: 16px; color: #000000;">
            The approved amount has been deposited into your account <b>' . $tradeDeposit->code . '</b>.
        </p>

        <p style="font-size: 16px; font-weight: bold; color: #000000;">Transaction Details:</p>
        <ol style="font-size: 16px; padding-left: 20px; color: #000000;">
            <li><b>Approved Amount:</b> $' . $tradeDeposit->deposit_amount . '</li>
            <li><b>Reference ID:</b> ' . $tradeDeposit->id . '</li>
            <li><b>Transaction ID:</b> <span style="word-break: break-all;">' . $transid . '</span></li>
            <li><b>Deposited Date:</b> ' . $tradeDeposit->deposted_date . '</li>
            <li><b>Payment Type:</b> ' . $depositType . '</li>
        </ol>';

        $templateVars = [
            'name' => $user->fullname,
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
