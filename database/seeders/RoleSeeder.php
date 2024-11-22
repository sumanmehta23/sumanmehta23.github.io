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
                'name' => 'Super Admin',
                'description' => 'Super Admin Full Access',
                'is_active' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Relationship Manager',
                'description' => 'Manager Access',
                'is_active' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Admin',
                'description' => 'Admin Access',
                'is_active' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Finance',
                'description' => 'Finance Team Access',
                'is_active' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Sales',
                'description' => 'Sales Team Access',
                'is_active' => 1,
                'created_by' => 1,
            ],
            [
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
