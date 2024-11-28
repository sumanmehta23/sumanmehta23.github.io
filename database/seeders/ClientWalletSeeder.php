<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\ClientWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClientWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wallets = json_decode(File::get(database_path('seeders/data/client_wallets.json')), true);
        foreach ($wallets as $wallet) {
            ClientWallet::create($wallet);
        }
    }
}
