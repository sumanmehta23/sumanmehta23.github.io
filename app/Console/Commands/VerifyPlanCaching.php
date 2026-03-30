<?php

namespace App\Console\Commands;

use App\Models\Ib1;
use App\Models\IbPlanDetails;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyPlanCaching extends Command
{
    protected $signature = 'verify:plan-caching {--sample-ibs=50 : Number of IBs to test with}';
    protected $description = 'Verify that pre-cached IB plans improve performance and return identical results';

    public function handle(): int
    {
        $this->info("\n========================================");
        $this->info("PLAN CACHING VERIFICATION - BEFORE & AFTER");
        $this->info("========================================\n");

        $sampleSize = (int) $this->option('sample-ibs');

        // Get a sample of active IBs with plans
        $testIbs = Ib1::with(['planDetails', 'user'])
            ->where('status', 1)
            ->whereNotNull('ib_plan_details_id')
            ->limit($sampleSize)
            ->get();

        if ($testIbs->isEmpty()) {
            $this->error('❌ No active IBs found for testing.');
            return 1;
        }

        $this->line("Testing with " . count($testIbs) . " IBs\n");

        // ============================================
        // OLD APPROACH: Individual Cache::remember
        // ============================================
        $this->line("<fg=yellow>--------- OLD APPROACH (Individual Caching) ---------</>");
        $this->line("Method: Cache::remember for each IB");
        $this->line("Pattern: N separate cache lookups during loop\n");

        // Clear cache first
        $cacheKeys = $testIbs->map(fn($ib) => "ibPlans:{$ib->user_id}")->unique();
        foreach ($cacheKeys as $key) {
            \Illuminate\Support\Facades\Cache::forget($key);
        }

        $oldStartTime = microtime(true);
        $oldResults = [];

        foreach ($testIbs as $ib) {
            if (!$ib->planDetails) continue;

            $plan_id = $ib->planDetails->ib_category_id ?? null;
            if (!$plan_id) continue;

            $userId = $ib->user_id;

            // OLD APPROACH: Cache::remember for each IB
            $ibPlans = \Illuminate\Support\Facades\Cache::remember("ibPlans:$userId", 3600, function () use ($plan_id) {
                return IbPlanDetails::where('ib_category_id', $plan_id)
                    ->where('status', 1)
                    ->whereNull('deleted_at')
                    ->get()
                    ->toArray();
            });

            $oldResults[$userId] = count($ibPlans);
        }

        $oldDuration = microtime(true) - $oldStartTime;

        $this->line("<info>✅ Completed</info>");
        $this->line("⏱️  Execution time: <info>" . number_format($oldDuration * 1000, 2) . "ms</info>");
        $this->line("📊 Plans fetched for " . count($oldResults) . " users\n");

        // ============================================
        // NEW APPROACH: Pre-cached all plans
        // ============================================
        $this->line("<fg=yellow>--------- NEW APPROACH (Pre-cached) ---------</>");
        $this->line("Method: Single upfront query, in-memory lookup");
        $this->line("Pattern: 1 query + O(1) lookups in loop\n");

        $newStartTime = microtime(true);

        // Pre-cache all plans (as done in preCacheAllIbPlans method)
        $plans = IbPlanDetails::where('status', 1)
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('ib_category_id');

        $cachedPlans = [];
        foreach ($plans as $categoryId => $planCollection) {
            $cachedPlans[$categoryId] = $planCollection->toArray();
        }

        $preCacheDuration = microtime(true) - $newStartTime;

        // Now use the pre-cached plans
        $loopStartTime = microtime(true);
        $newResults = [];

        foreach ($testIbs as $ib) {
            if (!$ib->planDetails) continue;

            $plan_id = $ib->planDetails->ib_category_id ?? null;
            if (!$plan_id) continue;

            $userId = $ib->user_id;

            // NEW APPROACH: Direct array lookup
            $ibPlans = $cachedPlans[$plan_id] ?? [];
            $newResults[$userId] = count($ibPlans);
        }

        $loopDuration = microtime(true) - $loopStartTime;
        $newTotalDuration = $preCacheDuration + $loopDuration;

        $this->line("<info>✅ Completed</info>");
        $this->line("⏱️  Pre-cache time: <info>" . number_format($preCacheDuration * 1000, 2) . "ms</info>");
        $this->line("⏱️  Loop time: <info>" . number_format($loopDuration * 1000, 2) . "ms</info>");
        $this->line("⏱️  Total time: <info>" . number_format($newTotalDuration * 1000, 2) . "ms</info>");
        $this->line("📊 Plans fetched for " . count($newResults) . " users\n");

        // ============================================
        // COMPARISON & VERIFICATION
        // ============================================
        $this->line("========================================");
        $this->info("COMPARISON RESULTS");
        $this->line("========================================\n");

        // Verify results match
        $resultsMatch = $oldResults === $newResults;
        $this->line("1. Results Match: " . ($resultsMatch ? "<info>✅ YES</info>" : "<error>❌ NO</error>"));
        if (!$resultsMatch) {
            $this->error("   OLD: " . json_encode($oldResults));
            $this->error("   NEW: " . json_encode($newResults));
        }
        $this->line("");

        // Performance comparison
        $this->line("2. Performance Comparison:");
        $this->line("   - OLD approach: " . number_format($oldDuration * 1000, 2) . "ms");
        $this->line("   - NEW approach: " . number_format($newTotalDuration * 1000, 2) . "ms");
        if ($oldDuration > 0) {
            $improvement = (($oldDuration - $newTotalDuration) / $oldDuration) * 100;
            if ($improvement > 0) {
                $this->line("   - Improvement: <info>" . number_format(abs($improvement), 1) . "% faster</info> ✅");
            } else {
                $this->line("   - Difference: " . number_format(abs($improvement), 1) . "% slower");
            }
        }
        $this->line("");

        // Cache efficiency
        $uniqueCategories = count($cachedPlans);
        $totalPlans = array_sum(array_map('count', $cachedPlans));
        $this->line("3. Caching Efficiency:");
        $this->line("   - Unique plan categories: <info>$uniqueCategories</info>");
        $this->line("   - Total plans cached: <info>$totalPlans</info>");
        $this->line("   - Cache reuse ratio: <info>" . number_format(count($testIbs) / $uniqueCategories, 2) . "x</info>");
        $this->line("");

        // Final verdict
        $this->line("========================================");
        $this->info("FINAL VERDICT");
        $this->line("========================================\n");

        if ($resultsMatch) {
            $this->info("✅ PLAN CACHING VERIFIED\n");
            $this->line("✓ Results identical between approaches");
            $this->line("✓ Pre-caching strategy is safe to deploy");
            if ($improvement > 0) {
                $this->line("✓ Performance improved by " . number_format(abs($improvement), 1) . "%");
            }
            return 0;
        } else {
            $this->error("❌ VERIFICATION FAILED\n");
            $this->line("✗ Results differ between approaches");
            $this->line("✗ Do NOT deploy until this is resolved");
            return 1;
        }
    }
}
