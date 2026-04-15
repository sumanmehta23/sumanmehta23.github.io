<?php

namespace App\Http\Controllers\Admin;

use App\Models\Account;
use App\MT5\MTRetCode;
use App\Services\UniversalMT5Service;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;
use App\Http\Controllers\Controller;

class AccountNotFoundController extends Controller
{
    protected $mt5Service;

    public function __construct(UniversalMT5Service $mt5Service)
    {
        $this->mt5Service = $mt5Service;
        
        // Permission checks via middleware
        // $this->middleware('check.permissions:accounts:view_not_found', ['only' => ['index', 'export', 'stats']]);
        // $this->middleware('check.permissions:accounts:verify_not_found', ['only' => ['stats']]);
        // $this->middleware('check.permissions:accounts:bulk_archive', ['only' => ['bulkVerifyAndArchive']]);
    }

    /**
     * Display a listing of accounts not found in MT5
     */
    public function index(Request $request)
    {
        $query = Account::notFoundInMt5()
            ->where('account_request_status', 1)
            ->orderBy('updated_at', 'desc');

        // Filter by account code if provided
        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->input('code') . '%');
        }

        // Filter by user email if provided
        if ($request->filled('email')) {
            $query->whereHas('user', function ($q) {
                $q->where('email', 'like', '%' . request()->input('email') . '%');
            });
        }

        // Filter by deletion type if provided
        if ($request->filled('deletion_type')) {
            $query->where('deletion_type', $request->input('deletion_type'));
        }

        $accounts = $query->paginate(200);
        // Get distinct deletion types for filter dropdown
        $deletionTypes = Account::notFoundInMt5()
            ->distinct()
            ->pluck('deletion_type')
            ->filter()
            ->values();

        return view('admin.accounts.not_found_in_mt5', [
            'accounts' => $accounts,
            'deletionTypes' => $deletionTypes,
        ]);
    }

    /**
     * Bulk verify and archive accounts not found in MT5
     */
    public function bulkVerifyAndArchive(Request $request)
    {
        $accountIds = $request->input('account_ids', []);

        if (empty($accountIds)) {
            return response()->json(['error' => 'No accounts selected'], 400);
        }

        $results = [
            'verified' => 0,
            'deleted' => 0,
            'found' => 0,
            'errors' => [],
            'details' => [],
        ];

        try {
            $accounts = Account::whereIn('id', $accountIds)
                ->notFoundInMt5()
                ->with('user')
                ->get();

            foreach ($accounts as $account) {
                try {
                    $login = $account->code;

                    // Re-check if account exists in MT5
                    $total = 0;
                    $error_code = $this->mt5Service->executeOperation(function ($api) use ($login, &$total) {
                        return $api->HistoryGetTotal($login, 'January 01,2024', 'December 31,2099', $total);
                    });

                    if ($error_code == MTRetCode::MT_RET_OK) {
                        // Account found! Remove deletion_type
                        $account->update([
                            'deletion_type' => null,
                            'trade_sync_status' => null,
                        ]);
                        $results['found']++;
                        $results['details'][] = [
                            'account_id' => $account->id,
                            'account_code' => $login,
                            'user_email' => $account->user?->email,
                            'status' => 'found',
                            'message' => 'Account found in MT5, deletion_type removed',
                        ];
                        Log::info("Account {$login} found in MT5 during bulk verification", [
                            'account_id' => $account->id,
                            'user_email' => $account->user?->email,
                        ]);
                    } elseif ($error_code == MTRetCode::MT_RET_ERR_NOTFOUND) {
                        // Account still not found - soft delete it
                        $account->delete();
                        $results['deleted']++;
                        $results['details'][] = [
                            'account_id' => $account->id,
                            'account_code' => $login,
                            'user_email' => $account->user?->email,
                            'status' => 'deleted',
                            'message' => 'Account confirmed not found and soft deleted',
                        ];
                        Log::info("Account {$login} soft deleted after bulk verification", [
                            'account_id' => $account->id,
                            'user_email' => $account->user?->email,
                            'deletion_type' => $account->deletion_type,
                        ]);
                    } else {
                        // API error - mark as verified but don't delete
                        $account->update([
                            'verified_at' => now(),
                        ]);
                        $results['verified']++;
                        $results['details'][] = [
                            'account_id' => $account->id,
                            'account_code' => $login,
                            'user_email' => $account->user?->email,
                            'status' => 'error',
                            'message' => 'API error: ' . MTRetCode::GetError($error_code),
                        ];
                        Log::warning("API error verifying account {$login}: " . MTRetCode::GetError($error_code), [
                            'account_id' => $account->id,
                            'error_code' => $error_code,
                            'user_email' => $account->user?->email,
                        ]);
                    }
                } catch (Exception $e) {
                    $results['errors'][] = [
                        'account_id' => $account->id,
                        'account_code' => $account->code,
                        'user_email' => $account->user?->email,
                        'error' => $e->getMessage(),
                    ];
                    Log::error("Error verifying account {$account->code}", [
                        'account_id' => $account->id,
                        'error' => $e->getMessage(),
                        'user_email' => $account->user?->email,
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error("Bulk verification failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($results, 200);
    }

    /**
     * Export not found accounts to CSV
     */
    public function export(Request $request)
    {
        $query = Account::notFoundInMt5()
            ->with('user')
            ->where('demo', false)
            ->orderBy('updated_at', 'desc');

        // Apply same filters as index
        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->input('code') . '%');
        }

        if ($request->filled('email')) {
            $query->whereHas('user', function ($q) {
                $q->where('email', 'like', '%' . request()->input('email') . '%');
            });
        }

        if ($request->filled('deletion_type')) {
            $query->where('deletion_type', $request->input('deletion_type'));
        }

        $accounts = $query->get();

        // Create CSV content
        $filename = 'not_found_accounts_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['Account Code', 'Account ID', 'User Email', 'User Name', 'Deletion Type', 'Deleted At', 'Updated At', 'Account Type'];

        $callback = function () use ($accounts, $columns) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, $columns);

            // Data rows
            foreach ($accounts as $account) {
                fputcsv($file, [
                    $account->code,
                    $account->id,
                    $account->user?->email,
                    $account->user?->name,
                    $account->deletion_type,
                    $account->deleted_at,
                    $account->updated_at,
                    $account->accountType?->typeName,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get statistics about not found accounts
     */
    public function stats()
    {
        $stats = [
            'total_not_found' => Account::notFoundInMt5()->count(),
            'by_deletion_type' => Account::notFoundInMt5()
                ->select('deletion_type', DB::raw('count(*) as count'))
                ->groupBy('deletion_type')
                ->get()
                ->pluck('count', 'deletion_type'),
            'deleted_in_last_7_days' => Account::notFoundInMt5()
                ->where('deleted_at', '>=', now()->subDays(7))
                ->withTrashed()
                ->count(),
            'oldest_not_found' => Account::notFoundInMt5()
                ->min('updated_at'),
        ];

        return response()->json($stats, 200);
    }

    /**
     * Sync MT5 account status
     */
    public function syncMT5AccountStatus(Request $request)
    {
        try {
            Log::info('MT5 Account Status Sync started', [
                'user_id' => auth('admin')->id(),
                'user_name' => auth('admin')->user()?->name,
            ]);

            // Use StreamedResponse with Process to stream real-time output
            return new StreamedResponse(function () {
                $php = config('app.php_cli_path') ?: trim(shell_exec('which php')) ?: PHP_BINARY;

                $process = new Process([
                    $php,
                    'artisan',
                    'app:sync-mt5-account-status'
                ]);
                

                $process->setWorkingDirectory(base_path());
                $process->setTimeout(null); // No timeout

                $process->run(function ($type, $buffer) {
                    echo $buffer;
                    ob_flush();
                    flush();
                });

                Log::info('MT5 Account Status Sync process completed', [
                    'exit_code' => $process->getExitCode(),
                    'user_id' => auth('admin')->id(),
                ]);
            }, 200, [
                'Content-Type' => 'text/plain',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        } catch (Exception $e) {
            Log::error('Error running MT5 Account Status Sync', [
                'error' => $e->getMessage(),
                'user_id' => auth('admin')->id(),
            ]);

            return response()->stream(function () use ($e) {
                echo "Error: " . $e->getMessage() . "\n";
                flush();
            }, 500, [
                'Content-Type' => 'text/plain',
            ]);
        }
    }

    /**
     * Get the PHP CLI executable path
     * Handles both FPM and CLI variants
     */
    private function getPhpCliPath()
{
    // Try system php first
    $whichPhp = trim(shell_exec('which php'));

    if ($whichPhp && file_exists($whichPhp)) {
        return $whichPhp;
    }

    // fallback to PHP_BINARY (last resort)
    return PHP_BINARY;
}
}