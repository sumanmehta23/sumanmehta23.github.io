<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletWithdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IbWithdrawalController extends Controller
{
    /**
     * List all IB withdrawals with overpaid amount tracking
     */
    public function index(Request $request)
    {
        $perPage = 50;
        $userId = $request->input('user_id');
        $hasOverpaid = $request->input('has_overpaid'); // null=all, 1=only overpaid, 0=clean only
        $referralCode = $request->input('referral_code');

        $query = DB::table('wallet_withdraw as ww')
            ->join('aspnetusers as u', 'u.id', '=', 'ww.user_id')
            ->select(
                'ww.id',
                'ww.user_id',
                'u.referral as referral_code',
                'ww.withdraw_amount',
                'ww.withdraw_date',
                'ww.withdraw_type',
                'ww.status',
                'ww.overpaid_amount',
                'ww.has_overpaid',
                'ww.created_at',
                DB::raw('CAST(ww.withdraw_amount AS DECIMAL(20,10)) as amount_decimal'),
                DB::raw('CAST(ww.overpaid_amount AS DECIMAL(20,10)) as overpaid_decimal')
            )
            ->orderBy('ww.withdraw_date', 'DESC');

        if ($userId) {
            $query->where('ww.user_id', $userId);
        }

        if ($hasOverpaid !== null) {
            $query->where('ww.has_overpaid', $hasOverpaid);
        }

        if ($referralCode) {
            $query->where('u.referral', $referralCode);
        }

        $withdrawals = $query->paginate($perPage)->withQueryString();

        // Calculate summary stats
        $stats = DB::table('wallet_withdraw as ww')
            ->join('aspnetusers as u', 'u.id', '=', 'ww.user_id')
            ->select(
                DB::raw('COUNT(*) as total_withdrawals'),
                DB::raw('SUM(CAST(ww.withdraw_amount AS DECIMAL(20,10))) as total_withdrawn'),
                DB::raw('SUM(CASE WHEN ww.has_overpaid = 1 THEN 1 ELSE 0 END) as withdrawals_with_overpaid'),
                DB::raw('SUM(CAST(ww.overpaid_amount AS DECIMAL(20,10))) as total_overpaid_withdrawn')
            )
            ->when($userId, fn($q) => $q->where('ww.user_id', $userId))
            ->when($referralCode, fn($q) => $q->where('u.referral', $referralCode))
            ->first();

        return view('admin.ib.withdrawals.index', [
            'withdrawals' => $withdrawals,
            'stats' => $stats,
            'filters' => [
                'user_id' => $userId,
                'has_overpaid' => $hasOverpaid,
                'referral_code' => $referralCode,
            ],
        ]);
    }

    /**
     * Get withdrawal details with overpaid breakdown
     */
    public function show($id)
    {
        $withdrawal = DB::table('wallet_withdraw as ww')
            ->join('aspnetusers as u', 'u.id', '=', 'ww.user_id')
            ->where('ww.id', $id)
            ->select(
                'ww.id',
                'ww.user_id',
                'u.referral as referral_code',
                'ww.withdraw_amount',
                'ww.overpaid_amount',
                'ww.has_overpaid',
                'ww.withdraw_date',
                'ww.withdraw_type',
                'ww.status',
                'ww.approved_by',
                'ww.approved_date',
                'ww.created_at',
                DB::raw('CAST(ww.withdraw_amount AS DECIMAL(20,10)) as amount_decimal'),
                DB::raw('CAST(ww.overpaid_amount AS DECIMAL(20,10)) as overpaid_decimal')
            )
            ->first();

        if (!$withdrawal) {
            abort(404, 'Withdrawal not found');
        }

        // Get the overpaid wallet entries included in this withdrawal
        $overpaidEntries = [];
        if ($withdrawal->has_overpaid) {
            $overpaidEntries = DB::table('ib_wallet as w')
                ->join('ib1_commission as c', 'c.id', '=', 'w.ib1_commission_id')
                ->where('w.user_id', $withdrawal->user_id)
                ->whereNotNull('w.deleted_at')
                ->where('w.overpayment_flag', 'flagged')
                ->select(
                    'w.id',
                    'w.order_id',
                    'c.expert_position_id',
                    DB::raw('CAST(w.ib_wallet AS DECIMAL(20,10)) as amount'),
                    'w.created_at',
                    'w.deleted_at'
                )
                ->get();
        }

        return view('admin.ib.withdrawals.show', [
            'withdrawal' => $withdrawal,
            'overpaidEntries' => $overpaidEntries,
        ]);
    }

    /**
     * API endpoint: Get overpaid details for a withdrawal
     */
    public function getOverpaidDetails($id)
    {
        $withdrawal = DB::table('wallet_withdraw')
            ->where('id', $id)
            ->select('id', 'user_id', 'overpaid_amount', 'has_overpaid', 'withdraw_date')
            ->first();

        if (!$withdrawal) {
            return response()->json(['error' => 'Withdrawal not found'], 404);
        }

        if (!$withdrawal->has_overpaid) {
            return response()->json([
                'withdrawal_id' => $withdrawal->id,
                'overpaid_amount' => 0,
                'entries' => [],
            ]);
        }

        $entries = DB::table('ib_wallet as w')
            ->join('ib1_commission as c', 'c.id', '=', 'w.ib1_commission_id')
            ->where('w.user_id', $withdrawal->user_id)
            ->whereNotNull('w.deleted_at')
            ->where('w.overpayment_flag', 'flagged')
            ->select(
                'w.id',
                'w.order_id',
                'c.expert_position_id',
                DB::raw('CAST(w.ib_wallet AS DECIMAL(20,10)) as amount'),
                'w.primary_wallet_id'
            )
            ->get();

        return response()->json([
            'withdrawal_id' => $withdrawal->id,
            'overpaid_amount' => round($withdrawal->overpaid_amount, 4),
            'entries_count' => count($entries),
            'entries' => $entries,
        ]);
    }
}
