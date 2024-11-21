<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IbPlanDetails;
use Illuminate\Http\Request;
use App\Models\Leverage;
use App\Models\Ib1;
use App\Models\Mt5GroupCategory;
use App\Models\Mt5Group;
use App\Models\IBCategory;
use App\Models\AccountType;
use App\Models\User;
use DB;
use Exception;

// use Illuminate\Support\Facades\Hash;

class ApiAjaxController extends Controller
{
    public function handleRequest(Request $request)
    {
        if ($request->has('type') && $request->type == 'leverage') {
            $leverage = Leverage::where('account_type_id', $request->id)->get();
            return response()->json($leverage);
        }

        if ($request->has('client_id')) {
            return $this->handleClientRequest($request);
        }

        if ($request->has('get_groupcat') && $request->has('id')) {
            $groupCat = Mt5GroupCategory::where(DB::raw('mt5_grp_cat_id'), $request->id)->first();
            return $groupCat ? response()->json($groupCat) : response()->json(false);
        }

        if ($request->has('get_groupMains') && $request->has('id')) {
            $groupMain = Mt5Group::where(DB::raw('mt5_group_id'), $request->id)->first();
            return $groupMain ? response()->json($groupMain) : response()->json(false);
        }

        if ($request->has('get_ibplan') && $request->has('id')) {
            $ibPlan = IbCategory::where(DB::raw('ib_cat_id'), $request->id)->first();
            return $ibPlan ? response()->json($ibPlan) : response()->json(false);
        }

        if ($request->has('group_update') && $request->id) {
            $updated = Mt5GroupCategory::where(DB::raw('mt5_grp_cat_id'), $request->id)
                ->update([
                    'mt5_grp_cat_name' => $request->mt5_grp_cat_name,
                    'mt5_grp_cat_desc' => $request->mt5_grp_cat_desc,
                    'is_active' => $request->is_active
                ]);
            return response()->json($updated ? 'true' : 'false');
        }

        if ($request->has('ib_plan_update')) {
            return $request->id ? $this->updateIbPlan($request) : $this->createIbPlan($request);
        }

        if ($request->has('groupMain_update')) {
            return $request->id ? $this->updateGroupMain($request) : $this->createGroupMain($request);
        }

        if ($request->has('group_update')) {
            return $this->createGroupCategory($request);
        }

        if ($request->has('groupCreation')) {
            return $this->createGroup($request);
        }

        if ($request->has('groupUpdation')) {
            return $this->updateGroup($request);
        }

        if ($request->has('ibPlanUpdate')) {
            return $this->updateIbPlanData($request);
        }

        return response()->json('false');
    }

    private function handleClientRequest($request)
    {
        $clientId = $request->client_id;
        $ibStatus = $request->ib_status;
        $ibGroup = $request->ib_group;

        $ib = Ib1::where(DB::raw('email'), $clientId)->first();

        if (!$ib) {
            $user = User::where(DB::raw('email'), $clientId)->first();
            if ($user) {
                $ib = new Ib1();
                $ib->uid = $user->uid;
                $ib->email = $user->email;
                $ib->password = $user->password;
                $ib->number = $user->number;
                $ib->username = $user->email;
                $ib->name = $user->fullname;
                $ib->country = $user->country;
                $ib->emailToken = $user->emailToken;
                $ib->status = 1;
                $ib->save();
            }
        }

        $ibUpdate = Ib1::where(DB::raw('email'), $clientId)
            ->update(['status' => $ibStatus, 'acc_type' => $ibGroup]);

        return response()->json($ibUpdate ? 'true' : 'false');
    }

    private function createIbPlan($request)
    {
        $ibPlan = IBCategory::create([
            'ib_cat_name' => $request->ib_cat_name,
            'ib_cat_desc' => $request->ib_cat_desc,
            'is_active' => $request->is_active
        ]);

        return response()->json($ibPlan ? 'true' : 'false');
    }

    private function updateGroupMain($request)
    {
        $updated = Mt5Group::where(DB::raw('mt5_group_id'), $request->id)
            ->update([
                'mt5_group_name' => $request->mt5_group_name,
                'mt5_group_desc' => $request->mt5_group_desc,
                'is_active' => $request->is_active,
                'updated_by' => session('alogin')
            ]);

        return response()->json($updated ? 'true' : 'false');
    }

    private function createGroupMain($request)
    {
        $group = Mt5Group::create([
            'mt5_group_name' => $request->mt5_group_name,
            'mt5_group_type' => $request->mt5_group_type,
            'mt5_group_desc' => $request->mt5_group_desc,
            'is_active' => $request->is_active,
            'updated_by' => session('alogin')
        ]);

        return response()->json($group ? 'true' : 'false');
    }

    private function createGroup($request)
    {
        try {
            DB::beginTransaction();

            $userGroup = DB::table('mt5_groups')
                ->where('mt5_group_id', $request->input('ac_type'))
                ->select('user_group_id')
                ->first();
            if (!$userGroup) {
                return response()->json(['error' => 'User group not found'], 400);
            }
            $ac_group = str_replace('\\', '\\\\', $request->input('ac_group'));
            $is_exist = DB::table('account_types')
                ->where('ac_group', $ac_group)
                ->exists();
            if ($is_exist) {
                return response()->json(['error' => 'Group name already exists.'], 409);
            }
            $newGroup = $this->api->GroupCreate();
            $newGroup->Group = $ac_group;
            $newGroup->Commissions = 0;
            $newGroup->Symbols = "";
            $newGroup->Company = settings()['mt5_company_name'];
            $newGroup->Server = 1;
            $newGroup->MarginMode = 2;
            $newGroup->LimitPositions = 0;

            $error_code = $this->api->GroupAdd($newGroup, $new_group);
            if ($error_code != MTRetCode::MT_RET_OK) {
                return response()->json([
                    'error' => "Something went wrong. Please try again later. Code: $error_code [" . MTRetCode::GetError($error_code) . "]"
                ], 500);
            }
            $accountType = new AccountType();
            $accountType->ac_type = $request->ac_type;
            $accountType->ac_name = $request->ac_name;
            $accountType->ac_group = $request->ac_group;
            $accountType->ac_min_deposit = $request->ac_min_deposit;
            $accountType->ac_max_leverage = $request->ac_max_leverage;
            $accountType->ac_spread = $request->ac_spread;
            $accountType->ac_swap = $request->ac_swap;
            $accountType->status = $request->status;
            $accountType->ib_enabled = $request->ib_enabled;
            $accountType->ac_category = $request->ac_category;
            $accountType->ac_book_type = $request->ac_book_type;
            $accountType->is_client_group = $request->is_client_group;
            $accountType->inquiry_status = $request->inquiry_status;
            $accountType->display_priority = $request->display_priority ?? 0;
            $accountType->save();

            foreach (explode(",", $request->ac_max_leverage) as $lev) {
                Leverage::create([
                    'account_type_id' => $accountType->ac_index,
                    'account_leverage' => $lev
                ]);
            }

            DB::commit();
            return response()->json('true');
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(["status" => 'false', "message" => $e->getMessage()]);
        }
    }

    private function updateGroup($request)
    {
        try {
            $accountType = AccountType::where(DB::raw("(ac_index)"), $request->ac_index)->first();
            if ($accountType) {
                $accountType->ac_name = $request->ac_name;
                $accountType->ac_min_deposit = $request->ac_min_deposit;
                $accountType->ac_max_leverage = $request->ac_max_leverage;
                $accountType->ac_swap = $request->ac_swap;
                $accountType->is_client_group = $request->is_client_group;
                $accountType->status = $request->status;
                $accountType->ib_enabled = $request->ib_enabled;
                $accountType->inquiry_status = $request->inquiry_status;
                $accountType->display_priority = $request->display_priority ?? 0;
                $accountType->save();

                Leverage::where('account_type_id', $accountType->ac_index)->delete();

                foreach (explode(",", $request->ac_max_leverage) as $lev) {
                    Leverage::create([
                        'account_type_id' => $accountType->ac_index,
                        'account_leverage' => $lev
                    ]);
                }

                return response()->json('true');
            }
            return response()->json(["status" => 'false', "message" => "Group is not Exist"]);
        } catch (Exception $e) {
            return response()->json(["status" => 'false', "message" => $e->getMessage()]);
        }
    }

    private function updateIbPlanData($request)
    {
        try {
            DB::beginTransaction();

            IbPlanDetails::where('ib_plan_cat_id', $request->ib_plan_cat_id)
                ->where('ib_acc_type_id', $request->ib_acc_type_id)
                ->update(['status' => 0, 'updated_by' => session('alogin'), 'deleted_at' => now()]);

            IbPlanDetails::create([
                'ib_acc_type_id' => $request->ib_acc_type_id,
                'ib_plan_cat_id' => $request->ib_plan_cat_id,
                'ib_plan_code' => $request->ib_plan_code,
                'ib_plan_amount' => $request->ib_plan_amount,
                'ib_plan_type' => $request->ib_plan_type,
                'ib_plan_desc' => $request->ib_plan_desc,
                'updated_by' => session('alogin')
            ]);

            DB::commit();
            return response()->json('true');
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json('false');
        }
    }
}
