<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KycUpdate;

class Kyc extends Controller
{
    public function updateKyc(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|integer',
            'email' => 'required|email',
            'description' => 'required|string',
            'status' => 'required|string',
        ]);
        $kyc = KycUpdate::find($validatedData['id']);
        if (!$kyc) {
            return response()->json(['message' => 'KYC record not found'], 404);
        }
        $kyc->Admin_Remark = $validatedData['description'];
        $kyc->Status = $validatedData['status'];
        $kyc->approved_by = session('userData')['client_index'];
        $kyc->save();
        return redirect()->back()->with("success","KYC Status Updated Successfully");
    }
}
