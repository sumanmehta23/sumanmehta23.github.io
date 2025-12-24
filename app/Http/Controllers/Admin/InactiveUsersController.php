<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InactiveUsersController extends Controller
{
    /**
     * Display a listing of inactive users.
     */
    public function index()
    {
        return view('admin.inactive_users.index');
    }

    /**
     * Get inactive users data for DataTable
     */
    public function getInactiveUsers(Request $request)
    {
        try {
            $query = User::where('is_inactive', true);

            // Search functionality
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                        ->orWhere('fullname', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                });
            }

            // Filter by registration date range
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('reg_date', '>=', $request->date_from);
            }
            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('reg_date', '<=', $request->date_to);
            }

            // Count total records
            $totalRecords = User::where('is_inactive', true)->count();
            $filteredRecords = $query->count();

            // Ordering
            if ($request->has('order') && !empty($request->order)) {
                $orderColumn = $request->columns[$request->order[0]['column']]['data'];
                $orderDir = $request->order[0]['dir'];
                
                if ($orderColumn === 'last_login' || $orderColumn === 'last_login_formatted') {
                    // For last_login, we need a subquery
                    $query->orderByRaw("(
                        SELECT MAX(created_date_js) 
                        FROM login_history 
                        WHERE login_history.user_id = aspnetusers.id 
                        AND action = 'login' 
                        AND status = 1
                    ) " . $orderDir);
                } elseif ($orderColumn === 'reg_date_formatted') {
                    $query->orderBy('reg_date', $orderDir);
                } else {
                    $query->orderBy($orderColumn, $orderDir);
                }
            } else {
                $query->orderBy('reg_date', 'desc');
            }

            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            $users = $query->skip($start)->take($length)->get();

            // Format data for DataTable
            $data = $users->map(function ($user) {
                // Get last login from login_history
                $lastLogin = DB::table('login_history')
                    ->where('user_id', $user->id)
                    ->where('action', 'login')
                    ->where('status', 1)
                    ->max('created_date_js');

                // Calculate days since last login
                $daysSinceLogin = null;
                if ($lastLogin) {
                    $daysSinceLogin = Carbon::parse($lastLogin)->diffInDays(Carbon::now());
                } else {
                    // If never logged in, calculate from registration date
                    $daysSinceLogin = $user->reg_date ? Carbon::parse($user->reg_date)->diffInDays(Carbon::now()) : null;
                }

                return [
                    'id' => $user->id,
                    'user_email' => $user->email ?? 'N/A',
                    'user_name' => $user->fullname ?? 'N/A',
                    'country' => $user->country ?? 'Unknown',
                    'reg_date' => $user->reg_date ? Carbon::parse($user->reg_date)->format('Y-m-d') : 'N/A',
                    'reg_date_formatted' => $user->reg_date ? Carbon::parse($user->reg_date)->format('Y-m-d H:i:s') : 'N/A',
                    'last_login' => $lastLogin ? Carbon::parse($lastLogin)->format('Y-m-d H:i:s') : 'Never',
                    'last_login_formatted' => $lastLogin ? Carbon::parse($lastLogin)->format('Y-m-d') : 'Never',
                    'days_inactive' => $daysSinceLogin ?? 'N/A',
                    'user_id' => $user->id,
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching inactive users: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch inactive users data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

