<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     * Supports filtering by registration date, last modified date, user ID, and affiliate ID
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        // Authorization check for API token permissions
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:users:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $request->validate([
            'registration_date_from' => 'nullable|date',
            'registration_date_to' => 'nullable|date|after_or_equal:registration_date_from',
            'last_modified_from' => 'nullable|date',
            'last_modified_to' => 'nullable|date|after_or_equal:last_modified_from',
            'user_id' => 'nullable|string',
            'email' => 'nullable|string|email',
            'affiliate_id' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:500',
            'sort_by' => 'nullable|string|in:id,email,name,created_at,updated_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'include_archived' => 'nullable|boolean',
        ]);

        $includeArchived = filter_var($request->input('include_archived', false), FILTER_VALIDATE_BOOLEAN);
        $query = User::query();

        // Handle archived records
        if (! $includeArchived) {
            $query->whereNull('deleted_at');
        } else {
            $query->withTrashed();
        }

        // Registration date range filter
        if ($request->has('registration_date_from')) {
            $query->whereDate('created_at', '>=', $request->input('registration_date_from'));
        }
        if ($request->has('registration_date_to')) {
            $query->whereDate('created_at', '<=', $request->input('registration_date_to'));
        }

        // Last modified date range filter
        if ($request->has('last_modified_from')) {
            $query->whereDate('updated_at', '>=', $request->input('last_modified_from'));
        }
        if ($request->has('last_modified_to')) {
            $query->whereDate('updated_at', '<=', $request->input('last_modified_to'));
        }

        // User ID filter
        if ($request->has('user_id')) {
            $query->where('id', $request->input('user_id'));
        }

        // Email filter
        if ($request->has('email')) {
            $query->where('email', $request->input('email'));
        }

        // Affiliate ID filter
        if ($request->has('affiliate_id')) {
            $query->where('affiliate_id', $request->input('affiliate_id'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $users = $query->with(['relationshipManager.employee'])->paginate($perPage);

        return UserResource::collection($users);
    }

    /**
     * Display a single user.
     *
     * @param  string  $id  User ID
     * @return mixed
     */
    public function show($id)
    {
        // Authorization check
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:users:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $user = User::with(['relationshipManager.employee'])->find($id);

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return new UserResource($user);
    }

    /**
     * Show user profile information.
     * Returns authenticated user's profile
     *
     *
     * @return mixed
     */
    public function profile(Request $request)
    {
        $user = auth('sanctum')->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Reload with eager-loaded relationships
        $user = User::with(['relationshipManager.employee'])->find($user->id);

        return new UserResource($user);
    }

    /**
     * Display bonus history for a user.
     * Supports filtering by date range, status, and bonus type
     *
     * @param  string  $id  User ID
     */
    public function bonusHistory($id, Request $request)
    {
        // Authorization check
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:users:bonus-history:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $userRecord = User::find($id);

        if (! $userRecord) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $request->validate([
            'bonus_date_from' => 'nullable|date',
            'bonus_date_to' => 'nullable|date|after_or_equal:bonus_date_from',
            'status' => 'nullable|string',
            'bonus_type' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:500',
            'sort_by' => 'nullable|string|in:id,bonus_amount,bonus_date,status,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
        ]);

        $query = $userRecord->BonusTransaction();

        // Bonus date range filter
        if ($request->has('bonus_date_from')) {
            $query->whereDate('bonus_date', '>=', $request->input('bonus_date_from'));
        }
        if ($request->has('bonus_date_to')) {
            $query->whereDate('bonus_date', '<=', $request->input('bonus_date_to'));
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Bonus type filter
        if ($request->has('bonus_type')) {
            $query->where('bonus_type', $request->input('bonus_type'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'bonus_date');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $bonuses = $query->paginate($perPage);

        return \App\Http\Resources\V1\BonusTransactionResource::collection($bonuses);
    }

    /**
     * Display competition participation for a user.
     * Shows all competitions the user participated in via their accounts
     *
     * @param  string  $id  User ID
     */
    public function competitions($id, Request $request)
    {
        // Authorization check
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:users:competitions:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $userRecord = User::find($id);

        if (! $userRecord) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $request->validate([
            'status' => 'nullable|string',
            'start_date_from' => 'nullable|date',
            'start_date_to' => 'nullable|date|after_or_equal:start_date_from',
            'per_page' => 'nullable|integer|min:1|max:500',
            'sort_by' => 'nullable|string|in:id,created_at,competition_status',
            'sort_order' => 'nullable|string|in:asc,desc',
        ]);

        $query = $userRecord->accounts()
            ->whereNotNull('competition_product_id')
            ->with('product');

        // Status filter
        if ($request->has('status')) {
            $query->where('competition_status', $request->input('status'));
        }

        // Start date range filter
        if ($request->has('start_date_from')) {
            $query->whereDate('created_at', '>=', $request->input('start_date_from'));
        }
        if ($request->has('start_date_to')) {
            $query->whereDate('created_at', '<=', $request->input('start_date_to'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $competitions = $query->paginate($perPage);

        return \App\Http\Resources\V1\UserCompetitionResource::collection($competitions);
    }

    /**
     * Check if user has specific permission
     *
     * @param  User  $user
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
