<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AccountResource;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts.
     * Supports filtering by user ID, account type, status, and creation date range.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        // Authorization check for API token permissions
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:accounts:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $request->validate([
            'user_id' => 'nullable|string',
            'email' => 'nullable|string|email',
            'account_type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'created_from' => 'nullable|date',
            'created_to' => 'nullable|date|after_or_equal:created_from',
            'per_page' => 'nullable|integer|min:1|max:500',
            'sort_by' => 'nullable|string|in:id,user_id,account_type,balance,status,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'include_archived' => 'nullable|boolean',
        ]);

        $includeArchived = filter_var($request->input('include_archived', false), FILTER_VALIDATE_BOOLEAN);

        $query = Account::query();

        // Handle archived records
        if (! $includeArchived) {
            $query->whereNull('deleted_at');
        } else {
            $query->withTrashed();
        }

        // User ID filter
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Email filter (through related user)
        if ($request->has('email')) {
            $email = $request->input('email');
            $query->whereHas('user', function ($q) use ($email) {
                $q->where('aspnetusers.email', $email);
            });
        }

        // Account type filter
        if ($request->has('account_type')) {
            $query->where('account_type', $request->input('account_type'));
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Creation date range filter
        if ($request->has('created_from')) {
            $query->whereDate('created_at', '>=', $request->input('created_from'));
        }
        if ($request->has('created_to')) {
            $query->whereDate('created_at', '<=', $request->input('created_to'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $accounts = $query->paginate($perPage);

        return AccountResource::collection($accounts);
    }

    /**
     * Display a single account.
     *
     * @param  string  $id  Account ID
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Authorization check
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:accounts:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $account = Account::find($id);

        if (! $account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        return new AccountResource($account);
    }

    /**
     * Get accounts for a specific user.
     *
     * @param  string  $userId  User ID
     * @return \Illuminate\Http\Response
     */
    public function userAccounts($userId, Request $request)
    {
        // Authorization check
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:accounts:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $request->validate([
            'status' => 'nullable|string|max:50',
            'per_page' => 'nullable|integer|min:1|max:500',
            'sort_by' => 'nullable|string|in:id,account_type,balance,status,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
        ]);

        $query = Account::query()->where('user_id', $userId);

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $accounts = $query->paginate($perPage);

        return AccountResource::collection($accounts);
    }

    /**
     * Get account statistics.
     * Returns total accounts, total balance, etc.
     *
     * @return \Illuminate\Http\Response
     */
    public function statistics(Request $request)
    {
        // Authorization check
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:accounts:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $request->validate([
            'user_id' => 'nullable|string',
            'email' => 'nullable|string|email',
            'account_type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
        ]);

        $query = Account::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        if ($request->has('email')) {
            $email = $request->input('email');
            $query->whereHas('user', function ($q) use ($email) {
                $q->where('aspnetusers.email', $email);
            });
        }
        if ($request->has('account_type')) {
            $query->where('account_type', $request->input('account_type'));
        }
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $stats = [
            'total_accounts' => $query->count(),
            'total_balance' => $query->sum('balance'),
            'average_balance' => $query->avg('balance'),
            'min_balance' => $query->min('balance'),
            'max_balance' => $query->max('balance'),
        ];

        return response()->json($stats);
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
