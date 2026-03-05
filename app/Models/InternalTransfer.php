<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

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
        // Build deposits query
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
            ]);

        // Apply filters to deposits query
        if ($email) {
            $deposits->where('email', $email);
        }
        if ($status !== null) {
            $deposits->where('status', $status);
        }

        // Filter by deposit types
        $depositTypes = $types ? array_intersect($types, ['Internal Transfer', 'Wallet Transfer', 'CRM', 'IB Withdraw']) : ['Internal Transfer', 'Wallet Transfer', 'CRM', 'IB Withdraw'];
        $deposits->whereIn('deposit_type', $depositTypes);

        // Build withdrawals query
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
            ]);

        // Apply filters to withdrawals query
        if ($email) {
            $withdrawals->where('email', $email);
        }
        if ($status !== null) {
            $withdrawals->where('status', $status);
        }

        // Filter by withdrawal types
        $withdrawalTypes = $types ? array_intersect($types, ['Internal Transfer', 'Wallet Withdrawal']) : ['Internal Transfer', 'Wallet Withdrawal'];
        // $withdrawals->whereIn('withdraw_type', $withdrawalTypes);
        $withdrawals->whereIn('withdraw_type', $withdrawalTypes)->where('withdraw_type', '!=', 'Internal Transfer');

        // Union the queries and get results as arrays for backward compatibility with views
        $transfers = $deposits->union($withdrawals)->orderBy('raw_id', 'desc')->get();

        // Load relationships and convert to Fluent objects (supports both array and object access)
        // Include soft-deleted accounts using withTrashed()
        $transfers = $transfers->map(function ($transfer) {
            // Convert stdClass to array, then to Fluent for both array and object access
            $data = (array) $transfer;

            // Load relationships
            $accountTo = $data['it_to'] ? Account::withTrashed()->find($data['it_to']) : null;
            $accountFrom = $data['it_from'] ? Account::withTrashed()->find($data['it_from']) : null;

            // Add relationships to data
            $data['accountTo'] = $accountTo;
            $data['accountFrom'] = $accountFrom;

            // Return as Fluent object (supports both $obj->prop and $obj['prop'])
            return new Fluent($data);
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
