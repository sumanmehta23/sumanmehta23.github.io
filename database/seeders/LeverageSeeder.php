<?php

namespace Database\Seeders;

use App\Models\Leverage;
use App\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
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
            try {
                Leverage::create([
                    'account_type_id' => AccountType::where('ac_index',$leverage['account_type_id'])->value('id'),
                    'account_leverage' => $leverage['account_leverage'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $th) {
                Log::error($th->getMessage(). json_encode($leverage));
                

            }
           
        }
    }
}
