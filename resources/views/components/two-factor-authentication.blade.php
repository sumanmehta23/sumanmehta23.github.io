<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<div class="card">
        <section x-data="twoFA()">
            <header>
                <div class="card-header">
                    <h5>
                        {{ __('Two Factor Authentication') }}
                    </h5>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Add additional security to your account using two factor authentication.') }}
                    </p>
                </div>
            </header>
            <div class="card-body table-card">
                <div name="content ">
                    <h6 class="mt-2 text-gray-700 dark:text-gray-100">
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

                                    <x-text-input id="code" type="text" name="code" class="block mt-1 w-25"
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

                    <div class="gap-3 mt-5 d-flex justify-content-between align-items-center">
                        @if (!$enabled)
                            <x-primary-button type="submit" class="me-3" wire:loading.attr="disabled"
                                @click="enableTwoFactorAuthentication()">
                                {{ __('Enable') }}
                            </x-primary-button>
                        @else
                            @if ($showingRecoveryCodes)
                                <x-primary-button type="button" class="me-3" wire:loading.attr="disabled"
                                    @click="regenerateRecoveryCodes()">
                                    {{ __('Regenerate Recovery Codes') }}
                                </x-primary-button>
                            @elseif ($showingConfirmation)
                                <x-primary-button type="button" class="p-2 me-3" wire:loading.attr="disabled"
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
                    {{-- <div x-show="showPasswordConfirmation">
                        <form @submit.prevent="confirmPassword()">
                            <x-text-input id="password" type="text" name="password" class="block w-1/2 mt-2" autofocus
                                autocomplete="password" x-model="password" x-on:keydown.enter="confirmPassword" />
                            <x-primary-button type="submit" class="mt-4 me-3">
                                {{ __('Confirm Password') }}
                            </x-primary-button>
                        </form>
                    </div> --}}
                </div>
            </div>
        </section>
</div>

<script>
    let twoFA = () => {
        return {
            invalidCode: false,
            // showPasswordConfirmation: false,
            code: '',
            // confirmPassword() {
            //     axios.post('{{ route('two-factor.confirm') }}', {
            //         code: this.code
            //     }).then(response => {
            //         if (response.data.errors == undefined) {
            //             location.reload();
            //         } else {
            //             this.invalidCode = true;
            //         }
            //     }).catch(error => {
            //         console.log(error.response.data);
            //     });
            // },
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
                        window.location.href = '{{ route('confirm_password') }}#two-factor-auth';
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
                    window.location.href = '{{ route('user-profile') }}#two-factor-auth';
                }).catch(error => {
                    if (error.response.data.message !== undefined) {
                        window.location.href = '{{ route('confirm_password') }}';
                        // this.showPasswordConfirmation = true;
                    }
                    console.log(error.response.data);
                });
            },
            regenerateRecoveryCodes() {
                console.log('regenerateRecoveryCodes');
                axios.post('{{ route('two-factor.recovery-codes') }}').then(response => {
                    location.reload();
                    window.location.href = '{{ route('user-profile') }}#two-factor-auth';
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
</script>
