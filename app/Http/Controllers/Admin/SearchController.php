<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        // Initialize the base query.
        $query = DB::table('liveaccount')
            ->select('liveaccount.*', DB::raw('aspnetusers.id as enc_id'), 'account_types.ac_group')
            ->leftJoin('aspnetusers', 'aspnetusers.email', '=', 'liveaccount.email')
            ->join('account_types', 'account_types.ac_index', '=', 'liveaccount.account_type');

        // Build the rmCondition based on user roles.
        $userData = session('userData');
        $roleId = $userData['role_id'] ?? null;

        if ($roleId != 1) {
            $query->leftJoin('aspnetusers as user', 'user.email', '=', 'liveaccount.email');
        }

        if ($roleId == 2) {
            $query->leftJoin('relationship_manager as rmgr', 'rmgr.user_id', '=', 'liveaccount.email')
                ->where('rmgr.rm_id', session('alogin'));
        }

        // Apply conditions based on user groups.
        if ($roleId != 1) {
            $userGroups = session('user_groups');
            if ($roleId == 2) {
                $query->whereIn('user.group_id', explode(',', $userGroups));
            } else {
                $query->whereIn('user.group_id', explode(',', $userGroups));
            }
        }

        // Apply search condition if it exists.
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('liveaccount.code', 'like', '%' . $search . '%')
                    ->orWhere('liveaccount.email', 'like', '%' . $search . '%')
                    ->orWhere('aspnetusers.fullname', 'like', '%' . $search . '%');
            });
        }

        // Order the results.
        $accounts = $query->orderByDesc('id')->get();
        return view("admin.search", compact("accounts"));
    }
}
