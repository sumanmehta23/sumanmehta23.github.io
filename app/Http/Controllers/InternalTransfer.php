<?php

namespace App\Http\Controllers;

use App\MT5\MTRetCode;
use App\Models\Account;
use App\Models\LiveAccount;
use App\MT5\MTEnDealAction;
use App\Models\TotalBalance;
use App\Models\TradeDeposit;
use Illuminate\Http\Request;
use App\Helpers\AccountHelper;
use App\Models\BonusTransaction;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\UniversalMT5Service;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\RateLimiter;

class InternalTransfer extends Controller
{
    protected $api;
    protected $mt5Service;

    public function __construct()
    {
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
        $email = auth()->user()->email;
        AccountHelper::updateLiveAndDemoAccounts(auth()->user()->id, $this->api);
        $liveaccount_details = auth()->user()->liveAccounts()->with([
            'accountType',
            'BonusTransaction' => function ($query) {
                $query->where('bonus_type', 'Bonus In')
                    ->orWhere('bonus_type', 'Bonus Out');
            }
        ])->withCount(['tradeDeposits as successful_trade_deposits_count' => function ($query) {
            $query->where('status', 1);
        }])
            ->where('account_request_status', "!=", "0")
            ->where(function ($query) {
                $query->whereNull('created_from')
                    ->orWhere('created_from', '!=', 'zapier');
            })
            ->get();
        // dd($liveaccount_details[8]->BonusTransaction->sum('bonus_amount'));
        return view('internal-transfer', compact('liveaccount_details'));
    }
    public function processTransfer(Request $request)
    {

        // Generate a unique rate-limiting key based on user or IP
        $key = 'deposit:' . (auth()->id() ?: $request->ip());

        // Check if the user has exceeded the rate limit
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retryAfter = RateLimiter::availableIn($key);

            redirect()->back()->with('error', "Too many requests.Please wait {$retryAfter} seconds before trying again.");
        }

        // Increment the rate limiter
        RateLimiter::hit($key, 10); // Lock for 10 seconds

        if (!$this->ensureMT5Connection()) {

            redirect()->back()->with('error', "Unable to connect to trading server. Please trying again.");
        }
        $validated = $request->validate([
            'fromAccount' => 'required',
            'toAccount' => 'required|different:fromAccount',
            'transferable_amount' => 'required|numeric|min:.01',
        ]);
        $fromAccountId = $request->input('fromAccount');
        $toAccountId = $request->input('toAccount');
        $user = auth()->user();
        $userId = $user->id;
        $fromAccount = Account::where(['id' => $fromAccountId, 'user_id' => $userId])->firstOrFail();
        $toAccount = Account::where(['id' => $toAccountId, 'user_id' => $userId])->withCount(['tradeDeposits as successful_trade_deposits_count' => function ($query) {
            $query->where('status', 1);
        }])->firstOrFail();

        // Block Zapier accounts from internal transfer
        if ($fromAccount->isZapierAccount() || $toAccount->isZapierAccount()) {
            return redirect()->back()->with('error', 'Internal transfer is not available for promotional accounts.');
        }
        // dump($fromAccount);
        //         dd($toAccount->accountType->ac_group);


        // Get current balance from API
        $apiAccountData =  AccountHelper::getAccount($fromAccount->code);

        if (!isset($apiAccountData['balance'])) {
            Log::error('Failed to get current balance from API', [
                'account' => $fromAccount->code,
                'user_id' => $user->id
            ]);
            return redirect()->back()->with('error', 'Unable to verify account balance. Please try again.');
        }

        // Store the latest balance and equity from API
        $fromAccount->update([
            'balance' => $apiAccountData['balance'],
            'equity' => $apiAccountData['equity'] ?? $apiAccountData['balance'],
        ]);

        // Refresh model instance to get updated values
        $fromAccount->refresh();

        $total_bonus = BonusTransaction::where('account_id', $fromAccount->id)
            ->where(function ($query) {
                $query->where('bonus_type', 'Bonus In')
                    ->orWhere('bonus_type', 'Bonus Out');
            })
            ->whereNotIn('admin_remark', ['Credit', '10x Trader Leverage', 'Bonus Pay Off', 'Promo Bonus', 'Promo Deduction', 'Promo Addition'])
            ->sum('bonus_amount');

        $transferable_amount = $request->input('transferable_amount');

        if ((float)$transferable_amount > (float)$fromAccount->balance - (float)$total_bonus) {
            return redirect()->back()->with('error', 'Insufficient balance');
        }
        //       dd($transferable_amount);
        $email = auth()->user()->email;
        $ticket = NULL;
        $ticket1 = NULL;

        activity()->causedBy(auth()->user()->id)
            ->withProperties(
                [
                    'ip' => $request->ip(),
                    'email' => auth()->user()->email,
                    'from' => $fromAccount->code,
                    'to' => $toAccount->code,
                    'transfer_amount' => $transferable_amount,
                    'remark' => 'Internal Transfer'
                ]
            )
            ->event('create')
            ->log('Internal Transfer');

        // Track which API operations succeeded for potential rollback
        $apiOperations = [
            'source_withdraw' => false,
            'source_bonus' => false,
            'dest_bonus' => false,
            'dest_deposit' => false,
        ];

        try {
            $Comment = 'Internal T- O:' . $fromAccount->code . ' - D:' . $toAccount->code;

            // Step 1: Withdraw from source account (BEFORE DB transaction)
            $errorCode = $this->api->TradeBalance($fromAccount->code, MTEnDealAction::DEAL_BALANCE, -$transferable_amount, $Comment, $ticket, true);
            if ($errorCode != MTRetCode::MT_RET_OK) {
                throw new \Exception('Failed to withdraw from the account: ' . MTRetCode::GetError($errorCode));
            }
            $apiOperations['source_withdraw'] = true;

            // Step 2: Apply source account bonus deduction if applicable (BEFORE DB transaction)
            $sourceBonusAmount = 0;
            if ($fromAccount->accountType->ac_group == 'LM\B-Book\10x\DF-B') {
                $multiplier = $transferable_amount;
                if ($multiplier > 250) {
                    $multiplier = 250;
                }
                $sourceBonusAmount = -abs(-9 * $multiplier);

                $bonus_left = BonusTransaction::where('account_id', $fromAccount->id)
                    ->where(function ($query) {
                        $query->where('bonus_type', 'Bonus In')
                            ->orWhere('bonus_type', 'Bonus Out');
                    })
                    ->whereNotIn('admin_remark', ['Credit', '10x Trader Leverage', 'Bonus Pay Off', 'Promo Bonus', 'Promo Deduction', 'Promo Addition'])
                    ->sum('bonus_amount');

                if (isset($bonus_left) && $bonus_left > 1) {
                    $error_code = $this->api->TradeBalance($fromAccount->code, MTEnDealAction::DEAL_BONUS, $sourceBonusAmount, '10x Trader Leverage', $ticket, true);
                    if ($error_code !== MTRetCode::MT_RET_OK) {
                        throw new \Exception('Failed to apply source bonus deduction: ' . MTRetCode::GetError($error_code));
                    }
                    $apiOperations['source_bonus'] = true;
                }
            }

            // Step 3: Apply destination account bonus credit if applicable (BEFORE DB transaction)
            $destBonusAmount = 0;
            if ($toAccount->accountType->ac_group == 'LM\B-Book\10x\DF-B' && $toAccount->successful_trade_deposits_count == 0) {
                if ($transferable_amount > 250) {
                    $destBonusAmount = 9 * 250;
                } else {
                    $destBonusAmount = 9 * $transferable_amount;
                }

                $error_code1 = $this->api->TradeBalance($toAccount->code, MTEnDealAction::DEAL_BONUS, $destBonusAmount, '10x Trader Leverage', $ticket1, true);
                if ($error_code1 !== MTRetCode::MT_RET_OK) {
                    throw new \Exception('Failed to apply destination bonus credit: ' . MTRetCode::GetError($error_code1));
                }
                $apiOperations['dest_bonus'] = true;
            }

            // Step 4: Deposit to destination account (BEFORE DB transaction)
            $errorCode = $this->api->TradeBalance($toAccount->code, MTEnDealAction::DEAL_BALANCE, $transferable_amount, $Comment, $ticket, true);
            if ($errorCode != MTRetCode::MT_RET_OK) {
                throw new \Exception('Failed to deposit to the account: ' . MTRetCode::GetError($errorCode));
            }
            $apiOperations['dest_deposit'] = true;

            // Sync account balances
            Artisan::call('app:sync-account-balances', [
                '--accounts' => $fromAccount->code,
                '--force' => true
            ]);

            // Calculate bonus amounts
            $bonusData = $this->calculateBonusAmounts($fromAccount->id);
            $totalBonus = $bonusData['total_bonus'];
            $promoLeft = $bonusData['promo_left'];
            // Log::info("Calculated bonus amounts", [
            //     'total_bonus' => $totalBonus,
            //     'promo_left' => $promoLeft
            // ]);
            // Validate sufficient balance
            $this->validateSufficientBalance($transferable_amount, $fromAccount->balance, $totalBonus);

            // Calculate withdrawal balance
            $balance = (float)$transferable_amount > (float)$fromAccount->balance
                ? abs((float)$transferable_amount - ((float)$transferable_amount - (float)$fromAccount->balance)) * -1
                : abs((float)$transferable_amount) * -1;
            // Log::info("Calculated withdrawal balance", [
            //     'transferable_amount' => $transferable_amount,
            //     'account_balance' => $fromAccount->balance,
            //     'calculated_balance' => $balance
            // ]);

            $totalPromoDeducted = $this->handlePromoDeductions($fromAccount, $transferable_amount, $userId, $promoLeft);
            Log::info("message".$totalPromoDeducted);

            $userFullname = auth()->user()->fullname;

            // Step 5: All API operations succeeded, now execute DB transaction
            DB::transaction(function () use ($email, $fromAccount, $toAccount, $transferable_amount, $sourceBonusAmount, $destBonusAmount, $userId, $totalPromoDeducted, $request) {
                $customerID = auth()->user()->id;

                // Create withdrawal record
                TradeWithdrawals::create([
                    'email' => $email,
                    'user_id' => $customerID,
                    'account_id' => $fromAccount->id,
                    'code' => $fromAccount->code,
                    'withdrawal_amount' => $transferable_amount,
                    'withdraw_type' => 'Internal Transfer',
                    'withdraw_to' => $toAccount->id,
                    'withdraw_date' => now(),
                    'status' => 1,
                    'promo_deduction' => $totalPromoDeducted
                ]);

                // Log source account bonus deduction if it was applied
                if ($sourceBonusAmount != 0) {
                    BonusTransaction::create([
                        'email' => $fromAccount->email,
                        'user_id' => $customerID,
                        'account_id' => $fromAccount->id,
                        'code' => $fromAccount->code,
                        'bonus_amount' => $sourceBonusAmount,
                        'bonus_type' => 'Bonus Out',
                        'status' => 1,
                        'admin_remark' => '10x Trader Leverage',
                        'bonus_currency' => 'USD',
                    ]);
                }

                // Log destination account bonus credit if it was applied
                if ($destBonusAmount != 0) {
                    BonusTransaction::create([
                        'email' => $email,
                        'user_id' => $customerID,
                        'account_id' => $toAccount->id,
                        'code' => $toAccount->code,
                        'bonus_amount' => $destBonusAmount,
                        'bonus_type' => 'Bonus In',
                        'status' => 1,
                        'admin_remark' => '10x Trader Leverage',
                        'bonus_currency' => 'USD',
                    ]);
                }

                // Create deposit records
                TradeDeposit::create([
                    'user_id' => auth()->user()->id,
                    'account_id' => $toAccount->id,
                    'email' => $email,
                    'code' => $toAccount->code,
                    'deposit_amount' => $transferable_amount,
                    'deposit_type' => 'Internal Transfer',
                    'deposit_from' => $fromAccount->id,
                    'status' => 1,
                    'callback_code' => 'success'
                ]);

                TotalBalance::create([
                    'user_id' => auth()->user()->id,
                    'account_id' => $toAccount->id,
                    'email' => $email,
                    'code' => $toAccount->code,
                    'trading_deposited' => $transferable_amount,
                    'deposit_type' => 'Internal Transfer',
                ]);
            });
        } catch (\Throwable $th) {
            Log::error('Internal transfer failed - attempting API rollback', [
                'message' => $th->getMessage(),
                'from_account' => $fromAccount->code,
                'to_account' => $toAccount->code,
                'transfer_amount' => $transferable_amount,
                'user_id' => auth()->user()->id,
                'api_operations' => $apiOperations
            ]);

            // Reverse API operations in reverse order (compensating transactions)
            if ($apiOperations['dest_deposit']) {
                $rollbackErrorCode = $this->api->TradeBalance($toAccount->code, MTEnDealAction::DEAL_BALANCE, -$transferable_amount, 'rollback deposit', $ticket, true);
                if ($rollbackErrorCode != MTRetCode::MT_RET_OK) {
                    Log::critical('CRITICAL: Failed to reverse deposit - destination account may be inconsistent', [
                        'code' => $toAccount->code,
                        'amount' => $transferable_amount,
                        'error' => MTRetCode::GetError($rollbackErrorCode)
                    ]);
                }
            }

            if ($apiOperations['dest_bonus']) {
                $rollbackErrorCode = $this->api->TradeBalance($toAccount->code, MTEnDealAction::DEAL_BONUS, -$destBonusAmount, 'rollback bonus', $ticket1, true);
                if ($rollbackErrorCode != MTRetCode::MT_RET_OK) {
                    Log::critical('CRITICAL: Failed to reverse destination bonus - destination account may be inconsistent', [
                        'code' => $toAccount->code,
                        'bonus_amount' => $destBonusAmount,
                        'error' => MTRetCode::GetError($rollbackErrorCode)
                    ]);
                }
            }

            if ($apiOperations['source_bonus']) {
                $rollbackErrorCode = $this->api->TradeBalance($fromAccount->code, MTEnDealAction::DEAL_BONUS, -$sourceBonusAmount, 'rollback bonus', $ticket, true);
                if ($rollbackErrorCode != MTRetCode::MT_RET_OK) {
                    Log::critical('CRITICAL: Failed to reverse source bonus - source account may be inconsistent', [
                        'code' => $fromAccount->code,
                        'bonus_amount' => $sourceBonusAmount,
                        'error' => MTRetCode::GetError($rollbackErrorCode)
                    ]);
                }
            }

            if ($apiOperations['source_withdraw']) {
                $rollbackErrorCode = $this->api->TradeBalance($fromAccount->code, MTEnDealAction::DEAL_BALANCE, $transferable_amount, 'rollback withdraw', $ticket, true);
                if ($rollbackErrorCode != MTRetCode::MT_RET_OK) {
                    Log::critical('CRITICAL: Failed to reverse withdrawal - source account may be inconsistent', [
                        'code' => $fromAccount->code,
                        'amount' => $transferable_amount,
                        'error' => MTRetCode::GetError($rollbackErrorCode)
                    ]);
                }
            }

            return redirect()->back()->with('error', 'Transfer failed: ' . $th->getMessage());
        }
        return redirect()->back()->with('success', 'Internal Transfer Successfully Done');
    }

    private function calculateBonusAmounts($accountId)
    {
        // log::info("Calculating bonus amounts for account", ['account_id' => $accountId]);
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
}
