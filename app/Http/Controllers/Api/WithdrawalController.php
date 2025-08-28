<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\WalletWithdraw;
use App\Models\TradeWithdrawals;
use App\Http\Resources\UniversalWithdrawalResource;

class WithdrawalController extends Controller
{
    /**
     * Display a listing of withdrawals.
     * Supports filtering by withdrawal date range, user ID, type, and product ID.
     * Combines both wallet withdrawals and trade withdrawals.
     */
    public function index(Request $request)
    {
        $request->validate([
            'withdraw_date_from' => 'nullable|date',
            'withdraw_date_to' => 'nullable|date|after_or_equal:withdraw_date_from',
            'user_id' => 'nullable|string',
            'transaction_type' => 'nullable|string|max:50',
            'product_id' => 'nullable|string|max:50',
            'per_page' => 'nullable|integer|min:1|max:500'
        ]);

        // Get filter parameters
        $dateFrom = $request->input('withdraw_date_from');
        $dateTo = $request->input('withdraw_date_to');
        $userId = $request->input('user_id');
        $transactionType = $request->input('transaction_type');
        $productId = $request->input('product_id');

        // Build wallet withdrawals query
        $walletQuery = WalletWithdraw::query();

        // Apply date filters
        if (!empty($dateFrom) && !empty($dateTo)) {
            $fromDate = Carbon::parse($dateFrom)->startOfDay();
            $toDate = Carbon::parse($dateTo)->endOfDay();
            $walletQuery->whereBetween('withdraw_date', [$fromDate, $toDate]);
        } elseif (!empty($dateFrom)) {
            $fromDate = Carbon::parse($dateFrom)->startOfDay();
            $walletQuery->where('withdraw_date', '>=', $fromDate);
        } elseif (!empty($dateTo)) {
            $toDate = Carbon::parse($dateTo)->endOfDay();
            $walletQuery->where('withdraw_date', '<=', $toDate);
        }

        // Apply other filters for wallet withdrawals
        if (!empty($userId)) {
            $walletQuery->where('user_id', $userId);
        }
        if (!empty($transactionType)) {
            $walletQuery->where('withdraw_type', $transactionType);
        }
        if (!empty($productId)) {
            $walletQuery->where('admin_remark', $productId);
        }

        // Build trade withdrawals query
        $tradeQuery = TradeWithdrawals::query();

        // Apply date filters
        if (!empty($dateFrom) && !empty($dateTo)) {
            $fromDate = Carbon::parse($dateFrom)->startOfDay();
            $toDate = Carbon::parse($dateTo)->endOfDay();
            $tradeQuery->whereBetween('withdraw_date', [$fromDate, $toDate]);
        } elseif (!empty($dateFrom)) {
            $fromDate = Carbon::parse($dateFrom)->startOfDay();
            $tradeQuery->where('withdraw_date', '>=', $fromDate);
        } elseif (!empty($dateTo)) {
            $toDate = Carbon::parse($dateTo)->endOfDay();
            $tradeQuery->where('withdraw_date', '<=', $toDate);
        }

        // Apply other filters for trade withdrawals
        if (!empty($userId)) {
            $tradeQuery->where('user_id', $userId);
        }
        if (!empty($transactionType)) {
            $tradeQuery->where('withdraw_type', $transactionType);
        }
        if (!empty($productId)) {
            $tradeQuery->where('admin_remark', $productId);
        }

        // Get both collections
        $walletWithdrawals = $walletQuery->get();
        $tradeWithdrawals = $tradeQuery->get();

        // Combine both collections
        $allWithdrawals = $walletWithdrawals->concat($tradeWithdrawals);

        // Sort by withdraw_date in descending order
        $allWithdrawals = $allWithdrawals->sortByDesc('withdraw_date');

        // Handle pagination manually
        $perPage = min($request->input('per_page', 15), 500);
        $currentPage = $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;

        $total = $allWithdrawals->count();
        $paginatedWithdrawals = $allWithdrawals->slice($offset, $perPage)->values();

        try {
            return response()->json([
                'data' => UniversalWithdrawalResource::collection($paginatedWithdrawals),
                'meta' => [
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total),
                    'total' => $total,
                    'current_page' => $currentPage,
                    'last_page' => ceil($total / $perPage),
                    'per_page' => $perPage,
                    'wallet_withdrawals_count' => $walletWithdrawals->count(),
                    'trade_withdrawals_count' => $tradeWithdrawals->count(),
                ],
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('JSON encoding error in withdrawals: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to process withdrawals data',
                'message' => 'Data encoding error occurred'
            ], 500);
        }
    }
}
