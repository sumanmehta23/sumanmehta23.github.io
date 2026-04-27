<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\IbResource;
use App\Models\Ib1;
use Illuminate\Http\Request;

class IbController extends Controller
{
    /**
     * Display a paginated listing of IBs (Affiliate Partners).
     * Supports filtering by email, status, country, and name
     */
    public function index(Request $request)
    {
        // Authorization check for API token permissions
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:ibs:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $request->validate([
            'email' => 'nullable|string|email',
            'name' => 'nullable|string',
            'country' => 'nullable|string',
            'ib_status' => 'nullable|string',
            'parent_id' => 'nullable|string',
            'depth' => 'nullable|integer|min:0',
            'include_parent' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:500',
            'sort_by' => 'nullable|string|in:id,email,firstname,created_at,ib_status',
            'sort_order' => 'nullable|string|in:asc,desc',
            'include_archived' => 'nullable|boolean',
        ]);

        $includeArchived = filter_var($request->input('include_archived', false), FILTER_VALIDATE_BOOLEAN);

        $query = Ib1::query();

        // Handle archived records
        if (! $includeArchived) {
            $query->whereNull('deleted_at');
        } else {
            $query->withTrashed();
        }

        // Email filter (exact match)
        if ($request->has('email')) {
            $query->where('email', $request->input('email'));
        }

        // Name filter (partial match) - search both firstname and lastname
        if ($request->has('name')) {
            $name = $request->input('name');
            $query->where(function ($q) use ($name) {
                $q->where('firstname', 'like', '%'.$name.'%')
                    ->orWhere('lastname', 'like', '%'.$name.'%');
            });
        }

        // Country filter
        if ($request->has('country')) {
            $query->where('country', $request->input('country'));
        }

        // IB Status filter
        if ($request->has('ib_status')) {
            $query->where('ib_status', $request->input('ib_status'));
        }

        // Parent ID filter (for nested set hierarchy)
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->input('parent_id'));
        }

        // Depth filter (for nested set hierarchy)
        if ($request->has('depth')) {
            $query->where('_lft', '>', 0); // Ensure node exists in tree
            $query->addSelect(\DB::raw('(_rgt - _lft - 1) / 2 as depth'));
            $depth = (int) $request->input('depth');
            $query->havingRaw('(_rgt - _lft - 1) / 2 = ?', [$depth]);
        }

        // Include parent relationship if requested
        if (filter_var($request->input('include_parent', false), FILTER_VALIDATE_BOOLEAN)) {
            $query->with('parent');
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);
        $ibs = $query->paginate($perPage);

        return IbResource::collection($ibs);
    }

    /**
     * Display a single IB.
     *
     * @param  string  $id  IB ID
     */
    public function show($id)
    {
        // Authorization check
        $user = auth('sanctum')->user();
        if ($user && ! $this->checkPermission($user, 'api:kpi:ibs:read')) {
            return response()->json(['error' => 'Insufficient permissions to access this endpoint'], 403);
        }

        $ib = Ib1::with('parent')->find($id);

        if (! $ib) {
            return response()->json(['error' => 'IB not found'], 404);
        }

        return new IbResource($ib);
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
