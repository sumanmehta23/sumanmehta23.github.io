<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ib1Commission;
use App\Models\IbWallet;
use App\Models\Symbol;
use App\Models\Ib1;
use App\Models\IbPlanDetails;
use App\Models\Account;

class TestMultiLevelLogic extends Command
{
    protected $signature = 'commission:test-multilevel {order_id}';
    protected $description = 'Test multi-level commission distribution logic';

    public function handle()
    {
        $orderId = $this->argument('order_id');

        $this->info("========== MULTI-LEVEL DISTRIBUTION LOGIC TEST ==========\n");

        // Fetch commission
        $commission = Ib1Commission::where('order_id', $orderId)->first();
        if (!$commission) {
            $this->error("Commission not found");
            return 1;
        }

        $commission->load('user', 'account');
        $user = $commission->user;
        $account = $commission->account;

        $this->line("📦 Commission: Order {$commission->order_id} | {$commission->symbol} × {$commission->volume}");
        $this->line("👤 Trader: {$user->email}");
        $this->line("📊 Account Type: {$account->account_type_id}");

        // Collect all IB levels
        $levelTrades = [];
        for ($i = 1; $i <= 15; $i++) {
            $refCode = $user->{"ib$i"};
            if ($refCode) {
                if (!isset($levelTrades[$i])) {
                    $levelTrades[$i] = [];
                }
                $levelTrades[$i][] = $commission;
            }
        }

        if (empty($levelTrades)) {
            $this->error("No IB referrals found");
            return 1;
        }

        $this->line("\n🎯 IB Levels Found:");
        foreach (array_keys($levelTrades) as $level) {
            $this->line("  Level $level: {$user->{"ib$level"}}");
        }

        // ============================================
        // SIMULATE: Process each level
        // ============================================

        $this->line("\n========== SIMULATING DISTRIBUTION ==========\n");

        $symbolMappings = [];
        $symbol = Symbol::where('symbol', $commission->symbol)->first();
        $symbolPath = $symbol ? $symbol->path : 'default/path';
        $symbolMappings[$commission->symbol] = $symbolPath;
        $isForexOrMetals = preg_match('/Forex|Metals/', $symbolPath);

        $this->line("Symbol: {$commission->symbol}");
        $this->line("Path: {$symbolPath}");
        $this->line("Type: " . ($isForexOrMetals ? 'Forex/Metals ✅' : 'Other'));

        foreach ($levelTrades as $level => $trades) {
            $this->line("\n--- Processing Level $level ---");

            $referralCode = $user->{"ib$level"};
            $this->line("Referral Code: {$referralCode}");

            // Get IB user
            $ibUser = Ib1::where('referral_code', $referralCode)->first();
            if (!$ibUser) {
                $this->line("❌ IB user not found");
                continue;
            }

            if (!$ibUser->planDetails) {
                $this->line("❌ Plan details not found");
                continue;
            }

            $planDetails = $ibUser->planDetails;
            $this->line("Plan Category: {$planDetails->ib_category_id}");

            // Find plan for this account type
            $applicablePlan = IbPlanDetails::where('ib_category_id', $planDetails->ib_category_id)
                ->where('account_type_id', $account->account_type_id)
                ->where('status', 1)
                ->first();

            if (!$applicablePlan) {
                $this->line("⚠️  No plan for account type {$account->account_type_id}");

                // Try to show what plans DO exist
                $existingPlans = IbPlanDetails::where('ib_category_id', $planDetails->ib_category_id)
                    ->where('status', 1)
                    ->get();

                if ($existingPlans->count() > 0) {
                    $this->line("   Available plans for {$referralCode}:");
                    foreach ($existingPlans as $plan) {
                        $this->line("     - Account Type: {$plan->account_type_id}");
                    }
                }
                continue;
            }

            $depthKey = "d$level";
            $commissionRate = (float)$applicablePlan->{$depthKey};
            $this->line("Commission Rate ({$depthKey}): {$commissionRate}");

            if (!$isForexOrMetals && $commissionRate > 0) {
                $this->line("⚠️  Non-Forex/Metals → Commission = 0");
                $commissionRate = 0;
            }

            $ibWalletAmount = ((float)$commissionRate) * $commission->volume;
            $formattedWallet = number_format($ibWalletAmount, 10, '.', '');

            $this->line("✅ Would Create Wallet: {$referralCode}");
            $this->line("   Amount: {$formattedWallet}");
            $this->line("   Level: IB Level $level - D$level");

            // Check if wallet already exists
            $existing = IbWallet::where('order_id', $orderId)
                ->where('email', $referralCode)
                ->first();

            if ($existing) {
                if (abs((float)$existing->ib_wallet - $ibWalletAmount) < 0.0000001) {
                    $this->line("   Status: Already in DB ✅ {$existing->ib_wallet}");
                } else {
                    $this->line("   Status: In DB but MISMATCH ❌");
                    $this->line("   DB: {$existing->ib_wallet}");
                }
            } else {
                $this->line("   Status: NOT in DB - Would need to be created");
            }
        }

        $this->line("\n========== END TEST ==========\n");

        return 0;
    }
}
