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
     * Supports sorting by created_at or deposit_date (deposted_date column).
     * 
     * @param  Request  $request
     * @param  string   $request->sort_by           Optional. Sort field: 'created_at' or 'deposit_date'. Default: 'deposit_date'
     * @param  string   $request->sort_direction    Optional. Sort direction: 'asc' or 'desc'. Default: 'desc'
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
            'per_page' => 'nullable|integer|min:1|max:500',
            'sort_by' => 'nullable|string|in:created_at,deposit_date',
            'sort_direction' => 'nullable|string|in:asc,desc'
        ]);

        // Initialize query
        $query = TradeDeposit::query()
            ->with('account:id,user_id,currency')
            ->whereHas('account', function ($q) {
                $q->where('demo', 0);
            })
            ->whereHas('user', function ($q) {
                $q->whereNotNull('cxd');
            })
            ->where(function ($q) {
                // Case 1: CreditCardPayissa or CryptoChill → include all
                $q->whereIn('deposit_type', ['CreditCardPayissa', 'CryptoChill']);

                // Case 2: CRM → only if cell_tracking = 1
                $q->orWhere(function ($q2) {
                    $q2->where('deposit_type', 'CRM')
                        ->where('cell_tracking', 1);
                });
            });

        // Filter by transaction date range
        $dateFrom = $request->input('transaction_date_from');
        $dateTo = $request->input('transaction_date_to');

        // Ensure filters are applied only when data is provided
        if (!empty($dateFrom) && !empty($dateTo)) {
            $fromDate = Carbon::parse($dateFrom);
            $toDate = Carbon::parse($dateTo);
            // Only apply startOfDay/endOfDay if time portion is not provided
            if ($fromDate->format('H:i:s') === '00:00:00') {
                $fromDate = $fromDate->startOfDay();
            }
            if ($toDate->format('H:i:s') === '00:00:00') {
                $toDate = $toDate->endOfDay();
            }
            $query->whereBetween('deposted_date', [$fromDate, $toDate]);
        } elseif (!empty($dateFrom)) {
            $fromDate = Carbon::parse($dateFrom);
            // Only apply startOfDay/endOfDay if time portion is not provided
            if ($fromDate->format('H:i:s') === '00:00:00') {
                $fromDate = $fromDate->startOfDay();
            }

            $query->where('deposted_date', '>=', $fromDate);
        } elseif (!empty($dateTo)) {
            $toDate = Carbon::parse($dateTo);

            if ($toDate->format('H:i:s') === '00:00:00') {
                $toDate = $toDate->endOfDay();
            }
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

        // Handle sorting
        $sortBy = $request->input('sort_by', 'deposit_date'); // Default to deposit_date
        $sortDirection = $request->input('sort_direction', 'desc'); // Default to descending

        // Map sort_by values to actual column names
        $sortColumn = $sortBy === 'deposit_date' ? 'deposted_date' : 'created_at';

        // Apply sorting
        $query->orderBy($sortColumn, $sortDirection);

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
