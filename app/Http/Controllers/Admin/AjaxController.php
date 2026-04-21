<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Enums\PlatformEnum;
use App\Models\Ib1;
use App\Models\Task;
use App\Models\User;
use App\Models\Trade;
use App\Models\Account;
use App\Models\UserLog;
use App\Models\IbWallet;
use App\Models\Promocode;
use App\Models\ClientTask;
use App\Models\Permission;
use App\Models\AccountType;
use App\Models\RestrictIps;
use App\Models\ClientWallet;
use App\Models\EmployeeList;
use App\Models\TradeDeposit;
use Illuminate\Http\Request;

use App\Models\WalletDeposit;
use App\Helpers\AccountHelper;

use App\Models\WalletWithdraw;
use App\Events\IbStatusChanged;
use App\Models\TradeWithdrawals;
use Yajra\DataTables\DataTables;
use App\Exports\CompetitionExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AjaxController extends Controller
{
    public function __contract()
    {
        if (!session("alogin")) {
            return response(["status" => false, "message" => "Please Login or Refresh the Page"], 401);
        }
    }

    public function index(Request $request)
    {
        if (isset($request->action)) {
            $action = $request->action;
            $id = isset($request->id) ? $request->id : null;
            $type = isset($request->type) ? $request->type : null;
            $tier = isset($request->tier) ? $request->tier : null;
            $search = isset($request->search) ? $request->search : null;
            $requestData = $request->all();
            try {
                //code...
                $result = [];
                switch ($action) {
                    case 'getClientDetails':
                        $result = $this->getClientDetails($requestData);
                        break;
                    case 'getWalletDeposit':
                        $result = $this->getWalletDeposit();
                        break;
                    case 'getComissionData':
                        $result = $this->getComissionData($requestData);
                        break;
                    case 'getWalletWithdrawal':
                        $result = $this->getWalletWithdrawal();
                        break;
                    case 'getTradingDeposit':
                        $result = $this->getTradingDeposit();
                        break;
                    case 'getTradingWithdrawal':
                        $result = $this->getTradingWithdrawal();
                        break;
                    case 'getInternalTransfer':
                        $result = $this->getInternalTransfer();
                        break;
                    case 'getPendingWalletDeposit':
                        $result = $this->getPendingWalletDeposit();
                        break;
                    case 'getPendingWalletWithdrawal':
                        $result = $this->getPendingWalletWithdrawal();
                        break;
                    case 'getPendingTradingDeposit':
                        $result = $this->getPendingTradingDeposit();
                        break;
                    case 'getPendingTradingWithdrawal':
                        $result = $this->getPendingTradingWithdrawal();
                        break;
                    case 'getPendingInternalTransfer':
                        $result = $this->getPendingInternalTransfer();
                        break;
                    case 'getKYCHistory':
                        $result = $this->getKYCHistory();
                        break;
                    case 'getBankDetails':
                        $result = $this->getBankDetails();
                        break;
                    case 'getAdminUsers':
                        $result = $this->getAdminUsers();
                        break;
                    case 'getMT5Groups':
                        $result = $this->getMT5Groups($type);
                        break;
                    case 'getCompetitionGroups':
                        $result = $this->getCompetitionGroups($request);
                        break;
                    case 'getIbGroups':
                        $result = $this->getIbGroups($type);
                        break;
                    case 'getIbPlans':
                        $result = $this->getIbPlans($type);
                        break;
                    case 'getMT5Category':
                        $result = $this->getMT5Category($type);
                        break;
                    case 'getRoles':
                        $result = $this->getRoles();
                        break;
                    case 'getRolePermissions':
                        $result = $this->getRolePermisions();
                        break;
                    case 'getAllTickets':
                        $result = $this->getAllTickets();
                        break;
                    case 'getOpenTickets':
                        $result = $this->getOpenTickets();
                        break;
                    case 'getClosedTickets':
                        $result = $this->getClosedTickets();
                        break;
                    case 'getRoleDetails':
                        $result = $this->getRoleDetails($id);
                        break;
                    case 'getPaymentGateways':
                        $result = $this->getPaymentGateways();
                        break;
                    case 'ibEnroll':
                        $result = $this->ibEnroll();
                        break;
                    case 'getLatestDeposit':
                        $result = $this->getLatestDeposit($id);
                        break;
                    case 'getLatestWithdrawal':
                        $result = $this->getLatestWithdrawal($id);
                        break;
                    case 'getLatestTransfer':
                        $result = $this->getLatestTransfer($id);
                        break;
                    case 'getClientWallets':
                        $result = $this->getClientWallets($id);
                        break;
                    case 'verifyClientWallet':
                        $result = $this->verifyClientWallet($requestData);
                        break;
                    case 'deleteClientWallet':
                        $result = $this->deleteClientWallet($requestData);
                        break;
                    case 'getIbUsers':
                        $result = $this->getIbUsers();
                        break;
                    case 'getPendingIbUsers':
                        $result = $this->getPendingIbUsers();
                        break;
                    case 'getAdminDetails':
                        $result = $this->getAdminDetails($id);
                        break;
                    case 'deleteAdminUser':
                        $result = $this->deleteAdminUser($requestData);
                        break;
                    case 'getPaymentDetails':
                        $result = $this->getPaymentDetails($id);
                        break;
                    case 'updateClientStatus':
                        $result = $this->updateClientStatus($requestData);
                        break;
                    case 'getIbList':
                        $result = $this->getIbList($id);
                        break;
                    case 'getRMbyGroup':
                        $result = $this->getRMbyGroup($id);
                        break;
                    case 'getListOfGroups':
                        $result = $this->getListOfGroups($search);
                        break;
                    case 'getListOfUsers':
                        $result = $this->getListOfUsers($search);
                        break;
                    case 'getListOfIBs':
                        $result = $this->getListOfIBs($search);
                        break;
                    case 'requestIB':
                        $result = $this->requestIB($requestData);
                        break;
                    case 'resendVerificationEmail':
                        $result = $this->resendVerificationEmail($requestData);
                        break;
                    case 'getPermissions':
                        $result = $this->getPermissions($request);
                        break;
                    default:
                        $result = ['error' => 'Invalid function call'];
                        break;
                }
            } catch (\Throwable $th) {
                $result = ['error' => $th->getMessage()];
            }
        } else {
            $result =  ['error' => 'No functions specified'];
        }
        return  response()->json($result);
    }

    public function resendVerificationEmail($requestData)
    {
        $user = User::find($requestData['id']);
        if ($user) {
            $user->sendEmailVerificationNotification();
            activity()
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_email' => auth()->guard('admin')->user()->email,
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'user_id' => auth()->guard('admin')->user()->id,
                    'send_to' => $user->id,
                    'receiver_email' => $user->email,
                    'remark' => 'Client Email Confirmation'
                ])
                ->event('update')
                ->log('Email Confirmation');
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'User not found'];
        }
    }

    public function getPermissions(Request $request)
    {



        // Base query

        $rmCondition = Permission::orderBy('name', 'asc');



        // dd($query);
        if ($request->ajax()) {
            return DataTables::of($rmCondition)
                ->addColumn('action', function ($row) {
                    return "<a href='/admin/trading_withdrawal_details?id={$row->id}' class=' style='font-size: 13px;padding: 2px 20px;'><i class='fe fe-eye fs-14 text-info'></i></a>";
                })
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }
    public function getListOfGroups($string)
    {

        $sql = "SELECT account_types.ac_index as id,account_types.ac_group as text from account_types where account_types.ac_group like '%$string%' and status = 1";
        $query = DB::select($sql);
        $results = $query;
        return $results;
    }
    public function getListOfUsers($string)
    {

        $sql = "SELECT aspnetusers.email as id,concat(aspnetusers.fullname,' [',aspnetusers.email,']') as text from aspnetusers where (aspnetusers.email like '%$string%' OR aspnetusers.fullname like '%$string%') and status = 1";
        $query = DB::select($sql);
        $results = $query;
        return $results;
    }
    public function getListOfIBs($string)
    {

        $sql = "SELECT aspnetusers.email as id,concat(aspnetusers.fullname,' [',aspnetusers.email,']') as text from aspnetusers
  join ib1 on ib1.email = aspnetusers.email
  where (aspnetusers.email like '%$string%' OR aspnetusers.fullname like '%$string%') and aspnetusers.status = 1 and ib1.status = 1";
        $query = DB::select($sql);
        $results = $query;
        return $results;
    }

    public function getClientSwitch(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'client_id' => 'required', // Ensure the ID is valid and exists in the users table
        ]);

        $clientId = $validated['client_id'];

        $admin = EmployeeList::where('id', $request->admin_user['id'])->first();

        try {
            $admin = Auth::guard('admin')->user();

            // Find the user to impersonate
            $client = User::findOrFail($clientId);
            Gate::forUser($admin)->authorize('client:impersonate', $client);

            activity()
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_email' => auth()->guard('admin')->user()->email,
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'user_id' => auth()->guard('admin')->user()->id,
                    'client_email' => $client->email,
                    'client_user_id' => $client->id,
                    'remark' => 'Switch To User'
                ])
                ->event('update')
                ->log('Switch To User');
            // Log in as the new user
            Auth::guard('web')->login($client);
            Session::put('admin', $admin);
            Session::put('user', $client);

            // Bypass 2FA verification when admin impersonates a client
            Session::put('2fa:verified', true);

            // dd('ssss');
            return response()->json([
                'success' => true,
                'message' => 'Logged in successfully.',
                'redirectUrl' => url('/dashboard'), // Include the redirect URL
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to impersonate the user. Please try again.' . $e->getMessage(),
            ], 500);
        }
    }
    public function switchToAdmin(Request $request)
    {
        if (Auth::user() instanceof User && Session::has('admin')) {
            $admin = Session::get('admin');
            Session::forget('user');
            Auth::logout();
            // $request->session()->invalidate();
            // $request->session()->regenerateToken();
            Auth::guard('admin')->loginUsingId($admin->id);

            return redirect()->route('admin.dashboard');
            // Auth::logout();
            // $request->session()->invalidate();
            // $request->session()->regenerateToken();
            // Session::forget('admin');
        } else {
            return redirect()->route('admin.login');
        }
    }

    public function getClientList(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        // dd($admin);

        $query = User::with([
            'ib',
            'countryDetail',
            'employee' => function ($query) use ($admin) {
                if ($admin->userRole === 'Relationship Manager') {
                    $query->wherePivot('rm_id', $admin->id); // Ensure it filters by rm_id
                }
                $query->select('emplist.id', 'username');
            }
        ])
            ->select([
                'aspnetusers.id',
                'aspnetusers.email',
                'aspnetusers.fullname',
                'aspnetusers.number',
                'aspnetusers.ib1',
                'aspnetusers.status',
                'aspnetusers.email_confirmed',
                'aspnetusers.country',
                'aspnetusers.kyc_verify',
                'aspnetusers.country_code',
                'aspnetusers.two_factor_secret',
                'aspnetusers.two_factor_confirmed_at',
                'aspnetusers.created_at',
            ])
            ->when($admin->userRole === 'Relationship Manager', function ($q) use ($admin) {
                // Ensure that only users linked to the admin's rm_id are retrieved
                $q->whereHas('employee', function ($query) use ($admin) {
                    $query->where('relationship_manager.rm_id', $admin->id);
                });
            })
            ->groupBy('aspnetusers.email');

        // $admin=Auth::guard('admin')->user();
        // dd($admin);

        // $query = User::with([
        //     'ib',
        //     'countryDetail',
        //     'employee' => function ($query) {
        //         $query->select('emplist.id', 'username');
        //     }
        // ])
        // ->select([
        //     'aspnetusers.id',
        //     'aspnetusers.email',
        //     'aspnetusers.fullname',
        //     'aspnetusers.number',
        //     'aspnetusers.ib1',
        //     'aspnetusers.status',
        //     'aspnetusers.email_confirmed',
        //     'aspnetusers.country',
        //     'aspnetusers.kyc_verify',
        //     'aspnetusers.country_code',
        // ])->groupBy('aspnetusers.email');


        // $query = DB::table('aspnetusers AS ap')
        //     ->leftJoin('ib1', 'ib1.user_id', '=', 'ap.id')
        //     ->leftJoin('ib1 AS ibs', 'ibs.user_id', '=', 'ap.id')
        //     ->leftJoin('relationship_manager AS rm', 'ap.id', '=', 'rm.user_id')
        //     ->leftJoin('ib_plan_details AS ibpd', 'ibpd.id', '=', 'ib1.ib_plan_details_id')
        //     ->leftJoin('emplist AS emp', 'rm.rm_id', '=', 'emp.email')
        //     ->leftJoin('countries AS c', 'ap.country', '=', 'c.country_name')
        //     ->leftJoin('ib1 AS parent_ib', function ($join) {
        //         $join->on('parent_ib.referral_code', '=', 'ap.ib1')
        //             ->orOn('parent_ib.email', '=', 'ap.ib1');
        //     })
        //     ->select([
        //         'ibs.name AS ib_name',
        //         'c.country_alpha',
        //         'emp.username AS rm_name',
        //         'rm.rm_id',
        //         'ap.id AS enc_id',
        //         'ap.*',
        //         'ib1.status AS ib_status',
        //         'ibpd.id AS ib_group',
        //         'parent_ib.name AS parent_name',
        //         'parent_ib.email AS parent_email'
        //     ])
        //     ->groupBy('ap.email');

        // $query->when(session('userData')['userRole'] != "Super Admin", function ($query) {
        // $query->leftJoin('aspnetusers AS user', 'user.email', '=', 'ap.email');
        // });

        // if (session('userData')['userRole'] == "Relationship Manager") {
        //     $query->where('rm.rm_id', session('alogin'));
        // }

        // if ($request->has('rm_id') && !empty($request->get('rm_id'))) {
        //     $query->where('rm.rm_id', $request->get('rm_id'));
        // }

        if ($request->ajax()) {
            return DataTables::of($query)
                ->editColumn('created_at', function ($row) {
                    $createdAt = Carbon::parse($row->created_at)->addHours(3);
                    return "<div class='d-grid'>
                            <div class='date'>{$createdAt->format('Y-m-d')}</div>
                            <div class='time text-muted'>{$createdAt->format('H:i:s')}</div>
                        </div>";
                })
                ->editColumn('user_email', function ($row) {
                    return "<a href='/admin/client_details/{$row->id}'>
                            <div class='d-flex align-items-center'>
                                <div class='me-2'>
                                    <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                </div>
                                <div>
                                    <div class='lh-1'><span>{$row->fullname}</span></div>
                                    <div class='lh-1'><span class='fs-11 text-muted'>{$row->email}</span></div>
                                </div>
                            </div>
                        </a>";
                })
                ->editColumn('user_country', function ($row) {
                    $countryAlpha = strtolower($row->countryDetail ? $row->countryDetail->country_alpha : '');
                    return $countryAlpha ? "<span class='fi fis fi-{$countryAlpha}'></span> {$row->countryDetail->country_alpha}" : '-';
                })
                ->editColumn('phone', function ($row) {
                    return $row->number ?? '-';
                })

                ->editColumn('ib_name', function ($row) {
                    return $row->getParentIb() ? $row->getParentIb()->name : '-';
                })
                ->editColumn('ib_email', function ($row) {
                    return $row->getParentIb() ? $row->getParentIb()->email : '-';
                })
                ->editColumn('ib_status', function ($row) {
                    return $row->ib ? $row->ib->status : '';
                })
                ->editColumn('ib_group', function ($row) {
                    return $row->ib ? $row->ib->ib_plan_details_id : '';
                })
                ->addColumn('ib_id', function ($row) {
                    return $row->ib ? $row->ib->id : '';
                })
                ->editColumn('ib', function ($row) {
                    $ib_name = $row->getParentIb() ? $row->getParentIb()->name : 'noIB';
                    $ib_email  = $row->getParentIb() ? $row->getParentIb()->email : '';
                    $svg = $ib_name !== 'noIB' ? "<div class='me-2'>
                                <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='icon icon-tabler icons-tabler-outline icon-tabler-user-pentagon text-dark'>
                                    <path stroke='none' d='M0 0h24v24H0z' fill='none'></path>
                                    <path d='M13.163 2.168l8.021 5.828c.694 .504 .984 1.397 .719 2.212l-3.064 9.43a1.978 1.978 0 0 1 -1.881 1.367h-9.916a1.978 1.978 0 0 1 -1.881 -1.367l-3.064 -9.43a1.978 1.978 0 0 1 .719 -2.212l8.021 -5.828a1.978 1.978 0 0 1 2.326 0z'></path>
                                    <path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path>
                                    <path d='M6 20.703v-.703a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.707'></path>
                                </svg>
                            </div>" : '';
                    return "<div class='cursor-pointer updateIb d-flex align-items-center'>
                            <div class='me-2'>
                            {$svg}
                            </div>
                            <div>
                                <div class='lh-1'><span>{$ib_name}</span></div>
                                <div class='lh-1'><span class='fs-11 text-muted'>{$ib_email}</span></div>
                            </div>
                        </div>";
                })
                ->editColumn('user_ib_status', function ($row) {
                    $count = $row->ib ? $row->ib->status : 3;
                    switch ($count) {
                        case 1:
                            return "<button class='ibToggle badge btn-sm btn btn-outline-success'>Active IB</button>";
                        case 2:
                            return "<button class='ibToggle badge btn-sm btn btn-outline-danger'>Rejected</button>";
                        case 0:
                            return "<button class='ibToggle badge btn-sm btn btn-outline-info'>IB Requested</button>";
                        default:
                            return "<button class='ibToggle badge btn-sm btn btn-outline-primary'>Not Requested</button>";
                    }
                })
                ->editColumn('rm', function ($row) {
                    return ($row->employee && $row->employee->first()) ? "<span class='text-primary'>{$row->employee->first()->username}</span>" : "<button class='rmToggle badge btn-sm btn btn-outline-dark'>RM Not Mapped</button>";
                })
                ->editColumn('user_status', function ($row) {
                    return $row->status == 1 ?
                        "<span class='badge text-success'>Active User</span>" :
                        "<span class='badge text-danger'>Inactive User</span>";
                })
                ->editColumn('user_email_confirmed', function ($row) {
                    return $row->email_confirmed ?
                        "<span class='badge text-success'>Email Verified</span>" :
                        "<span class='badge text-danger'>Email Not Verified</span>";
                })
                ->addColumn('action', function ($row) {
                    $html = '';

                    $success = '';
                    if (intval($row->kyc_verify) >= 1) {
                        $success = ($row->status == 0) ? 'bg-success' : 'bg-success text-white';
                    }
                    // if (Auth::guard('admin')->user()->can('update', $row)) {
                    $html .= "<span class='statusToggle' data-status='{$row->status}'>";
                    if ($row->status == 0) {
                        $html .= "<span class='badge text-danger {$success}' data-bs-toggle='tooltip' title='Inactive User'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='25' class='tabler-icon tabler-icon-user-scan'>
                                            <path d='M10 9a2 2 0 1 0 4 0a2 2 0 0 0 -4 0'></path>
                                            <path d='M4 8v-2a2 2 0 0 1 2 -2h2'></path>
                                            <path d='M4 16v2a2 2 0 0 0 2 2h2'></path>
                                            <path d='M16 4h2a2 2 0 0 1 2 2v2'></path>
                                            <path d='M16 20h2a2 2 0 0 0 2 -2v-2'></path>
                                            <path d='M8 16a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2'></path>
                                        </svg>
                                    </span>";
                    } elseif ($row->status == 1) {
                        $html .= "<span class='badge text-success {$success}' data-bs-toggle='tooltip' title='Active User'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='25' class='tabler-icon tabler-icon-user-scan'>
                                            <path d='M10 9a2 2 0 1 0 4 0a2 2 0 0 0 -4 0'></path>
                                            <path d='M4 8v-2a2 2 0 0 1 2 -2h2'></path>
                                            <path d='M4 16v2a2 2 0 0 0 2 2h2'></path>
                                            <path d='M16 4h2a2 2 0 0 1 2 2v2'></path>
                                            <path d='M16 20h2a2 2 0 0 0 2 -2v-2'></path>
                                            <path d='M8 16a2 2 0 0 1 2 -2h4a4 4 0 0 1 2 2'></path>
                                        </svg>
                                    </span>";
                    }
                    $html .= "</span>";
                    // }
                    if ($row->email_confirmed == 0) {
                        $html .= "<span class='resendToggle' data-status='{$row->email_confirmed}'>";
                        $html .= "<span class='badge text-danger' data-bs-toggle='tooltip' title='Email Not Verified'>
                                    <svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='0 0 24 24' fill='none' stroke='#FFCC80' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='25' class='tabler-icon tabler-icon-mail-x'>
                                        <path d='M13.5 19h-8.5a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v6'></path>
                                        <path d='M3 7l9 6l9 -6'></path>
                                        <path d='M22 22l-5 -5'></path>
                                        <path d='M17 22l5 -5'></path>
                                    </svg>
                                  </span>";
                        $html .= "<span class='badge text-info pointer resendVerificationEmail' data-bs-toggle='tooltip' title='Resend Verification Email'>
                                   <svg class='w-64 h-64' fill='currentColor' width='25' height='25' xmlns='http://www.w3.org/2000/svg' id='mdi-email-sync-outline' viewBox='0 0 24 24'><path d='M3 4C1.9 4 1 4.9 1 6V18C1 19.1 1.9 20 3 20H13.5A6.5 6.5 0 0 1 13 18H3V8L11 13L19 8V11A6.5 6.5 0 0 1 19.5 11A6.5 6.5 0 0 1 21 11.18V6C21 4.9 20.1 4 19 4H3M3 6H19L11 11L3 6M19 12L16.75 14.25L19 16.5V15C20.38 15 21.5 16.12 21.5 17.5C21.5 17.9 21.41 18.28 21.24 18.62L22.33 19.71C22.75 19.08 23 18.32 23 17.5C23 15.29 21.21 13.5 19 13.5V12M15.67 15.29C15.25 15.92 15 16.68 15 17.5C15 19.71 16.79 21.5 19 21.5V23L21.25 20.75L19 18.5V20C17.62 20 16.5 18.88 16.5 17.5C16.5 17.1 16.59 16.72 16.76 16.38L15.67 15.29Z'></path></svg>
                                  </span>";
                    } else {
                        $html .= "<span class='statusToggle' data-status='{$row->email_confirmed}'>";
                        $html .= "<span class='badge text-success' data-bs-toggle='tooltip' title='Email Verified'>
                                    <svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='0 0 24 24' fill='none' stroke='#81C784' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='25' color='#81C784' class='tabler-icon tabler-icon-mail-check'>
                                        <path d='M11 19h-6a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v6'></path>
                                        <path d='M3 7l9 6l9 -6'></path>
                                        <path d='M15 19l2 2l4 -4'></path>
                                    </svg>
                                  </span>";
                    }
                    $html .= "</span>";
                    $html .= "<span class='viewClient' data-enc='{$row->id}'>
                                <span class='badge text-danger' data-bs-toggle='tooltip' title='View Client'>
                                    <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='icon icon-tabler icons-tabler-outline icon-tabler-eye'><path stroke='none' d='M0 0h24v24H0z' fill='none' /><path d='M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0' /><path d='M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6' /></svg>
                                </span>
                              </span>";

                    // 2FA Remove Shield Icon - Only show if 2FA is enabled
                    $has2FA = !empty($row->two_factor_secret) && !empty($row->two_factor_confirmed_at);
                    if ($has2FA) {
                        $html .= "<span class='remove2FAIcon' data-user-id='{$row->id}'>
                                    <span class='badge text-danger' data-bs-toggle='tooltip' title='Remove 2FA'>
                                        <i class='ri-shield-cross-line' style='font-size: 20px;'></i>
                                    </span>
                                  </span>";
                    }

                    if (Auth::guard('admin')->user()->can('client:update', $row)) {
                        $html .= "<span class='editClient' data-enc='{$row->id}'>
                                <span class='badge text-secondary' data-bs-toggle='tooltip' title='Edit Client'>
                                    <svg  xmlns='http://www.w3.org/2000/svg'  width='24'  height='24'  viewBox='0 0 24 24'  fill='none'  stroke='currentColor'  stroke-width='2'  stroke-linecap='round'  stroke-linejoin='round'  class='icon icon-tabler icons-tabler-outline icon-tabler-edit text-secondary'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1' /><path d='M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z' /><path d='M16 5l3 3' /></svg>
                                </span>
                              </span>";
                    }
                    if (Auth::guard('admin')->user()->can('client:impersonate', $row)) {
                        $html .= "<span class='switchClient' data-enc='{$row->id}'>
                                <span class='badge text-secondary' data-bs-toggle='tooltip' title='Switch Client'>
                                    <svg xmlns='http://www.w3.org/2000/svg' class='icon icon-tabler icon-tabler-arrows-shuffle' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                                    <path stroke='none' d='M0 0h24v24H0z' fill='none'/>
                                    <path d='M18 4l3 3l-3 3' />
                                    <path d='M18 20l3 -3l-3 -3' />
                                    <path d='M3 7h3a4 4 0 0 1 4 4a4 4 0 0 0 4 4h7' />
                                    <path d='M21 7h-7a4 4 0 0 0 -4 4a4 4 0 0 1 -4 4h-3' />
                                    </svg>
                                </span>
                              </span>";
                    }

                    return $html;
                })
                ->rawColumns(['created_at', 'user_country', 'user_email', 'ib', 'user_ib_status', 'rm', 'user_status', 'user_email_confirmed', 'action', 'ib_name', 'ib_email'])
                ->make(true);
        }

        return response()->json([
            'message' => 'Invalid request',
        ]);
    }

    public function getRequestedAccountsList(Request $request)
    {
        // dump( session('userData'));
        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];
        $userGroups = explode(',', session('user_groups'));
        // dd($alogin);
        // Base query
        $rmCondition = Account::select('accounts.*')
            // ->select('accounts.*')
            ->where('account_request_status', 0)
            ->whereNull('competition_start_date')
            ->whereNull('competition_end_date')
            ->with(['user', 'accountType']);

        if ($role !== "Super Admin") {
            $rmCondition->whereHas('user');
        }

        // if ($role === "Relationship Manager") {
        //     $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
        //         $query->where('rm_id', $alogin);
        //     });
        // }

        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('user.employee', function ($query) use ($alogin) {
                $query->where('relationship_manager.rm_id', $alogin); // Filter based on rm_id in pivot
            });
        }

        $rmCondition->orderBy('id', 'desc');

        if ($request->ajax()) {
            // dd(DataTables::of($rmCondition));
            return DataTables::of($rmCondition)
                ->editColumn('email', function ($row) {
                    $fullname = $row->user
                        ? ($row->user->fullname)
                        : 'Unknown';
                    $email = $row->user ? $row->user->email : 'No Email';
                    return "<a href='/admin/client_details/{$row->user->id}'>
                                <div class='d-flex align-items-center'>
                                    <div class='me-2'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                    </div>
                                    <div>
                                        <div class='lh-1'><span>{$fullname}</span></div>
                                        <div class='lh-1'><span class='fs-11 text-muted'>{$email}</span></div>
                                    </div>
                                </div>
                            </a>";
                })
                ->addColumn('code', function ($row) {
                    $accountGroup = $row->accountType->ac_group;
                    return "<a href='" . (($row->code && $row->code != 'Rejected') ? '/admin/view_account_details/' . $row->id : '#') . "'>
                                <div class='row align-items-center'>
                                    <div class='col-auto pe-0'><img src='/assets/images/mt5.png'
                                            alt='user-image' class='rounded wid-50 hei-50'></div>
                                    <div class='col ps-2'>
                                        <h6 class='mb-0'><span class='text-truncate w-100'>" .
                        ($row->code ? $row->code : 'Pending') .
                        "</span>
                                        </h6>
                                        <p class='mb-0 text-muted f-12'><span
                                                class='text-truncate w-100'> $accountGroup </span>
                                        </p>
                                    </div>
                                </div>
                            </a>";
                })
                // ->addColumn('leverage', function($row){
                //     return $row->leverage;
                // })
                ->addColumn('balance', function ($row) {
                    return $row->balance;
                })
                ->addColumn('created_at', function ($row) {
                    // $date = date('Y-m-d', strtotime($row->created_at));
                    $date = Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                    // $time = date('H:i:s', strtotime($row->created_at));
                    $time = Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('fullname', function ($row) {
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function ($row) {
                    return $row->email;
                })
                ->addColumn('account_code', function ($row) {
                    return $row->code;
                })
                ->addColumn('account_group', function ($row) {
                    return $row->accountType->ac_group;
                })
                ->addColumn('account_request_status', function ($row) {

                    if ($row->account_request_status == 1) {
                        return "<button class=' badge bg-outline-success'>Approved</button>";
                        // }elseif($row->account_request_status == 2){
                        //     return "<button class='ibToggle badge bg-outline-danger'>Rejected</button>";
                    } elseif ($row->account_request_status == 0) {
                        return "<button class='ibToggle badge bg-outline-primary'>Pending</button>";
                    }
                })
                ->editColumn('request_status', function ($row) {
                    return $row->account_request_status;
                })
                ->addColumn('created_date', function ($row) {
                    // return date('Y-m-d', strtotime($row->created_at));
                    return Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    // return date('H:i:s', strtotime($row->created_at));
                    return Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                })
                ->rawColumns(['email', 'code', 'leverage', 'balance', 'created_at', 'fullname', 'fullemail', 'account_request_status', 'request_status'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getLiveAccountsList(Request $request)
    {
        $rmCondition = $this->buildLiveAccountsBaseQuery();
        $this->applyLiveAccountsFilters($rmCondition, $request);

        // Don't set a default order here - let DataTables handle ordering
        // Only apply default order if no order is requested
        if (!$request->has('order') || empty($request->input('order'))) {
            $rmCondition->orderBy('id', 'desc');
        }

        if ($request->ajax()) {
            // dd(DataTables::of($rmCondition));
            return DataTables::of($rmCondition)
                ->filter(function ($rmCondition) use ($request) {
                    if (!empty($request->search['value'])) {
                        $searchValue = $request->search['value'];
                        $rmCondition->where(function ($q) use ($searchValue) {
                            $q->where('accounts.code', 'LIKE', "%{$searchValue}%")
                                ->orWhere('accounts.balance', 'LIKE', "%{$searchValue}%")
                                ->orWhere('accounts.email', 'LIKE', "%{$searchValue}%")
                                // ->orWhere('user.email', 'LIKE', "%{$searchValue}%")
                                ->orWhereRaw("DATE_FORMAT(accounts.created_at, '%Y-%m-%d') LIKE ?", ["%{$searchValue}%"]);
                        });
                    }
                })
                ->editColumn('email', function ($row) {
                    $fullname = $row->user
                        ? ($row->user->fullname)
                        : 'Unknown';
                    $email = $row->user ? $row->user->email : 'No Email';
                    $userId = $row->user ? $row->user->id : '#';
                    return "<a href='/admin/client_details/{$userId}'>
                                <div class='d-flex align-items-center'>
                                    <div class='me-2'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                    </div>
                                    <div>
                                        <div class='lh-1'><span>{$fullname}</span></div>
                                        <div class='lh-1'><span class='fs-11 text-muted'>{$email}</span></div>
                                    </div>
                                </div>
                            </a>";
                })
                ->addColumn('code', function ($row) {
                    $accountGroup = $row->accountType ? $row->accountType->ac_group : 'N/A';

                    // Determine platform image and display name
                    $platformImage = '/assets/images/mt5.png';
                    $platformName = PlatformEnum::MT5->displayName();

                    if ($row->platform === PlatformEnum::X9->value) {
                        $platformImage = '/assets/images/x9.png';
                        $platformName = PlatformEnum::X9->displayName();
                        // For X9 accounts, show the account type name instead of group
                        $accountGroup = $row->accountType->ac_name ?? 'Standard';
                    }

                    return "<a href='/admin/view_account_details/{$row->id}'>
                                <div class='row align-items-center'>
                                    <div class='col-auto pe-0'><img src='{$platformImage}'
                                            alt='{$platformName} platform' class='rounded wid-50 hei-50'></div>
                                    <div class='col ps-2'>
                                        <h6 class='mb-0'><span
                                                class='text-truncate w-100'> {$row->code} </span>
                                        </h6>
                                        <p class='mb-0 text-muted f-12'><span
                                                class='text-truncate w-100'> {$platformName} - {$accountGroup} </span>
                                        </p>
                                    </div>
                                </div>
                            </a>";
                })
                // ->addColumn('leverage', function($row){
                //     return $row->leverage;
                // })
                ->addColumn('balance', function ($row) {
                    return $row->balance;
                })
                ->addColumn('last_trade_date', function ($row) {
                    $lastTradeAt = $row->last_trade_at;
                    if (!$lastTradeAt) {
                        return '—';
                    }

                    return Carbon::parse($lastTradeAt)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('days_since_last_trade', function ($row) {
                    $lastTradeAt = $row->last_trade_at;

                    // Use same source as Last Trade Date: only show days when we have a real last trade timestamp
                    if ($lastTradeAt === null) {
                        return "<span class='text-muted'>No trades</span>";
                    }

                    $days = Carbon::parse($lastTradeAt)->diffInDays(now());
                    $days = max(0, (int) $days);

                    if ($days < 20) {
                        $class = 'text-success fw-medium';
                    } elseif ($days <= 40) {
                        $class = 'text-warning fw-medium';
                    } else {
                        $class = 'text-danger fw-medium';
                    }

                    return "<span class='{$class}'>{$days} days</span>";
                })
                ->addColumn('deposited', function ($row) {
                    $badgeStyle = 'padding:0.35rem 0.5rem';

                    // Check for CryptoChill, CreditCardPayissa, RagaPay deposits (Yes)
                    if ($row->successful_trade_deposits_count > 0) {
                        return "
                            <span class='gap-1 border badge rounded-pill border-success d-inline-flex align-items-center justify-content-center'
                                  style='background-color:rgba(232,252,244,1);{$badgeStyle}'>
                                <svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24'
                                     fill='none' stroke='#00b894' stroke-width='2.4' stroke-linecap='round'
                                     stroke-linejoin='round'>
                                    <polyline points='5 12 10 17 19 7' />
                                </svg>
                                <span class='fw-bold' style='color:#00b894'>Yes</span>
                            </span>
                        ";
                    }

                    // Check for Wallet Transfer deposits
                    if ($row->wallet_deposit_count > 0) {
                        return "
                            <span class='gap-1 border badge rounded-pill border-info d-inline-flex align-items-center justify-content-center'
                                  style='background-color:rgba(23,162,184,0.1);{$badgeStyle}'>
                                <svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24'
                                     fill='none' stroke='#17a2b8' stroke-width='2.4' stroke-linecap='round'
                                     stroke-linejoin='round'>
                                    <circle cx='12' cy='12' r='1' />
                                    <path d='M12 1v6m0 6v6' />
                                    <path d='M4.22 4.22l4.24 4.24m5.08 0l4.24-4.24' />
                                    <path d='M1 12h6m6 0h6' />
                                    <path d='M4.22 19.78l4.24-4.24m5.08 0l4.24 4.24' />
                                </svg>
                                <span class='fw-bold' style='color:#17a2b8'>Wallet Deposit</span>
                            </span>
                        ";
                    }

                    // Check for Internal Transfer deposits
                    if ($row->internal_transfer_count > 0) {
                        return "
                            <span class='gap-1 border badge rounded-pill border-warning d-inline-flex align-items-center justify-content-center'
                                  style='background-color:rgba(255,193,7,0.1);{$badgeStyle}'>
                                <svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24'
                                     fill='none' stroke='#ffc107' stroke-width='2.4' stroke-linecap='round'
                                     stroke-linejoin='round'>
                                    <line x1='12' y1='5' x2='12' y2='19' />
                                    <polyline points='19 12 12 19 5 12' />
                                </svg>
                                <span class='fw-bold' style='color:#ffc107'>Internal Transfer</span>
                            </span>
                        ";
                    }

                    // No deposits
                    return "
                        <span class='gap-1 border badge rounded-pill border-danger d-inline-flex align-items-center justify-content-center'
                              style='background-color:rgba(255,0,0,0.08);{$badgeStyle}'>
                            <svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24'
                                 fill='none' stroke='#dc3545' stroke-width='2' stroke-linecap='round'
                                 stroke-linejoin='round'>
                                <line x1='18' y1='6' x2='6' y2='18' />
                                <line x1='6' y1='6' x2='18' y2='18' />
                            </svg>
                            <span class='fw-bold' style='color:#dc3545'>No</span>
                        </span>
                    ";
                })
                ->addColumn('traded', function ($row) {
                    $hasTrades = $row->last_trade_at !== null || ($row->trades_count ?? 0) > 0;
                    $isNotTraded = !$hasTrades;

                    $badgeStyle = 'padding:0.35rem 0.5rem';
                    if ($isNotTraded) {
                        $orange = 'rgb(247, 86, 49)';
                        return "
                            <span class='gap-1 border border-2 badge rounded-pill d-inline-flex align-items-center justify-content-center' style='background-color:rgba(247,86,49,0.12); border-color:#ff0000a3 !important;{$badgeStyle}'>
                                <svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='{$orange}' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                                    <path stroke='none' d='M0 0h24v24H0z' fill='none'/>
                                    <path d='M12 9v4' />
                                    <path d='M12 17v.01' />
                                    <path d='M5 19h14l-7 -13z' />
                                </svg>
                                <span class='fw-bold' style='color:{$orange}'>No</span>
                            </span>
                        ";
                    }

                    return "
                        <span class='gap-1 border badge rounded-pill border-success d-inline-flex align-items-center justify-content-center'
                              style='background-color:rgba(232,252,244,1);{$badgeStyle}'>
                            <svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24'
                                 fill='none' stroke='#00b894' stroke-width='2.4' stroke-linecap='round'
                                 stroke-linejoin='round'>
                                <polyline points='5 12 10 17 19 7' />
                            </svg>
                            <span class='fw-bold' style='color:#00b894'>Yes</span>
                        </span>
                    ";
                })
                ->addColumn('created_at', function ($row) {
                    // $date = date('Y-m-d', strtotime($row->created_at));
                    $date = Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                    // $time = date('H:i:s', strtotime($row->created_at));
                    $time = Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('total_deposit', function ($row) {
                    return number_format($row->tradeDeposits()->where('status', 1)->whereIn('deposit_type', ['CryptoChill', 'CreditCardPayissa', 'RagaPay'])->sum('deposit_amount'), 2);
                })
                ->addColumn('total_withdraw', function ($row) {
                    return number_format($row->tradeWithdrawals()->where('status', 1)->whereIn('withdraw_type', ['Trade Withdrawal'])->sum(DB::raw('transaction_fee + withdrawal_amount')), 2);
                })
                ->addColumn('fullname', function ($row) {
                    return $row->user ? $row->user->fullname : 'Unknown';
                })
                ->addColumn('fullemail', function ($row) {
                    return $row->email;
                })
                ->addColumn('account_code', function ($row) {
                    return $row->code;
                })
                ->addColumn('user_country', function ($row) {
                    return $row->user ? $row->user->country : '';
                })
                ->addColumn('account_group', function ($row) {
                    return $row->accountType ? $row->accountType->ac_group : 'N/A';
                })
                ->addColumn('account_status', function ($row) {
                    return $row->deleted_at ? "<button class=' badge bg-outline-danger'>Deleted</button>" : "<button class=' badge bg-outline-success'>Active</button>";
                })
                ->addColumn('actions', function ($row) {
                    // If account is deleted, return empty string
                    if ($row->deleted_at) {
                        return '';
                    }

                    // Otherwise show delete button
                    $html = "<a class='deleteAcc statusToggle' data-bs-toggle='tooltip' data-enc='{$row->id}'>
                        <span class='badge text-danger'>
                            <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='icon icon-tabler icons-tabler-outline icon-tabler-trash'>
                                <path stroke='none' d='M0 0h24v24H0z' fill='none'/>
                                <path d='M4 7l16 0'/>
                                <path d='M10 11l0 6'/>
                                <path d='M14 11l0 6'/>
                                <path d='M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12'/>
                                <path d='M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3'/>
                            </svg>
                        </span>
                    </a>";

                    return $html;
                })
                ->addColumn('created_date', function ($row) {
                    // return date('Y-m-d', strtotime($row->created_at));
                    return Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    // return date('H:i:s', strtotime($row->created_at));
                    return Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                })
                ->orderColumn('last_trade_date', function ($query, $order) {
                    $query->orderByRaw('CASE WHEN accounts.last_trade_at IS NULL THEN 1 ELSE 0 END ' . $order)
                        ->orderBy('accounts.last_trade_at', $order);
                })
                ->orderColumn('days_since_last_trade', function ($query, $order) {
                    if (strtolower($order) === 'asc') {
                        $query->orderByRaw('CASE WHEN accounts.last_trade_at IS NULL THEN 1 ELSE 0 END ASC')
                            ->orderBy('accounts.last_trade_at', 'DESC');
                    } else {
                        $query->orderByRaw('CASE WHEN accounts.last_trade_at IS NULL THEN 1 ELSE 0 END DESC')
                            ->orderBy('accounts.last_trade_at', 'ASC');
                    }
                })
                ->orderColumn('deposited_not_traded', function ($query, $order) {
                    $query->orderByRaw("
                        CASE
                            WHEN successful_trade_deposits_count > 0 AND accounts.last_trade_at IS NULL THEN 0
                            ELSE 1
                        END {$order}
                    ");
                })
                ->orderColumn('created_at', function ($query, $order) {
                    $query->orderBy('accounts.created_at', $order);
                })
                ->orderColumn('balance', function ($query, $order) {
                    $query->orderBy('accounts.balance', $order);
                })
                ->orderColumn('leverage', function ($query, $order) {
                    $query->orderBy('accounts.leverage', $order);
                })
                ->orderColumn('email', function ($query, $order) {
                    $query->orderBy('accounts.email', $order);
                })
                ->orderColumn('code', function ($query, $order) {
                    $query->orderBy('accounts.code', $order);
                })
                ->orderColumn('deposited', function ($query, $order) {
                    $query->orderBy('successful_trade_deposits_count', $order);
                })
                ->orderColumn('traded', function ($query, $order) {
                    $query->orderByRaw('CASE WHEN accounts.last_trade_at IS NULL THEN 1 ELSE 0 END ' . $order)
                        ->orderBy('accounts.last_trade_at', $order);
                })
                ->orderColumn('account_status', function ($query, $order) {
                    $query->orderByRaw('CASE WHEN accounts.deleted_at IS NULL THEN 0 ELSE 1 END ' . $order)
                        ->orderBy('accounts.deleted_at', $order);
                })
                ->orderColumn('total_deposit', function ($query, $order) {
                    $query->orderByRaw('(SELECT SUM(deposit_amount) FROM trade_deposits WHERE account_id = accounts.id AND status = 1 AND deposit_type IN ("CryptoChill", "CreditCardPayissa", "RagaPay")) ' . $order);
                })
                ->orderColumn('total_withdraw', function ($query, $order) {
                    $query->orderByRaw('(SELECT SUM(transaction_fee + withdrawal_amount) FROM trade_withdrawal WHERE account_id = accounts.id AND status = 1 AND withdraw_type IN ("Trade Withdrawal")) ' . $order);
                })
                ->rawColumns(['email', 'code', 'leverage', 'balance', 'last_trade_date', 'days_since_last_trade', 'deposited_not_traded', 'created_at', 'fullname', 'fullemail', 'account_status', 'actions', 'deposited','traded', 'user_country'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    private function buildLiveAccountsBaseQuery()
    {
        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];

        $query = Account::query()
            ->where('demo', false)
            ->select('accounts.*')
            ->withTrashed()
            ->where('account_request_status', 1)
            ->with(['user', 'accountType'])
            ->withCount([
                'tradeDeposits as successful_trade_deposits_count' => function ($q) {
                    $q->where('status', 1)->whereIn('deposit_type', ['CryptoChill', 'CreditCardPayissa', 'RagaPay']);
                },
                'tradeDeposits as wallet_deposit_count' => function ($q) {
                    $q->where('status', 1)->where('deposit_type', 'Wallet Transfer');
                },
                'tradeDeposits as internal_transfer_count' => function ($q) {
                    $q->where('status', 1)->where('deposit_type', 'Internal Transfer');
                },
                'trades as trades_count',
            ]);

        if ($role !== "Super Admin") {
            $query->whereHas('user');
        }

        if ($role === "Relationship Manager") {
            $query->whereHas('user.employee', function ($q) use ($alogin) {
                $q->where('relationship_manager.rm_id', $alogin);
            });
        }

        return $query;
    }

    private function applyLiveAccountsFilters($query, Request $request): void
    {
        $status = $request->get('filter_status');
        if ($status === 'active') {
            $query->whereNull('accounts.deleted_at');
        } elseif ($status === 'deleted') {
            $query->whereNotNull('accounts.deleted_at');
        }

        $leverage = $request->get('filter_leverage');
        if ($leverage !== null && $leverage !== '') {
            $query->where('accounts.leverage', $leverage);
        }

        $balanceMin = $request->get('balance_min');
        if ($balanceMin !== null && $balanceMin !== '' && is_numeric($balanceMin)) {
            $query->where('accounts.balance', '>=', (float) $balanceMin);
        }

        $balanceMax = $request->get('balance_max');
        if ($balanceMax !== null && $balanceMax !== '' && is_numeric($balanceMax)) {
            $query->where('accounts.balance', '<=', (float) $balanceMax);
        }

        $hasBalance = $request->get('has_balance');
        if ($hasBalance === 'yes') {
            $query->where('accounts.balance', '>', 0);
        } elseif ($hasBalance === 'no') {
            $query->where('accounts.balance', '<=', 0);
        }

        $registeredFrom = $request->get('registered_from');
        if (!empty($registeredFrom)) {
            $query->whereDate('accounts.created_at', '>=', $registeredFrom);
        }

        $registeredTo = $request->get('registered_to');
        if (!empty($registeredTo)) {
            $query->whereDate('accounts.created_at', '<=', $registeredTo);
        }

        $daysMin = $request->get('days_since_last_trade_min');
        if ($daysMin !== null && $daysMin !== '' && is_numeric($daysMin)) {
            $query->whereNotNull('accounts.last_trade_at')
                ->whereRaw('DATEDIFF(CURDATE(), DATE(accounts.last_trade_at)) >= ?', [(int) $daysMin]);
        }

        $daysMax = $request->get('days_since_last_trade_max');
        if ($daysMax !== null && $daysMax !== '' && is_numeric($daysMax)) {
            $query->whereNotNull('accounts.last_trade_at')
                ->whereRaw('DATEDIFF(CURDATE(), DATE(accounts.last_trade_at)) <= ?', [(int) $daysMax]);
        }

        $activityStatus = $request->get('activity_status');
        if (!empty($activityStatus)) {
            if ($activityStatus === 'no_trades') {
                $query->whereNull('accounts.last_trade_at');
            } elseif ($activityStatus === 'lt_20') {
                $query->whereNotNull('accounts.last_trade_at')
                    ->whereRaw('DATEDIFF(CURDATE(), DATE(accounts.last_trade_at)) < 20');
            } elseif ($activityStatus === 'between_20_40') {
                $query->whereNotNull('accounts.last_trade_at')
                    ->whereRaw('DATEDIFF(CURDATE(), DATE(accounts.last_trade_at)) BETWEEN 20 AND 40');
            } elseif ($activityStatus === 'gt_40') {
                $query->whereNotNull('accounts.last_trade_at')
                    ->whereRaw('DATEDIFF(CURDATE(), DATE(accounts.last_trade_at)) > 40');
            }
        }

        $hasSuccessfulDepositSql = "EXISTS (
            SELECT 1
            FROM trade_deposits td
            WHERE td.account_id = accounts.id
              AND td.status = 1
              AND td.deposit_type IN ('CryptoChill', 'CreditCardPayissa', 'RagaPay')
              AND td.deleted_at IS NULL
        )";

        $hasSuccessfulWAlletDepositSql = "EXISTS (
            SELECT 1
            FROM trade_deposits td
            WHERE td.account_id = accounts.id
              AND td.status = 1
              AND td.deposit_type ='Wallet Transfer'
              AND td.deposit_from = 'Wallet Transfer'
              AND td.deleted_at IS NULL
        )";

        $hasSuccessfulInternalDepositSql = "EXISTS (
            SELECT 1
            FROM trade_deposits td
            WHERE td.account_id = accounts.id
              AND td.status = 1
              AND td.deposit_type ='Internal Transfer'
              AND td.deleted_at IS NULL
        )";

        $hasTradesSql = "(accounts.last_trade_at IS NOT NULL OR EXISTS (
            SELECT 1
            FROM trades tr
            WHERE tr.account_id = accounts.id
              AND tr.deleted_at IS NULL
        ))";

        // Separate Deposited filter
        $deposited = $request->get('deposited');
        if ($deposited === 'yes') {
            $query->whereRaw($hasSuccessfulDepositSql);
        } elseif ($deposited === 'no') {
            $query->whereRaw("NOT {$hasSuccessfulDepositSql}");
        } elseif ($deposited === 'wallet_deposit') {
            $query->whereRaw($hasSuccessfulWAlletDepositSql);
        } elseif ($deposited === 'internal_transfer') {
            $query->whereRaw($hasSuccessfulInternalDepositSql);
        }
        

        // Separate Not Traded filter
        $Traded = $request->get('traded');
        if ($Traded === 'yes') {
            $query->whereRaw($hasTradesSql);
        } elseif ($Traded === 'no') {
            $query->whereRaw("NOT {$hasTradesSql}");
        }
    }

    public function getDemoAccountsList(Request $request)
    {
        $role = session('userData')['userRole'];
        // $alogin = session('alogin');
        $alogin = session('userData')['id'];
        $userGroups = explode(',', session('user_groups'));

        // Base query
        $rmCondition = Account::where('demo', true)
            ->select('accounts.*')
            ->where('account_request_status', 1)
            ->with(['user', 'accountType']);

        // Apply platform filter
        if ($request->has('platform') && !empty($request->platform)) {
            $rmCondition->where('platform', $request->platform);
        }

        if ($role !== "Super Admin") {
            $rmCondition->whereHas('user');
        }

        // if ($role === "Relationship Manager") {
        //     $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
        //         $query->where('rm_id', $alogin);
        //     });
        // }

        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('user.employee', function ($query) use ($alogin) {
                $query->where('relationship_manager.rm_id', $alogin); // Filter based on rm_id in pivot
            });
        }

        $rmCondition->orderBy('id', 'desc');


        if ($request->ajax()) {
            return DataTables::of($rmCondition)
                ->filter(function ($rmCondition) use ($request) {
                    if (!empty($request->search['value'])) {
                        $searchValue = $request->search['value'];
                        $rmCondition->where(function ($q) use ($searchValue) {
                            $q->where('accounts.code', 'LIKE', "%{$searchValue}%")
                                ->orWhere('accounts.balance', 'LIKE', "%{$searchValue}%")
                                ->orWhere('accounts.email', 'LIKE', "%{$searchValue}%")
                                // ->orWhere('user.email', 'LIKE', "%{$searchValue}%")
                                ->orWhereRaw("DATE_FORMAT(accounts.created_at, '%Y-%m-%d') LIKE ?", ["%{$searchValue}%"]);
                        });
                    }
                })
                ->editColumn('email', function ($row) {
                    $fullname = $row->user
                        ? ($row->user->fullname)
                        : 'Unknown';
                    $email = $row->user ? $row->user->email : 'No Email';
                    return "<a href='/admin/client_details/{$row->user->id}'>
                                <div class='d-flex align-items-center'>
                                    <div class='me-2'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                    </div>
                                    <div>
                                        <div class='lh-1'><span>{$fullname}</span></div>
                                        <div class='lh-1'><span class='fs-11 text-muted'>{$email}</span></div>
                                    </div>
                                </div>
                            </a>";
                })
                ->addColumn('code', function ($row) {
                    $accountGroup = $row->accountType->ac_group;

                    // Determine platform image and display name
                    $platformImage = '/assets/images/mt5.png';
                    $platformName = PlatformEnum::MT5->displayName();

                    if ($row->platform === PlatformEnum::X9->value) {
                        $platformImage = '/assets/images/x9.png';
                        $platformName = PlatformEnum::X9->displayName();
                        // For X9 accounts, show the account type name instead of group
                        $accountGroup = $row->accountType->ac_name ?? 'Standard';
                    }

                    return "<a href='/admin/view_account_details/{$row->id}'>
                                <div class='row align-items-center'>
                                    <div class='col-auto pe-0'><img src='{$platformImage}'
                                            alt='{$platformName} platform' class='rounded wid-50 hei-50'></div>
                                    <div class='col ps-2'>
                                        <h6 class='mb-0'><span
                                                class='text-truncate w-100'> {$row->code} </span>
                                        </h6>
                                        <p class='mb-0 text-muted f-12'><span
                                                class='text-truncate w-100'> {$platformName} - {$accountGroup} </span>
                                        </p>
                                    </div>
                                </div>
                            </a>";
                })
                ->addColumn('leverage', function ($row) {
                    return $row->leverage;
                })
                ->addColumn('balance', function ($row) {
                    return $row->balance;
                })
                ->addColumn('created_at', function ($row) {
                    // $date = date('Y-m-d', strtotime($row->created_at));
                    $date = Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                    // $time = date('H:i:s', strtotime($row->created_at));
                    $time = Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('fullname', function ($row) {
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function ($row) {
                    return $row->email;
                })
                ->addColumn('account_code', function ($row) {
                    return $row->code;
                })
                ->addColumn('account_group', function ($row) {
                    return $row->accountType->ac_group;
                })
                ->addColumn('created_date', function ($row) {
                    // return date('Y-m-d', strtotime($row->created_at));
                    return Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    // return date('H:i:s', strtotime($row->created_at));
                    return Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                })
                ->orderColumn('email', function ($query, $order) {
                    $query->orderBy('accounts.email', $order);
                })
                ->orderColumn('code', function ($query, $order) {
                    $query->orderBy('accounts.code', $order);
                })
                ->orderColumn('leverage', function ($query, $order) {
                    $query->orderBy('accounts.leverage', $order);
                })
                ->orderColumn('balance', function ($query, $order) {
                    $query->orderBy('accounts.balance', $order);
                })
                ->orderColumn('created_at', function ($query, $order) {
                    $query->orderBy('accounts.created_at', $order);
                })
                ->rawColumns(['email', 'code', 'leverage', 'balance', 'created_at', 'fullname', 'fullemail'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getWalletDeposit2(Request $request)
    {
        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];

        // Base query
        $rmCondition = WalletDeposit::where('deposit_type', '!=', 'Internal Transfer')
            ->select('wallet_deposit.*')
            ->with(['user']);


        // if ($role === "Relationship Manager") {
        //     $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
        //         $query->where('rm_id', $alogin);
        //     });
        // }

        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('user.employee', function ($query) use ($alogin) {
                $query->where('relationship_manager.rm_id', $alogin); // Filter based on rm_id in pivot
            });
        }

        if (isset($request->status)) {
            $rmCondition->where('status', $request->status);
        }

        // $rmCondition->orderBy('id', 'desc');

        if ($request->ajax()) {
            return DataTables::of($rmCondition)
                ->filter(function ($rmCondition) use ($request) {
                    if (!empty($request->search['value'])) {
                        $searchValue = $request->search['value'];
                        $rmCondition->where(function ($q) use ($searchValue) {
                            $q->where('deposit_amount', 'LIKE', "%{$searchValue}%")
                                ->orWhere('deposit_type', 'LIKE', "%{$searchValue}%")
                                ->orWhere('email', 'LIKE', "%{$searchValue}%")
                                ->orWhereRaw("DATE_FORMAT(deposted_date, '%Y-%m-%d') LIKE ?", ["%{$searchValue}%"]);
                        });
                    }
                })
                ->orderColumn('amount', function ($query, $order) {
                    $query->orderBy('wallet_deposit.deposit_amount', $order);
                })
                ->orderColumn('payment_mode', function ($query, $order) {
                    $query->orderBy('wallet_deposit.deposit_type', $order);
                })
                ->orderColumn('deposit_date', function ($query, $order) {
                    $query->orderBy('wallet_deposit.deposted_date', $order);
                })
                ->orderColumn('status', function ($query, $order) {
                    $query->orderBy('wallet_deposit.status', $order);
                })
                ->editColumn('email', function ($row) {
                    $fullname = $row->user
                        ? ($row->user->fullname)
                        : 'Unknown';
                    $email = $row->user ? $row->user->email : 'No Email';
                    return "<a href='/admin/client_details/{$row->user->id}'>
                                <div class='d-flex align-items-center'>
                                    <div class='me-2'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                    </div>
                                    <div>
                                        <div class='lh-1'><span>{$fullname}</span></div>
                                        <div class='lh-1'><span class='fs-11 text-muted'>{$email}</span></div>
                                    </div>
                                </div>
                            </a>";
                })
                ->addColumn('amount', function ($row) {
                    return $row->deposit_amount;
                })
                ->addColumn('payment_mode', function ($row) {
                    return $row->deposit_type;
                })
                ->addColumn('deposit_date', function ($row) {
                    // $date = date('Y-m-d', strtotime($row->deposted_date));
                    $date = Carbon::parse($row->deposted_date)->addHours(3)->format('Y-m-d');
                    // $time = date('H:i:s', strtotime($row->deposted_date));
                    $time = Carbon::parse($row->deposted_date)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    } elseif ($row->status == 2) {
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    } else {
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function ($row) {
                    return "<a class='btn btn-sm btn-primary' href='/admin/wallet_deposit_details?id={$row->id}'>View</a>";
                })
                ->addColumn('fullname', function ($row) {
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function ($row) {
                    return $row->user->email;
                })
                ->addColumn('created_date', function ($row) {
                    // return date('Y-m-d', strtotime($row->deposted_date));
                    return Carbon::parse($row->deposted_date)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    // return date('H:i:s', strtotime($row->deposted_date));
                    return Carbon::parse($row->deposted_date)->addHours(3)->format('H:i:s');
                })
                ->rawColumns(['email', 'amount', 'payment_mode', 'deposit_date', 'status', 'action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getWalletWithdrawal2(Request $request)
    {
        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];
        $userGroups = explode(',', session('user_groups'));

        // Base query
        $rmCondition = WalletWithdraw::where('withdraw_type', '!=', 'Internal Transfer')
            ->select('wallet_withdraw.*')
            ->where('verified', true)
            ->with(['user']);


        // if ($role === "Relationship Manager") {
        //     $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
        //         $query->where('rm_id', $alogin);
        //     });
        // }

        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('user.employee', function ($query) use ($alogin) {
                $query->where('relationship_manager.rm_id', $alogin); // Filter based on rm_id in pivot
            });
        }

        if (isset($request->status)) {
            if ($request->status == 2) {
                $rmCondition->whereIn('Status', [2, 3]);
            } else {
                $rmCondition->where('Status', $request->status);
            }
        }

        // $rmCondition->orderBy('id', 'desc');

        if ($request->ajax()) {
            return DataTables::of($rmCondition)
                ->filter(function ($rmCondition) use ($request) {
                    if (!empty($request->search['value'])) {
                        $searchValue = $request->search['value'];
                        $rmCondition->where(function ($q) use ($searchValue) {
                            $q->where('withdraw_amount', 'LIKE', "%{$searchValue}%")
                                ->orWhere('withdraw_transaction_fee', 'LIKE', "%{$searchValue}%")
                                ->orWhere('withdraw_type', 'LIKE', "%{$searchValue}%")
                                ->orWhere('email', 'LIKE', "%{$searchValue}%")
                                ->orWhereRaw("DATE_FORMAT(withdraw_date, '%Y-%m-%d') LIKE ?", ["%{$searchValue}%"]);
                        });
                    }
                })

                ->orderColumn('amount', function ($query, $order) {
                    $query->orderBy('wallet_withdraw.withdraw_amount', $order);
                })
                ->orderColumn('fee', function ($query, $order) {
                    $query->orderBy('wallet_withdraw.withdraw_transaction_fee', $order);
                })
                ->orderColumn('payment_mode', function ($query, $order) {
                    $query->orderBy('wallet_withdraw.withdraw_type', $order);
                })
                ->orderColumn('withdraw_date', function ($query, $order) {
                    $query->orderBy('wallet_withdraw.created_at', $order);
                })
                ->orderColumn('status', function ($query, $order) {
                    $query->orderBy('wallet_withdraw.status', $order);
                })
                ->editColumn('email', function ($row) {
                    $fullname = $row->user
                        ? ($row->user->fullname)
                        : 'Unknown';
                    $email = $row->user ? $row->user->email : 'No Email';
                    return "<a href='/admin/client_details/{$row->user->id}'>
                                    <div class='d-flex align-items-center'>
                                        <div class='me-2'>
                                            <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                        </div>
                                        <div>
                                            <div class='lh-1'><span>{$fullname}</span></div>
                                            <div class='lh-1'><span class='fs-11 text-muted'>{$email}</span></div>
                                        </div>
                                    </div>
                                </a>";
                })
                ->addColumn('amount', function ($row) {
                    return $row->withdraw_amount;
                })
                ->addColumn('fee', function ($row) {
                    return $row->withdraw_transaction_fee;
                })
                ->addColumn('payment_mode', function ($row) {
                    return $row->withdraw_type;
                })
                ->addColumn('withdraw_date', function ($row) {
                    // $date = $row->approved_date ? date('Y-m-d', strtotime($row->approved_date)) : date('Y-m-d', strtotime($row->created_at));
                    $date = Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');

                    // $time = $row->approved_date ? date('H:i:s', strtotime($row->approved_date)) : date('H:i:s', strtotime($row->created_at));
                    $time = Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                    $date
                                </div>
                                <div class='lh-2 text-muted'>
                                    $time
                                </div>";
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    } elseif ($row->status == 2) {
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    } elseif ($row->status == 3) {
                        return "<div class='badge bg-outline-danger'>Declined</div>";
                    } else {
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function ($row) {
                    return "<a class='btn btn-sm btn-primary' href='/admin/wallet_withdrawal_details?id={$row->id}'>View</a>";
                })
                ->addColumn('fullname', function ($row) {
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function ($row) {
                    return $row->user->email;
                })
                ->addColumn('created_date', function ($row) {
                    // return $row->approved_date ? date('Y-m-d', strtotime($row->approved_date)) : date('Y-m-d', strtotime($row->created_at));
                    return Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    // return $row->approved_date ? date('H:i:s', strtotime($row->approved_date)) : date('H:i:s', strtotime($row->created_at));
                    return Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                })
                ->rawColumns(['email', 'amount', 'fee', 'payment_mode', 'withdraw_date', 'status', 'action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getTradingDeposit2(Request $request)
    {
        // dd(auth()->guard('web'));
        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];
        $query = TradeDeposit::select(
            'trade_deposits.*'
        )->with(['user', 'account'])->withTrashed();
        if (!isset($_GET['id'])) {
            // if ($role == "Relationship Manager") {
            //     $query->whereHas('user.relationshipManager', function ($q) use ($alogin) {
            //         $q->where('rm_id', $alogin);
            //     });
            // }
            if ($role === "Relationship Manager") {
                $query->whereHas('user.employee', function ($q) use ($alogin) {
                    $q->where('relationship_manager.rm_id', $alogin); // Filter based on rm_id in pivot
                });
            }
        } else {
            $query->where('code', $_GET['id']);
        }
        // dd($request->all());
        if (isset($request->status)) {
            $query->where('trade_deposits.status', $request->status);
        }

        if (isset($request->type)) {
            $query->where('trade_deposits.deposit_type', $request->type);
        }

        if (isset($request->clientId)) {
            $query->where('trade_deposits.user_id', $request->clientId);
        }


        if ($request->ajax()) {
            // if ($request->length == -1) {
            //     $query = $query->get(); // Fetch all rows
            // } else {
            //     $query = $query->paginate($request->length);
            // }
            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if (!empty($request->search['value'])) {
                        $searchValue = $request->search['value'];
                        $query->where(function ($q) use ($searchValue) {
                            $q->where('deposit_type', 'LIKE', "%{$searchValue}%")
                                ->orWhere('deposit_from', 'LIKE', "%{$searchValue}%")
                                ->orWhere('code', 'LIKE', "%{$searchValue}%")
                                ->orWhereRaw("DATE_FORMAT(deposted_date, '%Y-%m-%d') LIKE ?", ["%{$searchValue}%"]);
                        });
                    }
                })

                ->orderColumn('name', function ($query, $order) {
                    $query->join('aspnetusers as u', 'u.id', '=', 'trade_deposits.user_id')
                        ->orderBy('u.fullname', $order)
                        ->select('trade_deposits.*'); // Ensure columns remain consistent
                })


                ->orderColumn('code', function ($query, $order) {
                    $query->orderBy('trade_deposits.code', $order);
                })
                ->orderColumn('deposit_amount', function ($query, $order) {
                    $query->orderBy('trade_deposits.deposit_amount', $order);
                })
                ->orderColumn('deposit_type', function ($query, $order) {
                    $query->orderBy('trade_deposits.deposit_type', $order);
                })
                ->orderColumn('deposit_from', function ($query, $order) {
                    $query->orderBy('trade_deposits.deposit_from', $order);
                })
                ->orderColumn('deposit_date', function ($query, $order) {
                    $query->orderBy('trade_deposits.deposted_date', $order);
                })
                ->orderColumn('status', function ($query, $order) {
                    $query->orderBy('trade_deposits.status', $order);
                })
                ->addColumn('name', function ($row) {
                    return $row->user->fullname;
                })
                ->editColumn('email', function ($row) {
                    $fullname = $row->user
                        ? ($row->user->fullname)
                        : 'Unknown';
                    $email = $row->user ? $row->user->email : 'No Email';
                    return "<a href='/admin/client_details/{$row->user->id}'>
                                    <div class='d-flex align-items-center'>
                                        <div class='me-2'>
                                            <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                        </div>
                                        <div>
                                            <div class='lh-1'><span>{$fullname}</span></div>
                                            <div class='lh-1'><span class='fs-11 text-muted'>{$email}</span></div>
                                        </div>
                                    </div>
                                </a>";
                })
                ->addColumn('deposit_type', function ($row) {
                    if ($row->deposit_from) {
                        $acc = Account::where('id', $row->deposit_from)->first();
                    }
                    if ($row->deposit_from == 'IB Commission' || $row->deposit_type == 'IB Withdraw') {
                        $deposit_type = 'IB Deposit';
                    } else {
                        $deposit_type = $row->deposit_type;
                    }
                    return $deposit_type;
                })
                ->addColumn('deposit_from', function ($row) {
                    if ($row->deposit_from) {
                        $acc = Account::where('id', $row->deposit_from)->first();
                    }
                    if ($row->deposit_from == 'IB Commission' || $row->deposit_type == 'IB Withdraw') {
                        $deposit_from = 'IB Wallet';
                    } else {
                        $deposit_from = $row->deposit_type;
                    }
                    return ($row->deposit_from && $acc) ? $acc->code : $deposit_from;
                })
                ->addColumn('deposit_date', function ($row) {
                    // $date = date('Y-m-d', strtotime($row->deposted_date));
                    $date = Carbon::parse($row->deposted_date)->addHours(3)->format('Y-m-d');
                    // $time = date('H:i:s', strtotime($row->deposted_date));
                    $time = Carbon::parse($row->deposted_date)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    } elseif ($row->status == 2) {
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    } else {
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function ($row) {
                    return "<a href='/admin/trading_deposit_details?id={$row->id}' class='' style='font-size: 13px;padding: 2px 20px;'><i class='fe fe-eye fs-14 text-info'></i></a>";
                })
                ->addColumn('created_date', function ($row) {
                    // return date('Y-m-d', strtotime($row->deposted_date));
                    return Carbon::parse($row->deposted_date)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    // return date('H:i:s', strtotime($row->deposted_date));
                    return Carbon::parse($row->deposted_date)->addHours(3)->format('H:i:s');
                })
                ->addColumn('client_email', function ($row) {
                    return $row->user->email;
                })
                ->rawColumns(['name', 'email', 'id', 'account_no', 'amount', 'deposit_type', 'deposit_from', 'deposit_date', 'status', 'action', 'client_email'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getTradingWithdrawal2(Request $request)
    {
        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];
        $query = TradeWithdrawals::select('trade_withdrawal.*')
            ->with(['user', 'withdrawTo', 'account'])
            ->withTrashed()
            ->where('trade_withdrawal.email_verified', 1)
            ->whereIn('trade_withdrawal.withdraw_type', ['CRM', 'Internal Transfer', 'Trade Withdrawal']);

        if (!isset($_GET['id'])) {
            // if (session('userData')['userRole'] == "Relationship Manager") {
            //     $rmId = session('alogin');
            //     $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
            //         $q->where('rm_id', $rmId);
            //     });
            // }
            if ($role === "Relationship Manager") {
                $query->whereHas('user.employee', function ($q) use ($alogin) {
                    $q->where('relationship_manager.rm_id', $alogin); // Filter based on rm_id in pivot
                });
            }
        } else {
            $query->where('trade_withdrawal.account_id', $_GET['id']);
        }

        if (isset($request->status)) {
            $query->where('trade_withdrawal.status', $request->status);
        }

        if (isset($request->clientId)) {
            $query->where('trade_withdrawal.user_id', $request->clientId);
        }

        $query->orderByDesc('trade_withdrawal.created_at');

        // Fetch data
        // $query->orderByDesc('id')->get();

        if ($request->ajax()) {
            return DataTables::of($query)

                ->orderColumn('withdraw_date', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.withdraw_date', $order);
                })

                ->orderColumn('approve_date', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.approved_date', $order);
                })

                ->orderColumn('withdraw_type', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.withdraw_type', $order);
                })

                ->orderColumn('created_date', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.created_at', $order);
                })

                ->orderColumn('created_time', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.created_at', $order);
                })

                ->orderColumn('new_total_deposit', function ($query, $order) {
                    $query->join('trade_deposits as td', 'td.user_id', '=', 'trade_withdrawal.user_id')
                        ->where('td.status', 1)
                        ->selectRaw('trade_withdrawal.*, COALESCE(SUM(td.deposit_amount), 0) as total_deposit')
                        ->groupBy('trade_withdrawal.id')
                        ->orderBy('total_deposit', $order);
                })

                ->orderColumn('new_total_withdrawal', function ($query, $order) {
                    $query->join('trade_withdrawal as tw', function ($join) {
                            $join->on('tw.user_id', '=', 'trade_withdrawal.user_id')
                                ->where('tw.status', 1);
                        })
                        ->selectRaw('trade_withdrawal.*, COALESCE(SUM(tw.withdrawal_amount), 0) as total_withdrawal')
                        ->groupBy('trade_withdrawal.id')
                        ->orderBy('total_withdrawal', $order);
                })

                ->orderColumn('code', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.code', $order);
                })

                ->orderColumn('balance', function ($query, $order) {
                    $query->join('accounts as acc', 'acc.id', '=', 'trade_withdrawal.account_id')
                        ->orderBy('acc.balance', $order)
                        ->select('trade_withdrawal.*');
                })

                ->orderColumn('floating_balance', function ($query, $order) {
                    $query->leftJoin('accounts as live_acc', function ($join) {
                            $join->on('live_acc.user_id', '=', 'trade_withdrawal.user_id')
                                ->where('live_acc.balance', '>', 0);
                        })
                        ->orderByRaw("COALESCE(SUM(live_acc.balance), 0) {$order}")
                        ->groupBy('trade_withdrawal.id')
                        ->select('trade_withdrawal.*');
                })

                ->orderColumn('transaction_fee', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.transaction_fee', $order);
                })

                ->orderColumn('withdrawal_amount', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.withdrawal_amount', $order);
                })

                ->orderColumn('status', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.status', $order);
                })

                ->orderColumn('email', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.email', $order);
                })

                ->orderColumn('name', function ($query, $order) {
                    $query->join('aspnetusers as u', 'u.email', '=', 'trade_withdrawal.email')
                        ->orderBy('u.fullname', $order)
                        ->select('trade_withdrawal.*'); // Required to avoid column conflicts
                })

                ->addColumn('code', function ($row) {
                    return $row->account->code ?? '';
                })
                ->addColumn('withdraw_type', function ($row) {
                    return $row->withdraw_type;
                })
                ->addColumn('withdraw_method', function ($row) {
                    // if ($row->status == 1) {
                    //     return "<a class='text-success' target='_blank' href='https://uniwire.com/payout/{$row->transaction_id}'>{$row->withdraw_type}</a>";
                    // } else {
                    //     return 'N/A';
                    // }

                    if($row->status == 1){
                        $data = json_decode($row->payout_res, true);
                        $txid = $data['result']['txid'] ?? null;
                        $kind = $data['result']['kind'] ?? '';
                        $coin = strtoupper(preg_split('/[^a-zA-Z]/', $kind)[0]);
                        $link = '';
                        if($txid){
                            if($coin =='ETH'){
                                $link = "https://etherscan.io/tx/{$txid}";
                            }
                            elseif($coin != 'USDT'){
                                $link = "https://www.blockchain.com/explorer/transactions/{$coin}/{$txid}";
                            }
                            else{
                                $link = "https://tokenview.io/en/search/{$txid}";
                            }
                        }
                        $withdrawMethod =  ($row->admin_remark == 'Manually Approved') ? 'Manual' : $row->withdraw_type;
                        // dump($withdrawMethod);
                        return "<a class='text-success' target='_blank' href='{$link}'>{$withdrawMethod}</a>";
                    }else{
                        return 'N/A';
                    }
                })
                ->addColumn('withdraw_to', function ($row) {
                    if ($row->withdraw_to) {
                        $acc = Account::where('id', $row->withdraw_to)->first();
                    }
                    return ($row->withdraw_to && $acc) ? $acc->code : $row->withdraw_type;
                })
                ->addColumn('withdraw_date', function ($row) {
                    // $date = date('Y-m-d', strtotime($row->withdraw_date));
                    $date = Carbon::parse($row->withdraw_date)->addHours(3)->format('Y-m-d');
                    // $time = date('H:i:s', strtotime($row->withdraw_date));
                    $time = Carbon::parse($row->withdraw_date)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('approve_date', function ($row) {
                    // $date = date('Y-m-d', strtotime($row->withdraw_date));
                    $date = Carbon::parse($row->approved_date)->addHours(3)->format('Y-m-d');
                    // $time = date('H:i:s', strtotime($row->withdraw_date));
                    $time = Carbon::parse($row->approved_date)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    } elseif ($row->status == 2) {
                        return "<div class='badge bg-outline-danger'>Cancelled by Admin</div>";
                    } elseif ($row->status == 3) {
                        return "<div class='badge bg-outline-danger'>Cancelled By User</div>";
                    } else {
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function ($row) {
                    return "<a href='/admin/trading_withdrawal_details?id={$row->id}' class=' style='font-size: 13px;padding: 2px 20px;'><i class='fe fe-eye fs-14 text-info'></i></a>";
                })
                ->addColumn('name', function ($row) {
                    return $row->user->fullname;
                })
                ->editColumn('email', function ($row) {
                    $fullname = $row->user
                        ? ($row->user->fullname)
                        : 'Unknown';
                    $email = $row->user ? $row->user->email : 'No Email';
                    return "<a href='/admin/client_details/{$row->user->id}'>
                                <div class='d-flex align-items-center'>
                                    <div class='me-2'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                    </div>
                                    <div>
                                        <div class='lh-1'><span>{$fullname}</span></div>
                                        <div class='lh-1'><span class='fs-11 text-muted'>{$email}</span></div>
                                    </div>
                                </div>
                            </a>";
                })
                ->addColumn('withdrawal_amount', function ($row) {
                    // return $row->withdrawal_amount - ($row->promo_deduction ?? 0);
                    return $row->withdrawal_amount ?? 0;
                })
                ->addColumn('withdrawal_fee', function ($row) {
                    return $row->transaction_fee;
                })

                ->addColumn('balance', function ($row) {
                    return $row->account->balance <= 0 ? '0.00' : $row->account->balance;
                })

                ->addColumn('floating_balance', function ($row) {
                    $balance = round($row->user->liveAccounts->where('balance', '>', 0)->sum('balance') ?? 0,2);
                    return $balance;
                })

                ->addColumn('new_total_deposit', function ($row) {
                    $total_deposit = round($row->user->NewTotalDeposit ?? 0, 2);
                    return $total_deposit;
                })

                ->addColumn('new_total_withdrawal', function ($row) {
                    $new_total_withdrawal = round($row->user->NewTotalWithdrawal ?? 0, 2);
                    return $new_total_withdrawal;
                })

                ->addColumn('total_withdrawal', function ($row) {
                    return $row->transaction_fee + $row->withdrawal_amount;
                })
                ->addColumn('created_date', function ($row) {
                    // return date('Y-m-d', strtotime($row->withdraw_date));
                    return Carbon::parse($row->withdraw_date)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    // return date('H:i:s', strtotime($row->withdraw_date));
                    return Carbon::parse($row->withdraw_date)->addHours(3)->format('H:i:s');
                })
                ->addColumn('client_email', function ($row) {
                    return $row->user->email;
                })
                ->rawColumns(['withdraw_method', 'account_no', 'email', 'amount', 'transaction_fee', 'withdraw_type', 'withdraw_to', 'withdraw_date', 'approve_date', 'status', 'action', 'withdrawal_amount', 'withdrawal_fee', 'total_withdrawal', 'total_withdrawal', 'created_date', 'created_time', 'client_email'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getTradeHistory(Request $request)
    {
        $role = session('userData')['userRole'] ?? null;
        $alogin = session('userData')['id'] ?? null;
        $userGroups = session('user_groups');

        $query = Trade::select('trades.*')
            ->with(['account.user']);

        // Filter by account ID if provided
        if (isset($request->id)) {
            $query->where('account_id', $request->id);
        } else {
            // Country-based filtering (if user_groups contains country codes)
            if ($role !== "Super Admin" && $userGroups) {
                $allowedCountries = explode(',', $userGroups);
                if (!empty($allowedCountries)) {
                    $query->whereHas('account.user', function ($q) use ($allowedCountries) {
                        $q->whereIn('country', $allowedCountries);
                    });
                }
            }

            // Relationship Manager filtering
            if ($role === "Relationship Manager" && $alogin) {
                $query->whereHas('account.user.employee', function ($q) use ($alogin) {
                    $q->where('relationship_manager.rm_id', $alogin);
                });
            }
        }

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addColumn('order_id_display', function ($row) {
                    return $row->order_id ?? $row->code ?? '-';
                })
                ->addColumn('symbol_display', function ($row) {
                    return '<span class="fw-semibold">' . ($row->symbol ?? '-') . '</span>';
                })
                ->addColumn('type_display', function ($row) {
                    $type = strtoupper($row->type ?? '');
                    $badgeClass = $type === 'BUY' ? 'badge bg-success' : 'badge bg-danger';
                    return '<span class="' . $badgeClass . '">' . $type . '</span>';
                })
                ->addColumn('volume_display', function ($row) {
                    return number_format($row->volume ?? 0, 2);
                })
                ->addColumn('open_price_display', function ($row) {
                    return number_format($row->open_price ?? 0, 5);
                })
                ->addColumn('close_price_display', function ($row) {
                    return $row->close_price ? number_format($row->close_price, 5) : '-';
                })
                ->addColumn('profit_display', function ($row) {
                    $profit = $row->profit ?? 0;
                    $color = $profit >= 0 ? 'text-success' : 'text-danger';
                    return '<span class="' . $color . '">$' . number_format($profit, 2) . '</span>';
                })
                ->addColumn('status_display', function ($row) {
                    $status = strtolower($row->status ?? '');
                    $badgeClass = 'badge bg-secondary';
                    if ($status === 'closed') {
                        $badgeClass = 'badge bg-success';
                    } elseif ($status === 'open') {
                        $badgeClass = 'badge bg-primary';
                    } elseif ($status === 'cancelled') {
                        $badgeClass = 'badge bg-danger';
                    }
                    return '<span class="' . $badgeClass . '">' . ucfirst($row->status ?? '') . '</span>';
                })
                ->addColumn('open_time_display', function ($row) {
                    if (!$row->open_time) return '-';

                    return '<div class="d-grid">
                        <div class="date">' . $row->open_time->copy()->setTimezone('UTC')->format('Y-m-d') . '</div>
                        <div class="time text-muted">' . $row->open_time->copy()->setTimezone('UTC')->format('H:i:s') . '</div>
                    </div>';
                })
                ->addColumn('action', function ($row) {
                    return "<a href='#' class='btn btn-sm btn-info' title='View Details'><i class='fe fe-eye'></i></a>";
                })
                ->orderColumn('open_time', function ($query, $order) {
                    $query->orderBy('trades.open_time', $order);
                })
                ->orderColumn('order_id', function ($query, $order) {
                    $query->orderBy('trades.order_id', $order);
                })
                ->orderColumn('symbol', function ($query, $order) {
                    $query->orderBy('trades.symbol', $order);
                })
                ->orderColumn('profit', function ($query, $order) {
                    $query->orderBy('trades.profit', $order);
                })
                ->rawColumns(['order_id_display', 'symbol_display', 'type_display', 'profit_display', 'status_display', 'open_time_display', 'action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function exportAllTrades(Request $request)
    {
        $role = session('userData')['userRole'] ?? null;
        $alogin = session('userData')['id'] ?? null;
        $userGroups = session('user_groups');
        $accountId = $request->get('id');

        // Build filename with account info if accountId provided
        if ($accountId) {
            $account = Account::with('user')->find($accountId);
            if ($account && $account->user) {
                // Format name: replace spaces with underscores
                $accountName = str_replace(' ', '_', $account->user->fullname ?? 'Unknown');
                $accountCode = $account->code ?? 'N/A';
                $fileName = 'lqh_' . $accountName . '_' . $accountCode . '.csv';
            } else {
                $fileName = 'lqh_Trades_' . date('Y-m-d') . '.csv';
            }
        } else {
            $fileName = 'lqh_Trades_All_' . date('Y-m-d') . '.csv';
        }

        return Response::streamDownload(function () use ($role, $alogin, $userGroups, $accountId) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, ['Order ID', 'Symbol', 'Type', 'Volume', 'Open Price', 'Close Price', 'Profit', 'Commission', 'Swap', 'Status', 'Open Time', 'Close Time']);

            // Build query
            $query = Trade::select('trades.*')
                ->with(['account.user']);

            if ($accountId) {
                $query->where('account_id', $accountId);
            } else {
                // Country-based filtering
                if ($role !== "Super Admin" && $userGroups) {
                    $allowedCountries = explode(',', $userGroups);
                    if (!empty($allowedCountries)) {
                        $query->whereHas('account.user', function ($q) use ($allowedCountries) {
                            $q->whereIn('country', $allowedCountries);
                        });
                    }
                }

                // Relationship Manager filtering
                if ($role === "Relationship Manager" && $alogin) {
                    $query->whereHas('account.user.employee', function ($q) use ($alogin) {
                        $q->where('relationship_manager.rm_id', $alogin);
                    });
                }
            }

            // Fetch trades data in chunks
            $query->orderBy('open_time', 'desc')
                ->chunk(500, function ($trades) use ($handle) {
                    foreach ($trades as $trade) {
                        fputcsv($handle, [
                            $trade->order_id ?? $trade->code ?? '',
                            $trade->symbol ?? '',
                            strtoupper($trade->type ?? ''),
                            number_format($trade->volume ?? 0, 2),
                            number_format($trade->open_price ?? 0, 5),
                            $trade->close_price ? number_format($trade->close_price, 5) : '',
                            number_format($trade->profit ?? 0, 2),
                            number_format($trade->commission ?? 0, 2),
                            number_format($trade->swap ?? 0, 2),
                            ucfirst($trade->status ?? ''),
                            $trade->open_time ? Carbon::parse($trade->open_time)->addHours(3)->format('Y-m-d H:i:s') : '',
                            $trade->close_time ? Carbon::parse($trade->close_time)->addHours(3)->format('Y-m-d H:i:s') : '',
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportFilteredTrades(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $role = session('userData')['userRole'] ?? null;
        $alogin = session('userData')['id'] ?? null;
        $userGroups = session('user_groups');
        $accountId = $request->get('id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Build filename with account info and date range
        if ($accountId) {
            $account = Account::with('user')->find($accountId);
            if ($account && $account->user) {
                // Format name: replace spaces with underscores
                $accountName = str_replace(' ', '_', $account->user->fullname ?? 'Unknown');
                $accountCode = $account->code ?? 'N/A';
                $fileName = 'lqh_' . $accountName . '_' . $accountCode . '_' . $dateFrom . '_to_' . $dateTo . '.csv';
            } else {
                $fileName = 'lqh_Trades_Filtered_' . $dateFrom . '_to_' . $dateTo . '.csv';
            }
        } else {
            $fileName = 'lqh_Trades_Filtered_' . $dateFrom . '_to_' . $dateTo . '.csv';
        }

        return Response::streamDownload(function () use ($role, $alogin, $userGroups, $accountId, $dateFrom, $dateTo) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, ['Order ID', 'Symbol', 'Type', 'Volume', 'Open Price', 'Close Price', 'Profit', 'Commission', 'Swap', 'Status', 'Open Time', 'Close Time']);

            // Build query
            $query = Trade::select('trades.*')
                ->with(['account.user'])
                ->whereDate('open_time', '>=', $dateFrom)
                ->whereDate('open_time', '<=', $dateTo);

            if ($accountId) {
                $query->where('account_id', $accountId);
            } else {
                // Country-based filtering
                if ($role !== "Super Admin" && $userGroups) {
                    $allowedCountries = explode(',', $userGroups);
                    if (!empty($allowedCountries)) {
                        $query->whereHas('account.user', function ($q) use ($allowedCountries) {
                            $q->whereIn('country', $allowedCountries);
                        });
                    }
                }

                // Relationship Manager filtering
                if ($role === "Relationship Manager" && $alogin) {
                    $query->whereHas('account.user.employee', function ($q) use ($alogin) {
                        $q->where('relationship_manager.rm_id', $alogin);
                    });
                }
            }

            // Fetch trades data in chunks
            $query->orderBy('open_time', 'desc')
                ->chunk(500, function ($trades) use ($handle) {
                    foreach ($trades as $trade) {
                        fputcsv($handle, [
                            $trade->order_id ?? $trade->code ?? '',
                            $trade->symbol ?? '',
                            strtoupper($trade->type ?? ''),
                            number_format($trade->volume ?? 0, 2),
                            number_format($trade->open_price ?? 0, 5),
                            $trade->close_price ? number_format($trade->close_price, 5) : '',
                            number_format($trade->profit ?? 0, 2),
                            number_format($trade->commission ?? 0, 2),
                            number_format($trade->swap ?? 0, 2),
                            ucfirst($trade->status ?? ''),
                            $trade->open_time ? Carbon::parse($trade->open_time)->addHours(3)->format('Y-m-d H:i:s') : '',
                            $trade->close_time ? Carbon::parse($trade->close_time)->addHours(3)->format('Y-m-d H:i:s') : '',
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function getInternalTransfer2(Request $request)
    {
        $query = TradeDeposit::select('trade_deposits.*')
            ->with(['user', 'account']) // Eager load user and account relationships
            ->whereIn('trade_deposits.deposit_type', ['Internal Transfer', 'CRM']);

        $role = session('userData')['userRole'] ?? null;
        $alogin = session('userData')['id'] ?? null;

        // Filter for Relationship Manager if 'id' is not present in query string
        if (!isset($_GET['id']) && $role === "Relationship Manager" && $alogin) {
            $query->whereHas('user.employee', function ($q) use ($alogin) {
                $q->where('relationship_manager.rm_id', $alogin);
            });
        }

        // Filter by status if provided
        if (isset($request->status)) {
            $query->where('trade_deposits.status', $request->status);
        }

        if (isset($request->type)) {
            $query->where('trade_deposits.deposit_type', $request->type);
        }
        if (isset($request->clientId)) {
            $query->where('trade_deposits.user_id', $request->clientId);
        }

        if ($request->ajax()) {
            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if (!empty($request->search['value'])) {
                        $searchValue = $request->search['value'];

                        $query->where(function ($q) use ($searchValue) {
                            $q->where('trade_deposits.email', 'LIKE', "%{$searchValue}%")
                                ->orWhere('trade_deposits.code', 'LIKE', "%{$searchValue}%")
                                ->orWhere('trade_deposits.deposit_amount', 'LIKE', "%{$searchValue}%")
                                ->orWhere('trade_deposits.deposit_from', 'LIKE', "%{$searchValue}%")
                                ->orWhere('trade_deposits.deposit_type', 'LIKE', "%{$searchValue}%")
                                ->orWhereHas('accountDepositFrom', function ($query2) use ($searchValue) {
                                    $query2->where('code', 'LIKE', "%{$searchValue}%");
                                });
                        });
                    }
                })
                ->addColumn('name', function ($row) {
                    return $row->user->fullname ?? '-';
                })
                ->addColumn('email', function ($row) {
                    return $row->user->email ?? '-';
                })
                ->addColumn('amount', function ($row) {
                    return $row->deposit_amount;
                })
                ->addColumn('transfer_from', function ($row) {

                    $acc = null;
                    if (is_numeric($row->deposit_from)) {

                        $acc = Account::find($row->deposit_from);
                    }

                    if ($row->deposit_from == 'IB Commission' || $row->deposit_type == 'IB Withdraw') {
                        $transfer_from = 'IB Wallet';
                    } elseif ($row->deposit_type == 'CRM' && $row->deposit_from == NULL) {
                        $transfer_from = $row->deposit_type;
                    } else {
                        $acc = Account::find($row->deposit_from);
                        $transfer_from = $acc;
                    }
                    return ($acc) ? $acc->code : $transfer_from;
                })
                ->addColumn('transfer_to', function ($row) {
                    return $row->account->code ?? '-';
                })
                ->addColumn('date', function ($row) {
                    $created = Carbon::parse($row->created_at)->addHours(3);
                    return "<div class='lh-1'>{$created->format('Y-m-d')}</div>
                            <div class='lh-2 text-muted'>{$created->format('H:i:s')}</div>";
                })
                ->addColumn('status', function ($row) {
                    return match ($row->status) {
                        1 => "<div class='badge bg-outline-success'>Approved</div>",
                        2 => "<div class='badge bg-outline-danger'>Rejected</div>",
                        default => "<div class='badge bg-outline-primary'>Pending</div>",
                    };
                })
                ->orderColumn('name', function($query, $direction) {
                    return $query->leftJoin('aspnetusers', 'aspnetusers.email', '=', 'trade_deposits.email')
                                 ->orderBy('aspnetusers.fullname', $direction);
                })
                ->orderColumn('email', function($query, $direction) {
                    return $query->leftJoin('aspnetusers', 'aspnetusers.email', '=', 'trade_deposits.email')
                                 ->orderBy('aspnetusers.email', $direction);
                })
                ->orderColumn('amount', function($query, $direction) {
                    return $query->orderBy('trade_deposits.deposit_amount', $direction);
                })
                ->orderColumn('transfer_from', function($query, $direction) {
                    return $query->orderBy('trade_deposits.deposit_from', $direction);
                })
                ->orderColumn('transfer_to', function($query, $direction) {
                    return $query->leftJoin('accounts', 'accounts.id', '=', 'trade_deposits.account_id')
                                 ->orderBy('accounts.code', $direction);
                })
                ->orderColumn('date', function($query, $direction) {
                    return $query->orderBy('trade_deposits.created_at', $direction);
                })
                ->orderColumn('status', function($query, $direction) {
                    return $query->orderBy('trade_deposits.status', $direction);
                })
                ->rawColumns(['name', 'email', 'amount', 'transfer_from', 'transfer_to', 'date', 'status'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }



    // public function getWalletDeposit()
    // {

    //     $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
    //     if (session('userData')['userRole'] == "Relationship Manager") {
    //         $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "'";
    //     } else {
    //         $rmCondition .= " where ";
    //     }

    //     header('Content-Type: application/json');
    //     $sql = "SELECT (user.id) as enc_id,user.fullname as fullname,trs.* from wallet_deposit trs " . $rmCondition . " trs.deposit_type!='Internal Transfer' order by trs.id desc";
    //     $query = DB::select($sql);
    //     $results = $query;
    //     $data = [];
    //     foreach ($results as $row) {
    //         $data[] = [
    //             'email' => $row->email,
    //             'enc_id' => $row->enc_id,
    //             'fullname' => $row->fullname,
    //             'amount' => '$' . $row->deposit_amount,
    //             'payment_mode' => $row->deposit_type,
    //             'deposit_date' => $row->deposted_date,
    //             'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
    //                 '<span class="badge bg-outline-primary">Pending</span>'),
    //             'action' => ' <a class="btn btn-sm btn-primary" href="/admin/wallet_deposit_details?id=' . ($row->id) . '">View</a>'
    //         ];
    //     }
    //     return ['data' => $data];
    // }
    // public function getWalletWithdrawal()
    // {

    //     $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
    //     if (session('userData')['userRole'] == "Relationship Manager") {
    //         $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "'";
    //     } else {
    //         $rmCondition .= " where ";
    //     }

    //     header('Content-Type: application/json');
    //     $sql = "SELECT (user.id) as enc_id,user.fullname as fullname,trs.* from wallet_withdraw trs " . $rmCondition . " trs.withdraw_type!='Internal Transfer' order by trs.id desc";
    //     $results = DB::select($sql);
    //     $data = [];
    //     foreach ($results as $row) {
    //         $data[] = [
    //             'email' => $row->email,
    //             'enc_id' => $row->enc_id,
    //             'fullname' => $row->fullname,
    //             'amount' => '$' . $row->withdraw_amount,
    //             'fee' => '$' . $row->withdraw_transaction_fee,
    //             'payment_mode' => $row->withdraw_type,
    //             'withdraw_date' => $row->withdraw_date,
    //             'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
    //                 '<span class="badge bg-outline-primary">Pending</span>'),
    //             'action' => ' <a class="btn btn-sm btn-primary" href="/admin/wallet_withdrawal_details?id=' . ($row->id) . '">View</a>'
    //         ];
    //     }
    //     return ['data' => $data];
    // }

    public function getComissionData2(Request $request)
    {
        try {
            // dd($request->id);
            $userId = $request->id;

            if (!$userId) {
                return response()->json(['error' => 'User ID is required'], 400);
            }

            // Fetch histories
            $query = IbWallet::with('account')->where('user_id', $userId)->orderBy('created_at', 'desc');

            if ($request->ajax()) {
                return DataTables::of($query)
                    ->editColumn('amount', function ($row) {
                        return $row->code ? @money($row->ib_wallet) : ($row->ib_withdraw);
                    })
                    ->addColumn('type', function ($row) {
                        return $row->ib_wallet ? 'Commission' : 'Transfer';
                    })
                    ->addColumn('account', function ($row) {
                        $code = $row->account->code ?? '';
                        $email = $row->account->email ?? '';
                        return "
                                <div class='row align-items-center'>
                                     <div class='col-auto pe-0'>
                                         <img src='/assets/images/mt5.png' alt='user-image'
                                             class='rounded wid-50 hei-50'>
                                     </div>
                                     <div class='col'>
                                         <h4 class='mb-2 ms-2'>
                                             <span class='text-truncate w-100'>$code</span>
                                         </h4>
                                         <p class='mb-0 text-muted ms-2 f-12'>
                                             <span class='text-truncate w-100'>$email</span>
                                         </p>
                                     </div>
                                 </div>";
                    })
                    ->addColumn('date', function ($row) {
                        $date = date('Y-m-d', strtotime($row->created_at));
                        $time = date('H:i:s', strtotime($row->created_at));
                        return "<div class='lh-1'>
                                $date
                            </div>
                            <small class='lh-2 text-muted'>
                                $time
                            </small>";
                    })
                    ->addColumn('email', function ($row) {
                        $email = $row->account->email ?? '';
                        return $email;
                    })
                    ->addColumn('exp_date', function ($row) {
                        $date = date('Y-m-d', strtotime($row->created_at));
                        return $date;
                    })
                    ->addColumn('time', function ($row) {
                        $time = date('H:i:s', strtotime($row->created_at));
                        return $time;
                    })
                    ->addColumn('exp_account', function ($row) {
                        $code = $row->account->code ?? '';
                        return $code;
                    })
                    ->addColumn('exp_amount', function ($row) {
                        $amount = ($row->ib_wallet) ?? ($row->ib_withdraw);
                        return $amount;
                    })
                    ->rawColumns(['date', 'account', 'type', 'amount', 'email'])
                    ->make(true);
            }

            return response()->json(['error' => 'Invalid request'], 400);
        } catch (\Exception $e) {
            Log::error('Error in getComissionData2: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching data'], 500);
        }
    }

    // public function getComissionData($data)
    // {
    //     // Retrieve user ID from request
    //     $userId = $data['id'];

    //     // Fetch histories
    //     $histories = IbWallet::with('account')->where('user_id', $userId)->get();
    //     // Prepare data
    //     $data = $histories->map(function ($row) {
    //         return [
    //             'date' => $row->created_at->format('Y-m-d H:i:s'), // Format date for consistency
    //             'accounts' => $row->account->code,
    //             'email' => $row->account->email,
    //             'type' => $row->ib_wallet ? 'Commission' : 'Transfer',
    //             'amount' => $row->ib_wallet ?? $row->ib_withdraw
    //         ];
    //     });
    //     return ['data' => $data];
    // }


    // public function getTradingDeposit()
    // {
    //     $rmCondition = " left join accounts user on(user.email=trs.email) ";
    //     // $condition = "";

    //     // if (!isset($_GET['id'])) {
    //     //     if (session('userData')['userRole'] == "Relationship Manager") {
    //     //         $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "' ";
    //     //     } else {
    //     //     }
    //     // }
    //     // if (isset($_GET['id'])) {
    //     //     $condition = ' where trs.code=' . $_GET['id'];
    //     // }
    //     // header('Content-Type: application/json');
    //     // $sql = "SELECT (user.id) as enc_id,user.name as fullname,trs.* from trade_deposits trs " . $rmCondition . $condition . " group by trs.id order by trs.id desc";
    //     // $query = DB::select($sql);
    //     // $results = $query;

    //     $query = TradeDeposit::with(['user', 'account']);
    //     if (!isset($_GET['id'])) {
    //         if (session('userData')['userRole'] == "Relationship Manager") {
    //             $rmId = session('alogin');
    //             $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
    //                 $q->where('rm_id', $rmId);
    //             });
    //         }
    //     } else {
    //         $query->where('code', $_GET['id']);
    //     }
    //     $results = $query->orderByDesc('id')->get();
    //     $data = [];
    //     foreach ($results as $row) {
    //         if ($row->deposit_from) {
    //             $acc = Account::where('id', $row->deposit_from)->first();
    //         }
    //         if ($row->deposit_from == 'IB Commission' || $row->deposit_type == 'IB Withdraw') {
    //             $deposit_from = 'IB Wallet';
    //             $deposit_type = 'IB Deposit';
    //         } else {
    //             $deposit_from = $row->deposit_type;
    //             $deposit_type = $row->deposit_type;
    //         }
    //         $data[] = [
    //             'id' => 'TDID' . sprintf("%05d", $row->id),
    //             'account_no' => $row->code,
    //             'enc_id' => $row->enc_id,
    //             'fullname' => $row->fullname,
    //             'amount' => "$" . $row->deposit_amount,
    //             'deposit_type' => $deposit_type,
    //             'deposit_from' => ($row->deposit_from && $acc) ? $acc->code : $deposit_from,
    //             'deposit_date' => $row->deposted_date,
    //             'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
    //                 '<span class="badge bg-outline-primary">Pending</span>'),
    //             'action' => '<a href="/admin/trading_deposit_details?id=' . ($row->id) . '" class="" style="font-size: 13px;padding: 2px 20px;"><i class="fe fe-eye fs-14 text-info"></i></a>'
    //         ];
    //     }
    //     return ['data' => $data];
    // }
    // public function getTradingWithdrawal()
    // {

    //     // $condition = '';
    //     // $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
    //     // if (!isset($_GET['id'])) {
    //     //     if (session('userData')['userRole'] == "Relationship Manager") {
    //     //         $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "'  ";
    //     //     } else {
    //     //     }
    //     // }
    //     // if (isset($_GET['id'])) {
    //     //     $condition = ' where trs.code=' . $_GET['id'];
    //     // }
    //     // header('Content-Type: application/json');
    //     // $sql = "SELECT (user.id) as enc_id,user.fullname as fullname,trs.* from trade_withdrawal trs " . $rmCondition . $condition . " order by trs.id desc";
    //     // $query = DB::select($sql);
    //     // $results = $query;

    //     $query = TradeWithdrawals::with(['user', 'withdrawTo', 'account']);

    //     // Add conditions based on session and GET parameters
    //     if (!isset($_GET['id'])) {
    //         if (session('userData')['userRole'] == "Relationship Manager") {
    //             $rmId = session('alogin');
    //             $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
    //                 $q->where('rm_id', $rmId);
    //             });
    //         }
    //     } else {
    //         $query->where('account_id', $_GET['id']);
    //     }

    //     // Fetch data
    //     $withdrawals = $query->orderByDesc('id')->get();
    //     $data = [];

    //     foreach ($withdrawals as $row) {
    //         if ($row->withdraw_to) {
    //             $acc = Account::where('id', $row->withdraw_to)->first();
    //         }
    //         $data[] = [
    //             'id' => 'TWID' . sprintf("%05d", $row->id),
    //             'account_no' => $row->account->code,
    //             'enc_id' => $row->enc_id,
    //             'fullname' => $row->fullname,
    //             'amount' => '$' . $row->withdrawal_amount,
    //             'withdraw_type' => $row->withdraw_type,
    //             'withdraw_to' => ($row->withdraw_to && $acc) ? $acc->code : $row->withdraw_type,
    //             'withdraw_date' => $row->withdraw_date,
    //             'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
    //                 '<span class="badge bg-outline-primary">Pending</span>'),
    //             'action' => '<a href="/admin/trading_withdrawal_details?id=' . ($row->id) . '" class="" style="font-size: 13px;padding: 2px 20px;"><i class="fe fe-eye fs-14 text-info"></i></a>'
    //         ];
    //     }
    //     return ['data' => $data];
    // }
    // public function getInternalTransfer()
    // {
    //     // $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
    //     // if (session('userData')['userRole'] == "Relationship Manager") {
    //     //     $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "' and ";
    //     // } else {
    //     //     $rmCondition .= " where ";
    //     // }
    //     // header('Content-Type: application/json');
    //     // $sql = "SELECT trs.* from trade_deposits trs " . $rmCondition . " trs.deposit_type = 'Internal Transfer' order by trs.id desc";
    //     // $query = DB::select($sql);
    //     // $results = $query;

    //     $query = TradeDeposit::with(['user', 'account']);

    //     // Add conditions based on session and GET parameters
    //     if (!isset($_GET['id'])) {
    //         if (session('userData')['userRole'] == "Relationship Manager") {
    //             $rmId = session('alogin');
    //             $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
    //                 $q->where('rm_id', $rmId);
    //             });
    //         }
    //     } else {
    //         $query->where('deposit_type', 'Internal Transfer');;
    //     }

    //     // Fetch data
    //     $deposits = $query->orderByDesc('id')->get();

    //     $data = [];
    //     foreach ($deposits as $row) {
    //         if ($row->deposit_from) {
    //             $acc = Account::where('id', $row->deposit_from)->first();
    //         }
    //         if ($row->deposit_from == 'IB Commission' || $row->deposit_type == 'IB Withdraw') {
    //             $transfer_from = 'IB Wallet';
    //         } else {
    //             $transfer_from = $row->deposit_type;
    //         }
    //         $data[] = [
    //             'id' => 'ITID' . sprintf("%05d", $row->id),
    //             'email' => $row->email,
    //             'amount' => '$' . $row->deposit_amount,
    //             'transfer_from' => ($row->deposit_from && $acc) ? $acc->code : $transfer_from,
    //             'transfer_to' => $row->code,
    //             'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
    //                 '<span class="badge bg-outline-primary">Pending</span>'),
    //             'action' => ' <a class="btn btn-sm btn-primary" href="/admin/internal_transfer_details">View</a>'
    //         ];
    //     }
    //     return ['data' => $data];
    // }

    public function getPendingWalletDeposit2(Request $request)
    {
        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];

        // Base query
        $rmCondition = WalletDeposit::where('deposit_type', '!=', 'Internal Transfer')
            ->select('wallet_deposit.*')
            ->where('status', 0)
            ->with(['user']);


        // if ($role === "Relationship Manager") {
        //     $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
        //         $query->where('rm_id', $alogin);
        //     });
        // }

        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('user.employee', function ($query) use ($alogin) {
                $query->where('relationship_manager.rm_id', $alogin); // Filter based on rm_id in pivot
            });
        }

        // if (isset($request->status)) {
        //     $rmCondition->where('Status', $request->status);
        // }

        $rmCondition->orderBy('id', 'desc');

        if ($request->ajax()) {
            return DataTables::of($rmCondition)
                ->filter(function ($rmCondition) use ($request) {
                    if (!empty($request->search['value'])) {
                        $searchValue = $request->search['value'];
                        $rmCondition->where(function ($q) use ($searchValue) {
                            $q->where('withdraw_amount', 'LIKE', "%{$searchValue}%")
                                ->orWhere('deposit_type', 'LIKE', "%{$searchValue}%")
                                ->orWhereRaw("DATE_FORMAT(deposted_date, '%Y-%m-%d') LIKE ?", ["%{$searchValue}%"]);
                        });
                    }
                })
                ->editColumn('email', function ($row) {
                    $fullname = $row->user
                        ? ($row->user->fullname)
                        : 'Unknown';
                    $email = $row->user ? $row->user->email : 'No Email';
                    return "<a href='/admin/client_details/{$row->user->id}'>
                                <div class='d-flex align-items-center'>
                                    <div class='me-2'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                    </div>
                                    <div>
                                        <div class='lh-1'><span>{$fullname}</span></div>
                                        <div class='lh-1'><span class='fs-11 text-muted'>{$email}</span></div>
                                    </div>
                                </div>
                            </a>";
                })
                ->addColumn('amount', function ($row) {
                    return $row->withdraw_amount;
                })
                ->addColumn('payment_mode', function ($row) {
                    return $row->deposit_type;
                })
                ->addColumn('deposit_date', function ($row) {
                    // $date = date('Y-m-d', strtotime($row->deposted_date));
                    $date = Carbon::parse($row->deposted_date)->addHours(3)->format('Y-m-d');
                    // $time = date('H:i:s', strtotime($row->deposted_date));
                    $time = Carbon::parse($row->deposted_date)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    } elseif ($row->status == 2) {
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    } else {
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function ($row) {
                    return "<a class='btn btn-sm btn-primary' href='/admin/wallet_deposit_details?id={$row->id}'>View</a>";
                })
                ->addColumn('fullname', function ($row) {
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function ($row) {
                    return $row->user->email;
                })
                ->addColumn('created_date', function ($row) {
                    // return date('Y-m-d', strtotime($row->deposted_date));
                    return Carbon::parse($row->deposted_date)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    // return date('H:i:s', strtotime($row->deposted_date));
                    return Carbon::parse($row->deposted_date)->addHours(3)->format('H:i:s');
                })
                ->rawColumns(['email', 'amount', 'payment_mode', 'deposit_date', 'status', 'action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getPendingWalletWithdrawal2(Request $request)
    {
        $query = WalletWithdraw::with(['user'])
            ->where('verified', true)
            ->where('status', 0);
        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];
        // if (session('userData')['userRole'] == "Relationship Manager") {
        //     $rmId = session('alogin');
        //     $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
        //         $q->where('rm_id', $rmId);
        //     });
        // }
        if ($role === "Relationship Manager") {
            $query->whereHas('user.employee', function ($q) use ($alogin) {
                $q->where('relationship_manager.rm_id', $alogin); // Filter based on rm_id in pivot
            });
        }

        // if (isset($request->status)) {
        //     $query->where('Status', $request->status);
        // }

        // Fetch data
        // $query->orderByDesc('id')->get();

        if ($request->ajax()) {
            return DataTables::of($query)
                ->editColumn('email', function ($row) {
                    $fullname = $row->user
                        ? ($row->user->fullname)
                        : 'Unknown';
                    $email = $row->user ? $row->user->email : 'No Email';
                    return "<a href='/admin/client_details/{$row->user->id}'>
                                <div class='d-flex align-items-center'>
                                    <div class='me-2'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                    </div>
                                    <div>
                                        <div class='lh-1'><span>{$fullname}</span></div>
                                        <div class='lh-1'><span class='fs-11 text-muted'>{$email}</span></div>
                                    </div>
                                </div>
                            </a>";
                })

                ->orderColumn('amount', function ($query, $order) {
                    $query->orderBy('withdraw_amount', $order);
                })
                ->orderColumn('fee', function ($query, $order) {
                    $query->orderBy('withdraw_transaction_fee', $order);
                })
                ->orderColumn('payment_mode', function ($query, $order) {
                    $query->orderBy('withdraw_type', $order);
                })
                ->orderColumn('withdraw_date', function ($query, $order) {
                    $query->orderBy('created_at', $order);
                })
                ->orderColumn('status', function ($query, $order) {
                    $query->orderBy('status', $order);
                })

                ->addColumn('amount', function ($row) {
                    return $row->withdraw_amount;
                })
                ->addColumn('fee', function ($row) {
                    return $row->withdraw_transaction_fee;
                })
                ->addColumn('payment_mode', function ($row) {
                    return $row->withdraw_type;
                })
                ->addColumn('withdraw_date', function ($row) {
                    // $date = date('Y-m-d', strtotime($row->withdraw_date));
                    $date = Carbon::parse($row->withdraw_date)->addHours(3)->format('Y-m-d');
                    // $time = date('H:i:s', strtotime($row->withdraw_date));
                    $time = Carbon::parse($row->withdraw_date)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    } elseif ($row->status == 2) {
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    } elseif ($row->status == 3) {
                        return "<div class='badge bg-outline-danger'>Decline</div>";
                    } else {
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function ($row) {
                    return "<a class='btn btn-sm btn-primary' href='/admin/wallet_withdrawal_details?id={$row->id}'>View</a>";
                })
                ->addColumn('fullname', function ($row) {
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function ($row) {
                    return $row->user->email;
                })
                ->addColumn('created_date', function ($row) {
                    // return date('Y-m-d', strtotime($row->withdraw_date));
                    return Carbon::parse($row->withdraw_date)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    // return date('H:i:s', strtotime($row->withdraw_date));
                    return Carbon::parse($row->withdraw_date)->addHours(3)->format('H:i:s');
                })
                ->rawColumns(['email', 'amount', 'fee', 'payment_mode', 'withdraw_date', 'status', 'action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getPendingTradingDeposit2(Request $request)
    {
        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];
        $query = TradeDeposit::with(['user', 'account'])
            ->where('status', 0);
        if (!isset($_GET['id'])) {
            // if (session('userData')['userRole'] == "Relationship Manager") {
            //     $rmId = session('alogin');
            //     $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
            //         $q->where('rm_id', $rmId);
            //     });
            // }
            if ($role === "Relationship Manager") {
                $query->whereHas('user.employee', function ($q) use ($alogin) {
                    $q->where('relationship_manager.rm_id', $alogin); // Filter based on rm_id in pivot
                });
            }
        } else {
            $query->where('code', $_GET['id']);
        }

        // if (isset($request->status)) {
        //     $query->where('Status', $request->status);
        // }


        if ($request->ajax()) {
            return DataTables::of($query)
                ->editColumn('id', function ($row) {
                    return $row->id;
                })
                ->addColumn('account_no', function ($row) {
                    return $row->code;
                })
                ->addColumn('amount', function ($row) {
                    return $row->deposit_amount;
                })
                ->addColumn('deposit_type', function ($row) {
                    if ($row->deposit_from) {
                        $acc = Account::where('id', $row->deposit_from)->first();
                    }
                    if ($row->deposit_from == 'IB Commission' || $row->deposit_type == 'IB Withdraw') {
                        $deposit_type = 'IB Deposit';
                    } else {
                        $deposit_type = $row->deposit_type;
                    }
                    return $deposit_type;
                })
                ->addColumn('deposit_from', function ($row) {
                    if ($row->deposit_from) {
                        $acc = Account::where('id', $row->deposit_from)->first();
                    }
                    if ($row->deposit_from == 'IB Commission' || $row->deposit_type == 'IB Withdraw') {
                        $deposit_from = 'IB Wallet';
                    } else {
                        $deposit_from = $row->deposit_type;
                    }
                    return ($row->deposit_from && $acc) ? $acc->code : $deposit_from;
                })
                ->addColumn('deposit_date', function ($row) {
                    // $date = date('Y-m-d', strtotime($row->deposted_date));
                    $date = Carbon::parse($row->deposted_date)->addHours(3)->format('Y-m-d');
                    // $time = date('H:i:s', strtotime($row->deposted_date));
                    $time = Carbon::parse($row->deposted_date)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    } elseif ($row->status == 2) {
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    } else {
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function ($row) {
                    return "<a href='/admin/trading_deposit_details?id={$row->id}' class='' style='font-size: 13px;padding: 2px 20px;'><i class='fe fe-eye fs-14 text-info'></i></a>";
                })
                ->addColumn('created_date', function ($row) {
                    // return date('Y-m-d', strtotime($row->deposted_date));
                    return Carbon::parse($row->deposted_date)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    // return date('H:i:s', strtotime($row->deposted_date));
                    return Carbon::parse($row->deposted_date)->addHours(3)->format('H:i:s');
                })
                ->rawColumns(['id', 'account_no', 'amount', 'deposit_type', 'deposit_from', 'deposit_date', 'status', 'action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getPendingTradingWithdrawal2(Request $request)
    {

        $query = TradeWithdrawals::with(['user', 'withdrawTo', 'account', 'clientWallet'])
            ->distinct()
            ->where('trade_withdrawal.status', 0)
            ->where('trade_withdrawal.email_verified', 1)
            ->where('trade_withdrawal.withdraw_type', 'Trade Withdrawal');

        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];
        if (!isset($_GET['id'])) {
            // if (session('userData')['userRole'] == "Relationship Manager") {
            //     $rmId = session('alogin');
            //     $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
            //         $q->where('rm_id', $rmId);
            //     });
            // }
            if ($role === "Relationship Manager") {
                $query->whereHas('user.employee', function ($q) use ($alogin) {
                    $q->where('relationship_manager.rm_id', $alogin); // Filter based on rm_id in pivot
                });
            }
        } else {
            $query->where('account_id', $_GET['id']);
        }

        // if (isset($request->status)) {
        //     $query->where('Status', $request->status);
        // }

        if ($request->ajax()) {
            return DataTables::of($query)

                ->orderColumn('withdraw_date', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.withdraw_date', $order);
                })

                ->orderColumn('withdraw_type', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.withdraw_type', $order);
                })

                ->orderColumn('code', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.code', $order);
                })

                ->orderColumn('balance', function ($query, $order) {
                    $query->join('accounts as acc', 'acc.id', '=', 'trade_withdrawal.account_id')
                        ->orderBy('acc.balance', $order)
                        ->select('trade_withdrawal.*');
                })

                ->orderColumn('created_date', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.created_at', $order);
                })

                ->orderColumn('created_time', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.created_at', $order);
                })

                ->orderColumn('new_total_deposit', function ($query, $order) {
                    $query->join('trade_deposits as td', 'td.user_id', '=', 'trade_withdrawal.user_id')
                        ->where('td.status', 1)
                        ->selectRaw('trade_withdrawal.*, COALESCE(SUM(td.deposit_amount), 0) as total_deposit')
                        ->groupBy('trade_withdrawal.id')
                        ->orderBy('total_deposit', $order);
                })

                ->orderColumn('new_total_withdrawal', function ($query, $order) {
                    $query->join('trade_withdrawal as tw', function ($join) {
                            $join->on('tw.user_id', '=', 'trade_withdrawal.user_id')
                                ->where('tw.status', 1);
                        })
                        ->selectRaw('trade_withdrawal.*, COALESCE(SUM(tw.withdrawal_amount), 0) as total_withdrawal')
                        ->groupBy('trade_withdrawal.id')
                        ->orderBy('total_withdrawal', $order);
                })

                ->orderColumn('floating_balance', function ($query, $order) {
                    $query->leftJoin('accounts as live_acc', function ($join) {
                            $join->on('live_acc.user_id', '=', 'trade_withdrawal.user_id')
                                ->where('live_acc.balance', '>', 0);
                        })
                        ->orderByRaw("COALESCE(SUM(live_acc.balance), 0) {$order}")
                        ->groupBy('trade_withdrawal.id')
                        ->select('trade_withdrawal.*');
                })

                ->orderColumn('transaction_fee', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.transaction_fee', $order);
                })

                ->orderColumn('withdrawal_amount', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.withdrawal_amount', $order);
                })

                ->orderColumn('status', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.status', $order);
                })

                ->orderColumn('email', function ($query, $order) {
                    $query->orderBy('trade_withdrawal.email', $order);
                })

                ->orderColumn('name', function ($query, $order) {
                    $query->join('aspnetusers as u', 'u.email', '=', 'trade_withdrawal.email')
                        ->orderBy('u.fullname', $order)
                        ->select('trade_withdrawal.*'); // Required to avoid column conflicts
                })


                ->addColumn('account_no', function ($row) {
                    return $row->account ? $row->account->code : '';
                })
                ->addColumn('amount', function ($row) {
                    // dd($row);
                    return $row->withdrawal_amount;
                })
                ->addColumn('balance', function ($row) {
                    // dd($row);
                    return $row->account ? ($row->account->balance <= 0 ? '0.00' : $row->account->balance) : '';
                })
                ->addColumn('withdraw_type', function ($row) {
                    return $row->withdraw_type;
                })
                ->addColumn('withdraw_from', function ($row) {
                    // if ($row->withdraw_to) {
                    //     $acc = Account::where('id', $row->withdraw_to)->first();
                    // }
                    return ($row->code);
                })
                ->addColumn('withdraw_to', function ($row) {
                    // if ($row->withdraw_to) {
                    //     $acc = Account::where('id', $row->withdraw_to)->first();
                    // }
                    return ($row->withdraw_to) ? $row->withdraw_to : (($row->withdraw_to == null && $row->withdraw_type == 'Trade_Withdrawal') ? $row->clientWallet->wallet_address : $row->withdraw_type);
                })
                ->addColumn('withdraw_date', function ($row) {
                    // $date = date('Y-m-d', strtotime($row->withdraw_date));
                    $date = Carbon::parse($row->withdraw_date)->addHours(3)->format('Y-m-d');
                    // $time = date('H:i:s', strtotime($row->withdraw_date));
                    $time = Carbon::parse($row->withdraw_date)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    } elseif ($row->status == 2) {
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    } else {
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function ($row) {
                    // return "<a href='/admin/trading_withdrawal_details?id={$row->id}' class=' style='font-size: 13px;padding: 2px 20px;'><i class='fe fe-eye fs-14 text-info'></i></a>";
                    return "<a class='btn btn-sm btn-primary' href='/admin/trading_withdrawal_details?id={$row->id}'>View</a>";
                })
                ->addColumn('name', function ($row) {
                    return $row->user->fullname;
                })
                ->editColumn('email', function ($row) {
                    $fullname = $row->user
                        ? ($row->user->fullname)
                        : 'Unknown';
                    $email = $row->user ? $row->user->email : 'No Email';
                    return "<a href='/admin/client_details/{$row->user->id}'>
                                <div class='d-flex align-items-center'>
                                    <div class='me-2'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                    </div>
                                    <div>
                                        <div class='lh-1'><span>{$fullname}</span></div>
                                        <div class='lh-1'><span class='fs-11 text-muted'>{$email}</span></div>
                                    </div>
                                </div>
                            </a>";
                })
                ->addColumn('withdrawal_amount', function ($row) {
                    return $row->withdrawal_amount - ($row->promo_deduction ?? 0);
                })
                ->addColumn('withdrawal_fee', function ($row) {
                    return $row->transaction_fee;
                })

                ->addColumn('balance', function ($row) {
                    return $row->account->balance <= 0 ? '0.00' : $row->account->balance;
                })

                ->addColumn('floating_balance', function ($row) {
                    $balance = round($row->user->liveAccounts->where('balance', '>', 0)->sum('balance') ?? 0,2);
                    return $balance;
                })

                ->addColumn('new_total_deposit', function ($row) {
                    $total_deposit = round($row->user->NewTotalDeposit ?? 0, 2);
                    return $total_deposit;
                })

                ->addColumn('new_total_withdrawal', function ($row) {
                    $new_total_withdrawal = round($row->user->NewTotalWithdrawal ?? 0, 2);
                    return $new_total_withdrawal;
                })
                ->addColumn('total_withdrawal', function ($row) {
                    return ($row->transaction_fee + $row->withdrawal_amount);
                })
                ->addColumn('created_date', function ($row) {
                    // return date('Y-m-d', strtotime($row->withdraw_date));
                    return Carbon::parse($row->withdraw_date)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    // return date('H:i:s', strtotime($row->withdraw_date));
                    return Carbon::parse($row->withdraw_date)->addHours(3)->format('H:i:s');
                })
                ->addColumn('client_email', function ($row) {
                    return $row->user->email;
                })
                ->rawColumns(['account_no', 'amount', 'withdraw_type', 'withdraw_to', 'withdraw_date', 'status', 'action', 'name', 'email', 'withdrawal_amount', 'withdrawal_fee', 'total_withdrawal', 'client_email'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    // public function getPendingWalletDeposit()
    // {

    //     $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
    //     if (session('userData')['userRole'] == "Relationship Manager") {
    //         $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "' and ";
    //     } else {
    //         $rmCondition .= " where (1) and ";
    //     }
    //     header('Content-Type: application/json');
    //     $sql = "SELECT (user.id) as enc_id,user.fullname as fullname,trs.* from wallet_deposit trs " . $rmCondition . " trs.Status = 0 order by trs.id desc";
    //     info("getPendingWalletDeposit " . $sql);
    //     $query = DB::select($sql);
    //     $results = $query;
    //     $data = [];
    //     foreach ($results as $row) {
    //         $data[] = [
    //             'id' => 'WDID' . sprintf("%05d", $row->id),

    //             'email' => $row->email,
    //             'enc_id' => $row->enc_id,
    //             'fullname' => $row->fullname,
    //             'amount' => '$' . $row->deposit_amount,
    //             'payment_mode' => $row->deposit_type,
    //             'deposit_date' => $row->deposted_date,
    //             'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
    //                 '<span class="badge bg-outline-primary">Pending</span>'),
    //             'action' => ' <a class="btn btn-sm btn-primary" href="/admin/wallet_deposit_details?id=' . ($row->id) . '">View</a>'
    //         ];
    //     }

    //     return ['data' => $data];
    // }
    // public function getPendingWalletWithdrawal()
    // {

    //     // $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
    //     // if (session('userData')['userRole'] == "Relationship Manager") {
    //     //     $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "' and ";
    //     // } else {
    //     //     $rmCondition .= " where (1) and ";
    //     // }
    //     // header('Content-Type: application/json');
    //     // $sql = "SELECT (user.id) as enc_id,user.fullname as fullname,trs.* from wallet_withdraw trs " . $rmCondition . " trs.Status = 0 order by trs.id desc";
    //     // $query = DB::select($sql);
    //     // $results = $query;

    //     $query = WalletWithdraw::with(['user']);

    //     // Add conditions based on session and GET parameters
    //     if (session('userData')['userRole'] == "Relationship Manager") {
    //         $rmId = session('alogin');
    //         $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
    //             $q->where('rm_id', $rmId);
    //         });
    //     } else {
    //         $query->where('Status', 0);
    //     }

    //     // Fetch data
    //     $results = $query->orderByDesc('id')->get();
    //     $data = [];

    //     foreach ($results as $row) {
    //         $data[] = [
    //             'id' => 'WWID' . sprintf("%05d", $row->id),
    //             'email' => $row->email,
    //             'enc_id' => $row->enc_id,
    //             'fullname' => $row->user->fullname,
    //             'amount' => '$' . $row->withdraw_amount,
    //             'fee' => '$' . $row->withdraw_transaction_fee,
    //             'payment_mode' => $row->withdraw_type,
    //             'withdraw_date' => $row->withdraw_date,
    //             'status' => $row->Status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
    //                 '<span class="badge bg-outline-primary">Pending</span>'),
    //             'action' => ' <a class="btn btn-sm btn-primary" href="/admin/wallet_withdrawal_details?id=' . $row->id . '">View</a>'
    //         ];
    //     }
    //     return ['data' => $data];
    // }
    // public function getPendingTradingDeposit()
    // {

    //     $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
    //     if (session('userData')['userRole'] == "Relationship Manager") {
    //         $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "' and ";
    //     } else {
    //         $rmCondition .= " where (1) and ";
    //     }
    //     header('Content-Type: application/json');
    //     $sql = "SELECT trs.id as raw_erc,trs.* from trade_deposits trs " . $rmCondition . " trs.Status = 0 order by trs.id desc";
    //     $query = DB::select($sql);
    //     $results = $query;
    //     $data = [];
    //     foreach ($results as $row) {
    //         $data[] = [
    //             'id' => 'TDID' . sprintf("%05d", $row->id),
    //             'enc_id' => $row->raw_erc,
    //             'account_no' => $row->code,
    //             'amount' => '$' . $row->deposit_amount,
    //             'deposit_type' => $row->deposit_type,
    //             'deposit_from' => $row->deposit_from,
    //             'deposit_date' => $row->deposted_date,
    //             'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
    //                 '<span class="badge bg-outline-primary">Pending</span>'),
    //             'action' => ' <a class="btn btn-sm btn-primary" href="/admin/trading_deposit_details?id=' . ($row->id) . '">View</a>'
    //         ];
    //     }
    //     return ['data' => $data];
    // }
    // public function getPendingTradingWithdrawal()
    // {

    //     $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
    //     if (session('userData')['userRole'] == "Relationship Manager") {
    //         $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "' and ";
    //     } else {
    //         $rmCondition .= " where (1) and ";
    //     }
    //     header('Content-Type: application/json');
    //     $sql = "SELECT trs.* from trade_withdrawal trs " . $rmCondition . " trs.Status = 0 order by trs.id desc";
    //     $query = DB::select($sql);
    //     $results = $query;
    //     $data = [];
    //     foreach ($results as $row) {
    //         $data[] = [
    //             'id' => 'TWID' . sprintf("%05d", $row->id),
    //             'account_no' => $row->code,
    //             'amount' => '$' . $row->withdrawal_amount,
    //             'withdraw_type' => $row->withdraw_type,
    //             'withdraw_to' => $row->withdraw_to,
    //             'withdraw_date' => $row->withdraw_date,
    //             'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
    //                 '<span class="badge bg-outline-primary">Pending</span>'),
    //             'action' => ' <a class="btn btn-sm btn-primary" href="/admin/trading_withdrawal_details?id=' . $row->id . '">View</a>'
    //         ];
    //     }
    //     return ['data' => $data];
    // }
    public function getPendingInternalTransfer()
    {

        $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
        if (session('userData')['userRole'] == "Relationship Manager") {
            $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "' and ";
        } else {
            $rmCondition .= " where (1) and ";
        }
        header('Content-Type: application/json');
        $sql = "SELECT * from internaltransfer trs " . $rmCondition . " trs.Status = 0 order by trs.itIndex desc";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id' => 'ITID' . sprintf("%05d", $row->id),
                'name' => $row->clientName,
                'amount' => '$' . $row->amount,
                'transfer_from' => $row->TransferFromAccountId,
                'transfer_to' => $row->TransferToAccountId,
                'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
                'action' => ' <a class="btn btn-sm btn-primary" href="/admin/internal_transfer_details?id=' . $row->itIndex . '">View</a>'
            ];
        }
        return ['data' => $data];
    }

    public function getKYCHistory()
    {

        $rmCondition = " left join aspnetusers user on(user.email=kyc.email) ";
        if (session('userData')['userRole'] == "Relationship Manager") {
            $rmCondition .= " left join relationship_manager rm on (rm.user_id=kyc.email) where rm.rm_id='" . session('alogin') . "' and";
        }
        header('Content-Type: application/json');
        $sql = "SELECT kyc.id as id,kyc.id as id,max(registered_date_js) as date,group_concat(kyc.kyc_type) as kyc_type,
  group_concat(concat(kyc.kyc_type,'=',kyc.Status) SEPARATOR '#') as summary,
  kyc.email as email,sum(kyc.Status) as status,aspnetusers.fullname,(kyc.email) as enc_id from kyc_update kyc left join aspnetusers on aspnetusers.email = kyc.email " . $rmCondition . " group by kyc.email order by kyc.id desc";
        $query = DB::select($sql);
        $results = $query;

        return ['data' => $results];
    }
    public function getBankDetails()
    {

        $rmCondition = " left join aspnetusers user on(user.id=clientbankdetails.user_id) ";
        if (session('userData')['userRole'] == "Relationship Manager") {
            $rmCondition .= " left join relationship_manager rm on (rm.user_id=clientbankdetails.user_id) where rm.rm_id='" . session('alogin') . "'";
        } else {
            $rmCondition .= " where (1)";
        }

        header('Content-Type: application/json');
        $sql = "SELECT clientbankdetails.* from clientbankdetails " . $rmCondition . " order by clientbankdetails.id desc";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id' => $row->id,
                'email' => $row->userId,
                'account_name' => $row->ClientName,
                'bank_name' => $row->bankName,
                'account_no' => $row->accountNumber,
                'ifsc' => $row->code,
                'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
                'action' => ' <a class="btn btn-sm btn-primary" href="/admin/view_bank_details?id=' . $row->id . '">View</a>'
            ];
        }
        return ['data' => $data];
    }
    public function getAdminUsers()
    {

        header('Content-Type: application/json');
        $sql = "SELECT e.client_index, (e.id) as enc_id,e.username, e.email, e.number, e.userRole, e.gender, e.dob, e.address, e.website, e.uid, e.company_name, e.company_address, e.company_number, e.country,e.state, e.city, e.zipcode, e.two_factor_secret, e.two_factor_recovery_codes, e.two_factor_confirmed_at, 0 as permissions_count, e.status,r.name,r.id
                FROM emplist e
                LEFT JOIN roles r ON e.role_id = r.id
                WHERE e.deleted_at IS NULL
                -- LEFT JOIN pages ON p.page_id = pages.page_id
                GROUP BY e.id";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        $admin = Auth::guard('admin')->user();
        foreach ($results as $row) {
            $dat = $row;
            $dat->status = $row->status == 1 ? '<span class="badge bg-outline-success">Active</span>' : '<span class="badge bg-outline-danger">Inactive</span>';
            $dat->fa_status = (($row->two_factor_secret != null) && ($row->two_factor_recovery_codes != null) && ($row->two_factor_confirmed_at != null)) ? '<span class="badge bg-outline-success">Enabled</span>' : '<span class="badge bg-outline-danger">Disabled</span>';
            if ($admin->can('employee:update')) {
                $dat->action = '<a data-id="' . $row->id . '" class="btn btn-sm btn-secondary update-user" data-bs-toggle="modal" data-bs-target="#updateUserModal" >Edit</a>';
            } else {
                $dat->action = '';
            }
            // $dat->action = (session('userData')['userRole'] == "Super Admin" ? '<a data-id="' . $row->client_index . '" class="btn btn-sm btn-secondary update-user" data-bs-toggle="modal" data-bs-target="#updateUserModal" >Edit</a>' : '');
            $data[] = $dat;
        }
        return ['data' => $data];
    }

    public function getMT5Category($type = "category")
    {

        header('Content-Type: application/json');
        $sql = "SELECT * from mt5_group_categories where mt5_grp_cat_type = '" . $type . "' order by mt5_grp_cat_id";
        $query = DB::select($sql);
        $results = $query;
        return ['data' => $results];
    }


    public function getMT5Groups($type = NULL)
    {

        header('Content-Type: application/json');
        if ($type == NULL) {
            $sql = "SELECT * from account_types order by display_priority desc";
        } else {
            $sql = "SELECT * from account_types where (ac_category) = '$type' order by display_priority asc";
        }
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $dat = $row;
            $dat->enc_id = ($row->ac_index);
            $dat->ib_status = $row->ib_enabled == 1 ? '<span class="badge bg-outline-success">Active</span>' : '<span class="badge bg-outline-danger">Inactive</span>';
            $dat->acc_status = $row->status == 1 ? '<span class="badge bg-outline-success">Active</span>' : '<span class="badge bg-outline-danger">Inactive</span>';
            $data[] = $dat;
        }
        return ['data' => $data];
    }

    public function getCompetitionGroups(Request $request)
    {
        $type = $request->input('type');
        $status = $request->input('status', 'active'); // active, ended, or all

        // Build query using Eloquent to get competitions (account_types with competition_start_date)
        $query = AccountType::whereNotNull('competition_start_date');

        // Apply type filter if provided
        if ($type != NULL) {
            $query->where('ac_category', $type);
        }

        // Filter by competition status (active or ended)
        $now = now('UTC');
        if ($status === 'active') {
            // Active competitions: end date is in the future
            $query->where('competition_end_date', '>=', $now);
        } elseif ($status === 'ended') {
            // Ended competitions: end date is in the past
            $query->where('competition_end_date', '<', $now);
        }
        // If status is 'all', don't apply date filter

        // Get DataTables parameters
        $draw = $request->input('draw');
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $searchValue = $request->input('search.value');
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDirection = $request->input('order.0.dir', 'desc');

        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('ac_name', 'like', "%{$searchValue}%")
                    ->orWhere('ac_group', 'like', "%{$searchValue}%")
                    ->orWhere('ac_min_deposit', 'like', "%{$searchValue}%");
            });
        }

        // Count total records before pagination
        $totalRecords = AccountType::whereNotNull('competition_start_date')->count();
        $filteredRecords = $query->count();

        // Define columns for ordering
        $columns = [
            'ac_name',
            'display_priority',
            'competition_start_date',
            'competition_end_date',
            'total_participants', // Can't order by this (computed)
            'prize',
            'leaderboard', // Can't order by this (action)
            'ac_group',
            'ac_min_deposit',
            'ac_spread',
            'status',
            'is_client_group',
            'enc_id' // Can't order by this (action)
        ];

        // Apply ordering if valid column
        if (isset($columns[$orderColumnIndex]) && !in_array($columns[$orderColumnIndex], ['total_participants', 'leaderboard', 'enc_id'])) {
            $query->orderBy($columns[$orderColumnIndex], $orderDirection);
        } else {
            // Default ordering
            $query->orderBy('display_priority', 'desc');
        }

        // Apply pagination
        $results = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($results as $row) {
            $dat = $row;
            $url = route('admin.competition.leaderboard', [
                'competition_id' => $row->id,
            ]);
            $total_participants = Account::where('competition_product_id', $row->id)->count();
            $dat->leaderboard = '<a href="' . $url . '"
                                class="mt-1 btn btn-sm btn-outline-primary"
                                style="font-size: 0.75rem; padding: 2px 6px;"
                                target="_blank">
                                View Leaderboard
                                </a>';
            $dat->total_participants = $total_participants;
            $dat->enc_id = ($row->ac_index);
            $dat->ib_status = $row->ib_enabled == 1 ? '<span class="badge bg-outline-success">Active</span>' : '<span class="badge bg-outline-danger">Inactive</span>';
            $dat->acc_status = $row->status == 1 ? '<span class="badge bg-outline-success">Active</span>' : '<span class="badge bg-outline-danger">Inactive</span>';
            $data[] = $dat;
        }

        return [
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ];
    }

    public function getIbGroups($type = NULL)
    {

        header('Content-Type: application/json');
        if ($type == NULL) {
            $sql = "SELECT account_types.*,ib_categories.ib_cat_name as ib_plan from account_types left join ib_categories on ib_categories.ib_cat_id = account_types.acc_ib_cat ";
        } else {
            $sql = "SELECT account_types.*,ib_categories.ib_cat_name as ib_plan from account_types left join ib_categories on ib_categories.ib_cat_id = account_types.acc_ib_cat where (acc_ib_cat) = '$type'";
        }
        $query = DB::select($sql);
        $results = $query;
        return ['data' => $results];
    }

    public function getIbPlans($type = NULL)
    {

        header('Content-Type: application/json');
        if ($type == NULL) {
            $sql = "SELECT ib_plans.*,ib_categories.ib_cat_name as ib_plan,account_types.ac_name as ac_group from ib_plans left join account_types on account_types.ac_index = ib_plans.ib_acc_type_id left join ib_categories on ib_categories.ib_cat_id = ib_plans.ib_plan_cat_id where deleted_at is NULL";
        } else {
            $sql = "SELECT ib_plans.*,ib_categories.ib_cat_name as ib_plan,account_types.ac_name as ac_group from ib_plans left join account_types on account_types.ac_index = ib_plans.ib_acc_type_id left join ib_categories on ib_categories.ib_cat_id = ib_plans.ib_plan_cat_id where (ib_plan_cat_id) = '$type' and deleted_at is NULL";
        }
        $query = DB::select($sql);
        $results = $query;
        return ['data' => $results];
    }

    public function getRoles()
    {

        // header('Content-Type: application/json');
        $sql = "SELECT * from roles";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $dat = $row;
            $dat->status = $row->is_active == 1 ? '<span class="badge bg-outline-success">Active</span>' : '<span class="badge bg-outline-danger">Inactive</span>';
            $dat->action = ' <a data-id="' . $row->id . '" class="btn btn-sm btn-secondary me-1 update-role" href="#">Edit</a>' . ($row->is_active == 1 ? '<a class="btn btn-sm btn-danger" href="#" onclick="updateStatus(`' . $row->id . '`,0)">Deactivate</a>' : '<a class="btn btn-sm btn-success" href="#" onclick="updateStatus(`' . $row->id . '`,1)">Activate</a>');

            $data[] = $dat;
        }
        return ['data' => $data];
    }
    public function getRolePermisions()
    {


        header('Content-Type: application/json');
        $sql = "SELECT p.id,r.name,pg.pagename, p.created_at,p.updated_at from permissions p left join roles r on(p.role_id = r.role_id) left join pages pg on (p.page_id=pg.page_id)";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $dat = $row;
            $dat->action = ' <a class="btn btn-sm btn-danger disabled" href="#">DELETE</a>';
            $data[] = $dat;
        }
        return ['data' => $data];
    }
    public function getAllTickets()
    {


        header('Content-Type: application/json');
        $sql = "SELECT * FROM  tickets";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $dat = $row;
            $dat->status = $row->status == 'Open' ? '<span class="badge bg-outline-success">Open</span>' : '<span class="badge bg-outline-danger">Closed</span>';
            $data[] = $dat;
        }
        return ['data' => $data];
    }
    public function getOpenTickets()
    {
        header('Content-Type: application/json');
        $sql = "SELECT * FROM  tickets where Status='Open'";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $dat = $row;
            $dat->status = $row->status == 'Open' ? '<span class="badge bg-outline-success">Open</span>' : '<span class="badge bg-outline-danger">Closed</span>';
            $data[] = $dat;
        }
        return ['data' => $data];
    }
    public function getClosedTickets()
    {
        header('Content-Type: application/json');
        $sql = "SELECT * FROM  tickets where Status='Closed'";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $dat = $row;
            $dat->status = $row->status == 'Open' ? '<span class="badge bg-outline-success">Open</span>' : '<span class="badge bg-outline-danger">Closed</span>';
            $data[] = $dat;
        }
        return ['data' => $data];
    }
    public function getRoleDetails($id)
    {

        // header('Content-Type: application/json');
        $role = DB::table('roles')->where('id', $id)->first();
        if (($role)) {
            return $role;
        } else {
            // return [];
            return response()->json(['error' => 'Role not found'], 404);
        }
    }
    public function getPaymentGateways()
    {
        header('Content-Type: application/json');
        $sql = "SELECT * FROM  available_payment";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $dat = $row;
            $dat->action = '<a data-id="' . $row->id . '"  class="btn btn-sm btn-secondary me-1 update-payment" href="#">Edit</a> <a href="#" onclick="deletePayment(`' . $row->id . '`)" data-id="' . $row->id . '"  class="btn btn-sm btn-danger me-1 delete-payment" href="#">Delete</a>';
            $data[] = $dat;
        }
        return ['data' => $data];
    }
    public function ibEnroll()
    {

        $uid = uniqid();
        $code = md5(uniqid(rand()));
        $user = $_POST["user"];
        try {
            DB::table('ib1')->insert([
                'uid' => $uid,
                'email' => $user['email'],
                'name' => $user['fullname'],
                'password' => $user['password'],
                'number' => $user['number'],
                'username' => $user['email'],
                'emailToken' => $code,
                'status' => 0
            ]);
            return ["true"];
        } catch (Exception $e) {
            return ["false"];
        }
    }

    public function getLatestDeposit($id)
    {
        // Get wallet deposits
        $walletDeposits = WalletDeposit::where('user_id', $id)
            ->whereNotIn('deposit_type', ['CRM', 'Wallet Transfer'])
            ->get();

        // Get trade deposits
        $tradeDeposits = TradeDeposit::where('user_id', $id)
            ->whereNotIn('deposit_type', ['Internal Transfer', 'CRM', 'Wallet Transfer', 'Commission Transfer'])
            ->get();

        // Merge both collections and sort by id descending
        $results = $walletDeposits->merge($tradeDeposits)
            ->sortByDesc('id')
            ->values(); // reset the keys

        // Prepare the data array
        $data = $results->map(function ($row) {
            $link = '';

            if ($row->deposit_type == 'CryptoChill') {
                $callback_data = json_decode($row->callback_data, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error("JSON Decode Error: " . json_last_error_msg(), [
                        'row_id' => $row->id,
                        'callback_data' => $row->callback_data
                    ]);
                }

                if (isset($callback_data['transaction']['invoice']['id'])) {
                    $invoiceId = $callback_data['transaction']['invoice']['id'];
                    $link = 'https://uniwire.com/invoice/' . $invoiceId;
                } else {
                    Log::warning("Missing invoice ID in callback_data", [
                        'row_id' => $row->id,
                        'callback_data' => $callback_data
                    ]);
                }
            } elseif ($row->deposit_type == 'CreditCardPayissa') {
                $invoiceId = $row->transaction_id ?? null;
                if ($invoiceId) {
                    $link = 'https://blockscan.com/tx/' . $invoiceId;
                } else {
                    Log::warning("Missing transaction_id for CreditCardPayissa", [
                        'row_id' => $row->id
                    ]);
                }
            }

            return [
                'created_on' => $row->deposted_date
                    ? Carbon::parse($row->deposted_date)->addHours(3)->format('Y-m-d H:i:s')
                    : null,
                'from_to' => $row->code ? $row->code : 'Wallet',
                'payment_method' => '<a class="text-success" href="' . $link . '">' . $row->deposit_type . '</a>',
                'amount' => '$' . $row->deposit_amount,
                'status' => match ($row->status) {
                    1 => '<div class="badge bg-outline-success">Approved</div>',
                    2 => '<span class="badge bg-outline-danger">Rejected</span>',
                    default => '<span class="badge bg-outline-primary">Pending</span>',
                },
                'action' => '<a class="btn btn-sm btn-primary" href="/admin/trading_deposit_details?id=' . $row->id . '">View</a>'
            ];
        });

        return ['data' => $data];
    }


    public function getLatestWithdrawal($id)
    {
        // header('Content-Type: application/json');
        // $sql = "SELECT * from trade_withdrawal where email='" . $id . "' AND withdraw_type != 'Internal Transfer' order by id desc";
        // $query = DB::select($sql);
        $WalletWithdraw = WalletWithdraw::with('user')
            ->where('user_id', $id)
            ->where('withdraw_type', ['Wallet Withdrawal'])
            ->orderBy('withdraw_date', 'desc')
            ->get();

        $TradeWithdrawals = TradeWithdrawals::with('user')
            ->where('user_id', $id)
            ->where('withdraw_type', ['Trade Withdrawal'])
            ->orderBy('withdraw_date', 'desc')
            ->get();

        $results = $WalletWithdraw->merge($TradeWithdrawals)
            ->sortByDesc('id')
            ->values(); // reset the keys
        $data = [];
        foreach ($results as $row) {
            // dd($row);
            $amount = isset($row->withdraw_amount) ? $row->withdraw_amount : $row->withdrawal_amount;
            $fee = isset($row->withdraw_transaction_fee) ? $row->withdraw_transaction_fee : $row->transaction_fee;

            $link = '';
            if ($row->status == 1 && !empty($row->payout_res)) {
                // Decode JSON
                $payoutData = json_decode($row->payout_res, true);

                // Get txid
                $txid = $payoutData['result']['txid'] ?? null;

                // Example: BTC, ETH_TRC, ETH-ERC20 → ETH
                $kind = $payoutData['result']['kind'] ?? '';
                $coin = strtoupper(preg_split('/[^a-zA-Z]/', $kind)[0] ?? '');

                if ($coin == 'ETH') {
                    $link = "https://etherscan.io/tx/{$txid}";
                } elseif ($coin != 'USDT' && $coin != '') {
                    $link = "https://www.blockchain.com/explorer/transactions/{$coin}/{$txid}";
                } elseif ($coin == 'USDT') {
                    $link = "https://tokenview.io/en/search/{$txid}";
                }
            }
            $paymentMethod = $link
                ? '<a class="text-success" target="_blank" rel="noopener noreferrer" href="' . $link . '">' . $row->withdraw_type . '</a>'
                : '<span class="text-success">' . $row->withdraw_type . '</span>';
            $status = '<span class="badge bg-outline-primary">Pending</span>';

            if ($row->status == 1) {
                if($row->admin_remark == 'new' || $row->admin_remark == 'draft'){
                    $status = '<span class="badge bg-outline-danger">Processing (Cryptochill Draft)</span>';
                } else {
                    $status = '<div class="badge bg-outline-success">' . $row->admin_remark . '</div>';
                }
            } elseif ($row->status == 2) {
                $status = '<span class="badge bg-outline-danger">Cancelled by Admin</span>';
            } elseif ($row->status == 3) {
                $status = '<span class="badge bg-outline-danger">Cancelled by User</span>';
            }

            $data[] = [
                'created_on' => Carbon::parse($row->withdraw_date)->addHours(3)->format('Y-m-d H:i:s'),
                'from_to' => $row->code ?? 'Wallet',
                'payment_method' => $paymentMethod,
                'amount' => '$' . number_format((float)$amount, 2),
                'fee' => '$' . number_format((float)$fee, 2),
                'status' => $status,
                'action' => ' <a class="btn btn-sm btn-primary" href="/admin/trading_withdrawal_details?id=' . ($row->id) . '">View</a>'
            ];
        }
        return ['data' => $data];
    }
    public function getLatestTransfer($id)
    {
        // header('Content-Type: application/json');
        // $sql = "SELECT * from trade_deposits where deposit_type IN ('Internal Transfer', 'CRM', 'Wallet Transfer')  and user_id='" . $id . "'  order by id desc";
        // $query = DB::select($sql);
        $query = TradeDeposit::whereIn('deposit_type', ['Internal Transfer', 'CRM', 'Wallet Transfer', 'IB Withdraw'])
            ->with('accountDepositFrom')
            ->where('user_id', $id)
            ->get();
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            if ($row->deposit_type == 'IB Withdraw' || $row->deposit_from == 'IB Commission') {
                $deposit_from = 'IB Wallet';
            } else {
                $deposit_from = $row->deposit_type;
            }
            $data[] = [
                'created_on' => Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d H:i:s'),
                'from' => ($row->deposit_from && $row->accountDepositFrom) ? $row->accountDepositFrom->code : $deposit_from,
                'to' => $row->code,
                'amount' => '$' . $row->deposit_amount,
                'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
            ];
        }
        return ['data' => $data];
    }
    public function getIbUsers2(Request $request)
    {

        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];

        // Base query with optimized JOINs instead of subqueries
        $rmCondition = Ib1::select([
            'ib1.*',
            DB::raw('COALESCE(SUM(ib_wallet.ib_wallet), 0) as total_deposit'),
            DB::raw('COALESCE(SUM(ib_wallet.ib_withdraw), 0) as total_withdrawal')
        ])
            ->leftJoin('ib_wallet', 'ib1.user_id', '=', 'ib_wallet.user_id')
            ->where('ib1.status', 1)
            ->whereNull('ib1.deleted_at')
            ->with(['user', 'planDetails.accountType'])
            ->groupBy('ib1.id', 'ib1.user_id', 'ib1.indexId', 'ib1.email', 'ib1.name', 'ib1.country', 'ib1.number', 'ib1.reg_date', 'ib1.status', 'ib1.ib_plan_details_id', 'ib1.created_at', 'ib1.updated_at', 'ib1.deleted_at');

        if ($role !== "Super Admin") {
            $rmCondition->whereHas('user');
        }

        // if ($role === "Relationship Manager") {
        //     $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
        //         $query->where('rm_id', $alogin);
        //     });
        // }

        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('user.employee', function ($q) use ($alogin) {
                $q->where('relationship_manager.rm_id', $alogin); // Filter based on rm_id in pivot
            });
        }

        // ✅ Log raw SQL query


        // $rmCondition->orderBy('id', 'desc');

        // dd($query);
        if ($request->ajax()) {
            try {
                return DataTables::of($rmCondition)
                    ->filter(function ($query) use ($request) {
                        if (!empty($request->search['value'])) {
                            $searchValue = $request->search['value'];
                            $query->where(function ($q) use ($searchValue) {
                                $q->where('ib1.id', 'LIKE', "%{$searchValue}%")
                                    ->orWhere('ib1.indexId', 'LIKE', "%{$searchValue}%")
                                    ->orWhere('ib1.email', 'LIKE', "%{$searchValue}%")
                                    ->orWhereRaw("DATE_FORMAT(ib1.created_at, '%Y-%m-%d') LIKE ?", ["%{$searchValue}%"])
                                    // ✅ Search using already calculated aggregated fields
                                    ->orHaving('total_deposit', 'LIKE', "%{$searchValue}%")
                                    ->orHaving('total_withdrawal', 'LIKE', "%{$searchValue}%");
                            });
                        }
                    })

                    ->orderColumn('total_deposit', function ($query, $order) {
                        $query->orderBy('total_deposit', $order);
                    })
                    ->orderColumn('total_withdrawal', function ($query, $order) {
                        $query->orderBy('total_withdrawal', $order);
                    })



                    ->addColumn('id', function ($row) {
                        return $row->id;
                    })
                    ->addColumn('agent_id', function ($row) {
                        return $row->indexId;
                    })
                    ->editColumn('name', function ($row) {
                        if ($row->planDetails) {
                            $small = $row->planDetails->accountType->ac_name != null ? $row->planDetails->accountType->ac_name : '';
                        } else {
                            $small = '';
                        }

                        return "<a href='/admin/client_details/{$row->user_id}'><div class='d-flex align-items-center'><div class='me-2'><svg xmlns='http://www.w3.org/2000/svg'width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg></div><div><div class='lh-1'><span>{$row->name}</span></div><div class='lh-1'><span class='fs-11 text-muted'>{$row->email}</span></div>{$small}</div></div></a>";
                    })

                    ->addColumn('total_deposit', function ($row) {
                        return "$" . number_format($row->total_deposit, 2);
                    })
                    ->addColumn('total_withdrawal', function ($row) {
                        return "$" . number_format($row->total_withdrawal, 2);
                    })

                    ->editColumn('ib_status', function ($row) {
                        return $row->status;
                    })

                    ->addColumn('status', function ($row) {
                        if ($row->status == 1) {
                            return "<button class='ibToggle badge btn-sm btn btn-outline-success'>
                                    <span class='status-value' data-status='1'>Active IB</span>
                                </button>";
                        } elseif ($row->status == 2) {
                            return "<button class='ibToggle badge btn-sm btn btn-outline-danger'>
                                    <span class='status-value' data-status='2'>Rejected</span>
                                </button>";
                        } elseif ($row->status == 0) {
                            return "<button class='ibToggle badge btn-sm btn btn-outline-info'>
                                    <span class='status-value' data-status='0'>IB Requested</span>
                                </button>";
                        } else {
                            return "<button class='ibToggle badge btn-sm btn btn-outline-primary'>
                                    <span class='status-value' data-status='null'>Not Requested</span>
                                </button>";
                        }
                    })
                    ->addColumn('date', function ($row) {
                        // $date = date('Y-m-d', strtotime($row->created_at));
                        $date = Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                        // $time = date('H:i:s', strtotime($row->created_at));
                        $time = Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                        return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                    })
                    ->addColumn('fullname', function ($row) {
                        return $row->user->fullname;
                    })
                    ->addColumn('fullemail', function ($row) {
                        return $row->email;
                    })
                    ->addColumn('created_date', function ($row) {
                        // return date('Y-m-d', strtotime($row->created_at));
                        return Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                    })
                    ->addColumn('created_time', function ($row) {
                        // return date('H:i:s', strtotime($row->created_at));
                        return Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                    })
                    ->addColumn('phone_number', function ($row) {
                        return $row->user->number;
                    })

                    ->rawColumns(['id', 'name', 'total_deposit', 'total_withdrawal', 'status', 'date'])

                    ->orderColumn('id', 'id $1')
                    ->orderColumn('name', 'id $1')
                    ->orderColumn('agent_id', 'id $1')
                    ->orderColumn('total_deposit', 'total_deposit $1')
                    ->orderColumn('total_withdrawal', 'total_withdrawal $1')
                    ->orderColumn('date', 'id $1')
                    ->make(true);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    //     public function getIbUsers()
    //     {

    //         $rmCondition = "";
    //         if (session('userData')['userRole'] == "Relationship Manager") {
    //             $rmCondition = "  left join relationship_manager rm on(rm.user_id=ib1.email) where rm.rm_id='" . session('alogin') . "'";
    //         }
    //         header('Content-Type: application/json');
    //         $sql = "SELECT
    //     ib1.*,
    //     account_types.ac_name as grp,
    //     sum(ib_wallet.ib_wallet) as deposit,
    //     sum(ib_wallet.ib_withdraw) as withdraw
    // FROM
    //     ib1
    // LEFT JOIN
    //     ib_wallet
    // ON
    //     ib1.email = ib_wallet.email
    // LEFT JOIN account_types on account_types.ac_index = ib1.indexId " . $rmCondition . "
    //     group by ib1.email";
    //         $query = DB::select($sql);
    //         $results = $query;
    //         $data = [];
    //         foreach ($results as $row) {
    //             $data[] = [
    //                 'id' => $row->id,
    //                 'enc' => ($row->user_id),
    //                 // 'ib_category_id' => $row->ib_category_id,
    //                 'ib_plan_details_id' => $row->ib_plan_details_id,
    //                 'grp' => $row->grp,
    //                 'name' => $row->name,
    //                 'email' => $row->email,
    //                 'country' => $row->country,
    //                 'number' => $row->number,
    //                 'date' => $row->reg_date,
    //                 'total_deposit' => $row->deposit ? '$' . $row->deposit : '$0',
    //                 'total_withdrawal' => $row->withdraw ? '$' . $row->withdraw : '$0',
    //                 'status' => $row->status
    //             ];
    //         }
    //         return ['data' => $data];
    //     }

    public function getPendingIbUsers2(Request $request)
    {
        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];

        // Base query

        $rmCondition = Ib1::where('status', 0)
            ->select('ib1.*')
            ->with(['user', 'ibWallet', 'planDetails.accountType'])
            ->where('status', 0);


        if ($role !== "Super Admin") {
            $rmCondition->whereHas('user');
        }

        // if ($role === "Relationship Manager") {
        //     $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
        //         $query->where('rm_id', $alogin);
        //     });
        // }
        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('user.employee', function ($q) use ($alogin) {
                $q->where('relationship_manager.rm_id', $alogin); // Filter based on rm_id in pivot
            });
        }

        $rmCondition->orderBy('id', 'desc');


        if ($request->ajax()) {
            return DataTables::of($rmCondition)
                ->filter(function ($rmCondition) use ($request) {
                    if (!empty($request->search['value'])) {
                        $searchValue = $request->search['value'];
                        $rmCondition->where(function ($q) use ($searchValue) {
                            $q->where('id', 'LIKE', "%{$searchValue}%")
                                ->orWhere('name', 'LIKE', "%{$searchValue}%")
                                ->orWhere('email', 'LIKE', "%{$searchValue}%")
                                // ->orWhere('total_deposit', 'LIKE', "%{$searchValue}%")
                                // ->orWhere('total_withdrawal', 'LIKE', "%{$searchValue}%")
                                ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%{$searchValue}%"]);
                        });
                    }
                })
                ->addColumn('id', function ($row) {
                    return $row->id;
                })
                ->addColumn('name', function ($row) {
                    if ($row->planDetails) {
                        $small = $row->planDetails->accountType->ac_name != null ? $row->planDetails->accountType->ac_name : '';
                    } else {
                        $small = '';
                    }

                    return "<a href='/admin/client_details/{$row->user_id}'><div class='d-flex align-items-center'><div class='me-2'><svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg></div><div><div class='lh-1'><span>{$row->name}</span></div><div class='lh-1'><span class='fs-11 text-muted'>{$row->email}</span></div>{$small}</div></div></a>";
                })
                ->addColumn('total_deposit', function ($row) {
                    $total_deposit = $row->ibWallet ? $row->ibWallet->sum('ib_wallet') : '$' + 0;
                    return $total_deposit;
                })
                ->addColumn('total_withdrawal', function ($row) {
                    $total_withdrawal = $row->ibWallet ? $row->ibWallet->sum('ib_withdraw') : '$' + 0;
                    return $total_withdrawal;
                })
                ->editColumn('ib_status', function ($row) {
                    return $row->status;
                })
                ->addColumn('date', function ($row) {
                    // $date = date('Y-m-d', strtotime($row->created_at));
                    $date = Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                    // $time = date('H:i:s', strtotime($row->created_at));
                    $time = Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return "<button class='ibToggle badge btn-sm btn btn-outline-success'>Active IB</button>";
                    } elseif ($row->status == 2) {
                        return "<button class='ibToggle badge btn-sm btn btn-outline-danger'>Rejected</button>";
                    } elseif ($row->status == 0) {
                        return "<button class='ibToggle badge btn-sm btn btn-outline-info'>IB Requested</button>";
                    } else {
                        return "<button class='ibToggle badge btn-sm btn btn-outline-primary'>Not Requested</button>";
                    }
                })
                ->addColumn('action', function ($row) {
                    return "<a href='/admin/trading_withdrawal_details?id={$row->id}' class=' style='font-size: 13px;padding: 2px 20px;'><i class='fe fe-eye fs-14 text-info'></i></a>";
                })
                ->addColumn('fullname', function ($row) {
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function ($row) {
                    return $row->email;
                })
                ->addColumn('created_date', function ($row) {
                    // return date('Y-m-d', strtotime($row->created_at));
                    return Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    // return date('H:i:s', strtotime($row->created_at));
                    return Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                })
                ->addColumn('checkbox', function ($row) {
                    return "<input type='checkbox' class='row-checkbox' >";
                })
                ->addColumn('ib_plan_details_id', function ($row) {
                    return $row->ib_plan_details_id ?? '';
                })
                ->rawColumns(['id', 'name', 'total_deposit', 'total_withdrawal', 'date', 'status', 'action', 'checkbox'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }



    //     public function getPendingIbUsers()
    //     {

    //         $rmCondition = " left join aspnetusers user on(user.email=ib1.email) ";
    //         if (session('userData')['userRole'] == "Relationship Manager") {
    //             $rmCondition .= "  left join relationship_manager rm on(rm.user_id=ib1.email) where rm.rm_id='" . session('alogin') . "'";
    //         } else {
    //             $rmCondition .= " where (1) ";
    //         }
    //         header('Content-Type: application/json');
    //         $sql = "SELECT
    //     ib1.*,
    //     account_types.ac_name as grp,
    //     sum(ib_wallet.ib_wallet) as deposit,
    //     sum(ib_wallet.ib_withdraw) as withdraw
    // FROM
    //     ib1
    // LEFT JOIN
    //     ib_wallet
    // ON
    //     ib1.email = ib_wallet.email
    // LEFT JOIN account_types on account_types.ac_index = ib1.indexId " . $rmCondition . "
    // and ib1.status = 0
    //     group by ib1.email";
    //         $query = DB::select($sql);
    //         $results = $query;
    //         $data = [];
    //         foreach ($results as $row) {
    //             $data[] = [
    //                 'id' => $row->indexId,
    //                 'enc' => ($row->user_id),
    //                 'acc_type' => $row->acc_type,
    //                 'grp' => $row->grp,
    //                 'name' => $row->name,
    //                 'email' => $row->email,
    //                 'country' => $row->country,
    //                 'number' => $row->number,
    //                 'date' => $row->reg_date,
    //                 'total_deposit' => $row->deposit ? '$' . $row->deposit : '$0',
    //                 'total_withdrawal' => $row->withdraw ? '$' . $row->withdraw : '$0',
    //                 'status' => $row->status
    //             ];
    //         }
    //         return ['data' => $data];
    //     }



    public function getAdminDetails($id)
    {

        header('Content-Type: application/json');
        // $sql = "SELECT * FROM  emplist WHERE id='" . $id;
        return EmployeeList::select("id", "role_id", 'username', 'email', 'gender', 'dob', 'number', 'address', 'company_name', 'status')->where("id", $id)->first();
        // $query = DB::select($sql);
        // $result = $query[0];
        // unset($result->password);
        // return $result;
    }

    public function deleteAdminUser($data)
    {
        header('Content-Type: application/json');
        $id = $data['id'] ?? null;

        if (!$id) {
            return ['status' => false, 'message' => 'User ID is required'];
        }

        $user = EmployeeList::find($id);

        if (!$user) {
            return ['status' => false, 'message' => 'User not found'];
        }

        // Check if user has Super Admin or admin role
        $role = $user->role;
        if (!$role || !in_array(strtolower($role->name), ['super admin', 'admin'])) {
            return ['status' => false, 'message' => 'Only users with Super Admin or admin role can be deleted'];
        }

        // Check if trying to delete yourself
        $currentUser = Auth::guard('admin')->user();
        if ($currentUser && $currentUser->id == $id) {
            return ['status' => false, 'message' => 'You cannot delete your own account'];
        }

        // Soft delete the user
        $user->delete();

        return ['status' => true, 'message' => 'User deleted successfully'];
    }
    public function getPaymentDetails($id)
    {

        header('Content-Type: application/json');
        $sql = "SELECT * FROM  available_payment WHERE id=" . $id;
        $query = DB::select($sql);
        $result = $query[0];
        return $result;
    }
    public function updateClientStatus($data)
    {
        header('Content-Type: application/json');

        $user_id = $data['client_id'];
        $email_confirmed = isset($data['email_confirmed']) && $data['email_confirmed'] === "on" ? 1 : 0;
        $user_status = isset($data['status']) && $data['status'] === "on" ? 1 : 0;
        $kyc_verify = isset($data['kyc_verify']) && $data['kyc_verify'] === "on" ? 1 : 0;

        $user = User::select('status', 'email', 'email_confirmed', 'kyc_verify')
            ->where("id", $user_id)
            ->first();
        $admin = Auth::guard('admin')->user();
        Gate::forUser($admin)->authorize('client:update', $user);
        try {

            $updated = User::where("id", $user_id)
                ->update([
                    'status' => $user_status,
                    'email_confirmed' => $email_confirmed,
                    'kyc_verify' => $kyc_verify,
                ]);
            activity()
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_email' => auth()->guard('admin')->user()->email,
                    'client_id' => $user_id,
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'user_id' => auth()->guard('admin')->user()->id,
                    'email_confirmed' => isset($data['email_confirmed']) ?? '',
                    'status' => isset($data['status']) ?? '',
                    'kyc_verify' => isset($data['kyc_verify']) ?? '',
                    'remark' => 'Update Client Status'
                ])
                ->event('update')
                ->log('Update Client Status');

            return ['success' => true];

            // if ($updated) {
            //     $data['client_id'] = $result->email;
            //     if ($result->status != $user_status) {
            //         $data['field'] = 'status';
            //         $data['value'] = $user_status;
            //         $data['user_id']=$result->id;
            //         $this->add_to_user_log($data);
            //     }
            //     if ($result->email_confirmed != $email_confirmed) {
            //         $data['field'] = 'email_confirmed';
            //         $data['value'] = $email_confirmed;
            //         $data['user_id']=$result->id;
            //         $this->add_to_user_log($data);
            //     }
            //     if ($result->kyc_verify != $kyc_verify) {
            //         $data['field'] = 'kyc_verify';
            //         $data['value'] = $kyc_verify;
            //         $data['user_id']=$result->id;
            //         $this->add_to_user_log($data);
            //     }
            //     echo json_encode(['success' => true]);
            // } else {
            //     echo json_encode(['success' => false, 'message' => 'No rows updated']);
            // }

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function add_to_user_log($data)
    {
        UserLog::create([
            'user_id' => $data['user_id'],
            'email' => $data['email'],
            'admin_email' => session('alogin'),
            'type' => $data['field'],
            'value' => $data['value']
        ]);
    }
    public function getIbList($id)
    {
        $ib_columns = collect(range(1, 15))->map(function ($n) {
            return "ib$n";
        })->toArray();

        $result = DB::table('aspnetusers')
            ->select($ib_columns)
            ->where(DB::raw('id'), '=', $id)
            ->first();

        return (array) $result;
    }

    public function getRMbyGroup($id)
    {

        $results = EmployeeList::with('role')
            ->whereHas('role', function ($query) {
                $query->where('name', 'Relationship Manager');
            })
            ->select('id', 'client_index', 'email', 'username')
            ->get();
        return $results;
    }

    public function getClientDetails($data)
    {

        $result = DB::table('aspnetusers')
            ->join('countries', 'aspnetusers.country', '=', 'countries.country_name')
            ->select(
                'aspnetusers.id',
                'aspnetusers.email',
                'aspnetusers.fullname',
                'aspnetusers.country',
                'aspnetusers.affiliate_id',
                // 'aspnetusers.country_code',
                // 'aspnetusers.number AS telephone',
                DB::raw('concat(countries.country_code) as country_code'),
                DB::raw('REGEXP_REPLACE(aspnetusers.number, concat(countries.country_code), "") AS telephone')
            )
            ->where('aspnetusers.id', '=', $data['id'])
            ->first();

        // dd($result);
        return (array) $result;
    }

    public function requestIB($request)
    {
        try {
            $clientId = $request['client_id'];
            $ibStatus = $request['ib_status'];
            $ibGroup = $request['ib_group'];
            $result = Ib1::with('user')->where('user_id', $clientId)->first();
            $admin = Auth::guard('admin')->user();
            Gate::forUser($admin)->authorize('ib:update', $result);
            if ($result) {
                $clientId = $result->user->id;
            }

            if (!$result) {
                $user = User::whereRaw('email = ?', [$clientId])->first();

                if ($user) {
                    $ib1 = new Ib1();
                    $ib1->uid = $user->uid;
                    $ib1->email = $user->email;
                    $ib1->password = $user->password;
                    $ib1->number = $user->number;
                    $ib1->username = $user->email;
                    $ib1->name = $user->fullname;
                    $ib1->country = $user->country;
                    $ib1->emailToken = $user->emailToken;
                    $ib1->status = $ibStatus;
                    $ib1->save();
                    // IbCreated event will fire automatically via model boot()
                }
            }


            // Get IB record and old status before update
            $ibRecord = Ib1::where('user_id', $clientId)->first();
            if (!$ibRecord) {
                return ['status' => false, 'message' => 'IB record not found'];
            }

            $oldStatus = $ibRecord->status;

            $updated = Ib1::where('user_id', $clientId)
                ->update([
                    'status' => $ibStatus,
                    'ib_plan_details_id' => $ibGroup,
                    // 'indexId' => random_int(100000, 999999),
                ]);

            // Fire IbStatusChanged event ONLY when status changes to approved (status = 1)
            if ($updated && $oldStatus != $ibStatus && $ibStatus == 1) {
                $ibRecord->refresh();
                Log::info('requestIB: Firing IbStatusChanged event (IB Approved)', [
                    'ib_id' => $ibRecord->id,
                    'old_status' => $oldStatus,
                    'new_status' => $ibStatus,
                ]);
                event(new IbStatusChanged($ibRecord, $oldStatus, $ibStatus));
            } elseif ($updated && $oldStatus != $ibStatus && $ibStatus != 1) {
                Log::info('requestIB: IB status changed but not approved, skipping event', [
                    'ib_id' => $ibRecord->id,
                    'old_status' => $oldStatus,
                    'new_status' => $ibStatus,
                ]);
            }
            activity()
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_email' => auth()->guard('admin')->user()->email,
                    'userRole' => auth()->guard('admin')->user()->userRole,
                    'username' => auth()->guard('admin')->user()->username,
                    'user_id' => auth()->guard('admin')->user()->id,
                    'client_id' => $clientId,
                    'ib_status' => $ibStatus,
                    'ib_group' => $ibGroup,
                    'remark' => 'Ib Request'
                ])
                ->event('update')
                ->log('Ib Request');
            $cacheKey = 'ib1_' . $clientId;
            Cache::forget($cacheKey);

            if ($updated) {
                // return response()->json(['status' => true, 'message' => 'IB details updated successfully.']);
                return ['status' => true];
            } else {
                return response()->json(['status' => false, 'message' => 'Failed to update IB details.']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function bulkIbApprove(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'client_id' => 'required', // Ensure it's a comma-separated string
            'ib_status' => 'required', // Assuming status is an integer
            'ib_group' => 'required', // Assuming group is an integer
        ]);

        $clientIds = explode(',', $validated['client_id']);
        $ibStatus = $validated['ib_status'];
        $ibGroup = $validated['ib_group'];

        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($clientIds as $clientId) {
            try {
                $admin = Auth::guard('admin')->user();

                // Attempt to fetch the IB record
                $ibRecord = Ib1::with('user')->find($clientId);
                // If IB record exists, authorize and update
                if ($ibRecord) {
                    Gate::forUser($admin)->authorize('ib:update', $ibRecord);

                    // Get old status before update
                    $oldStatus = $ibRecord->status;

                    $updated = $ibRecord->update([
                        'status' => $ibStatus,
                        'ib_plan_details_id' => $ibGroup,
                    ]);

                    // Fire IbStatusChanged event ONLY when status changes to approved (status = 1)
                    if ($updated && $oldStatus != $ibStatus && $ibStatus == 1) {
                        $ibRecord->refresh();
                        Log::info('bulkIbApprove: Firing IbStatusChanged event (IB Approved)', [
                            'ib_id' => $ibRecord->id,
                            'old_status' => $oldStatus,
                            'new_status' => $ibStatus,
                        ]);
                        event(new IbStatusChanged($ibRecord, $oldStatus, $ibStatus));
                    } elseif ($updated && $oldStatus != $ibStatus && $ibStatus != 1) {
                        Log::info('bulkIbApprove: IB status changed but not approved, skipping event', [
                            'ib_id' => $ibRecord->id,
                            'old_status' => $oldStatus,
                            'new_status' => $ibStatus,
                        ]);
                    }

                    activity()
                        ->causedBy(auth()->guard('admin')->user())
                        ->withProperties([
                            'ip' => request()->ip(),
                            'user_email' => auth()->guard('admin')->user()->email,
                            'userRole' => auth()->guard('admin')->user()->userRole,
                            'username' => auth()->guard('admin')->user()->username,
                            'user_id' => auth()->guard('admin')->user()->id,
                            'ib_plan_details_id' => $ibGroup,
                            'ib_status' => $ibStatus,
                            'ib_id' => $clientId,
                            'remark' => 'Bulk Ib Request'
                        ])
                        ->event('update')
                        ->log('Bulk Ib Request');

                    Cache::forget('ib1_' . $clientId);

                    $results[$clientId] = [
                        'status' => $updated,
                        'message' => $updated ? 'IB details updated successfully.' : 'Failed to update IB details.',
                    ];
                    $updated ? $successCount++ : $failureCount++;
                    continue;
                }

                // If no IB record, create one
                $user = User::find($clientId);

                if (!$user) {
                    $results[$clientId] = ['status' => false, 'message' => 'User not found'];
                    $failureCount++;
                    continue;
                }

                $ibRecord = new Ib1();
                $ibRecord->fill([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'password' => $user->password,
                    'number' => $user->number,
                    'username' => $user->email,
                    'name' => $user->fullname,
                    'country' => $user->country,
                    'emailToken' => $user->emailToken,
                    'status' => 1,
                ]);

                $ibRecord->save();
                // IbCreated event will fire automatically via model boot()

                $oldStatus = $ibRecord->status;
                $updated = $ibRecord->update([
                    'status' => $ibStatus,
                    'ib_plan_details_id' => $ibGroup,
                ]);

                // Fire IbStatusChanged event if status changed
                if ($updated && $oldStatus != $ibStatus) {
                    $ibRecord->refresh();
                    event(new IbStatusChanged($ibRecord, $oldStatus, $ibStatus));
                }

                Cache::forget('ib1_' . $clientId);

                $results[$clientId] = [
                    'status' => $updated,
                    'message' => $updated ? 'IB details created and updated successfully.' : 'Failed to update newly created IB details.',
                ];
                $updated ? $successCount++ : $failureCount++;
            } catch (\Exception $e) {
                $results[$clientId] = ['status' => false, 'message' => $e->getMessage()];
                $failureCount++;
            }
        }

        // Return results with a session flash
        return redirect()->back()->with([
            'success' => "Successfully updated {$successCount} Ib requests.",
            'error' => $failureCount > 0 ? "Failed to update {$failureCount} Ib requests." : null,
            'details' => $results, // Optional debugging details
        ]);
    }




    public function getClientIbProfile(Request $request)
    {
        try {
            $id = request('userId');
            $level = request('level');

            if (!$id || !$level) {
                return response()->json(['error' => 'User ID and level are required'], 400);
            }

            $user = User::with('ib')->find($id);

            if (!$user || !$user->ib) {
                return response()->json(['error' => 'User or IB profile not found'], 404);
            }

            $query = DB::table('aspnetusers as au')
                ->leftJoin('accounts as acc', function ($join) {
                    $join->on('acc.user_id', '=', 'au.id')
                        ->where('acc.demo', '=', 0);
                })
                ->leftJoin('trade_deposits as td', function ($join) {
                    $join->on('td.user_id', '=', 'au.id')
                        ->where('td.status', '=', 1);
                })
                ->where(function ($q) use ($user, $level) {
                    $q->orWhere("au.ib{$level}", $user->ib->referral_code);
                })
                ->select(
                    DB::raw('COUNT(DISTINCT acc.id) AS total_accounts'),
                    DB::raw('SUM(DISTINCT td.deposit_amount) AS total_deposit'),
                    'au.*'
                )
                ->groupBy('au.id');

            if ($request->ajax()) {
                return DataTables::of($query)

                    ->editColumn('email', function ($row) {
                        return " <div class='row align-items-center'>
                                <div class='col-auto pe-0'>
                                    <img src='/assets/images/ib_avatar.png' alt='user-image' class='rounded wid-55 hei-55' style='height:50px'>
                                </div>
                                <div class='col'>
                                    <h6 class='mb-2'>
                                        <span class='text-truncate w-100'>{$row->fullname}</span>
                                    </h6>
                                    <p class='mb-0 text-muted f-12'>
                                        <span class='text-truncate w-100'>{$row->email}</span>
                                    </p>
                                </div>
                            </div>";
                    })

                    ->editColumn('total_accounts', function ($row) {
                        return $row->total_accounts;
                    })

                    ->editColumn('total_deposit', function ($row) {
                        return $row->total_deposit ? $row->total_deposit : "$0.00";
                    })

                    ->editColumn('profile_status', function ($row) {
                        if ($row->email_confirmed == 1) {
                            return " <span  class='badge btn bg-success'>Active</span>";
                        } else {
                            return "<span class='badge btn bg-info'>Not Verified</span>";
                        }
                    })
                    ->editColumn('client_name', function ($row) {
                        return $row->fullname;
                    })
                    ->editColumn('client_email', function ($row) {
                        return $row->email;
                    })
                    ->rawColumns(['email', 'total_accounts', 'profile_status', 'client_name', 'client_email'])
                    ->make(true);
            }

            return response()->json(['error' => 'Invalid request'], 400);
        } catch (\Exception $e) {
            Log::error('Error in getClientIbProfile: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching data'], 500);
        }
    }

    public function exportAllClients(Request $request)
    {
        $fileName = 'Client_List_' . date('Y-m-d') . '.csv';

        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Country', 'Created At', 'Client User Status', 'Client Email Status', 'Client KYC Status']);

            // Fetch client data
            User::chunk(500, function ($clients) use ($handle) {
                foreach ($clients as $client) {
                    fputcsv($handle, [
                        $client->id,
                        $client->fullname,
                        $client->email,
                        $client->number,
                        $client->country,
                        $client->created_at,
                        $client->status == 1 ? 'Active' : 'Inactive',
                        $client->email_confirmed == 1 ? 'Verified' : 'Not Verified',
                        $client->kyc_verify == 1 ? 'Verified' : 'Not Verified',
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    public function exportAllLiveAccounts(Request $request)
    {
        $fileName = 'Live_Accounts_' . date('Y-m-d') . '.csv';

        return Response::streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'ID',
                'Name',
                'Email',
                'Code',
                'Account Group',
                'Leverage',
                'Balance',
                'Equity',
                'Status',
                'Total Deposit',
                'Total Withdraw',
                'Last Trade Date',
                'Days Since Last Trade',
                'Deposited',
                'Traded',
                'Date',
                'Time',
                'Country',
            ]);

            $chunkCount = 0;

            $query = $this->buildLiveAccountsBaseQuery();
            $this->applyLiveAccountsFilters($query, $request);

            $query->orderBy('accounts.id', 'desc')
                ->chunk(500, function ($accounts) use ($handle, &$chunkCount) {
                $chunkCount++;
                Log::info("Processing chunk: {$chunkCount}, accounts count: " . $accounts->count());

                foreach ($accounts as $account) {
                    try {
                        $lastTradeAt = $account->last_trade_at;

                        if ($lastTradeAt !== null) {
                            $lastTradeAtCarbon = Carbon::parse($lastTradeAt);
                            $lastTradeDate = $lastTradeAtCarbon->format('Y-m-d H:i:s');
                            $days = $lastTradeAtCarbon->diffInDays(now());
                            $daysSinceLastTrade = max(0, (int) $days);
                        } else {
                            $lastTradeDate = '—';
                            $daysSinceLastTrade = 'No trades';
                        }

                        $hasDeposits = $account->successful_trade_deposits_count > 0;
                        $hasTrades = $lastTradeAt !== null || ($account->trades_count ?? 0) > 0;
                        $isDeposited = $hasDeposits ? 'Yes' : 'No';
                        $Traded = $hasTrades ? 'Yes' : 'No';


                        fputcsv($handle, [
                        $account->id,
                        $account->user->fullname ?? '',
                        $account->email,
                        $account->code,
                        $account->accountType->ac_group ?? '',
                        $account->leverage,
                        $account->balance,
                        $account->equity,
                        $account->deleted_at ? 'Deleted' : 'Active',
                        number_format($account->tradeDeposits()->where('status', 1)->whereIn('deposit_type', ['CryptoChill', 'CreditCardPayissa', 'RagaPay'])->sum('deposit_amount'), 2),
                        number_format($account->tradeWithdrawals()->where('status', 1)->where('withdraw_type', 'Trade Withdrawal')->sum(DB::raw('transaction_fee + withdrawal_amount')), 2),
                        $lastTradeDate,
                        $daysSinceLastTrade,
                        $isDeposited,
                        $Traded,
                        $account->created_at->format('Y-m-d'),
                        $account->created_at->format('H:i:s'),
                        $account->user->country ?? '',
                    ]);
                    } catch (\Exception $e) {
                        Log::error("Error writing account ID {$account->id}: " . $e->getMessage());
                    }
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportAllDemoAccounts(Request $request)
    {
        $fileName = 'Demo_Accounts_' . date('Y-m-d') . '.csv';

        return Response::streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, ['ID', 'Name', 'Email', 'Code', 'Account Group', 'Leverage', 'Balance', 'Equity', 'Status', 'Date', 'Time']);

            $chunkCount = 0;

            Account::with('user', 'accountType')->where('demo', 1)->chunk(500, function ($accounts) use ($handle, &$chunkCount) {
                $chunkCount++;
                Log::info("Processing chunk: {$chunkCount}, accounts count: " . $accounts->count());

                foreach ($accounts as $account) {
                    try {
                        fputcsv($handle, [
                            $account->id,
                            $account->user->fullname ?? '',
                            $account->email,
                            $account->code,
                            $account->accountType->ac_group ?? '',
                            $account->leverage,
                            $account->balance,
                            $account->equity,
                            $account->status,
                            $account->created_at->format('Y-m-d'),
                            $account->created_at->format('H:i:s'),
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Error writing account ID {$account->id}: " . $e->getMessage());
                    }
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }


    public function exportAllTradingDeposit(Request $request)
    {
        $fileName = 'Trading_Deposit_' . date('Y-m-d') . '.csv';

        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, ['Name', 'Email', 'Account No', 'Deposit Amount', 'Deposit Type', 'Deposit From', 'Deposited Date', 'Status']);

            // Fetch client data
            TradeDeposit::select(
                'trade_deposits.*'
            )->with(['user', 'account'])
                ->chunk(500, function ($tradeDeposits) use ($handle) {
                    foreach ($tradeDeposits as $tradeDeposit) {
                        fputcsv($handle, [
                            $tradeDeposit->user->fullname,
                            $tradeDeposit->user->email,
                            $tradeDeposit->code,
                            $tradeDeposit->deposit_amount,
                            $tradeDeposit->deposit_type,
                            $tradeDeposit->deposit_from,
                            $tradeDeposit->created_at,
                            $tradeDeposit->status,
                        ]);
                    }
                });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    public function exportAllInternalTransfer(Request $request)
    {
        $fileName = 'Internal_Transfer_' . date('Y-m-d') . '.csv';

        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, ['Name', 'Email', 'Amount', 'Transfer From', 'Transfer To', 'Status', 'Created At']);

            // Fetch client data
            TradeDeposit::with(['user', 'account'])
                ->whereIn('trade_deposits.deposit_type', ['Internal Transfer', 'CRM'])
                ->chunk(500, function ($tradeDeposits) use ($handle) {
                    foreach ($tradeDeposits as $tradeDeposit) {
                        if ($tradeDeposit->deposit_from) {
                            $acc = Account::where('id', $tradeDeposit->deposit_from)->first();
                        }
                        if ($tradeDeposit->deposit_from == 'IB Commission' || $tradeDeposit->deposit_type == 'IB Withdraw') {
                            $transfer_from = 'IB Wallet';
                        } elseif ($tradeDeposit->deposit_type == 'CRM' && $tradeDeposit->deposit_from == NULL) {
                            $transfer_from = $tradeDeposit->deposit_type;
                        } else {
                            $transfer_from = $tradeDeposit->deposit_type;
                        }
                        $transferfrom =  ($tradeDeposit->deposit_from && $acc) ? $acc->code : $transfer_from;
                        $created = Carbon::parse($tradeDeposit->created_at)->addHours(3);
                        fputcsv($handle, [
                            $tradeDeposit->user->fullname,
                            $tradeDeposit->user->email,
                            $tradeDeposit->deposit_amount,
                            $transferfrom,
                            $tradeDeposit->account->code ?? 'N/A',
                            $tradeDeposit->status,
                            $created,
                        ]);
                    }
                });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }


    public function getBlockedIPs(Request $request)
    {
        $query = RestrictIps::leftJoin('aspnetusers', 'aspnetusers.email', '=', 'restrict_ips.email')
                ->select(
                    'restrict_ips.*',
                    'aspnetusers.fullname'
                );
        // Apply search filter
        if (!empty($request->search['value'])) {
            $searchValue = '%' . $request->search['value'] . '%';
            $query->where(function ($q) use ($searchValue) {
                $q->where('restrict_ips.ip', 'LIKE', $searchValue)
                    ->orWhere('restrict_ips.block_reason', 'LIKE', $searchValue)
                    ->orWhere('aspnetusers.fullname', 'LIKE', $searchValue)
                    ->orWhere('aspnetusers.email', 'LIKE', $searchValue);
            });
        }

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addColumn('ip', function ($row) {
                    return $row->ip;
                })
                ->addColumn('fullname', function ($row) {
                    return $row->fullname ?? 'N/A';
                })
                ->addColumn('email', function ($row) {
                    return $row->email;
                })
                ->addColumn('reason', function ($row) {
                    return $row->block_reason;
                })
                ->addColumn('date', function ($row) {
                    $date = date('Y-m-d', strtotime($row->created_at));
                    $time = date('H:i:s', strtotime($row->created_at));
                    return "<div class='lh-1'>$date</div><div class='lh-2 text-muted'>$time</div>";
                })
                ->addColumn('action', function ($row) {
                    return "<a class='btn btn-sm btn-danger' href='/admin/delete_ip_ban?id={$row->id}&ip={$row->ip}'>Delete</a>";
                })
                ->orderColumn('ip', function ($query, $order) {
                    $query->orderBy('restrict_ips.ip', $order);
                })
                ->orderColumn('fullname', function ($query, $order) {
                    $query->orderBy('aspnetusers.fullname', $order);
                })
                ->orderColumn('email', function ($query, $order) {
                    $query->orderBy('aspnetusers.email', $order);
                })
                ->orderColumn('reason', function ($query, $order) {
                    $query->orderBy('restrict_ips.block_reason', $order);
                })
                ->orderColumn('date', function ($query, $order) {
                    $query->orderBy('restrict_ips.created_at', $order);
                })
                ->rawColumns(['ip', 'fullname', 'email', 'reason', 'date', 'action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getRequestedCompetitionList(Request $request)
    {
        // dump( session('userData'));
        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];
        $userGroups = explode(',', session('user_groups'));
        // dd($alogin);
        // Base query
        $rmCondition = Account::select('accounts.*')
            ->where('account_request_status', 0)
            ->whereNotNull('competition_start_date')
            ->whereNotNull('competition_end_date')
            ->where('demo', 1)
            ->with(['user', 'accountType']);


        if ($role !== "Super Admin") {
            $rmCondition->whereHas('user');
        }

        // if ($role === "Relationship Manager") {
        //     $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
        //         $query->where('rm_id', $alogin);
        //     });
        // }

        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('user.employee', function ($query) use ($alogin) {
                $query->where('relationship_manager.rm_id', $alogin); // Filter based on rm_id in pivot
            });
        }

        $rmCondition->orderBy('id', 'desc');

        if ($request->ajax()) {
            // dd(DataTables::of($rmCondition));
            return DataTables::of($rmCondition)
                ->editColumn('email', function ($row) {
                    $fullname = $row->user
                        ? ($row->user->fullname)
                        : 'Unknown';
                    $email = $row->user ? $row->user->email : 'No Email';
                    return "<a href='/admin/client_details/{$row->user->id}'>
                                <div class='d-flex align-items-center'>
                                    <div class='me-2'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                    </div>
                                    <div>
                                        <div class='lh-1'><span>{$fullname}</span></div>
                                        <div class='lh-1'><span class='fs-11 text-muted'>{$email}</span></div>
                                    </div>
                                </div>
                            </a>";
                })
                ->addColumn('code', function ($row) {
                    $accountGroup = $row->accountType->ac_group;
                    return "<a href='" . (($row->code && $row->code != 'Rejected') ? '/admin/view_account_details/' . $row->id : '#') . "'>
                                <div class='row align-items-center'>
                                    <div class='col-auto pe-0'><img src='/assets/images/mt5.png'
                                            alt='user-image' class='rounded wid-50 hei-50'></div>
                                    <div class='col ps-2'>
                                        <h6 class='mb-0'><span class='text-truncate w-100'>" .
                        ($row->code ? $row->code : 'Pending ' . $row->accountType->ac_name) .
                        "</span>
                                        </h6>
                                        <p class='mb-0 text-muted f-12'><span
                                                class='text-truncate w-100'> $accountGroup </span>
                                        </p>
                                    </div>
                                </div>
                            </a>";
                })
                // ->addColumn('leverage', function($row){
                //     return $row->leverage;
                // })
                ->addColumn('balance', function ($row) {
                    return $row->balance;
                })
                ->addColumn('created_at', function ($row) {
                    // $date = date('Y-m-d', strtotime($row->created_at));
                    $date = Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                    // $time = date('H:i:s', strtotime($row->created_at));
                    $time = Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('fullname', function ($row) {
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function ($row) {
                    return $row->email;
                })
                ->addColumn('account_code', function ($row) {
                    return $row->code;
                })
                ->addColumn('account_group', function ($row) {
                    return $row->accountType->ac_group;
                })
                ->addColumn('account_request_status', function ($row) {

                    if ($row->account_request_status == 1) {
                        return "<button class='badge bg-outline-success'>Approved</button>";
                        // }elseif($row->account_request_status == 2){
                        //     return "<button class='ibToggle badge bg-outline-danger'>Rejected</button>";
                    } elseif ($row->account_request_status == 0) {
                        return "<button class='ibToggle badge bg-outline-primary'>Pending</button>";
                    }
                })
                ->editColumn('request_status', function ($row) {
                    return $row->account_request_status;
                })
                ->addColumn('created_date', function ($row) {
                    // return date('Y-m-d', strtotime($row->created_at));
                    return Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    // return date('H:i:s', strtotime($row->created_at));
                    return Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                })
                ->rawColumns(['email', 'code', 'leverage', 'balance', 'created_at', 'fullname', 'fullemail', 'account_request_status', 'request_status'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getCompetitionsData(Request $request)
    {

        $role = session('userData')['userRole'];
        $alogin = session('userData')['id'];

        $rmCondition = Account::select('accounts.*')
            ->whereNotNull('competition_start_date')
            ->whereNotNull('competition_end_date')
            ->whereHas('accountType', function ($query) {
                $query->where('ac_name', 'like', '%Competition%');
            })
            ->with(['user', 'accountType']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $rmCondition->where(function ($query) use ($search, $end_date, $start_date) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                        ->orWhere('fullname', 'like', "%{$search}%");
                });
                $query->whereHas('accountType', function ($r) use ($start_date, $end_date) {
                    $r->where('competition_start_date', 'like', "%{$start_date}%")
                        ->orWhere('competition_end_date', 'like', "%{$end_date}%");
                });

                $query->orWhere('balance', 'like', "%{$search}%")
                    ->orWhere('equity', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // if ($request->has('competition_start_date') && !empty($request->competition_start_date)) {
        //     $rmCondition->where('competition_start_date', $request->competition_start_date);
        // }

        if ($request->has('start_date') && !empty($request->start_date)) {
            $rmCondition->whereDate('competition_start_date', '>=', $request->start_date);
        }

        // if ($request->has('competition_end_date') && !empty($request->competition_end_date)) {
        //     $rmCondition->where('competition_end_date', $request->competition_end_date);
        // }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $rmCondition->whereDate('competition_end_date', '<=', $request->end_date);
        }

        if ($request->has('status') && $request->status !== null) {
            $rmCondition->where('account_request_status', (int)$request->status);
        }

        if ($role !== "Super Admin") {
            $rmCondition->whereHas('user');
        }

        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('user.employee', function ($query) use ($alogin) {
                $query->where('relationship_manager.rm_id', $alogin);
            });
        }

        $rmCondition->orderBy('id', 'desc');

        if ($request->ajax()) {
            return DataTables::of($rmCondition)
                ->editColumn('email', function ($row) {
                    $fullname = $row->user
                        ? ($row->user->fullname)
                        : 'Unknown';
                    $email = $row->user ? $row->user->email : 'No Email';
                    return "<a href='/admin/client_details/{$row->user->id}'>
                                <div class='d-flex align-items-center'>
                                    <div class='me-2'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                    </div>
                                    <div>
                                        <div class='lh-1'><span>{$fullname}</span></div>
                                        <div class='lh-1'><span class='fs-11 text-muted'>{$email}</span></div>
                                    </div>
                                </div>
                            </a>";
                })
                ->addColumn('code', function ($row) {
                    $accountGroup = $row->accountType->ac_group;
                    return "<a href='" . (($row->code && $row->code != 'Rejected') ? '/admin/view_account_details/' . $row->id : '#') . "'>
                                <div class='row align-items-center'>
                                    <div class='col-auto pe-0'><img src='/assets/images/mt5.png'
                                            alt='user-image' class='rounded wid-50 hei-50'></div>
                                    <div class='col ps-2'>
                                        <h6 class='mb-0'><span class='text-truncate w-100'>" .
                        ($row->code ? $row->code : 'Pending') .
                        "</span>
                                        </h6>
                                        <p class='mb-0 text-muted f-12'><span
                                                class='text-truncate w-100'> $accountGroup </span>
                                        </p>
                                    </div>
                                </div>
                            </a>";
                })

                ->addColumn('balance', function ($row) {
                    return $row->balance;
                })
                ->addColumn('created_at', function ($row) {
                    $date = Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                    $time = Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('fullname', function ($row) {
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function ($row) {
                    return $row->email;
                })
                ->addColumn('initial_balance', function ($row) {
                    return $row->accountType->ac_min_deposit;
                })
                ->addColumn('profit', function ($row) {
                    if ($row->account_request_status == 0) {
                        return '<span>N/A</span>';
                    }
                    $profit = $row->balance - $row->accountType->ac_min_deposit;
                    return '<span class="' . ($profit >= 0 ? 'text-success' : 'text-danger') . '">' . number_format($profit, 2) . '</span>';
                })
                ->addColumn('account_status', function ($row) {
                    if ($row->account_request_status == 1) {
                        return "<span class='text-success'>Approved</span>";
                    } elseif ($row->account_request_status == 0) {
                        return "<span class='text-warning'>Pending</span>";
                    }
                })
                ->addColumn('start_end', function ($row) {
                    $monthYear = Carbon::parse($row->accountType->competition_start_date)->format('Y-m-d') . '/' . Carbon::parse($row->accountType->competition_end_date)->format('Y-m-d');
                    $url = route('admin.competition.leaderboard', [
                        'competition_id' => $row->accountType->id,
                    ]);

                    return '
                        <div class="d-flex flex-column align-items-start">
                            <div><strong>' . e($monthYear) . '</strong></div>
                            <a href="' . $url . '"
                            class="mt-1 btn btn-sm btn-outline-primary"
                            style="font-size: 0.75rem; padding: 2px 6px;"
                            target="_blank">
                            View Leaderboard
                            </a>
                        </div>
                    ';
                })
                ->addColumn('account_group', function ($row) {
                    return $row->accountType->ac_group;
                })
                ->addColumn('account_request_status', function ($row) {

                    if ($row->account_request_status == 1) {
                        return "<button class=' badge bg-outline-success'>Approved</button>";
                        // }elseif($row->account_request_status == 2){
                        //     return "<button class='ibToggle badge bg-outline-danger'>Rejected</button>";
                    } elseif ($row->account_request_status == 0) {
                        return "<button class='ibToggle badge bg-outline-primary'>Pending</button>";
                    }
                })

                ->addColumn('created_date', function ($row) {
                    return Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                })
                ->addColumn('created_time', function ($row) {
                    return Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                })
                ->rawColumns(['email', 'code', 'leverage', 'balance', 'created_at', 'fullname', 'fullemail', 'account_request_status', 'initial_balance', 'profit', 'start_end', 'account_status'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getPromocodes(Request $request)
    {
        $promocodes = Promocode::where('deleted_at', NULL);

        if ($request->ajax()) {
            return DataTables::of($promocodes)
                ->filter(function ($promocodes) use ($request) {
                    if (!empty($request->search['value'])) {
                        $searchValue = $request->search['value'];
                        $promocodes->where(function ($q) use ($searchValue) {
                            $q->where('id', 'LIKE', "%{$searchValue}%")
                                ->orWhere('code', 'LIKE', "%{$searchValue}%")
                                ->orWhere('promo_percentage', 'LIKE', "%{$searchValue}%")
                                ->orWhere('status', 'LIKE', "%{$searchValue}%")
                                ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%{$searchValue}%"]);
                        });
                    }
                })
                ->addColumn('id', function ($row) {
                    return $row->id;
                })
                ->addColumn('code', function ($row) {
                    return $row->code;
                })
                ->addColumn('percentage', function ($row) {
                    return $row->promo_percentage;
                })
                ->addColumn('min_deposit', function ($row) {
                    return $row->min_deposit;
                })
                ->addColumn('max_deposit', function ($row) {
                    return $row->max_deposit;
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';
                    return "<div class='form-check form-switch'>
                                <input class='form-check-input statusToggle' type='checkbox' data-id='{$row->id}' {$checked}>
                            </div>";
                })

                ->addColumn('created_at', function ($row) {
                    return date('Y-m-d', strtotime($row->created_at));
                })
                ->addColumn('action', function ($row) {
                    $html = "";
                    $html .= "
                                <span class='editPromocode' data-id='{$row->id}'>
                                    <span class='badge text-secondary' data-bs-toggle='tooltip' title='Edit Promocode'>
                                        <svg  xmlns='http://www.w3.org/2000/svg'  width='24'  height='24'  viewBox='0 0 24 24'  fill='none'  stroke='currentColor'  stroke-width='2'  stroke-linecap='round'  stroke-linejoin='round'  class='icon icon-tabler icons-tabler-outline icon-tabler-edit text-secondary'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1' /><path d='M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z' /><path d='M16 5l3 3' /></svg>
                                    </span>
                                </span>

                                <a class='deleteAcc pointer deletePromocode' data-bs-toggle='tooltip' data-id='{$row->id}'>
                                    <span class='badge text-danger'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='icon icon-tabler icons-tabler-outline icon-tabler-trash'>
                                            <path stroke='none' d='M0 0h24v24H0z' fill='none'/>
                                            <path d='M4 7l16 0' />
                                            <path d='M10 11l0 6' />
                                            <path d='M14 11l0 6' />
                                            <path d='M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12' />
                                            <path d='M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3' />
                                        </svg>
                                    </span>
                                </a>
                            ";
                    return $html;
                })
                ->rawColumns(['id', 'code', 'percentage', 'min_deposit', 'max_deposit', 'status', 'created_at', 'action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }
    public function getTasks(Request $request)
    {
        $tasks = Task::where('status', 1);
        if ($request->ajax()) {
            return DataTables::of($tasks)
                ->addColumn('action', function ($row) {
                    return '<a href="javascript:void(0)"
                                class="editTaskBtn"
                                data-id="' . $row->id . '"
                                data-name="' . $row->name . '"
                                data-title="' . $row->title . '"
                                data-description="' . $row->description . '"
                                data-points="' . $row->points . '"
                                data-status="' . $row->status . '"
                                data-expiration_date="' . $row->expiration_date . '">
                                <span class="badge text-secondary" data-bs-toggle="tooltip" title="Edit">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-edit text-secondary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                </span>
                            </a>


                                <button type="submit" data-bs-toggle="tooltip" class="p-0 deleteTask btn btn-link">
                                    <span class="badge text-danger">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M4 7l16 0" />
                                            <path d="M10 11l0 6" />
                                            <path d="M14 11l0 6" />
                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                        </svg>
                                    </span>
                                </button>';
                })
                ->rawColumns(['name', 'expiration_date', 'status', 'action'])
                ->make(true);
        }
    }

    public function getClientTasks(Request $request)
    {
        $tasks = ClientTask::with('user', 'task')->where('client_verification', 1);
        if ($request->ajax()) {
            return DataTables::of($tasks)

                ->addColumn('created_at', function ($row) {

                    $date = Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                    $time = Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->editColumn('email', function ($row) {
                    $fullname = $row->user
                        ? ($row->user->fullname)
                        : '';
                    $email = $row->user ? $row->user->email : '';
                    return "<a href='/admin/client_details/{$row->user->id}'>
                                    <div class='d-flex align-items-center'>
                                        <div class='me-2'>
                                            <svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg>
                                        </div>
                                        <div>
                                            <div class='lh-1'><span>{$fullname}</span></div>
                                            <div class='lh-1'><span class='fs-11 text-muted'>{$email}</span></div>
                                        </div>
                                    </div>
                                </a>";
                })
                ->addColumn('screenshot', function ($row) {
                    $imagePath = ($row->user && $row->image_path)
                        ? Storage::url($row->image_path)
                        : asset('default-user.png'); // fallback image

                    return "<img id='profile_image' class='rounded' src='{$imagePath}' style='width: 60px; height: 60px; object-fit: cover; cursor: pointer;' data-bs-toggle='modal' data-bs-target='#imageModal' data-image='{$imagePath}' />";
                })
                ->addColumn('task_name', function ($row) {
                    return "<span>{$row->task->name}</span>";
                })
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    } elseif ($row->status == 2) {
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    } else {
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('points', function ($row) {
                    return "<span>{$row->task->points}</span>";
                })
                ->addColumn('action', function ($row) {
                    if ($row->status == 0) {
                        return "<button class='taskToggle badge bg-outline-primary'>Pending</button>";
                    }
                })
                ->addColumn('name', function ($row) {
                    return $row->user
                        ? ($row->user->fullname)
                        : '';
                })
                ->addColumn('client_email', function ($row) {
                    return $row->user ? $row->user->email : '';
                })
                ->addColumn('date', function ($row) {
                    $date = Carbon::parse($row->created_at)->addHours(3)->format('Y-m-d');
                    return $date;
                })
                ->addColumn('time', function ($row) {
                    $time = Carbon::parse($row->created_at)->addHours(3)->format('H:i:s');
                    return $time;
                })

                ->rawColumns(['created_at', 'email', 'screenshot', 'task_name', 'status', 'points', 'action', 'name', 'client_email', 'date', 'time'])
                ->make(true);
        }
    }
    public function exportCompetitions(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'month' => $request->get('month'),
            'year' => $request->get('year'),
            'status' => $request->get('status')
        ];

        return (new CompetitionExport($filters))->download('competitions_' . date('Y-m-d') . '.xlsx');
    }

    public function verify_promocode(Request $request)
    {
        $code = $request->input('promocode');

        $promocode = Promocode::where('code', $code)->first();
        if ($promocode) {
            $message = 'Code verified! Get a ' . (int)$promocode->promo_percentage . '% deposit bonus. Maximum bonus you can receive is $' . $promocode->max_deposit . '.';

            // if (!is_null($promocode->max_deposit) && $promocode->max_deposit != 0) {
            //     $message .= ' The maximum discount is ' . $promocode->max_deposit . '!';
            // }

            return response()->json([
                'valid' => true,
                'message' => $message,
            ]);
        } else {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid promocode. Please try again.',
            ]);
        }
    }

    /**
     * Get client wallets for DataTable display
     */
    public function getClientWallets($id)
    {
        try {
            $wallets = ClientWallet::withTrashed()
                ->where('user_id', $id)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            $data = [];
            foreach ($wallets as $wallet) {
                $verifiedBadge = $wallet->verified
                    ? '<span class="badge bg-success"><i class="ri-check-line"></i> Verified</span>'
                    : '<span class="badge bg-warning"><i class="ri-close-line"></i> Not Verified</span>';

                // STATUS BADGE
                if ($wallet->deleted_at) {
                    if ($wallet->admin_action_by == 'Admin') {
                        $statusBadge = '<span class="badge bg-danger">Deleted by Admin</span>';
                    } else {
                        $statusBadge = '<span class="badge bg-danger">Deleted by User</span>';
                    }

                    $actionButtons = ''; // keep empty when deleted

                } else {

                    // Normal active / inactive badge
                    $statusBadge = $wallet->status == 1
                        ? '<span class="badge bg-outline-success">Active</span>'
                        : '<span class="badge bg-outline-danger">Inactive</span>';

                    // Normal action buttons
                    $actionButtons = '';

                    if (!$wallet->verified) {
                        $actionButtons .= '<button class="btn btn-sm btn-success verifyWallet" data-wallet-id="' . $wallet->id . '" title="Verify Wallet"><i class="ri-check-line"></i> Verify</button>';
                    }

                    $actionButtons .= ' <button class="btn btn-sm btn-danger deleteWallet" data-wallet-id="' . $wallet->id . '" data-wallet-name="' . $wallet->wallet_name . '" title="Delete Wallet"><i class="ri-delete-bin-line"></i> Delete</button>';
                }

                $data[] = [
                    'created_on' => Carbon::parse($wallet->created_at)->format('Y-m-d H:i:s'),
                    'wallet_name' => $wallet->wallet_name,
                    'wallet_currency' => ($wallet->wallet_network == 'BTC') ? 'BTC' : $wallet->wallet_currency,
                    'wallet_network' => $wallet->wallet_network,
                    'wallet_address' => $wallet->wallet_address,
                    'verified' => $verifiedBadge,
                    'status' => $statusBadge,
                    'action' => $actionButtons,
                ];
            }

            return ['data' => $data];
        } catch (\Exception $e) {
            Log::error('Error fetching client wallets: ' . $e->getMessage());
            return ['data' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify a client wallet
     */
    public function verifyClientWallet($requestData)
    {
        try {
            $walletId = $requestData['wallet_id'] ?? null;
            $userId = $requestData['user_id'] ?? null;

            if (!$walletId || !$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid wallet or user ID'
                ], 422);
            }

            $wallet = ClientWallet::where('id', $walletId)
                ->where('user_id', $userId)
                ->first();

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found'
                ], 404);
            }

            $wallet->verified = true;
            $wallet->admin_action_by = 'admin';
            $wallet->save();

            // Log the action
            Log::info("Wallet {$walletId} verified by " . Auth::user()->email);

            return response()->json([
                'success' => true,
                'message' => 'Wallet verified successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error verifying wallet: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify wallet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a client wallet
     * Checks for pending withdrawals before deletion
     */
    public function deleteClientWallet($requestData)
    {
        try {
            $walletId = $requestData['wallet_id'] ?? null;
            $userId = $requestData['user_id'] ?? null;

            if (!$walletId || !$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid wallet or user ID'
                ], 422);
            }

            $wallet = ClientWallet::where('id', $walletId)
                ->where('user_id', $userId)
                ->first();

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found'
                ], 404);
            }

            // Check for pending withdrawals
            $pendingWithdrawals = TradeWithdrawals::where('client_wallet_id', $wallet->id)
                ->where('status', 0)
                ->count();
            if ($pendingWithdrawals > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete wallet with pending withdrawals. Please resolve all pending withdrawals first.'
                ], 422);
            }

            // Update before soft delete
            $wallet->update([
                'admin_action_by'            => 'Admin',
                'wallet_delete_verification' => 1,
            ]);

            // Soft delete wallet
            $wallet->delete();

            return response()->json([
                'success' => true,
                'message' => 'Wallet deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting wallet: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete wallet: ' . $e->getMessage()
            ], 500);
        }
    }
}
