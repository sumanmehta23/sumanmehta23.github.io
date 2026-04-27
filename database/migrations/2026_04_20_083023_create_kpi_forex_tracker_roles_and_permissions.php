<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PermissionGroup;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create KPI Forex Tracker Permission Group if it doesn't exist
        $kpiGroup = PermissionGroup::firstOrCreate(
            ['name' => 'KPI Forex Tracker']
        );

        // Define KPI Forex Tracker API permissions (Read-only)
        $permissions = [
            [
                'name' => 'api:kpi:users:read',
                'description' => 'Read access to KPI users endpoint',
            ],
            [
                'name' => 'api:kpi:withdrawals:read',
                'description' => 'Read access to KPI withdrawals endpoint',
            ],
            [
                'name' => 'api:kpi:deposits:read',
                'description' => 'Read access to KPI deposits endpoint',
            ],
            [
                'name' => 'api:kpi:accounts:read',
                'description' => 'Read access to KPI accounts endpoint',
            ],
            [
                'name' => 'api:kpi:trades:read',
                'description' => 'Read access to KPI trades endpoint',
            ],
            [
                'name' => 'api:kpi:statistics:read',
                'description' => 'Read access to KPI statistics endpoint',
            ],
            [
                'name' => 'api:kpi:reports:read',
                'description' => 'Read access to KPI reports endpoint',
            ],

        ];

        // Create permissions
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                [
                    'description' => $perm['description'],
                    'permission_group_id' => $kpiGroup->id
                ]
            );
        }

        // Create KPI Forex Tracker Reader Role if it doesn't exist
        $kpiRole = Role::firstOrCreate(
            ['name' => 'KPI Forex Tracker Reader'],
            ['description' => 'Read-only access to KPI Forex Tracker API endpoints']
        );

        // Assign all KPI permissions to the role
        $kpiPermissions = Permission::where('permission_group_id', $kpiGroup->id)
            ->get()
            ->pluck('id');

        $kpiRole->permissions()->syncWithoutDetaching($kpiPermissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete KPI permissions
        $kpiGroup = PermissionGroup::where('name', 'KPI Forex Tracker')->first();

        if ($kpiGroup) {
            Permission::where('permission_group_id', $kpiGroup->id)->delete();
            $kpiGroup->delete();
        }

        // Delete KPI roles
        Role::where('name', 'KPI Forex Tracker Reader')->delete();
    }
};
