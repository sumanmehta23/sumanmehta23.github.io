<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\EmployeeList;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $role=Role::where('name', 'Super Admin')->first();
        EmployeeList::create([
            'client_index' => 1,
            'role_id' => $role->id,
            'username' => 'lqhmarket',
            'email' => 'admin@lqhmarkets.com',
            'userRole'=>$role->name,
            'gender' => '0',
            'status' => '1',
            'dob' => null,
            'password' => Hash::make('lqhmarket'),
            'number' => null,
            'address' => null,
            'website' => null,
            'uid' => (string) \Illuminate\Support\Str::uuid(),
            'company_name' => null,
            'company_address' => null,
            'company_number' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
