<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SearchController extends Controller
{

    public function index(Request $request)
{
    // dd($request->all());
    // Initialize the base query.
    $query = DB::table('accounts')
        ->where('accounts.account_request_status', 1)
        ->where('accounts.deleted_at', NULL)
        ->select('accounts.*', DB::raw('aspnetusers.id as enc_id'), 'account_types.ac_group')
        ->leftJoin('aspnetusers', 'aspnetusers.id', '=', 'accounts.user_id')
        ->join('account_types', 'account_types.id', '=', 'accounts.account_type_id');

    // Build the rmCondition based on user roles.
    $userData = session('userData');
    $roleId = $userData['role_id'] ?? null;

    if ($roleId != '9db6f441-3c60-4045-a236-8b7b71fa6e15') {
        $query->leftJoin('aspnetusers as user', 'user.email', '=', 'accounts.email');
    }

    // if ($roleId == '9db6f441-3d0e-4ad5-a0ce-05df46e81956') {
    //     $query->leftJoin('relationship_manager as rmgr', 'rmgr.user_id', '=', 'accounts.user_id')
    //         ->where('rmgr.rm_id',  $userData['id']);
    // }

    if($userData['userRole'] == 'Relationship Manager'){
        $query->leftJoin('relationship_manager as rmgr', 'rmgr.user_id', '=', 'accounts.user_id')
            ->where('rmgr.rm_id', $userData['id']);
    }

    // Apply conditions based on user groups.
    if ($roleId != '9db6f441-3c60-4045-a236-8b7b71fa6e15') {
        $userGroups = session('user_groups');
        if ($userGroups) {
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

    // Fetch the account data.
    $accounts = $query->orderByDesc('accounts.id')->get();
    // If no accounts are found, search in aspnetusers table.
    // dd($userData);

    if ($accounts->isEmpty()) {

        $userQuery = User::with([
            'ib',
            'countryDetail',
            'employee' => function ($query) use ($userData) {
                if ($userData['userRole'] === 'Relationship Manager') {
                    $query->wherePivot('rm_id', $userData['id']); // Ensure it filters by rm_id
                }
                $query->select('emplist.id', 'username');
            }
        ]) ->when($userData['userRole'] === 'Relationship Manager', function ($q) use ($userData) {
            // Ensure that only users linked to the admin's rm_id are retrieved
            $q->whereHas('employee', function ($query) use ($userData) {
                $query->where('relationship_manager.rm_id', $userData['id']);
            });
        });

        if ($request->has('search')) {
            $search = $request->input('search');
            $userQuery->where(function ($q) use ($search) {
                $q->where('aspnetusers.email', 'like', '%' . $search . '%')
                    ->orWhere('aspnetusers.fullname', 'like', '%' . $search . '%');
            });
        }

        $accounts = $userQuery->orderByDesc('id')->get();
        
        return view("admin.search2", compact("accounts"));
    }else{

        $search = $request->input('search');

        if(is_numeric($search))
        {
            if($accounts[0]->demo == "1"){

                $type="Client - Demo Accounts";
            }else{

                $type="Client - Live Accounts";
            }
        }else{
            $type="Client - Accounts";
        }

        return view("admin.search", compact("accounts", "type"));
    }

    // Return the merged or single dataset to the view.

}

}
