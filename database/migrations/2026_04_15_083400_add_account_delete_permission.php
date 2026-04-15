<?php

use Glhd\Bits\Snowflake;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Get the permission group ID for 'Account'
        $groupId = DB::table('permission_groups')->where('name', 'Account')->first()?->id;

        // Check if the permission already exists before inserting
        $existingPermission = DB::table('permissions')
            ->where('name', 'account:delete')
            ->first();

        if (!$existingPermission) {
            $permissionId = Str::uuid()->toString();
            DB::table('permissions')->insert([
                'id'                  => $permissionId,
                'name'                => 'account:delete',
                'description'         => 'delete',
                'permission_group_id' => $groupId,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('permissions')->where('name', 'account:delete')->delete();
    }
};
