<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Country;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

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
    ];

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

    public function getCountry()
    {
        return Country::where('country_name', '=', $this->country) 
            ->first();
    }

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
    public function countries()
    {
        return $this->hasMany(Country::class);
    }
    public function getWalletBalanceAttribute()
    {
        return Cache::remember("user:{$this->id}:wallet_balance", now()->addMinutes(10), function () {
            $totalDeposit = WalletDeposit::where('user_id', $this->id)
                ->where('status', 1)
                ->sum('deposit_amount');

            $totalWithdraw = WalletWithdraw::where('user_id', $this->id)
                ->where('status', '<>', 2)
                ->sum('withdraw_amount');
            $totalWithdrawFee = WalletWithdraw::where('user_id', $this->id)
                ->where('status', '<>', 2)
                ->sum('withdraw_transaction_fee');

            return (float) $totalDeposit - ((float) $totalWithdraw + (float) $totalWithdrawFee);
        });
    }

    public function getTotalWdAttribute()
    {
        return WalletDeposit::where('user_id', $this->id)
            ->whereIn('deposit_type', ['Internal Transfer', 'Crypto Chill'])
            ->where('status', 1)
            ->sum('deposit_amount');
    }

    // Accessor for Wallet Withdrawals (total withdraw amount)
    public function getTotalWwAttribute()
    {
        return WalletWithdraw::where('user_id', $this->id)
            ->where('withdraw_type', 'Internal Transfer')
            ->where('status', 1)
            ->selectRaw('SUM(withdraw_amount + COALESCE(withdraw_transaction_fee, 0)) as total')
            ->value('total');
    }

    // Accessor for Pending Wallet Withdrawals
    public function getPendingWwAttribute()
    {
        return WalletWithdraw::where('user_id', $this->id)
            ->where('status', 0)
            ->selectRaw('SUM(withdraw_amount + COALESCE(withdraw_transaction_fee, 0)) as total')
            ->value('total');
    }


    // Accessor for Total Balance (sum of deposits, trading deposits, etc.)
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

    // Accessor for Bank Details
    public function getBankDetailsAttribute()
    {
        return DB::table('clientbankdetails')->where('userId', $this->id)->first();
    }

    // Accessor for KYC Details
    public function getKycDetailsAttribute()
    {
        return DB::table('kyc_update')->where('email', $this->email)->get();
    }

    // Accessor for IB Details
    public function getIbDetailsAttribute()
    {
        return DB::table('ib1')
            ->leftJoin('ib_wallet', 'ib1.user_id', '=', 'ib_wallet.user_id')
            ->leftJoin('account_types as ac', 'ac.ac_index', '=', 'ib1.acc_type')
            ->select('ib1.*', DB::raw('SUM(ib_wallet.ib_wallet) as deposit'), DB::raw('SUM(ib_wallet.ib_withdraw) as withdraw'), 'ac.ac_name')
            ->where('ib1.status', 1)
            ->where('ib1.email', $this->email)
            ->groupBy('ib1.email')
            ->havingRaw('COUNT(ib1.email) > 0')
            ->first();
    }

    // Accessor for Relationship Manager (RM) Details
    public function getRmDetailsAttribute()
    {
        return DB::table('relationship_manager as rm')
            ->leftJoin('emplist as emp', 'rm.rm_id', '=', 'emp.email')
            ->select('emp.client_index', 'emp.username', 'rm.*')
            ->where('rm.user_id', $this->id)
            ->first();
    }

    // Accessor for Super Admin Details
    public function getSuperadminDetailsAttribute()
    {
        return DB::table('emplist')->where('role_id', 1)->first();
    }

    // Accessor for Country Code
    public function getCountryCodeAttribute()
    {
        return DB::table('countries')->where('country_name', $this->country)->first();
    }

    // Accessor for Clients grouped by referral code
    public function getClientsAttribute()
    {
        $clients = $this->ib ? IbClientList::whereIn('ib1', [$this->ib->referral_code])->get() : collect();
        return $clients->groupBy('ib1');
    }

    // Accessor for Ticket Status and Ticket Types (cached)
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
}
