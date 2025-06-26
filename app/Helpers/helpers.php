<?php

use App\Models\Role;
use App\Models\PageCategory;
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
    return "-"; // Return null if not found
}

if (!function_exists('settings')) {
    function settings()
    {
        return app()->view->shared('settings');
    }
}

function page_categories($roleId)
{
    // Cache categories for 60 minutes, specific to the roleId
    $categories = Cache::remember('page_categories_all_role_' . $roleId, 60, function () {
        return DB::select("SELECT * FROM page_categories ORDER BY order_by ASC");
    });

    // Cache main menus and submenus for each category, specific to the roleId
    foreach ($categories as $category) {
        // Cache main menus for each category, specific to the roleId
        $category->main_menus = Cache::remember('main_menus_category_' . $category->id . '_role_' . $roleId, 60, function () use ($category) {
            return DB::table('pages')
                ->where('is_submenu', 0) // Main menus (not submenus)
                ->where('page_category_id', $category->id)
                ->where('active', 1)
                ->orderBy('page_order', 'asc')
                ->get();
        });

        // Cache submenus for each main menu, specific to the roleId
        foreach ($category->main_menus as $main) {
            if ($main->is_submenu == 0) {
                // If it's a main menu, check if it has submenus
                $main->sub_menus = Cache::remember('sub_menus_main_' . $main->page_id . '_role_' . $roleId, 60, function () use ($main) {
                    return DB::table('pages')
                        ->where('is_submenu', $main->page_id) // Look for submenus where the parent is the current main menu
                        ->where('active', 1)
                        ->orderBy('page_order', 'asc')
                        ->get();
                });
            } else {
                // If it's already a submenu, no need to cache submenus
                $main->sub_menus = collect(); // No submenus for this one
            }
        }
    }
    return $categories;
}


function filePermissions($userRole)
{
    $filePermissions = [];
    // if ($userRole != "Super Admin") {
    //     $userRoleID = Cache::remember('role_id', 60 * 60, function () use ($userRole) {
    //         return Role::where('name', $userRole)->value('id');
    //     });
    //     $sql = "SELECT p.page_id,pg.filename from permissions p left join pages pg on(p.page_id=pg.id) WHERE p.role_id='" . $userRoleID."'";
    //     $role_permissions = DB::select($sql);
    //     $rolePermissionsList = array_values(array_column($role_permissions, 'page_id'));
    //     $filePermissions = array_values(array_column($role_permissions, 'filename'));
    // }
    return $filePermissions;
}

function rolePermissions($userRole)
{
    $rolePermissionsList = [];
    // if ($userRole != 'Super Admin') {
    //     $userRoleID = Cache::remember('role_id', 60 * 60, function () use ($userRole) {
    //         return Role::where('name', $userRole)->value('id');
    //     });

    //     $sql = "SELECT p.page_id,pg.filename from permissions p left join pages pg on(p.page_id=pg.id) WHERE p.role_id='" . $userRoleID."'";
    //     $role_permissions = DB::select($sql);
    //     $rolePermissionsList = array_values(array_column($role_permissions, 'page_id'));
    // }

    return $rolePermissionsList;
}

require_once __DIR__.'/formatToK.php';
