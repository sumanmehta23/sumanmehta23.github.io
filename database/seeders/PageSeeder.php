<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Permission;
use App\Models\Permissions;
use App\Models\EmployeeList;
use App\Models\PageCategory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pagecategories = json_decode(file_get_contents(__DIR__.'/data/page_categories.json'), true);
        foreach ($pagecategories as $category) {
            PageCategory::create([
                'page_category_id' => $category['page_category_id'],
                'category_name' => $category['category_name'],
                'category_desc' => $category['category_desc'],
                'is_active' => $category['is_active'],
                'order_by' => $category['order_by'],
                'created_by' => $category['created_by'],
            ]);
        }
        $superadmin=EmployeeList::where('username', 'lqhmarket')->first();
        $pages = json_decode(file_get_contents(__DIR__.'/data/pages.json'), true);
        foreach ($pages as $page) {
            $category=PageCategory::where('page_category_id', $page['page_category_id'])->first();
           $newpage= Page::create([
                'page_id' => $page['page_id'],
                'page_category_id' => $category->id,
                'pagename' => $page['pagename'],
                'filename' => $page['filename'],
                'is_submenu'=> $page['is_submenu'],
                'active'=> $page['active'],
                'page_order'=> $page['page_order'],
                'icon'=> $page['icon'],
                'show_in_menu'=> $page['show_in_menu']
            ]);
            Permission::create([
                'page_id' => $newpage->id,
                'role_id' => $superadmin->role->id,
                'created_by' => $superadmin->id,
            ]);

        }

    }
}
