<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\RelationshipManagerResource;
use App\Models\EmployeeList;
use Illuminate\Http\Request;

class RmController extends Controller
{
    /**
     * Display a paginated listing of relationship managers.
     * Supports filtering by name, email, role, and status
     */
    public function index(Request $request)
    {
        // Authorization check for API token permissions
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:relationship-managers:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|string|email',
            'role_id' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:500',
            'sort_by' => 'nullable|string|in:id,name,email,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'include_archived' => 'nullable|boolean',
        ]);

        $includeArchived = filter_var($request->input('include_archived', false), FILTER_VALIDATE_BOOLEAN);

        $query = EmployeeList::query();

        // Handle archived records
        if (! $includeArchived) {
            $query->whereNull('deleted_at');
        } else {
            $query->withTrashed();
        }

        // Name filter (partial match)
        if ($request->has('name')) {
            $query->where('name', 'like', '%'.$request->input('name').'%');
        }

        // Email filter (exact match)
        if ($request->has('email')) {
            $query->where('email', $request->input('email'));
        }

        // Role filter
        if ($request->has('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $rms = $query->paginate($perPage);

        return RelationshipManagerResource::collection($rms);
    }

    /**
     * Display a single relationship manager.
     *
     * @param  string  $id  RM ID
     */
    public function show($id)
    {
        // Authorization check
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:relationship-managers:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $rm = EmployeeList::find($id);

        if (! $rm) {
            return response()->json(['error' => 'Relationship manager not found'], 404);
        }

        return new RelationshipManagerResource($rm);
    }

    /**
     * Check if user has specific permission
     *
     * @param  mixed  $user
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
