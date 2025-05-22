<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     * Returns users with fields required for Cellexpert integration
     * Supports filtering by registration date, last modified date, user ID, and affiliate ID
     */
    public function index(Request $request)
    {
        // Initialize query
        $query = User::query();

        // Apply filters only when there are actual values
        // Filter by registration date range
        $regDateFrom = $request->input('registration_date_from');
        $regDateTo = $request->input('registration_date_to');

        if (!empty($regDateFrom) && !empty($regDateTo)) {
            $query->whereBetween('created_at', [
                Carbon::parse($regDateFrom)->startOfDay(),
                Carbon::parse($regDateTo)->endOfDay()
            ]);
        } elseif (!empty($regDateFrom)) {
            $query->where('created_at', '>=', Carbon::parse($regDateFrom)->startOfDay());
        } elseif (!empty($regDateTo)) {
            $query->where('created_at', '<=', Carbon::parse($regDateTo)->endOfDay());
        }

        // Filter by last modified date range
        $modDateFrom = $request->input('last_modified_date_from');
        $modDateTo = $request->input('last_modified_date_to');

        if (!empty($modDateFrom) && !empty($modDateTo)) {
            $query->whereBetween('updated_at', [
                Carbon::parse($modDateFrom)->startOfDay(),
                Carbon::parse($modDateTo)->endOfDay()
            ]);
        } elseif (!empty($modDateFrom)) {
            $query->where('updated_at', '>=', Carbon::parse($modDateFrom)->startOfDay());
        } elseif (!empty($modDateTo)) {
            $query->where('updated_at', '<=', Carbon::parse($modDateTo)->endOfDay());
        }

        // Filter by user ID (only if it has a value)
        $userId = $request->input('user_id');
        if (!empty($userId)) {
            $query->where('id', $userId);
        }

        // Filter by affiliate ID (only if it has a value)
        $affId = $request->input('aff_id');
        if (!empty($affId)) {
            $query->where('ib1', $affId);
        }

        // Paginate the results
        $users = $query->paginate($request->per_page ?? 15);

        // Return the user collection resource with JSON encoding options to handle invalid UTF-8
        return (new UserCollection($users))
            ->response()
            ->setEncodingOptions(JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user, Request $request)
    {
        // Return with JSON encoding options to handle invalid UTF-8
        return (new UserResource($user))
            ->response()
            ->setEncodingOptions(JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
