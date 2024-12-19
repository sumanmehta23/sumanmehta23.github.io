<?php

namespace App\Http\Controllers\Admin;

use DB;
use Exception;
use Carbon\Carbon;
use App\Models\Ib1;
use App\Models\User;
use App\Models\Account;
use App\Models\UserLog;
use App\Models\IbWallet;
use App\Models\EmployeeList;
use App\Models\TradeDeposit;
use Illuminate\Http\Request;
use App\Models\WalletWithdraw;
use App\Models\TradeWithdrawals;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\WalletDeposit;
use Illuminate\Support\Facades\Cache;

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
                    case 'getIbUsers':
                        $result = $this->getIbUsers();
                        break;
                    case 'getPendingIbUsers':
                        $result = $this->getPendingIbUsers();
                        break;
                    case 'getAdminDetails':
                        $result = $this->getAdminDetails($id);
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

    public function getClientList(Request $request)
    {
        // ini_set('memory_limit', '1024M');
        // ini_set('max_execution_time', 3000);
        $query = User::with([
            'ib' => function ($query) {
                $query->select('ib1.id', 'name');
            },
            'employee' => function ($query) {
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
        ])->groupBy('aspnetusers.email');

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

        $query->when(session('userData')['userRole'] != "Super Admin", function ($query) {
            $query->leftJoin('aspnetusers AS user', 'user.email', '=', 'ap.email');
        });

        if (session('userData')['userRole'] == "Relationship Manager") {
            $query->where('rm.rm_id', session('alogin'));
        }

        if ($request->has('rm_id') && !empty($request->get('rm_id'))) {
            $query->where('rm.rm_id', $request->get('rm_id'));
        }

        if ($request->ajax()) {
            return DataTables::of($query)
                ->editColumn('created_at', function ($row) {
                    $createdAt = Carbon::parse($row->created_at);
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
                    $countryAlpha = strtolower($row->getCountry() ? $row->getCountry()->country_alpha :'');
                    return $countryAlpha ? "<span class='fi fis fi-{$countryAlpha}'></span> {$row->getCountry()->country_alpha}" : '-';
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
                ->editColumn('ib', function ($row) {
                    $ib_name = $row->getParentIb() ? $row->getParentIb()->name : 'noIB';
                    $ib_email  =$row->getParentIb() ? $row->getParentIb()->email : '';
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
                                        <path d='M8 16a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2'></path>
                                    </svg>
                                  </span>";
                    }
                    $html .= "</span>";

                    $html .= "<span class='statusToggle' data-status='{$row->email_confirmed}'>";
                    if ($row->email_confirmed == 0) {
                        $html .= "<span class='badge text-danger' data-bs-toggle='tooltip' title='Email Not Verified'>
                                    <svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='0 0 24 24' fill='none' stroke='#FFCC80' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='25' class='tabler-icon tabler-icon-mail-x'>
                                        <path d='M13.5 19h-8.5a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v6'></path>
                                        <path d='M3 7l9 6l9 -6'></path>
                                        <path d='M22 22l-5 -5'></path>
                                        <path d='M17 22l5 -5'></path>
                                    </svg>
                                  </span>";
                    } else {
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


                    $html .= "<span class='editClient' data-enc='{$row->id}'>
                                <span class='badge text-secondary' data-bs-toggle='tooltip' title='Edit Client'>
                                    <svg  xmlns='http://www.w3.org/2000/svg'  width='24'  height='24'  viewBox='0 0 24 24'  fill='none'  stroke='currentColor'  stroke-width='2'  stroke-linecap='round'  stroke-linejoin='round'  class='icon icon-tabler icons-tabler-outline icon-tabler-edit text-secondary'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1' /><path d='M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z' /><path d='M16 5l3 3' /></svg>
                                </span>
                              </span>";

                    return $html;
                })

                ->rawColumns(['created_at', 'user_country' ,'user_email', 'ib', 'user_ib_status', 'rm', 'user_status', 'user_email_confirmed', 'action','ib_name','ib_email'])
                ->make(true);
        }

        return response()->json([
            'message' => 'Invalid request',
        ]);
    }

    public function getLiveAccountsList(Request $request)
    {
        $role = session('userData')['userRole'];
        $alogin = session('alogin');
        $userGroups = explode(',', session('user_groups'));

        // Base query
        $rmCondition = Account::where('demo', false)
            ->select('accounts.*')
            ->with(['user', 'accountType']);

        if ($role !== "Super Admin") {
            $rmCondition->whereHas('user');
        }

        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
                $query->where('rm_id', $alogin);
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
                ->addColumn('code', function($row) {
                    $accountGroup = $row->accountType->ac_group;
                    return "<a href='/admin/view_account_details/{$row->id}'>
                                <div class='row align-items-center'>
                                    <div class='col-auto pe-0'><img src='/assets/images/mt5.png'
                                            alt='user-image' class='rounded wid-50 hei-50'></div>
                                    <div class='col ps-2'>
                                        <h6 class='mb-0'><span
                                                class='text-truncate w-100'> $row->code </span>
                                        </h6>
                                        <p class='mb-0 text-muted f-12'><span
                                                class='text-truncate w-100'> $accountGroup </span>
                                        </p>
                                    </div>
                                </div>
                            </a>";
                })
                ->addColumn('leverage', function($row){
                    return $row->leverage;
                })
                ->addColumn('balance', function($row){
                    return $row->balance;
                })
                ->addColumn('created_at', function ($row) {
                    $date = date('Y-m-d', strtotime($row->created_at));
                    $time = date('H:i:s', strtotime($row->created_at));
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('fullname', function($row){
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function($row){
                    return $row->email;
                })
                ->addColumn('account_code', function($row){
                    return $row->code;
                })
                ->addColumn('account_group', function($row){
                    return $row->accountType->ac_group;
                })
                ->addColumn('created_date', function($row){
                    return date('Y-m-d', strtotime($row->created_at));
                })
                ->addColumn('created_time', function($row){
                    return date('H:i:s', strtotime($row->created_at));
                })
                ->rawColumns(['email', 'code', 'leverage', 'balance', 'created_at','fullname','fullemail'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }
    public function getDemoAccountsList(Request $request)
    {
        $role = session('userData')['userRole'];
        $alogin = session('alogin');
        $userGroups = explode(',', session('user_groups'));

        // Base query
        $rmCondition = Account::where('demo', true)
            ->select('accounts.*')
            ->with(['user', 'accountType']);

        if ($role !== "Super Admin") {
            $rmCondition->whereHas('user');
        }

        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
                $query->where('rm_id', $alogin);
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
                ->addColumn('code', function($row) {
                    $accountGroup = $row->accountType->ac_group;
                    return "<a href='/admin/view_account_details/{$row->id}'>
                                <div class='row align-items-center'>
                                    <div class='col-auto pe-0'><img src='/assets/images/mt5.png'
                                            alt='user-image' class='rounded wid-50 hei-50'></div>
                                    <div class='col ps-2'>
                                        <h6 class='mb-0'><span
                                                class='text-truncate w-100'> $row->code </span>
                                        </h6>
                                        <p class='mb-0 text-muted f-12'><span
                                                class='text-truncate w-100'> $accountGroup </span>
                                        </p>
                                    </div>
                                </div>
                            </a>";
                })
                ->addColumn('leverage', function($row){
                    return $row->leverage;
                })
                ->addColumn('balance', function($row){
                    return $row->balance;
                })
                ->addColumn('created_at', function ($row) {
                    $date = date('Y-m-d', strtotime($row->created_at));
                    $time = date('H:i:s', strtotime($row->created_at));
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('fullname', function($row){
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function($row){
                    return $row->email;
                })
                ->addColumn('account_code', function($row){
                    return $row->code;
                })
                ->addColumn('account_group', function($row){
                    return $row->accountType->ac_group;
                })
                ->addColumn('created_date', function($row){
                    return date('Y-m-d', strtotime($row->created_at));
                })
                ->addColumn('created_time', function($row){
                    return date('H:i:s', strtotime($row->created_at));
                })
                ->rawColumns(['email', 'code', 'leverage', 'balance', 'created_at','fullname','fullemail'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getWalletDeposit2(Request $request)
    {
        $role = session('userData')['userRole'];
        $alogin = session('alogin');

        // Base query
        $rmCondition = WalletDeposit::where('deposit_type','!=', 'Internal Transfer')
            ->select('wallet_deposit.*')
            ->with(['user']);


        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
                $query->where('rm_id', $alogin);
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
                ->addColumn('amount', function($row){
                    return $row->withdraw_amount;
                })
                ->addColumn('payment_mode', function($row){
                    return $row->deposit_type;
                })
                ->addColumn('deposit_date', function ($row) {
                    $date = date('Y-m-d', strtotime($row->deposted_date));
                    $time = date('H:i:s', strtotime($row->deposted_date));
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function($row){
                    if($row->status == 1){
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    }elseif($row->status == 2){
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    }else{
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function($row){
                    return "<a class='btn btn-sm btn-primary' href='/admin/wallet_deposit_details?id={$row->id}'>View</a>";
                })
                ->addColumn('fullname', function($row){
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function($row){
                    return $row->user->email;
                })
                ->addColumn('created_date', function($row){
                    return date('Y-m-d', strtotime($row->deposted_date));
                })
                ->addColumn('created_time', function($row){
                    return date('H:i:s', strtotime($row->deposted_date));
                })
                ->rawColumns(['email', 'amount', 'payment_mode', 'deposit_date','status','action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getWalletWithdrawal2(Request $request)
    {
        $role = session('userData')['userRole'];
        $alogin = session('alogin');
        $userGroups = explode(',', session('user_groups'));

        // Base query
        $rmCondition = WalletWithdraw::where('withdraw_type','!=', 'Internal Transfer')
            ->select('wallet_withdraw.*')
            ->with(['user']);


        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
                $query->where('rm_id', $alogin);
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
                    ->addColumn('amount', function($row){
                        return $row->withdraw_amount;
                    })
                    ->addColumn('fee', function($row){
                        return $row->withdraw_transaction_fee;
                    })
                    ->addColumn('payment_mode', function($row){
                        return $row->withdraw_type;
                    })
                    ->addColumn('withdraw_date', function ($row) {
                        $date = date('Y-m-d', strtotime($row->withdraw_date));
                        $time = date('H:i:s', strtotime($row->withdraw_date));
                        return "<div class='lh-1'>
                                    $date
                                </div>
                                <div class='lh-2 text-muted'>
                                    $time
                                </div>";
                    })
                    ->addColumn('status', function($row){
                        if($row->status == 1){
                            return "<div class='badge bg-outline-success'>Approved</div>";
                        }elseif($row->status == 2){
                            return "<div class='badge bg-outline-danger'>Rejected</div>";
                        }else{
                            return "<div class='badge bg-outline-primary'>Pending</div>";
                        }
                    })
                    ->addColumn('action', function($row){
                        return "<a class='btn btn-sm btn-primary' href='/admin/wallet_withdrawal_details?id={$row->id}'>View</a>";
                    })
                    ->addColumn('fullname', function($row){
                        return $row->user->fullname;
                    })
                    ->addColumn('fullemail', function($row){
                        return $row->user->email;
                    })
                    ->addColumn('created_date', function($row){
                        return date('Y-m-d', strtotime($row->withdraw_date));
                    })
                    ->addColumn('created_time', function($row){
                        return date('H:i:s', strtotime($row->withdraw_date));
                    })
                    ->rawColumns(['email', 'amount', 'fee', 'payment_mode', 'withdraw_date','status','action'])
                    ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getTradingDeposit2(Request $request)
    {
        $query = TradeDeposit::with(['user', 'account']);
        if (!isset($_GET['id'])) {
            if (session('userData')['userRole'] == "Relationship Manager") {
                $rmId = session('alogin');
                $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
                    $q->where('rm_id', $rmId);
                });
            }
        } else {
            $query->where('code', $_GET['id']);
        }


        if ($request->ajax()) {
            return DataTables::of($query)
                ->editColumn('id', function ($row) {
                    return $row->id;
                })
                ->addColumn('account_no', function($row){
                    return $row->code;
                })
                ->addColumn('amount', function($row){
                    return $row->deposit_amount;
                })
                ->addColumn('deposit_type', function($row){
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
                ->addColumn('deposit_from', function($row){
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
                    $date = date('Y-m-d', strtotime($row->deposted_date));
                    $time = date('H:i:s', strtotime($row->deposted_date));
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function($row){
                    if($row->status == 1){
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    }elseif($row->status == 2){
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    }else{
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function($row){
                    return "<a href='/admin/trading_deposit_details?id={$row->id}' class='' style='font-size: 13px;padding: 2px 20px;'><i class='fe fe-eye fs-14 text-info'></i></a>";
                })
                ->addColumn('created_date', function($row){
                    return date('Y-m-d', strtotime($row->deposted_date));
                })
                ->addColumn('created_time', function($row){
                    return date('H:i:s', strtotime($row->deposted_date));
                })
                ->rawColumns(['id', 'account_no', 'amount', 'deposit_type', 'deposit_from','deposit_date','status','action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getTradingWithdrawal2(Request $request)
    {
        $query = TradeWithdrawals::with(['user', 'withdrawTo', 'account']);

        if (!isset($_GET['id'])) {
            if (session('userData')['userRole'] == "Relationship Manager") {
                $rmId = session('alogin');
                $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
                    $q->where('rm_id', $rmId);
                });
            }
        } else {
            $query->where('account_id', $_GET['id']);
        }

        // Fetch data
        $query->orderByDesc('id')->get();


        if ($request->ajax()) {
            return DataTables::of($query)
                ->addColumn('account_no', function($row){
                    return $row->account->code;
                })
                ->addColumn('amount', function($row){
                    return $row->withdrawal_amount;
                })
                ->addColumn('withdraw_type', function($row){
                    return $row->withdraw_type;
                })
                ->addColumn('withdraw_to', function($row){
                    if ($row->withdraw_to) {
                        $acc = Account::where('id', $row->withdraw_to)->first();
                    }
                    return ($row->withdraw_to && $acc) ? $acc->code : $row->withdraw_type;
                })
                ->addColumn('withdraw_date', function ($row) {
                    $date = date('Y-m-d', strtotime($row->withdraw_date));
                    $time = date('H:i:s', strtotime($row->withdraw_date));
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function($row){
                    if($row->status == 1){
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    }elseif($row->status == 2){
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    }else{
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function($row){
                    return "<a href='/admin/trading_withdrawal_details?id={$row->id}' class=' style='font-size: 13px;padding: 2px 20px;'><i class='fe fe-eye fs-14 text-info'></i></a>";
                })
                ->addColumn('created_date', function($row){
                    return date('Y-m-d', strtotime($row->withdraw_date));
                })
                ->addColumn('created_time', function($row){
                    return date('H:i:s', strtotime($row->withdraw_date));
                })
                ->rawColumns(['account_no', 'amount', 'withdraw_type', 'withdraw_to','withdraw_date','status','action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getInternalTransfer2(Request $request)
    {
        $query = TradeDeposit::with(['user', 'account']);

        // Add conditions based on session and GET parameters
        if (!isset($_GET['id'])) {
            if (session('userData')['userRole'] == "Relationship Manager") {
                $rmId = session('alogin');
                $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
                    $q->where('rm_id', $rmId);
                });
            }
        } else {
            $query->where('deposit_type', 'Internal Transfer');;
        }

        // Fetch data
        $query->orderByDesc('id')->get();

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addColumn('email', function($row){
                    return $row->email;
                })
                ->addColumn('amount', function($row){
                    return $row->deposit_amount;
                })
                ->addColumn('transfer_from', function($row){
                    if ($row->deposit_from) {
                        $acc = Account::where('id', $row->deposit_from)->first();
                    }
                    if ($row->deposit_from == 'IB Commission' || $row->deposit_type == 'IB Withdraw') {
                        $transfer_from = 'IB Wallet';
                    } else {
                        $transfer_from = $row->deposit_type;
                    }
                    return ($row->deposit_from && $acc) ? $acc->code : $transfer_from;
                })
                ->addColumn('transfer_to', function($row){
                    return $row->code;
                })
                ->addColumn('status', function($row){
                    if($row->status == 1){
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    }elseif($row->status == 2){
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    }else{
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->rawColumns(['email', 'amount', 'transfer_from', 'transfer_to','status'])
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
    public function getComissionData($data)
    {
        // Retrieve user ID from request
        $userId = $data['id'];

        // Fetch histories
        $histories = IbWallet::with('account')->where('user_id', $userId)->get();
        // Prepare data
        $data = $histories->map(function ($row) {
            return [
                'date' => $row->created_at->format('Y-m-d H:i:s'), // Format date for consistency
                'accounts' => $row->account->code,
                'email' => $row->account->email,
                'type' => $row->ib_wallet ? 'Commission' : 'Transfer',
                'amount' => $row->ib_wallet ?? $row->ib_withdraw
            ];
        });
        return ['data' => $data];
    }


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
        $alogin = session('alogin');

        // Base query
        $rmCondition = WalletDeposit::where('deposit_type','!=', 'Internal Transfer')
            ->select('wallet_deposit.*')
            ->where('status',0)
            ->with(['user']);


        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
                $query->where('rm_id', $alogin);
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
                ->addColumn('amount', function($row){
                    return $row->withdraw_amount;
                })
                ->addColumn('payment_mode', function($row){
                    return $row->deposit_type;
                })
                ->addColumn('deposit_date', function ($row) {
                    $date = date('Y-m-d', strtotime($row->deposted_date));
                    $time = date('H:i:s', strtotime($row->deposted_date));
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function($row){
                    if($row->status == 1){
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    }elseif($row->status == 2){
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    }else{
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function($row){
                    return "<a class='btn btn-sm btn-primary' href='/admin/wallet_deposit_details?id={$row->id}'>View</a>";
                })
                ->addColumn('fullname', function($row){
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function($row){
                    return $row->user->email;
                })
                ->addColumn('created_date', function($row){
                    return date('Y-m-d', strtotime($row->deposted_date));
                })
                ->addColumn('created_time', function($row){
                    return date('H:i:s', strtotime($row->deposted_date));
                })
                ->rawColumns(['email', 'amount', 'payment_mode', 'deposit_date','status','action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getPendingWalletWithdrawal2(Request $request)
    {
        $query = WalletWithdraw::with(['user']);

        if (session('userData')['userRole'] == "Relationship Manager") {
            $rmId = session('alogin');
            $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
                $q->where('rm_id', $rmId);
            });
        } else {
            $query->where('Status', 0);
        }

        // Fetch data
        $query->orderByDesc('id')->get();

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
                ->addColumn('amount', function($row){
                    return $row->withdraw_amount;
                })
                ->addColumn('fee', function($row){
                    return $row->withdraw_transaction_fee;
                })
                ->addColumn('payment_mode', function($row){
                    return $row->withdraw_type;
                })
                ->addColumn('withdraw_date', function ($row) {
                    $date = date('Y-m-d', strtotime($row->withdraw_date));
                    $time = date('H:i:s', strtotime($row->withdraw_date));
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function($row){
                    if($row->status == 1){
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    }elseif($row->status == 2){
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    }else{
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function($row){
                    return "<a class='btn btn-sm btn-primary' href='/admin/wallet_withdrawal_details?id={$row->id}'>View</a>";
                })
                ->addColumn('fullname', function($row){
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function($row){
                    return $row->user->email;
                })
                ->addColumn('created_date', function($row){
                    return date('Y-m-d', strtotime($row->withdraw_date));
                })
                ->addColumn('created_time', function($row){
                    return date('H:i:s', strtotime($row->withdraw_date));
                })
                ->rawColumns(['email', 'amount', 'fee', 'payment_mode', 'withdraw_date','status','action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getPendingTradingDeposit2(Request $request)
    {
        $query = TradeDeposit::with(['user', 'account'])
                    ->where('status', 0);
        if (!isset($_GET['id'])) {
            if (session('userData')['userRole'] == "Relationship Manager") {
                $rmId = session('alogin');
                $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
                    $q->where('rm_id', $rmId);
                });
            }
        } else {
            $query->where('code', $_GET['id']);
        }


        if ($request->ajax()) {
            return DataTables::of($query)
                ->editColumn('id', function ($row) {
                    return $row->id;
                })
                ->addColumn('account_no', function($row){
                    return $row->code;
                })
                ->addColumn('amount', function($row){
                    return $row->deposit_amount;
                })
                ->addColumn('deposit_type', function($row){
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
                ->addColumn('deposit_from', function($row){
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
                    $date = date('Y-m-d', strtotime($row->deposted_date));
                    $time = date('H:i:s', strtotime($row->deposted_date));
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function($row){
                    if($row->status == 1){
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    }elseif($row->status == 2){
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    }else{
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function($row){
                    return "<a href='/admin/trading_deposit_details?id={$row->id}' class='' style='font-size: 13px;padding: 2px 20px;'><i class='fe fe-eye fs-14 text-info'></i></a>";
                })
                ->addColumn('created_date', function($row){
                    return date('Y-m-d', strtotime($row->deposted_date));
                })
                ->addColumn('created_time', function($row){
                    return date('H:i:s', strtotime($row->deposted_date));
                })
                ->rawColumns(['id', 'account_no', 'amount', 'deposit_type', 'deposit_from','deposit_date','status','action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getPendingTradingWithdrawal2(Request $request)
    {
        $query = TradeWithdrawals::with(['user', 'withdrawTo', 'account'])->where('status',0);

        if (!isset($_GET['id'])) {
            if (session('userData')['userRole'] == "Relationship Manager") {
                $rmId = session('alogin');
                $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
                    $q->where('rm_id', $rmId);
                });
            }
        } else {
            $query->where('account_id', $_GET['id']);
        }

        // Fetch data
        $query->orderByDesc('id')->get();


        if ($request->ajax()) {
            return DataTables::of($query)
                ->addColumn('account_no', function($row){
                    return $row->account->code;
                })
                ->addColumn('amount', function($row){
                    return $row->withdrawal_amount;
                })
                ->addColumn('withdraw_type', function($row){
                    return $row->withdraw_type;
                })
                ->addColumn('withdraw_to', function($row){
                    if ($row->withdraw_to) {
                        $acc = Account::where('id', $row->withdraw_to)->first();
                    }
                    return ($row->withdraw_to && $acc) ? $acc->code : $row->withdraw_type;
                })
                ->addColumn('withdraw_date', function ($row) {
                    $date = date('Y-m-d', strtotime($row->withdraw_date));
                    $time = date('H:i:s', strtotime($row->withdraw_date));
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function($row){
                    if($row->status == 1){
                        return "<div class='badge bg-outline-success'>Approved</div>";
                    }elseif($row->status == 2){
                        return "<div class='badge bg-outline-danger'>Rejected</div>";
                    }else{
                        return "<div class='badge bg-outline-primary'>Pending</div>";
                    }
                })
                ->addColumn('action', function($row){
                    return "<a href='/admin/trading_withdrawal_details?id={$row->id}' class=' style='font-size: 13px;padding: 2px 20px;'><i class='fe fe-eye fs-14 text-info'></i></a>";
                })
                ->addColumn('created_date', function($row){
                    return date('Y-m-d', strtotime($row->withdraw_date));
                })
                ->addColumn('created_time', function($row){
                    return date('H:i:s', strtotime($row->withdraw_date));
                })
                ->rawColumns(['account_no', 'amount', 'withdraw_type', 'withdraw_to','withdraw_date','status','action'])
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
        $sql = "SELECT e.client_index, (e.id) as enc_id,e.username, e.email, e.number, e.userRole, e.gender, e.dob, e.address, e.website, e.uid, e.company_name, e.company_address, e.company_number, e.country,e.state, e.city, e.zipcode, COUNT(pages.page_id) as permissions_count, e.status,r.name,r.id
                FROM emplist e
                LEFT JOIN permissions p ON e.role_id = p.role_id
                LEFT JOIN roles r ON e.role_id = r.id
                LEFT JOIN pages ON p.page_id = pages.page_id
                GROUP BY e.id";
        $query = DB::select($sql);
        $results = $query;
        $data = [];

        foreach ($results as $row) {
            $dat = $row;
            $dat->status = $row->status == 1 ? '<span class="badge bg-outline-success">Active</span>' : '<span class="badge bg-outline-danger">Inactive</span>';
            $dat->action = (session('userData')['userRole'] == "Super Admin" ? '<a data-id="' . $row->client_index . '" class="btn btn-sm btn-secondary update-user" data-bs-toggle="modal" data-bs-target="#updateUserModal" >Edit</a>' : '');
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

        header('Content-Type: application/json');
        $sql = "SELECT * from roles";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $dat = $row;
            $dat->status = $row->is_active == 1 ? '<span class="badge bg-outline-success">Active</span>' : '<span class="badge bg-outline-danger">Inactive</span>';
            $dat->action = ' <a data-id="' . $row->role_id . '" class="btn btn-sm btn-secondary me-1 update-role" href="#">Edit</a>' . ($row->is_active == 1 ? '<a class="btn btn-sm btn-danger" href="#" onclick="updateStatus(`' . $row->role_id . '`,0)">Deactivate</a>' : '<a class="btn btn-sm btn-success" href="#" onclick="updateStatus(`' . $row->role_id . '`,1)">Activate</a>');

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

        header('Content-Type: application/json');
        $sql = "SELECT * FROM  roles WHERE role_id=" . $id;
        $query = DB::select($sql);
        if (count($query)) {
            $result = $query[0];
            return $result;
        } else {
            return [];
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
        header('Content-Type: application/json');
        $sql = "SELECT * from wallet_deposit where user_id='" . $id . "' AND deposit_type NOT IN ('Internal Transfer', 'CRM', 'Wallet Transfer') order by id desc";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {

            $data[] = [
                'created_on' => $row->deposted_date,
                'from_to' => $row->code ?? 'Wallet',
                'payment_method' => $row->deposit_type,
                'amount' => '$' . $row->deposit_amount,
                'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
            ];
        }

        return ['data' => $data];
    }
    public function getLatestWithdrawal($id)
    {
        // header('Content-Type: application/json');
        // $sql = "SELECT * from trade_withdrawal where email='" . $id . "' AND withdraw_type != 'Internal Transfer' order by id desc";
        // $query = DB::select($sql);
        $query = WalletWithdraw::with('user')
            ->where('user_id', $id)
            ->where('Status', 1)
            ->where('withdraw_type', ['Wallet Withdrawal'])
            ->get();

        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'created_on' => $row->withdraw_date,
                'from_to' => 'Wallet',
                'payment_method' => $row->withdraw_type,
                'amount' => '$' . $row->withdraw_amount,
                'fee' => '$' . $row->withdraw_transaction_fee,
                'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>')
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
                'created_on' => $row->deposted_date,
                'from' => ($row->deposit_from && $row->accountDepositFrom) ? $row->accountDepositFrom->code : $deposit_from,
                'to' => $row->code,
                'amount' => '$' . $row->deposit_amount,
                'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
            ];
        }
        return ['data' => $data];
    }
    public function getIbUsers()
    {

        $rmCondition = "";
        if (session('userData')['userRole'] == "Relationship Manager") {
            $rmCondition = "  left join relationship_manager rm on(rm.user_id=ib1.email) where rm.rm_id='" . session('alogin') . "'";
        }
        header('Content-Type: application/json');
        $sql = "SELECT
    ib1.*,
    account_types.ac_name as grp,
    sum(ib_wallet.ib_wallet) as deposit,
    sum(ib_wallet.ib_withdraw) as withdraw
FROM
    ib1
LEFT JOIN
    ib_wallet
ON
    ib1.email = ib_wallet.email
LEFT JOIN account_types on account_types.ac_index = ib1.indexId " . $rmCondition . "
    group by ib1.email";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id' => $row->id,
                'enc' => ($row->user_id),
                // 'ib_category_id' => $row->ib_category_id,
                'ib_plan_details_id' => $row->ib_plan_details_id,
                'grp' => $row->grp,
                'name' => $row->name,
                'email' => $row->email,
                'country' => $row->country,
                'number' => $row->number,
                'date' => $row->reg_date,
                'total_deposit' => $row->deposit ? '$' . $row->deposit : '$0',
                'total_withdrawal' => $row->withdraw ? '$' . $row->withdraw : '$0',
                'status' => $row->status
            ];
        }
        return ['data' => $data];
    }

    public function getPendingIbUsers2(Request $request)
    {


    //     header('Content-Type: application/json');
    //     $sql = "SELECT
    //             ib1.*,
    //             account_types.ac_name as grp,
    //             sum(ib_wallet.ib_wallet) as deposit,
    //             sum(ib_wallet.ib_withdraw) as withdraw
    //         FROM
    //             ib1
    //         LEFT JOIN
    //             ib_wallet
    //         ON
    //             ib1.email = ib_wallet.email
    //         LEFT JOIN account_types on account_types.ac_index = ib1.indexId " . $rmCondition . "
    //         and ib1.status = 0
    // group by ib1.email";
    //     $query = DB::select($sql);
    //     $results = $query;
    //     $data = [];


        $role = session('userData')['userRole'];
        $alogin = session('alogin');

        // Base query

        $rmCondition = Ib1::where('status', 0)
            ->select('ib1.*')
            ->with(['user', 'ibWallet','planDetails.accountType'])
            ->where('status',0);


        if ($role !== "Super Admin") {
            $rmCondition->whereHas('user');
        }

        if ($role === "Relationship Manager") {
            $rmCondition->whereHas('relationshipManager', function ($query) use ($alogin) {
                $query->where('rm_id', $alogin);
            });
        }

        $rmCondition->orderBy('id', 'desc');


        if ($request->ajax()) {
            return DataTables::of($rmCondition)
                ->addColumn('id', function($row){
                    return $row->id;
                })
                ->addColumn('name', function($row){
                    if($row->planDetails){
                        $small = $row->planDetails->accountType->ac_name != null ? $row->planDetails->accountType->ac_name : '';
                    }else{
                        $small = '';
                    }

                    return "<a href='/admin/client_details/{$row->user_id}'><div class='d-flex align-items-center'><div class='me-2'><svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg></div><div><div class='lh-1'><span>{$row->name}</span></div><div class='lh-1'><span class='fs-11 text-muted'>{$row->email}</span></div>{$small}</div></div></a>";
                })
                ->addColumn('total_deposit', function($row){
                    $total_deposit = $row->ibWallet ? $row->ibWallet->sum('ib_wallet') : '$'+0;
                    return $total_deposit;
                })
                ->addColumn('total_withdrawal', function($row){
                    $total_withdrawal = $row->ibWallet ? $row->ibWallet->sum('ib_withdraw') : '$'+0;
                    return $total_withdrawal;
                })
                ->addColumn('date', function ($row) {
                    $date = date('Y-m-d', strtotime($row->created_at));
                    $time = date('H:i:s', strtotime($row->created_at));
                    return "<div class='lh-1'>
                                $date
                            </div>
                            <div class='lh-2 text-muted'>
                                $time
                            </div>";
                })
                ->addColumn('status', function($row){
                    if($row->status == 1){
                        return "<button class='ibToggle badge btn-sm btn btn-outline-success'>Active IB</button>";
                    }elseif($row->status == 2){
                        return "<button class='ibToggle badge btn-sm btn btn-outline-danger'>Rejected</button>";
                    }elseif($row->status == 0){
                        return "<button class='ibToggle badge btn-sm btn btn-outline-info'>IB Requested</button>";
                    }else{
                        return "<button class='ibToggle badge btn-sm btn btn-outline-primary'>Not Requested</button>";
                    }
                })
                ->addColumn('action', function($row){
                    return "<a href='/admin/trading_withdrawal_details?id={$row->id}' class=' style='font-size: 13px;padding: 2px 20px;'><i class='fe fe-eye fs-14 text-info'></i></a>";
                })
                ->addColumn('fullname', function($row){
                    return $row->user->fullname;
                })
                ->addColumn('fullemail', function($row){
                    return $row->email;
                })
                ->addColumn('created_date', function($row){
                    return date('Y-m-d', strtotime($row->created_at));
                })
                ->addColumn('created_time', function($row){
                    return date('H:i:s', strtotime($row->created_at));
                })
                ->rawColumns(['id', 'name', 'total_deposit', 'total_withdrawal','date','status','action'])
                ->make(true);
        }

        return response()->json(['message' => 'Invalid request'], 400);
    }

    public function getPendingIbUsers()
    {

        $rmCondition = " left join aspnetusers user on(user.email=ib1.email) ";
        if (session('userData')['userRole'] == "Relationship Manager") {
            $rmCondition .= "  left join relationship_manager rm on(rm.user_id=ib1.email) where rm.rm_id='" . session('alogin') . "'";
        } else {
            $rmCondition .= " where (1) ";
        }
        header('Content-Type: application/json');
        $sql = "SELECT
    ib1.*,
    account_types.ac_name as grp,
    sum(ib_wallet.ib_wallet) as deposit,
    sum(ib_wallet.ib_withdraw) as withdraw
FROM
    ib1
LEFT JOIN
    ib_wallet
ON
    ib1.email = ib_wallet.email
LEFT JOIN account_types on account_types.ac_index = ib1.indexId " . $rmCondition . "
and ib1.status = 0
    group by ib1.email";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id' => $row->indexId,
                'enc' => ($row->user_id),
                'acc_type' => $row->acc_type,
                'grp' => $row->grp,
                'name' => $row->name,
                'email' => $row->email,
                'country' => $row->country,
                'number' => $row->number,
                'date' => $row->reg_date,
                'total_deposit' => $row->deposit ? '$' . $row->deposit : '$0',
                'total_withdrawal' => $row->withdraw ? '$' . $row->withdraw : '$0',
                'status' => $row->status
            ];
        }
        return ['data' => $data];
    }



    public function getAdminDetails($id)
    {

        header('Content-Type: application/json');
        $sql = "SELECT * FROM  emplist WHERE client_index=" . $id;
        $query = DB::select($sql);
        $result = $query[0];
        unset($result->password);
        return $result;
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

        $result = DB::table('aspnetusers')
            ->select('status', 'email', 'email_confirmed', 'kyc_verify')
            ->where(DB::raw('id'), '=', $user_id)
            ->first();
        try {

            $updated = DB::table('aspnetusers')
                ->where(DB::raw('id'), '=', $user_id)
                ->update([
                    'status' => $user_status,
                    'email_confirmed' => $email_confirmed,
                    'kyc_verify' => $kyc_verify,
                ]);

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
        ->select('id','client_index', 'email', 'username')
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
            $result = Ib1::with('user')->whereRaw('user_id = ?', [$clientId])->first();

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
                    $ib1->status = 1;
                    $ib1->save();
                }
            }
            $updated = Ib1::where('user_id', $clientId)
                ->update([
                    'status' => $ibStatus,
                    'ib_plan_details_id' => $ibGroup
                ]);

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
}
