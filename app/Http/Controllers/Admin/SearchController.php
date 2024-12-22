<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{

    public function index(Request $request)
    {

        // Initialize the base query.
        $query = DB::table('accounts')
            ->select('accounts.*', DB::raw('aspnetusers.id as enc_id'), 'account_types.ac_group')
            ->leftJoin('aspnetusers', 'aspnetusers.id', '=', 'accounts.user_id')
            ->join('account_types', 'account_types.id', '=', 'accounts.account_type_id');

        // Build the rmCondition based on user roles.
        $userData = session('userData');
        $roleId = $userData['role_id'] ?? null;
        // dd(session('user_groups'));
        if ($roleId != '9db6f441-3c60-4045-a236-8b7b71fa6e15') {
            $query->leftJoin('aspnetusers as user', 'user.email', '=', 'accounts.email');
        }

        if ($roleId == '9db6f441-3d0e-4ad5-a0ce-05df46e81956') {
            $query->leftJoin('relationship_manager as rmgr', 'rmgr.user_id', '=', 'accounts.email')
                ->where('rmgr.rm_id', session('alogin'));
        }
        // Apply conditions based on user groups.
        if ($roleId != '9db6f441-3c60-4045-a236-8b7b71fa6e15') {
            $userGroups = session('user_groups');
            // dd($userGroups);
            if ($roleId == '9db6f441-3d0e-4ad5-a0ce-05df46e81956') {
                $query->whereIn('user.group_id', explode(',', $userGroups));
            } else {
                $query->whereIn('user.group_id', explode(',', $userGroups));
            }
        }

        // Apply search condition if it exists.
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('accounts.code', 'like', '%' . $search . '%')
                    ->orWhere('accounts.email', 'like', '%' . $search . '%')
                    ->orWhere('aspnetusers.fullname', 'like', '%' . $search . '%');
            });
        }

        // Order the results.
        $accounts = $query->orderByDesc('id')->get();
        return view("admin.search", compact("accounts"));
    }
}
