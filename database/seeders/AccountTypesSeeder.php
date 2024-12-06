<?php

namespace Database\Seeders;

use App\Models\Mt5Group;
use App\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AccountTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Read the JSON file from the 'seeders/data' directory
         $accountTypes = json_decode(File::get(database_path('seeders/data/account_types.json')), true);

         // Loop through the data and insert it into the account_types table
         foreach ($accountTypes as $accountType) {
             AccountType::create([
                 'ac_index' => $accountType['ac_index'],
                 'ac_category' => $accountType['ac_category'],
                 'ac_book_type' => $accountType['ac_book_type'],
                 'ac_name' => $accountType['ac_name'],
                 'ac_min_deposit' => $accountType['ac_min_deposit'],
                 'ac_max_deposit' => $accountType['ac_max_deposit'],
                 'ac_max_leverage' => $accountType['ac_max_leverage'],
                 'ac_lot_size' => $accountType['ac_lot_size'],
                 'ac_group' => $accountType['ac_group'],
                 'ac_spread' => $accountType['ac_spread'],
                 'mt5_group_id' => Mt5Group::where('mt5_group_id',$accountType['ac_type'])->value('id'),
                 'acc_ib_cat' => $accountType['acc_ib_cat'],
                 'ib_enabled' => $accountType['ib_enabled'],
                 'ac_swap' => $accountType['ac_swap'],
                 'is_client_group' => $accountType['is_client_group'],
                 'inquiry_status' => $accountType['inquiry_status'],
                 'status' => $accountType['status'],
                 'created_at' => $accountType['created_at'],
                 'updated_at' => $accountType['updated_at'],
                 'display_priority' => $accountType['display_priority'],
             ]);
         }
    }
}
