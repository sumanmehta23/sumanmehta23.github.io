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
        // Create permission groups for each API resource type
        $groups = [
            'Trades' => 'trade-related API permissions',
            'Users' => 'user-related API permissions',
            'Transactions' => 'transaction-related API permissions',
            'Withdrawals' => 'withdrawal-related API permissions',
            'Deposits' => 'deposit-related API permissions',
            'Webhooks' => 'webhook-related API permissions',
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

        // Associate permissions with their respective groups
        // Trades
        DB::table('permissions')
            ->where('name', 'like', 'api:trades:%')
            ->update(['permission_group_id' => $groupIds['Trades']]);

        // Users
        DB::table('permissions')
            ->where('name', 'like', 'api:users:%')
            ->update(['permission_group_id' => $groupIds['Users']]);

        // Transactions
        DB::table('permissions')
            ->where('name', 'like', 'api:transactions:%')
            ->update(['permission_group_id' => $groupIds['Transactions']]);

        // Withdrawals
        DB::table('permissions')
            ->where('name', 'like', 'api:withdrawals:%')
            ->update(['permission_group_id' => $groupIds['Withdrawals']]);

        // Deposits
        DB::table('permissions')
            ->where('name', 'like', 'api:deposits:%')
            ->update(['permission_group_id' => $groupIds['Deposits']]);

        // Webhooks
        DB::table('permissions')
            ->where('name', 'like', 'api:webhooks:%')
            ->update(['permission_group_id' => $groupIds['Webhooks']]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't delete the API group, just remove the association
        DB::table('permissions')
            ->where('name', 'like', 'api:%')
            ->update(['permission_group_id' => null]);
    }
};
