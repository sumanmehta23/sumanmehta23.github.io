<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\ClientWallet;
use Illuminate\Database\Seeder;

class UserFakeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate 100 users
        User::factory(100)->create()->each(function ($user) {
            // Create between 10–20 accounts per user
            $accounts = Account::factory(rand(10, 20))->make();
            $user->accounts()->saveMany($accounts);

            // Create between 2–5 wallets per user
            $wallets = ClientWallet::factory(rand(2, 5))->make();
            $user->wallets()->saveMany($wallets);
        });
    }
}