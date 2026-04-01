<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Create Export permission group
        $groupId = DB::table('permission_groups')->where('name', 'Export')->value('id');

        if (!$groupId) {
            $groupId = Str::uuid()->toString();
            DB::table('permission_groups')->insert([
                'id'         => $groupId,
                'name'       => 'Export',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Define all export permissions
        $permissions = [
            ['name' => 'export:clients',                    'description' => 'Export all clients'],
            ['name' => 'export:live_accounts',               'description' => 'Export live accounts'],
            ['name' => 'export:demo_accounts',               'description' => 'Export demo accounts'],
            ['name' => 'export:requested_accounts',          'description' => 'Export requested accounts'],
            ['name' => 'export:wallet_deposit',              'description' => 'Export wallet deposits'],
            ['name' => 'export:wallet_withdrawal',           'description' => 'Export wallet withdrawals'],
            ['name' => 'export:trading_deposit',             'description' => 'Export trading deposits'],
            ['name' => 'export:trading_withdrawal',          'description' => 'Export trading withdrawals'],
            ['name' => 'export:internal_transfer',           'description' => 'Export internal transfers'],
            ['name' => 'export:pending_wallet_deposit',      'description' => 'Export pending wallet deposits'],
            ['name' => 'export:pending_wallet_withdrawal',   'description' => 'Export pending wallet withdrawals'],
            ['name' => 'export:pending_trading_deposit',     'description' => 'Export pending trading deposits'],
            ['name' => 'export:pending_trading_withdrawal',  'description' => 'Export pending trading withdrawals'],
            ['name' => 'export:ib_dashboard',                'description' => 'Export IB dashboard'],
            ['name' => 'export:ib_list',                     'description' => 'Export IB list'],
            ['name' => 'export:ib_list_active',              'description' => 'Export active IB list'],
            ['name' => 'export:promocode',                   'description' => 'Export promocodes'],
            ['name' => 'export:leaderboard',                 'description' => 'Export leaderboard'],
            ['name' => 'export:requested_competition',       'description' => 'Export requested competition accounts'],
            ['name' => 'export:affiliates',                  'description' => 'Export affiliates'],
            ['name' => 'export:login_history',               'description' => 'Export login history'],
            ['name' => 'export:logs',                        'description' => 'Export activity logs'],
            ['name' => 'export:ip_ban',                      'description' => 'Export blocked IPs'],
        ];

        $permissionIds = [];

        foreach ($permissions as $perm) {
            $existing = DB::table('permissions')->where('name', $perm['name'])->first();

            if ($existing) {
                $permissionIds[$perm['name']] = $existing->id;
            } else {
                $id = Str::uuid()->toString();
                DB::table('permissions')->insert([
                    'id'                  => $id,
                    'name'                => $perm['name'],
                    'description'         => $perm['description'],
                    'permission_group_id' => $groupId,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
                $permissionIds[$perm['name']] = $id;
            }
        }

        // Assign all export permissions to Super Admin
        $superAdminRole = DB::table('roles')->where('name', 'Super Admin')->first();

        if ($superAdminRole) {
            foreach ($permissionIds as $permissionId) {
                $alreadyLinked = DB::table('permission_role')
                    ->where('role_id', $superAdminRole->id)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (!$alreadyLinked) {
                    DB::table('permission_role')->insert([
                        'permission_id' => $permissionId,
                        'role_id'       => $superAdminRole->id,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
        }

        // Do NOT assign export permissions to Support Team (they are unchecked by default)
        // Support Team role will not have these permissions unless explicitly assigned
    }

    public function down(): void
    {
        $permissionNames = [
            'export:clients',
            'export:live_accounts',
            'export:demo_accounts',
            'export:requested_accounts',
            'export:wallet_deposit',
            'export:wallet_withdrawal',
            'export:trading_deposit',
            'export:trading_withdrawal',
            'export:internal_transfer',
            'export:pending_wallet_deposit',
            'export:pending_wallet_withdrawal',
            'export:pending_trading_deposit',
            'export:pending_trading_withdrawal',
            'export:ib_list',
            'export:ib_list_active',
            'export:ib_dashboard',
            'export:promocode',
            'export:leaderboard',
            'export:requested_competition',
            'export:affiliates',
            'export:login_history',
            'export:logs',
            'export:ip_ban',
        ];

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $permissionNames)
            ->pluck('id');

        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('name', $permissionNames)->delete();
        DB::table('permission_groups')->where('name', 'Export')->delete();
    }
};
