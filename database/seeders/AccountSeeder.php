<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\Mt5Group;
use App\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = json_decode(File::get(database_path('seeders/data/accounts.json')), true);
        foreach ($accounts as $account) {
            $user=User::where('email',$account['email'])->first();
            $account['user_id']=$user->id;
            $accountTypeId=AccountType::first()->value('id');
            $account['account_type_id']=$accountTypeId;
            Account::create($account);
        }
    }
}
