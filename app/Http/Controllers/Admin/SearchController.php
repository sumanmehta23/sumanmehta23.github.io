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
        $query=Account::query()
            ->with(['user','accountType']);

        // Initialize the base query.
        // $query = DB::table('liveaccount')
        //     ->select('liveaccount.*', DB::raw('aspnetusers.id as enc_id'), 'account_types.ac_group')
        //     ->leftJoin('aspnetusers', 'aspnetusers.email', '=', 'liveaccount.email')
        //     ->join('account_types', 'account_types.ac_index', '=', 'liveaccount.account_type');

        // Build the rmCondition based on user roles.
        $user = auth()->user();
        if ($user->role->name != "Super Admin") {

            // $query->leftJoin('aspnetusers as user', 'user.id', '=', 'account.user_id');
        }

        if ($user->role->name ==  'Relationship Manager') {
            
            $query->leftJoin('relationship_manager as rmgr', 'rmgr.user_id', '=', 'liveaccount.email')
                ->where('rmgr.rm_id', session('alogin'));
        }

        // Apply conditions based on user groups.
        if ($user->role->name != "Super Admin") {
            $userGroups = session('user_groups');
            if ($user->role->name ==  'Relationship Manager') {
                $query->whereIn('user.group_id', explode(',', $userGroups));
            } else {
                $query->whereIn('user.group_id', explode(',', $userGroups));
            }
        }

        // Apply search condition if it exists.
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {

                $q->where('code', 'like', '%' . $search . '%')->orWhereHas('user', function ($q) use ($search) {
                    $q->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('fullname', 'like', '%' . $search . '%');
                });
                    
            });
        }

        // Order the results.
        $accounts = $query->orderByDesc('id')->get();
        return view("admin.search", compact("accounts"));
    }
}
