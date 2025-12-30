<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

/**
 * Admin Zapier Accounts Controller
 * 
 * Manages view and export of accounts created via Zapier webhook
 * Features:
 * - List all Zapier-created accounts
 * - Filter by date range, status
 * - Search by email, name
 * - Export to CSV/Excel
 * - Pagination
 */
class ZapierAccountsController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin');
        $this->middleware('check.permissions:client:viewAny', ['only' => ['index', 'getData', 'export']]);
    }

    /**
     * Display Zapier accounts index page
     */
    public function index()
    {
        return view('admin.zapier-accounts.index');
    }

    /**
     * Get Zapier accounts data for DataTables
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        try {
            // Query users created via Zapier without eager loading initially
            $query = User::query()
                ->where('created_from', 'zapier')
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->has('email') && $request->email) {
                $query->where('email', 'like', '%' . $request->email . '%');
            }

            if ($request->has('name') && $request->name) {
                $query->where('fullname', 'like', '%' . $request->name . '%');
            }

            if ($request->has('status') && $request->status) {
                if ($request->status === 'verified') {
                    $query->where('email_confirmed', 1);
                } elseif ($request->status === 'unverified') {
                    $query->where('email_confirmed', 0);
                }
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Use DataTables for server-side processing
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('user_id', function ($user) {
                    return $user->id;
                })
                ->addColumn('name', function ($user) {
                    return $user->fullname ?? 'N/A';
                })
                ->addColumn('email', function ($user) {
                    return $user->email;
                })
                ->addColumn('phone', function ($user) {
                    return $user->number ?? 'N/A';
                })
                ->addColumn('accounts_count', function ($user) {
                    try {
                        $liveCount = $user->liveAccounts()->count();
                        $demoCount = $user->demoAccounts()->count();
                        return "Live: {$liveCount}, Demo: {$demoCount}";
                    } catch (\Exception $e) {
                        return "0";
                    }
                })
                ->addColumn('account_codes', function ($user) {
                    try {
                        $codes = $user->liveAccounts()->pluck('code')
                            ->merge($user->demoAccounts()->pluck('code'))
                            ->implode(', ');
                        return $codes ?: 'None';
                    } catch (\Exception $e) {
                        return 'None';
                    }
                })
                ->addColumn('status', function ($user) {
                    $badge = $user->email_confirmed ? 'success' : 'warning';
                    $status = $user->email_confirmed ? 'Verified' : 'Unverified';
                    return "<span class='badge badge-{$badge}'>{$status}</span>";
                })
                ->addColumn('created_at', function ($user) {
                    return $user->created_at ? $user->created_at->format('M d, Y H:i') : 'N/A';
                })
                ->addColumn('actions', function ($user) {
                    // Show resend welcome email and delete buttons
                    $buttons = '<button class="btn btn-sm btn-primary resend-welcome-email mr-1" data-id="' . $user->id . '" title="Resend Welcome Email">';
                    $buttons .= '<i class="fas fa-envelope"></i> Resend';
                    $buttons .= '</button>';

                    $buttons .= '<button class="btn btn-sm btn-danger delete-zapier-user" data-id="' . $user->id . '" title="Delete User">';
                    $buttons .= '<i class="fas fa-trash"></i> Delete';
                    $buttons .= '</button>';
                    return $buttons;
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error fetching Zapier accounts', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error fetching data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Zapier accounts to CSV/Excel
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        try {
            $query = User::query()
                ->where('created_from', 'zapier')
                ->with(['liveAccounts', 'demoAccounts'])
                ->orderBy('created_at', 'desc');

            // Apply same filters as getData
            if ($request->has('email') && $request->email) {
                $query->where('email', 'like', '%' . $request->email . '%');
            }

            if ($request->has('name') && $request->name) {
                $query->where('fullname', 'like', '%' . $request->name . '%');
            }

            if ($request->has('status') && $request->status) {
                if ($request->status === 'verified') {
                    $query->where('email_confirmed', 1);
                } elseif ($request->status === 'unverified') {
                    $query->where('email_confirmed', 0);
                }
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $users = $query->get();

            // Generate CSV
            $fileName = 'zapier-accounts-' . now()->format('Y-m-d-His') . '.csv';
            $path = storage_path('exports/' . $fileName);

            // Create directory if it doesn't exist
            if (!file_exists(storage_path('exports'))) {
                mkdir(storage_path('exports'), 0755, true);
            }

            $file = fopen($path, 'w');

            // Add headers
            fputcsv($file, [
                'User ID',
                'Full Name',
                'Email',
                'Phone',
                'Live Accounts',
                'Demo Accounts',
                'Account Codes',
                'Email Verified',
                'Created At',
                'Country',
                'Status'
            ]);

            // Add data
            foreach ($users as $user) {
                $liveCount = $user->liveAccounts->count();
                $demoCount = $user->demoAccounts->count();
                $codes = $user->liveAccounts->pluck('code')->merge(
                    $user->demoAccounts->pluck('code')
                )->implode('; ');

                fputcsv($file, [
                    $user->id,
                    $user->fullname ?? 'N/A',
                    $user->email,
                    $user->number ?? 'N/A',
                    $liveCount,
                    $demoCount,
                    $codes ?: 'None',
                    $user->email_confirmed ? 'Yes' : 'No',
                    $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A',
                    $user->country ?? 'N/A',
                    'Active'
                ]);
            }

            fclose($file);

            Log::info('Zapier accounts exported', [
                'file' => $fileName,
                'count' => $users->count()
            ]);

            return response()->download($path)->deleteFileAfterSend(true);
        } catch (Exception $e) {
            Log::error('Error exporting Zapier accounts', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

    /**
     * Get statistics for Zapier accounts
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        try {
            $totalZapierUsers = User::where('created_from', 'zapier')->count();
            $verifiedUsers = User::where('created_from', 'zapier')
                ->where('email_confirmed', 1)
                ->count();
            $unverifiedUsers = $totalZapierUsers - $verifiedUsers;

            // Count accounts
            $totalAccounts = Account::whereIn('user_id',
                User::where('created_from', 'zapier')->pluck('id')
            )->count();

            // Get this month's signups
            $thisMonthSignups = User::where('created_from', 'zapier')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();

            return response()->json([
                'total_users' => $totalZapierUsers,
                'verified_users' => $verifiedUsers,
                'unverified_users' => $unverifiedUsers,
                'total_accounts' => $totalAccounts,
                'this_month_signups' => $thisMonthSignups,
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching Zapier stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error fetching statistics'
            ], 500);
        }
    }

    /**
     * Delete Zapier user and associated accounts
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUser(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:aspnetusers,id',
            ]);

            $user = User::findOrFail($request->id);

            // Check if user was created via Zapier
            if ($user->created_from !== 'zapier') {
                return response()->json([
                    'success' => false,
                    'message' => 'This user was not created via Zapier'
                ], 403);
            }

            // Delete all associated accounts
            Account::where('user_id', $user->id)->delete();

            // Delete user
            $user->delete();

            Log::info('Zapier user deleted', [
                'user_id' => $user->id,
                'email' => $user->email,
                'admin_id' => auth()->guard('admin')->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User and accounts deleted successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting Zapier user', [
                'error' => $e->getMessage(),
                'user_id' => $request->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend welcome / account email to a Zapier-created user
     */
    public function resendEmail(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:aspnetusers,id',
            ]);

            $user = User::findOrFail($request->id);

            if ($user->created_from !== 'zapier') {
                return response()->json([
                    'success' => false,
                    'message' => 'This user was not created via Zapier'
                ], 403);
            }

            // Send general welcome email (same as Zapier flow)
            try {
                $settings = settings();
                $emailSubject = $settings['admin_title'] . ' - Welcome to LQH Markets';
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $settings['email_from_address'] . '>' . "\r\n";

                $content = '<p>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</p>' .
                    '<p>Your account has been created.</p>';

                $templateVars = [
                    'name' => $user->fullname,
                    'server_name' => $settings['mt5_company_name'],
                    'email' => $settings['email_from_address'],
                    'content' => $content,
                    'title_right' => 'Welcome',
                    'subtitle_right' => 'To LQH Markets',
                    'btn_text' => 'Dashboard',
                    'site_link' => $settings['copyright_site_name_text'],
                ];

                // send general welcome via MailService
                app(\App\Services\MailService::class)->sendEmail($user->email, $emailSubject, $headers, '', $templateVars);
            } catch (\Exception $e) {
                Log::warning('Zapier Admin: Failed to send general welcome email', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            }

            // Resend MT5 account emails for each live account
            $accounts = $user->liveAccounts()->get();
            $sent = 0;
            foreach ($accounts as $account) {
                try {
                    // Reconstruct the mt5 user object expected by MT5Accounts::sendMail
                    $new_user = json_decode(json_encode([
                        'Email' => $account->email ?? $user->email,
                        'Name' => $account->name ?? $user->fullname,
                        'Login' => $account->code,
                        'MainPassword' => $account->trader_password,
                        'InvestPassword' => $account->invester_password,
                        'Leverage' => $account->leverage ?? 1,
                        'type' => optional($account->accountType)->ac_name ?? 'Live',
                    ]));

                    $mt5Controller = app(\App\Http\Controllers\MT5Accounts::class);
                    $mt5Controller->sendMail($new_user, 'Live', $account->platform);
                    $sent++;
                } catch (\Exception $e) {
                    Log::warning('Zapier Admin: Failed to resend MT5 account email', ['error' => $e->getMessage(), 'account_id' => $account->id]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Resend attempted',
                'emails_sent' => $sent
            ]);
        } catch (Exception $e) {
            Log::error('Error resending Zapier emails', ['error' => $e->getMessage(), 'id' => $request->id]);
            return response()->json([
                'success' => false,
                'message' => 'Error resending email: ' . $e->getMessage()
            ], 500);
        }
    }
}
