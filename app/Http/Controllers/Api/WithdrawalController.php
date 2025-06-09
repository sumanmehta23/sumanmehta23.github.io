<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\WalletWithdraw;
use App\Http\Resources\WithdrawalResource;

class WithdrawalController extends Controller
{
    /**
     * Display a listing of withdrawals.
     * Supports filtering by withdrawal date range, user ID, type, and product ID.
     */
    public function index(Request $request)
    {
        $request->validate([
            'withdraw_date_from' => 'nullable|date',
            'withdraw_date_to' => 'nullable|date|after_or_equal:withdraw_date_from',
            'user_id' => 'nullable|string',
            'transaction_type' => 'nullable|string|max:50',
            'product_id' => 'nullable|string|max:50',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        $query = WalletWithdraw::query();

        $dateFrom = $request->input('withdraw_date_from');
        $dateTo = $request->input('withdraw_date_to');
        if (!empty($dateFrom) && !empty($dateTo)) {
            $fromDate = Carbon::parse($dateFrom)->startOfDay();
            $toDate = Carbon::parse($dateTo)->endOfDay();
            $query->whereBetween('withdraw_date', [$fromDate, $toDate]);
        } elseif (!empty($dateFrom)) {
            $fromDate = Carbon::parse($dateFrom)->startOfDay();
            $query->where('withdraw_date', '>=', $fromDate);
        } elseif (!empty($dateTo)) {
            $toDate = Carbon::parse($dateTo)->endOfDay();
            $query->where('withdraw_date', '<=', $toDate);
        }

        $userId = $request->input('user_id');
        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }

        $transactionType = $request->input('transaction_type');
        if (!empty($transactionType)) {
            $query->where('withdraw_type', $transactionType);
        }

        $productId = $request->input('product_id');
        if (!empty($productId)) {
            $query->where('admin_remark', $productId);
        }

        $perPage = min($request->input('per_page', 15), 100);
        $withdrawals = $query->paginate($perPage);

        try {
            return response()->json([
                'data' => WithdrawalResource::collection($withdrawals->items()),
                'meta' => [
                    'from' => $withdrawals->firstItem(),
                    'to' => $withdrawals->lastItem(),
                    'total' => $withdrawals->total(),
                    'current_page' => $withdrawals->currentPage(),
                    'last_page' => $withdrawals->lastPage(),
                    'per_page' => $withdrawals->perPage(),
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
