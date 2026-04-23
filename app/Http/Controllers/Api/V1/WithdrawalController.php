<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\WithdrawalResource;
use App\Models\TradeWithdrawals;
use App\Models\WalletWithdraw;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    /**
     * Display a listing of withdrawals.
     * Supports filtering by withdrawal date range, user ID, type, and product ID.
     * Returns both wallet withdrawals and trade withdrawals.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Authorization check for API token permissions
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:withdrawals:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $request->validate([
            'withdraw_date_from' => 'nullable|date',
            'withdraw_date_to' => 'nullable|date|after_or_equal:withdraw_date_from',
            'user_id' => 'nullable|string',
            'email' => 'nullable|string|email',
            'transaction_type' => 'nullable|string|max:50',
            'product_id' => 'nullable|string|max:50',
            'per_page' => 'nullable|integer|min:1|max:500',
            'sort_by' => 'nullable|string|in:id,withdraw_date,amount,user_id,status,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'include_archived' => 'nullable|boolean',
        ]);

        // Get date filters
        $dateFrom = $request->input('withdraw_date_from');
        $dateTo = $request->input('withdraw_date_to');
        $userId = $request->input('user_id');
        $email = $request->input('email');
        $transactionType = $request->input('transaction_type');
        $productId = $request->input('product_id');
        $includeArchived = filter_var($request->input('include_archived', false), FILTER_VALIDATE_BOOLEAN);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 15);

        // Build wallet withdraw query
        $walletQuery = WalletWithdraw::query()
            ->select('id', 'user_id', 'email', 'withdraw_date', 'withdraw_amount', 'withdraw_transaction_fee', 'transaction_id', 'status', 'withdraw_type', 'approved_date', 'created_at', 'updated_at')
            ->selectRaw("'wallet' as source");

        // Handle archived records
        if (! $includeArchived) {
            $walletQuery->whereNull('deleted_at');
        } else {
            $walletQuery->withTrashed();
        }

        // Build trade withdraw query
        $tradeQuery = TradeWithdrawals::query()
            ->selectRaw("id, user_id, email, withdraw_date, withdrawal_amount as withdraw_amount,transaction_fee as withdraw_transaction_fee,transaction_id, status, withdraw_type, approved_date,created_at, updated_at, 'trade' as source");

        // Handle archived records
        if (! $includeArchived) {
            $tradeQuery->whereNull('deleted_at');
        } else {
            $tradeQuery->withTrashed();
        }

        // Apply filters to wallet query
        if ($dateFrom) {
            $walletQuery->whereDate('withdraw_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $walletQuery->whereDate('withdraw_date', '<=', $dateTo);
        }
        if ($userId) {
            $walletQuery->where('user_id', $userId);
        }
        if ($email) {
            $walletQuery->where('email', $email);
        }
        if ($transactionType) {
            $walletQuery->where('withdraw_type', $transactionType);
        }

        // Apply filters to trade query
        if ($dateFrom) {
            $tradeQuery->whereDate('withdraw_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $tradeQuery->whereDate('withdraw_date', '<=', $dateTo);
        }
        if ($userId) {
            $tradeQuery->where('user_id', $userId);
        }
        if ($email) {
            $tradeQuery->where('email', $email);
        }
        if ($transactionType) {
            $tradeQuery->where('withdraw_type', $transactionType);
        }

        // Union both queries
        $baseQuery = $walletQuery->union($tradeQuery);

        // Apply sorting and pagination
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'asc' : 'desc';
        $withdrawals = \DB::table(\DB::raw("({$baseQuery->toSql()}) as combined"))
            ->mergeBindings($baseQuery->getQuery())
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        return WithdrawalResource::collection($withdrawals);
    }

    /**
     * Display a single withdrawal.
     *
     * @param  string  $id  Withdrawal ID
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Authorization check
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:withdrawals:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $withdrawal = WalletWithdraw::find($id);

        if (! $withdrawal) {
            return response()->json(['error' => 'Withdrawal not found'], 404);
        }

        return new WithdrawalResource($withdrawal);
    }

    /**
     * Get withdrawal statistics.
     * Returns total withdrawals, total amount, average amount, etc.
     *
     * @return \Illuminate\Http\Response
     */
    public function statistics(Request $request)
    {
        // Authorization check
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:withdrawals:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'user_id' => 'nullable|string',
            'email' => 'nullable|string|email',
            'include_archived' => 'nullable|boolean',
        ]);

        $includeArchived = filter_var($request->input('include_archived', false), FILTER_VALIDATE_BOOLEAN);

        // Build wallet query with explicit column selection
        $walletQuery = WalletWithdraw::query()
            ->select('id', 'user_id', 'email', 'withdraw_date', 'withdraw_amount', 'status', 'withdraw_type', 'created_at', 'updated_at')
            ->selectRaw("'wallet' as source");

        // Handle archived records
        if (! $includeArchived) {
            $walletQuery->whereNull('deleted_at');
        } else {
            $walletQuery->withTrashed();
        }

        // Build trade query with explicit column selection
        $tradeQuery = TradeWithdrawals::query()
            ->selectRaw("id, user_id, email, withdraw_date, withdrawal_amount as withdraw_amount, status, withdraw_type, created_at, updated_at, 'trade' as source");

        // Handle archived records
        if (! $includeArchived) {
            $tradeQuery->whereNull('deleted_at');
        } else {
            $tradeQuery->withTrashed();
        }

        if ($request->has('date_from')) {
            $walletQuery->whereDate('withdraw_date', '>=', $request->input('date_from'));
            $tradeQuery->whereDate('withdraw_date', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to')) {
            $walletQuery->whereDate('withdraw_date', '<=', $request->input('date_to'));
            $tradeQuery->whereDate('withdraw_date', '<=', $request->input('date_to'));
        }
        if ($request->has('user_id')) {
            $walletQuery->where('user_id', $request->input('user_id'));
            $tradeQuery->where('user_id', $request->input('user_id'));
        }
        if ($request->has('email')) {
            $walletQuery->where('email', $request->input('email'));
            $tradeQuery->where('email', $request->input('email'));
        }

        // Union both queries for combined statistics
        $unionQuery = $walletQuery->union($tradeQuery);

        $stats = \DB::table(\DB::raw("({$unionQuery->toSql()}) as combined"))
            ->mergeBindings($unionQuery->getQuery())
            ->selectRaw('COUNT(*) as total_withdrawals, SUM(withdraw_amount) as total_amount, AVG(withdraw_amount) as average_amount, MIN(withdraw_amount) as min_amount, MAX(withdraw_amount) as max_amount')
            ->first();

        return response()->json([
            'total_withdrawals' => (int) ($stats->total_withdrawals ?? 0),
            'total_amount' => (float) ($stats->total_amount ?? 0),
            'average_amount' => (float) ($stats->average_amount ?? 0),
            'min_amount' => $stats->min_amount ? (float) $stats->min_amount : null,
            'max_amount' => $stats->max_amount ? (float) $stats->max_amount : null,
        ]);
    }

    /**
     * Check if user has specific permission
     *
     * @param  object  $user
     * @param  string  $permission
     * @return bool
     */
    private function checkPermission($user, $permission)
    {
        if ($user->isSuperAdmin() ?? false) {
            return true;
        }

        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }

        return false;
    }
}
