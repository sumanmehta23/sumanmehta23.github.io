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
        // Create permission groups for admin web UI resources
        $groups = [
            'Trades' => 'Admin access to trade management',
            'Users' => 'Admin access to user management',
            'Transactions' => 'Admin access to transaction management',
            'Withdrawals' => 'Admin access to withdrawal management',
            'Deposits' => 'Admin access to deposit management',
        ];
        $groupIds = [];
        foreach ($groups as $name => $description) {
            $existing = DB::table('permission_groups')
                ->where('name', $name)
                ->first();

            if ($existing) {
                $groupIds[$name] = $existing->id;
            } else {
                $groupId = Str::uuid()->toString();
                DB::table('permission_groups')->insert([
                    'id' => $groupId,
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $groupIds[$name] = $groupId;
            }
        }

        // Create web UI admin permissions for Trades
        $tradePermissions = [
            ['name' => 'trades:viewAny', 'description' => 'View all trades', 'permission_group_id' => $groupIds['Trades']],
            ['name' => 'trades:view', 'description' => 'View trade details', 'permission_group_id' => $groupIds['Trades']],
            ['name' => 'trades:export', 'description' => 'Export trades', 'permission_group_id' => $groupIds['Trades']],
            ['name' => 'trades:analyze', 'description' => 'Analyze trades', 'permission_group_id' => $groupIds['Trades']],
        ];

        // Create web UI admin permissions for Users
        $userPermissions = [
            ['name' => 'users:viewAny', 'description' => 'View all users', 'permission_group_id' => $groupIds['Users']],
            ['name' => 'users:view', 'description' => 'View user details', 'permission_group_id' => $groupIds['Users']],
            ['name' => 'users:export', 'description' => 'Export user data', 'permission_group_id' => $groupIds['Users']],
        ];

        // Create web UI admin permissions for Transactions
        $transactionPermissions = [
            ['name' => 'transactions:viewAny', 'description' => 'View all transactions', 'permission_group_id' => $groupIds['Transactions']],
            ['name' => 'transactions:view', 'description' => 'View transaction details', 'permission_group_id' => $groupIds['Transactions']],
            ['name' => 'transactions:export', 'description' => 'Export transactions', 'permission_group_id' => $groupIds['Transactions']],
            ['name' => 'transactions:reports', 'description' => 'View transaction reports', 'permission_group_id' => $groupIds['Transactions']],
        ];

        // Create web UI admin permissions for Withdrawals
        $withdrawalPermissions = [
            ['name' => 'withdrawals:viewAny', 'description' => 'View all withdrawals', 'permission_group_id' => $groupIds['Withdrawals']],
            ['name' => 'withdrawals:view', 'description' => 'View withdrawal details', 'permission_group_id' => $groupIds['Withdrawals']],
            ['name' => 'withdrawals:export', 'description' => 'Export withdrawals', 'permission_group_id' => $groupIds['Withdrawals']],
        ];

        // Create web UI admin permissions for Deposits
        $depositPermissions = [
            ['name' => 'deposits:viewAny', 'description' => 'View all deposits', 'permission_group_id' => $groupIds['Deposits']],
            ['name' => 'deposits:view', 'description' => 'View deposit details', 'permission_group_id' => $groupIds['Deposits']],
            ['name' => 'deposits:export', 'description' => 'Export deposits', 'permission_group_id' => $groupIds['Deposits']],
        ];
        // Collect all permissions
        $allPermissions = array_merge(
            $tradePermissions,
            $userPermissions,
            $transactionPermissions,
            $withdrawalPermissions,
            $depositPermissions
        );

        // Add timestamps to all permissions
        $permissions = array_map(function ($permission) {
            return array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $allPermissions);
        // Insert permissions, ignoring duplicates
        foreach ($permissions as $permission) {
            $permission['id'] = Str::uuid()->toString();
            DB::table('permissions')->insertOrIgnore($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete web UI admin permissions
        DB::table('permissions')->whereIn('name', [
            'trades:viewAny',
            'trades:view',
            'trades:export',
            'trades:analyze',
            'users:viewAny',
            'users:view',
            'users:export',
            'transactions:viewAny',
            'transactions:view',
            'transactions:export',
            'transactions:reports',
            'withdrawals:viewAny',
            'withdrawals:view',
            'withdrawals:export',
            'deposits:viewAny',
            'deposits:view',
            'deposits:export',
        ])->delete();
    }
};
