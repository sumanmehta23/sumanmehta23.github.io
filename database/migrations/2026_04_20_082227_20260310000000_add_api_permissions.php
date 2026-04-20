<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only create if permissions table exists
        if (!Schema::hasTable('permissions')) {
            return;
        }
        $existing = DB::table('permission_groups')
            ->where('name', 'API')
            ->first();

        if (!$existing) {

            $groupId = Str::uuid()->toString();
            DB::table('permission_groups')->insert([
                'id' => $groupId,
                'name' => "API",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        // Create API permission records using DB insert to avoid mass assignment issues
        $permissions = [
            // User API permissions
            ['name' => 'api:users:read', 'description' => 'View user data via API', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'api:users:export', 'description' => 'Export user data via API', 'created_at' => now(), 'updated_at' => now()],

            // Trade API permissions
            ['name' => 'api:trades:read', 'description' => 'View trade data via API', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'api:trades:export', 'description' => 'Export trade data via API', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'api:trades:analyze', 'description' => 'Analyze trade data via API', 'created_at' => now(), 'updated_at' => now()],

            // Transaction API permissions
            ['name' => 'api:transactions:read', 'description' => 'View transaction data via API', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'api:transactions:export', 'description' => 'Export transaction data via API', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'api:transactions:reports', 'description' => 'View transaction reports via API', 'created_at' => now(), 'updated_at' => now()],

            // Withdrawal API permissions
            ['name' => 'api:withdrawals:read', 'description' => 'View withdrawal data via API', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'api:withdrawals:export', 'description' => 'Export withdrawal data via API', 'created_at' => now(), 'updated_at' => now()],

            // Deposit API permissions
            ['name' => 'api:deposits:read', 'description' => 'View deposit data via API', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'api:deposits:export', 'description' => 'Export deposit data via API', 'created_at' => now(), 'updated_at' => now()],

            // Webhook API permissions
            ['name' => 'api:webhooks:read', 'description' => 'View webhook status via API', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'api:webhooks:manage', 'description' => 'Manage webhooks (enable/disable) via API', 'created_at' => now(), 'updated_at' => now()],
        ];

        // Insert permissions, ignoring duplicates
        foreach ($permissions as $permission) {
//            $permission['id'] = Str::uuid()->toString();
            $permission['permission_group_id'] = $existing ? $existing->id : $groupId;
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        // Delete API permissions
        DB::table('permissions')->where('name', 'like', 'api:%')->delete();
    }
};
