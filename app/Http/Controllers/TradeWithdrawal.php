<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\MT5\MTEnDealAction;
use App\Models\ClientWallet;
use App\Models\TotalBalance;
use Illuminate\Http\Request;
use App\Models\WalletDeposit;
use App\Helpers\AccountHelper;
use App\Models\BonusTransaction;
use App\Models\ClientBankDetail;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\UniversalMT5Service;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\MailService as MailService;

class TradeWithdrawal extends Controller
{
    protected $api;
    protected $settings;
    protected $mailService;
    protected $mt5Service;

    public function __construct(MailService $mailService)
    {
        $this->settings = settings();
        $this->mailService = $mailService;
        // MT5 service will be initialized on demand to avoid startup hangs
        // MT5 connection deferred - use ensureMT5Connection() in methods that need it
        // Note: AccountHelper calls moved to individual methods to avoid startup issues
    }

    private function ensureMT5Connection()
    {
        if (!$this->mt5Service) {
            $this->mt5Service = new UniversalMT5Service();
        }

        if (!$this->mt5Service->connect()) {
            Log::error('Failed to establish MT5 connection in TradeWithdrawal');
            return false;
        }

        $this->api = $this->mt5Service->getApi();
        return true;
    }

    private function validateWithdrawalRequest(Request $request)
    {
        if (!$request->filled('client_wallet_id')) {
            throw new \Exception('Please set up wallet address.');
        }

        $request->validate([
            'account_id' => 'required',
            'withdraw_amount' => 'required|numeric|min:10',
        ], [
            'account_id.required' => 'Account is not selected.',
        ]);
    }

    private function calculateBonusAmounts($accountId)
    {
        $bonus = BonusTransaction::where('account_id', $accountId)
            ->where(function ($query) {
                $query->where('bonus_type', 'Bonus In')
                    ->orWhere('bonus_type', 'Bonus Out');
            })
            ->selectRaw("
                SUM(CASE
                    WHEN admin_remark NOT LIKE '%Credit%'
                    AND admin_remark NOT LIKE '%10x Trader Leverage%'
                    AND admin_remark NOT LIKE '%Promo Bonus%'
                    AND admin_remark NOT LIKE '%Promo Deduction%'
                    AND admin_remark NOT LIKE '%Promo Addition%'
                    AND admin_remark NOT LIKE '%Bonus Pay Off%'
                    THEN bonus_amount
                    ELSE 0
                END) AS total_bonus,

                SUM(CASE
                    WHEN admin_remark LIKE '%Promo Bonus%'
                    THEN bonus_amount
                    ELSE 0
                END) AS total_promo_bonus_amount,

                SUM(CASE
                    WHEN admin_remark LIKE '%Promo Bonus%'
                    THEN bonus_used
                    ELSE 0
                END) AS total_promo_bonus_used,

                SUM(CASE
                    WHEN admin_remark LIKE '%Promo Deduction%'
                    THEN bonus_amount
                    ELSE 0
                END) AS total_promo_deduction
            ")
            ->first();

        return [
            'total_bonus' => $bonus->total_bonus ?? 0,
            'promo_left' => ($bonus->total_promo_bonus_amount ?? 0) - ($bonus->total_promo_bonus_used ?? 0),
            'total_promo_bonus' => $bonus->total_promo_bonus_amount ?? 0,
            'total_promo_bonus_used' => $bonus->total_promo_bonus_used ?? 0,
        ];
    }

    private function validateSufficientBalance($amount, $accountBalance, $totalBonus)
    {
        if ((float)$amount > ((float)$accountBalance - (float)$totalBonus)) {
            throw new \Exception('Insufficient balance');
        }
    }

    private function calculateWithdrawalFees($amount)
    {
        if ($amount >= 100) {
            return ['withdrawal_amount' => $amount, 'withdrawal_fee' => 0];
        }
        return ['withdrawal_amount' => $amount - 5, 'withdrawal_fee' => 5];
    }

    private function handle10xLeverageAdjustment($account, $amount, $userId)
    {
        if ($account->accountType->ac_group !== 'LM\B-Book\10x\DF-B') {
            return;
        }

        $totalDepositAmount = $account->tradeDeposits->sum('deposit_amount');
        $accountBalance = $account->balance;
        $accountProfit = $accountBalance - $totalDepositAmount;

        if ($amount <= $accountProfit) {
            return; // No adjustment needed if withdrawing only profit
        }

        $multiplier = $accountProfit < 0 ? $amount : $amount - $accountProfit;
        $multiplier = min($multiplier, 250);
        $bonusAmount = -abs(-9 * $multiplier);

        $ticket = null;
        $errorCode = $this->api->TradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $bonusAmount, '10x Trader Leverage', $ticket, true);

        if ($errorCode !== MTRetCode::MT_RET_OK) {
            throw new \Exception(MTRetCode::GetError($errorCode));
        }

        $tradeUser = null;
        if ($this->api->UserGet($account->code, $tradeUser) !== MTRetCode::MT_RET_OK) {
            throw new \Exception('Failed to get user account details');
        }

        $bonusTransaction = BonusTransaction::where('email', $account->email)
            ->where('account_id', $account->id)
            ->where('user_id', $userId)
            ->where('bonus_type', 'Bonus In')
            ->where('admin_remark', '10x Trader Leverage')
            ->first();

        if ($bonusTransaction) {
            $tradeDeposit = $account->tradeDeposits->where('transaction_id', $bonusTransaction->transaction_id)->first();

            if ($tradeDeposit) {
                $leverage = round($account->leverage * ($tradeDeposit->deposit_amount) / ($tradeDeposit->deposit_amount + $tradeUser->Credit), 2);
                $tradeUser->Leverage = $leverage;

                $updatedUser = "";
                if ($this->api->UserUpdate($tradeUser, $updatedUser) !== MTRetCode::MT_RET_OK) {
                    throw new \Exception('Failed to update leverage');
                }
            }
        }

        BonusTransaction::create([
            'email' => $account->email,
            'user_id' => $userId,
            'account_id' => $account->id,
            'code' => $account->code,
            'bonus_amount' => $bonusAmount,
            'bonus_type' => 'Bonus Out',
            'status' => 1,
            'admin_remark' => '10x Trader Leverage',
            'bonus_currency' => 'USD',
        ]);

        Log::info("10x Leverage adjustment applied", [
            'account' => $account->code,
            'bonus_amount' => $bonusAmount
        ]);
    }

    private function handlePromoDeductions($account, $amount, $userId, $promoLeft)
    {
        if (!$promoLeft) {
            return 0;
        }

        $tradeDeposits = $account->tradeDeposits->where('deposit_amount', '>', 0)->sum('deposit_amount');
        $tradeWithdrawals = $account->tradeWithdrawals->where('withdrawal_amount', '>', 0)
            ->where('status', '!=', 3)
            ->sum(function ($item) {
                return $item->withdrawal_amount + $item->transaction_fee;
            });

        $depositsWithoutPromo = $account->tradeDeposits->whereNull('promocode_code')->sum('deposit_amount');
        $pnl = $account->balance - $tradeDeposits + $tradeWithdrawals;
        $amountToDeduct = $amount - $depositsWithoutPromo - $pnl;

        $totalBonusDepositValue = BonusTransaction::select(DB::raw('SUM(bonus_amount / (promocode.promo_percentage / 100)) as total'))
            ->leftJoin('promocode', 'bonus_transactions.promocode_id', '=', 'promocode.id')
            ->where('bonus_transactions.account_id', $account->id)
            ->value('total');

        $mt5Account = new \stdClass();
        if ($this->api->UserAccountGet($account->code, $mt5Account) !== MTRetCode::MT_RET_OK) {
            throw new \Exception('Unable to get MT5 account details');
        }

        // Adjust deductible amount based on balance threshold

        if ($mt5Account->Balance < $totalBonusDepositValue && $account->balance > $totalBonusDepositValue) {
            $amountToDeduct = $amount - ($account->balance - $totalBonusDepositValue);
        } elseif ($mt5Account->Balance < $totalBonusDepositValue && $account->balance <= $totalBonusDepositValue) {
            $amountToDeduct = $amount;
        }

        $totalDeductableAmount = $amountToDeduct;
        $totalPromoDeducted = 0;
        $deductedAmounts = 0;

        $promos = $account->BonusTransaction()
            ->where('admin_remark', 'Promo Bonus')
            ->with('promocode')
            ->where(function ($query) {
                $query->whereRaw('CAST(bonus_amount AS DECIMAL(10,2)) > CAST(COALESCE(bonus_used, 0) AS DECIMAL(10,2))')
                    ->orWhereNull('bonus_used');
            })
            ->get()
            ->sortByDesc(function ($transaction) {
                return optional($transaction->promocode)->promo_percentage;
            });

        Log::info("Processing promo deductions", [
            'account' => $account->code,
            'amount_to_deduct' => $amountToDeduct,
            'promo_count' => $promos->count()
        ]);

        foreach ($promos as $promo) {
            $promoPercentage = $promo->promocode ? $promo->promocode->promo_percentage : 0;

            if (!$promoPercentage || $amountToDeduct <= 0 || $mt5Account->Balance >= $totalBonusDepositValue) {
                break;
            }

            $depositAmountOfBonus = $promo->bonus_amount / ($promoPercentage / 100);
            $depositAmountOfBonusUsed = $promo->bonus_used > 0 ? $promo->bonus_used / ($promoPercentage / 100) : 0;
            $depositAmountOfBonusLeft = $depositAmountOfBonus - $depositAmountOfBonusUsed;

            $threshold = $amountToDeduct;

            $promoDeduction = 0;

            if ($depositAmountOfBonusLeft >= $threshold) {
                $promoDeduction = $threshold * ($promoPercentage / 100);
                $deductedAmounts += $threshold;
            } else {
                $promoDeduction = $depositAmountOfBonusLeft * ($promoPercentage / 100);
                $amountToDeduct -= $depositAmountOfBonusLeft;
                $deductedAmounts += $depositAmountOfBonusLeft;
            }

            if ($mt5Account->Balance == 0) {
                $maxDeductible = $promo->bonus_amount - $promo->bonus_used;
                $promoDeduction = $maxDeductible;

                // Reset leverage to original
                $tradeUser = null;
                if ($this->api->UserGet($account->code, $tradeUser) === MTRetCode::MT_RET_OK) {
                    $tradeUser->Leverage = $account->leverage;
                    $updatedUser = "";
                    $this->api->UserUpdate($tradeUser, $updatedUser);
                }
            }

            if ($promoDeduction > 0) {
                if($promoDeduction > $mt5Account->Credit){
                    $promoDeduction = $mt5Account->Credit;
                }
                $deduction = abs((float)$promoDeduction) * -1;
                $ticket = null;

                if ($this->api->TradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $deduction, 'Promo Deduction', $ticket, true) !== MTRetCode::MT_RET_OK) {
                    throw new \Exception('Failed to apply promo deduction');
                }

                $promo->bonus_used += $promoDeduction;
                $promo->save();
                $totalPromoDeducted += $promoDeduction;

                BonusTransaction::create([
                    'email' => $account->email,
                    'user_id' => $userId,
                    'account_id' => $account->id,
                    'code' => $account->code,
                    'bonus_amount' => $deduction,
                    'bonus_type' => 'Bonus Out',
                    'status' => 1,
                    'admin_remark' => 'Promo Deduction',
                    'bonus_currency' => 'USD',
                ]);

                if ($mt5Account->Balance > 0) {
                    $tradeUser = null;
                    if ($this->api->UserGet($account->code, $tradeUser) === MTRetCode::MT_RET_OK) {
                        $leverage = round($account->leverage * ($tradeDeposits) / ($tradeDeposits + $tradeUser->Credit), 2);
                        $tradeUser->Leverage = $leverage;
                        $updatedUser = "";
                        $this->api->UserUpdate($tradeUser, $updatedUser);
                    }
                }
            }

            if ($deductedAmounts >= $totalDeductableAmount) {
                break;
            }
        }

        Log::info("Promo deductions completed", [
            'account' => $account->code,
            'total_deducted' => $totalPromoDeducted
        ]);

        return $totalPromoDeducted;
    }

    public function index(Request $request)
    {
        $account_id = $request['account_id'];
        $email = auth()->user()->email;
        $user = auth()->user();

        // Ensure MT5 connection before using AccountHelper
        if ($this->ensureMT5Connection()) {
            AccountHelper::updateLiveAndDemoAccounts($user->id, $this->api);
        } else {
            Log::warning('MT5 connection failed in TradeWithdrawal index method');
        }
        // $liveaccount_details = Account::with('accountType','BonusTransaction')
        //     ->where('user_id', $user->id)
        //     ->where('demo', false)
        //     ->get();
        $liveaccount_details = Account::with([
            'accountType',
            'BonusTransaction' => function ($query) {
                $query->where('bonus_type', 'Bonus In')
                    ->orWhere('bonus_type', 'Bonus Out');
            }
        ])
            ->where('user_id', $user->id)
            ->where('account_request_status', 1)
            ->where('demo', false)
            ->get();

        $walletenabled = $user->wallet_enabled ?? false;
        $bank_details = ClientBankDetail::where('user_id', $user->id)->first() ?? [];
        $walletBalance = round(auth()->user()->wallet_balance, 2);
        $totals = Account::where('user_id', $user->id)
            ->where('demo', false)
            ->selectRaw('SUM(equity) as equity, SUM(credit) as credit, SUM(balance) as balance')
            ->first();

        $client_banks = ClientWallet::where('user_id', $user->id)
            ->where('status', 1)
            ->where('verified', 1)
            ->where('wallet_delete_verification', 0)
            ->where('deleted_at', NULL)
            ->get();
        return view('trade_withdrawal', compact('liveaccount_details', 'walletenabled', 'bank_details', 'totals', 'walletBalance', 'client_banks', 'account_id'));
    }

    // TODO: Consider RagaPay service endpoint for withdrawal/payout processing
    public function withdraw(Request $request)
    {
        try {
            // Validate wallet setup
            $this->validateWithdrawalRequest($request);

            if (!$request->filled('client_wallet_id')) {
                return redirect()->back()->with('error', 'Please set up wallet address.');
            }
            // Ensure MT5 connection
            if (!$this->ensureMT5Connection()) {
                return redirect()->back()->with('error', 'Failed to connect to MT5 server');
            }

            // Rate limiting
            $key = 'deposit:' . (auth()->id() ?: $request->ip());
            if (RateLimiter::tooManyAttempts($key, 1)) {
                $retryAfter = RateLimiter::availableIn($key);
                return redirect()->back()->with('error', "Too many requests. Please wait {$retryAfter} seconds before trying again.");
            }
            RateLimiter::hit($key, 10);

            // Get user and account details
            $userId = auth()->user()->id;
            $userEmail = auth()->user()->email;
            $userFullname = auth()->user()->fullname;
            $accountId = $request->account_id;
            $amount = $request->input('withdraw_amount');

            // Fetch account with relations
            $account = Account::with('accountType', 'tradeDeposits', 'BonusTransaction')
                ->where('id', $accountId)
                ->where('user_id', $userId)
                ->firstOrFail();

            // Sync account balances
            Artisan::call('app:sync-account-balances', [
                '--accounts' => $account->code,
                '--force' => true
            ]);

            // Calculate bonus amounts
            $bonusData = $this->calculateBonusAmounts($accountId);
            $totalBonus = $bonusData['total_bonus'];
            $promoLeft = $bonusData['promo_left'];

            // Validate sufficient balance
            $this->validateSufficientBalance($amount, $account->balance, $totalBonus);

            // Calculate withdrawal balance
            $balance = (float)$amount > (float)$account->balance
                ? abs((float)$amount - ((float)$amount - (float)$account->balance)) * -1
                : abs((float)$amount) * -1;

            // Log activity
            activity()->causedBy($userId)
                ->withProperties([
                    'ip' => $request->ip(),
                    'email' => $userEmail,
                    'code' => $account->code,
                    'withdraw_amount' => $balance,
                    'remark' => 'Account Withdraw'
                ])
                ->event('create')
                ->log('Account Withdraw');

            // Get client wallet
            $clientWalletId = $request->input('client_wallet_id');
            $clientWallet = ClientWallet::where('id', $clientWalletId)
                ->where('user_id', $userId)
                ->firstOrFail();

            // Handle 10x leverage adjustments
            $this->handle10xLeverageAdjustment($account, $amount, $userId);

            // Process MT5 withdrawal
            $ticket = null;
            $comment = 'Withdraw';
            $errorCode = $this->api->TradeBalance(
                $account->code,
                MTEnDealAction::DEAL_BALANCE,
                $balance,
                $comment,
                $ticket,
                true
            );

            if ($errorCode !== MTRetCode::MT_RET_OK) {
                Log::error('MT5 withdrawal failed', [
                    'account' => $account->code,
                    'amount' => $balance,
                    'error' => MTRetCode::GetError($errorCode)
                ]);
                return redirect()->back()->with('error', 'Withdrawal failed: ' . MTRetCode::GetError($errorCode));
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Calculate fees
                $fees = $this->calculateWithdrawalFees($amount);
                $withdrawalAmount = $fees['withdrawal_amount'];
                $withdrawalFee = $fees['withdrawal_fee'];
                Log::info("message".$withdrawalAmount);
                log::info("fee".$withdrawalFee);

                // Handle promo deductions
                $totalPromoDeducted = $this->handlePromoDeductions($account, $amount, $userId, $promoLeft);
                Log::info("message".$totalPromoDeducted);
                // Create withdrawal record
                $tradeWithdrawal = TradeWithdrawals::create([
                    'email' => $userEmail,
                    'user_id' => $userId,
                    'account_id' => $account->id,
                    'withdrawal_amount' => $withdrawalAmount,
                    'transaction_fee' => $withdrawalFee,
                    'withdraw_type' => $request->input('withdraw_type'),
                    'code' => $account->code,
                    'wallet_qr' => '',
                    'status' => 0,
                    'email_verified' => 0,
                    'client_wallet_id' => $clientWallet->id,
                    'promo_deduction' => $totalPromoDeducted
                ]);

                DB::commit();

                // Send verification email
                $this->sendWithdrawalVerificationEmail($tradeWithdrawal, $userEmail, $userFullname, $account, $withdrawalAmount);

                Log::info('Withdrawal request created successfully', [
                    'withdrawal_id' => $tradeWithdrawal->id,
                    'account' => $account->code,
                    'amount' => $withdrawalAmount
                ]);

                return redirect()->route('transactions', ['tab' => 'withdrawals'])->with('status', 'Verification email sent successfully.');

            } catch (\Exception $e) {
                DB::rollBack();

                // Reverse the MT5 withdrawal
                $reverseBalance = abs((float)$balance);
                $this->api->TradeBalance(
                    $account->code,
                    MTEnDealAction::DEAL_BALANCE,
                    $reverseBalance,
                    'Withdrawal Reversal',
                    $ticket,
                    true
                );

                Log::error('Withdrawal transaction failed', [
                    'account' => $account->code,
                    'error' => $e->getMessage()
                ]);

                throw $e;
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Account or wallet not found.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Withdrawal error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function sendWithdrawalVerificationEmail($tradeWithdrawal, $userEmail, $userFullname, $account, $withdrawalAmount)
    {
        $type = 'Withdrawal Details Verification';
        $from = $this->settings['email_from_address'];
        $emailSubject = $this->settings['admin_title'] . ' - ' . $type;
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $this->settings['admin_title'] . '<' . $from . '>' . "\r\n";

        $content = '<p>Welcome to ' . htmlspecialchars($this->settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</p>' .
            '<p></p>' .
            '<p>You are receiving this email because you have requested a withdrawal of amount $' . $withdrawalAmount . ' from your account ' . $account->code . '</p>' .
            '<p></p>' .
            '<p>Click the link below to activate your Account Withdrawal</p>';

        $templateVars = [
            'name' => $userFullname,
            'server_name' => $this->settings['mt5_company_name'],
            'site_link' => $this->settings['copyright_site_name_text'] . "/account_withdrawal_verify?accountWithdrawal_id=$tradeWithdrawal->id",
            'email' => $from,
            "content" => $content,
            "title_right" => "Activate",
            "subtitle_right" => "Your Account Withdrawal Request",
            "btn_text" => "Verify"
        ];

        $blockedEmails = [
            'topzplaza18@gmail.com',
            'lhenriquega@gmail.com',
            'luchatrader23fx@gmail.com',
            'alexbostontrading@gmail.com',
            'alisakotsa@gmail.com',
            'abhay@lqhmarkets.com',
        ];

        if (!in_array($userEmail, $blockedEmails)) {
            $this->mailService->sendEmail($userEmail, $emailSubject, $headers, '', $templateVars);
        }
    }

    public function account_withdrawal_verify(Request $request)
    {

        if (!auth()->check()) {
            return redirect('/login');
        }

        $settings = settings();
        $id = auth()->user()->id;
        $accountWithdrawal_id = $request->query('accountWithdrawal_id');

        $new_wallet_Withdrawal = TradeWithdrawals::with('user')->where('user_id', $id)
            ->where('id', $accountWithdrawal_id)
            ->first();

        if ($new_wallet_Withdrawal) {
            if ($new_wallet_Withdrawal->email_verified  == 0) {
                $new_wallet_Withdrawal->email_verified = 1;
                $new_wallet_Withdrawal->save();
                activity()->causedBy(auth()->user())
                    ->withProperties(
                        [
                            'ip' => $request->ip(),
                            'email' => auth()->user()->email,
                            'withdraw_amount' => $new_wallet_Withdrawal->withdrawal_amount,
                            // 'withdraw_transaction_fee' => $new_wallet_Withdrawal->withdraw_transaction_fee,
                            'wallet_withdraw_id' => $new_wallet_Withdrawal->id,
                            'remark' => 'Trade Withdraw'
                        ]
                    )
                    ->event('create')
                    ->log('Account Withdraw');
                $from = $settings['email_from_address'];
                $emailSubject = $settings['admin_title'] . ' - Thank You for Confirming Your Trade Withdrawal';
                $htmlContent = "";
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $content =
                    '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
                    '<div>Your withdrawal has been confirmed, your funds will be processed shortly.</div>'.
                    '<div>If you have any questions, our support team is ready to assist.</div>';
                $templateVars = [
                    'name' => $new_wallet_Withdrawal->user->fullname,
                    'server_name' => $settings['mt5_company_name'],
                    'email' => $settings['email_from_address'],
                    "content" => $content,
                    "title_right" => "Account Withdrawal Verification",
                    "subtitle_right" => "Successful",
                ];
                $this->mailService->sendEmail($new_wallet_Withdrawal->user->email, $emailSubject, $headers, '', $templateVars);
                // return redirect()->route('transactions')->with('status', 'Your withdrawal request has been successfully verified.');
                return redirect()->route('transactions', ['tab' => 'withdrawals'])->with('status', 'Your withdrawal request has been successfully verified.');
            } else {
                return redirect()->route('dashboard')->with('error', 'Sorry! Account Withdrawal is already Verified');
            }
        } else {
            return redirect()->route('dashboard')->with('error', 'Sorry! No Account Withdrawal Found. Signup here');
        }
    }
}
