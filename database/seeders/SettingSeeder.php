<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = json_decode(file_get_contents(__DIR__.'/data/settings.json'), true);
        foreach ($settings as $setting) {
            Setting::create($setting);
        }
        
    }
}
