<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Country;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Services\MailService;
use App\Models\TradeWithdrawals;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use App\Http\Controllers\TradeWithdrawal;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use  HasFactory, Notifiable, HasUuids, TwoFactorAuthenticatable,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'aspnetusers';

    protected $primaryKey = 'id';
    protected $guarded = [];

    public $timestamps = false;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'customerio_last_error' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'kyc_synced_at' => 'datetime',
    ];

    protected static function booted()
    {
        parent::booted();
        // dd('check');

        static::updated(function ($user) {

            // Check if the email was updated
            if ($user->isDirty('email')) {
                // Get the old and new email values
                $oldEmail = $user->getOriginal('email');
                $newEmail = $user->email;


                // Update the email in related tables
                Account::where('email', $oldEmail)->update(['email' => $newEmail]);
                WalletWithdraw::where('email', $oldEmail)->update(['email' => $newEmail]);
                WalletDeposit::where('email', $oldEmail)->update(['email' => $newEmail]);
                TradeWithdrawals::where('email', $oldEmail)->update(['email' => $newEmail]);
                TradeDeposit::where('email', $oldEmail)->update(['email' => $newEmail]);
                TotalBalance::where('email', $oldEmail)->update(['email' => $newEmail]);
                BonusTransaction::where('email', $oldEmail)->update(['email' => $newEmail]);
                DemoDeposit::where('email', $oldEmail)->update(['email' => $newEmail]);
                Ib1::where('email', $oldEmail)->update(['email' => $newEmail]);
                LoginHistory::where('email', $oldEmail)->update(['email' => $newEmail]);

                // Add other table updates as necessary
            }
        });
    }

    public function BonusTransaction()
    {
        return $this->hasMany(BonusTransaction::class);
    }
    // Has many live accounts
    public function liveAccounts()
    {
        return $this->hasMany(Account::class)->where('demo', false);
    }

    // Has many demo accounts
    public function demoAccounts()
    {
        return $this->hasMany(Account::class)->where('demo', true);
    }
    public function ib1Commissions()
    {
        return $this->hasMany(Ib1Commission::class);
    }
    public function ib()
    {
        return $this->hasOne(Ib1::class);
    }

    public function parentib()
    {
        return $this->hasOne(Ib1::class, 'referral_code', 'ib1');
    }

    public function employee()
    {
        return $this->belongsToMany(
            EmployeeList::class,
            'relationship_manager',
            'user_id',
            'rm_id'
        )
            ->withPivot('added_by');
    }

    public function relationshipManager()
    {
        return $this->hasOne(RelationshipManager::class, 'user_id');
    }

    // public function getCountry()
    // {
    //     return Country::where('country_name', '=', $this->country)
    //         ->first();
    // }

    public function getParentIb()
    {
        if (is_null($this->ib1)) {
            return null;
        }

        return Ib1::where('referral_code', $this->ib1)
            ->orWhere('referral_code', $this->email)
            ->first();
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
    public function wallets()
    {
        return $this->hasMany(ClientWallet::class);
    }
    public function walletDeposits()
    {
        return $this->hasMany(WalletDeposit::class);
    }

    public function walletWithdraws()
    {
        return $this->hasMany(WalletWithdraw::class);
    }
    public function countryDetail()
    {
        return $this->belongsTo(Country::class, 'country', 'country_name');
    }

    /**
     * Get all KYC logs for this user
     */
    public function kycLogs()
    {
        return $this->hasMany(KycLog::class);
    }

    /**
     * Get the latest KYC log (for efficient access without N+1)
     */
    public function latestKycLog()
    {
        return $this->hasOne(KycLog::class)
            ->latest('created_at');
    }

    /**
     * Get KYC status from latest log payload
     * Usage: $user->kyc_status_from_log
     * FAST: Uses eager-loaded relationship, no extra queries
     */
    public function getKycStatusFromLogAttribute(): ?string
    {
        // Use eager-loaded relationship (set via withLatestKycLog scope)
        // Only lazy-load if not already loaded to avoid N+1 queries in loops
        $latestLog = $this->relationLoaded('latestKycLog') 
            ? $this->latestKycLog 
            : $this->latestKycLog()->first();
        
        if (!$latestLog || !$latestLog->callback_payload) {
            return null;
        }

        $payload = $latestLog->callback_payload;
        $reviewStatus = $payload['reviewStatus'] ?? null;
        $reviewResult = $payload['reviewResult'] ?? [];
        $reviewAnswer = $reviewResult['reviewAnswer'] ?? null;

        // Determine status from review answer
        return match ($reviewAnswer) {
            'GREEN' => 'APPROVED',
            'RED' => 'REJECTED',
            default => strtoupper($reviewStatus ?? 'PENDING'),
        };
    }

    /**
     * Get KYC reason from latest log payload
     * Usage: $user->kyc_reason_from_log
     * FAST: Uses eager-loaded relationship, no extra queries
     */
    public function getKycReasonFromLogAttribute(): ?string
    {
        // Use eager-loaded relationship (set via withLatestKycLog scope)
        // Only lazy-load if not already loaded to avoid N+1 queries in loops
        $latestLog = $this->relationLoaded('latestKycLog') 
            ? $this->latestKycLog 
            : $this->latestKycLog()->first();
        
        if (!$latestLog || !$latestLog->callback_payload) {
            return null;
        }

        $payload = $latestLog->callback_payload;
        $reviewResult = $payload['reviewResult'] ?? [];
        $reviewAnswer = $reviewResult['reviewAnswer'] ?? null;
        $rejectLabels = $reviewResult['rejectLabels'] ?? [];
        $rejectReasons = $reviewResult['rejectReasons'] ?? [];

        // Build reason based on status
        if ($reviewAnswer === 'GREEN') {
            return null; // No reason for approved
        } elseif ($reviewAnswer === 'RED') {
            // Build rejection reason from labels
            if (!empty($rejectLabels)) {
                return implode(' | ', array_map(function($label) {
                    return ucfirst(strtolower(str_replace('_', ' ', $label)));
                }, $rejectLabels));
            } elseif (!empty($rejectReasons)) {
                return implode(' | ', $rejectReasons);
            }
            return 'KYC verification rejected';
        } else {
            return 'KYC verification in progress';
        }
    }

    /**
     * Eager load latest KYC log to avoid N+1 queries
     * Usage: User::withLatestKycLog()->get()
     */
    public function scopeWithLatestKycLog($query)
    {
        return $query->with(['latestKycLog' => function ($q) {
            $q->select('id', 'user_id', 'callback_payload', 'created_at');
        }]);
    }
    // {
    //     $totalDeposit = WalletDeposit::where('user_id', $userId)
    //         ->where('status', 1)
    //         ->sum('deposit_amount');
    //     $totalWithdraw = WalletWithdraw::where('user_id', $userId)
    //         ->whereNotIn('status',[2,3])
    //         ->sum('withdraw_amount');
    //     $totalWithdrawFee = WalletWithdraw::where('user_id', $userId)
    //         ->whereNotIn('status',[2,3])
    //         ->sum('withdraw_transaction_fee');
    //     $walletBalance = $totalDeposit - ($totalWithdraw + $totalWithdrawFee);
    //     return round($walletBalance,2);
    // }
    public function getWalletBalanceAttribute()
    {
        return Cache::remember("user:{$this->id}:wallet_balance", now()->addMinutes(10), function () {
            $totalDeposit = WalletDeposit::where('user_id', $this->id)
                ->where('status', 1)
                ->sum('deposit_amount');

            $totalWithdraw = WalletWithdraw::where('user_id', $this->id)
                ->whereNotIn('status', [2, 3])
                ->sum('withdraw_amount');
            $totalWithdrawFee = WalletWithdraw::where('user_id', $this->id)
                ->whereNotIn('status', [2, 3])
                ->sum('withdraw_transaction_fee');

            return round((float) $totalDeposit - ((float) $totalWithdraw + (float) $totalWithdrawFee), 2);
        });
    }

    public function getTotalWdAttribute()
    {

        return WalletDeposit::where('user_id', $this->id)
            ->whereIn('deposit_type', ['CryptoChill', 'CreditCardPayissa'])
            ->where('status', 1)
            ->sum('deposit_amount');
    }

    public function getTotalWwAttribute()
    {
        return WalletWithdraw::where('user_id', $this->id)
            ->where('withdraw_type', 'Wallet Withdrawal')
            ->where('status', 1)
            ->selectRaw('SUM(withdraw_amount + COALESCE(withdraw_transaction_fee, 0)) as total')
            ->value('total');
    }

    public function getNewTotalDepositAttribute()
    {
        return TradeDeposit::withTrashed()
            ->where('user_id', $this->id)
            ->whereIn('deposit_type', ['CryptoChill', 'CreditCardPayissa'])
            ->where('status', 1)
            ->sum('deposit_amount');
    }

    public function getNewTotalWithdrawalAttribute()
    {
        return TradeWithdrawals::withTrashed()
            ->where('user_id', $this->id)
            ->where('withdraw_type', 'Trade Withdrawal')
            ->where('status', 1)
            ->selectRaw('SUM(withdrawal_amount + COALESCE(transaction_fee, 0)) as total')
            ->value('total');
    }

    public function getPendingWwAttribute()
    {
        return WalletWithdraw::withTrashed()
            ->where('user_id', $this->id)
            ->where('status', 0)
            ->selectRaw('SUM(withdraw_amount + COALESCE(withdraw_transaction_fee, 0)) as total')
            ->value('total');
    }

    public function getTotalBalanceAttribute()
    {
        return TotalBalance::withTrashed()
            ->where('user_id', $this->id)
            ->selectRaw('
                SUM(deposit_amount) as deposit_amount,
                SUM(trading_deposited) as trading_deposited,
                SUM(trading_withdrawal) as trading_withdrawal,
                SUM(withdraw_amount) as withdraw_amount')
            ->first();
    }

    public function getBankDetailsAttribute()
    {
        return DB::table('clientbankdetails')->where('userId', $this->id)->first();
    }

    public function getKycDetailsAttribute()
    {
        return DB::table('kyc_update')->where('email', $this->email)->get();
    }

    public function getIbDetailsAttribute()
    {
        return DB::table('ib1')
            ->leftJoin('ib_wallet', 'ib1.user_id', '=', 'ib_wallet.user_id')
            ->leftJoin('account_types as ac', 'ac.ac_index', '=', 'ib1.acc_type')
            ->select('ib1.*', DB::raw('SUM(ib_wallet.ib_wallet) as deposit'), DB::raw('SUM(ib_wallet.ib_withdraw) as withdraw'), 'ac.ac_name')
            ->where('ib1.status', 1)
            ->where('ib1.user_id', $this->id)
            ->groupBy('ib1.email')
            ->havingRaw('COUNT(ib1.email) > 0')
            ->first();
    }

    public function getRmDetailsAttribute()
    {
        return DB::table('relationship_manager as rm')
            ->leftJoin('emplist as emp', 'rm.rm_id', '=', 'emp.email')
            ->select('emp.client_index', 'emp.username', 'rm.*')
            ->where('rm.user_id', $this->id)
            ->first();
    }

    public function getSuperadminDetailsAttribute()
    {
        return DB::table('emplist')->where('role_id', 1)->first();
    }

    public function getCountryCodeAttribute()
    {
        return $this->countryDetail;
    }

    /**
     * Get affiliate parent user
     * Usage: $user->affiliateParent()
     */
    public function affiliateParent()
    {
        if (!$this->cxd) return null;

        $cxdValue = strpos($this->cxd, '_') !== false
            ? substr($this->cxd, 0, strpos($this->cxd, '_'))
            : $this->cxd;

        return Affiliate::where('custom_id', $cxdValue)->first();
    }

    /**
     * Get affiliate children users
     * Usage: $user->affiliateChildren()
     */
    public function affiliateChildren()
    {
        if (!$this->affiliate_id) return collect();

        return User::where('cxd', $this->affiliate_id)
            ->orWhere('cxd', 'LIKE', $this->affiliate_id . '_%')
            ->get();
    }

    public function getClientsAttribute()
    {
        if (!$this->ib) {
            return collect(); // Return an empty collection if the user has no IB.
        }

        $referralCode = $this->ib->referral_code ? $this->ib->referral_code : $this->ib->email;

        // Query aspnetusers with aggregated data from accounts and trade_deposits
        $clients = DB::table('aspnetusers as au')
            ->leftJoin('accounts as acc', function ($join) {
                $join->on('acc.user_id', '=', 'au.id')
                    ->where('acc.demo', '=', 0);
            })
            ->leftJoin('trade_deposits as td', function ($join) {
                $join->on('td.user_id', '=', 'au.id')
                    ->where('td.status', '=', 1);
            })
            ->where(function ($query) use ($referralCode) {
                for ($i = 1; $i <= 15; $i++) {
                    $query->orWhere("au.ib$i", $referralCode);
                }
            })
            ->select(
                DB::raw('COUNT(DISTINCT acc.id) AS liveaccounts'),
                DB::raw('SUM(DISTINCT td.deposit_amount) AS total_deposit'),
                'au.*'
            )
            ->groupBy('au.id')
            ->get();

        // Group clients by level (ib1, ib2, ..., ib15)
        $groupedClients = $clients->mapToGroups(function ($client) use ($referralCode) {
            foreach (range(1, 15) as $level) {
                if ($client->{"ib{$level}"} === $referralCode) {
                    return [$level => $client];
                }
            }
            return [];
        });

        return $groupedClients;
    }

    public function getIbTotalDepositsAttribute()
    {
        if (!$this->ib) {
            return 0; // Return 0 if the user has no IB
        }

        $referralCode = $this->ib->referral_code ?: $this->ib->getAttribute('referral_code');

        // Perform a single query to calculate the total deposit
        $totalDeposit = DB::table('aspnetusers as au')
            ->leftJoin('trade_deposits as td', function ($join) {
                $join->on('td.user_id', '=', 'au.id')
                    ->where('td.status', '=', 1);
            })
            ->where(function ($query) use ($referralCode) {
                for ($i = 1; $i <= 15; $i++) {
                    $query->orWhere("au.ib{$i}", $referralCode);
                }
            })
            ->sum(DB::raw('DISTINCT td.deposit_amount'));

        return $totalDeposit ?? 0;
    }

    public function getIbTotalWithdrawalAttribute()
    {
        if (!$this->ib) {
            return 0; // Return 0 if the user has no IB.
        }

        $referralCode = $this->ib->referral_code ? $this->ib->referral_code : $this->ib->email;
        // Dynamically build the query for all 15 levels using a single query.
        $totalWithdrawal = DB::table('aspnetusers as au')
            ->leftJoin('trade_withdrawal as tw', function ($join) {
                $join->on('tw.user_id', '=', 'au.id')
                    ->where('tw.status', '=', 1)->where('tw.withdraw_type', '=', 'Trade Withdrawal');
            })
            ->where(function ($query) use ($referralCode) {
                for ($i = 1; $i <= 15; $i++) {
                    $query->orWhere("au.ib$i", $referralCode);
                }
            })
            ->sum(DB::raw('DISTINCT (tw.withdrawal_amount + COALESCE(tw.transaction_fee, 0))'));

        // No withdrawal field available, returning count as placeholder
        return $totalWithdrawal;
    }

    public function getTicketStatusAttribute()
    {
        return Cache::remember('ticket_status', 60, function () {
            return DB::table('ticket_status')->get();
        });
    }

    public function getTicketTypesAttribute()
    {
        return Cache::remember('ticket_types', 60, function () {
            return DB::table('ticket_types')->get();
        });
    }
    public function sendEmailVerificationNotification()
    {
        $mailservice = new MailService();
        $settings = settings();
        $from = $settings['email_from_address'];
        $toEmail = $this->email;
        $uid = uniqid();
        $emailSubject = $settings['admin_title'] . ' - Email Address Verification';
        $htmlContent = "";
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content =
            '<p>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</p>' .
            '<p></p>' .
            '<p>You are receiving this email because you have registered for a LQH Markets Account.</p>' .
            '<p></p>'.
            '<p>Click the link below to activate your Account</p>';
        $code = $this->email_verify_token;
        $templateVars = [
            'name' => $this->fullname,
            'server_name' => $settings['mt5_company_name'],
            'site_link' => $settings['copyright_site_name_text'] . "/email_verify?id={$this->id}&code=$code",
            'email' => $settings['email_from_address'],
            "content" => $content,
            "title_right" => "Activate",
            "subtitle_right" => "Your Account",
            "btn_text" => "Activate",
        ];

        $mailservice->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);
    }

    public function getTotalBonusAttribute()
    {
        return $this->accounts->sum(function ($account) {
            return $account->BonusTransaction ? $account->BonusTransaction->sum(function ($bonus) {
                return (float) $bonus->bonus_amount;
            }) : 0;
        });
    }
}
