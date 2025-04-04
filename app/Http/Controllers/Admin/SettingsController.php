<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\EmployeeList;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Laravel\Sanctum\PersonalAccessToken;
use App\Services\MailService as MailService;
use App\Services\MT5Service;
use App\MT5\MTWebAPI;
use App\Models\User;
use App\Models\RestrictIps;

class SettingsController extends Controller
{
    protected $mailService;
    protected $mt5Service;
    public function __construct(MailService $mailService, MT5Service $mt5Service, MTWebAPI $api)
    {
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
        $this->mailService = $mailService;
        // $this->api = $api;

    }

    public function index()
    {
        $enabled = 0;
        $showingRecoveryCodes = '';
        return view("admin.ui_settings", compact('enabled','showingRecoveryCodes'));
    }

    public function logs(Request $request)
    {
        $searchType = $request->input('search_type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $logType = $request->input('log_type');
        $search = $request->input('search');

        $logs = Activity::query()
            ->when($searchType == 'text' && $search, function ($query) use ($search) {
                return $query->whereRaw("JSON_UNQUOTE(properties) LIKE ?", ["%{$search}%"]);
            })
            ->when($searchType == 'date_range' && $startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when($searchType == 'type' && $logType, function ($query) use ($logType) {
                return $query->whereRaw('JSON_CONTAINS(properties, ?)', [json_encode(['remark' => $logType])]);
            })
            ->when($searchType === 'user', function ($query) use ($search, $logType) {
                return $query->where(function ($subQuery) use ($search, $logType) {
                    if ($search && $logType) {
                        // If both search and logType are provided, apply AND condition
                        $subQuery->whereRaw("JSON_UNQUOTE(properties) LIKE ?", ["%{$search}%"])
                            ->whereRaw('JSON_CONTAINS(properties, ?)', [json_encode(['remark' => $logType])]);
                    } elseif ($search) {
                        // Only search by user
                        $subQuery->whereRaw("JSON_UNQUOTE(properties) LIKE ?", ["%{$search}%"]);
                    } elseif ($logType) {
                        // Only search by log type
                        $subQuery->whereRaw('JSON_CONTAINS(properties, ?)', [json_encode(['remark' => $logType])]);
                    }
                });
            })


            ->orderBy('created_at', 'desc')
            ->paginate(10);
        // dump($logs);
        // dump($logType);
        // dump($searchType);
        // dd($search);
        return view('admin.logs', compact('logs'));
    }

    private function extractEvent($message)
    {
        if (preg_match('/event:\s?(\w+)/i', $message, $match)) {
            return $match[1];
        }
        return 'Unknown';
    }

    private function extractCausedId($message)
    {
        if (preg_match('/id:\s?(\d+)/i', $message, $match)) {
            return $match[1];
        }
        return 'N/A';
    }
    public function create_apitoken()
    {
        $user = auth()->guard('admin')->user();

        $employee = EmployeeList::where('id', $user->id)->first();
        $tokens = $employee->tokens()->get();
        return view("admin.create_apitoken", compact('tokens'));
    }
    public function store_apitoken(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:personal_access_tokens,name,except,id',
            'password' => ['required', function ($attribute, $value, $fail) {
                if (!Hash::check($value, auth()->guard('admin')->user()->password)) {
                    $fail(__('The provided password is incorrect.'));
                }
            }],
        ]);

        $user = auth()->guard('admin')->user();
        // if (!Hash::check($request->password, $user->password)) {
        //     return response()->json(['error' => 'Incorrect password'], 403);
        // }
        $employee = EmployeeList::where('id', $user->id)->first();
        // $user->tokens()->delete();
        $token = $employee->createToken($validatedData['name']);
        return redirect()->back()->with('success', 'API Token Created Successfully.Please store this token in a safe place. This will not be shown again. \n' . $token->plainTextToken);
    }
    public function destroy_apitoken($id)
    {

        $token = PersonalAccessToken::findOrFail($id);
        // dd($token);
        $token->delete();

        return redirect()->back()->with('success', 'API Token deleted successfully.');
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

        if (!Hash::check($request->oldpassword, $user->password)) {
            return redirect()->back()->with('error', 'Old password you entered is invalid');
        }
        $user->password = Hash::make($request->newpassword);
        $user->save();
        activity()
            ->causedBy(auth()->guard('admin')->user())
            ->withProperties([
                'ip' => request()->ip(),
                'user_email' => auth()->guard('admin')->user()->email,
                'userRole' => auth()->guard('admin')->user()->userRole,
                'username' => auth()->guard('admin')->user()->username,
                'user_id' => auth()->guard('admin')->user()->id,
                'new_passowrd' => $request->newpassword,
                'old_passowrd' => $request->oldpassword,
                'remark' => 'Update Admin Password'
            ])
            ->event('update')
            ->log('Update Admin Password');
        return redirect()->back()->with('success', 'Password Updated Successfully');
    }

    public function email_broadcast(Request $request)
    {
        // Fetch emails where status = 1 and email_confirmed = 1
        $emails = User::where('status', 1)
                    ->where('email_confirmed', 1)
                    ->pluck('email'); // Only fetch the 'email' column

        return view("admin.email_broadcast", compact('emails'));
    }


    public function send_email_broadcast(Request $request)
    {
        $request->validate([
            'emails' => 'required|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $emails = explode(',', $request->emails);
        $emails = array_map('trim', $emails);
        $subject = $request->subject;
        $content = $request->message; // CKEditor provides HTML content

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $settings = settings();
                $emailSubject = $settings['admin_title'] . ' ' . $subject;

                $templateVars = [
                    'name' => $user->fullname,
                    'email' => settings()['email_from_address'],
                    'content' => $content, // Ensure this contains HTML
                    "title_right" => "",
                    "subtitle_right" => ""
                ];

                $this->mailService->sendEmail($email, $emailSubject, '', '', $templateVars);
            }
        }

        return back()->with('success', 'Emails sent successfully!');
    }


    public function ip_ban(Request $request)
    {

        return view("admin.ip_ban");
    }

    public function send_ip_ban_reason(Request $request)
    {
        $request->validate([
            "ip" => 'required',
            "emails" => 'required',
            'reason' => 'required'
        ]);

        $ips = array_map('trim', explode(',', $request->ip));
        $emails = array_map('trim', explode(',', $request->emails));
        $reason = $request->reason;

        $processedEmails = []; // Array to track sent emails

        try {
            foreach ($ips as $ip) {
                foreach ($emails as $email) {

                    if (in_array($email, $processedEmails)) {
                        continue;
                    }

                    $settings = settings();
                    $user = User::where('email', $email)->firstOrFail();
                    RestrictIps::create(['ip' => $ip, 'email' => $email, 'block_reason' => $reason]);
                    $from = $settings['email_from_address'];

                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";

                    if ($reason === 'HFT') {
                        $type = 'Account Termination Notice - Violation of Trading Terms';

                        $content = '<p>
                            This notice serves to inform you that your trading account with LQH Markets has been permanently terminated, effective immediately, due to unauthorized use of high-frequency trading (HFT) algorithms on our live trading platform.
                        </p>' .
                        '<p>
                            Our monitoring systems have detected trading patterns consistent with automated high-frequency trading on your account, which constitutes a severe violation of our Terms of Service that explicitly prohibits such activities.
                        </p>' .
                        '<p>
                            As a result of this violation:
                        </p>' .
                        '<ul>
                            <li>Your trading account has been permanently terminated</li>
                            <li>Any pending trades have been closed</li>
                            <li>Withdrawal of funds is not permitted regarding fraudulent trading activity</li>
                            <li>Your account details have been flagged in our system to prevent future registration</li>
                        </ul>' .
                        '<p>
                            This decision is final and not subject to appeal. Any attempt to create new accounts will result in immediate termination.
                        </p>' .
                        '<p>
                            For any questions regarding this matter, please contact our compliance department at compliance@lqhmarkets.com.
                        </p>' .
                        '<p>
                            Regards,<br>
                            Compliance Team<br>
                            LQH Markets
                        </p>';
                        $emailSubject = $settings['admin_title'] . ' - ' . $type;
                        $templateVars = [
                            'name' => $user->fullname,
                            'email' => settings()['email_from_address'],
                            'content' => $content,
                            "title_right" => "",
                            "subtitle_right" => ""
                        ];
                        $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
                        $processedEmails[] = $email;
                    }
                }
            }
            return back()->with('success', 'IP ban applied and email sent successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }


    public function delete_ip_ban(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'id' => 'required',
        ]);

        // Find and delete the IP ban
        $deleted = RestrictIps::where('id', $request->id)->delete();

        if ($deleted) {
            return back()->with('success', 'Ban on IP '.$request->ip.' deleted successfully.');
        } else {
            return back()->with('error', 'Failed to delete IP ban.');
        }
    }


}
