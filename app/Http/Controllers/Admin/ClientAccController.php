<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\RelationshipManager;
use App\Models\User;
use DB;

class ClientAccController extends Controller
{
    public function live_accounts()
    {
        // $role = session('userData')['userRole'];
        // $alogin = session('alogin');
        // $userGroups = explode(',', session('user_groups'));

        // // $rmCondition = DB::table('liveaccount')
        // //     ->leftJoin('aspnetusers', 'aspnetusers.email', '=', 'liveaccount.email')
        // //     ->join('account_types', 'account_types.ac_index', '=', 'liveaccount.account_type');
        // $rmCondition = Account::where('demo',false)->with(['user', 'accountType']);
        // // Check the role of the user
        // // if ($role != "Super Admin") {
        // //     $rmCondition->leftJoin('aspnetusers as user', 'user.email', '=', 'liveaccount.email');
        // // } else {
        // //     $rmCondition->whereRaw('1=1');
        // // }

        // if ($role != "Super Admin") {
        //     $rmCondition->whereHas('user');
        // }
        // // Additional conditions for role 2
        // if ($role == "Relationship Manager") {
        //     // $rmCondition->leftJoin('relationship_manager as rmgr', 'rmgr.user_id', '=', 'liveaccount.email')
        //     //     ->where('rmgr.rm_id', $alogin);
        //     $rmCondition->whereHas('relationshipManager', function ($q) use ($alogin) {
        //         $q->where('rm_id', $alogin);
        //     });
        // }

        // Add the order by clause
        // $rmCondition->orderBy('liveaccount.id', 'desc');

        // // Select the fields and execute the query
        // $accounts = $rmCondition->select('liveaccount.*', DB::raw('aspnetusers.id as enc_id'), 'account_types.ac_group')
        //     ->get();

        // $accountCount = $rmCondition->count();

        return view('admin.client_accounts.live_accounts');
    }
    public function demo_accounts()
    {
        // Get session data
        // $email = session('alogin');
        // $role = session('userData')['userRole'];
        // // Start building the query
        // // $rmCondition = DB::table('demoaccount')
        // //     ->join('aspnetusers', 'aspnetusers.email', '=', 'demoaccount.email');
        // $rmCondition = Account::where('demo',true)->with(['user','accountType']);

        // // If the user's role is 2, add the left join with `relationship_manager` and a condition
        // if ($role == "Relationship Manager") {
        //     // $rmCondition->leftJoin('relationship_manager as rmgr', 'rmgr.user_id', '=', 'demoaccount.email')
        //     //     ->where('rmgr.rm_id', $email);
        //     $rmCondition->whereHas('relationshipManager', function ($q) use ($email) {
        //             $q->where('rm_id', $email);
        //         });
        // }

        // // // Add the left join for `aspnetusers` regardless of the role
        // // $rmCondition->leftJoin('aspnetusers as user', 'user.email', '=', 'demoaccount.email');

        // // // Apply the group filter

        // // // Select required columns and add ordering
        // // $accounts = $rmCondition->select(DB::raw('demoaccount.*,aspnetusers.fullname as name,(aspnetusers.email) as enc_id'))
        // //     ->orderBy('demoaccount.id', 'desc')
        // //     ->get();
        // $totalaccounts = $rmCondition->count();
        return view('admin.client_accounts.demo_accounts');
    }
}
