<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;

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
        $query = User::query()->with('countryDetail:country_name,country_alpha')->with(['liveAccounts' => function ($q) {
            $q->where('demo', 0)
                ->select('id', 'user_id', 'code', 'balance');
        }])->select('id', 'email', 'country', 'ib1', 'cxd', 'status', 'kyc_verify', 'created_at', 'updated_at', 'client_ip');

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
        // Filter by email (only if it has a value)
        $email = $request->input('email');
        if (!empty($email)) {
            $query->where('email', $email);
        }

        // Filter by affiliate ID (only if it has a value)
        $affId = $request->input('aff_id');
        if (!empty($affId)) {
            $query->where('ib1', $affId);
        }

        // Paginate the results
        $users = $query->paginate($request->per_page ?? 15);

        // First, try to handle UTF-8 using a more direct approach
        try {
            // Use UserResource instead of UserCollection for better control
            // And wrap it in a custom array structure without pagination links
            return response()->json([
                'data' => UserResource::collection($users->items()),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'from' => $users->firstItem(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'to' => $users->lastItem(),
                    'total' => $users->total(),
                ],
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            // Log the error for investigation
            Log::error('JSON encoding error: ' . $e->getMessage());

            // Fall back to manual cleaning of user data
            $cleanedUsers = $this->cleanUserCollection($users);
            return response()->json([
                'data' => UserResource::collection($cleanedUsers->items()),
                'meta' => [
                    'current_page' => $cleanedUsers->currentPage(),
                    'from' => $cleanedUsers->firstItem(),
                    'last_page' => $cleanedUsers->lastPage(),
                    'per_page' => $cleanedUsers->perPage(),
                    'to' => $cleanedUsers->lastItem(),
                    'total' => $cleanedUsers->total(),
                ],
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }
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
        try {
            $user = User::findOrFail($request->id);
            return (new UserResource($user))
                ->response()
                ->setEncodingOptions(JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            // Log the error for investigation
            Log::error('JSON encoding error for user ' . $user->id . ': ' . $e->getMessage());

            // Fall back to manual cleaning
            $cleanedUser = $this->cleanUserData($user);
            //            return (new UserResource($cleanedUser))
            //                ->response()
            //                ->setEncodingOptions(JSON_UNESCAPED_UNICODE);
            return [];
        }
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

    /**
     * Clean all string attributes in the user collection
     */
    protected function cleanUserCollection($users)
    {
        $cleanedUsers = clone $users;

        // Get a clean collection of users
        $cleanedItems = collect($users->items())->map(function ($user) {
            return $this->cleanUserData($user);
        });

        // Replace the items in the paginator with our cleaned items
        $cleanedUsersReflection = new \ReflectionClass($cleanedUsers);
        $itemsProperty = $cleanedUsersReflection->getProperty('items');
        $itemsProperty->setAccessible(true);
        $itemsProperty->setValue($cleanedUsers, $cleanedItems);

        return $cleanedUsers;
    }

    /**
     * Clean all string attributes in a user model
     */
    protected function cleanUserData($user)
    {
        $cleanedUser = clone $user;
        $attributes = $user->getAttributes();

        foreach ($attributes as $key => $value) {
            if (is_string($value)) {
                // Fix encoding issues by converting to valid UTF-8
                $cleanValue = $this->cleanString($value);
                $cleanedUser->$key = $cleanValue;
            }
        }

        return $cleanedUser;
    }

    /**
     * Clean a string of invalid UTF-8 characters
     */
    protected function cleanString($string)
    {
        // First try using mb_convert_encoding
        $cleaned = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

        // If that fails, try a more aggressive approach
        if (!mb_check_encoding($cleaned, 'UTF-8')) {
            $cleaned = preg_replace(
                '/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]' .
                    '|[\x00-\x7F][\x80-\xBF]+' .
                    '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' .
                    '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})' .
                    '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
                '�',
                $string
            );

            // As a last resort, just strip all non-ASCII characters
            if (!mb_check_encoding($cleaned, 'UTF-8')) {
                $cleaned = preg_replace('/[^\x20-\x7E]/', '�', $string);
            }
        }

        return $cleaned;
    }
}
