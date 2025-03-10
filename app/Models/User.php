<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Country;
use Illuminate\Support\Str;
use App\Services\MailService;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use  HasFactory, Notifiable, HasUuids, TwoFactorAuthenticatable;

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
        'klaviyo_last_error' => 'json',
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
    // public function getWalletBalance($userId)
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

            return (float) $totalDeposit - ((float) $totalWithdraw + (float) $totalWithdrawFee);
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

    public function getPendingWwAttribute()
    {
        return WalletWithdraw::where('user_id', $this->id)
            ->where('status', 0)
            ->selectRaw('SUM(withdraw_amount + COALESCE(withdraw_transaction_fee, 0)) as total')
            ->value('total');
    }

    public function getTotalBalanceAttribute()
    {
        return TotalBalance::where('user_id', $this->id)
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

    public function getClientsAttribute()
    {
        if (!$this->ib) {
            return collect(); // Return an empty collection if the user has no IB.
        }

        $referralCode = $this->ib->referral_code ? $this->ib->referral_code : $this->ib->email;

        // Dynamically build the query for all 15 levels using a single query.
        $clients = IbClientList::where(function ($query) use ($referralCode) {
            for ($i = 1; $i <= 15; $i++) {
                $query->orWhere("ib$i", $referralCode);
            }
        })->get();

        // Group clients by level (ib1, ib2, ..., ib15)
        $groupedClients = $clients->mapToGroups(function ($client) use ($referralCode) {
            foreach (range(1, 15) as $level) {
                if ($client["ib$level"] === $referralCode) {
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
            return 0; // Return 0 if the user has no IB.
        }

        $referralCode = $this->ib->referral_code ? $this->ib->referral_code : $this->ib->email;

        // Dynamically build the query for all 15 levels using a single query.
        $clients = IbClientList::where(function ($query) use ($referralCode) {
            for ($i = 1; $i <= 15; $i++) {
                $query->orWhere("ib$i", $referralCode);
            }
        })->get();

        // Calculate the total sum of 'total_deposit'
        $totalDeposit = $clients->sum('total_deposit');
        return $totalDeposit;
    }

    public function getIbTotalWithdrawalAttribute()
    {
        if (!$this->ib) {
            return 0; // Return 0 if the user has no IB.
        }

        $referralCode = $this->ib->referral_code ? $this->ib->referral_code : $this->ib->email;

        // Dynamically build the query for all 15 levels using a single query.
        $clients = IbClientList::where(function ($query) use ($referralCode) {
            for ($i = 1; $i <= 15; $i++) {
                $query->orWhere("ib$i", $referralCode);
            }
        })->get();

        // Calculate the total sum of 'total_deposit'
        $totalWithdrawal = $clients->sum('total_withdrawal');
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
        $emailSubject = $settings['admin_title'] . ' - Email Address Verfication';
        $htmlContent = "";
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content =
            '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
            '<div>You are receiving this email because you have registered for a Trading Account.</div>' .
            '<div>Click the link below to activate your Trading Account</div>';
        $code = $this->emailToken;
        $templateVars = [
            'name' => $this->fullname,
            'server_name' => $settings['mt5_company_name'],
            'site_link' => $settings['copyright_site_name_text'] . "/email_verify?id={$this->id}&code=$code",
            'email' => $settings['email_from_address'],
            "content" => $content,
            "title_right" => "Activate",
            "subtitle_right" => "Your Account"
        ];

        $mailservice->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);
    }

    public function getTotalBonusAttribute()
    {
        return $this->accounts->sum(function ($account) {
            return $account->BonusTransaction->sum('bonus_amount');
        });
    }
}
