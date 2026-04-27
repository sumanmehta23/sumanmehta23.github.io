<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Create or get the MT5 Accounts permission group
        $group = DB::table('permission_groups')->where('name', 'MT5 Accounts')->first();

        if (!$group) {
            $groupId = Str::uuid()->toString();
            DB::table('permission_groups')->insert([
                'id'         => $groupId,
                'name'       => 'MT5 Accounts',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $groupId = $group->id;
        }

        // Define permissions for managing not found MT5 accounts
        $permissions = [
            [
                'name'        => 'accounts:view_not_found',
                'description' => 'View accounts not found in MT5',
            ],
            [
                'name'        => 'accounts:verify_not_found',
                'description' => 'Verify and re-check accounts in MT5',
            ],
            [
                'name'        => 'accounts:bulk_archive',
                'description' => 'Bulk archive not found accounts',
            ],
        ];

        // Insert permissions and assign to Super Admin
        $superAdminRole = DB::table('roles')->where('name', 'Super Admin')->first();

        foreach ($permissions as $permission) {
            // Check if permission already exists
            $existingPermission = DB::table('permissions')
                ->where('name', $permission['name'])
                ->first();

            if (!$existingPermission) {
                $permissionId = Str::uuid()->toString();
                DB::table('permissions')->insert([
                    'id'                  => $permissionId,
                    'name'                => $permission['name'],
                    'description'         => $permission['description'],
                    'permission_group_id' => $groupId,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                // Assign to Super Admin
                if ($superAdminRole) {
                    $existing = DB::table('permission_role')
                        ->where('role_id', $superAdminRole->id)
                        ->where('permission_id', $permissionId)
                        ->first();

                    if (!$existing) {
                        DB::table('permission_role')->insert([
                            'role_id'       => $superAdminRole->id,
                            'permission_id' => $permissionId,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Remove permissions
        DB::table('permissions')
            ->whereIn('name', [
                'accounts:view_not_found',
                'accounts:verify_not_found',
                'accounts:bulk_archive',
            ])
            ->delete();

        // Optionally remove the permission group if it has no other permissions
        $group = DB::table('permission_groups')->where('name', 'MT5 Accounts')->first();
        if ($group) {
            $permissionCount = DB::table('permissions')
                ->where('permission_group_id', $group->id)
                ->count();

            if ($permissionCount === 0) {
                DB::table('permission_groups')->where('id', $group->id)->delete();
            }
        }
    }
};
