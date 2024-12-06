<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = json_decode(File::get(database_path('seeders/data/countries.json')), true);

        foreach ($countries as $country) {
            Country::create([
                'country_id' => $country['country_id'],
                'country_name' => $country['country_name'],
                'country_code' => $country['country_code'],
                'country_alpha' => $country['country_alpha'],
            ]);
        }
    }
}
