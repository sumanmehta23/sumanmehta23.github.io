<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\PageSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\SuperAdminSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */

    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            SymbolSeeder::class,
            RoleSeeder::class,
            SuperAdminSeeder::class,
            PageSeeder::class,
            CountrySeeder::class,
            Mt5GroupCategoriesSeeder::class,
            Mt5GroupsSeeder::class,
            AccountTypesSeeder::class,
            LeverageSeeder::class,
            UserSeeder::class,
            AccountSeeder::class,
            
            // ClientWalletSeeder::class,
            // TradeWithdrawalsSeeder::class,
            // TotalBalanceSeeder::class,
            // WalletDepositSeeder::class,
          ]);
    }
}
