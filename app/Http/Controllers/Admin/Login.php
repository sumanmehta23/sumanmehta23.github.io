<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeList;
use App\Models\LoginHistory;
use App\Services\MailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticationProvider;

class Login extends Controller
{
    protected $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function index()
    {
        return view('admin.login');
    }

    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {

            if (Auth::guard('admin')->user()->two_factor_secret) {
                return view('admin.verify_2fa');
            } else {
                return redirect()->route('admin.dashboard');
            }
        }

        return view('admin.login');
    }

    public function verify_2fa()
    {
        return view('admin.verify_2fa');
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|email',
            'password' => 'required',
        ]);
        $credentials = $request->only('username', 'password');
        $remember = (bool) $request->input('remember');

        // Attempt to log the user in
        $user = EmployeeList::where('email', $credentials['username'])
            ->first();

        if (! $user) {
            return redirect()->back()->with('error', 'Your login details are invalid or your email is not verified.');
        }

        if (Hash::needsRehash($user->password)) {
            if ($user->password === $request->input('password')) {
                $user->password = Hash::make($request->input('password'));
                $user->save();
            } else {
                return redirect()->back()->with('error', 'Login Details are Invalid');
            }
        } else {
            if (! Hash::check($request->input('password'), $user->password)) {
                return redirect()->back()->with('error', 'Your login details are invalid.');
            }
        }

        if ($user->status !== 1) {
            return back()->with('error', 'Your account is inactive. Please contact the administrator.');
        }

        // Authenticate the user with remember functionality
        Auth::guard('admin')->login($user, $remember);
        $request->session()->regenerate();

        activity()
            ->causedBy($user)
            ->withProperties([
                'ip' => $request->ip(),
                'email' => $user->email,
                'userRole' => $user->userRole,
                'userAccessLevel' => $user->userAccessLevel,
                'username' => $user->username,
                'admin_id' => $user->id,
                'remark' => 'Login',
            ])
            ->log('Authentication');

        // Store user details in session
        Cache::flush();
        Session::put('alogin', $user->email);
        Session::put('userRoleID', $user->role_id);
        Session::put('userRole', $user->userRole);
        Session::put('userID', $user->client_index);
        Session::put('userData', $user->toArray());

        // Log user in
        $this->logLoginHistory($user->email);

        if ($user->two_factor_secret && $user->two_factor_confirmed_at) {
            return redirect('admin/verify_2fa');
        } else {
            return redirect('admin/dashboard');
        }
    }

    public function verify_two_factor_auth(Request $request, TwoFactorAuthenticationProvider $twoFactorProvider)
    {
        $user = auth()->guard('admin')->user();

        if (! $user || ! $user->two_factor_secret) {
            return redirect()->back()->with('error', '2FA is not set up.');
        }

        $mode = $request->input('mode'); // either 'auth' or 'recovery'
        $inputCode = $mode === 'recovery'
            ? $request->input('recovery_code')
            : $request->input('code');

        if (! $inputCode) {
            return redirect()->back()->with('error', 'Please enter your '.($mode === 'recovery' ? 'recovery' : 'authenticator').' code.');
        }

        $isValid = false;

        if ($mode === 'recovery') {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

            if (in_array($inputCode, $recoveryCodes)) {
                $isValid = true;
                $recoveryCodes = array_diff($recoveryCodes, [$inputCode]);

                $user->forceFill([
                    'two_factor_recovery_codes' => encrypt(json_encode(array_values($recoveryCodes))),
                ])->save();
            }
        } else {
            $isValid = $twoFactorProvider->verify(
                decrypt($user->two_factor_secret),
                $inputCode
            );
        }

        if (! $isValid) {
            return redirect()->back()->with(
                'error',
                $mode === 'recovery'
                    ? 'Invalid Two Factor Recovery Code.'
                    : 'Invalid Two Factor Authentication Code.'
            );
        }

        // ✅ 2FA successful
        return redirect('admin/dashboard');
    }

    private function logLoginHistory($email)
    {
        $country = '';
        $ip = request()->ip();
        // LoginHistory::create([
        //     'user_id' => Auth::user()->id,
        //     'email' => $email,
        //     'ip' => $ip,
        //     'country' => $country,
        //     'action' => 'login',
        //     'status' => 1
        // ]);
    }

    public function logout(Request $request)
    {
        activity()
            ->causedBy(auth()->guard('admin')->user())
            ->withProperties([
                'ip' => $request->ip(),
                'email' => auth()->guard('admin')->user()->email,
                'userRole' => auth()->guard('admin')->user()->userRole,
                'userAccessLevel' => auth()->guard('admin')->user()->userAccessLevel,
                'username' => auth()->guard('admin')->user()->username,
                'id' => auth()->guard('admin')->user()->id,
                'remark' => 'Logout',
            ])
            ->log('Authentication');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::logout();
        session()->forget(['userData', 'alogin']);
        unset($_SESSION['alogin']);
        unset($_SESSION['userData']);

        return redirect('/admin/login');
    }

    /**
     * Show admin forgot password form
     */
    public function showAdminForgotPasswordForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.forgot-password');
    }

    /**
     * Send admin password reset link
     */
    public function sendAdminResetLink(Request $request)
    {
        $key = 'adminResetLink:'.(auth('admin')->id() ?: $request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $retryAfter = RateLimiter::availableIn($key);
            $minutes = floor($retryAfter / 60);
            $seconds = $retryAfter % 60;
            $formattedTime = sprintf('%02d min %02d sec', $minutes, $seconds);

            return redirect()->back()->with(
                'error',
                "Too many requests. Please wait {$formattedTime} before trying again."
            );
        }
        RateLimiter::hit($key, 600);

        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $admin = EmployeeList::where('email', $email)->first();

        if ($admin) {
            $code = Str::random(50);
            $admin->emailToken = $code;
            $admin->email_token_time = Carbon::now();
            $admin->save();

            $settings = settings();
            $from = $settings['email_from_address'];
            $emailSubject = 'Admin Password Reset - '.$settings['admin_title'];
            $headers = 'MIME-Version: 1.0'."\r\n";
            $headers .= 'Content-type:text/html;charset=UTF-8'."\r\n";
            $headers .= 'From:'.$settings['admin_title'].'<'.$from.'>'."\r\n";

            $content = '<div>Welcome to '.htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8').' Admin Panel!</div>'.
                '<div>We have received a request to reset the password associated with your admin account. If you initiated this request, please click the link below to reset your password:</div>';

            $id = $admin->id;
            $resetLink = $settings['copyright_site_name_text']."/admin/reset-password?id=$id&code=$code";

            $templateVars = [
                'name' => $admin->username,
                'site_link' => $resetLink,
                'after_btn_text' => '<div>If you did not request a password reset, please disregard this email, and no further action is required.</div>'.
                    '<div>If you have any questions or need assistance, feel free to reach out to our support team.</div>'.
                    '<div>Best regards,<br>The Administration Team</div>',
                'btn_text' => 'Reset Admin Password',
                'email' => $settings['email_from_address'],
                'content' => $content,
                'title_right' => '',
                'subtitle_right' => '',
            ];

            $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);

            return redirect()->back()->with('success', "We have sent a password reset link to $email. Please check your email to reset your password.");
        } else {
            return redirect()->back()->with('error', 'Sorry! This email was not found in admin records.');
        }
    }

    /**
     * Show admin reset password form
     */
    public function showAdminResetPasswordForm(Request $request)
    {
        $id = $request->query('id');
        $code = $request->query('code');

        $admin = EmployeeList::where('id', $id)->where('emailToken', $code)->first();

        if ($admin && $admin->email_token_time >= Carbon::now('UTC')->subMinutes(env('FORGOT_PASSWORD_EXPIRATION_TIME', 60))) {
            return view('admin.reset-password', ['id' => $id, 'code' => $code]);
        } else {
            return redirect('/admin/login')->with('error', 'This password reset link is invalid or has expired.');
        }
    }

    /**
     * Update admin password
     */
    public function resetAdminPassword(Request $request)
    {
        if ($request->isMethod('post')) {
            $id = $request->input('id');
            $code = $request->input('code');

            $admin = EmployeeList::where('id', $id)->where('emailToken', $code)->first();

            if (! $admin || $admin->email_token_time < Carbon::now('UTC')->subMinutes(env('FORGOT_PASSWORD_EXPIRATION_TIME', 60))) {
                return redirect('/admin/login')->with('error', 'This password reset link is invalid or has expired.');
            }

            $request->validate([
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required_with:password|same:password',
            ]);

            $admin->password = Hash::make($request->input('password'));
            $admin->emailToken = null;
            $admin->email_token_time = null;
            $admin->save();

            activity()
                ->causedBy($admin)
                ->withProperties([
                    'email' => $admin->email,
                    'username' => $admin->username,
                    'admin_id' => $admin->id,
                    'remark' => 'Password Reset',
                ])
                ->log('Authentication');

            return redirect('/admin/login')->with('success', 'Your password has been reset successfully. Please log in with your new password.');
        }

        // GET request - show form
        $id = $request->query('id');
        $code = $request->query('code');

        $admin = EmployeeList::where('id', $id)->where('emailToken', $code)->first();

        if ($admin && $admin->email_token_time >= Carbon::now('UTC')->subMinutes(env('FORGOT_PASSWORD_EXPIRATION_TIME', 60))) {
            return view('admin.reset-password', ['id' => $id, 'code' => $code]);
        } else {
            return redirect('/admin/login')->with('error', 'This password reset link is invalid or has expired.');
        }
    }
}
