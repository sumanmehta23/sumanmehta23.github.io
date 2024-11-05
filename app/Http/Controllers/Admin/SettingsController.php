<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\EmployeeList;

class SettingsController extends Controller
{
    public function index()
    {
        return view("admin.ui_settings");
    }

    public function store(Request $request)
    {
        $req = $request->except(["_token", "update"]);
        foreach ($request->file() as $key => $file) {
            if ($request->hasFile($key)) {
                $file = $request->file($key); // Retrieve the uploaded file from the request
                $filename = time() . '_' . $file->getClientOriginalName(); // Retrieve the original filename
                Storage::disk('local')->put('public/files/' . $filename, file_get_contents($file));
                $file_path = "storage/files/" . $filename;
                $req[$key] = $file_path;
            }
        }

        foreach ($req as $key => $value) {
            Setting::where("name", $key)->update(["value" => $value]);
        }
        alert()->success("Settings Successfully Updated");
        return redirect()->back();
    }
    public function update_password()
    {
        return view("admin.update_password");
    }
    public function store_password(Request $request)
    {
        $request->validate([
            'oldpassword' => 'required',
            'newpassword' => 'required|confirmed',
        ]);
        $user = EmployeeList::where('email', session('alogin'))->first();
        if ($request->oldpassword!=$user->password){
            return redirect()->back()->with('error','Old password you entered is invalid');
        }
        $user->password = $request->newpassword;
        $user->save();
        return redirect()->back()->with('success','Password Updated Successfully');
    }

}
