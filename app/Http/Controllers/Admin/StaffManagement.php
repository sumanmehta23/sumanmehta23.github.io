<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Page;
use App\Models\Permission;
use App\Models\EmployeeList;
class StaffManagement extends Controller
{

    public function roles()
    {
        return view('admin.roles');
    }
    public function rolePermissions()
    {
        $roles = Role::all();
        return view('admin.role_permissions', compact('roles'));
    }
    public function adminUsers()
    {
        $roles = Role::where('is_active', 1)->get();
        return view('admin.admin_users', compact('roles'));
    }
    public function permissionsList(Request $request)
    {
        $id = $request->id;
        $roles = Role::where('id', $id)->first();
        $pages = Page::all();
        $permissions = Permission::where('role_id', $id)->get()->toArray();
        $rolePermissions = array_values(array_column($permissions, 'page_id'));
        $menu = [];
        foreach ($pages as $page) {
            if ($page['is_submenu'] == 0) {
                $menu[$page['page_id']] = [
                    'page_name' => $page['pagename'],
                    'page_id' => $page['id'],
                    'submenu' => []
                ];
            } else {
                $menu[$page['is_submenu']]['submenu'][] = [
                    'page_id' => $page['id'],
                    'page_name' => $page['pagename']
                ];
            }
        }

        return view('admin.permissions_list', compact('menu', 'rolePermissions', 'pages', 'roles'));
    }
    public function addRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $role = new Role();
        $role->name = $request->input('name');
        $role->description = $request->input('description');
        $role->created_by = session('userData')['client_index'];
        $role->is_active = $request->has('is_active') ? 1 : 0;
        if ($role->save()) {
            return redirect()->back()->with("success", "New Role Added");
        }
        return redirect()->back()->with("error", "Failed to add role");
    }
    public function updateRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $id = $request->input('role_id');
        $role = Role::where('role_id', $id)->firstOrFail();
        $role->name = $request->input('name');
        $role->description = $request->input('description');
        $role->is_active = $request->has('is_active') ? 1 : 0;
        if ($role->save()) {
            return redirect()->back()->with("success", "Role Details Updated");
        }
        return redirect()->back()->with("error", "Failed to update role");
    }
    public function updateRoleStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);
        $id = $request->input('role_id');
        $role = Role::where('role_id', $id)->firstOrFail();
        $role->is_active = $request->input('status');
        if ($role->save()) {
            return redirect()->back()->with("success", "Status Updated Successfully");
        }
        return redirect()->back()->with("error", "Failed to update role");
    }
    public function updateRolePermissions(Request $request)
    {
        $request->validate([
            'pages' => 'required|array',
        ]);
        $roleId = $request->input('role_id');
        Permission::where('role_id', $roleId)->delete();

        $pages = $request->input('pages');
        $createdBy = session('userData')['client_index'];

        $currentPermissions = Permission::where('role_id', $roleId)->pluck('page_id')->toArray();
        $pagesToAdd = array_diff($pages, $currentPermissions);
        $pagesToRemove = array_diff($currentPermissions, $pages);

        foreach ($pagesToAdd as $pageId) {
            Permission::updateOrCreate(
                [
                    'role_id' => $roleId,
                    'page_id' => $pageId,
                ],
                [
                    'role_id' => $roleId,
                    'page_id' => $pageId,
                    'created_by' => $createdBy,
                ]
            );
        }

        if (!empty($pagesToRemove)) {
            Permission::where('role_id', $roleId)
                    ->whereIn('page_id', $pagesToRemove)
                    ->delete();
        }
        // foreach ($pages as $pageId) {
        //     $permissions[] = [
        //         'page_id' => $pageId,
        //         'role_id' => $roleId,
        //         'created_by' => $createdBy,
        //     ];
        // }
        // Permission::insert($permissions);
        return redirect()->back()->with('permissions', 'Permissions updated successfully');
    }
    public function saveUser(Request $request, $userId = null)
    {
        $userId = request()->input('user_id');
        $request->validate([
            'username' => 'required|string|max:255',
            'role_id' => 'required',
            'email' => 'required|email|max:255|unique:emplist,email,' . $userId . ',client_index',
            'number' => 'required|string|max:15',
            'password' => $userId ? 'nullable|string' : 'required|string',
            'company_name' => 'required|string|max:255',
        ]);
        if ($userId) {
            $user = emplist::findOrFail($userId);
        } else {
            $user = new EmployeeList();
            $user->uid = '';
            $user->profile_pic = '';
            $user->empId = '';
            $user->userDepartment = '';
            $user->emailToken = '';
            $user->country = '';
        }
        $user->username = $request->input('username');
        $user->role_id = $request->input('role_id');
        $user->email = $request->input('email');
        $user->number = $request->input('number');
        if ($request->filled('password')) {
            $user->password = $request->input('password');
        }
        $user->company_name = $request->input('company_name');
        $user->userRole = $this->getRoleName($request->input('role_id'));
        $user->status = $request->has('is_active') ? 1 : 0;


        if ($user->save()) {
            $respMsg = $userId ? 'User Updated' : 'New User Added';
            return redirect()->back()->with('success', $respMsg);
        }
        return redirect()->back()->with('error', "Failed to save user");
    }
    protected function getRoleName($roleId)
    {
        $role = Role::find($roleId);
        return $role ? $role->name : 'Unknown';
    }
    public function rmDashboard(Request $request)
    {
        $clientIndex = $request->query('id');
        $rm_details = EmployeeList::whereRaw("(client_index) = ?", [$clientIndex])->first();
        if (!$rm_details) {
            return redirect('/admin/admin_users')->with('error', 'Invalid Account / Account Not Found');
        } else {
            $rm_id = $rm_details->email;
            $rm_email_enc = ($rm_id);
        }
        $inactiveUsers = \DB::table('aspnetusers as trs')
            ->Join('relationship_manager as rm', 'rm.user_id', '=', 'trs.email')
            ->where('rm.rm_id', $rm_id)
            ->selectRaw('SUM(CASE WHEN trs.status = 0 THEN 1 ELSE 0 END) AS inactive_users,
                  SUM(CASE WHEN trs.status = 1 THEN 1 ELSE 0 END) AS active_users')
            ->first();

        $total_clients = [
            'inactive_users' => $inactiveUsers->inactive_users,
            'active_users' => $inactiveUsers->active_users,
        ];
        $trade_deposit = \DB::table('trade_deposit as trs')
            ->Join('relationship_manager as rm', 'rm.user_id', '=', 'trs.email')
            ->where('rm.rm_id', $rm_id)
            ->where('trs.status', 1)
            ->whereNotIn('trs.deposit_type', ['Wallet Transfer'])
            ->sum('trs.deposit_amount');

        $wallet_deposit = \DB::table('wallet_deposit as trs')
            ->Join('relationship_manager as rm', 'rm.user_id', '=', 'trs.email')
            ->where('rm.rm_id', $rm_id)
            ->where('trs.status', 1)
            ->sum('trs.deposit_amount');

        $trade_withdrawal = \DB::table('trade_withdrawal as trs')
            ->Join('relationship_manager as rm', 'rm.user_id', '=', 'trs.email')
            ->where('rm.rm_id', $rm_id)
            ->where('trs.status', 1)
            ->whereNotIn('trs.withdraw_type', ['Wallet Withdrawal'])
            ->sum('trs.withdrawal_amount');

        $wallet_withdrawal = \DB::table('wallet_withdraw as trs')
            ->Join('relationship_manager as rm', 'rm.user_id', '=', 'trs.email')
            ->where('rm.rm_id', $rm_id)
            ->where('trs.status', 1)
            ->sum('trs.withdraw_amount');

        $pending_wd = \DB::table('wallet_deposits as trs')
            ->Join('relationship_manager as rm', 'rm.user_id', '=', 'trs.email')
            ->where('rm.rm_id', $rm_id)
            ->where('trs.status', 0)
            ->count();

        $pending_td = \DB::table('trade_deposit as trs')
            ->Join('relationship_manager as rm', 'rm.user_id', '=', 'trs.email')
            ->where('rm.rm_id', $rm_id)
            ->where('trs.status', 0)
            ->count();

        $pending_ww = \DB::table('wallet_withdraw as trs')
            ->Join('relationship_manager as rm', 'rm.user_id', '=', 'trs.email')
            ->where('rm.rm_id', $rm_id)
            ->where('trs.status', 0)
            ->count();

        $pending_tw = \DB::table('trade_withdrawal as trs')
            ->Join('relationship_manager as rm', 'rm.user_id', '=', 'trs.email')
            ->where('rm.rm_id', $rm_id)
            ->where('trs.status', 0)
            ->count();

        $pending_ib = \DB::table('ib1 as trs')
            ->Join('relationship_manager as rm', 'rm.user_id', '=', 'trs.email')
            ->where('rm.rm_id', $rm_id)
            ->where('trs.status', 0)
            ->count();

        $wallet_users = \DB::table('aspnetusers as trs')
            ->Join('relationship_manager as rm', 'rm.user_id', '=', 'trs.email')
            ->where('rm.rm_id', $rm_id)
            ->where('trs.wallet_enabled', 1)
            ->count();
        $latest_pending_deposit = \DB::table('wallet_deposits as trs')
            ->select('trs.*')
            ->Join('relationship_manager as rm', 'rm.user_id', '=', 'trs.email')
            ->where('rm.rm_id', $rm_id)
            ->where('trs.status', 0)
            ->orderBy('trs.raw_id', 'desc')
            ->limit(10)
            ->get();
        $latest_pending_withdrawal = \DB::table('wallet_withdraws as trs')
            ->Join('relationship_manager as rm', 'rm.user_id', '=', 'trs.email')
            ->where('rm.rm_id', $rm_id)
            ->where('trs.status', 0)
            ->orderBy('trs.raw_id', 'desc')
            ->limit(10)
            ->get();
        return view('admin.rm_dashboard', compact('rm_email_enc','latest_pending_deposit', 'latest_pending_withdrawal', 'rm_details', 'inactiveUsers', 'wallet_users', 'pending_ib', 'pending_td', 'pending_wd', 'pending_tw', 'pending_ww', 'wallet_withdrawal', 'trade_withdrawal', 'wallet_deposit', 'trade_deposit', 'total_clients'));
    }
}
