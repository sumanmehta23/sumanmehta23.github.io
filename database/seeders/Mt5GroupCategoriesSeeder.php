<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MT5GroupCategory;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class Mt5GroupCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Read the JSON file from the 'seeders/data' directory
        $mt5GroupCategories = json_decode(File::get(database_path('seeders/data/mt5_group_categories.json')), true);

        // Loop through each mt5 group and insert it into the database
        foreach ($mt5GroupCategories as $category) {
            MT5GroupCategory::create([
                'mt5_grp_cat_id' => $category['mt5_grp_cat_id'],
                'mt5_grp_cat_name' => $category['mt5_grp_cat_name'],
                'mt5_grp_cat_type' => $category['mt5_grp_cat_type'],
                'mt5_grp_cat_desc' => $category['mt5_grp_cat_desc'],
                'is_active' => $category['is_active'],
                'created_at' => $category['created_at'],
                'updated_at' => $category['updated_at'],
                'created_by' => $category['created_by'],
            ]);
        }
    }
}
