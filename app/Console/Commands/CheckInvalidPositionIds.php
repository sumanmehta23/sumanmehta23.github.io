<?php

namespace App\Console\Commands;

use App\Models\Trade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckInvalidPositionIds extends Command
{
    protected $signature = 'app:check-invalid-position-ids 
                            {--fix : Attempt to delete invalid entries (DANGEROUS)}
                            {--report : Generate detailed report}
                            {--monitor : Continuous monitoring mode}';

    protected $description = 'Check for trades with invalid position_id values (0, null, empty)';

    public function handle()
    {
        $this->info("🔍 Checking for trades with invalid position_id values...");
        $this->info("=" . str_repeat("=", 60));

        if ($this->option('monitor')) {
            $this->runMonitoringMode();
            return;
        }

        // Get statistics
        $stats = $this->getInvalidPositionIdStats();

        // Display summary
        $this->displaySummary($stats);

        if ($this->option('report')) {
            $this->generateDetailedReport($stats);
        }

        if ($this->option('fix')) {
            $this->handleFixMode($stats);
        }

        $this->info("\n✅ Check completed. Monitor logs for any future invalid position_id attempts.");
    }

    protected function runMonitoringMode()
    {
        $this->info("🔄 Starting continuous monitoring mode...");
        $this->info("Press Ctrl+C to stop monitoring.");

        $lastCount = 0;
        $checkInterval = 30; // seconds

        while (true) {
            $currentStats = $this->getInvalidPositionIdStats();
            $currentCount = $currentStats['total_invalid'];

            if ($currentCount > $lastCount) {
                $newInvalid = $currentCount - $lastCount;
                $this->warn("🚨 ALERT: {$newInvalid} new invalid position_id entries detected!");
                $this->displaySummary($currentStats);

                // Log critical alert
                Log::critical("MONITORING ALERT: {$newInvalid} new invalid position_id entries detected", [
                    'previous_count' => $lastCount,
                    'current_count' => $currentCount,
                    'new_entries' => $newInvalid,
                    'timestamp' => now(),
                    'stats' => $currentStats
                ]);

                // Send admin notification
                activity('trade_data_integrity')
                    ->withProperties([
                        'alert_type' => 'new_invalid_position_ids',
                        'new_entries' => $newInvalid,
                        'total_invalid' => $currentCount
                    ])
                    ->log("🚨 MONITORING: {$newInvalid} new invalid position_id entries detected");
            } elseif ($currentCount < $lastCount) {
                $fixed = $lastCount - $currentCount;
                $this->info("✅ {$fixed} invalid entries have been fixed/removed.");
            }

            $lastCount = $currentCount;
            sleep($checkInterval);
        }
    }

    protected function getInvalidPositionIdStats(): array
    {
        // Get detailed statistics about invalid position_id entries
        $nullCount = Trade::whereNull('position_id')->count();
        $emptyStringCount = Trade::where('position_id', '')->count();
        $zeroStringCount = Trade::where('position_id', '0')->count();
        $zeroIntCount = Trade::where('position_id', 0)->count();

        // Get recent entries (last 24 hours)
        $recentInvalid = Trade::where(function ($query) {
            $query->whereNull('position_id')
                ->orWhere('position_id', '')
                ->orWhere('position_id', '0')
                ->orWhere('position_id', 0);
        })
            ->where('created_at', '>=', now()->subDay())
            ->count();

        // Get account distribution
        $accountsAffected = Trade::where(function ($query) {
            $query->whereNull('position_id')
                ->orWhere('position_id', '')
                ->orWhere('position_id', '0')
                ->orWhere('position_id', 0);
        })
            ->distinct('account_id')
            ->count();

        // Get samples for investigation
        $sampleInvalid = Trade::where(function ($query) {
            $query->whereNull('position_id')
                ->orWhere('position_id', '')
                ->orWhere('position_id', '0')
                ->orWhere('position_id', 0);
        })
            ->with('account')
            ->limit(5)
            ->get();

        return [
            'null_count' => $nullCount,
            'empty_string_count' => $emptyStringCount,
            'zero_string_count' => $zeroStringCount,
            'zero_int_count' => $zeroIntCount,
            'total_invalid' => $nullCount + $emptyStringCount + $zeroStringCount + $zeroIntCount,
            'recent_invalid' => $recentInvalid,
            'accounts_affected' => $accountsAffected,
            'sample_entries' => $sampleInvalid,
            'total_trades' => Trade::count()
        ];
    }

    protected function displaySummary(array $stats)
    {
        $this->table(['Issue Type', 'Count'], [
            ['NULL position_id', $stats['null_count']],
            ['Empty string position_id', $stats['empty_string_count']],
            ['Zero string position_id ("0")', $stats['zero_string_count']],
            ['Zero integer position_id (0)', $stats['zero_int_count']],
            ['---', '---'],
            ['TOTAL INVALID', $stats['total_invalid']],
            ['Recent (24h)', $stats['recent_invalid']],
            ['Accounts Affected', $stats['accounts_affected']],
            ['Total Trades', $stats['total_trades']],
            ['Data Integrity', $stats['total_invalid'] == 0 ? '✅ GOOD' : '❌ COMPROMISED']
        ]);

        if ($stats['total_invalid'] > 0) {
            $percentage = round(($stats['total_invalid'] / $stats['total_trades']) * 100, 4);
            $this->warn("⚠️  Data integrity issue: {$stats['total_invalid']} invalid entries ({$percentage}% of total trades)");

            if ($stats['recent_invalid'] > 0) {
                $this->error("🚨 CRITICAL: {$stats['recent_invalid']} invalid entries created in the last 24 hours!");
            }
        } else {
            $this->info("✅ All trades have valid position_id values.");
        }
    }

    protected function generateDetailedReport(array $stats)
    {
        $this->info("\n📊 DETAILED REPORT");
        $this->info("=" . str_repeat("=", 50));

        if (!empty($stats['sample_entries'])) {
            $this->info("\n🔍 Sample Invalid Entries:");
            foreach ($stats['sample_entries'] as $trade) {
                $this->line("- Trade ID: {$trade->id}");
                $this->line("  Position ID: " . ($trade->position_id === null ? 'NULL' : "'{$trade->position_id}'"));
                $this->line("  Account: {$trade->account->code} (ID: {$trade->account_id})");
                $this->line("  Symbol: {$trade->symbol}");
                $this->line("  Order ID: {$trade->order_id}");
                $this->line("  Created: {$trade->created_at}");
                $this->line("  Status: {$trade->status}");
                $this->line("");
            }
        }

        // Generate recommendations
        $this->info("💡 RECOMMENDATIONS:");
        if ($stats['total_invalid'] > 0) {
            $this->warn("1. Investigate MT5 data source - why are zero/null position_ids being received?");
            $this->warn("2. Review sync job logs for the affected accounts");
            $this->warn("3. Check MT5 server configuration and API responses");
            $this->warn("4. Consider data cleanup after investigation");

            if ($stats['recent_invalid'] > 0) {
                $this->error("5. 🚨 URGENT: Recent invalid entries indicate ongoing issue - check sync jobs immediately!");
            }
        } else {
            $this->info("✅ No action needed - validation system is working correctly.");
        }
    }

    protected function handleFixMode(array $stats)
    {
        if ($stats['total_invalid'] == 0) {
            $this->info("✅ No invalid entries found - nothing to fix.");
            return;
        }

        $this->warn("\n⚠️  FIX MODE ACTIVATED");
        $this->warn("This will permanently DELETE {$stats['total_invalid']} trades with invalid position_id!");
        $this->warn("This action CANNOT be undone!");

        if (!$this->confirm('Are you absolutely sure you want to delete these invalid trades?')) {
            $this->info("❌ Fix operation cancelled.");
            return;
        }

        if (!$this->confirm('Have you backed up the database?')) {
            $this->error("❌ Please backup the database before proceeding with destructive operations.");
            return;
        }

        if (!$this->confirm('Type YES to confirm deletion', false)) {
            $this->info("❌ Fix operation cancelled.");
            return;
        }

        $this->info("🗑️  Deleting invalid trades...");

        $deleted = Trade::where(function ($query) {
            $query->whereNull('position_id')
                ->orWhere('position_id', '')
                ->orWhere('position_id', '0')
                ->orWhere('position_id', 0);
        })->delete();

        $this->info("✅ Deleted {$deleted} invalid trades.");

        // Log the cleanup operation
        Log::warning("ADMIN CLEANUP: Deleted {$deleted} trades with invalid position_id", [
            'admin_command' => true,
            'deleted_count' => $deleted,
            'stats_before' => $stats,
            'timestamp' => now()
        ]);

        activity('trade_data_integrity')
            ->withProperties([
                'action' => 'admin_cleanup',
                'deleted_count' => $deleted,
                'stats_before' => $stats
            ])
            ->log("Admin cleanup: Deleted {$deleted} trades with invalid position_id");
    }
}
