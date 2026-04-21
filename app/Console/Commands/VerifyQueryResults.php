<?php

namespace App\Console\Commands;

use App\Models\Ib1;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyQueryResults extends Command
{
    protected $signature = 'verify:query-results {--ib-email= : Specific IB email to test}';
    protected $description = 'Verify that old (whereHas) and new (join) queries return identical results';

    public function handle(): int
    {
        $this->info("\n========================================");
        $this->info("QUERY VERIFICATION - BEFORE & AFTER");
        $this->info("========================================\n");

        // Get a sample IB for testing
        $query = Ib1::with(['planDetails', 'user'])
            ->where('status', 1)
            ->whereNotNull('ib_plan_details_id');

        // Use specified email or default to known good test email
        $ibEmail = $this->option('ib-email') ?? 'duonghieu20121996@gmail.com';
        $query->where('email', $ibEmail);

        $testIb = $query->first();

        if (!$testIb) {
            $this->error('❌ No active IB found for testing.');
            return 1;
        }

        $referral_code = $testIb->referral_code ?: $testIb->email;
        $this->line("Testing with IB: <info>$referral_code</info> (ID: {$testIb->id})");
        $this->line("User ID: {$testIb->user_id}\n");

        // ============================================
        // BEFORE: Using whereHas (OLD APPROACH)
        // ============================================
        $this->line("<fg=yellow>--------- OLD QUERY (whereHas) ---------</>");
        $this->line("Method: Eloquent whereHas with subquery");
        $this->line("File: app/Console/Commands/SyncAccountTrades.php (OLD)\n");

        $beforeStartTime = microtime(true);

        try {
            $oldQueryResults = \App\Models\Account::select('id', 'code', 'user_id', 'account_type_id', 'last_trade_at')
                ->where('demo', false)
                ->where('account_request_status', 1)
                ->whereHas(
                    'user',
                    fn($query) =>
                    $query->where(function ($q) use ($referral_code) {
                        for ($i = 1; $i <= 15; $i++) {
                            $q->orWhere("ib$i", $referral_code);
                        }
                    })->where('status', 1)
                )
                ->get()
                ->toArray();

            $beforeDuration = microtime(true) - $beforeStartTime;

            $this->line("<info>✅ Query executed successfully</info>");
            $this->line("⏱️  Execution time: <info>" . number_format($beforeDuration * 1000, 2) . "ms</info>");
            $this->line("📊 Results: <info>" . count($oldQueryResults) . "</info> accounts found\n");
        } catch (\Exception $e) {
            $this->error("❌ Query failed: " . $e->getMessage() . "\n");
            $beforeDuration = 0;
            $oldQueryResults = [];
            return 1;
        }

        // ============================================
        // AFTER: Using Direct Join (NEW APPROACH)
        // ============================================
        $this->line("<fg=yellow>--------- NEW QUERY (Direct Join) ---------</>");
        $this->line("Method: DB::table() with direct JOIN");
        $this->line("File: app/Console/Commands/SyncAccountTrades.php (NEW)\n");

        $afterStartTime = microtime(true);

        try {
            $newQueryResults = DB::table('accounts as a')
                ->join('aspnetusers as u', 'a.user_id', '=', 'u.id')
                ->select('a.id', 'a.code', 'a.user_id', 'a.account_type_id', 'a.last_trade_at')
                ->where('a.demo', false)
                ->where('a.account_request_status', 1)
                ->whereNull('a.deleted_at')  // Exclude soft-deleted accounts (SoftDeletes trait)
                ->where('u.status', 1)
                ->where(function ($q) use ($referral_code) {
                    for ($i = 1; $i <= 15; $i++) {
                        $q->orWhere("u.ib{$i}", $referral_code);
                    }
                })
                ->get()
                ->toArray();

            $afterDuration = microtime(true) - $afterStartTime;

            $this->line("<info>✅ Query executed successfully</info>");
            $this->line("⏱️  Execution time: <info>" . number_format($afterDuration * 1000, 2) . "ms</info>");
            $this->line("📊 Results: <info>" . count($newQueryResults) . "</info> accounts found\n");
        } catch (\Exception $e) {
            $this->error("❌ Query failed: " . $e->getMessage() . "\n");
            $afterDuration = 0;
            $newQueryResults = [];
            return 1;
        }

        // ============================================
        // COMPARISON & VERIFICATION
        // ============================================
        $this->line("========================================");
        $this->info("COMPARISON RESULTS");
        $this->line("========================================\n");

        // Convert results to comparable format
        $oldResultsNormalized = collect($oldQueryResults)
            ->map(fn($item) => is_object($item) ? (array)$item : $item)
            ->sortBy('id')
            ->values()
            ->toArray();

        $newResultsNormalized = collect($newQueryResults)
            ->map(fn($item) => is_object($item) ? (array)$item : $item)
            ->sortBy('id')
            ->values()
            ->toArray();

        // Verify counts match
        $countMatch = count($oldResultsNormalized) === count($newResultsNormalized);
        $this->line("1. Record Count Match: " . ($countMatch ? "<info>✅ YES</info>" : "<error>❌ NO</error>"));
        $this->line("   - OLD: " . count($oldResultsNormalized) . " records");
        $this->line("   - NEW: " . count($newResultsNormalized) . " records\n");

        // Verify content matches
        $contentMatch = true;
        $differences = [];

        if ($countMatch && count($oldResultsNormalized) > 0) {
            foreach ($oldResultsNormalized as $index => $oldRecord) {
                $newRecord = $newResultsNormalized[$index];

                $oldId = object_get($oldRecord, 'id', $oldRecord['id'] ?? null);
                $newId = object_get($newRecord, 'id', $newRecord['id'] ?? null);

                if ($oldId !== $newId) {
                    $contentMatch = false;
                    $differences[] = "Row $index: ID mismatch - OLD: $oldId, NEW: $newId";
                    continue;
                }

                foreach ($oldRecord as $field => $oldValue) {
                    if (!array_key_exists($field, $newRecord)) {
                        $contentMatch = false;
                        $differences[] = "Row $index (ID: $oldId): Field '$field' missing in NEW result";
                    } elseif ($oldValue != $newRecord[$field]) {
                        $contentMatch = false;
                        $oldValueStr = is_null($oldValue) ? 'null' : ((is_object($oldValue) ? json_encode($oldValue) : $oldValue));
                        $newValueStr = is_null($newRecord[$field]) ? 'null' : ((is_object($newRecord[$field]) ? json_encode($newRecord[$field]) : $newRecord[$field]));
                        $differences[] = "Row $index (ID: $oldId): Field '$field' mismatch - OLD: $oldValueStr, NEW: $newValueStr";
                    }
                }
            }
        }

        $this->line("2. Data Content Match: " . ($contentMatch ? "<info>✅ YES</info>" : "<error>❌ NO</error>"));
        if (!$contentMatch && !empty($differences)) {
            $this->line("   Differences found:");
            foreach (array_slice($differences, 0, 5) as $diff) {
                $this->line("   - " . $diff);
            }
            if (count($differences) > 5) {
                $this->line("   ... and " . (count($differences) - 5) . " more differences");
            }
        } else {
            $this->line("   All records have identical data");
        }
        $this->line("");

        // Performance comparison
        $this->line("3. Performance:");
        $this->line("   - OLD (whereHas): " . number_format($beforeDuration * 1000, 2) . "ms");
        $this->line("   - NEW (join):     " . number_format($afterDuration * 1000, 2) . "ms");
        if ($beforeDuration > 0) {
            $improvement = (($beforeDuration - $afterDuration) / $beforeDuration) * 100;
            $suffix = $improvement > 0 ? "faster ✅" : "slower ❌";
            $this->line("   - Improvement:    " . number_format(abs($improvement), 1) . "% $suffix");
        } else {
            $this->line("   - Improvement:    Could not calculate");
        }
        $this->line("");

        // Final verdict
        $this->line("========================================");
        $this->info("FINAL VERDICT");
        $this->line("========================================\n");

        if ($countMatch && $contentMatch) {
            $this->info("✅ BUSINESS LOGIC VERIFIED\n");
            $this->line("✓ Same number of records returned");
            $this->line("✓ Identical data in all records");
            $this->line("✓ Same field values");
            $this->line("✓ Query optimization is safe to deploy\n");
            $this->info("Safe to commit and deploy to production ✅");
            return 0;
        } else {
            $this->error("❌ ISSUES FOUND\n");
            $this->line("✗ Results differ between old and new queries");
            $this->line("✗ Business logic may have changed");
            $this->line("✗ Do NOT deploy without investigation\n");
            $this->error("Please review the differences above ❌");
            return 1;
        }
    }
}
