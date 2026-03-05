<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;

class TwoFactorAuthController extends Controller
{
    protected $guard = 'admin';

    public $showingQrCode = false;
    public $showingConfirmation = false;
    public $showingRecoveryCodes = false;

    /**
     * Display the 2FA settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.two-factor-auth-settings');
    }

    /**
     * Get the currently authenticated admin user.
     */
    protected function user()
    {
        return Auth::guard($this->guard)->user();
    }

    /**
     * Check if 2FA is enabled for the user.
     */
    protected function isEnabled()
    {
        return !empty($this->user()->two_factor_secret);
    }

    /**
     * Enable two factor authentication for the user.
     */
    public function enableTwoFactorAuthentication(EnableTwoFactorAuthentication $enable)
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $this->ensurePasswordIsConfirmed();
        }

        $enable($this->user());

        $this->showingQrCode = true;
        $this->showingConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        $this->showingRecoveryCodes = !$this->showingConfirmation;

        return response()->json([
            'success' => true,
            'showingQrCode' => $this->showingQrCode,
            'showingConfirmation' => $this->showingConfirmation,
            'showingRecoveryCodes' => $this->showingRecoveryCodes,
            'two_factor_secret' => decrypt($this->user()->two_factor_secret),
            'recovery_codes' => json_decode(decrypt($this->user()->two_factor_recovery_codes), true),
        ]);
    }

    /**
     * Confirm two factor authentication for the user.
     */
    public function confirmTwoFactorAuthentication(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $code = $request->input('code');

        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $this->ensurePasswordIsConfirmed();
        }

        try {
            $confirm($this->user(), $code);

            return response()->json([
                'success' => true,
                'message' => 'Two factor authentication confirmed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'The provided two factor authentication code was invalid.'
            ], 422);
        }
    }

    /**
     * Show recovery codes for the user.
     */
    public function showRecoveryCodes()
    {
        $this->showingRecoveryCodes = true;

        return response()->json([
            'success' => true,
            'recovery_codes' => json_decode(decrypt($this->user()->two_factor_recovery_codes), true),
        ]);
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate)
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $this->ensurePasswordIsConfirmed();
        }

        $generate($this->user());

        return response()->json([
            'success' => true,
            'recovery_codes' => json_decode(decrypt($this->user()->two_factor_recovery_codes), true),
        ]);
    }

    /**
     * Disable two factor authentication for the user.
     */
    public function disableTwoFactorAuthentication(DisableTwoFactorAuthentication $disable)
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $this->ensurePasswordIsConfirmed();
        }

        $disable($this->user());

        return response()->json(['success' => true]);
    }

    /**
     * Get the current 2FA status for the user.
     */
    public function getStatus()
    {
        $enabled = $this->isEnabled();
        $showingQrCode = false;
        $showingConfirmation = false;
        $showingRecoveryCodes = false;

        if ($enabled && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm')) {
            $showingConfirmation = (bool)(!empty($this->user()->two_factor_secret) &&
                !empty($this->user()->two_factor_recovery_codes) &&
                is_null($this->user()->two_factor_confirmed_at));
            $showingQrCode = $showingConfirmation;
            $showingRecoveryCodes = !$showingConfirmation && !empty($this->user()->two_factor_confirmed_at);
        } else if ($enabled) {
            $showingRecoveryCodes = !empty($this->user()->two_factor_confirmed_at);
        }

        return response()->json([
            'enabled' => $enabled,
            'showingQrCode' => $showingQrCode,
            'showingConfirmation' => $showingConfirmation,
            'showingRecoveryCodes' => $showingRecoveryCodes,
            'two_factor_confirmed_at' => $this->user()->two_factor_confirmed_at,
        ]);
    }

    protected function ensurePasswordIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        $this->passwordIsConfirmed($maximumSecondsSinceConfirmation) ? null : abort(403, 'Password confirmation required.');
    }

    protected function passwordIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        // Use the admin guard session key
        return (time() - session('auth.password_confirmed_at', 0)) < $maximumSecondsSinceConfirmation;
    }
}
