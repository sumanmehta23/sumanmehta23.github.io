<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\WalletDeposit;
use App\Http\Resources\TransactionResource;
use App\Models\TradeDeposit;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     * Returns transactions with fields required for integration.
     * Supports filtering by transaction date range and user ID.
     */
    public function index(Request $request)
    {
        // Validate request parameters
        $request->validate([
            'transaction_date_from' => 'nullable|date',
            'transaction_date_to' => 'nullable|date|after_or_equal:transaction_date_from',
            'user_id' => 'nullable|string',
            'transaction_type' => 'nullable|string|max:50',
            'product_id' => 'nullable|string|max:50',
            'per_page' => 'nullable|integer|min:1|max:500'
        ]);

        // Initialize query
        $query = TradeDeposit::query()->with('account:id,user_id,currency')
            ->whereHas('account', function ($q) {
                $q->where('demo', 0);
            })
            ->whereIn('deposit_type', ['CreditCardPayissa', 'CryptoChill'])
            ->where(function ($q) {
                $q->whereIn('deposit_type', ['CreditCardPayissa', 'CryptoChill'])
                ->orWhere('cell_tracking', 1);
            });

        // Filter by transaction date range
        $dateFrom = $request->input('transaction_date_from');
        $dateTo = $request->input('transaction_date_to');

        // Ensure filters are applied only when data is provided
        if (!empty($dateFrom) && !empty($dateTo)) {
            $fromDate = Carbon::parse($dateFrom)->startOfDay();
            $toDate = Carbon::parse($dateTo)->endOfDay();
            $query->whereBetween('deposted_date', [$fromDate, $toDate]);
        } elseif (!empty($dateFrom)) {
            $fromDate = Carbon::parse($dateFrom)->startOfDay();
            $query->where('deposted_date', '>=', $fromDate);
        } elseif (!empty($dateTo)) {
            $toDate = Carbon::parse($dateTo)->endOfDay();
            $query->where('deposted_date', '<=', $toDate);
        }

        $userId = $request->input('user_id');
        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }

        $transactionType = $request->input('transaction_type');
        if (!empty($transactionType)) {
            $query->where('deposit_type', $transactionType);
        }

        $productId = $request->input('product_id');
        if (!empty($productId)) {
            $query->where('admin_remark', $productId);
        }

        // Order by transaction date descending by default
        // Removed ordering by transaction_date as the column does not exist

        // Paginate the results
        $perPage = min($request->input('per_page', 15), 500);
        $transactions = $query->paginate($perPage);

        // Transform the paginated data using TransactionResource
        $transactions = new \Illuminate\Pagination\LengthAwarePaginator(
            TransactionResource::collection($transactions->items()),
            $transactions->total(),
            $transactions->perPage(),
            $transactions->currentPage(),
            [
                'path' => $transactions->path(),
                'query' => $transactions->appends(request()->query())->toArray()
            ]
        );

        try {
            // Update the response to separate data and metadata
            return response()->json([
                'data' => $transactions->items(),
                'meta' => [
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                    'total' => $transactions->total(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                ],
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            // Log the error for investigation
            Log::error('JSON encoding error in transactions: ' . $e->getMessage());

            return response()->json([
                'error' => 'Unable to process transactions data',
                'message' => 'Data encoding error occurred'
            ], 500);
        }
    }
}
