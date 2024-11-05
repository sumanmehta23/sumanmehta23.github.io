<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeList;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Permissions;


class Login extends Controller
{
    public function index()
    {
        return view('admin.login');
    }
    public function showLoginForm()
    {
        // if (Auth::check()) {
        //     return redirect()->route('admin.dashboard');
        // }
        return view('admin.login');
    }
    public function adminLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|email',
            'password' => 'required',
        ]);
        $credentials = $request->only('username', 'password');
        // Attempt to log the user in
        $user = EmployeeList::where('email', $credentials['username'])
            ->first();
        if ($user && ($credentials['password'] == $user->password)) {
            if ($user->status == '1') {
                // Store user details in session
                Auth::login($user);
                Session::put('alogin', $user->email);
                Session::put('userRoleID', $user->role_id);
                Session::put('userRole', $user->userRole);
                Session::put('userID', $user->client_index);
                Session::put('userData', $user->toArray());
                // Fetch permissions
                $permissions = DB::table('permissions as p')
                    ->join('pages as pg', 'p.page_id', '=', 'pg.page_id')
                    ->where('p.role_id', $user->role_id)
                    ->where('pg.is_submenu', 0)
                    ->orderBy('pg.page_order', 'asc')
                    ->get(['p.page_id', 'pg.filename']);

                $current_permissions=[];
                foreach ($permissions as $permission) {
                    $current_permissions[] = $permission->filename;
                    $submenus = DB::table('pages')
                        ->where('is_submenu', $permission->page_id)
                        ->orderBy('page_order', 'asc')
                        ->get();
                    foreach ($submenus as $submenu) {
                        $current_permissions[] = $submenu->filename;
                    }
                }
                Session::put('current_permissions', $current_permissions);
                // Log user in
                if ($user->userRole == 'Super admin' || $user->userRole == 'Relationship Manager') {
                    $this->logLoginHistory($user->email);
                    return redirect('admin/dashboard');
                }
                if (in_array('/admin/dashboard', $current_permissions)) {
                    $this->logLoginHistory($user->email);
                    return redirect('admin/dashboard');
                } else {
                    $first_php_page = '';
                    foreach ($current_permissions as $permission) {
                        if (strpos($permission, '.php') !== false) {
                            $first_php_page = $permission;
                            break;
                        }
                    }
                    if (!empty($first_php_page)) {
                        $this->logLoginHistory($user->email);
                        return redirect($first_php_page);
                    } else {
                        return back()->with('error', 'You do not have any page permissions. Please contact the administrator.');
                    }
                }
            } else {
                return back()->with('error', 'Your account is inactive. Please contact the administrator.');
            }
        } else {
            return back()->with('error', 'Login Details are Invalid');
        }
    }
    private function logLoginHistory($email)
    {
        $country = '';
        $ip = request()->ip();
        LoginHistory::create([
            'email' => $email,
            'ip' => $ip,
            'country' => $country,
            'action' => 'login',
            'status' => 1
        ]);
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::logout();
        session()->forget(['userData', 'alogin']);
        unset($_SESSION['alogin']);
        unset($_SESSION['userData']);
        return redirect('/admin/login');
    }

}
