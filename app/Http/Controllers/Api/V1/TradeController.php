<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TradeResource;
use App\Models\Trade;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    /**
     * Display a listing of trades.
     * Supports filtering by open time range, user ID, symbol, and status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Authorization check for API token permissions
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:trades:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $request->validate([
            'open_time_from' => 'nullable|date',
            'open_time_to' => 'nullable|date|after_or_equal:open_time_from',
            'user_id' => 'nullable|string',
            'email' => 'nullable|string|email',
            'symbol' => 'nullable|string|max:20',
            'status' => 'nullable|string|max:50',
            'type' => 'nullable|string|in:buy,sell',
            'per_page' => 'nullable|integer|min:1|max:500',
            'sort_by' => 'nullable|string|in:id,open_time,close_time,symbol,profit,volume,account_id,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'include_archived' => 'nullable|boolean',
        ]);

        // Get filters
        $openTimeFrom = $request->input('open_time_from');
        $openTimeTo = $request->input('open_time_to');
        $userId = $request->input('user_id');
        $email = $request->input('email');
        $symbol = $request->input('symbol');
        $status = $request->input('status');
        $type = $request->input('type');
        $includeArchived = filter_var($request->input('include_archived', false), FILTER_VALIDATE_BOOLEAN);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 15);

        // Build query
        $query = Trade::query()
            ->with(['account', 'user']);

        // Handle archived records
        if (! $includeArchived) {
            $query->whereNull('deleted_at');
        } else {
            $query->withTrashed();
        }

        // Apply filters
        if ($openTimeFrom) {
            $query->whereDate('open_time', '>=', $openTimeFrom);
        }

        if ($openTimeTo) {
            $query->whereDate('open_time', '<=', $openTimeTo);
        }

        if ($userId) {
            $query->whereHas('user', function ($q) use ($userId) {
                $q->where('aspnetusers.id', $userId);
            });
        }

        if ($email) {
            $query->whereHas('user', function ($q) use ($email) {
                $q->where('aspnetusers.email', $email);
            });
        }

        if ($symbol) {
            $query->where('symbol', 'like', '%'.$symbol.'%');
        }

        if ($status) {
            $query->where('status', 'like', '%'.$status.'%');
        }

        if ($type) {
            $query->where('type', $type);
        }

        // Apply sorting
        if ($sortBy === 'id') {
            $query->orderBy('id', $sortOrder);
        } elseif ($sortBy === 'symbol') {
            $query->orderBy('symbol', $sortOrder);
        } elseif ($sortBy === 'profit') {
            $query->orderBy('profit', $sortOrder);
        } elseif ($sortBy === 'volume') {
            $query->orderBy('volume', $sortOrder);
        } elseif ($sortBy === 'account_id') {
            $query->orderBy('account_id', $sortOrder);
        } elseif ($sortBy === 'open_time') {
            $query->orderBy('open_time', $sortOrder);
        } elseif ($sortBy === 'close_time') {
            $query->orderBy('close_time', $sortOrder);
        } else {
            $query->orderBy('created_at', $sortOrder);
        }

        $trades = $query->paginate($perPage);

        return response()->json([
            'data' => TradeResource::collection($trades->items()),
            'meta' => [
                'total' => $trades->total(),
                'per_page' => $trades->perPage(),
                'current_page' => $trades->currentPage(),
                'last_page' => $trades->lastPage(),
                'from' => $trades->firstItem(),
                'to' => $trades->lastItem(),
                'total_pages' => $trades->lastPage(),
            ],
            'links' => [
                'first' => $trades->url(1),
                'last' => $trades->url($trades->lastPage()),
                'prev' => $trades->previousPageUrl(),
                'next' => $trades->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Display the specified trade.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        // Authorization check for API token permissions
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:trades:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $includeArchived = filter_var($request->input('include_archived', false), FILTER_VALIDATE_BOOLEAN);

        $query = Trade::query()->with(['account', 'user']);

        if (! $includeArchived) {
            $query->whereNull('deleted_at');
        } else {
            $query->withTrashed();
        }

        $trade = $query->find($id);

        if (! $trade) {
            return response()->json(['error' => 'Trade not found'], 404);
        }

        return response()->json([
            'data' => new TradeResource($trade),
        ]);
    }

    /**
     * Get trade statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        // Authorization check for API token permissions
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:trades:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $request->validate([
            'open_time_from' => 'nullable|date',
            'open_time_to' => 'nullable|date|after_or_equal:open_time_from',
            'user_id' => 'nullable|string',
            'email' => 'nullable|string|email',
            'symbol' => 'nullable|string|max:20',
            'type' => 'nullable|string|in:buy,sell',
            'include_archived' => 'nullable|boolean',
        ]);

        $openTimeFrom = $request->input('open_time_from');
        $openTimeTo = $request->input('open_time_to');
        $userId = $request->input('user_id');
        $email = $request->input('email');
        $symbol = $request->input('symbol');
        $type = $request->input('type');
        $includeArchived = filter_var($request->input('include_archived', false), FILTER_VALIDATE_BOOLEAN);

        $query = Trade::query();

        // Handle archived records
        if (! $includeArchived) {
            $query->whereNull('deleted_at');
        } else {
            $query->withTrashed();
        }

        // Apply filters
        if ($openTimeFrom) {
            $query->whereDate('open_time', '>=', $openTimeFrom);
        }

        if ($openTimeTo) {
            $query->whereDate('open_time', '<=', $openTimeTo);
        }

        if ($userId) {
            $query->whereHas('user', function ($q) use ($userId) {
                $q->where('aspnetusers.id', $userId);
            });
        }

        if ($email) {
            $query->whereHas('user', function ($q) use ($email) {
                $q->where('aspnetusers.email', $email);
            });
        }

        if ($symbol) {
            $query->where('symbol', 'like', '%'.$symbol.'%');
        }

        if ($type) {
            $query->where('type', $type);
        }

        // Calculate statistics
        $stats = [
            'total_trades' => $query->count(),
            'winning_trades' => (clone $query)->where('profit', '>', 0)->count(),
            'losing_trades' => (clone $query)->where('profit', '<', 0)->count(),
            'breakeven_trades' => (clone $query)->where('profit', '=', 0)->count(),
            'total_profit' => (clone $query)->sum('profit'),
            'total_volume' => (clone $query)->sum('volume'),
            'total_commission' => (clone $query)->sum('commission'),
            'total_swap' => (clone $query)->sum('swap'),
            'buy_trades' => (clone $query)->where('type', 'buy')->count(),
            'sell_trades' => (clone $query)->where('type', 'sell')->count(),
            'avg_profit' => (clone $query)->avg('profit'),
            'max_profit' => (clone $query)->max('profit'),
            'min_profit' => (clone $query)->min('profit'),
            'avg_volume' => (clone $query)->avg('volume'),
        ];

        // Calculate win rate
        if ($stats['total_trades'] > 0) {
            $stats['win_rate'] = round(($stats['winning_trades'] / $stats['total_trades']) * 100, 2);
        } else {
            $stats['win_rate'] = 0;
        }

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Check if user has specific permission.
     */
    protected function checkPermission($user, string $permission): bool
    {
        if (! $user || ! method_exists($user, 'can')) {
            return false;
        }

        return $user->can($permission);
    }
}
