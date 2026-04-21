<?php

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the existing KPI Forex Tracker Permission Group
        $kpiGroup = PermissionGroup::where('name', 'KPI Forex Tracker')->first();

        if (! $kpiGroup) {
            // Create if doesn't exist
            $kpiGroup = PermissionGroup::create([
                'name' => 'KPI Forex Tracker',
            ]);
        }

        // Define new KPI API permissions
        $newPermissions = [
            [
                'name' => 'api:kpi:users:bonus-history:read',
                'description' => 'Read access to user bonus history endpoint',
            ],
            [
                'name' => 'api:kpi:users:competitions:read',
                'description' => 'Read access to user competition participation endpoint',
            ],
            [
                'name' => 'api:kpi:relationship-managers:read',
                'description' => 'Read access to relationship managers endpoint',
            ],
            [
                'name' => 'api:kpi:ibs:read',
                'description' => 'Read access to IBs/Affiliates endpoint',
            ],
        ];

        // Create permissions and collect their IDs
        $permissionIds = [];
        foreach ($newPermissions as $perm) {
            $permission = Permission::firstOrCreate(
                ['name' => $perm['name']],
                [
                    'description' => $perm['description'],
                    'permission_group_id' => $kpiGroup->id,
                ]
            );
            $permissionIds[] = $permission->id;
        }

        // Get the KPI Forex Tracker Reader role and assign new permissions
        $kpiRole = Role::where('name', 'KPI Forex Tracker Reader')->first();
        if ($kpiRole) {
            $kpiRole->permissions()->syncWithoutDetaching($permissionIds);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the new KPI permissions
        Permission::whereIn('name', [
            'api:kpi:users:bonus-history:read',
            'api:kpi:users:competitions:read',
            'api:kpi:relationship-managers:read',
            'api:kpi:ibs:read',
        ])->delete();
    }
};
