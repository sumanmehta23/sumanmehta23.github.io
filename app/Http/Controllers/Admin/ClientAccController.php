<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class ClientAccController extends Controller
{
    public function live_accounts()
    {
        $roleId = session('userData')['role_id'];
        $alogin = session('alogin');
        $userGroups = explode(',', session('user_groups'));

        $rmCondition = DB::table('liveaccount')
            ->leftJoin('aspnetusers', 'aspnetusers.email', '=', 'liveaccount.email')
            ->join('account_types', 'account_types.ac_index', '=', 'liveaccount.account_type');

        // Check the role of the user
        if ($roleId != 1) {
            $rmCondition->leftJoin('aspnetusers as user', 'user.email', '=', 'liveaccount.email');
        } else {
            $rmCondition->whereRaw('1=1');
        }

        // Additional conditions for role 2
        if ($roleId == 2) {
            $rmCondition->leftJoin('relationship_manager as rmgr', 'rmgr.user_id', '=', 'liveaccount.email')
                ->where('rmgr.rm_id', $alogin);
        }

        // Add the order by clause
        $rmCondition->orderBy('liveaccount.id', 'desc');

        // Select the fields and execute the query
        $accounts = $rmCondition->select('liveaccount.*', DB::raw('md5(aspnetusers.id) as enc_id'), 'account_types.ac_group')
            ->get();
        return view('admin.client_accounts.live_accounts', compact("accounts"));
    }
    public function demo_accounts()
    {
        // Get session data
        $email = session('alogin');
        $roleId = session('userData')['role_id'];
        // Start building the query
        $rmCondition = DB::table('demoaccount')
            ->join('aspnetusers', 'aspnetusers.email', '=', 'demoaccount.email');

        // If the user's role is 2, add the left join with `relationship_manager` and a condition
        if ($roleId == 2) {
            $rmCondition->leftJoin('relationship_manager as rmgr', 'rmgr.user_id', '=', 'demoaccount.email')
                ->where('rmgr.rm_id', $email);
        }

        // Add the left join for `aspnetusers` regardless of the role
        $rmCondition->leftJoin('aspnetusers as user', 'user.email', '=', 'demoaccount.email');

        // Apply the group filter

        // Select required columns and add ordering
        $accounts = $rmCondition->select(DB::raw('demoaccount.*,aspnetusers.fullname as name,md5(aspnetusers.email) as enc_id'))
            ->orderBy('demoaccount.id', 'desc')
            ->get();
        return view('admin.client_accounts.demo_accounts', compact("accounts"));
    }
}
