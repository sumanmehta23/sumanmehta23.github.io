<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ib1;
use App\Models\User;
use App\Models\Account;
use App\Models\Setting;
use App\Models\PopupImpression;
use App\Support\PopupCampaigns\ReviewPopupCampaign;
use App\Rules\ValidPassword;
use App\Models\RestrictIps;
use App\Models\ToggleGroup;
use App\Models\EmployeeList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendBroadcastEmailJob;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Services\UniversalMT5Service;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Response;
use Laravel\Sanctum\PersonalAccessToken;
use App\Services\MailService as MailService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    protected $mailService;
    protected $mt5Service;
    protected $api;
    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
        // MT5 connection deferred - use ensureMT5Connection() in methods that need it
    }

    private function ensureMT5Connection()
    {
        if (!$this->mt5Service) {
            $this->mt5Service = new UniversalMT5Service();
        }

        if (!$this->mt5Service->connect()) {
            Log::error('Failed to establish MT5 connection in SettingsController');
            return false;
        }

        $this->api = $this->mt5Service->getApi();
        return true;
    }

    public function index()
    {
        $enabled = 0;
        $showingRecoveryCodes = '';
        $toggle = ToggleGroup::first();
        return view("admin.ui_settings", compact('enabled', 'showingRecoveryCodes', 'toggle'));
    }

    public function reviewPopupSettings()
    {
        $campaign = new ReviewPopupCampaign(settings());
        $campaignKey = $campaign->key();

        $baseQuery = PopupImpression::query()->where('popup_key', $campaignKey);
        $usersSawPopup = (clone $baseQuery)->count();
        $usersClickedCta = (clone $baseQuery)->whereNotNull('cta_clicked_at')->count();
        $pureDismissals = (clone $baseQuery)
            ->whereNotNull('dismissed_at')
            ->whereNull('cta_clicked_at')
            ->count();
        $clickedThenDismissed = (clone $baseQuery)
            ->whereNotNull('dismissed_at')
            ->whereNotNull('cta_clicked_at')
            ->count();
        $usersDismissedPopup = (clone $baseQuery)->whereNotNull('dismissed_at')->count();

        $pct = static function (int $numerator, int $denominator): ?float {
            if ($denominator <= 0) {
                return null;
            }

            return round(($numerator / $denominator) * 100, 1);
        };

        $reviewPopupMetrics = [
            'campaign_key' => $campaignKey,
            'users_saw_popup' => $usersSawPopup,
            'users_dismissed_popup' => $usersDismissedPopup,
            'users_clicked_cta' => $usersClickedCta,
            'pure_dismissals' => $pureDismissals,
            'clicked_then_dismissed' => $clickedThenDismissed,
            'pure_dismissal_rate_pct' => $pct($pureDismissals, $usersSawPopup),
            'cta_click_rate_pct' => $pct($usersClickedCta, $usersSawPopup),
        ];

        return view('admin.review_popup_settings', compact('reviewPopupMetrics'));
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
            Setting::updateOrCreate(
                ['name' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }
        alert()->success("Settings Successfully Updated");
        return redirect()->back();
    }

    public function updateReviewPopupSettings(Request $request)
    {
        return $this->store($request);
    }
    public function update_password()
    {
        return view("admin.update_password");
    }
    public function store_password(Request $request)
    {
        $request->validate([
            'oldpassword' => 'required',
            'newpassword' => ['required', 'confirmed', new ValidPassword()],
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

        $subject = $request->subject;
        $content = $request->message;
        $settings = settings();

        // If user selected "Send to All Clients"
        if (strtolower(trim($request->emails)) === 'all') {

            // Dispatch queue job for all users
            dispatch(new SendBroadcastEmailJob(
                $subject,
                $content,
                $settings
            ));

            return back()->with('success', 'Broadcast email sent to all clients (queued)!');
        }

        // Otherwise manual email list
        $emails = explode(',', $request->emails);
        $emails = array_map('trim', $emails);

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            if (!$user) continue;

            // Clone content so placeholders do not override next user
            $personalContent = str_replace('{{name}}', $user->fullname, $content);

            $emailSubject = $settings['admin_title'] . ' ' . $subject;

            $templateVars = [
                'name'      => $user->fullname,
                'email'     => $settings['email_from_address'],
                'content'   => $personalContent,
                "title_right" => "",
                "subtitle_right" => ""
            ];

            $this->mailService->sendEmail($user->email, $emailSubject, '', '', $templateVars);
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

        try {
            foreach ($emails as $email) {
                $settings = settings();

                $user = User::where('email', $email)->firstOrFail();

                // Create RestrictIps entries for all IPs for this email
                foreach ($ips as $ip) {
                    RestrictIps::create(['ip' => $ip, 'email' => $email, 'block_reason' => $reason]);
                }

                $from = $settings['email_from_address'];

                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";

                if ($reason === 'HFT' || $reason === 'Latency Arbitrage') {
                    if ($reason === 'HFT') {
                        $reason_text = 'high-frequency trading (HFT)';
                    } elseif ($reason === 'Latency Arbitrage') {
                        $reason_text = 'Latency Arbitrage';
                    }

                    $type = 'Account Termination Notice - Violation of Trading Terms';

                    $content = '<p>
                        This notice serves to inform you that your trading account with LQH Markets has been permanently terminated, effective immediately, due to unauthorized use of ' . $reason_text . ' algorithms on our live trading platform.
                    </p>' .
                        '<p>
                        Our monitoring systems have detected trading patterns consistent with automated ' . $reason_text . ' on your account, which constitutes a severe violation of our Terms of Service that explicitly prohibits such activities.
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
                } elseif ($reason === 'General_Ban') {
                    $type = 'Account Review Notification';

                    $content = '<p>Following a review of trading activity on your account, we have identified patterns that constitute a breach of the Restricted Trading Activities section of our Terms and Conditions (<a href="https://www.lqhmarkets.com/terms-conditions">https://www.lqhmarkets.com/terms-conditions</a>).</p>' .

                        '<p>Our Terms prohibit activity that disrupts fair market operation, including manipulative tactics and high-frequency trading exploits. The activity identified on your account falls within these restrictions.</p>' .

                        '<p>Accordingly:</p>' .
                        '<p>• Trading on your account has been restricted with immediate effect<br>' .
                        '• Profits derived from the restricted activity have been removed<br>' .
                        '• Your original deposit(s), less any amounts previously withdrawn, will be returned to your active wallet within 10 business days</p>' .

                        '<p>This decision has been made following a documented review of trading data associated with your account.</p>' .

                        '<p>If you wish to request a review, please contact us at <a href="mailto:compliance@lqhmarkets.com">compliance@lqhmarkets.com</a>, and your case will be assessed by our Compliance Team.</p>' .

                        '<p>Kind regards,<br>LQH Markets Compliance Team</p>';
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
                } elseif ($reason === 'Manually') {
                    return back()->with('success', 'IP and Email ban applied successfully.');
                } else {
                    return back()->with('success', 'IP ban applied and email sent successfully.');
                }
            }
            return back()->with('success', 'IP ban applied and email sent successfully.');
        } catch (\Exception $e) {
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
            return back()->with('success', 'Ban on IP ' . $request->ip . ' deleted successfully.');
        } else {
            return back()->with('error', 'Failed to delete IP ban.');
        }
    }

    public function export(Request $request)
    {
        $searchType = $request->input('search_type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $logType = $request->input('log_type');
        $search = $request->input('search');

        $logsQuery = Activity::query()
            ->when($searchType === 'text' && $search, function ($query) use ($search) {
                return $query->whereRaw("JSON_UNQUOTE(properties) LIKE ?", ["%{$search}%"]);
            })
            ->when($searchType === 'date_range' && $startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when($searchType === 'type' && $logType, function ($query) use ($logType) {
                return $query->whereRaw('JSON_CONTAINS(properties, ?)', [json_encode(['remark' => $logType])]);
            })
            ->when($searchType === 'user', function ($query) use ($search, $logType) {
                return $query->where(function ($subQuery) use ($search, $logType) {
                    if ($search && $logType) {
                        $subQuery->whereRaw("JSON_UNQUOTE(properties) LIKE ?", ["%{$search}%"])
                            ->whereRaw('JSON_CONTAINS(properties, ?)', [json_encode(['remark' => $logType])]);
                    } elseif ($search) {
                        $subQuery->whereRaw("JSON_UNQUOTE(properties) LIKE ?", ["%{$search}%"]);
                    } elseif ($logType) {
                        $subQuery->whereRaw('JSON_CONTAINS(properties, ?)', [json_encode(['remark' => $logType])]);
                    }
                });
            })
            ->orderBy('created_at', 'desc');

        $filename = 'Activity_Logs_' . now()->format('Ymd_His') . '.csv';

        return new StreamedResponse(function () use ($logsQuery) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Time', 'IP', 'User', 'Description']);

            $logsQuery->chunk(2000, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    $user = null;
                    if ($log->causer_type == 'App\Models\EmployeeList') {
                        $user = EmployeeList::find($log->causer_id);
                    } else {
                        $user = User::find($log->causer_id);
                    }
                    $userLink = $user ? $user->email : 'Unknown';

                    $properties = is_array($log->properties) ? $log->properties : json_decode($log->properties, true);
                    $account = $properties['code'] ?? '';
                    $fromAccount_url = $properties['from'] ?? '';
                    $toAccount_url = $properties['to'] ?? '';
                    $ip = $properties['ip'] ?? '';
                    $formattedTime = $log->created_at->format('Y-m-d H:i:s');
                    $humanTime = $log->created_at->diffForHumans();

                    $remark = $properties['remark'] ?? '';
                    $logDescription = '';

                    // 🔄 All your switch cases here
                    switch ($remark) {
                        case 'Wallet Deposits':
                            $amount = $log->properties['payment_amount'];
                            $method = $log->properties['payment_type'];
                            $logDescription = "User {$userLink} deposited \${$amount} by using method {$method}.";
                            break;

                        case 'Login':
                            $logDescription = "User {$userLink} Logged in";
                            break;

                        case 'Logout':
                            $logDescription = "User {$userLink} Logged out.";
                            break;

                        case 'Incorrect login details':
                            $logDescription = "User {$userLink} entered wrong login details.";
                            break;
                        case 'Too many requests':
                            $logDescription = "Too many login requests for User {$userLink}.";
                            break;
                        case 'Invalid email or unverified account':
                            $logDescription = "User {$userLink} entered wrong email details";
                            break;
                        case 'Switch To User':
                            $logDescription = "User {$userLink} switched to {$log->properties['client_email']}";
                            break;
                        case 'Update Client Email':
                            $logDescription = "User {$userLink} entered wrong email details";
                            break;

                        case 'Create Demo Account':
                            $logDescription = "User {$userLink} created Demo account {$account} with amount {$log->properties['amount']} and leverage {$log->properties['leverage']}";
                            break;

                        case 'Create Live Account':

                            if ($log->properties['code'] == 'Pending') {
                                $logDescription = "User {$userLink} sent request for live account with leverage: {$log->properties['leverage']}";
                            } else {
                                $code = $log->properties['code'];
                                $account_data = Account::withTrashed()->where('code', $code)->first();
                                if (isset($account_data)) {
                                    $client = User::where('id', $account_data->user_id)->first();
                                    $logDescription = "Live account {$account} issued to user {$client->email} by {$user->email} with leverage {$log->properties['leverage']}";
                                }
                            }
                            break;
                        case 'Trade Withdraw':
                            $withdrawal_amount = $log->properties['withdraw_amount'];
                            // $transaction_id_link = "$log->properties['wallet_withdraw_id']";
                            $properties = json_decode($log->properties, true);
                            $transaction_id = $properties['wallet_withdraw_id'];

                            $logDescription = "User {$userLink} approve withdraw request of \${$withdrawal_amount} from account having transaction ID {$transaction_id}";
                            break;
                        case 'Approve Account Withdraw':
                            $withdrawal_amount = $log->properties['approved_amount'];
                            // $transaction_id_link = "$log->properties['wallet_withdraw_id']";
                            $properties = json_decode($log->properties, true);
                            $transaction_id = $properties['transaction_id'];

                            $logDescription = "User {$userLink} approve withdrawal request of \${$withdrawal_amount} from account having transaction ID {$transaction_id}";
                            break;
                        case 'Wallet Withdraw':
                            $withdrawal_amount = $log->properties['withdraw_amount'] + $log->properties['withdraw_transaction_fee'];
                            // $transaction_id_link = "$log->properties['wallet_withdraw_id']";
                            $properties = json_decode($log->properties, true);
                            $transaction_id = $properties['wallet_withdraw_id'];

                            $logDescription = "User {$userLink} send request of \${$withdrawal_amount} using {$log->properties['remark']} with transaction ID {$transaction_id}";
                            break;
                        case 'Reject Wallet Withdraw':
                            $withdrawal_amount = $log->properties['approved_amount'];
                            $transaction_id_link = "{$log->properties['transaction_id']}";
                            $logDescription = "User {$userLink} {$log->properties['remark']} of \${$withdrawal_amount}, having transaction ID {$transaction_id_link}";
                            break;
                        case 'Approve Wallet Withdraw':
                            $withdrawal_amount = $log->properties['approved_amount'];
                            $transaction_id_link = "{$log->properties['transaction_id']}";
                            $logDescription = "User {$userLink} {$log->properties['remark']} of \${$withdrawal_amount}, having transaction ID {$transaction_id_link}";
                            break;
                        case 'Manually Approved Wallet Withdraw':
                            $withdrawal_amount = $log->properties['approved_amount'];
                            $transaction_id_link = "{$log->properties['transaction_id']}";
                            $logDescription = "User {$userLink} {$log->properties['remark']} of \${$withdrawal_amount}, having transaction ID {$transaction_id_link}";
                            break;
                        case 'Wallet Withdraw Cancel By Client':
                            $withdrawal_amount = $log->properties['amount'];
                            $logDescription = "{$log->properties['remark']} {$userLink} having amount  \${$withdrawal_amount}.";
                            break;
                        case 'Account Withdraw':
                            $withdrawal_amount = $log->properties['withdraw_amount'];
                            $logDescription = "User {$userLink} send withdraw request of \${$withdrawal_amount} from account {$account}.";
                            break;
                        case 'Account Deposit':
                            $withdrawal_amount = $log->properties['deposit_amount'];
                            $logDescription = "User {$userLink} deposit  \${$withdrawal_amount} to account {$account}.";
                            break;
                        case 'Internal Transfer':
                            $transfer_amount = $log->properties['transfer_amount'];
                            $logDescription = "User {$userLink} internal transfer  \${$transfer_amount} from account {$fromAccount_url} to {$toAccount_url}.";
                            break;
                        case 'Created New Wallet Address':
                            $wallet_name = $log->properties['wallet_name'];
                            $wallet_address = $log->properties['wallet_address'];
                            $wallet_network = $log->properties['wallet_network'];
                            $logDescription = "User ({$userLink}) created new wallet ( {$wallet_name} ) having address ({$wallet_address}) on network ({$wallet_network}). Address is not verified yet.";
                            break;
                        case 'Verified New Wallet Address':
                            $wallet_name = $log->properties['wallet_name'];
                            $wallet_address = $log->properties['wallet_address'];
                            $wallet_network = $log->properties['wallet_network'];
                            $logDescription = "User ({$userLink}) verified new wallet ( {$wallet_name} ) having address ({$wallet_address}) on network ({$wallet_network}).";
                            break;
                        case 'Edit Wallet Details':
                            $wallet_name = $log->properties['wallet_name'];
                            $wallet_address = $log->properties['wallet_address'];
                            $wallet_network = $log->properties['wallet_network'];
                            $logDescription = "User ({$userLink}) updated to wallet ( {$wallet_name} ) having address ({$wallet_address}) on network ({$wallet_network}).";
                            break;
                        case 'Verify Wallet Deletion':
                            $wallet_name = $log->properties['wallet_name'];
                            $logDescription = "User ({$userLink}) send verification email to delete wallet ( {$wallet_name} ).";
                            break;
                        case 'Wallet Deleted':
                            $wallet_name = $log->properties['wallet_name'];
                            $logDescription = "User ({$userLink}) deleted wallet ( {$wallet_name} ).";
                            break;
                        case 'Update Client Password':
                            $new_passowrd = $log->properties['new_passowrd'];
                            $logDescription = "User ({$userLink}) updated password to ( {$new_passowrd} ).";
                            break;
                        case 'Commission Transfer':
                            $deposit_amount = $log->properties['deposit_amount'];
                            $code = $log->properties['code'];
                            $logDescription = "User ({$userLink}) transfer comission of \${$deposit_amount} to {$account}.";
                            break;
                        case 'Update Referral':
                            $new = $log->properties['new'];
                            $old = $log->properties['old'];
                            $logDescription = "User ({$userLink}) updated referral code from {$old} to {$new}.";
                            break;
                        case 'Update Client Status':
                            $client_id = $log->properties['client_id'];
                            $client = User::where('id', $client_id)->first();
                            $client_url = "{$client->email}";
                            $logDescription = "User {$userLink} updated client {$client_url} status.";
                            break;
                        case 'Client Email Confirmation':
                            $client_id = $log->properties['send_to'];
                            $client = User::where('id', $client_id)->first();
                            $client_url = "{$client->email}";
                            $logDescription = "User {$userLink} send Email Confirmation mail to {$client_url}.";
                            break;
                        case 'Update Client Details':
                            $client_id = $log->properties['send_to'];
                            $client = User::where('id', $client_id)->first();
                            $client_url = "{$client->email}";
                            $logDescription = "User {$userLink} updated {$client_url} details.";
                            break;
                        case 'Delete Account':
                            $client_id = $log->properties['client_id'];
                            $code = $log->properties['code'];
                            $client = User::where('id', $client_id)->first();
                            $client_url = "{$client->email}";
                            $logDescription = "User {$userLink} deleted client {$client_url} account {$code} .";
                            break;
                        case 'CRM Deposit':
                            $client_id = $log->properties['client_id'];
                            $deposit_amount = $log->properties['deposit_amount'];
                            $client = User::where('id', $client_id)->first();
                            $client_url = "{$client->email}";
                            $logDescription = "User {$userLink} deposited \${$deposit_amount} to account {$account} of user {$client_url}.";
                            break;
                        case 'CRM Withdraw':
                            $client_id = $log->properties['client_id'];
                            $withdrawal_amount = $log->properties['withdrawal_amount'];
                            $client = User::where('id', $client_id)->first();
                            $client_url = "{$client->email}";
                            $logDescription = "User {$userLink} withdraw \${$withdrawal_amount} from account {$account} of user {$client_url}.";
                            break;
                        case 'CRM Credit Bonus':
                            $client_id = $log->properties['client_id'];
                            $bonus_amount = $log->properties['bonus_amount'];
                            $bonus_type = $log->properties['bonus_type'];
                            $client = User::where('id', $client_id)->first();
                            $client_url = "{$client->email}";
                            $logDescription = "User {$userLink} {$bonus_type} \${$bonus_amount} to account {$account} of user {$client_url}.";
                            break;
                        case 'CRM Deposit Bonus':
                            $client_id = $log->properties['client_id'];
                            $bonus_amount = $log->properties['bonus_amount'];
                            $bonus_type = $log->properties['bonus_type'];
                            $client = User::where('id', $client_id)->first();
                            $client_url = "{$client->email}";
                            $logDescription = "User  {$userLink} {$bonus_type} \${$bonus_amount} from account {$account} of user {$client_url}.";
                            break;
                        case 'CRM Update Investor Password':
                            $code = $log->properties['code'];
                            $account_data = Account::where('code', $code)->first();
                            $client = User::where('id', $account_data->user_id)->first();
                            $client_url = "{$client->email}";
                            $logDescription = "User  {$userLink} updated account investor password of user {$client_url} having account {$account}.";
                            break;
                        case 'CRM Update Master Password':
                            $code = $log->properties['code'];
                            $account_data = Account::where('code', $code)->first();
                            $client = User::where('id', $account_data->user_id)->first();
                            $client_url = "{$client->email}";
                            $logDescription = "User  {$userLink} updated account master password of user {$client_url} having account {$account}.";
                            break;
                        case 'CRM Update Group Leverage':
                            $code = $log->properties['code'];
                            $account_data = Account::where('code', $code)->first();
                            $client = User::where('id', $account_data->user_id)->first();
                            $client_url = "{$client->email}";
                            $logDescription = "User {$userLink} updated Group/Leverage of user {$client_url} having account {$account}.";
                            break;
                        case 'IB Plan Create':
                            $ib_cat_name = $log->properties['ib_cat_name'];
                            $logDescription = "User {$userLink} created IB Plan {$ib_cat_name}.";
                            break;
                        case 'IB Plan Update':
                            $ib_cat_name = $log->properties['ib_cat_name'];
                            $logDescription = "User {$userLink} updated IB Plan {$ib_cat_name}.";
                            break;
                        case 'IB Commission Create':
                            $ib_category_id = $log->properties['ib_category_id'];
                            $ib_plan =  IbCategory::where('id', $ib_category_id)->first();
                            $acc_type = $log->properties['acc_type'];
                            $ib_group =  AccountType::where('id', $acc_type)->first();
                            $logDescription = "User {$userLink} created IB commission with Group {$ib_group->ac_group} and Plan {$ib_plan->ib_cat_name}.";
                            break;
                        case 'IB Commission Update':
                            $ib_category_id = $log->properties['ib_category_id'];
                            $ib_plan =  IbCategory::where('id', $ib_category_id)->first();
                            $acc_type = $log->properties['acc_type'];
                            $ib_group =  AccountType::where('id', $acc_type)->first();
                            $logDescription = "User {$userLink} update IB commission with Group {$ib_group->ac_group} and Plan {$ib_plan->ib_cat_name}.";
                            break;
                        case 'Create Role':
                            $role_name = $log->properties['role_name'];
                            $logDescription = "User {$userLink} created new role {$role_name}.";
                            break;
                        case 'Update Role':
                            $role_name = $log->properties['role_name'];
                            $logDescription = "User {$userLink} updated role {$role_name}.";
                            break;
                        case 'Ib Request':
                            $ib_group = $log->properties['ib_group'];
                            $ib_plan = IbPlanDetails::with('plan')->withTrashed()->where('id', $ib_group)->first();
                            $ib_status = $log->properties['ib_status'];
                            $client_id = $log->properties['client_id'];
                            $client = User::where('id', $client_id)->first();
                            $client_url = "{$client->email}";
                            if ($ib_status == 1) {
                                $logDescription = "User {$userLink} approve ib request of client {$client_url} having plan {$ib_plan->plan->ib_cat_name}.";
                            } elseif ($ib_status == 0) {
                                $logDescription = "User {$userLink} change ib request of client {$client_url} having plan {$ib_plan->plan->ib_cat_name} to pending.";
                            } elseif ($ib_status == 2) {
                                $logDescription = "User {$userLink} change ib request of client {$client_url} having plan {$ib_plan->plan->ib_cat_name} to rejected.";
                            }

                            break;

                        default:
                            $logDescription = "Activity recorded for {$userLink}.";
                            break;
                    }

                    fputcsv($handle, [
                        $humanTime,
                        $ip,
                        $userLink,
                        $logDescription,
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function updatePaymentGateways(Request $request)
    {
        // Get checkbox values (1 if checked, 0 if unchecked)
        $payissaEnabled = $request->input('enable_creditcardpayissa', '0') === '1';
        $ragapayEnabled = $request->input('enable_ragapay', '0') === '1';
        $cryptochillEnabled = $request->input('enable_cryptochill', '0') === '1';

        // Auto-manage credit card: enabled if either sub-option is enabled
        $creditEnabled = $payissaEnabled || $ragapayEnabled;

        // Gateway settings to update
        $settings = [
            'enable_cryptochill' => $cryptochillEnabled ? '1' : '0',
            'enable_credit' => $creditEnabled ? '1' : '0',
            'enable_creditcardpayissa' => $payissaEnabled ? '1' : '0',
            'enable_ragapay' => $ragapayEnabled ? '1' : '0'
        ];

        // Update all settings
        foreach ($settings as $name => $value) {
            try {
                $result = Setting::updateOrCreate(
                    ['name' => $name],
                    ['value' => $value, 'updated_at' => now()]
                );

                // Log what actually happened
                Log::info("Gateway {$name}: " . ($result->wasRecentlyCreated ? 'CREATED' : 'UPDATED') . " with value {$value}, ID: {$result->id}");
            } catch (\Exception $e) {
                Log::error("Failed to update/create setting {$name}: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Payment gateway settings updated!');
    }

    public function toggleGroupCode(Request $request)
    {
        $groupCode = $request->input('group_code');

        if (!in_array($groupCode, ['A-Book', 'B-Book'])) {
            return redirect()->back()->with('error', 'Invalid group code selected.');
        }

        $toggle = ToggleGroup::first();
        if ($toggle) {
            if ($groupCode === 'A-Book') {
                $toggle->a_book = 1;
                $toggle->b_book = 0;
            } else {
                $toggle->a_book = 0;
                $toggle->b_book = 1;
            }
            $toggle->save();
            Artisan::call("app:alter-group-codes --group_code={$groupCode}");
        } else {
            if ($groupCode === 'A-Book') {
                ToggleGroup::create(['a_book' => 1, 'b_book' => 0]);
            } else {
                ToggleGroup::create(['a_book' => 0, 'b_book' => 1]);
            }
            Artisan::call("app:alter-group-codes --group_code={$groupCode}");
        }
        return redirect()->back()->with('success', 'Group code toggled to ' . strtoupper($groupCode) . ' successfully.');
    }

    public function toggleIbApproveRequest(Request $request)
    {
        $ibApprovalType = $request->input('ibApprovalType');

        if (!in_array($ibApprovalType, ['automatic', 'manually'])) {
            return redirect()->back()->with('error', 'Invalid group code selected.');
        }

        $ibRequestToggle = Setting::where('name', 'ib_toggle_activation')->first();
        if ($ibRequestToggle) {
            if ($ibApprovalType === 'manually') {
                $ibRequestToggle->value = 'manually';
            } elseif ($ibApprovalType === 'automatic') {
                $ibStatus = 1; // Active status
                $ibGroup = DB::table('ib_plan_details')
                    ->leftJoin('ib_categories', 'ib_categories.id', '=', 'ib_plan_details.ib_category_id')
                    ->where('ib_categories.ib_cat_name', 'default')
                    ->where('ib_plan_details.status', $ibStatus)
                    ->whereNull('ib_plan_details.deleted_at')
                    ->value('ib_plan_details.id');
                $Ibs = Ib1::where('status', 0)->get();
                // dd($Ibs);
                $Ibs->each(function ($ib) use ($ibGroup) {
                    $oldStatus = $ib->status;
                    $ib->status = 1; // Approve status
                    $ib->ib_plan_details_id = $ibGroup;
                    $ib->save();
                    // Fire IbStatusChanged event for auto-approval (status = 1)
                    if ($oldStatus != 1) {
                        Log::info('toggleIbApproveRequest: Firing IbStatusChanged event (IB Auto-Approved)', [
                            'ib_id' => $ib->id,
                            'old_status' => $oldStatus,
                            'new_status' => 1,
                        ]);
                        event(new \App\Events\IbStatusChanged($ib, $oldStatus, 1));
                    }
                });
                $ibRequestToggle->value = 'automatic';
            }
            $ibRequestToggle->save();
        } else {
            return redirect()->back()->with('error', 'IB Request setting not found.');
        }
        return redirect()->back()->with('success', 'IB Request toggle set to ' . (($ibApprovalType == 'manually') ? 'Manual' : 'Automatic')  . ' successfully.');
    }

    /**
     * Update KYC provider setting (Sumsub or Veriff).
     */
    public function updateKycProvider(Request $request)
    {
        $provider = $request->input('kyc_provider', 'sumsub');

        if (!in_array($provider, ['sumsub', 'veriff'], true)) {
            return redirect()->back()->with('error', 'Invalid KYC provider selected.');
        }

        Setting::updateOrCreate(
            ['name' => 'kyc_provider'],
            ['value' => $provider, 'updated_at' => now()]
        );

        return redirect()->back()->with('success', 'KYC provider updated to ' . ucfirst($provider) . ' successfully.');
    }

    public function twoFactorAuthenticationAdmin()
    {
        // $settings = Setting::whereIn('name', ['2fa_sms_enabled', '2fa_email_enabled', '2fa_authenticator_enabled'])->pluck('value', 'name')->toArray();
        return view('admin.admin2fa');
    }
}
