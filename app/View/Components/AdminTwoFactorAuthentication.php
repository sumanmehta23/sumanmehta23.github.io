<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;

use function PHPUnit\Framework\isNull;

class AdminTwoFactorAuthentication extends Component
{
    public $showingQrCode = false;
    public $showingConfirmation = false;
    public $showingRecoveryCodes = false;
    public $code;

    public function __construct()
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm')) {
            // app(DisableTwoFactorAuthentication::class)(Auth::user());
            if ($this->isEnabled()) {
                $this->showingConfirmation = $this->showingQrCode = (bool) (!empty($this->user()->two_factor_secret) && !empty($this->user()->two_factor_recovery_codes) && is_null($this->user()->two_factor_confirmed_at));
                $this->showingRecoveryCodes = !$this->showingConfirmation;
            }
        }
    }

    public function enableTwoFactorAuthentication(EnableTwoFactorAuthentication $enable)
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {

            if (Auth::guard('admin')->check()) {
                $this->ensureAdminPasswordIsConfirmed();
            } else {
                $this->ensurePasswordIsConfirmed();
            }
        }
        $enable(Auth::user());
        $this->showingQrCode = true;
        $this->showingConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        $this->showingRecoveryCodes = !$this->showingConfirmation;
    }

    public function confirmTwoFactorAuthentication(ConfirmTwoFactorAuthentication $confirm)
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
            $this->ensurePasswordIsConfirmed();
        }
        $confirm(Auth::user(), $this->code);
        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = true;
    }

    public function showRecoveryCodes()
    {
        $this->showingRecoveryCodes = true;
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate)
    {
        $generate(Auth::user());
        $this->showingRecoveryCodes = true;
    }

    public function disableTwoFactorAuthentication(DisableTwoFactorAuthentication $disable)
    {
        if (Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {

            $this->ensurePasswordIsConfirmed();
        }
        $disable(Auth::user());
        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = false;
    }

    public function user()
    {
        return Auth::user();
    }

    public function isEnabled()
    {
        return !empty($this->user()->two_factor_secret);
    }


    public function render()
    {
        // Determine if the authenticated user is an admin

        return view('components.admin-two-factor-authentication', [
            'user' => $this->user(),
            'enabled' => $this->isEnabled(),
            'showingQrCode' => $this->showingQrCode,
            'showingConfirmation' => $this->showingConfirmation,
            'showingRecoveryCodes' => $this->showingRecoveryCodes,
            'code' => $this->code,
        ]);
    }

    /**
     * Ensure that the user's password has been recently confirmed.
     *
     * @param  int|null  $maximumSecondsSinceConfirmation
     * @return void
     */


    protected function ensureAdminPasswordIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        if (!$this->adminPasswordIsConfirmed($maximumSecondsSinceConfirmation)) {
            abort(403, 'Admin password confirmation required.');
        }
    }

    protected function adminPasswordIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        return (time() - session('auth.admin_password_confirmed_at', 0)) < ($maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900));
    }

}
