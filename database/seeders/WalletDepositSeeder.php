<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\WalletDeposit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WalletDepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $walletdeposits = json_decode(File::get(database_path('seeders/data/wallet_deposit.json')), true);
        foreach ($walletdeposits as $walletdeposit) {
            WalletDeposit::create($walletdeposit);
        }
    }
}
