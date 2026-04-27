<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Models\Trade;
use App\MT5\MTEnDealAction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\TradeResource;
use App\Http\Resources\CorrectionResource;

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
            'last_modified_date_from' => 'nullable|date',
            'last_modified_date_to' => 'nullable|date|after_or_equal:last_modified_date_from',
            'user_id' => 'nullable|string',
            'symbol' => 'nullable|string|max:20',
            'position_status' => 'nullable|string|max:50',
            'position_type' => 'nullable|string|max:50',
            'position_trading_group' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:500'
        ]);

        // Apply filters only when there are actual values
        // Filter by position close date range (mandatory filter support)
        $closeDateFrom = $request->input('position_close_date_from');
        $closeDateTo = $request->input('position_close_date_to');

        // Parse date ranges first to use in both Trade and Correction filters
        $fromDate = null;
        $toDate = null;

        if (!empty($closeDateFrom)) {
            $fromDate = Carbon::parse($closeDateFrom);
            if (!preg_match('/\d{2}:\d{2}/', $closeDateFrom)) {
                $fromDate->startOfDay();
            }
        }

        if (!empty($closeDateTo)) {
            $toDate = Carbon::parse($closeDateTo);
            if (!preg_match('/\d{2}:\d{2}/', $closeDateTo)) {
                $toDate->endOfDay();
            }
        }

        // Initialize query with account relationship for user_id and currency, and filter for live accounts only
        $query = Trade::query()->with('account:id,user_id,currency,account_type_id')
            ->whereHas('account', function ($q) {
                $q->where('demo', 0);
            })
            ->whereHas('user', function ($q) {
                $q->whereNotNull('cxd');
            })
            ->where(function ($q) {
                $q->where('profit', '!=', 0)
                    ->orWhere(function ($q2) {
                        $q2->where('profit', 0)
                            ->where('created_at', '<=', now()->subHours(2));
                    });
            }); // Exclude trades with zero profit

        // Apply date filters to trades
        if ($fromDate && $toDate) {
            $query->whereBetween('close_time', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->where('close_time', '>=', $fromDate);
        } elseif ($toDate) {
            $query->where('close_time', '<=', $toDate);
        }
        $modifiedDateFrom = $request->input('last_modified_date_from');
        $modifiedDateTo = $request->input('last_modified_date_to');
        if (!empty($modifiedDateFrom) && !empty($modifiedDateTo)) {
            // Parse from date - use specified time or start of day
            $fromDate = Carbon::parse($modifiedDateFrom);
            if (!preg_match('/\d{2}:\d{2}/', $modifiedDateFrom)) {
                $fromDate->startOfDay();
            }

            // Parse to date - use specified time or end of day
            $toDate = Carbon::parse($modifiedDateTo);
            if (!preg_match('/\d{2}:\d{2}/', $modifiedDateTo)) {
                $toDate->endOfDay();
            }

            $query->whereBetween('updated_at', [$fromDate, $toDate]);
        } elseif (!empty($modifiedDateFrom)) {
            $fromDate = Carbon::parse($modifiedDateFrom);
            if (!preg_match('/\d{2}:\d{2}/', $modifiedDateFrom)) {
                $fromDate->startOfDay();
            }
            $query->where('updated_at', '>=', $fromDate);
        } elseif (!empty($modifiedDateTo)) {
            $toDate = Carbon::parse($modifiedDateTo);
            if (!preg_match('/\d{2}:\d{2}/', $modifiedDateTo)) {
                $toDate->endOfDay();
            }
            $query->where('updated_at', '<=', $toDate);
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

        // Get all trades (we'll paginate after merging with corrections)
        $trades = $query->get();
        // Query correction deals independently (action = 5)
        $correctionsQuery = Deal::query()
            ->with('account:id,user_id,currency,account_type_id')
            ->where('action', MTEnDealAction::DEAL_CORRECTION)
            ->whereHas('account', function ($q) {
                $q->where('demo', 0);
            })
            ->whereHas('account.user', function ($q) {
                $q->whereNotNull('cxd');
            });

        // Apply the same date filters to corrections
        if ($fromDate && $toDate) {
            $correctionsQuery->whereBetween('time_done', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $correctionsQuery->where('time_done', '>=', $fromDate);
        } elseif ($toDate) {
            $correctionsQuery->where('time_done', '<=', $toDate);
        }

        // Apply user_id filter to corrections if present
        if (!empty($userId)) {
            $correctionsQuery->whereHas('account', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        $corrections = $correctionsQuery->orderBy('time_done', 'desc')->get();

        try {
            // Merge trades and corrections into a single collection
            $combined = collect();

            // Add all trades with their close time for sorting
            foreach ($trades as $trade) {
                $combined->push([
                    'type' => 'trade',
                    'data' => $trade,
                    'sort_time' => $trade->close_time,
                ]);
            }

            // Add all corrections with their time_done for sorting
            foreach ($corrections as $correction) {
                $combined->push([
                    'type' => 'correction',
                    'data' => $correction,
                    'sort_time' => $correction->time_done,
                ]);
            }

            // Sort combined collection by close time descending
            $combined = $combined->sortByDesc('sort_time')->values();
            // Paginate the combined results
            $perPage = min($request->input('per_page', 15), 500);
            $currentPage = $request->input('page', 1);
            $total = $combined->count();
            $lastPage = (int) ceil($total / $perPage);

            // Get items for current page
            $offset = ($currentPage - 1) * $perPage;
            $items = $combined->slice($offset, $perPage);

            // Transform to resources
            $data = $items->map(function ($item) {
                if ($item['type'] === 'trade') {
                    return new TradeResource($item['data']);
                } else {
                    return new CorrectionResource($item['data']);
                }
            })->values()->all();

            return response()->json([
                'data' => $data,
                'meta' => [
                    'from' => $total > 0 ? $offset + 1 : null,
                    'to' => $total > 0 ? min($offset + $perPage, $total) : null,
                    'total' => $total,
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'per_page' => $perPage,
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
            $trade = Trade::with('account:id,user_id,currency')->where(['position_id' => $id])->first();

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
