<?php

use App\Models\Role;
use Illuminate\Support\Facades\Cache;

// use DB;

function hexToRGB($hex)
{
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return "$r, $g, $b";
}
function getBadgeProperties($remark)
{
    switch ($remark) {
        case '1':
            return ['bg-success-transparent', '<i class="ri-checkbox-circle-line"></i>'];
        case '2':
            return ['bg-danger-transparent', '<i class="ri-close-circle-line"></i>'];
        default:
            return ['bg-warning-transparent', '<i class="ri-time-line"></i>'];
    }
}
function getPlanNameByPlanId($plans, $plan_id)
{
    foreach ($plans as $plan) {
        if ($plan->ib_plan_id == $plan_id) {
            return $plan->ib_cat_name;
        }
    }
    return null; // Return null if not found
}

if (!function_exists('settings')) {
    function settings()
    {
        return app()->view->shared('settings');
    }
}

function page_categories()
{
    $sql = "SELECT * from page_categories order by order_by asc";
    $query = DB::select($sql);
    return $query;
}

function filePermissions($userRole)
{
    $filePermissions = [];
    if ($userRole != "Super Admin") {
        $userRoleID = Cache::remember('role_id', 60 * 60, function () use ($userRole) {
            return Role::where('name', $userRole)->value('id');
        });
        $sql = "SELECT p.page_id,pg.filename from permissions p left join pages pg on(p.page_id=pg.id) WHERE p.role_id='" . $userRoleID."'";
        $role_permissions = DB::select($sql);
        $rolePermissionsList = array_values(array_column($role_permissions, 'page_id'));
        $filePermissions = array_values(array_column($role_permissions, 'filename'));
    }
    return $filePermissions;
}

function rolePermissions($userRole)
{
    $rolePermissionsList = [];
    if ($userRole != 'Super Admin') {
        $userRoleID = Cache::remember('role_id', 60 * 60, function () use ($userRole) {
            return Role::where('name', $userRole)->value('id');
        });
       
        $sql = "SELECT p.page_id,pg.filename from permissions p left join pages pg on(p.page_id=pg.id) WHERE p.role_id='" . $userRoleID."'";
        $role_permissions = DB::select($sql);
        $rolePermissionsList = array_values(array_column($role_permissions, 'page_id'));
    }
    return $rolePermissionsList;
}
