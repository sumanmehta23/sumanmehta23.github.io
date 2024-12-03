<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use DB;
use Exception;
use App\Models\Ib1;
use App\Models\User;
use App\Models\UserLog;
use App\Models\TradeDeposit;
use App\Models\WalletWithdraw;
use Illuminate\Http\Request;
use App\Models\TradeWithdrawals;

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
            switch ($action) {
                case 'getClientList':
                    $this->getClientList($requestData);
                    break;
                case 'getClientDetails':
                    $this->getClientDetails($requestData);
                    break;
                case 'getWalletDeposit':
                    $this->getWalletDeposit();
                    break;
                case 'getWalletWithdrawal':
                    $this->getWalletWithdrawal();
                    break;
                case 'getTradingDeposit':
                    $this->getTradingDeposit();
                    break;
                case 'getTradingWithdrawal':
                    $this->getTradingWithdrawal();
                    break;
                case 'getInternalTransfer':
                    $this->getInternalTransfer();
                    break;
                case 'getPendingWalletDeposit':
                    $this->getPendingWalletDeposit();
                    break;
                case 'getPendingWalletWithdrawal':
                    $this->getPendingWalletWithdrawal();
                    break;
                case 'getPendingTradingDeposit':
                    $this->getPendingTradingDeposit();
                    break;
                case 'getPendingTradingWithdrawal':
                    $this->getPendingTradingWithdrawal();
                    break;
                case 'getPendingInternalTransfer':
                    $this->getPendingInternalTransfer();
                    break;
                case 'getKYCHistory':
                    $this->getKYCHistory();
                    break;
                case 'getBankDetails':
                    $this->getBankDetails();
                    break;
                case 'getAdminUsers':
                    $this->getAdminUsers();
                    break;
                case 'getMT5Groups':
                    $this->getMT5Groups($type);
                    break;
                case 'getIbGroups':
                    $this->getIbGroups($type);
                    break;
                case 'getIbPlans':
                    $this->getIbPlans($type);
                    break;
                case 'getMT5Category':
                    $this->getMT5Category($type);
                    break;
                case 'getRoles':
                    $this->getRoles();
                    break;
                case 'getRolePermissions':
                    $this->getRolePermisions();
                    break;
                case 'getAllTickets':
                    $this->getAllTickets();
                    break;
                case 'getOpenTickets':
                    $this->getOpenTickets();
                    break;
                case 'getClosedTickets':
                    $this->getClosedTickets();
                    break;
                case 'getRoleDetails':
                    $this->getRoleDetails($id);
                    break;
                case 'getPaymentGateways':
                    $this->getPaymentGateways();
                    break;
                case 'ibEnroll':
                    $this->ibEnroll();
                    break;
                case 'getLatestDeposit':
                    $this->getLatestDeposit($id);
                    break;
                case 'getLatestWithdrawal':
                    $this->getLatestWithdrawal($id);
                    break;
                case 'getLatestTransfer':
                    $this->getLatestTransfer($id);
                    break;
                case 'getIbUsers':
                    $this->getIbUsers();
                    break;
                case 'getPendingIbUsers':
                    $this->getPendingIbUsers();
                    break;
                case 'getAdminDetails':
                    $this->getAdminDetails($id);
                    break;
                case 'getPaymentDetails':
                    $this->getPaymentDetails($id);
                    break;
                case 'updateClientStatus':
                    $this->updateClientStatus($requestData);
                    break;
                case 'getIbList':
                    $this->getIbList($id);
                    break;
                case 'getRMbyGroup':
                    $this->getRMbyGroup($id);
                    break;
                case 'getListOfGroups':
                    $this->getListOfGroups($search);
                    break;
                case 'getListOfUsers':
                    $this->getListOfUsers($search);
                    break;
                case 'getListOfIBs':
                    $this->getListOfIBs($search);
                    break;
                case 'requestIB':
                    $this->requestIB($requestData);
                    break;

                default:
                    echo json_encode(['error' => 'Invalid function call']);
                    break;
            }
        } else {
            echo json_encode(['error' => 'No functions specified']);
        }
    }


    public function getListOfGroups($string)
    {

        $sql = "SELECT account_types.ac_index as id,account_types.ac_group as text from account_types where account_types.ac_group like '%$string%' and status = 1";
        $query = DB::select($sql);
        $results = $query;
        echo json_encode($results);
    }
    public function getListOfUsers($string)
    {

        $sql = "SELECT aspnetusers.email as id,concat(aspnetusers.fullname,' [',aspnetusers.email,']') as text from aspnetusers where (aspnetusers.email like '%$string%' OR aspnetusers.fullname like '%$string%') and status = 1";
        $query = DB::select($sql);
        $results = $query;
        echo json_encode($results);
    }
    public function getListOfIBs($string)
    {

        $sql = "SELECT aspnetusers.email as id,concat(aspnetusers.fullname,' [',aspnetusers.email,']') as text from aspnetusers
  join ib1 on ib1.email = aspnetusers.email
  where (aspnetusers.email like '%$string%' OR aspnetusers.fullname like '%$string%') and aspnetusers.status = 1 and ib1.status = 1";
        $query = DB::select($sql);
        $results = $query;
        echo json_encode($results);
    }

    public function getClientList($requestData)
    {

        $rmCondition = " ";
        if (session('userData')['userRole'] != "Super Admin") {
            $rmCondition .= " left join aspnetusers user on(user.email=ap.email) ";
        } else {
            $rmCondition .= " where";
        }
        if (session('userData')['userRole'] == "Relationship Manager") {
            $rmCondition .= "  left join relationship_manager rmgr on(rmgr.user_id=ap.email) where rmgr.rm_id='" . session('alogin') . "' and ";
        }
        if (session('userData')['userRole'] != "Super Admin") {
            if (session('userData')['userRole'] == "Relationship Manager") {
                $rmCondition .= " (1) and ";
            } else {
                $rmCondition .= " where (1) and";
            }
        }
        if(isset($requestData['rm_id']) && !empty($requestData['rm_id'])){
            $rmCondition = "  left join relationship_manager rmgr on(rmgr.user_id=ap.email) where (rmgr.rm_id)='" . $requestData['rm_id'] . "' and ";
        }
        header('Content-Type: application/json');
        $sql = "SELECT ibs.name as ib_name,c.country_alpha,emp.username as rm_name,rm.rm_id,(ap.id) as enc_id,ap.fullname as fullname,ap.*,COALESCE(SUM(tb.deposit_amount), 0) as deposit_amount,COALESCE(SUM(tb.trading_deposited), 0) as trading_deposited,COALESCE(SUM(tb.trading_withdrawal), 0) as trading_withdrawal,COALESCE(SUM(tb.withdraw_amount), 0) as withdraw_amount,ib1.status as ib_status,ib1.acc_type as ib_group from aspnetusers ap
  LEFT JOIN ib1 on ib1.email = ap.email
  LEFT JOIN ib1 as ibs on ibs.email = ap.ib1
  LEFT JOIN relationship_manager rm on(ap.email =rm.user_id)
  LEFT JOIN emplist emp on(rm.rm_id =emp.email)
  LEFT JOIN countries c on(ap.country =c.country_name)
  LEFT JOIN total_balance tb on (ap.email=tb.email) " . $rmCondition . " (1=1) group by ap.email";
        $results = DB::select($sql);
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id' => $row->id,
                'enc' => ($row->email),
                'enc_id' => $row->enc_id,
                'fullname' => $row->fullname,
                'created_at' => $row->created_at,
                'created_date' => date("d-m-Y", strtotime($row->created_at)),
                'created_time' => date('H:s:i', strtotime($row->created_at)),
                'email' => $row->email,
                'phone' => $row->number,
                'country' => $row->country_alpha,
                'ib' => $row->ib1,
                'ib_name' => $row->ib_name,
                'ib_status' => $row->ib_status,
                'kyc_verify' => $row->kyc_verify,
                'rm_id' => $row->rm_name ?? '',
                'rmid' => $row->rm_id ?? '',
                'ib_group' => $row->ib_group,
                'total_deposit' => $row->trading_deposited + $row->deposit_amount,
                'total_withdraw' => $row->trading_withdrawal + $row->withdraw_amount,
                'status' => $row->status,
                'email_confirmed' => $row->email_confirmed,
                'action' => ' <a class="btn btn-sm btn-secondary me-2 edit-user d-none" data-id="' . $row->email . '"><i class="fa fa-edit"></i></a><a class="btn btn-sm btn-primary" href="/admin/client_details?id=' . ($row->email) . '"><i class="fa fa-eye"></i></a>'
            ];
        }
        echo json_encode(['data' => $data]);
    }
    public function getWalletDeposit()
    {

        $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
        if (session('userData')['userRole'] == "Relationship Manager") {
            $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "'";
        } else {
            $rmCondition .= " where ";
        }

        header('Content-Type: application/json');
        $sql = "SELECT (user.id) as enc_id,user.fullname as fullname,trs.* from wallet_deposit trs " . $rmCondition . " trs.deposit_type!='Internal Transfer' order by trs.id desc";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'email' => $row->email,
                'enc_id' => $row->enc_id,
                'fullname' => $row->fullname,
                'amount' => '$' . $row->deposit_amount,
                'payment_mode' => $row->deposit_type,
                'deposit_date' => $row->deposted_date,
                'status' => $row->Status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
                'action' => ' <a class="btn btn-sm btn-primary" href="/admin/wallet_deposit_details?id=' . ($row->id) . '">View</a>'
            ];
        }
        echo json_encode(['data' => $data]);
    }
    public function getWalletWithdrawal()
    {

        $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
        if (session('userData')['userRole'] == "Relationship Manager") {
            $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "'";
        }else{
            $rmCondition .= " where ";
        }

        header('Content-Type: application/json');
        $sql = "SELECT (user.id) as enc_id,user.fullname as fullname,trs.* from wallet_withdraw trs " . $rmCondition . " trs.withdraw_type!='Internal Transfer' order by trs.id desc";
        $results = DB::select($sql);
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'email' => $row->email,
                'enc_id' => $row->enc_id,
                'fullname' => $row->fullname,
                'amount' => '$' . $row->withdraw_amount,
                'payment_mode' => $row->withdraw_type,
                'withdraw_date' => $row->withdraw_date,
                'status' => $row->Status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
                'action' => ' <a class="btn btn-sm btn-primary" href="/admin/wallet_withdrawal_details?id=' . ($row->id) . '">View</a>'
            ];
        }
        echo json_encode(['data' => $data]);
    }
    public function getTradingDeposit()
    {
        $rmCondition = " left join accounts user on(user.email=trs.email) ";
        // $condition = "";

        // if (!isset($_GET['id'])) {
        //     if (session('userData')['userRole'] == "Relationship Manager") {
        //         $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "' ";
        //     } else {
        //     }
        // }
        // if (isset($_GET['id'])) {
        //     $condition = ' where trs.code=' . $_GET['id'];
        // }
        // header('Content-Type: application/json');
        // $sql = "SELECT (user.id) as enc_id,user.name as fullname,trs.* from trade_deposits trs " . $rmCondition . $condition . " group by trs.id order by trs.id desc";
        // $query = DB::select($sql);
        // $results = $query;

        $query = TradeDeposit::with(['user','account']);
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
        $results = $query->orderByDesc('id')->get();
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id' => 'TDID' . sprintf("%05d", $row->id),
                'account_no' => $row->code,
                'enc_id' => $row->enc_id,
                'fullname' => $row->fullname,
                'amount' => '$' . $row->deposit_amount,
                'deposit_type' => $row->deposit_type,
                'deposit_from' => $row->deposit_from ?? $row->deposit_type,
                'deposit_date' => $row->deposted_date,
                'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
                'action' => '<a href="/admin/trading_deposit_details?id=' . ($row->id) . '" class="" style="font-size: 13px;padding: 2px 20px;"><i class="fe fe-eye fs-14 text-info"></i></a>'
            ];
        }

        echo json_encode(['data' => $data]);
    }
    public function getTradingWithdrawal()
    {

        // $condition = '';
        // $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
        // if (!isset($_GET['id'])) {
        //     if (session('userData')['userRole'] == "Relationship Manager") {
        //         $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "'  ";
        //     } else {
        //     }
        // }
        // if (isset($_GET['id'])) {
        //     $condition = ' where trs.code=' . $_GET['id'];
        // }
        // header('Content-Type: application/json');
        // $sql = "SELECT (user.id) as enc_id,user.fullname as fullname,trs.* from trade_withdrawal trs " . $rmCondition . $condition . " order by trs.id desc";
        // $query = DB::select($sql);
        // $results = $query;

        $query = TradeWithdrawals::with(['user', 'withdrawTo','account']);

        // Add conditions based on session and GET parameters
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
        $withdrawals = $query->orderByDesc('id')->get();
        $data = [];

        foreach ($withdrawals as $row) {
            if($row->to_account_id){
                $acc = Account::where('id',$row->to_account_id)->first();
            }
            $data[] = [
                'id' => 'TWID' . sprintf("%05d", $row->id),
                'account_no' => $row->account->code,
                'enc_id' => $row->enc_id,
                'fullname' => $row->fullname,
                'amount' => '$' . $row->withdrawal_amount,
                'withdraw_type' => $row->withdraw_type,
                'to_account_id' => $row->to_account_id ? $acc->code : $row->withdraw_type,
                'withdraw_date' => $row->withdraw_date,
                'status' => $row->Status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
                'action' => '<a href="/admin/trading_withdrawal_details?id=' . ($row->id) . '" class="" style="font-size: 13px;padding: 2px 20px;"><i class="fe fe-eye fs-14 text-info"></i></a>'
            ];
        }
        echo json_encode(['data' => $data]);
    }
    public function getInternalTransfer()
    {
        // $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
        // if (session('userData')['userRole'] == "Relationship Manager") {
        //     $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "' and ";
        // } else {
        //     $rmCondition .= " where ";
        // }
        // header('Content-Type: application/json');
        // $sql = "SELECT trs.* from trade_deposits trs " . $rmCondition . " trs.deposit_type = 'Internal Transfer' order by trs.id desc";
        // $query = DB::select($sql);
        // $results = $query;

        $query = TradeDeposit::with(['user','account']);

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
        $deposits = $query->orderByDesc('id')->get();


        $data = [];
        foreach ($deposits as $row) {
            // dd($row);
            $data[] = [
                'id' => 'ITID' . sprintf("%05d", $row->id),
                'email' => $row->email,
                'amount' => '$' . $row->deposit_amount,
                'transfer_from' => $row->deposit_from,
                'transfer_to' => $row->code,
                'status' => $row->Status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
                'action' => ' <a class="btn btn-sm btn-primary" href="/admin/internal_transfer_details">View</a>'
            ];
        }
        echo json_encode(['data' => $data]);
    }
    public function getPendingWalletDeposit()
    {

        $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
        if (session('userData')['userRole'] == "Relationship Manager") {
            $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "' and ";
        } else {
            $rmCondition .= " where (1) and ";
        }
        header('Content-Type: application/json');
        $sql = "SELECT (user.id) as enc_id,user.fullname as fullname,trs.* from wallet_deposit trs " . $rmCondition . " trs.Status = 0 order by trs.id desc";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id' => 'WDID' . sprintf("%05d", $row->id),

                'email' => $row->email,
                'enc_id' => $row->enc_id,
                'fullname' => $row->fullname,
                'amount' => '$' . $row->deposit_amount,
                'payment_mode' => $row->deposit_type,
                'deposit_date' => $row->deposted_date,
                'status' => $row->Status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
                'action' => ' <a class="btn btn-sm btn-primary" href="/admin/wallet_deposit_details?id=' . ($row->id) . '">View</a>'
            ];
        }
        echo json_encode(['data' => $data]);
    }
    public function getPendingWalletWithdrawal()
    {

        // $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
        // if (session('userData')['userRole'] == "Relationship Manager") {
        //     $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "' and ";
        // } else {
        //     $rmCondition .= " where (1) and ";
        // }
        // header('Content-Type: application/json');
        // $sql = "SELECT (user.id) as enc_id,user.fullname as fullname,trs.* from wallet_withdraw trs " . $rmCondition . " trs.Status = 0 order by trs.id desc";
        // $query = DB::select($sql);
        // $results = $query;

        $query = WalletWithdraw::with(['user']);

        // Add conditions based on session and GET parameters
            if (session('userData')['userRole'] == "Relationship Manager") {
                $rmId = session('alogin');
                $query->whereHas('user.relationshipManager', function ($q) use ($rmId) {
                    $q->where('rm_id', $rmId);
                });
            }
            else {
                $query->where('Status', 0);
            }

        // Fetch data
        $results = $query->orderByDesc('id')->get();

        $data = [];

        foreach ($results as $row) {
            // dd($row);
            $data[] = [
                'id' => 'WWID' . sprintf("%05d", $row->id),
                'email' => $row->email,
                'enc_id' => $row->enc_id,
                'fullname' => $row->user->fullname,
                'amount' => '$' . $row->withdraw_amount,
                'payment_mode' => $row->withdraw_type,
                'withdraw_date' => $row->withdraw_date,
                'status' => $row->Status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
                'action' => ' <a class="btn btn-sm btn-primary" href="/admin/wallet_withdrawal_details?id=' . $row->id . '">View</a>'
            ];
        }
        echo json_encode(['data' => $data]);
    }
    public function getPendingTradingDeposit()
    {

        $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
        if (session('userData')['userRole'] == "Relationship Manager") {
            $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "' and ";
        } else {
            $rmCondition .= " where (1) and ";
        }
        header('Content-Type: application/json');
        $sql = "SELECT trs.id as raw_erc,trs.* from trade_deposits trs " . $rmCondition . " trs.Status = 0 order by trs.id desc";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id' => 'TDID' . sprintf("%05d", $row->id),
                'enc_id' => $row->raw_erc,
                'account_no' => $row->code,
                'amount' => '$' . $row->deposit_amount,
                'deposit_type' => $row->deposit_type,
                'deposit_from' => $row->deposit_from,
                'deposit_date' => $row->deposted_date,
                'status' => $row->Status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
                'action' => ' <a class="btn btn-sm btn-primary" href="/admin/trading_deposit_details?id=' . ($row->id) . '">View</a>'
            ];
        }
        echo json_encode(['data' => $data]);
    }
    public function getPendingTradingWithdrawal()
    {

        $rmCondition = " left join aspnetusers user on(user.email=trs.email) ";
        if (session('userData')['userRole'] == "Relationship Manager") {
            $rmCondition .= " left join relationship_manager rm on (trs.email=rm.user_id) where rm.rm_id='" . session('alogin') . "' and ";
        } else {
            $rmCondition .= " where (1) and ";
        }
        header('Content-Type: application/json');
        $sql = "SELECT trs.* from trade_withdrawal trs " . $rmCondition . " trs.Status = 0 order by trs.id desc";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id' => 'TWID' . sprintf("%05d", $row->id),
                'account_no' => $row->code,
                'amount' => '$' . $row->withdrawal_amount,
                'withdraw_type' => $row->withdraw_type,
                'to_account_id' => $row->to_account_id,
                'withdraw_date' => $row->withdraw_date,
                'status' => $row->Status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
                'action' => ' <a class="btn btn-sm btn-primary" href="/admin/trading_withdrawal_details?id=' . $row->id . '">View</a>'
            ];
        }
        echo json_encode(['data' => $data]);
    }
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
                'status' => $row->Status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
                'action' => ' <a class="btn btn-sm btn-primary" href="/admin/internal_transfer_details?id=' . $row->itIndex . '">View</a>'
            ];
        }
        echo json_encode(['data' => $data]);
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

        echo json_encode(['data' => $results]);
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
        echo json_encode(['data' => $data]);
    }
    public function getAdminUsers()
    {

        header('Content-Type: application/json');
        $sql = "SELECT e.client_index, (e.client_index) as enc_id,e.username, e.email, e.number, e.userRole, e.gender, e.dob, e.address, e.website, e.uid, e.company_name, e.company_address, e.company_number, e.country,e.state, e.city, e.zipcode, COUNT(pages.page_id) as permissions_count, e.status,r.name,r.id
                FROM emplist e
                LEFT JOIN permissions p ON e.role_id = p.role_id
                LEFT JOIN roles r ON e.role_id = r.id
                LEFT JOIN pages ON p.page_id = pages.page_id
                GROUP BY e.client_index";
        $query = DB::select($sql);
        $results = $query;
        $data = [];

        foreach ($results as $row) {
            $dat = $row;
            $dat->status = $row->status == 1 ? '<span class="badge bg-outline-success">Active</span>' : '<span class="badge bg-outline-danger">Inactive</span>';
            $dat->action = (session('userData')['userRole'] == "Super Admin" ? '<a data-id="' . $row->client_index . '" class="btn btn-sm btn-secondary update-user" data-bs-toggle="modal" data-bs-target="#updateUserModal" >Edit</a>' : '');
            $data[] = $dat;
        }
        echo json_encode(['data' => $data]);
    }

    public function getMT5Category($type = "category")
    {

        header('Content-Type: application/json');
        $sql = "SELECT * from mt5_group_categories where mt5_grp_cat_type = '" . $type . "' order by mt5_grp_cat_id";
        $query = DB::select($sql);
        $results = $query;
        echo json_encode(['data' => $results]);
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
        echo json_encode(['data' => $data]);
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
        echo json_encode(['data' => $results]);
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
        echo json_encode(['data' => $results]);
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
        echo json_encode(['data' => $data]);
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
        echo json_encode(['data' => $data]);
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
            $dat->status = $row->Status == 'Open' ? '<span class="badge bg-outline-success">Open</span>' : '<span class="badge bg-outline-danger">Closed</span>';
            $data[] = $dat;
        }
        echo json_encode(['data' => $data]);
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
            $dat->status = $row->Status == 'Open' ? '<span class="badge bg-outline-success">Open</span>' : '<span class="badge bg-outline-danger">Closed</span>';
            $data[] = $dat;
        }
        echo json_encode(['data' => $data]);
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
            $dat->status = $row->Status == 'Open' ? '<span class="badge bg-outline-success">Open</span>' : '<span class="badge bg-outline-danger">Closed</span>';
            $data[] = $dat;
        }
        echo json_encode(['data' => $data]);
    }
    public function getRoleDetails($id)
    {

        header('Content-Type: application/json');
        $sql = "SELECT * FROM  roles WHERE role_id=" . $id;
        $query = DB::select($sql);
        if (count($query)) {
            $result = $query[0];
            echo json_encode($result);
        } else {
            echo json_encode([]);
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
        echo json_encode(['data' => $data]);
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
            echo "true";
        } catch (Exception $e) {
            echo "false";
        }
        exit();
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
                'status' => $row->Status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
            ];
        }

        echo json_encode(['data' => $data]);
    }
    public function getLatestWithdrawal($id)
    {
        header('Content-Type: application/json');
        // $sql = "SELECT * from trade_withdrawal where email='" . $id . "' AND withdraw_type != 'Internal Transfer' order by id desc";
        // $query = DB::select($sql);
        $query = TradeWithdrawals::with('account')
                            ->where('user_id',$id)
                            ->where('withdraw_type',['Internal Transfer'])
                            ->get();

        $results = $query;
        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'created_on' => $row->withdraw_date,
                'from_to' => $row->code,
                'payment_method' => $row->withdraw_type,
                'amount' => '$' . $row->withdrawal_amount,
                'status' => $row->Status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>')
            ];
        }
        echo json_encode(['data' => $data]);
    }
    public function getLatestTransfer($id)
    {
        header('Content-Type: application/json');
        $sql = "SELECT * from trade_deposits where deposit_type IN ('Internal Transfer', 'CRM', 'Wallet Transfer')  and user_id='" . $id . "'  order by id desc";
        $query = DB::select($sql);
        $results = $query;
        $data = [];
        // var_dump($results);
        // die;
        foreach ($results as $row) {
            $data[] = [
                'created_on' => $row->deposted_date,
                'from' => $row->deposit_from ? $row->deposit_from : $row->deposit_type,
                'to' => $row->code,
                'amount' => '$' . $row->deposit_amount,
                'status' => $row->status == 1 ? '<div class="badge bg-outline-success">Approved</div>' : ($row->Status == 2 ? '<span class="badge bg-outline-danger">Rejected</span>' :
                    '<span class="badge bg-outline-primary">Pending</span>'),
            ];
        }
        echo json_encode(['data' => $data]);
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
                'id' => $row->indexId,
                'enc' => ($row->email),
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
        echo json_encode(['data' => $data]);
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
                'enc' => ($row->email),
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
        echo json_encode(['data' => $data]);
    }



    public function getAdminDetails($id)
    {

        header('Content-Type: application/json');
        $sql = "SELECT * FROM  emplist WHERE client_index=" . $id;
        $query = DB::select($sql);
        $result = $query[0];
        echo json_encode($result);
    }
    public function getPaymentDetails($id)
    {

        header('Content-Type: application/json');
        $sql = "SELECT * FROM  available_payment WHERE id=" . $id;
        $query = DB::select($sql);
        $result = $query[0];
        echo json_encode($result);
    }
    public function updateClientStatus($data)
    {
        header('Content-Type: application/json');

        $email = $data['client_id'];
        $email_confirmed = isset($data['email_confirmed']) && $data['email_confirmed'] === "on" ? 1 : 0;
        $user_status = isset($data['status']) && $data['status'] === "on" ? 1 : 0;
        $kyc_verify = isset($data['kyc_verify']) && $data['kyc_verify'] === "on" ? 1 : 0;

        $result = DB::table('aspnetusers')
            ->select('status', 'email', 'email_confirmed', 'kyc_verify')
            ->where(DB::raw('email'), '=', $email)
            ->first();

        try {
            $updated = DB::table('aspnetusers')
                ->where(DB::raw('email'), '=', $email)
                ->update([
                    'status' => $user_status,
                    'email_confirmed' => $email_confirmed,
                    'kyc_verify' => $kyc_verify,
                ]);

            if ($updated) {
                $data['email'] = $result->email;
                if ($result->status != $user_status) {
                    $data['field'] = 'status';
                    $data['value'] = $user_status;
                    $data['user_id']=$result->id;
                    $this->add_to_user_log($data);
                }
                if ($result->email_confirmed != $email_confirmed) {
                    $data['field'] = 'email_confirmed';
                    $data['value'] = $email_confirmed;
                    $data['user_id']=$result->id;
                    $this->add_to_user_log($data);
                }
                if ($result->kyc_verify != $kyc_verify) {
                    $data['field'] = 'kyc_verify';
                    $data['value'] = $kyc_verify;
                    $data['user_id']=$result->id;
                    $this->add_to_user_log($data);
                }
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No rows updated']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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
            ->where(DB::raw('email'), '=', $id)
            ->first();

        echo json_encode((array) $result);
    }

    public function getRMbyGroup($id)
    {

        $results = [];
        // $group_id = DB::table('aspnetusers')
        //   ->where(DB::raw('email'), '=', $id)
        //   ->value('group_id');
        // if ($group_id !== null) {
        $results = DB::table('emplist as emp')
            ->select('emp.client_index', 'emp.email', 'emp.username')
            ->where('emp.role_id', 2)
            ->get()
            ->toArray();
        // }
        echo json_encode($results);
    }

    public function getClientDetails($data)
    {
        $result = DB::table('aspnetusers')
            ->select(
                DB::raw('email as id'),
                'email',
                'password',
                'fullname',
                'country',
                'number AS telephone',
                'country_code',
                'password as confirm_password',
                // DB::raw("SUBSTRING(number, 1, LOCATE(')', number)) AS country_code"),
                // DB::raw("REPLACE(SUBSTRING_INDEX(number, ')', -1), ' ', '') AS telephone")
            )
            ->where(DB::raw('email'), '=', $data['id'])
            ->first();

        echo json_encode((array) $result);
    }

    public function requestIB($request)
    {
        try {
            $clientId = $request['client_id'];
            $ibStatus = $request['ib_status'];
            $ibGroup = $request['ib_group'];
            $result = Ib1::whereRaw('email = ?', [$clientId])->first();
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
            $updated = Ib1::whereRaw('email = ?', [$clientId])
                ->update([
                    'status' => $ibStatus,
                    'account_type_id' => $ibGroup
                ]);
            if ($updated) {
                echo json_encode(['status' => true, 'message' => 'IB details updated successfully']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Failed to update IB details']);
            }
        } catch (Exception $e) {
            dd($e->getMessage());
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }


}
