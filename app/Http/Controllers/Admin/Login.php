<?php

namespace App\Http\Controllers\Admin;

use App\Models\Permissions;
use App\Models\EmployeeList;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class Login extends Controller
{
    public function index()
    {
        return view('admin.login');
    }
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }
    public function adminLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|email',
            'password' => 'required',
        ]);
        $credentials = $request->only('username', 'password');
        $remember=$request->remember;
        if (Auth::guard('admin')->attempt(['email' => $credentials['username'], 'password' => $credentials['password'],'status'=>1])) {
            $request->session()->regenerate();
            // return redirect()->intended('dashboard');
        }
        // Attempt to log the user in
        $user = EmployeeList::where('email', $credentials['username'])
            ->first();
            
        if (!$user) {
            return redirect()->back()->with('error', 'Your login details are invalid or your email is not verified.');
        }
        
        if (Hash::needsRehash($user->password)) {
            if ($user->password === $request->input('password')) {
                $user->password = Hash::make($request->input('password'));
                $user->save(); 
            } else {
                return redirect()->back()->with('error', 'Login Details are Invalid');
            }
        }else {
            if (!Hash::check($request->input('password'), $user->password)) {
                return redirect()->back()->with('error', 'Your login details are invalid.');
            }
        }
            if ($user->status == '1') {
                // $credentials = $request->only('email', 'password');
// dd($credentials);
                if (Auth::guard('admin')->attempt(['email' => $credentials['username'], 'password' => $credentials['password']])) {
                    $request->session()->regenerate();
                    // return redirect()->intended('dashboard');
                }
                
                // Store user details in session
                // Auth::login($user);
                // $request->session()->regenerate();
                Cache::flush();
                Session::put('alogin', $user->email);
                Session::put('userRoleID', $user->role_id);
                Session::put('userRole', $user->userRole);
                Session::put('userID', $user->client_index);
                Session::put('userData', $user->toArray());
                // Fetch permissions
                $permissions = DB::table('permissions as p')
                    ->join('pages as pg', 'p.page_id', '=', 'pg.id')
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
                if ($user->userRole == "Super admin" || $user->userRole == "Relationship Manager") {
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
        
    }
    private function logLoginHistory($email)
    {
        $country = '';
        $ip = request()->ip();
        // LoginHistory::create([
        //     'user_id' => Auth::user()->id,
        //     'email' => $email,
        //     'ip' => $ip,
        //     'country' => $country,
        //     'action' => 'login',
        //     'status' => 1
        // ]);
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
