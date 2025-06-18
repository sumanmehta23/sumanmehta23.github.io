<?php

namespace App\Http\Controllers\Admin;

use App\Models\AccountType;
use Illuminate\Http\Request;
use App\Models\MT5GroupCategory;
use App\Enums\ProductStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
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
        dd($request->all());

        AccountType::create($validatedData);
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
