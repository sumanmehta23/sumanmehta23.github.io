<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\RelationshipManager;
use App\Models\User;
use App\Services\MailService;
use DB;

class ClientAccController extends Controller
{
    protected $mailService;
    public function __construct(MailService  $mailService)
    {
        $this->mailService = $mailService;
    }
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
    public function requested_accounts()
    {
        return view('admin.client_accounts.requested_accounts');
    }
    public function deleteAccounts(Request $request)
    {
        // dd($request->all());
        $settings = settings();

        $validatedData = $request->validate([
            // 'id' => 'required|id',
            'email' => 'required|email'
        ]);

        $account = Account::where('id', $request->id)->first();

        if ($account) {
           
            // $account->delete();

            // $date = date('Y-m-d', strtotime($account_details->created_at));

            $email = $validatedData['email'];
            $type = $account->demo == "1"? "Demo account": "Live account";

            $from = $settings['email_from_address'];
            $transid = "WDID" . str_pad($account->id, 4, '0', STR_PAD_LEFT);
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
            $emailSubject = $settings['admin_title'] . ' - Transaction Approved';
            $content = '<div>We are pleased to inform you that your account has been deleted.</div>
                        <div><b>Account code: </b>' . $account->code . '</div>
                        <div><b>Account type: </b>' .  $type . '</div>
                        <div><b>Created Date: </b>' . $account->created_at . '</div>
                        <div><b>Deleted Date: </b>' . $account->deleted_at . '</div>';
            $templateVars = [
                'name' => $account->name,
                'site_link' => $settings['copyright_site_name_text'],
                'email' => $settings['email_from_address'],
                'content' => $content,
                'title_right' => 'Account',
                'subtitle_right' => 'Deleted',
                'btn_text' => 'Go To Dashboard',    
            ];
            $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
    
            return redirect()->back()->with('success', 'Account deleted successfully.');
        } else {
           
            return redirect()->back()->with('error', 'Account not found.');
        }
        
    }
}
