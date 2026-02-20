<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isNull;

class AdminTwoFactorAuthentication extends Component
{
    protected $guard = 'admin';

    public $showingQrCode = false;
    public $showingConfirmation = false;
    public $showingRecoveryCodes = false;
    public $code;

    public function __construct()
    {
        $this->initTwoFactorStatus();
    }

    /**
     * Initialize the 2FA status based on the user's current state.
     */
    protected function initTwoFactorStatus()
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm')) {
            if ($this->isEnabled()) {
                // If two_factor_confirmed_at is null, we're still in confirmation mode
                $this->showingConfirmation = $this->showingQrCode = (bool) (
                    !empty($this->user()->two_factor_secret) &&
                    !empty($this->user()->two_factor_recovery_codes) &&
                    is_null($this->user()->two_factor_confirmed_at)
                );
                $this->showingRecoveryCodes = !$this->showingConfirmation;
            }
        }
    }

    /**
     * Get the currently authenticated user based on guard.
     */
    public function user()
    {
        // Check if admin is authenticated using admin guard
        if (Auth::guard($this->guard)->check()) {
            return Auth::guard($this->guard)->user();
        }

        // Fallback to default auth
        return Auth::user();
    }

    /**
     * Check if 2FA is enabled for the user.
     */
    public function isEnabled()
    {
        return !empty($this->user()->two_factor_secret);
    }

    public function enableTwoFactorAuthentication(EnableTwoFactorAuthentication $enable)
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $this->ensurePasswordIsConfirmed();
        }

        $enable($this->user());
        $this->showingQrCode = true;
        $this->showingConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        $this->showingRecoveryCodes = !$this->showingConfirmation;

        return true;
    }

    public function confirmTwoFactorAuthentication(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $code = $request->input('code');

        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $this->ensurePasswordIsConfirmed();
        }

        try {
            $confirm($this->user(), $code);
            $this->showingQrCode = false;
            $this->showingConfirmation = false;
            $this->showingRecoveryCodes = true;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function showRecoveryCodes()
    {
        $this->showingRecoveryCodes = true;
        return true;
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate)
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $this->ensurePasswordIsConfirmed();
        }

        $generate($this->user());
        $this->showingRecoveryCodes = true;
        return true;
    }

    public function disableTwoFactorAuthentication(DisableTwoFactorAuthentication $disable)
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $this->ensurePasswordIsConfirmed();
        }

        $disable($this->user());
        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = false;
        return true;
    }

    /**
     * Ensure that the user's password has been recently confirmed.
     *
     * @param  int|null  $maximumSecondsSinceConfirmation
     * @return void
     */
    protected function ensurePasswordIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        if (!$this->passwordIsConfirmed($maximumSecondsSinceConfirmation)) {
            abort(403, 'Password confirmation required.');
        }
    }

    protected function passwordIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        return (time() - session('auth.password_confirmed_at', 0)) < $maximumSecondsSinceConfirmation;
    }

    public function render()
    {
        return view('components.admin-two-factor-authentication', [
            'user' => $this->user(),
            'enabled' => $this->isEnabled(),
            'showingQrCode' => $this->showingQrCode,
            'showingConfirmation' => $this->showingConfirmation,
            'showingRecoveryCodes' => $this->showingRecoveryCodes,
            'code' => $this->code,
        ]);
    }
}
