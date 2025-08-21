<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\Ib1;
use App\Models\IbCategory;
use App\Models\AccountType;
use Illuminate\Http\Request;
use App\Models\IbPlanDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IbUsersExport;
use App\Jobs\ExportIbUsersJob;
use Illuminate\Support\Facades\Auth;

class IBController extends Controller
{

    public function index(Request $request)
    {
        $userRole = $request->session()->get('userData.userRole');
        $alogin = $request->session()->get('userData.id');
        // dump($userRole);
        // dd($alogin);

        $rmCondition = DB::table('ib1 as ib')
            ->leftJoin('aspnetusers as user', 'user.email', '=', 'ib.email');

        if ($userRole == "Relationship Manager") {
            $rmCondition->leftJoin('relationship_manager as rm', 'ib.user_id', '=', 'rm.user_id')
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
            $totalClients->leftJoin('relationship_manager as rm', 'ib.user_id', '=', 'rm.user_id')
                ->where('rm.rm_id', $alogin);
        }

        $totalClientsCount = $totalClients->count('ib.email');


        $pendingKyc = DB::table('ib1 as ib')
            ->join('kyc_update as kyc', 'ib.email', '=', 'kyc.email')
            ->leftJoin('aspnetusers as user', 'user.email', '=', 'ib.email');

            if ($userRole == "Relationship Manager") {
            $pendingKyc->leftJoin('relationship_manager as rm', 'ib.user_id', '=', 'rm.user_id')
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
            $rmCondition->leftJoin('relationship_manager as rm', 'ib1.user_id', '=', 'rm.user_id')
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
            ->select('ib_categories.ib_cat_name', 'ib_plan_details.id', 'ib_plan_details.ib_category_id')
            ->leftJoin('ib_categories', 'ib_categories.id', '=', 'ib_plan_details.ib_category_id')
            ->where(['ib_plan_details.status'=> 1,'ib_plan_details.deleted_at'=> null])
            ->groupBy('ib_plan_details.ib_category_id')
            ->get(); // Use get() to retrieve results
        return view("admin.ib.iblist", ["acc_groups" => $accGroups]);
    }
    public function list_active()
    {
        $accGroups = DB::table('ib_plan_details')
            ->select('ib_categories.ib_cat_name', 'ib_plan_details.id', 'ib_plan_details.ib_category_id')
            ->leftJoin('ib_categories', 'ib_categories.id', '=', 'ib_plan_details.ib_category_id')
            ->where(['ib_plan_details.status'=> 1,'ib_plan_details.deleted_at'=> null])
            ->groupBy('ib_plan_details.ib_category_id')
            ->get(); // Use get() to retrieve results
        return view("admin.ib.iblist_active", ["acc_groups" => $accGroups]);
    }

    public function ib_settings()
    {
        // Get activeType if it's set
        $activeType = request()->get('activeType');

        // Step 1: Query for categories with counts of distinct account types
        $results = IbCategory::with('plans')
            ->get();


        // Step 2: Query for ib_plan_details with account types and category names
        $plans = IbPlanDetails::select(
                'ib_plan_details.*',
            )
            ->with(['accountType','plan']);

        // Apply the filter if `activeType` is not null
        if ($activeType !== null) {
            $plans->whereHas('plan', function ($query) use ($activeType) {
                $query->where('id', $activeType);
            });
        }

        // Group and execute the query for plans
        $plans = $plans
            // ->groupBy('created_at')
            ->get();

        // Step 3: Query for all account types
        $groups =AccountType::get();
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

        $ib_category_id = $request->input('ib_category_id');
        $acc_types = $request->input('acc_type');
        $status = $request->input('status');
        $levels = $request->input('level');
        $email = $request->session()->get('alogin');
        try {

            foreach($acc_types as $acc_type){
                DB::beginTransaction();
                $IbPlanDetails = IbPlanDetails::where('ib_category_id', $ib_category_id)
                ->where('account_type_id', $acc_type)->first();

                if($IbPlanDetails){
                    $IbPlanDetails->update(['deleted_at' => now()]);
                }

                foreach ($levels as $key => $divs) {
                    $data = [
                        'ib_category_id' => $ib_category_id,
                        'account_type_id' => $acc_type,
                        'level_id' => $key,
                        'updated_by' => $email,
                    ];

                    foreach ($divs as $d => $val) {
                        $data[$d] = $val;
                    }

                IbPlanDetails::create($data);
                }
                DB::commit();

                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'user_email' => auth()->guard('admin')->user()->email,
                        'userRole' =>auth()->guard('admin')->user()->userRole,
                        'username' =>auth()->guard('admin')->user()->username,
                        'user_id' =>auth()->guard('admin')->user()->id,
                        'ib_category_id' => $ib_category_id,
                        'acc_type' => $acc_type,
                        'status' => $status,
                        'levels' => count($levels),
                        'remark' => 'IB Commission Create'
                    ])
                ->event('create')
                ->log('IB Commission Create');
            }

            alert()->success("IB Plan Successfully Updated");
            return redirect("/admin/ib_settings");
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(),'trace' => $e->getTraceAsString()]);
            alert()->error("Failed to update IB Plan", "Please try again or Contact Support.");
            return redirect("/admin/ib_settings");
        }
    }

    public function ibCommissionEdit($planId,$accType,Request $request)
    {
        // Retrieve selected IB plan details
        $selected = DB::table('ib_plan_details')
            ->join('account_types', 'account_types.id', '=', 'ib_plan_details.account_type_id')
            ->join('ib_categories', 'ib_categories.id', '=', 'ib_plan_details.ib_category_id')
            ->whereNull('ib_plan_details.deleted_at')
            ->where(DB::raw('ib_plan_details.ib_category_id'), $planId)
            ->where(DB::raw('ib_plan_details.account_type_id'), $accType)
            ->select('ib_plan_details.*', 'account_types.ac_group', 'ib_categories.ib_cat_name', DB::raw('count(*) as count'))
            ->groupBy('ib_plan_details.ib_category_id', 'ib_plan_details.account_type_id')
            ->first();

        // dd($request->all());
        // If the form is submitted (for example, via POST request)
        if ($request->isMethod('post') && $request->has('action')) {
            $ibPlanId = $request->input('ib_category_id');
            $accType = $request->input('account_type_id');
            $level = $request->input('level');
            $email = $request->session()->get('alogin');

            // Update existing plan details (soft delete by setting `deleted_at`)
            // IbPlanDetails::where('ib_category_id', $ibPlanId)
            //     ->where('account_type_id', $accType)
            //     ->update(['deleted_at' => now()]);
            // dd($level);
            // Insert new plan details
            foreach ($level as $key => $divs) {
                // dd($divs);
                $data = [
                    'ib_category_id' => $ibPlanId,
                    'account_type_id' => $accType,
                    'level_id' => $key,
                    'updated_by' => $email,
                ];

                foreach ($divs as $d => $val) {
                    $data[$d] = $val;
                }
                // dump($ibPlanId);
                // dump($accType);
                // dd($key);
                $existingPlan = IbPlanDetails::where('ib_category_id', $ibPlanId)
                ->where('account_type_id', $accType)
                ->where('level_id', $key)
                ->first();
                // dd($existingPlan);
                if ($existingPlan) {
                    // Update the record if it exists
                    $existingPlan->update($data);
                } else {
                    // Create a new record if it does not exist
                    IbPlanDetails::create($data);
                }
            }

            activity()
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_email' => auth()->guard('admin')->user()->email,
                    'userRole' =>auth()->guard('admin')->user()->userRole,
                    'username' =>auth()->guard('admin')->user()->username,
                    'user_id' =>auth()->guard('admin')->user()->id,
                    'ib_category_id' => $ibPlanId,
                    'acc_type' => $accType,
                    'levels' => count($level),
                    'remark' => 'IB Commission Update'
                ])
            ->event('update')
            ->log('IB Commission Update');

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

    public function exportAllIbUsers(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'status' => 'nullable|integer|in:0,1,2',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'search' => 'nullable|string|max:255',
                'email' => 'required|email|max:255'
            ]);

            // Get current authenticated user
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Prepare filters
            $filters = array_filter([
                'status' => $request->input('status'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'search' => $request->input('search')
            ]);

            // Get export email
            $exportEmail = $request->input('email');

            // Generate unique filename
            $fileName = 'IB_List_' . date('Y-m-d_H-i-s') . '_' . $user->id . '.xlsx';

            // Dispatch the export job with email
            ExportIbUsersJob::dispatch($user, $filters, $fileName, $exportEmail);

            // Return immediate response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Export job has been queued successfully! We'll send notifications to {$exportEmail} when it's complete.",
                    'export_email' => $exportEmail,
                    'estimated_time' => 'This may take several minutes depending on the data size.'
                ]);
            }

            alert()->success('Export Started!', "Export job has been queued successfully! We'll send notifications to {$exportEmail} when it's complete.");
            return redirect()->back();

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            alert()->error('Validation Error', 'Please check your input and try again.');
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Export initiation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to start export. Please try again.'
                ], 500);
            }

            alert()->error('Export Failed', 'Failed to start export. Please try again or contact support.');
            return redirect()->back();
        }
    }

    /**
     * Download exported file with token verification
     */
    public function downloadExport(Request $request, $file, $token)
    {
        try {
            // Decrypt and validate token
            $tokenData = decrypt($token);
            
            // Check if token is expired
            if (now()->greaterThan($tokenData['expires_at'])) {
                abort(410, 'Download link has expired');
            }

            // Check if user matches (handle both User and EmployeeList models)
            $currentUserId = Auth::id();
            if ($tokenData['user_id'] !== $currentUserId) {
                abort(403, 'Unauthorized access');
            }

            // Check if file name matches
            if ($tokenData['file_name'] !== $file) {
                abort(404, 'File not found');
            }

            $filePath = 'exports/' . $file;

            // Check if file exists
            if (!Storage::disk('local')->exists($filePath)) {
                abort(404, 'Export file not found or has been deleted');
            }

            // Return file download
            return response()->download(
                Storage::disk('local')->path($filePath),
                $file,
                [
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0'
                ]
            );

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(403, 'Invalid download token');
        } catch (\Exception $e) {
            Log::error('Download failed', [
                'file' => $file,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            abort(500, 'Download failed');
        }
    }
}
