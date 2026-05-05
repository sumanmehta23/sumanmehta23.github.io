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

        <!-- Loading State -->
        <div x-show="isLoading" class="mt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Loading...') }}</p>
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
                    {!! auth()->user()->twoFactorQrCodeSvg() !!}
                </div>

                <div class="max-w-xl mt-4 text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-semibold">
                        {{ __('Setup Key') }}: {{ decrypt(auth()->user()->two_factor_secret) }}
                    </p>
                </div>

                @if ($showingConfirmation)
                    <div class="mt-4">
                        <label for="code">Code</label>

                        <input id="code" type="text" name="code" class="block mt-1 form-control w-25"
                            inputmode="numeric" autofocus autocomplete="one-time-code" x-model="code"
                            x-on:keydown.enter="confirmTwoFactorAuthentication()" />

                        <p x-show="invalidCode" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display: none;">
                            {{ __('The provided two factor authentication code was invalid.') }}
                        </p>
                    </div>
                @endif
            @endif

            @if ($showingRecoveryCodes)
                <div class="max-w-xl mt-4 text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-semibold">
                        {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
                    </p>
                </div>

                <div class="flex flex-wrap max-w-xl gap-2 px-4 py-4 mt-4 font-mono text-sm bg-gray-100 rounded-lg dark:bg-gray-900 dark:text-gray-100">
                    @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                        <div>{{ $code }}</div>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="gap-3 mt-5 mb-5 d-flex justify-content-between align-items-center">
            @if (!$enabled)
                <Button type="button" class="btn btn-primary me-3"
                    @click="enableTwoFactorAuthentication()"
                    x-show="!showPasswordConfirmation && !isLoading"
                    x-cloak>
                    {{ __('Enable') }}
                </Button>
            @else
                @if ($showingRecoveryCodes)
                    <Button type="button" class="btn btn-primary me-3"
                        @click="regenerateRecoveryCodes()">
                        {{ __('Regenerate Recovery Codes') }}
                    </Button>
                @elseif ($showingConfirmation)
                    <Button type="button" class="btn btn-primary me-3"
                        @click="confirmTwoFactorAuthentication()">
                        {{ __('Confirm') }}
                    </Button>
                @else
                    <Button class="me-3" @click="showRecoveryCodes()">
                        {{ __('Show Recovery Codes') }}
                    </Button>
                @endif

                @if ($showingConfirmation)
                    <Button class="btn btn-primary me-3" @click="cancelEnable()">
                        {{ __('Cancel') }}
                    </Button>
                @else
                    <Button class="btn btn-primary" @click="disableTwoFactorAuthentication()">
                        {{ __('Disable') }}
                    </Button>
                @endif

            @endif
        </div>

        <!-- Password Confirmation Modal -->
        <div id='password_confirmation' x-show="showPasswordConfirmation" x-cloak style="display: none;">
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4 dark:bg-gray-800">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="modal-title">
                                        {{ __('Confirm Password') }}
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('Please confirm your password before continuing.') }}
                                        </p>
                                        <div class="mt-4">
                                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Password') }}</label>
                                            <input id="password" class="block mt-1 form-control w-100" type="password" name="password" required autocomplete="current-password" x-model="password">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse dark:bg-gray-700">
                            <Button type="button" class="btn btn-primary me-3" @click="confirmPassword()" :disabled="isLoading">
                                <span x-show="!isLoading">{{ __('Confirm') }}</span>
                                <span x-show="isLoading">{{ __('Processing...') }}</span>
                            </Button>
                            <Button type="button" class="btn btn-secondary" @click="showPasswordConfirmation = false" :disabled="isLoading">
                                {{ __('Cancel') }}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
    let twoFA = () => {
        return {
            invalidCode: false,
            showPasswordConfirmation: false,
            code: '',
            password: '',
            enableButtonVisible: true,
            isLoading: false,
            errorMessage: '',
            twoFactorTabHash: '#two-factor-auth',

            reloadToTwoFactorTab() {
                window.location.hash = this.twoFactorTabHash;
                window.location.reload();
            },

            async confirmPassword() {
                if (this.isLoading) return;
                this.isLoading = true;
                this.errorMessage = '';

                try {
                    const formData = new FormData();
                    formData.append("password", this.password);

                    const response = await axios.post("{{ route('admin.password.confirm') }}", formData);
                    console.log("Password confirmed:", response.data);

                    this.showPasswordConfirmation = false;
                    this.password = '';

                    // Now enable 2FA after password confirmation
                    await this.enableTwoFactorAuthentication();
                } catch (error) {
                    console.error("Password confirmation error:", error);
                    let errorMessage = 'Something went wrong. Please try again.';
                    if (error.response && error.response.data) {
                        if (error.response.data.message) {
                            errorMessage = error.response.data.message;
                        } else if (error.response.data.error) {
                            errorMessage = error.response.data.error;
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Authentication Failed',
                        text: errorMessage,
                    });
                } finally {
                    this.isLoading = false;
                }
            },

            async enableTwoFactorAuthentication() {
                console.log('enableTwoFactorAuthentication');
                this.isLoading = true;
                this.errorMessage = '';

                try {
                    const response = await axios.post("{{ route('admin.two-factor.enable') }}");
                    console.log("2FA enabled:", response.data);

                    if (response.data.success) {
                        // Reload to show QR code and confirmation
                        this.reloadToTwoFactorTab();
                    }
                } catch (error) {
                    console.error("Enable 2FA error:", error);

                    if (error.response && error.response.status === 403) {
                        // Password confirmation required
                        this.showPasswordConfirmation = true;
                        this.enableButtonVisible = false;
                    } else if (error.response && error.response.data && error.response.data.message) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.response.data.message,
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to enable two factor authentication. Please try again.',
                        });
                    }
                } finally {
                    this.isLoading = false;
                }
            },

            async confirmTwoFactorAuthentication() {
                console.log('confirmTwoFactorAuthentication');
                this.isLoading = true;
                this.invalidCode = false;
                this.errorMessage = '';

                try {
                    const response = await axios.post("{{ route('admin.two-factor.confirm') }}", {
                        code: this.code
                    });

                    console.log("2FA confirmed:", response.data);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Two factor authentication has been confirmed successfully!',
                    }).then(() => {
                        this.reloadToTwoFactorTab();
                    });
                } catch (error) {
                    console.error("Confirm 2FA error:", error);

                    if (error.response && error.response.status === 403) {
                        // Password confirmation required
                        this.showPasswordConfirmation = true;
                    } else if (error.response && error.response.data && error.response.data.message) {
                        this.invalidCode = true;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.response.data.message,
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to confirm two factor authentication. Please try again.',
                        });
                    }
                } finally {
                    this.isLoading = false;
                }
            },

            async regenerateRecoveryCodes() {
                console.log('regenerateRecoveryCodes');
                this.isLoading = true;

                try {
                    const response = await axios.post("{{ route('admin.two-factor.recovery-codes') }}");
                    console.log("Recovery codes regenerated:", response.data);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Recovery codes have been regenerated!',
                    }).then(() => {
                        this.reloadToTwoFactorTab();
                    });
                } catch (error) {
                    console.error("Regenerate recovery codes error:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to regenerate recovery codes. Please try again.',
                    });
                } finally {
                    this.isLoading = false;
                }
            },

            async showRecoveryCodes() {
                console.log('showRecoveryCodes');
                this.isLoading = true;

                try {
                    const response = await axios.get("{{ route('admin.two-factor.recovery-codes.show') }}");
                    console.log("Recovery codes:", response.data);

                    this.reloadToTwoFactorTab();
                } catch (error) {
                    console.error("Show recovery codes error:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to retrieve recovery codes. Please try again.',
                    });
                } finally {
                    this.isLoading = false;
                }
            },

            async disableTwoFactorAuthentication() {
                console.log('disableTwoFactorAuthentication');

                const result = await Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to disable two factor authentication?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, disable it!',
                    cancelButtonText: 'Cancel'
                });

                if (!result.isConfirmed) return;

                this.isLoading = true;

                try {
                    const response = await axios.delete("{{ route('admin.two-factor.disable') }}");
                    console.log("2FA disabled:", response.data);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Two factor authentication has been disabled!',
                    }).then(() => {
                        this.reloadToTwoFactorTab();
                    });
                } catch (error) {
                    console.error("Disable 2FA error:", error);

                    if (error.response && error.response.status === 403) {
                        // Password confirmation required
                        this.showPasswordConfirmation = true;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to disable two factor authentication. Please try again.',
                        });
                    }
                } finally {
                    this.isLoading = false;
                }
            },

            async cancelEnable() {
                // Cancel the 2FA setup by disabling it
                try {
                    await axios.delete("{{ route('admin.two-factor.disable') }}");
                    this.reloadToTwoFactorTab();
                } catch (error) {
                    console.error("Cancel enable error:", error);
                    this.reloadToTwoFactorTab();
                }
            }
        }
    }
</script>
