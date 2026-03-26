<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Get the permission group ID for 'TradeWithdrawals'
        $groupId = DB::table('permission_groups')->where('name', 'TradeWithdrawals')->first()?->id;

        // Check if the permission already exists before inserting
        $existingPermission = DB::table('permissions')
            ->where('name', 'trade_withdrawals:payout')
            ->first();

        if (!$existingPermission) {
            DB::table('permissions')->insert([
                'id'                  => Str::uuid()->toString(),
                'name'                => 'trade_withdrawals:payout',
                'description'         => 'Handle Trade Withdrawal Payouts (Edit, Approve, Reject, Manual Pay)',
                'permission_group_id' => $groupId,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            // Assign this permission to Super Admin role by default
            $superAdminRole = DB::table('roles')->where('name', 'Super Admin')->first();

            if ($superAdminRole) {
                $permissionId = DB::table('permissions')
                    ->where('name', 'trade_withdrawals:payout')
                    ->first()
                    ->id;

                // Check if the permission is not already assigned
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

    public function down(): void
    {
        DB::table('permissions')->where('name', 'trade_withdrawals:payout')->delete();
    }
};
