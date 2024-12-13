<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $roles = [
            [
                'role_id' => 1,
                'name' => 'Super Admin',
                'description' => 'Super Admin Full Access',
                'is_active' => 1,
                'created_by' => 1,
            ],
            [
                'role_id' => 2,
                'name' => 'Relationship Manager',
                'description' => 'Manager Access',
                'is_active' => 1,
                'created_by' => 1,
            ],
            [
                'role_id' => 3,
                'name' => 'Admin',
                'description' => 'Admin Access',
                'is_active' => 1,
                'created_by' => 1,
            ],
            [
                'role_id' => 4,
                'name' => 'Finance',
                'description' => 'Finance Team Access',
                'is_active' => 1,
                'created_by' => 1,
            ],
            [
                'role_id' => 5,
                'name' => 'Sales',
                'description' => 'Sales Team Access',
                'is_active' => 1,
                'created_by' => 1,
            ],
            [
                'role_id' => 6,
                'name' => 'Knowers',
                'description' => 'Knowers',
                'is_active' => 1,
                'created_by' => 1,
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
