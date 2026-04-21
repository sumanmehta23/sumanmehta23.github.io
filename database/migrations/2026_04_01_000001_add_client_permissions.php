<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Get or create the Client permission group
        $groupId = DB::table('permission_groups')->where('name', 'Client')->value('id');

        if (!$groupId) {
            $groupId = Str::uuid()->toString();
            DB::table('permission_groups')->insert([
                'id'         => $groupId,
                'name'       => 'Client',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $permissions = [
            ['name' => 'client:viewBankDetails', 'description' => 'View Bank Details'],
            ['name' => 'client:viewClientDocuments', 'description' => 'View Client Documents'],
            ['name' => 'client:viewUploadDocuments', 'description' => 'View Upload Documents'],
        ];

        $superAdminRoleId = DB::table('roles')->where('name', 'Super Admin')->value('id');

        foreach ($permissions as $perm) {
            $existing = DB::table('permissions')->where('name', $perm['name'])->first();

            if (!$existing) {
                $permissionId = Str::uuid()->toString();
                DB::table('permissions')->insert([
                    'id'                  => $permissionId,
                    'name'                => $perm['name'],
                    'description'         => $perm['description'],
                    'permission_group_id' => $groupId,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            } else {
                $permissionId = $existing->id;
            }

            if ($superAdminRoleId) {
                $rolePermissionExists = DB::table('permission_role')
                    ->where('role_id', $superAdminRoleId)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (!$rolePermissionExists) {
                    DB::table('permission_role')->insert([
                        'role_id'       => $superAdminRoleId,
                        'permission_id' => $permissionId,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('name', [
                'client:viewBankDetails',
                'client:viewClientDocuments',
                'client:viewUploadDocuments',
            ])
            ->delete();

        DB::table('permission_groups')->where('name', 'Client')->delete();
    }
};
