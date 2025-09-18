<?php

namespace App\Http\Controllers\Api;

use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\TradeResource;

/**
 * Trades API Controller for Cellexpert Integration
 *
 * Provides endpoints for retrieving trade/position data with the following fields:
 *
 * REQUIRED FIELDS:
 * - position_volume: The monetary position amount (Volume) - e.g., $100,000 USD
 * - position_close_date: The position finalization date (after outcome is concluded)
 *
 * OPTIONAL FIELDS:
 * - symbol: The symbol for the traded asset (enables per-symbol payouts)
 * - position_lot_volume: The LOT Volume (enables per-LOT commission models)
 * - position_spread: The position monetary spread
 * - position_open_date: The position open time
 * - position_base_currency: Transaction currency in 3-letter ISO format (USD, EUR)
 * - position_pl: Profit/Loss derived from position (enables revenue share)
 * - position_trading_group: Associated trading group (common in MT4/MT5)
 * - position_status: Outcome for user (Won, Lost, Cancelled, etc.)
 * - position_type: Description of the position
 *
 * AVAILABLE FILTERS:
 * - position_close_date_from/to: Filter by close date range (mandatory support)
 * - user_id: Query individual users regardless of timestamps
 * - symbol: Filter by trading symbol
 * - position_status: Filter by trade outcome
 * - position_type: Filter by position type
 * - position_trading_group: Filter by trading group
 */
class TradeController extends Controller
{
    /**
     * Display a listing of trades.
     * Returns trades with fields required for Cellexpert integration
     * Supports filtering by position close date range and user ID
     */
    public function index(Request $request)
    {
        // Validate date formats if provided
        $request->validate([
            'position_close_date_from' => 'nullable|date',
            'position_close_date_to' => 'nullable|date|after_or_equal:position_close_date_from',
            'user_id' => 'nullable|string',
            'symbol' => 'nullable|string|max:20',
            'position_status' => 'nullable|string|max:50',
            'position_type' => 'nullable|string|max:50',
            'position_trading_group' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:500'
        ]);

        // Initialize query with account relationship for user_id and currency, and filter for live accounts only
        $query = Trade::query()->with('account:id,user_id,currency')
            ->whereHas('account', function ($q) {
                $q->where('demo', 0);
            });

        // Apply filters only when there are actual values
        // Filter by position close date range (mandatory filter support)
        $closeDateFrom = $request->input('position_close_date_from');
        $closeDateTo = $request->input('position_close_date_to');

        if (!empty($closeDateFrom) && !empty($closeDateTo)) {
            // Parse from date - use specified time or start of day
            $fromDate = Carbon::parse($closeDateFrom);
            if (!preg_match('/\d{2}:\d{2}/', $closeDateFrom)) {
                $fromDate->startOfDay();
            }

            // Parse to date - use specified time or end of day
            $toDate = Carbon::parse($closeDateTo);
            if (!preg_match('/\d{2}:\d{2}/', $closeDateTo)) {
                $toDate->endOfDay();
            }

            $query->whereBetween('close_time', [$fromDate, $toDate]);
        } elseif (!empty($closeDateFrom)) {
            $fromDate = Carbon::parse($closeDateFrom);
            if (!preg_match('/\d{2}:\d{2}/', $closeDateFrom)) {
                $fromDate->startOfDay();
            }
            $query->where('close_time', '>=', $fromDate);
        } elseif (!empty($closeDateTo)) {
            $toDate = Carbon::parse($closeDateTo);
            if (!preg_match('/\d{2}:\d{2}/', $closeDateTo)) {
                $toDate->endOfDay();
            }
            $query->where('close_time', '<=', $toDate);
        }

        // Filter by user ID (optional) - allows querying individual users
        $userId = $request->input('user_id');
        if (!empty($userId)) {
            $query->whereHas('account', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        // Filter by symbol (optional) - for individual per symbol payouts
        $symbol = $request->input('symbol');
        if (!empty($symbol)) {
            $query->where('symbol', 'like', '%' . $symbol . '%');
        }

        // Filter by position status (optional) - Won, Lost, Cancelled etc
        $status = $request->input('position_status');
        if (!empty($status)) {
            $query->where('status', $status);
        }

        // Filter by position type (optional)
        $type = $request->input('position_type');
        if (!empty($type)) {
            $query->where('type', $type);
        }

        // Filter by position trading group (optional)
        $tradingGroup = $request->input('position_trading_group');
        if (!empty($tradingGroup)) {
            $query->where('trading_group', $tradingGroup);
        }

        // Order by close time descending by default
        $query->orderBy('close_time', 'desc');

        // Paginate the results
        $perPage = min($request->input('per_page', 15), 500); // Limit max per page to 100
        $trades = $query->paginate($perPage);

        try {
            return response()->json([
                'data' => TradeResource::collection($trades->items()),
                'meta' => [
                    'from' => $trades->firstItem(),
                    'to' => $trades->lastItem(),
                    'total' => $trades->total(),
                    'current_page' => $trades->currentPage(),
                    'last_page' => $trades->lastPage(),
                    'per_page' => $trades->perPage(),
                ],
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            // Log the error for investigation
            Log::error('JSON encoding error in trades: ' . $e->getMessage());

            return response()->json([
                'error' => 'Unable to process trades data',
                'message' => 'Data encoding error occurred'
            ], 500);
        }
    }

    /**
     * Display the specified trade.
     */
    public function show(Request $request, $id)
    {
        try {
            $trade = Trade::with('account:id,user_id,currency')->findOrFail($id);

            return (new TradeResource($trade))
                ->response()
                ->setEncodingOptions(JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            // Log the error for investigation
            Log::error('JSON encoding error for trade ' . $id . ': ' . $e->getMessage());

            return response()->json([
                'error' => 'Unable to process trade data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Implementation for creating trades if needed
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trade $trade)
    {
        // Implementation for updating trades if needed
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trade $trade)
    {
        // Implementation for deleting trades if needed
    }
}
