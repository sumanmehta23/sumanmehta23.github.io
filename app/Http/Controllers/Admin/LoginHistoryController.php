<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Exports\LoginHistoryExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LoginHistoryController extends Controller
{
    /**
     * Display a listing of the login history.
     */
    public function index()
    {
        return view('admin.login_history.index');
    }

    /**
     * Get login history data for DataTable
     */
    public function getLoginHistory(Request $request)
    {
        try {
            $query = LoginHistory::with('user:id,email,fullname');

            // Search functionality
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                        ->orWhere('ip', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('email', 'like', "%{$search}%")
                                ->orWhere('fullname', 'like', "%{$search}%");
                        });
                });
            }

            // Filter by action
            if ($request->has('action') && !empty($request->action)) {
                $query->where('action', $request->action);
            }

            // Filter by status
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
            }

            // Filter by date range - Default to last 30 days if no date filters provided
            $hasDateFilter = $request->has('date_from') && !empty($request->date_from) || 
                           $request->has('date_to') && !empty($request->date_to);
            
            if ($hasDateFilter) {
                if ($request->has('date_from') && !empty($request->date_from)) {
                    $query->whereDate('created_date_js', '>=', $request->date_from);
                }
                if ($request->has('date_to') && !empty($request->date_to)) {
                    $query->whereDate('created_date_js', '<=', $request->date_to);
                }
            } else {
                // Default: Show only last 30 days
                $thirtyDaysAgo = Carbon::now()->subDays(30)->startOfDay();
                $query->where('created_date_js', '>=', $thirtyDaysAgo);
            }

            // Count total records for last 30 days (if no custom date filter)
            if (!$hasDateFilter) {
                $thirtyDaysAgo = Carbon::now()->subDays(30)->startOfDay();
                $totalRecords = LoginHistory::where('created_date_js', '>=', $thirtyDaysAgo)->count();
            } else {
                $totalRecords = LoginHistory::count();
            }
            
            $filteredRecords = $query->count();

            // Ordering
            if ($request->has('order')) {
                $orderColumn = $request->columns[$request->order[0]['column']]['data'];
                $orderDir = $request->order[0]['dir'];
                
                // Handle special columns
                if ($orderColumn === 'user_email' || $orderColumn === 'user_name') {
                    $query->leftJoin('aspnetusers', 'login_history.user_id', '=', 'aspnetusers.id')
                        ->orderBy('aspnetusers.' . ($orderColumn === 'user_email' ? 'email' : 'fullname'), $orderDir)
                        ->select('login_history.*');
                } elseif ($orderColumn === 'created_date_formatted' || $orderColumn === 'created_time_formatted') {
                    $query->orderBy('created_date_js', $orderDir);
                } else {
                    $query->orderBy($orderColumn, $orderDir);
                }
            } else {
                $query->orderBy('created_date_js', 'desc');
            }

            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            $loginHistory = $query->skip($start)->take($length)->get();

            // Format data for DataTable
            $data = $loginHistory->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_email' => $item->user->email ?? $item->email ?? 'N/A',
                    'user_name' => $item->user->fullname ?? 'N/A',
                    'ip' => $item->ip ?? 'N/A',
                    'country' => $item->country ?? 'Unknown',
                    'action' => $item->action ?? 'N/A',
                    'created_date_js' => $item->created_date_js ? Carbon::parse($item->created_date_js)->format('Y-m-d H:i:s') : 'N/A',
                    'created_date_formatted' => $item->created_date_js ? Carbon::parse($item->created_date_js)->format('Y-m-d') : 'N/A',
                    'created_time_formatted' => $item->created_date_js ? Carbon::parse($item->created_date_js)->format('H:i:s') : 'N/A',
                    'status' => $item->status ?? 0,
                    'user_id' => $item->user_id ?? null,
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching login history: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch login history data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export login history to Excel
     */
    public function export(Request $request)
    {
        try {
            $hasDateFilter = $request->has('date_from') && !empty($request->input('date_from')) || 
                           $request->has('date_to') && !empty($request->input('date_to'));
            
            $filters = [
                'action' => $request->input('action'),
                'status' => $request->input('status'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ];

            // If no date filter provided, default to last 30 days
            if (!$hasDateFilter) {
                $filters['date_from'] = Carbon::now()->subDays(30)->format('Y-m-d');
            }

            $filename = 'login_history_' . date('Y-m-d_His') . '.xlsx';
            
            return Excel::download(new LoginHistoryExport($filters), $filename);
        } catch (\Exception $e) {
            Log::error('Login history export error: ' . $e->getMessage());
            return back()->with('error', 'Failed to export login history');
        }
    }
}

