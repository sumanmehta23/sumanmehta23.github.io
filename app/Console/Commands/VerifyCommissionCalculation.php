<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ib1Commission;
use App\Models\IbWallet;
use App\Models\Symbol;
use App\Models\Ib1;
use App\Models\IbPlanDetails;

class VerifyCommissionCalculation extends Command
{
    protected $signature = 'commission:verify {order_id} {referral_code}';
    protected $description = 'Verify commission calculations for a specific order';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        $referralCode = $this->argument('referral_code');

        $this->info("========== COMMISSION VERIFICATION ==========\n");

        // Fetch commission
        $commission = Ib1Commission::where('order_id', $orderId)->first();
        if (!$commission) {
            $this->error("Commission not found for order: $orderId");
            return 1;
        }

        $commission->load('user', 'account');
        $user = $commission->user;
        $account = $commission->account;

        $this->line("Commission Data:");
        $this->line("  Order ID: " . $commission->order_id);
        $this->line("  Symbol: " . $commission->symbol);
        $this->line("  Volume: " . $commission->volume);
        $this->line("  User Email: " . $user->email);
        $this->line("  User ib1: " . $user->ib1);
        $this->line("  Account Type: " . $account->account_type_id);

        // Get symbol
        $symbol = Symbol::where('symbol', $commission->symbol)->first();
        $symbolPath = $symbol ? $symbol->path : 'NOT_FOUND';
        $this->line("  Symbol Path: " . $symbolPath);

        // Get IB plan
        $ibUser = Ib1::where('referral_code', $referralCode)->first();
        if (!$ibUser || !$ibUser->planDetails) {
            $this->error("IB user or plan details not found for: $referralCode");
            return 1;
        }

        $planDetails = $ibUser->planDetails;
        $applicablePlan = IbPlanDetails::where('ib_category_id', $planDetails->ib_category_id)
            ->where('account_type_id', $account->account_type_id)
            ->where('status', 1)
            ->first();

        if (!$applicablePlan) {
            $this->error("No applicable plan found");
            return 1;
        }

        $this->line("\n========== CALCULATION ==========\n");

        // Step 1: Get commission rate
        $commissionRate = (float)$applicablePlan->d1;
        $this->line("Commission Rate from Plan (d1): " . $commissionRate);

        // Step 2: Check Forex/Metals
        $isForexOrMetals = preg_match('/Forex|Metals/', $symbolPath) ? true : false;
        $this->line("Is Forex/Metals: " . ($isForexOrMetals ? 'YES' : 'NO'));

        if (!$isForexOrMetals) {
            $commissionRate = 0;
            $this->line("  → Commission set to 0 (not Forex/Metals)");
        }

        // Step 3: Check special referral codes
        $specialReferral3Codes = ['sensei', 'wealthytrades', 'fxalexg'];
        $specialReferralCodes = ['K08EjL', 'EzHMpw', 'dhMKco', '4uStWn', 'ZiVehO', 'ubFUp7', 'HGvsS1', 'JV4a0Q', 'hvzla', 'zOhX4z', 'jDZVem', 'g6ofHI', 'zzLXS5', 'jMKn9O', 'W0V2I5', 'MPE8QF', 'bNiFv5', 'viQJWM', 'B0AG0Q', '2uDAEC', 'n8veXm', 'MREUR', 'bonus', 'LoTDGy', 'r5rY60', 'l1ILDq', '0D7QTR', 'NfMdsB', '5I6KMP', 'BnqfyN', 'aAWtvV', 'n19Nvf', 'NMdvcb', 'hlS4W0', 'Chinner', 'zym6oK', 'xh8Ule', 'FmL7M0', 'IvkCZH', 'o7Bzs5', 'fpate08', 'EIz0Oy', 'jbz0sX', 'xJpgdd', 'yWFOZc', 'tLnCex', 'jKRjpD','P1OvW1', 'waCJXU', 'Veedmj', 'RHF2N0', 'dV2STG', 'FzomIK', 'yaUWBg', 'mV7z7o', 'hAvjby', '7WhWdD', 'kRDJN3', 'sWNb7n'];

        if (in_array($referralCode, $specialReferral3Codes)) {
            $this->line("  → Special 3x rate applied");
            $commissionRate = 3;
        } else if (in_array($referralCode, $specialReferralCodes)) {
            $this->line("  → Special 6x rate applied");
            $commissionRate = 6;
        }

        if ($referralCode === 'W0V2I5') {
            $this->line("  → Special W0V2I5 8x rate applied");
            $commissionRate = 8;
        }

        // Step 4: Calculate wallet
        $ibWalletAmount = ((float)$commissionRate) * $commission->volume;
        $formattedWallet = number_format($ibWalletAmount, 10, '.', '');
        if ($formattedWallet < 0.0000001) {
            $formattedWallet = '0.0000000000';
        }

        $this->line("\nCalculation: " . $commissionRate . " × " . $commission->volume . " = " . $ibWalletAmount);
        $this->line("Formatted: " . $formattedWallet);

        // Verify against database
        $this->line("\n========== VERIFICATION ==========\n");

        $actualWallet = IbWallet::where('order_id', $orderId)
            ->where('email', $referralCode)
            ->first();

        if ($actualWallet) {
            $this->line("Expected Wallet: " . $formattedWallet);
            $this->line("Actual Wallet:   " . $actualWallet->ib_wallet);

            $calculated = (float)$formattedWallet;
            $actual = (float)$actualWallet->ib_wallet;

            if (abs($calculated - $actual) < 0.0000001) {
                $this->info("✅ MATCH: Calculations are IDENTICAL");
                $this->line("   Difference: " . abs($calculated - $actual));
            } else {
                $this->error("❌ MISMATCH: Calculations do NOT match");
                $this->line("   Difference: " . abs($calculated - $actual));
            }
        } else {
            $this->warn("⚠️  No wallet record found in database");
        }

        // Check for duplicates
        $duplicateCount = IbWallet::where('order_id', $orderId)
            ->where('email', $referralCode)
            ->count();

        $this->line("\nWallet Records: " . $duplicateCount);
        if ($duplicateCount > 1) {
            $this->error("❌ WARNING: Multiple wallet records found (duplicates detected!)");
        } else if ($duplicateCount === 1) {
            $this->info("✅ No duplicates");
        } else {
            $this->warn("⚠️  No wallet records");
        }

        // Check commission status
        $statusText = match ($commission->status) {
            1 => 'Processed ✅',
            10 => 'Discarded',
            default => 'Unprocessed ⚠️',
        };
        $this->line("\nCommission Status: " . $commission->status . " (" . $statusText . ")");

        $this->line("\n========== END VERIFICATION ==========\n");

        return 0;
    }
}
