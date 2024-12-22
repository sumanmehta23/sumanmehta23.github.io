<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\EmployeeList;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = json_decode(File::get(database_path('seeders/data/emplist.json')), true);
        foreach ($employees as $employee) {
            $role=Role::where('role_id', $employee['role_id'])->first();
            EmployeeList::create([
                'client_index' => 1,
                'role_id' => $role->id,
                'username' => $employee['username'],
                'email' =>  $employee['email'],
                'userRole'=>$role->name,
                'gender' => $employee['gender'],
                'status' => $employee['status'],
                'dob' => null,
                'password' => $employee['password'],
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
}
