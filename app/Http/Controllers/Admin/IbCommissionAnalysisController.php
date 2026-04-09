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
        return view('admin.ib.commission-analysis');
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
            // Clean up cache after delivering results
            Cache::forget($cacheKey . ':status');
            Cache::forget($cacheKey . ':progress');
            Cache::forget($cacheKey . ':result');
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
}
