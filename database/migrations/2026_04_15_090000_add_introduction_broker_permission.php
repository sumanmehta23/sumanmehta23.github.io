<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $groupId = DB::table('permission_groups')->where('name', 'Client')->value('id');

        if (! $groupId) {
            $groupId = Str::uuid()->toString();
            DB::table('permission_groups')->insert([
                'id' => $groupId,
                'name' => 'Client',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $permissions = [
            ['name' => 'client:introducingBrokerButton', 'description' => 'View the IB request text and button in client detail page'],
        ];

        $permissionIds = [];

        foreach ($permissions as $perm) {
            $existing = DB::table('permissions')->where('name', $perm['name'])->first();

            if ($existing) {
                $permissionIds[$perm['name']] = $existing->id;
            } else {
                $id = Str::uuid()->toString();
                DB::table('permissions')->insert([
                    'id' => $id,
                    'name' => $perm['name'],
                    'description' => $perm['description'],
                    'permission_group_id' => $groupId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $permissionIds[$perm['name']] = $id;
            }
        }

        $superAdminRole = DB::table('roles')->where('name', 'Super Admin')->first();

        if ($superAdminRole) {
            foreach ($permissionIds as $permissionId) {
                $alreadyLinked = DB::table('permission_role')
                    ->where('role_id', $superAdminRole->id)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (! $alreadyLinked) {
                    DB::table('permission_role')->insert([
                        'permission_id' => $permissionId,
                        'role_id' => $superAdminRole->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $permissionNames = [
            'client:introducingBrokerButton',
        ];

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $permissionNames)
            ->pluck('id');

        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('name', $permissionNames)->delete();
    }
};
