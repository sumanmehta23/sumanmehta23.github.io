<?php

namespace App\Http\Controllers;

use App\Events\AccountTradesDepositEvent;
use Carbon\Carbon;
use App\Models\Ib1;
use App\Models\User;
use App\Models\RestrictIps;
use App\Models\Country;
use Illuminate\Support\Str;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Services\MailService as MailService;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller

{
    protected $mailService;
    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }
    // Show login form
    public function showLoginForm()
    {
        // Redirect authenticated users to dashboard
        if (Auth::check()) {
            $user = Auth::user();
            // Check if 2FA is enabled and not yet verified
            if ($user->two_factor_secret && $user->two_factor_confirmed_at && !Session::has('2fa:verified')) {
                return redirect()->route('two_factor_auth');
            }
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function showConfirmPasswordForm()
    {

        return view('auth.confirm-password');
    }

    public function confirmPassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        // Check admin guard first (for admin users)
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            if (!Hash::check($request->password, $admin->password)) {
                return response()->json([
                    'message' => 'The password is incorrect.'
                ], 422);
            }
        } else {
            // Check web guard (for regular users)
            $user = Auth::guard('web')->user();
            if (!$user) {
                return response()->json([
                    'message' => 'You must be authenticated to confirm your password.'
                ], 401);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'The password is incorrect.'
                ], 422);
            }
        }

        // Store the timestamp in session to mark password confirmation
        $request->session()->put('auth.password_confirmed_at', time());

        return response()->json([
            'message' => 'Password confirmed successfully.'
        ]);
    }


    public function login(Request $request)
    {

        $restriction = RestrictIps::where('ip', $request->ip())->where('email', $request->email)->first();
        if ($restriction) {
            return redirect()->back()->with('error', 'Your account has been temporarily disabled. Please contact <a href="mailto:Compliance@1xTrade.com">Compliance@1xTrade.com</a>.');
        }
        $key = 'login:' . (auth()->id() ?: $request->ip());
        if (RateLimiter::tooManyAttempts($key, 3)) {
            RateLimiter::clear($key);
            RateLimiter::hit($key, 30);
            $retryAfter = 30;
            $hours = floor($retryAfter / 3600);
            $minutes = floor(($retryAfter % 3600) / 60);
            $seconds = $retryAfter % 60;
            $formattedTime = sprintf('%02d min %02d sec', $minutes, $seconds);
            activity()->withProperties(
                [
                    'ip' => $request->ip(),
                    'email' => $request->input('email'),
                    'remark' => 'Too many requests'
                ]
            )
                ->log('Authentication');
            return redirect()->back()->with(
                'error',
                "Too many requests. Please wait {$formattedTime} before trying again."
            )->with('retry_after', $retryAfter);
        }
        RateLimiter::hit($key, 30);
        // Validate form inputs
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (($request->input('email') == 'andrei_makalicza@yahoo.com') || ($request->input('email') == 'teodorescuv1990@gmail.com') || ($request->input('email') == 'aleksandra_andreea@yahoo.com')) {
            return redirect()->back()->with('error', 'Your account has been temporarily disabled. Please contact <a href="mailto:Compliance@1xTrade.com">Compliance@1xTrade.com</a>.');
        }

        // Find the user by email
        $user = User::where('email', $request->input('email'))->where('email_confirmed', 1)->first();

        // Check if user exists
        if (!$user) {
            activity()->withProperties(
                [
                    'ip' => $request->ip(),
                    'email' => $request->input('email'),
                    'remark' => 'Invalid email or unverified account'
                ]
            )
                ->log('Authentication');
            return redirect()->back()->with('error', 'Your login details are invalid or your email is not verified.');
        }

        // Check if the password is in plain text (not hashed)
        if (Hash::needsRehash($user->password)) {

            // If it's plain text, hash it and update the user's password
            if ($user->password === $request->input('password')) {
                $user->password = Hash::make($request->input('password'));
                $user->save(); // Update the user's password in the database
            } else {
                activity()->withProperties(
                    [
                        'ip' => $request->ip(),
                        'email' => $user->email,
                        'remark' => 'Incorrect password'
                    ]
                )
                    ->log('Authentication');
                return redirect()->back()->with('error', 'Your login details are invalid or your email is not verified.');
            }
        } else {
            // dump($user->password);
            // dump(Hash::make($request->input('password')));
            // dd(Hash::check($request->input('password'), $user->password));
            // If password is hashed, verify it
            if (!Hash::check($request->input('password'), $user->password)) {
                activity()->withProperties(
                    [
                        'ip' => $request->ip(),
                        'email' => $user->email,
                        'remark' => 'Incorrect login details'
                    ]
                )
                    ->log('Authentication');
                return redirect()->back()->with('error', 'Your login details are invalid or your email is not verified.');
            }
        }
        User::where('id', $user->id)
            ->whereNull('client_ip')
            ->update(['client_ip' => $request->ip()]);

        // Reactivate user if they were marked as inactive
        if ($user->is_inactive) {
            $user->is_inactive = false;
            $user->save();
        }

        Auth::login($user);
        $request->session()->regenerate();
        // Set session variables
        Session::put('clogin', $user->email);
        Session::put('user', $user);
        $this->recordLoginHistory($user, $request->ip());
        Session::put('2fa:user_id', $user->id);

        activity()->causedBy($user->id)
            ->withProperties(
                [
                    'ip' => $request->ip(),
                    'email' => $user->email,
                    'remark' => 'Login'
                ]
            )
            ->log('Authentication');

        if ($user->two_factor_secret  && $user->two_factor_confirmed_at) {
            return redirect()->route('two_factor_auth');
        } else {
            return redirect()->intended('/dashboard')->with('success', 'Logged in successfully.');
        }
    }

    public function two_factor_auth()
    {
        $user = auth()->user();

        // If 2FA is already verified, redirect to dashboard
        if ($user && Session::has('2fa:verified')) {
            return redirect()->route('dashboard');
        }

        // If user doesn't have 2FA enabled, redirect to dashboard
        if (!$user || !$user->two_factor_secret || !$user->two_factor_confirmed_at) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-2fa');
    }

    public function verify_two_factor_auth(Request $request, TwoFactorAuthenticationProvider $twoFactorProvider)
    {
        $user = auth()->user(); // Use the appropriate guard if needed

        if (!$user || !$user->two_factor_secret) {
            return redirect()->back()->with('error', 'Two-factor authentication is not set up.');
        }

        $mode = $request->input('mode'); // 'auth' or 'recovery'
        $inputCode = $mode === 'recovery'
            ? $request->input('recovery_code')
            : $request->input('code');

        if (!$inputCode) {
            return redirect()->back()->with('error', 'Please enter your ' . ($mode === 'recovery' ? 'recovery' : 'authenticator') . ' code.');
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

        if (!$isValid) {
            return redirect()->back()->with(
                'error',
                $mode === 'recovery'
                    ? 'Invalid Two Factor Recovery Code.'
                    : 'Invalid Two Factor Authentication Code.'
            );
        }

        // ✅ Success: 2FA verified - Set session flag
        Session::put('2fa:verified', true);
        Session::forget('2fa:user_id'); // Clean up temporary session

        return redirect()->intended(route('dashboard'));
    }


    // Logout user
    public function logout(Request $request)
    {
        activity()->causedBy(auth()->user()->id)
            ->withProperties(
                [
                    'ip' => $request->ip(),
                    'email' => auth()->user()->email,
                    'remark' => 'Logout'
                ]
            )
            ->log('Authentication');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
    public function forgot_password()
    {
        // Redirect authenticated users to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.forgot-password');
    }
    public function sendResetLink(Request $request)
    {

        $key = 'sendResetLink:' . (auth()->id() ?: $request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $retryAfter = RateLimiter::availableIn($key);
            $hours = floor($retryAfter / 3600);
            $minutes = floor(($retryAfter % 3600) / 60);
            $seconds = $retryAfter % 60;
            $formattedTime = sprintf('%02d min %02d sec', $minutes, $seconds);
            return redirect()->back()->with(
                'error',
                "Too many requests. Please wait {$formattedTime} before trying again."
            );
        }
        RateLimiter::hit($key, 600);

        $request->validate([
            'txtemail' => 'required|email',
        ]);

        $email = $request->input('txtemail');
        $user = User::where('email', $email)->first();

        if ($user) {
            $code = Str::random(60);
            // User::where('email', $email)->update(['emailToken' => $code]);

            $user->emailToken = $code;
            $user->email_token_time = Carbon::now();
            $user->save();

            $settings = settings();
            $from = $settings['email_from_address'];
            $emailSubject = $settings['admin_title'] . ' Password Reset';
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
            $content =
                '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
                '<div>We have received a request to reset the password associated with your account. If you initiated this request, please click the link below to reset your password:
                </div>';
            $id = $user['id'];
            $templateVars = [
                'name' => $user['fullname'],
                'site_link' => $settings['copyright_site_name_text'] . "/reset-password?id=$id&code=$code",
                'after_btn_text' => "<div>If you did not request a password reset, please disregard this email, and no further action is required.</div>
                                   <div>If you have any questions or need assistance, feel free to reach out to our support team.</div>
                                   <div>Best regards,<br>
                                   The Liquidity House Team
                                   </div>",
                'btn_text' => "Reset Password",
                'email' => $settings['email_from_address'],
                "content" => $content,
                "title_right" => "",
                "subtitle_right" => ""
            ];
            $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
            return redirect()->back()->with('success', "We have sent an email to $email. Please click on the password reset link in the email to generate a new password.");
        } else {
            return redirect()->back()->with('error', "Sorry! This email was not found.");
        }
    }

    public function resetPassword(Request $request)
    {

        $id = $request->query('id');
        $code = $request->query('code');
        // Check user exists
        $user = User::where('id', $id)->where('emailToken', $code)->first();
        // if ($user->email == 'abhay@lqhmarkets.com') {
        //     dump($request->all());
        //     dump($request->isMethod('post'));
        //     dump($user);
        //     dump($user->email_token_time);
        //     dump(Carbon::now()->subMinutes(env('FORGOT_PASSWORD_EXPIRATION_TIME')));
        //     dd($user->email_token_time >= Carbon::now()->subMinutes(env('FORGOT_PASSWORD_EXPIRATION_TIME')));
        // }
        if ($user && $user->email_token_time >= Carbon::now('UTC')->subMinutes(env('FORGOT_PASSWORD_EXPIRATION_TIME'))) {
            if ($request->isMethod('post')) {
                // Validate
                // $request->validate([
                //     'password' => 'required|string|confirmed'
                // ]);
                // dd($request->all());
                // $validatedData = Validator::make($request->all(), [
                //     'password' => [
                //         'required',
                //         'string',
                //         'confirmed', // At least 8 characters
                //         'regex:/[a-z]/', // At least one lowercase letter
                //         'regex:/[A-Z]/', // At least one uppercase letter
                //         'regex:/\d/', // At least one number
                //         'regex:/[\W_]/', // At least one special character
                //     ],
                //     'password_confirmation' => 'required_with:password|same:password',
                // ]);
                // if ($validatedData->fails()) {
                //     $errors = $validatedData->errors();
                //     $filteredErrors = [];
                //     // dd($errors);
                //     // Check which specific regex rule failed and return only unmet requirements
                //     if ($errors->has('password')) {
                //         $password = $request->password;

                //         if (!preg_match('/[a-z]/', $password)) {
                //             $filteredErrors[] = 'The password must contain at least one lowercase letter.';
                //         }
                //         if (!preg_match('/[A-Z]/', $password)) {
                //             $filteredErrors[] = 'The password must contain at least one uppercase letter.';
                //         }
                //         if (!preg_match('/\d/', $password)) {
                //             $filteredErrors[] = 'The password must contain at least one number.';
                //         }
                //         if (!preg_match('/[\W_]/', $password)) {
                //             $filteredErrors[] = 'The password must contain at least one special character.';
                //         }
                //         if (strlen($password) < 8) {
                //             $filteredErrors[] = 'The password must be at least 8 characters long.';
                //         }
                //         if ($errors->has('password.confirmed')) {
                //             $filteredErrors[] = 'Passwords do not match.';
                //         }
                //     }

                //     $errorString = '';
                //     foreach ($filteredErrors as $error) {
                //         $errorString .= '• ' . $error;
                //     }
                //     // dd($errorString);
                //     $errorString = html_entity_decode($errorString);
                //     // dd($errorString);
                //     // return redirect()->back()->with('error', 'The email you entered is already in use and exists in our system.');
                //     return redirect()->back()->with('error', $errorString);
                // }
                $request->validate([
                    'password' => ['required', 'string', new \App\Rules\ValidPassword(), 'confirmed'],
                ]);
                $password = $request->input('password');
                // DB::table('aspnetusers')
                //     ->where('email', $user->email)
                //     ->update(['password' => $password]);
                // Send the email notification
                User::where('email', $user->email)->update(['password' =>  Hash::make($password)]);
                $user->update(['emailToken' => null]);

                $this->sendPasswordResetSuccessEmail($user);
                return redirect()->route('login')->with('status', 'Password has been reset successfully. You can now login.');
            } else {
                return view('auth.reset-password', ['user' => $user]); // Return view
            }
        } else {
            return redirect()->route('login')->with('error', 'This password reset link is no longer valid. Please request a new one.');
        }
    }

    protected function sendPasswordResetSuccessEmail($user)
    {
        $settings = settings();
        $from = $settings['email_from_address'];
        $toEmail = $user->email;
        $emailSubject = $settings['admin_title'] . ' - Password Successfully Reset!';
        $htmlContent = "";
        // Set content-type header for sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        // Additional headers
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content =
            '<p>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</p>' .
            '<p></p>' .
            '<p>Your password has been successfully reset! If you made this change, no further action is needed. If you did not request this change, please contact our support team immediately.</p>
            <p></p>
            Thank you for being a valued member of our community!</div>';
        // Send email
        $templateVars = [
            'name' => $user->fullname,
            'site_link' => $settings['copyright_site_name_text'],
            'btn_text' => "Login",
            'email' => $settings['email_from_address'],
            "content" => $content,
            "title_right" => "Password Reset",
            "subtitle_right" => "Successful"
        ];
        $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);
    }
    public function register()
    {
        // Redirect authenticated users to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $countries = Country::all();
        return view('auth.register', compact('countries'));
    }
    public function addUser(Request $request)
    {

        // Generate a unique rate-limiting key based on user or IP
        $key = 'deposit:' . (auth()->id() ?: $request->ip());

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $retryAfter = RateLimiter::availableIn($key);
            return redirect()->back()->with([
                'error' => "Too many requests. Please wait {$retryAfter} seconds before trying again."
            ]);
        }

        // Increment the rate limiter
        RateLimiter::hit($key, 10); // Lock for 10 seconds
        // dd($request->all());
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'fullname' => [
                'required',
                'string',
                'min:2',
                'max:80',
                // Allow only letters, spaces and a few common name characters
                'regex:/^[\pL\s\.\'-]+$/u',
                // Block obvious spam / scripted content in the name field
                'not_regex:/http/i',
                'not_regex:/www\./i',
                'not_regex:/@/i',
                'not_regex:/\d{3,}/',
                'not_regex:/<script/i', // Prevents `<script>` tags or similar injections
            ],
            'email' => 'required|string|email|max:255|unique:aspnetusers',
            // 'password' => 'required|string|confirmed',
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8', // At least 8 characters
                'regex:/[a-z]/', // At least one lowercase letter
                'regex:/[A-Z]/', // At least one uppercase letter
                'regex:/\d/', // At least one number
                'regex:/[\W_]/', // At least one special character
            ],
            'country' => 'required|string',
            'country_code' => 'required',
            'telephone' => 'required',
        ], [
            'fullname.regex' => 'Please enter a valid full name (letters and basic punctuation only, no links, emails or codes).',
            'email.unique' => 'The email you entered is already in use and exists in our system. If you believe this is incorrect, please contact support at support@lqhmarkets.com.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.regex' => 'The password must contain at least one lowercase letter, one uppercase letter, one number, and one special character.',
            'password.confirmed' => 'Passwords do not match.',
        ]);


        if ($validator->fails()) {
            return redirect()->route('register')->with('errors', $validator->errors());
        }

        $userData = [];

        if ($request->has('refercode') || $request->has('referral')) {
            $refercode = $request->refercode;
            if (empty($refercode)) {
                $refercode = $request->referral;
            }
            $referrals = [];
            $nextReferral = $refercode;

            for ($i = 1; $i <= 15; $i++) {
                if (!$nextReferral) {
                    break;
                }
                $result = Ib1::where('referral_code', $nextReferral)->first();
                if (!$result || empty($result->email)) {
                    $nextReferral = null;
                    break;
                }
                $email2 = $result->email;
                $referrals["ib{$i}"] = $nextReferral;
                $parentUser = User::where('email', $email2)->first();
                $nextReferral = $parentUser ? $parentUser->ib1 : null;
            }

            for ($i = 1; $i <= 15; $i++) {
                $ibKey = "ib{$i}";
                if (!array_key_exists($ibKey, $referrals)) {
                    $referrals[$ibKey] = null;
                }
            }

            foreach ($referrals as $key => $referralCode) {
                $userData[$key] = $referralCode;
            }
        }
        $userData['referral'] = '';
        // if($request->has('referral')){
        //     $result = Ib1::where(['referral_code'=> $request->referral,'status'=>1])->first();
        //     if ($result) {
        //         $userData['ib1'] = $request->referral;
        //     }
        // }
        $code = Str::random(60);
        $number = $request->country_code . $request->telephone;

        $userData['email'] = $request->email;
        $userData['fullname'] = $request->fullname;
        $userData['password'] = Hash::make($request->password);
        $userData['country_code'] = $request->country_code;
        $userData['number'] = $number;
        $userData['username'] = $request->email;
        $userData['gender'] = $request->gender;

        $userData['email_verify_token'] = $code;
        $userData['country'] = $request->country;
        $userData['created_at'] = now();
        $userData['updated_at'] = now();
        $userData['client_ip'] = $request->ip();

        // Check for affiliate reference code in cookie
        if ($request->hasCookie('cxd')) {
            $affiliateReferenceCode = $request->cookie('cxd');
            // Validate the affiliate code format
            if (preg_match('/^\d+_\d+$/', $affiliateReferenceCode)) {
                $userData['cxd'] = $affiliateReferenceCode;
            }
        }

        $user = User::create($userData);

        if ($user) {
            // Fire the Registered event for Omnisend integration
            event(new \Illuminate\Auth\Events\Registered($user));

            $settings = settings();
            $from = $settings['email_from_address'];
            $toEmail = $request->email;
            $uid = uniqid();
            $emailSubject = $settings['admin_title'] . ' - Email Address Verification';
            $htmlContent = "";
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
            $content =
                '<p>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</p>' .
                '<p></p>' .
                '<p>You are receiving this email because you have registered for a LQH Markets Account.</p>' .
                '<p></p>' .
                '<p>Click the button below to activate your Account</p>';

            $templateVars = [
                'name' => $request->fullname,
                'server_name' => $settings['mt5_company_name'],
                'site_link' => $settings['copyright_site_name_text'] . "/email_verify?id={$user->id}&code=$code",
                'email' => $settings['email_from_address'],
                "content" => $content,
                "title_right" => "Activate",
                "subtitle_right" => "Your Account",
                "btn_text" => "Activate",
            ];
            $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);
            // User registration will automatically trigger Omnisend via Registered event
            // No additional Omnisend code needed here

            return redirect()->route('register')->with('status', 'We have sent an email to ' . $toEmail . '. Please click on the confirmation link in the email to activate your account and login.');
        }
        return back()->withErrors(['error' => 'Registration failed. Please try again.']);
    }
    public function verifyEmail(Request $request)
    {
        $settings = settings();
        $id = $request->query('id');
        $code = $request->query('code');

        $user = User::where('id', $id)
            ->where('email_verify_token', $code)
            ->first();

        if ($user) {
            if ($user->status == 0) {
                $user->status = 1;
                $user->email_confirmed = 1;
                $user->save();
                $from = $settings['email_from_address'];
                $emailSubject = $settings['admin_title'] . ' - Thank You for Confirming Your Email Address';
                $htmlContent = "";
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $content =
                    '<p>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</p>' .
                    '<p></p>' .
                    '<p>Your email address has been successfully confirmed, and you’re all set to start exploring everything we have to offer.</p>' .
                    '<p></p>' .
                    '<p><b>Here are your login credentials:</b></p>
                     <p></p>
                     <p><b>Username: </b> <a href="mailto:' . $user->email . '" style="color: #00b98e; text-decoration: none;">' . $user->email . '</a></p>';
                $templateVars = [
                    'name' => $user->fullname,
                    'server_name' => $settings['mt5_company_name'],
                    'site_link' => $settings['copyright_site_name_text'] . "/login",
                    'email' => $settings['email_from_address'],
                    "content" => $content,
                    "title_right" => "Email Verification",
                    "subtitle_right" => "Successful",
                    "btn_text" => "Login"
                ];
                $this->mailService->sendEmail($user->email, $emailSubject, $headers, '', $templateVars);
                return redirect()->route('login')->with('status', 'Your account has been activated');
            } else {
                return redirect()->route('login')->with('error', 'Sorry! Your Account is already Activated');
            }
        } else {
            return redirect()->route('register')->with('error', 'Sorry! No Account Found. Signup here');
        }
    }
    private function recordLoginHistory($user, $ip)
    {
        $geoData = Http::get(config('services.ip_geolocation.url'), [
            'apikey' => config('services.ip_geolocation.key'),
            'ip' => $ip
        ])->json();

        // Use country from user's profile, fallback to geolocation API, then 'Unknown'
        $country = $user->country ?? ($geoData['country_name'] ?? 'Unknown');

        LoginHistory::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $geoData['ip'] ?? $ip,
            'country' => $country,
            'action' => 'login',
            'created_date_js' => now(),
            'status' => 1
        ]);
    }
}
