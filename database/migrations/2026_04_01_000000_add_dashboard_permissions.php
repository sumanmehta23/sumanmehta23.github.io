<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $groupId = DB::table('permission_groups')->where('name', 'Dashboard')->value('id');

        $permissions = [
            ['name' => 'dashboard:viewTotalDeposit', 'description' => 'View Total Deposit Card'],
            ['name' => 'dashboard:viewTotalWithdraw', 'description' => 'View Total Withdraw Card'],
            ['name' => 'dashboard:viewActiveClients', 'description' => 'View Active Clients Card'],
            ['name' => 'dashboard:viewPendingDeposits', 'description' => 'View Pending Deposits Card'],
            ['name' => 'dashboard:viewPendingWithdraws', 'description' => 'View Pending Withdrawals Card'],
            ['name' => 'dashboard:viewPendingIB', 'description' => 'View Pending IB Requests Card'],
            ['name' => 'dashboard:viewActivatedWallets', 'description' => 'View Activated Wallets Card'],
            ['name' => 'dashboard:viewLatestPendingDeposit', 'description' => 'View Latest Pending Deposit Table'],
            ['name' => 'dashboard:viewLatestPendingWithdrawals', 'description' => 'View Latest Pending Withdrawals Table'],
            ['name' => 'dashboard:viewLatestPendingTradeWithdrawals', 'description' => 'View Latest Pending Trade Withdrawals Table'],
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
                'dashboard:viewTotalDeposit',
                'dashboard:viewTotalWithdraw',
                'dashboard:viewActiveClients',
                'dashboard:viewPendingDeposits',
                'dashboard:viewPendingWithdraws',
                'dashboard:viewPendingIB',
                'dashboard:viewActivatedWallets',
                'dashboard:viewLatestPendingDeposit',
                'dashboard:viewLatestPendingWithdrawals',
                'dashboard:viewLatestPendingTradeWithdrawals',
            ])
            ->delete();
    }
};
