<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $groupIds = [];

        $group = DB::table('permission_groups')->where('name', 'Blog')->first();
        if (! $group) {
            $groupIds['Blog'] = Str::uuid();
            DB::table('permission_groups')->insert([
                'id' => $groupIds['Blog'],
                'name' => 'Blog',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $groupIds['Blog'] = $group->id;
        }

        $permissions = [
            ['name' => 'blog:viewAny', 'description' => 'View Blog Index', 'group' => 'Blog'],
            ['name' => 'blog:view', 'description' => 'View Blog Detail', 'group' => 'Blog'],
        ];

        $superAdminRole = DB::table('roles')->where('name', 'Super Admin')->first();

        foreach ($permissions as $perm) {
            $permission = DB::table('permissions')
                ->where('name', $perm['name'])
                ->first();

            if (! $permission) {
                $permId = Str::uuid();
                DB::table('permissions')->insert([
                    'id' => $permId,
                    'name' => $perm['name'],
                    'description' => $perm['description'],
                    'permission_group_id' => $groupIds[$perm['group']],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $permission = (object) ['id' => $permId];
            } else {
                DB::table('permissions')
                    ->where('name', $perm['name'])
                    ->update([
                        'description' => $perm['description'],
                        'permission_group_id' => $groupIds[$perm['group']],
                        'updated_at' => now(),
                    ]);
            }

            if ($superAdminRole) {
                $exists = DB::table('permission_role')
                    ->where('role_id', $superAdminRole->id)
                    ->where('permission_id', $permission->id)
                    ->exists();

                if (! $exists) {
                    DB::table('permission_role')->insert([
                        'role_id' => $superAdminRole->id,
                        'permission_id' => $permission->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permNames = [
            'menu:blog', 'blog:viewAny', 'blog:view',
            'blog:create', 'blog:update', 'blog:delete',
        ];

        DB::table('permissions')->whereIn('name', $permNames)->delete();
    }
};
