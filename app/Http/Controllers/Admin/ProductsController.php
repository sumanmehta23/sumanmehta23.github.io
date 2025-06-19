<?php

namespace App\Http\Controllers\Admin;

use App\Models\AccountType;
use Illuminate\Http\Request;
use App\Models\MT5GroupCategory;
use App\Enums\ProductStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Mt5Group;
use App\Models\PlatformGroup;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productCategories = MT5GroupCategory::all();
        $products = AccountType::all();
        $groups = PlatformGroup::pluck('name', 'id');
        $activeCategory = request()->get('activeCategory');
        return view('admin.products.index', compact('products', 'productCategories', 'activeCategory', 'groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'groupCreation' => ['required'],
            'ac_type' => ['required', 'integer'],
            'ac_name' => ['required', 'string'],
            'ac_category' => ['required', 'integer'],
            'ac_book_type' => ['required', 'integer'],
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
        ]);

        $acc_group = Mt5Group::where('mt5_group_id',$validatedData['ac_type'])->first();

        $lastIndex = AccountType::max('ac_index');
        $ac_index = $lastIndex ? $lastIndex + 1 : 1;
        // dd($request->all());
        AccountType::create([
            // 'id' => $validatedData[''],
            'ac_index' => $ac_index,
            'ac_category' => $validatedData['ac_category'],
            'ac_book_type' => $validatedData['ac_book_type'],
            'ac_name' => $validatedData['ac_name'],
            'ac_min_deposit' => $validatedData['ac_min_deposit'],
            // 'ac_max_deposit' => $validatedData['ac_type'],
            'ac_max_leverage' => $validatedData['ac_max_leverage'],
            // 'ac_lot_size' => $validatedData['ac_type'],
            'ac_group' => $validatedData['ac_group'],
            'ac_spread' => $validatedData['ac_spread'],
            'mt5_group_id' => $acc_group->id,
            'ib_enabled' => $validatedData['ib_enabled'],
            'ac_swap' => $validatedData['ac_swap'],
            'is_client_group' => $validatedData['is_client_group'],
            'inquiry_status' => $validatedData['inquiry_status'],
            'status' => $validatedData['status'],
            'display_priority' => $validatedData['display_priority'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return response()->json('true');
    }

    /**
     * Display the specified resource.
     */
    public function show(AccountType $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccountType $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccountType $product)
    {
        dd($request->all());
        $validatedData = $request->validated();
        $validatedData['leverages'] = explode(',', $validatedData['leverages']);
        $validatedData['status'] = ProductStatusEnum::from($validatedData['status']);
        $product->update($validatedData);
        return response()->json('true');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountType $product)
    {
        //
    }
}
