<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Database\Seeder;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TradeWithdrawalsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tradewithdrawals = json_decode(File::get(database_path('seeders/data/trade_withdrawals.json')), true);
        foreach ($tradewithdrawals as $tradewithdrawal) {
            TradeWithdrawals::create($tradewithdrawal);
        }
    }
}
