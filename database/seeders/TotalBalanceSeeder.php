<?php

namespace Database\Seeders;

use App\Models\TotalBalance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TotalBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $totalBalances = json_decode(File::get(database_path('seeders/data/total_balance.json')), true);
        foreach ($totalBalances as $totalBalance) {
            TotalBalance::create($totalBalance);
        }
    }
}
