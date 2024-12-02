<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Exception;

class IBController extends Controller
{

    public function index(Request $request)
    {
        $userRole = $request->session()->get('userData.role_id');
        $alogin = $request->session()->get('alogin');


        $rmCondition = DB::table('ib1 as ib')
            ->leftJoin('aspnetusers as user', 'user.email', '=', 'ib.email');

        if ($userRole == "Relationship Manager") {
            $rmCondition->leftJoin('relationship_manager as rm', 'ib.email', '=', 'rm.user_id')
                ->where('rm.rm_id', $alogin);
        }


        $totalIb = $rmCondition->count('ib.indexId');


        $activeIbCount = (clone $rmCondition)
            ->where('ib.status', 1)
            ->count('ib.indexId');


        $totalClients = DB::table('ib1 as ib')
            ->join('aspnetusers as t2', 'ib.email', '=', 't2.ib1')
            ->leftJoin('aspnetusers as user', 'user.email', '=', 'ib.email');

        if ($userRole == "Relationship Manager") {
            $totalClients->leftJoin('relationship_manager as rm', 'ib.email', '=', 'rm.user_id')
                ->where('rm.rm_id', $alogin);
        }

        $totalClientsCount = $totalClients->count('ib.email');


        $pendingKyc = DB::table('ib1 as ib')
            ->join('kyc_update as kyc', 'ib.email', '=', 'kyc.email')
            ->leftJoin('aspnetusers as user', 'user.email', '=', 'ib.email');

            if ($userRole == "Relationship Manager") {
            $pendingKyc->leftJoin('relationship_manager as rm', 'ib.email', '=', 'rm.user_id')
                ->where('rm.rm_id', $alogin);
        }

        $pendingKycCount = $pendingKyc->where('kyc.Status', 0)
            ->count('ib.indexId');


        $ibInternal = DB::table('ib_internal as ib')
            ->leftJoin('aspnetusers as user', 'user.email', '=', 'ib.email');

            if ($userRole == "Relationship Manager") {
            $ibInternal->leftJoin('relationship_manager as rm', 'ib.email', '=', 'rm.user_id')
                ->where('rm.rm_id', $alogin);
        }

        $ibInternalCount = $ibInternal->count('ib.id');



        $ibPendingTrans = DB::table('ib_internal as ib')
            ->leftJoin('aspnetusers as user', 'user.email', '=', 'ib.email');

            if ($userRole == "Relationship Manager") {
            $ibPendingTrans->leftJoin('relationship_manager as rm', 'ib.email', '=', 'rm.user_id')
                ->where('rm.rm_id', $alogin);
        }


        $ibPendingTrans = $ibPendingTrans->get(); // Retrieve the results

        $rmCondition = DB::table('ib1 as ib1')
            ->join('kyc_update as kyc', 'ib1.email', '=', 'kyc.email');

        // Applying dynamic conditions
        if ($userRole == "Relationship Manager") {
            $rmCondition->leftJoin('relationship_manager as rm', 'ib1.email', '=', 'rm.user_id')
                ->where('rm.rm_id', $alogin);
        }

        // Fetching results for KYC where Status is 0
        $kycpending = $rmCondition->select('ib1.email', 'kyc.kyc_type', 'kyc.id', 'kyc.registered_date_js', 'kyc.Status')
            ->where('kyc.Status', 0)
            ->get(); // Use get() to retrieve results



        return view('admin.ib.ibdashboard', [
            'total_ib' => $totalIb,
            'active_ib' => $activeIbCount,
            'total_clients' => $totalClientsCount,
            'pending_kyc' => $pendingKycCount,
            'ib_internal' => $ibInternalCount,
            'ibPendingTrans' => $ibPendingTrans,
            'kycpending' => $kycpending
        ]);
    }

    public function list()
    {
        $accGroups = DB::table('ib_plan_details')
            ->select('ib_categories.ib_cat_name', 'ib_plan_details.ib_plan_id')
            ->leftJoin('ib_categories', 'ib_categories.ib_cat_id', '=', 'ib_plan_details.ib_plan_id')
            ->where('ib_plan_details.status', 1)
            ->groupBy('ib_plan_details.ib_plan_id')
            ->get(); // Use get() to retrieve results
        return view("admin.ib.iblist", ["acc_groups" => $accGroups]);
    }
    public function list_active()
    {
        $accGroups = DB::table('ib_plan_details')
            ->select('ib_categories.ib_cat_name', 'ib_plan_details.ib_plan_id')
            ->leftJoin('ib_categories', 'ib_categories.ib_cat_id', '=', 'ib_plan_details.ib_plan_id')
            ->where('ib_plan_details.status', 1)
            ->groupBy('ib_plan_details.ib_plan_id')
            ->get(); // Use get() to retrieve results
        // dd($accGroups);
        return view("admin.ib.iblist_active", ["acc_groups" => $accGroups]);
    }

    public function ib_settings()
    {
        // Get activeType if it's set
        $activeType = request()->get('activeType');

        // Step 1: Query for categories with counts of distinct account types
        $results = DB::table('ib_categories')
            ->select('ib_categories.*', DB::raw('count(DISTINCT ib_plan_details.acc_type) as count'))
            ->leftJoin('ib_plan_details', function ($join) {
                $join->on('ib_plan_details.ib_plan_id', '=', 'ib_categories.ib_cat_id')
                    ->whereNull('ib_plan_details.deleted_at');
            })
            ->groupBy('ib_categories.ib_cat_id')
            ->orderBy('ib_categories.ib_cat_id')
            ->get();

        // Step 2: Query for ib_plan_details with account types and category names
        $plans = DB::table('ib_plan_details')
            ->select(
                'ib_plan_details.*',
                'account_types.ac_group',
                'ib_categories.ib_cat_name',
                DB::raw('count(*) as count')
            )
            ->join('account_types', 'account_types.ac_index', '=', 'ib_plan_details.acc_type')
            ->join('ib_categories', 'ib_categories.ib_cat_id', '=', 'ib_plan_details.ib_plan_id')
            ->whereNull('ib_plan_details.deleted_at');

        // Apply the filter if `activeType` is not null
        if ($activeType !== null) {
            $plans->where(DB::raw('ib_categories.ib_cat_id'), '=', $activeType);
        }

        // Group and execute the query for plans
        $plans = $plans
            ->groupBy('ib_plan_details.ib_plan_id', 'ib_plan_details.acc_type')
            ->get();

        // Step 3: Query for all account types
        $groups = DB::table('account_types')->get();

        // Combine all results into a single array if you want to return or process them together
        $data = [
            'results' => $results,
            'plans' => $plans,
            'groups' => $groups,
            'activeType' => $activeType
        ];

        return view("admin.ib.ib_settings", $data);
    }

    public function ibCommission()
    {
        $ibCategories = DB::table('ib_categories')->get();
        $accountTypes = DB::table('account_types')
            ->orderBy('ac_index', 'desc')
            ->get();
        return view("admin.ib.ibCommission", [
            'ibCategories' => $ibCategories,
            'accountTypes' => $accountTypes,
        ]);
    }

    public function updateIbPlan(Request $request)
    {
        $ib_plan_id = $request->input('ib_plan_id');
        $acc_type = $request->input('acc_type');
        $status = $request->input('status');
        $levels = $request->input('level');
        $email = $request->session()->get('alogin');

        try {

            DB::beginTransaction();

            DB::table('ib_plan_details')
                ->where('ib_plan_id', $ib_plan_id)
                ->where('acc_type', $acc_type)
                ->update(['deleted_at' => now()]);

            foreach ($levels as $key => $divs) {
                $data = [
                    'ib_plan_id' => $ib_plan_id,
                    'acc_type' => $acc_type,
                    'level_id' => $key,
                    'updated_by' => $email,
                ];

                foreach ($divs as $d => $val) {
                    $data[$d] = $val;
                }
                DB::table('ib_plan_details')->insert($data);
            }

            DB::commit();

            alert()->success("IB Plan Successfully Updated");
            return redirect("/admin/ib_settings");
        } catch (Exception $e) {

            DB::rollBack();
            alert()->error("Failed to update IB Plan", "Please try again or Contact Support.");
            return redirect("/admin/ib_settings");
        }
    }

    public function ibCommissionEdit($planId,$accType,Request $request)
    {
        // Retrieve selected IB plan details
        $selected = DB::table('ib_plan_details')
            ->join('account_types', 'account_types.ac_index', '=', 'ib_plan_details.acc_type')
            ->join('ib_categories', 'ib_categories.ib_cat_id', '=', 'ib_plan_details.ib_plan_id')
            ->whereNull('ib_plan_details.deleted_at')
            ->where(DB::raw('ib_plan_details.ib_plan_id'), $planId)
            ->where(DB::raw('ib_plan_details.acc_type'), $accType)
            ->select('ib_plan_details.*', 'account_types.ac_group', 'ib_categories.ib_cat_name', DB::raw('count(*) as count'))
            ->groupBy('ib_plan_details.ib_plan_id', 'ib_plan_details.acc_type')
            ->first();

        // dd($selected,$request->all());
        // If the form is submitted (for example, via POST request)
        if ($request->isMethod('post') && $request->has('action')) {
            $ibPlanId = $request->input('ib_plan_id');
            $accType = $request->input('acc_type');
            $level = $request->input('level');
            $email = $request->session()->get('alogin');

            // Update existing plan details (soft delete by setting `deleted_at`)
            DB::table('ib_plan_details')
                ->where('ib_plan_id', $ibPlanId)
                ->where('acc_type', $accType)
                ->update(['deleted_at' => now()]);

            // Insert new plan details
            foreach ($level as $key => $divs) {
                $data = [];
                foreach ($divs as $d => $val) {
                    $data[$d] = $val;
                }
                $data['ib_plan_id'] = $ibPlanId;
                $data['acc_type'] = $accType;
                $data['level_id'] = $key;
                $data['updated_by'] = $email;

                // Insert new record into ib_plan_details
                DB::table('ib_plan_details')->insert($data);
            }

            // Return a success message using SweetAlert or any preferred method
            return redirect("/admin/ib_settings")->with('success', 'IB Plan Successfully Updated');
        }

        // Retrieve all IB Categories
        $ibCategories = DB::table('ib_categories')->get();

        // Retrieve all Account Types ordered by `ac_index` in descending order
        $accountTypes = DB::table('account_types')
            ->orderBy('ac_index', 'desc')
            ->get();
        // Return data to the view
        return view('admin.ib.ibCommissionEdit', [
            'selected' => $selected,
            'ibCategories' => $ibCategories,
            'groups' => $accountTypes,
            'planId' => $planId,
            'accType' => $accType,
        ]);
    }
}
