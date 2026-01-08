<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InternalTransfer extends Model
{
    use HasFactory;

    // This model no longer uses a database view
    // Instead, it provides static methods to query the union of trade_deposits and trade_withdrawal tables
    protected $table = null;
    public $timestamps = false;

    /**
     * Get internal transfers using a query builder approach (replaces the view)
     * 
     * @return \Illuminate\Database\Query\Builder
     */
    public static function query()
    {
        // Query for deposits (from trade_deposits table)
        $deposits = DB::table('trade_deposits')
            ->select([
                'email',
                'id as raw_id',
                DB::raw("'TDID' as source"),
                'account_id as it_to',
                'deposit_from as it_from',
                'deposit_amount as amount',
                'deposted_date as date',
                'status',
                'deposit_type as type'
            ])
            ->whereIn('deposit_type', ['Internal Transfer', 'Wallet Transfer', 'CRM', 'IB Withdraw']);

        // Query for withdrawals (from trade_withdrawal table)
        $withdrawals = DB::table('trade_withdrawal')
            ->select([
                'email',
                'id as raw_id',
                DB::raw("'TWID' as source"),
                'withdraw_to as it_to',
                'account_id as it_from',
                'withdrawal_amount as amount',
                'withdraw_date as date',
                'status',
                'withdraw_type as type'
            ])
            ->whereIn('withdraw_type', ['Internal Transfer', 'Wallet Withdrawal']);

        // Union the two queries
        return $deposits->union($withdrawals);
    }

    /**
     * Get internal transfers with relationships loaded
     * 
     * @param string|null $email
     * @param array $types
     * @param int|null $status
     * @return \Illuminate\Support\Collection
     */
    public static function getTransfers($email = null, $types = null, $status = null)
    {
        $query = static::query();

        if ($email) {
            $query->where('email', $email);
        }

        if ($types) {
            $query->whereIn('type', $types);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        $transfers = $query->orderBy('raw_id', 'desc')->get();

        // Load relationships manually since we're using query builder
        $transfers->transform(function ($transfer) {
            $transfer->accountTo = $transfer->it_to ? Account::find($transfer->it_to) : null;
            $transfer->accountFrom = $transfer->it_from ? Account::find($transfer->it_from) : null;
            return $transfer;
        });

        return $transfers;
    }

    public function accountTo()
    {
        return $this->belongsTo(Account::class, 'it_to');
    }

    public function accountFrom()
    {
        return $this->belongsTo(Account::class, 'it_from');
    }
}
