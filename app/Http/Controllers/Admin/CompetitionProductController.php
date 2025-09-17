<?php

namespace App\Http\Controllers\Admin;

use App\Models\Leverage;
use App\Models\Mt5Group;
use App\Models\AccountType;
use Illuminate\Http\Request;
use App\Models\PlatformGroup;
use App\Models\MT5GroupCategory;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class CompetitionProductController extends Controller
{
    public function index()
    {
        // $productCategories = MT5GroupCategory::all();
        // $products = AccountType::all();
        // $groups = PlatformGroup::pluck('name', 'id');
        // $activeCategory = request()->get('activeCategory');
        // return view('admin.products.index', compact('products', 'productCategories', 'activeCategory', 'groups'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'groupCreation' => ['required'],
                'ac_type' => ['required', 'integer'],
                'ac_name' => ['required', 'string'],
                // 'ac_category' => ['required', 'integer'],
                // 'ac_book_type' => ['required', 'integer'],
                'ac_group' => ['required'],
                'ac_min_deposit' => ['required'],
                'ac_max_leverage' => ['required'],
                'ac_spread' => ['required'],
                'ac_swap' => ['required'],
                'is_client_group' => ['required'],
                'inquiry_status' => ['required'],
                'status' => ['required'],
                'ib_enabled' => ['required'],
                'display_priority' => ['required'],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date'],
                'prize_pool' => ['required'],
            ]);


            $acc_group = Mt5Group::where('mt5_group_id', $validatedData['ac_type'])->firstOrFail();

            $ac_index = AccountType::max('ac_index') + 1;

            $prizes = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validatedData['prize_pool'])));
            $prizeHtml = '<ul><li>' . implode('</li><li>', $prizes) . '</li></ul>';

            $accountType = AccountType::create([
                'ac_index' => $ac_index,
                // 'ac_category' => $validatedData['ac_category'],
                // 'ac_book_type' => $validatedData['ac_book_type'],
                'ac_name' => $validatedData['ac_name'],
                'ac_min_deposit' => $validatedData['ac_min_deposit'],
                'ac_max_leverage' => $validatedData['ac_max_leverage'],
                'ac_group' => $validatedData['ac_group'],
                'ac_spread' => $validatedData['ac_spread'],
                'mt5_group_id' => $acc_group->id,
                'ib_enabled' => $validatedData['ib_enabled'],
                'ac_swap' => $validatedData['ac_swap'],
                'is_client_group' => $validatedData['is_client_group'],
                'inquiry_status' => $validatedData['inquiry_status'],
                'status' => $validatedData['status'],
                'display_priority' => $validatedData['display_priority'],
                'competition_start_date' => $validatedData['start_date'],
                'competition_end_date' => $validatedData['end_date'],
                'prize' => $prizeHtml,
            ]);

            $leverages = array_map('intval', explode(',', $validatedData['ac_max_leverage']));

            foreach ($leverages as $key => $leverage) {
                Leverage::create([
                    'account_type_id' => $accountType->id,
                    'account_leverage' => $leverage,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return redirect()->route('admin.dashboard')->with('success', 'Group Created Successfully');

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'ac_index' => ['required', 'integer'],
                'groupUpdation' => ['required'],
                'ac_name' => ['required', 'string'],
                'ac_group' => ['required'],
                'ac_min_deposit' => ['required'],
                'ac_max_leverage' => ['required'],
                'ac_swap' => ['required'],
                'is_client_group' => ['required'],
                'inquiry_status' => ['required'],
                'status' => ['required'],
                'ib_enabled' => ['required'],
                'display_priority' => ['required'],
                'competition_start_date' => ['required', 'date'],
                'competition_end_date' => ['required', 'date'],
                'prize_pool' => ['required'],
            ]);

            $acc_type = AccountType::where('ac_index', $request->ac_index)->first();

            $now = Carbon::now();

            $prizes = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validatedData['prize_pool'])));
            $prizeHtml = '<ul><li>' . implode('</li><li>', $prizes) . '</li></ul>';

            $updateData = [
                'ac_name' => $validatedData['ac_name'],
                'ac_min_deposit' => $validatedData['ac_min_deposit'],
                'ac_max_leverage' => $validatedData['ac_max_leverage'],
                'ac_group' => $validatedData['ac_group'],
                'ib_enabled' => $validatedData['ib_enabled'],
                'ac_swap' => $validatedData['ac_swap'],
                'is_client_group' => $validatedData['is_client_group'],
                'inquiry_status' => $validatedData['inquiry_status'],
                'status' => $validatedData['status'],
                'display_priority' => $validatedData['display_priority'],
                'prize' => $prizeHtml,
            ];


            // Update competition_start_date only if old date is in the past
            if ($acc_type->competition_start_date >= $now  && $acc_type->competition_start_date >= $now) {
                $updateData['competition_start_date'] = $validatedData['competition_start_date'];
                $updateData['competition_end_date'] = $validatedData['competition_end_date'];
            } else if ($acc_type->competition_start_date <= $now && $acc_type->competition_end_date >= $now) {
                $updateData['competition_start_date'] = $acc_type->competition_start_date;
                $updateData['competition_end_date'] = $validatedData['competition_end_date'];
            }elseif($acc_type->competition_end_date <= $now && $acc_type->competition_start_date <= $now) {
                return response()->json([
                    'success' => false,
                    'message' => 'Competition already ended. You cannot change date.'
                ], 400);
            }

            $acc_type->update($updateData);

            // Delete existing leverage records for this account type
            Leverage::where('account_type_id', $acc_type->id)->delete();

            // Insert new leverage records
            $leverages = array_map('intval', explode(',', $validatedData['ac_max_leverage']));
            foreach ($leverages as $leverage) {
                Leverage::create([
                    'account_type_id' => $acc_type->id,
                    'account_leverage' => $leverage,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Group Updated Successfully'
            ]);
            // return redirect()->back()->with('success', 'Group Updated Successfully');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
