<?php

namespace Database\Seeders;

use App\Models\AccountType;
use App\Models\Leverage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LeverageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Read the JSON file from the 'seeders/data' directory
        $leverageData = json_decode(File::get(database_path('seeders/data/leverage.json')), true);

         // Insert each record into the 'leverage' table
         foreach ($leverageData as $leverage) {
            Leverage::create([
                'account_type_id' => AccountType::where('ac_index',$leverage['account_type_id'])->value('id'),
                'account_leverage' => $leverage['account_leverage'],
                'created_at' => $leverage['created_at'],
                'updated_at' => $leverage['updated_at'],
            ]);
        }
    }
}
