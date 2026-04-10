<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessStuckCommissionsJob;
use App\Jobs\RunIbCommissionAnalysisJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IbCommissionAnalysisController extends Controller
{
    public function index()
    {
        return view('admin.ib.commission-analysis-tabs');
    }

    public function startAnalysis(Request $request)
    {
        $code = $request->input('code') ?: null;
        $referral = $request->input('referral') ?: null;

        $analysisId = Str::uuid()->toString();
        $cacheKey = 'ib_analysis:' . $analysisId;

        Cache::put($cacheKey . ':status', 'queued', 900);
        Cache::put($cacheKey . ':progress', 'Queued, waiting for worker...', 900);

        RunIbCommissionAnalysisJob::dispatch($cacheKey, $code, $referral)
            ->onQueue('default');

        return response()->json([
            'analysis_id' => $analysisId,
        ]);
    }

    public function getStatus(Request $request)
    {
        $analysisId = $request->input('id');

        if (!$analysisId) {
            return response()->json(['error' => 'Missing analysis ID'], 400);
        }

        $cacheKey = 'ib_analysis:' . $analysisId;
        $status = Cache::get($cacheKey . ':status', 'unknown');
        $progress = Cache::get($cacheKey . ':progress', '');

        $response = [
            'status' => $status,
            'progress' => $progress,
        ];

        if ($status === 'completed') {
            $response['data'] = Cache::get($cacheKey . ':result');
            // DO NOT delete cache here - it's needed for tab data loading
            // Cache will expire naturally after 15 minutes (900 seconds)
        }

        return response()->json($response);
    }

    public function fixDuplicateWallets(Request $request)
    {
        $deleted = 0;

        $duplicates = DB::table('ib_wallet')
            ->select('order_id', 'user_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('order_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $keepId = DB::table('ib_wallet')
                ->where('order_id', $dup->order_id)
                ->where('user_id', $dup->user_id)
                ->orderBy('created_at', 'asc')
                ->value('id');

            if ($keepId) {
                $count = DB::table('ib_wallet')
                    ->where('order_id', $dup->order_id)
                    ->where('user_id', $dup->user_id)
                    ->where('id', '!=', $keepId)
                    ->delete();
                $deleted += $count;
            }
        }

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => "Deleted {$deleted} duplicate wallet rows.",
        ]);
    }

    public function fixDuplicateCommissions(Request $request)
    {
        $fixed = 0;
        $walletsRemoved = 0;

        $duplicates = DB::table('ib1_commission')
            ->whereNull('deleted_at')
            ->select('order_id', 'code', DB::raw('COUNT(*) as cnt'))
            ->groupBy('order_id', 'code')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $commissions = DB::table('ib1_commission')
                ->where('order_id', $dup->order_id)
                ->where('code', $dup->code)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'asc')
                ->get();

            $keep = $commissions->first();
            $toDelete = $commissions->slice(1);

            foreach ($toDelete as $dupe) {
                // Soft-delete wallets linked to this duplicate commission
                $walletCount = DB::table('ib_wallet')
                    ->where('ib1_commission_id', $dupe->id)
                    ->count();

                if ($walletCount > 0) {
                    // Move wallets to the original commission instead of deleting
                    // This preserves data while fixing the linkage
                    DB::table('ib_wallet')
                        ->where('ib1_commission_id', $dupe->id)
                        ->update([
                            'ib1_commission_id' => $keep->id,
                            'remark' => DB::raw("CONCAT(IFNULL(remark,''), ' [moved from duplicate commission]')"),
                            'ib_wallet' => '0', // Zero out the duplicate credit
                            'updated_at' => now(),
                        ]);
                    $walletsRemoved += $walletCount;
                }

                // Soft-delete the duplicate commission
                DB::table('ib1_commission')
                    ->where('id', $dupe->id)
                    ->update([
                        'deleted_at' => now(),
                        'updated_at' => now(),
                    ]);
                $fixed++;
            }
        }

        return response()->json([
            'success' => true,
            'commissions_soft_deleted' => $fixed,
            'wallets_zeroed' => $walletsRemoved,
            'message' => "Soft-deleted {$fixed} duplicate commissions. Zeroed out {$walletsRemoved} duplicate wallet credits.",
        ]);
    }

    public function processStuckCommissions(Request $request)
    {
        $processId = Str::uuid()->toString();
        $cacheKey = 'ib_stuck_process:' . $processId;

        Cache::put($cacheKey . ':status', 'queued', 1800);
        Cache::put($cacheKey . ':progress', 'Queued, waiting for worker...', 1800);

        ProcessStuckCommissionsJob::dispatch($cacheKey)
            ->onQueue('default');

        return response()->json([
            'process_id' => $processId,
        ]);
    }

    public function getStuckProcessStatus(Request $request)
    {
        $processId = $request->input('id');

        if (!$processId) {
            return response()->json(['error' => 'Missing process ID'], 400);
        }

        $cacheKey = 'ib_stuck_process:' . $processId;
        $status = Cache::get($cacheKey . ':status', 'unknown');
        $progress = Cache::get($cacheKey . ':progress', '');

        $response = [
            'status' => $status,
            'progress' => $progress,
        ];

        if ($status === 'completed') {
            $response['data'] = Cache::get($cacheKey . ':result');
            Cache::forget($cacheKey . ':status');
            Cache::forget($cacheKey . ':progress');
            Cache::forget($cacheKey . ':result');
        }

        return response()->json($response);
    }

    public function getTableData(Request $request)
    {
        $section = $request->input('section');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 50;

        $code = $request->input('code');
        $referral = $request->input('referral');

        $items = [];
        $total = 0;

        switch ($section) {
            case 'overview':
                $items = []; // Overview is returned differently
                break;
            case 'duplicate_wallets':
                $result = $this->analyzeDupWallets($code, $referral);
                $items = $result['items'] ?? [];
                $total = $result['count'] ?? 0;
                break;
            case 'duplicate_commissions':
                $result = $this->analyzeDupCommissions($code);
                $items = $result['items'] ?? [];
                $total = $result['count'] ?? 0;
                break;
            case 'missing_commissions':
                $result = $this->analyzeMissingCommissions($code);
                $items = $result;
                break;
            case 'stuck_commissions':
                $result = $this->analyzeStuckCommissions();
                $items = $result;
                break;
            case 'overpaid_ibs':
                $result = $this->analyzeOverpaidIbs();
                $items = $result['overpaid_ibs'] ?? [];
                $total = count($items);
                break;
            case 'fixable_issues':
                $result = $this->analyzeFixableIssues();
                $items = $result['items'] ?? [];
                $total = $result['total'] ?? 0;
                break;
            case 'overpayment_audit':
                $result = $this->analyzeOverpaymentAudit();
                $items = $result['ibs_affected'] ?? [];
                $total = count($items);
                break;
            case 'pipeline_health':
                $result = $this->analyzePipelineHealth();
                $items = $result;
                break;
        }

        if (!is_array($items)) {
            $items = [];
        }

        $paginated = collect($items)->forPage($page, $perPage);
        $lastPage = (int) ceil(($total ?: count($items)) / $perPage);

        return response()->json([
            'data' => $paginated->values(),
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total ?: count($items),
        ]);
    }

    private function analyzeDupWallets($code, $referral)
    {
        // Use the job's method directly
        $job = new \App\Jobs\RunIbCommissionAnalysisJob('temp');
        $reflect = new \ReflectionClass($job);
        $method = $reflect->getMethod('getDuplicateWallets');
        $method->setAccessible(true);
        return $method->invoke($job, $code, $referral);
    }

    private function analyzeDupCommissions($code)
    {
        $job = new \App\Jobs\RunIbCommissionAnalysisJob('temp');
        $reflect = new \ReflectionClass($job);
        $method = $reflect->getMethod('getDuplicateCommissions');
        $method->setAccessible(true);
        return $method->invoke($job, $code);
    }

    private function analyzeMissingCommissions($code)
    {
        $job = new \App\Jobs\RunIbCommissionAnalysisJob('temp');
        $reflect = new \ReflectionClass($job);
        $method = $reflect->getMethod('getMissingCommissions');
        $method->setAccessible(true);
        return $method->invoke($job, $code);
    }

    private function analyzeStuckCommissions()
    {
        $job = new \App\Jobs\RunIbCommissionAnalysisJob('temp');
        $reflect = new \ReflectionClass($job);
        $method = $reflect->getMethod('getStuckCommissions');
        $method->setAccessible(true);
        return $method->invoke($job);
    }

    private function analyzeOverpaidIbs()
    {
        $job = new \App\Jobs\RunIbCommissionAnalysisJob('temp');
        $reflect = new \ReflectionClass($job);
        $method = $reflect->getMethod('getOverpaidIbs');
        $method->setAccessible(true);
        return $method->invoke($job, null);
    }

    private function analyzeOverpaymentAudit()
    {
        $job = new \App\Jobs\RunIbCommissionAnalysisJob('temp');
        $reflect = new \ReflectionClass($job);
        $method = $reflect->getMethod('getOverpaymentAudit');
        $method->setAccessible(true);
        return $method->invoke($job, null);
    }

    private function analyzeFixableIssues()
    {
        $service = new \App\Services\FixOverpaidCommissionsService();
        $fixable = $service->getFixableDuplicates();

        $totalRecoverable = 0;
        $totalEntries = 0;

        $formatted = array_map(function ($group) use (&$totalRecoverable, &$totalEntries) {
            $totalRecoverable += $group['recoverable_amount'] ?? 0;
            $totalEntries += $group['duplicate_entries'] ?? 0;

            return [
                'expert_position_id' => $group['expert_position_id'] ?? '',
                'user_id' => $group['user_id'] ?? '',
                'user_email' => $group['user_email'] ?? '',
                'referral_code' => $group['referral_code'] ?? '',
                'order_count' => $group['order_count'] ?? 0,
                'duplicate_count' => $group['duplicate_entries'] ?? 0,
                'recoverable_amount' => $group['recoverable_amount'] ?? 0,
                'withdrawn_amount' => $group['withdrawn_amount'] ?? 0,
                'status' => 'pending_fix',
            ];
        }, $fixable);

        return [
            'items' => $formatted,
            'total' => count($formatted),
            'total_recoverable' => $totalRecoverable,
            'total_entries' => $totalEntries,
        ];
    }

    private function analyzePipelineHealth()
    {
        $job = new \App\Jobs\RunIbCommissionAnalysisJob('temp');
        $reflect = new \ReflectionClass($job);
        $method = $reflect->getMethod('getPipelineHealth');
        $method->setAccessible(true);
        return $method->invoke($job);
    }

    /**
     * Get fixable duplicate groups (non-withdrawn overpayments only)
     */
    public function getFixableDuplicates()
    {
        $service = new \App\Services\FixOverpaidCommissionsService();
        $fixable = $service->getFixableDuplicates();

        return response()->json([
            'count' => count($fixable),
            'items' => $fixable,
        ]);
    }

    /**
     * Get commission timeline for a specific expert position
     */
    public function getCommissionTimeline(Request $request)
    {
        $expertPositionId = $request->input('expert_position_id');
        $userId = $request->input('user_id');

        if (!$expertPositionId || !$userId) {
            return response()->json([
                'error' => 'Missing expert_position_id or user_id',
            ], 400);
        }

        $service = new \App\Services\FixOverpaidCommissionsService();
        $timeline = $service->getCommissionTimeline($expertPositionId, $userId);

        return response()->json($timeline);
    }

    /**
     * Fix overpaid commissions for a specific expert position and user
     * Soft-deletes non-withdrawn overpaid entries and flags them
     */
    public function fixOverpaidCommissions(Request $request)
    {
        $expertPositionId = $request->input('expert_position_id');
        $userId = $request->input('user_id');

        if (!$expertPositionId || !$userId) {
            return response()->json([
                'error' => 'Missing expert_position_id or user_id',
            ], 400);
        }

        $service = new \App\Services\FixOverpaidCommissionsService();
        $result = $service->fixForDuplicateGroup($expertPositionId, $userId);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Get all fixable duplicate entries with their details
     */
    public function getFixableEntries()
    {
        $entries = DB::table('ib1_commission as c')
            ->join('ib_wallet as w', 'w.ib1_commission_id', '=', 'c.id')
            ->select(
                'c.expert_position_id',
                'w.user_id',
                DB::raw('COUNT(DISTINCT w.order_id) as order_count'),
                DB::raw('COUNT(CASE WHEN w.ib_withdraw IS NULL THEN 1 END) as recoverable_count'),
                DB::raw('SUM(CASE WHEN w.ib_withdraw IS NULL THEN CAST(w.ib_wallet AS DECIMAL(20,10)) ELSE 0 END) as recoverable_amount'),
                DB::raw('SUM(CASE WHEN w.ib_withdraw IS NOT NULL THEN CAST(w.ib_wallet AS DECIMAL(20,10)) ELSE 0 END) as withdrawn_amount'),
                DB::raw('COUNT(*) as total_wallet_entries')
            )
            ->whereNull('c.deleted_at')
            ->whereNotNull('c.expert_position_id')
            ->whereNull('w.deleted_at')
            ->groupBy('c.expert_position_id', 'w.user_id')
            ->havingRaw('COUNT(DISTINCT w.order_id) > 1')
            ->havingRaw('COUNT(CASE WHEN w.ib_withdraw IS NULL THEN 1 END) > 0')
            ->limit(100)
            ->get();

        return response()->json([
            'count' => count($entries),
            'entries' => $entries->map(fn($e) => [
                'expert_position_id' => $e->expert_position_id,
                'user_id' => $e->user_id,
                'order_count' => $e->order_count,
                'total_wallet_entries' => $e->total_wallet_entries,
                'recoverable_count' => $e->recoverable_count,
                'recoverable_amount' => round($e->recoverable_amount, 4),
                'withdrawn_amount' => round($e->withdrawn_amount, 4),
                'can_fix' => $e->recoverable_count > 0,
            ])->toArray(),
        ]);
    }
}
