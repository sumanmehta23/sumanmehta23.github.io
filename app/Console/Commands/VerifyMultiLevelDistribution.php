<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ib1Commission;
use App\Models\IbWallet;
use App\Models\Symbol;
use App\Models\Ib1;
use App\Models\IbPlanDetails;
use Illuminate\Support\Facades\DB;

class VerifyMultiLevelDistribution extends Command
{
    protected $signature = 'commission:verify-multilevel {order_id}';
    protected $description = 'Verify multi-level commission distribution for an order with multiple IB levels';

    public function handle()
    {
        $orderId = $this->argument('order_id');

        $this->info("========== MULTI-LEVEL COMMISSION DISTRIBUTION TEST ==========\n");

        // Fetch commission
        $commission = Ib1Commission::where('order_id', $orderId)->first();
        if (!$commission) {
            $this->error("Commission not found for order: $orderId");
            return 1;
        }

        $commission->load('user', 'account');
        $user = $commission->user;
        $account = $commission->account;

        // Collect all IB referrals for this user
        $ibReferrals = [];
        for ($i = 1; $i <= 15; $i++) {
            $ibCode = $user->{"ib$i"};
            if ($ibCode) {
                $ibReferrals[$i] = $ibCode;
            }
        }

        $this->line("Commission Details:");
        $this->line("  Order ID: " . $commission->order_id);
        $this->line("  Trader Email: " . $user->email);
        $this->line("  Symbol: " . $commission->symbol);
        $this->line("  Volume: " . $commission->volume);
        $this->line("  Account Type: " . $account->account_type_id);

        $this->line("\nIB Referrals for this Trader:");
        foreach ($ibReferrals as $level => $code) {
            $this->line("  Level $level: $code");
        }

        if (empty($ibReferrals)) {
            $this->warn("No IB referrals found for this trader");
            return 1;
        }

        // Get symbol path
        $symbol = Symbol::where('symbol', $commission->symbol)->first();
        $symbolPath = $symbol ? $symbol->path : 'NOT_FOUND';
        $isForexOrMetals = preg_match('/Forex|Metals/', $symbolPath) ? true : false;

        $this->line("\nSymbol Details:");
        $this->line("  Path: " . $symbolPath);
        $this->line("  Forex/Metals: " . ($isForexOrMetals ? 'YES' : 'NO'));

        // ============================================
        // TEST: Calculate what should be distributed
        // ============================================

        $this->line("\n========== DISTRIBUTION CALCULATION ==========\n");

        $testResults = [];

        foreach ($ibReferrals as $level => $referralCode) {
            $this->line("Testing Level $level: $referralCode");

            // Get IB user and plan
            $ibUser = Ib1::where('referral_code', $referralCode)->first();
            if (!$ibUser) {
                $this->line("  ❌ IB user not found");
                continue;
            }

            if (!$ibUser->planDetails) {
                $this->line("  ❌ Plan details not found");
                continue;
            }

            $planDetails = $ibUser->planDetails;

            // Find applicable plan for this account type
            $applicablePlan = IbPlanDetails::where('ib_category_id', $planDetails->ib_category_id)
                ->where('account_type_id', $account->account_type_id)
                ->where('status', 1)
                ->first();

            if (!$applicablePlan) {
                $this->line("  ⚠️  No plan found for this account type");
                continue;
            }

            // Get commission rate for this level
            $depthKey = "d$level";
            $commissionRate = (float)$applicablePlan->{$depthKey} ?? 0;

            $this->line("  Commission Rate (d$level): " . $commissionRate);

            // Apply forex/metals check
            if (!$isForexOrMetals) {
                $commissionRate = 0;
                $this->line("  ⚠️  Not Forex/Metals - commission set to 0");
            }

            // Calculate wallet
            $ibWalletAmount = ((float)$commissionRate) * $commission->volume;
            $formattedWallet = number_format($ibWalletAmount, 10, '.', '');

            $this->line("  Calculation: " . $commissionRate . " × " . $commission->volume . " = " . $formattedWallet);

            $testResults[$level] = [
                'referral_code' => $referralCode,
                'rate' => $commissionRate,
                'wallet_amount' => $formattedWallet,
            ];
        }

        // ============================================
        // CHECK CURRENT STATE vs EXPECTED
        // ============================================

        $this->line("\n========== VERIFICATION ==========\n");

        $existingWallets = IbWallet::where('order_id', $orderId)->get();

        $this->line("Current Wallet Records: " . $existingWallets->count());
        if ($existingWallets->isNotEmpty()) {
            foreach ($existingWallets as $wallet) {
                $this->line("  - " . $wallet->email . " (" . $wallet->ib_level . "): " . $wallet->ib_wallet);
            }
        } else {
            $this->line("  (No wallets yet)");
        }

        $this->line("\nExpected Distribution:");
        $totalExpected = 0;
        foreach ($testResults as $level => $result) {
            $this->line("  Level $level - " . $result['referral_code'] . ": " . $result['wallet_amount']);
            $totalExpected += (float)$result['wallet_amount'];
        }
        $this->line("  Total Expected: " . number_format($totalExpected, 10, '.', ''));

        // Check if all expected wallets exist
        $this->line("\nComparison:");
        foreach ($testResults as $level => $result) {
            $existingWallet = $existingWallets->firstWhere('email', $result['referral_code']);
            if ($existingWallet) {
                $match = abs((float)$existingWallet->ib_wallet - (float)$result['wallet_amount']) < 0.0000001;
                $status = $match ? '✅' : '❌';
                $this->line("$status Level $level ({$result['referral_code']}): Expected {$result['wallet_amount']}, Got {$existingWallet->ib_wallet}");
            } else {
                $this->line("⚠️  Level $level ({$result['referral_code']}): NOT FOUND in database");
            }
        }

        // Check for unexpected wallets
        $expectedCodes = collect($testResults)->pluck('referral_code')->toArray();
        foreach ($existingWallets as $wallet) {
            if (!in_array($wallet->email, $expectedCodes)) {
                $this->line("⚠️  Unexpected wallet: {$wallet->email}");
            }
        }

        $this->line("\n========== END VERIFICATION ==========\n");

        return 0;
    }
}
