<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Support\Str;
use App\Models\PermissionGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync {--force : Delete all permissions and recreate them from scratch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync or recreate permissions from defined policies with descriptions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Step 1: Optionally delete all existing permissions
        if ($this->option('force')) {
            $this->info('Force option selected. Deleting all existing permissions...');
            Permission::query()->delete();
        }

        // Step 2: Get all policy classes
        $policiesPath = app_path('Policies');
        if (!File::exists($policiesPath)) {
            $this->error('No Policies directory found!');
            return 1;
        }

        $policyFiles = File::allFiles($policiesPath);

        $permissions = [];
        foreach ($policyFiles as $file) {
            $policyClass = 'App\\Policies\\' . $file->getFilenameWithoutExtension();

            if (!class_exists($policyClass)) {
                $this->warn("Class $policyClass does not exist. Skipping...");
                continue;
            }

            $methods = get_class_methods($policyClass);

            // Exclude default Laravel methods (like constructor)
            $filteredMethods = array_filter($methods, function ($method) {
                return !in_array($method, ['__construct']);
            });
            $groupName = class_basename($policyClass); // E.g., PermissionPolicy
            $groupName = str_replace('Policy', '', $groupName); // Remove "Policy"

            // Check if the group already exists
            $permissionGroup = PermissionGroup::firstOrCreate(
                ['name' => $groupName],
                ['id' => (string) \Illuminate\Support\Str::uuid()]
            );
            foreach ($filteredMethods as $method) {
                $permissions[] = [
                    'name' => $this->generatePermissionName($policyClass, $method),
                    'description' => $this->generateDescription($policyClass, $method),
                    'permission_group_id' => $permissionGroup->id
                ];
            }
        }

        // Step 3: Insert or sync permissions
        $this->info('Syncing permissions...');
        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                [
                    'id' => Str::uuid()->toString(),
                    'description' => $permission['description'],
                    'permission_group_id' => $permission['permission_group_id']
                ]
            );
        }

        $this->info('Permissions synced successfully!');
        return 0;
    }

    /**
     * Generate a human-readable permission name (e.g., permission:create).
     *
     * @param string $policyClass
     * @param string $method
     * @return string
     */
    private function generatePermissionName(string $policyClass, string $method): string
    {
        // Extract the policy name without namespace
        $policyName = class_basename($policyClass);

        // Remove "Policy" from the class name
        $entity = Str::snake(str_replace('Policy', '', $policyName));

        // Combine entity and method into a "entity:action" format
        return $entity . ':' . $method;
    }

    /**
     * Generate a human-readable description for a permission.
     *
     * @param string $policyClass
     * @param string $method
     * @return string
     */
    private function generateDescription(string $policyClass, string $method): string
    {
        // Extract the policy name without namespace
        $policyName = class_basename($policyClass);

        // Remove "Policy" from the class name
        $entity = str_replace('Policy', '', $policyName);
        $readableMethod = ucwords(preg_replace('/(?<!^)([A-Z])/', ' $1', $method));
        // Capitalize the method
        $action = Str::ucfirst($readableMethod);

        // Combine entity and action into a description
        return  $action;
    }
}