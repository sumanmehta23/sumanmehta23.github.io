
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<section x-data="twoFA()">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Two Factor Authentication') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Add additional security to your account using two factor authentication.') }}
        </p>
    </header>
    <div name="content ">
        <h6 class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">
            {{-- {{ dd($enabled) }} --}}
            @if ($enabled)
                @if ($showingConfirmation)
                    {{ __('Finish enabling two factor authentication.') }}
                @else
                    {{ __('You have enabled two factor authentication.') }}
                @endif
            @else
                {{ __('You have not enabled two factor authentication.') }}
            @endif
        </h6>

        <div class="max-w-xl mt-3 text-sm text-gray-600 dark:text-gray-400">
            <p>
                {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
            </p>
        </div>
        @if ($enabled)
            @if ($showingQrCode)
                <div class="max-w-xl mt-4 text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-semibold">
                        @if ($showingConfirmation)
                            {{ __('To finish enabling two factor authentication, scan the following QR code using your phone\'s authenticator application or enter the setup key and provide the generated OTP code.') }}
                        @else
                            {{ __('Two factor authentication is now enabled. Scan the following QR code using your phone\'s authenticator application or enter the setup key.') }}
                        @endif
                    </p>
                </div>

                <div class="inline-block p-2 mt-4">
                    {!! $user->twoFactorQrCodeSvg() !!}
                </div>

                <div class="max-w-xl mt-4 text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-semibold">
                        {{ __('Setup Key') }}: {{ decrypt($user->two_factor_secret) }}
                    </p>
                </div>

                @if ($showingConfirmation)
                    <div class="mt-4">
                        <x-input-label for="code" value="{{ __('Code') }}" />

                        <x-text-input id="code" type="text" name="code" class="block w-25 mt-1"
                            inputmode="numeric" autofocus autocomplete="one-time-code" x-model="code"
                            x-on:keydown.enter="confirmTwoFactorAuthentication" />

                        <x-input-error for="code" class="mt-2"
                            messages="The provided two factor authentication code was invalid." x-show="invalidCode" />
                    </div>
                @endif
            @endif

            @if ($showingRecoveryCodes)
                <div class="max-w-xl mt-4 text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-semibold">
                        {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
                    </p>
                </div>

                <div class="grid max-w-xl gap-1 px-4 py-4 mt-4 font-mono text-sm bg-gray-100 rounded-lg dark:bg-gray-900 dark:text-gray-100">
                    @foreach (json_decode(decrypt($user->two_factor_recovery_codes), true) as $code)
                        <div>{{ $code }}</div>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="mt-5 d-flex justify-content-between align-items-center gap-3">
            @if (!$enabled)
                {{-- <x-primary-button type="submit" class="me-3" wire:loading.attr="disabled"
                    @click="enableTwoFactorAuthentication()">
                    {{ __('Enable') }}
                </x-primary-button> --}}
                <x-primary-button type="submit" class="me-3" wire:loading.attr="disabled"
                    @click="enableTwoFactorAuthentication()" x-show="enableButtonVisible" x-cloak>
                    {{ __('Enable') }}
                </x-primary-button>
            @else
                @if ($showingRecoveryCodes)
                    <x-primary-button type="button" class="me-3" wire:loading.attr="disabled"
                        @click="regenerateRecoveryCodes()">
                        {{ __('Regenerate Recovery Codes') }}
                    </x-primary-button>
                @elseif ($showingConfirmation)
                    <x-primary-button type="button" class="me-3 p-2" wire:loading.attr="disabled"
                        @click="confirmTwoFactorAuthentication()">
                        {{ __('Confirm') }}
                    </x-primary-button>
                @else
                    <x-secondary-button class="me-3" @click="showRecoveryCodes()">
                        {{ __('Show Recovery Codes') }}
                    </x-secondary-button>
                @endif

                @if ($showingConfirmation)
                    <x-secondary-button wire:loading.attr="disabled" class="p-3" @click="disableTwoFactorAuthentication()">
                        {{ __('Cancel') }}
                    </x-secondary-button>
                @else
                    <x-danger-button wire:loading.attr="disabled" @click="disableTwoFactorAuthentication()">
                        {{ __('Disable') }}
                    </x-danger-button>
                @endif

            @endif
        </div>
        <div id='password_confirmation' x-show="showPasswordConfirmation">
            <form id="password-confirm-form" method="POST">
                @csrf
                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-input-field id="password" class="block w-100 mt-1" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <div class="flex justify-end mt-4">
                    <x-primary-button type="button" @click="confirmPassword()">
                        {{ __('Confirm') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</section>
<script>
    let twoFA = () => {
        return {
            invalidCode: false,
            showPasswordConfirmation: false,
            code: '',
            enableButtonVisible: true,
            confirmPassword() {
                let form = document.getElementById("password-confirm-form");
                let formData = new FormData(form);

                axios.post("{{ route('admin.password.confirm') }}", formData)
                    .then(response => {
                        console.log("Password confirmed:", response.data);
                        this.showPasswordConfirmation = false;
                        this.enableButtonVisible = true; // Show Enable button again

                        return axios.post("{{ route('two-factor.enable') }}");
                    })
                    .then(response => {
                        console.log("Two-Factor Authentication enabled:", response.data);
                        window.location.href = '{{ route('user-profile') }}#two-factor-auth';
                    })
                    .catch(error => {
                        console.error("Error:", error.response.data);
                        Swal.fire({
                            icon: 'error',
                            title: 'Incorrect Password',
                            text: 'Please enter the correct password to proceed.',
                        });
                    });
            },
            confirmTwoFactorAuthentication() {
                console.log('confirmTwoFactorAuthentication');
                axios.post('{{ route('two-factor.confirm') }}', {
                    code: this.code
                }).then(response => {
                    if (response.data.errors == undefined) {
                        location.reload();
                        window.location.href = '{{ route('user-profile') }}#two-factor-auth';
                    } else {
                        this.invalidCode = true;
                    }
                }).catch(error => {
                    console.log(error.response.data);

                    if(error.response.data.message == "Password confirmation required."){
                        window.location.href = '{{ route('confirm_password') }}';
                    }else{
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.response.data.message || 'Something went wrong. Please try again.',
                        });
                    }
                });
            },
            enableTwoFactorAuthentication() {
                console.log('enableTwoFactorAuthentication');
                axios.post('{{ route('two-factor.enable') }}').then(response => {
                    location.reload();
                    window.location.href = '{{ route('user-profile') }}';
                }).catch(error => {
                    console.log(error.response.data.message);
                    if (error.response.data.message === "Unauthenticated.") {
                        this.showPasswordConfirmation = true;
                        this.$nextTick(() => {
                            this.enableButtonVisible = false; // Move it inside nextTick
                        });
                    } else {
                        window.location.href = '{{ route('admin.ui-settings.view') }}';
                    }
                });
            },
            regenerateRecoveryCodes() {
                console.log('regenerateRecoveryCodes');
                axios.post('{{ route('two-factor.recovery-codes') }}').then(response => {
                    location.reload();
                    window.location.href = '{{ route('user-profile') }}';
                }).catch(error => {
                    console.log(error.response.data);
                    window.location.href = '{{ route('confirm_password') }}';
                });
            },
            showRecoveryCodes() {
                console.log('showRecoveryCodes');
                axios.get('{{ route('two-factor.recovery-codes') }}').then(response => {
                    location.reload();
                }).catch(error => {
                    console.log(error.response.data);
                });
            },
            disableTwoFactorAuthentication() {
                console.log('disableTwoFactorAuthentication');
                axios.delete('{{ route('two-factor.disable') }}', {
                    action: 'cancel'
                }).then(response => {
                    location.reload();
                }).catch(error => {
                    console.log(error.response.data);
                    location.reload();
                    window.location.href = '{{ route('confirm_password') }}#two-factor-auth';
                });
            },
        }
    }
    document.addEventListener("DOMContentLoaded", function () {
        let form = document.getElementById("password-confirm-form");

        form.addEventListener("submit", function (event) {
            event.preventDefault();
            twoFA().confirmPassword();
        });
    });

</script>
