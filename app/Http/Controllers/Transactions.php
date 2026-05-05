<?php

namespace App\Http\Controllers;

use App\MT5\MTRetCode;
use App\Models\Account;
use App\MT5\MTEnDealAction;
use App\Models\TradeDeposit;
use Illuminate\Http\Request;
use App\Models\WalletDeposit;
use App\Models\WalletWithdraw;
use App\Models\BonusTransaction;
use App\Models\InternalTransfer;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use App\Services\UniversalMT5Service;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\TradeWithdrawal;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\MailService as MailService;


class Transactions extends Controller
{
    protected $mailService;
    protected $api;
    protected $settings;
    protected $mt5Service;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
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

    public function index()
    {

        $email = Auth::user()->email;

        $deposit_history1 = WalletDeposit::where('user_id',  Auth::user()->id)
            ->whereIn('deposit_type', ['CryptoChill', 'CreditCardPayissa', 'RagaPay'])
            ->orderBy('id', 'desc')
            ->get();

        $deposit_history2 = TradeDeposit::where('user_id',  Auth::user()->id)
            ->whereIn('deposit_type', ['CryptoChill', 'CreditCardPayissa', 'RagaPay'])
            ->orderBy('id', 'desc')
            ->get();

        $deposit_history = $deposit_history1->merge($deposit_history2)
            ->values(); // reset the keys

        // dd($wallet_deposit_history);
        // Fetching withdrawal history

        $withdrawal_history1 = WalletWithdraw::where('email', $email)
            ->where('withdraw_type', 'Wallet Withdrawal')
            ->orderBy('withdraw_date', 'desc')
            ->get();

        $withdrawal_history2 = TradeWithdrawals::where('email', $email)
            ->where('withdraw_type', 'Trade Withdrawal')
            ->orderBy('withdraw_date', 'desc')
            ->get();

        $withdrawal_history = $withdrawal_history2->merge($withdrawal_history1)
            ->values(); // reset the keys

        // dd($withdrawal_history);

        // Fetching internal transfers
        $internal_transfer = InternalTransfer::getTransfers(
            email: $email,
            types: ['Internal Transfer', 'Wallet Withdrawal', 'Wallet Transfer', 'CRM', 'IB Withdraw'],
            status: 1
        );

        // Sort by date descending
        $internal_transfer = $internal_transfer->sortByDesc('date')->values();

        // dd($internal_transfer);
        // $tradeWithdrawals = TradeWithdrawals::with('account')->whereIn('withdraw_type', ['Internal Transfer','Wallet Withdrawal','Wallet Transfer', 'CRM'])
        //     ->select('id','withdrawal_amount', 'withdraw_type','withdraw_date','email','status','withdraw_to','account_id')
        //     ->where('user_id', Auth::user()->id)
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
        //     ->where('user_id', Auth::user()->id)
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
        $internal_transfer = !empty($internal_transfer) ? $internal_transfer->sortByDesc('date') : collect();
        // dd($internal_transfer);
        // foreach ($internal_transfer as $key => $value) {
        //     echo ($value).'/n';
        // }
        // die;
        return view('transactions', compact('deposit_history', 'withdrawal_history', 'internal_transfer'));
    }
    public function updateTransaction(Request $request)
    {
        if (!$this->ensureMT5Connection()) {
            return redirect()->back()->with('error', 'Failed to connect to MT5 server');
        }

        // Generate a unique rate-limiting key based on user or IP
        $key = 'cancel_withdrawal:' . (auth()->id() ?: $request->ip());

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retryAfter = RateLimiter::availableIn($key);
            return redirect()->back()->with([
                'error' => "Too many requests. Please wait {$retryAfter} seconds before trying again."
            ]);
        }

        // Increment the rate limiter
        RateLimiter::hit($key, 10); // Lock for 10 seconds

        $settings = settings();
        $status = $request->status;
        if ($status == '3') {
            $validatedData = $request->validate([
                'status' => 'required|integer',
                'email' => 'required|email',
                'amount' => 'required|numeric',
            ]);
        } elseif ($status == '1') {
            $validatedData = $request->validate([
                'status' => 'required|integer',
                'email' => 'required|email',
                'amount' => 'required|numeric',
            ]);
        }
        $status = $validatedData['status'];
        $email = $validatedData['email'];
        $depositAmount = $validatedData['amount'];
        $did = $request->input('transaction_id');
        // dd($did);
        $transaction_id = $request->input('id');

        // Use DB transaction and row-level locking to prevent double processing
        DB::beginTransaction();
        try {
            $transaction = TradeWithdrawals::where('id', $did)->lockForUpdate()->first();
            if (!$transaction) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Transaction Not Found');
            }

            // Check if already processed to prevent double execution
            if ($transaction->status == 1 || $transaction->status == 3) {
                DB::rollBack();
                return redirect()->back()->with('status', 'Your transaction is already processed.');
            }

            // Update status to prevent concurrent processing
            $transaction->status = $status;
            $transaction->transaction_id = $transaction_id;
            $transaction->admin_remark = 'Cancelled by User';
            $transaction->save();

            // Process API call within the same transaction for status 3
            if ($status == 3) {
                activity()->causedBy(Auth::user()->id)
                    ->withProperties([
                        'ip' => $request->ip(),
                        'email' => Auth::user()->email,
                        'transaction_id' => $transaction_id,
                        'amount' => $depositAmount,
                        'status' => $status,
                        'remark' => 'Wallet Withdraw Cancel By Client'
                    ])
                    ->event('delete')
                    ->log('Wallet Withdraw Cancel');

                $comment = "Deposit";
                $ticket = NULL;

                // Connection handled by ensureMT5Connection() called at method start
                $errorCode = $this->api->TradeBalance($transaction->code, $typed = MTEnDealAction::DEAL_BALANCE, ($transaction->withdrawal_amount + $transaction->transaction_fee), $comment, $ticket, $margin_check = true);

                // Process bonus deduction logic
                $bonus = BonusTransaction::where('account_id', $transaction->account_id)
                    ->where(function ($query) {
                        $query->where('bonus_type', 'Bonus In')
                            ->orWhere('bonus_type', 'Bonus Out');
                    })
                    ->where('admin_remark', 'LIKE', '%Promo Bonus%')
                    ->selectRaw("
                            SUM(bonus_amount) as total_promo_bonus_amount,
                            SUM(bonus_used) as total_promo_bonus_used
                        ")
                    ->first();

                $total_promo_bonus = $bonus->total_promo_bonus_amount;
                $total_promo_bonus_used = $bonus->total_promo_bonus_used;
                $promo_left = $total_promo_bonus - $total_promo_bonus_used;

                if ($total_promo_bonus_used) {
                    $promo_deduction = $transaction->promo_deduction;

                    $remaining_deduction = $promo_deduction;
                    $account = Account::where('id', $transaction->account_id)->first();

                    Artisan::call('app:sync-account-balances', [
                        '--accounts' => $account->code,
                        '--force' => true
                    ]);

                    if (($error_codes = $this->api->TradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $promo_deduction, 'Promo Addition', $tickets, true)) !== MTRetCode::MT_RET_OK) {
                        return redirect()->back()->with('error', MTRetCode::GetError($error_codes));
                    }


                    $promos = $account->BonusTransaction()
                        ->where('admin_remark', 'Promo Bonus')
                        ->with('promocode')
                        ->orderBy('bonus_used', 'asc')
                        ->get();

                    foreach ($promos as $promo) {
                        if ($remaining_deduction <= 0) {
                            break;
                        }
                        if ($promo->bonus_used == 0) {
                            break;
                        }

                        $deduct_from_this = min($promo->bonus_used, $remaining_deduction);
                        Log::info('Promo deduction applied', [
                            'promo_id' => $promo->id,
                            'account_id' => $account->id,
                            'deducted_amount' => $deduct_from_this,
                            'remaining_deduction' => $remaining_deduction,
                            'user_id' => $transaction->user_id,
                            'transaction_id' => $transaction->id
                        ]);

                        $promo->bonus_used -= $deduct_from_this;
                        $promo->save();

                        $log = BonusTransaction::create([
                            'email' => $account->email,
                            'user_id' => $transaction->user_id,
                            'account_id' => $account->id,
                            'code' => $account->code,
                            'bonus_amount' => $deduct_from_this,
                            'bonus_type' => 'Bonus In',
                            'status' => 1,
                            'admin_remark' => 'Promo Addition',
                            'bonus_currency' => 'USD',
                        ]);

                        $transaction->promo_deduction = $transaction->promo_deduction - $deduct_from_this;
                        $transaction->save();

                        Log::info('Promo deduction BonusTransaction log created', [
                            'bonus_transaction_id' => $log->id,
                            'deducted_amount' => $deduct_from_this,
                            'user_id' => $transaction->user_id,
                            'transaction_id' => $transaction->id
                        ]);

                        $remaining_deduction -= $deduct_from_this;
                    }
                }

                // Send email notification
                if (($transaction->payout_res) == NULL) {
                    $from = $settings['email_from_address'];
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                    $emailSubject = $settings['admin_title'] . ' - Transaction Cancelled';
                    $content = '<p>We are pleased to inform you that your withdraw request has been successfully cancelled.</p>
                                <p>The cancelled amount has been credited back to your wallet.</p>
                                <p>Transaction Details</p>
                                <p>Withdrawal Cancelled Amount: ' . $depositAmount . '</p>
                                <p>Transaction ID: ' . $did . '</p>
                                <p>Withdrawal Date: ' . $transaction->withdraw_date . '</p>
                                <p>Withdrawal Type: Wallet Withdrawal</p>';

                    $templateVars = [
                        'name' => $transaction->user->fullname,
                        'site_link' => $settings['copyright_site_name_text'],
                        'email' => $settings['email_from_address'],
                        'content' => $content,
                        'title_right' => 'Transaction',
                        'subtitle_right' => 'Cancelled',
                        'btn_text' => 'Go To Dashboard',
                    ];
                    $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
                }
            }

            DB::commit();
            return redirect()->back()->with('status', 'Transaction Cancelled Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction update failed', [
                'transaction_id' => $did,
                'error' => $e->getMessage(),
                'user_id' => Auth::user()->id
            ]);
            return redirect()->back()->with('error', 'Transaction processing failed. Please try again.');
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
