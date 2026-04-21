<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugQueryDifference extends Command
{
    protected $signature = 'debug:query-difference {--ib-email=}';
    protected $description = 'Find which accounts are different between old and new queries';

    public function handle(): int
    {
        $this->info("\n========================================");
        $this->info("DEBUG: Finding Query Differences");
        $this->info("========================================\n");

        // Get sample IB
        $query = \App\Models\Ib1::with(['planDetails', 'user'])
            ->where('status', 1)
            ->whereNotNull('ib_plan_details_id');

        // Use specified email or default to known good test email
        $ibEmail = $this->option('ib-email') ?? 'duonghieu20121996@gmail.com';
        $query->where('email', $ibEmail);

        $testIb = $query->first();

        if (!$testIb) {
            $this->error('❌ No active IB found');
            return 1;
        }

        $referral_code = $testIb->referral_code ?: $testIb->email;
        $this->line("IB: <info>$referral_code</info>\n");

        // ============================================
        // OLD QUERY (whereHas)
        // ============================================
        $this->line("Fetching OLD query results...");
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
            ->pluck('id')
            ->toArray();

        $this->line("✅ OLD: " . count($oldQueryResults) . " records\n");

        // ============================================
        // NEW QUERY (Direct Join)
        // ============================================
        $this->line("Fetching NEW query results...");
        $newQueryResults = DB::table('accounts as a')
            ->join('aspnetusers as u', 'a.user_id', '=', 'u.id')
            ->select('a.id', 'a.code', 'a.user_id', 'a.account_type_id', 'a.last_trade_at')
            ->where('a.demo', false)
            ->where('a.account_request_status', 1)
            ->whereNull('a.deleted_at')  // Exclude soft-deleted accounts
            ->where('u.status', 1)
            ->where(function ($q) use ($referral_code) {
                for ($i = 1; $i <= 15; $i++) {
                    $q->orWhere("u.ib{$i}", $referral_code);
                }
            })
            ->get()
            ->pluck('id')
            ->toArray();

        $this->line("✅ NEW: " . count($newQueryResults) . " records\n");

        // ============================================
        // Find differences
        // ============================================
        $this->line("========================================");
        $this->info("FINDING DIFFERENCES");
        $this->line("========================================\n");

        $onlyInOld = array_diff($oldQueryResults, $newQueryResults);
        $onlyInNew = array_diff($newQueryResults, $oldQueryResults);

        $this->line("Records in OLD but NOT in NEW: " . count($onlyInOld));
        if (!empty($onlyInNew)) {
            $this->line("Records in NEW but NOT in OLD: " . count($onlyInNew));
            $this->line("\nThese " . count($onlyInNew) . " extra accounts in NEW query:");

            $extraAccounts = \App\Models\Account::whereIn('id', $onlyInNew)
                ->select('id', 'code', 'user_id', 'demo', 'account_request_status', 'last_trade_at')
                ->get();

            foreach ($extraAccounts as $acc) {
                $this->line("  - ID: {$acc->id}, Code: {$acc->code}, User: {$acc->user_id}");
            }
        }
        $this->line("");

        // ============================================
        // Debug: Check if these extra accounts have proper user relationships
        // ============================================
        if (!empty($onlyInNew)) {
            $this->line("========================================");
            $this->info("DEBUGGING EXTRA RECORDS");
            $this->line("========================================\n");

            $this->line("Checking the user records for these extra accounts:\n");

            $extraAccounts = \App\Models\Account::whereIn('id', $onlyInNew)
                ->get();

            foreach ($extraAccounts as $acc) {
                $user = DB::table('aspnetusers')
                    ->where('id', $acc->user_id)
                    ->first();

                if ($user) {
                    $this->line("Account: {$acc->code}");
                    $this->line("  User Email: {$user->email}");
                    $this->line("  User Status: {$user->status}");

                    // Check which IB field matches
                    $ibMatch = false;
                    for ($i = 1; $i <= 15; $i++) {
                        $ibField = "ib{$i}";
                        if ($user->$ibField === $referral_code) {
                            $this->line("  ✅ Matches in field: {$ibField}");
                            $ibMatch = true;
                        }
                    }

                    if (!$ibMatch) {
                        $this->error("  ❌ NO MATCH in any ib1-ib15 fields!");
                    }
                    $this->line("");
                } else {
                    $this->error("Account {$acc->code}: NO USER FOUND (user_id: {$acc->user_id})");
                }
            }
        }

        if (empty($onlyInOld) && empty($onlyInNew)) {
            $this->info("\n✅ QUERIES MATCH! No differences found.\n");
            return 0;
        } else {
            $this->error("\n❌ QUERIES DIFFER\n");
            return 1;
        }
    }
}
