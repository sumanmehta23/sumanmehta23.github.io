@extends('layouts.crm.crm')
@section('content')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<div class="pc-container">
    <div class="pc-content">
        <div class="pb-0 mb-0 page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title h2">
                            <h4 class="mb-0">Password Confirmation</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-auth-layout>
            <div title="Confirm Password">
                <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                </div>

                <form id="password-confirm-form" method="POST" action="{{ route('password.confirm') }}">
                    @csrf
                    <div>
                        <x-input-label for="password" :value="__('Password')" />

                        <x-input-field id="password" class="block mt-1 w-25" type="password" name="password" required
                            autocomplete="current-password" />

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex justify-end mt-4">
                        <x-primary-button>
                            {{ __('Confirm') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </x-auth-layout>
    </div>
</div>
@endsection

{{-- <script>
    document.addEventListener("DOMContentLoaded", function () {
        let form = document.getElementById("password-confirm-form");

        form.addEventListener("submit", function (event) {
            event.preventDefault();

            let formData = new FormData(form);

            axios.post("{{ route('password.confirm') }}", formData)
                .then(response => {
                    console.log("Password confirmed:", response.data);

                    return axios.post("{{ route('two-factor.enable') }}");
                })
                .then(response => {
                    console.log("Two-Factor Authentication enabled:", response.data);

                    window.location.href = '{{ route('user-profile') }}#two-factor-auth-tab';
                })
                .catch(error => {
                    console.error("Error:", error.response.data);
                });
        });
    });
</script> --}}
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let form = document.getElementById("password-confirm-form");

        form.addEventListener("submit", function (event) {
            event.preventDefault();

            let formData = new FormData(form);

            axios.post("{{ route('password.confirm') }}", formData)
                .then(response => {
                    console.log("Password confirmed:", response.data);

                    return axios.post("{{ route('two-factor.enable') }}");
                })
                .then(response => {
                    console.log("Two-Factor Authentication enabled:", response.data);

                    window.location.href = '{{ route('user-profile') }}#two-factor-auth-tab';
                })
                .catch(error => {
                    console.error("Error:", error.response.data);

                    Swal.fire({
                        icon: 'error',
                        title: 'Incorrect Password',
                        text: 'Please enter the correct password to proceed.',
                    });
                });
        });
    });
</script>
