@extends('layouts.admin')
@section('content')
    <div id="app" data-v-app="">
        <div id="layout-wrapper">
            <div id="app" class="login-page">
                <div
                    class="py-5 auth-page-wrapper auth-bg-cover d-flex justify-content-center align-items-center min-vh-100">
                    <div class="bg-overlay"></div>
                    <div class="overflow-hidden auth-page-content pt-lg-5">
                        <div class="container">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="overflow-hidden border-0 card card-bg-fill card-border-effect-none">
                                        <div class="row g-0">
                                            <div class="col-lg-6">
                                                <div class="p-4 p-lg-5 auth-one-bg h-100">
                                                    <div class="bg-overlay"></div>
                                                    <div class="position-relative h-100 d-flex flex-column">
                                                        <div class="mb-4">
                                                            <a href="{{ url('/admin/login') }}" class="d-block">
                                                                <img src="{{ asset($settings['admin_sidebar_logo']) }}"
                                                                    alt="" height="70">
                                                            </a>
                                                        </div>
                                                        <div class="mt-auto">
                                                            <div class="mb-3"><i
                                                                    class="ri-double-quotes-l display-4 text-success"></i>
                                                            </div>
                                                            <div class="pb-5 text-center text-grey-darken-2">
                                                                <p class="fs-15 fst-italic">"Create a strong password to
                                                                    secure your admin account and protect sensitive data."
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <form method="POST" action="{{ route('admin.reset-password') }}">
                                                    @csrf
                                                    <div class="p-4 p-lg-5">
                                                        <div>
                                                            <h5 class="text-primary">Reset Your Password</h5>
                                                            <p class="text-muted">Enter your new password below.
                                                            </p>
                                                        </div>

                                                        @if (session('success'))
                                                            <div class="alert alert-success alert-dismissible fade show"
                                                                role="alert">
                                                                {{ session('success') }}
                                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                                    aria-label="Close"></button>
                                                            </div>
                                                        @endif

                                                        @if (session('error'))
                                                            <div class="alert alert-danger alert-dismissible fade show"
                                                                role="alert">
                                                                {{ session('error') }}
                                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                                    aria-label="Close"></button>
                                                            </div>
                                                        @endif

                                                        @if ($errors->any())
                                                            <div class="alert alert-danger alert-dismissible fade show"
                                                                role="alert">
                                                                <ul class="mb-0">
                                                                    @foreach ($errors->all() as $error)
                                                                        <li>{{ $error }}</li>
                                                                    @endforeach
                                                                </ul>
                                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                                    aria-label="Close"></button>
                                                            </div>
                                                        @endif

                                                        <div class="mt-4">
                                                            <div class="card-body">
                                                                <input type="hidden" name="id" value="{{ $id }}">
                                                                <input type="hidden" name="code" value="{{ $code }}">

                                                                <div class="form-group">
                                                                    <label for="password" class="form-label">New
                                                                        Password</label>
                                                                    <input id="password" type="password"
                                                                        class="form-control @error('password') is-invalid @enderror"
                                                                        name="password" required autofocus>
                                                                    <small class="form-text text-muted d-block mt-2">
                                                                        Password must be at least 8 characters long.
                                                                    </small>
                                                                    @error('password')
                                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                                    @enderror
                                                                </div>

                                                                <div class="form-group mt-3">
                                                                    <label for="password_confirmation"
                                                                        class="form-label">Confirm Password</label>
                                                                    <input id="password_confirmation" type="password"
                                                                        class="form-control @error('password_confirmation') is-invalid @enderror"
                                                                        name="password_confirmation" required>
                                                                    @error('password_confirmation')
                                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                                    @enderror
                                                                </div>

                                                                <div class="form-group mt-4">
                                                                    <button type="submit" class="btn btn-dark w-100">
                                                                        Reset Password
                                                                    </button>
                                                                </div>

                                                                <div class="form-group text-center mt-3">
                                                                    <p class="mb-0">
                                                                        <a href="{{ route('admin.login') }}"
                                                                            class="text-primary fw-bold">Back to Login</a>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('admin_assets/assets/admin_files/jquery.min.js') }}"></script>
    <script src="{{ asset('admin_assets/assets/admin_files/sweetalert-2.all.min.js') }}"></script>

    @include('components.google-translate')

    <!-- Visible language dropdown for login page -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css">
    <div style="position: fixed; top: 10px; right: 10px; z-index: 2000;">
        @include('components.language-dropdown', [
            'selectId' => 'custom_translate_select_login',
            'flagPreviewId' => 'flag-preview-login'
        ])
        </div>
@endsection
