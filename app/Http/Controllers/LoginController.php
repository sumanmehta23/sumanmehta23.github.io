<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Ib1;
use App\Models\User;
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
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validate form inputs
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
         // Find the user by email
         $user = User::where('email', $request->input('email'))->where('email_confirmed', 1)->first();

         // Check if user exists
         if (!$user) {
             return redirect()->back()->with('error', 'Your login details are invalid or your email is not verified.');
         }

         // Check if the password is in plain text (not hashed)
         if (Hash::needsRehash($user->password)) {
             // If it's plain text, hash it and update the user's password
             if ($user->password === $request->input('password')) {
                 $user->password = Hash::make($request->input('password'));
                 $user->save(); // Update the user's password in the database
             } else {
                 return redirect()->back()->with('error', 'Your login details are invalid or your email is not verified.');
             }
         } else {
             // If password is hashed, verify it
             if (!Hash::check($request->input('password'), $user->password)) {
                 return redirect()->back()->with('error', 'Your login details are invalid or your email is not verified.');
             }
         }
         Auth::login($user);
         $request->session()->regenerate();
         // Set session variables
         Session::put('clogin', $user->email);
         Session::put('user', $user);
         $this->recordLoginHistory($user, $request->ip());
         return redirect()->intended('/dashboard')->with('success', 'Logged in successfully.');

    }



    // Logout user
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
    public function forgot_password()
    {
        return view('auth.forgot-password');
    }
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'txtemail' => 'required|email',
        ]);
        $email = $request->input('txtemail');
        $user = User::where('email', $email)->first();
        if ($user) {
            $code =Str::random(60);
            User::where('email', $email)->update(['emailToken' => $code]);
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
                'after_btn_text'=>"<p>If you did not request a password reset, please disregard this email, and no further action is required.</p>
                                   <p>If you have any questions or need assistance, feel free to reach out to our support team.</p>
                                   <p>Best regards,<br>
                                   The Liquidity House Team
                                   <p>",
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
        if ($user) {
            if ($request->isMethod('post')) {
                // Validate
                $request->validate([
                    'password' => 'required|string|confirmed'
                ]);
                $password = $request->input('password');
                DB::table('aspnetusers')
                    ->where('email', $user->email)
                    ->update(['password' => $password]);
                // Send the email notification
                $this->sendPasswordResetSuccessEmail($user);
                return redirect()->route('login')->with('status', 'Password has been reset successfully. You can now login.');
            }
            return view('auth.reset-password', ['user' => $user]); // Return view
        } else {
            return redirect()->route('login')->with('error', 'No account found with the given ID and token.');
        }
    }

    protected function sendPasswordResetSuccessEmail($user)
    {
        $settings = settings();
        $from = $settings['email_from_address'];
        $toEmail = $user->email;
        $emailSubject = $settings['admin_title'] . ' - Client Portal Password Reset Success!';
        $htmlContent = "";
        // Set content-type header for sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        // Additional headers
        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
        $content =
            '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
            'Your password has been successfully reset! You can now log in to your account using your new password. If you did not request this change, please reach out to our support team immediately.<br>
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
        $countries = Country::all();
        return view('auth.register', compact('countries'));
    }
    public function addUser(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:aspnetusers',
            'password' => 'required|string|confirmed',
            'country' => 'required|string',
            'country_code' => 'required',
            'telephone' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('register')->with('errors', $validator->errors());
        }

        $userData = [];

        if ($request->has('refercode')) {
            $refercode = $request->query('refercode');
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

        $code = Str::random(60);
        $number = $request->country_code . $request->telephone;

        $userData['email'] =$request->email;
        $userData['fullname'] =$request->fullname;
        $userData['password'] =Hash::make($request->password);
        $userData['number'] =$number;
        $userData['username'] =$request->email;
        $userData['referral'] ='';
        $userData['emailToken'] =$code;
        $userData['country'] =$request->country;
        $userData['created_at'] =now();

        $user = User::create($userData);

        if ($user) {
            $settings = settings();
            $from = $settings['email_from_address'];
            $toEmail = $request->email;
            $uid = uniqid();
            $emailSubject = $settings['admin_title'] . ' - Email Address Verfication';
            $htmlContent = "";
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
            $content =
                '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
                '<div>You are receiving this email because you have registered for a Trading Account.</div>' .
                '<div>Click the link below to activate your Trading Account</div>';

            $templateVars = [
                'name' => $request->fullname,
                'server_name' => $settings['mt5_company_name'],
                'site_link' => $settings['copyright_site_name_text'] . "/email_verify?id={$user->id}&code=$code",
                'email' => $settings['email_from_address'],
                "content" => $content,
                "title_right" => "Activate",
                "subtitle_right" => "Your Account"
            ];
            $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);
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
            ->where('emailToken', $code)
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
                    '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
                    '<div>Your email address has been successfully confirmed, and you’re all set to start exploring everything we have to offer.</div>' .
                    '<div><b>Here are your login credentials:</b></div>
          <div><b>Username: </b>' . $user->email . '</div>
          <div><b>Password: </b>' . $user->password . '</div>';
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
                return redirect()->route('login')->with('status', 'WoW! Your Account is Now Activated');
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

        LoginHistory::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $geoData['ip'] ?? $ip,
            'country' => $geoData['country_name'] ?? 'Unknown',
            'action' => 'login',
            'created_date_js' => now(),
            'status' => 1
        ]);
    }
}
