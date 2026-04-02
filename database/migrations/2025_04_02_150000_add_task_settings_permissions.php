<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get or create permission groups
        $groupIds = [];
        
        $groups = ['Task' => 'Task', 'Setting' => 'Setting', 'Menu' => 'Menu'];
        foreach ($groups as $groupName) {
            $group = DB::table('permission_groups')->where('name', $groupName)->first();
            if (!$group) {
                $groupIds[$groupName] = Str::uuid();
                DB::table('permission_groups')->insert([
                    'id' => $groupIds[$groupName],
                    'name' => $groupName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $groupIds[$groupName] = $group->id;
            }
        }

        // Define all permissions
        $permissions = [
            // Tasks section menu visibility
            ['name' => 'menu:tasks', 'description' => 'Show Tasks Menu Section', 'group' => 'Menu'],
            
            // Individual task indexes
            ['name' => 'task:viewAny', 'description' => 'View Tasks Index', 'group' => 'Task'],
            ['name' => 'clientTask:viewAny', 'description' => 'View Client Tasks Index', 'group' => 'Task'],
            
            // Settings section menu visibility
            ['name' => 'menu:settings', 'description' => 'Show Settings Menu Section', 'group' => 'Menu'],
            
            // Settings permissions - Using Setting group (singular)
            ['name' => 'settings:sumsub', 'description' => 'Sumsub KYC Sync', 'group' => 'Setting'],
            ['name' => 'settings:updatePassword', 'description' => 'Update Password', 'group' => 'Setting'],
            ['name' => 'settings:uiSettings', 'description' => 'UI Settings', 'group' => 'Setting'],
            ['name' => 'settings:reviewPopup', 'description' => 'Review Popup Settings', 'group' => 'Setting'],
            ['name' => 'settings:logs', 'description' => 'View Logs', 'group' => 'Setting'],
            ['name' => 'settings:apiToken', 'description' => 'API Token', 'group' => 'Setting'],
            ['name' => 'settings:banIps', 'description' => 'Ban IP\'s', 'group' => 'Setting'],
            ['name' => 'settings:emailBroadcasting', 'description' => 'Email Broadcasting', 'group' => 'Setting'],
        ];

        // Insert or update permissions and assign to Super Admin
        $superAdminRole = DB::table('roles')->where('name', 'Super Admin')->first();
        
        foreach ($permissions as $perm) {
            // Check if permission exists
            $permission = DB::table('permissions')
                ->where('name', $perm['name'])
                ->first();

            if (!$permission) {
                // Permission doesn't exist, create it
                $permId = Str::uuid();
                DB::table('permissions')->insert([
                    'id' => $permId,
                    'name' => $perm['name'],
                    'description' => $perm['description'],
                    'permission_group_id' => $groupIds[$perm['group']],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $permission = (object)['id' => $permId];
            } else {
                // Permission exists, update description and group
                DB::table('permissions')
                    ->where('name', $perm['name'])
                    ->update([
                        'description' => $perm['description'],
                        'permission_group_id' => $groupIds[$perm['group']],
                        'updated_at' => now(),
                    ]);
            }

            // Assign to Super Admin if not already assigned
            if ($superAdminRole) {
                $exists = DB::table('permission_role')
                    ->where('role_id', $superAdminRole->id)
                    ->where('permission_id', $permission->id)
                    ->exists();

                if (!$exists) {
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
            // Tasks
            'menu:tasks', 'task:viewAny', 'clientTask:viewAny',
            // Settings
            'menu:settings', 'settings:sumsub', 'settings:updatePassword', 
            'settings:uiSettings', 'settings:reviewPopup', 'settings:logs', 
            'settings:apiToken', 'settings:banIps', 'settings:emailBroadcasting'
        ];
        
        DB::table('permissions')->whereIn('name', $permNames)->delete();
    }
};
