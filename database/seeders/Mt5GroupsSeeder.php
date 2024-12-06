<?php

namespace Database\Seeders;

use App\Models\Mt5Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class Mt5GroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Read the JSON file from the 'seeders/data' directory
        $mt5Groups = json_decode(File::get(database_path('seeders/data/mt5_groups.json')), true);

            // Loop through each mt5 group and insert it into the database
        foreach ($mt5Groups as $group) {
            Mt5Group::create([
                'mt5_group_id' => $group['mt5_group_id'],
                'mt5_group_name' => $group['mt5_group_name'],
                'mt5_group_type' => $group['mt5_group_type'],
                'mt5_group_desc' => $group['mt5_group_desc'],
                'is_active' => $group['is_active'],
                'updated_by' => $group['updated_by'],
                'created_at' => $group['created_at'],
                'updated_at' => $group['updated_at'],
            ]);
        }
    }
}
