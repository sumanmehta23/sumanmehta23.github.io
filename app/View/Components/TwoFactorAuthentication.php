<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isNull;


class TwoFactorAuthentication extends Component
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
            $this->ensurePasswordIsConfirmed();
        }
        $enable(Auth::user());
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
        $confirm(Auth::user(), $code);
        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = true;

        return true;
    }

    public function showRecoveryCodes()
    {
        $this->showingRecoveryCodes = true;
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate)
    {
        $generate(Auth::user());
        $this->showingRecoveryCodes = true;

        return true;
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

        return true;
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

        return view('components.two-factor-authentication', [
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
    protected function ensurePasswordIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        $this->passwordIsConfirmed($maximumSecondsSinceConfirmation) ? null : abort(403);
    }
    protected function passwordIsConfirmed($maximumSecondsSinceConfirmation = null)
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        return (time() - session('auth.password_confirmed_at', 0)) < $maximumSecondsSinceConfirmation;
    }
}
