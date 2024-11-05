<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Services\MailService as MailService;
use App\Models\Country;
use Illuminate\Support\Facades\Validator;

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
        // Prepare credentials
        $credentials = $request->only('email', 'password');
        // Check if the email is confirmed
        $user = User::where('email', $credentials['email'])
            ->where('email_confirmed', 1)
            ->first();
        // Check if user exists
        if ($user) {
            if ($user->password === $credentials['password']) {
                // Log the user in
                Auth::login($user);
                $_SESSION['clogin'] = $credentials['email'];
                $_SESSION['user'] = $user->toArray();
                session(['user' => $user, 'clogin' => $credentials['email']]);
            } else {
                // Password doesn't match
                return back()->with('error', 'Your login details are invalid or your email is not verified.');
            }
            // Regenerate the session to prevent session fixation
            $request->session()->regenerate();
            // Get IP and country information
            $response = Http::get('https://api.ipgeolocation.io/ipgeo', [
                'apikey' => '77ac63f823cd4a6d891562102dec49bb',
                'ip' => $request->ip() // Use client's real IP
            ]);
            $geoData = $response->json();
            // Insert login history into the database
            DB::table('login_history')->insert([
                'email' => $user->email,
                'ip' => $geoData['ip'] ?? $request->ip(),
                'country' => $geoData['country_name'] ?? 'Unknown',
                'action' => 'login',
                'created_date_js' => Carbon::now(),
                'status' => 1
            ]);
            // Redirect to the dashboard
            return redirect()->route('dashboard');
        }
        // If login fails, redirect back with error message
        return back()->with('error', 'Your login details are invalid or your email is not verified.');
    }



    // Logout user
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::logout();
        session()->forget(['user', 'clogin']);
        unset($_SESSION['clogin']);
        unset($_SESSION['user']);
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
            $code = md5(uniqid(rand()));
            User::where('email', $email)->update(['emailToken' => $code]);
            $settings = settings();
            $from = $settings['email_from_address'];
            $emailSubject = $settings['admin_title'] . ' Password Reset';
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
            $content =
                '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
                '<div>We received a request to reset your password. If you made this request, click the link below to reset your password. If you did not request a password reset, you can ignore this email.
        </div>';
            $id = $user['id'];
            $templateVars = [
                'name' => $user['fullname'],
                'site_link' => $settings['copyright_site_name_text'] . "/reset-password?id=$id&code=$code",
                'btn_text' => "Reset Password",
                'email' => $settings['email_from_address'],
                "content" => $content,
                "title_right" => "Reset",
                "subtitle_right" => "Your Password"
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
            '<div>You have successfully reset your password. Thank you for being with us.</div>';
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
        // If validation fails, return errors
        if ($validator->fails()) {
            return redirect()->route('register')->with('errors', $validator->errors());
        }
        $ib1 = '';
        if ($request->has('refercode')) {
            $ib1 = base64_decode($request->query('refercode'));
        }
        // Create the user
        $code = md5(uniqid(rand()));
        $number = $request->country_code . $request->telephone;

        $lastInsertId = DB::table('aspnetusers')->insertGetId([
            'email' => $request->email,
            'fullname' => $request->fullname,
            'password' => $request->password, // Ensure this is hashed if required
            'number' => $number,
            'username' => $request->email,
            'referral' => '',
            'emailToken' => $code,
            'country' => $request->country,
            'ib1' => $ib1
        ]);
        if ($lastInsertId) {
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
                'site_link' => $settings['copyright_site_name_text'] . "/email_verify?id=$lastInsertId&code=$code",
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
        echo $id;
        echo $code;
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
}
