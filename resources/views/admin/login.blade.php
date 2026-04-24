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
                                                            <a href="{{ $settings['main_website_url'] ?? '#' }}" class="d-block">
                                                                <img src="{{ asset($settings['admin_sidebar_logo']) }}"
                                                                    alt="" height="70">
                                                            </a>
                                                        </div>
                                                        <div class="mt-auto">
                                                            <div class="mb-3"><i
                                                                    class="ri-double-quotes-l display-4 text-success"></i>
                                                            </div>
                                                            <div id="qoutescarouselIndicators"
                                                                class="carousel slide pointer-event"
                                                                data-bs-ride="carousel">
                                                                <div class="carousel-indicators"><button type="button"
                                                                        data-bs-target="#qoutescarouselIndicators"
                                                                        data-bs-slide-to="0" class="bg-primary"
                                                                        aria-label="Slide 1"></button><button type="button"
                                                                        data-bs-target="#qoutescarouselIndicators"
                                                                        data-bs-slide-to="1" aria-label="Slide 2"
                                                                        class="bg-primary"></button><button type="button"
                                                                        data-bs-target="#qoutescarouselIndicators"
                                                                        data-bs-slide-to="2" aria-label="Slide 3"
                                                                        class="bg-primary active"
                                                                        aria-current="true"></button></div>
                                                                <div
                                                                    class="pb-5 text-center carousel-inner text-grey-darken-2">
                                                                    <div class="carousel-item">
                                                                        <p class="fs-15 fst-italic">"Welcome to your
                                                                            gateway for client management, where you can
                                                                            access comprehensive client profiles, track
                                                                            interactions, and optimize your brokerage
                                                                            services."</p>
                                                                    </div>
                                                                    <div class="carousel-item">
                                                                        <p class="fs-15 fst-italic">"Empowering our
                                                                            relationship managers with seamless tools to
                                                                            enhance client engagement and drive
                                                                            successful trading strategies."</p>
                                                                    </div>
                                                                    <div class="carousel-item active">
                                                                        <p class="fs-15 fst-italic">"Log in to access
                                                                            your dashboard, manage client accounts, and
                                                                            leverage advanced analytics to provide
                                                                            tailored support and insights."</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <form method="POST">
                                                    @csrf <!-- CSRF protection -->
                                                    <div class="p-4 p-lg-5">
                                                        <div>
                                                            <h5 class="text-primary">Welcome Back!</h5>
                                                            <p class="text-muted">Sign in to continue to Staff Portal.
                                                            </p>
                                                        </div>
                                                        @if (session('msg'))
                                                            <div>
                                                                <strong class="text-danger">{{ session('msg') }}</strong>
                                                            </div>
                                                        @endif
                                                        <div class="mt-4">
                                                            <div class="card-body">
                                                                <div class="form-group">
                                                                    <label for="email" class="form-label">Email</label>
                                                                    <input id="email" type="email" class="form-control"
                                                                        name="username" tabindex="1" required autofocus>
                                                                </div>
                                                                <div class="form-group">
                                                                    <div class="d-block">
                                                                        <label for="password"
                                                                            class="control-label">Password</label>
                                                                        <div class="float-right">
                                                                            <a href="{{ route('admin.forgot-password') }}"
                                                                                class="text-small"> Forgot
                                                                                Password?</a>
                                                                        </div>
                                                                    </div>
                                                                    <input id="password" type="password"
                                                                        class="form-control" name="password" tabindex="2"
                                                                        required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" name="remember"
                                                                            class="form-check-input me-2" tabindex="3"
                                                                            id="remember-me">
                                                                        <label class="form-check-label"
                                                                            for="remember-me">Remember Me</label>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <input type="submit" class="btn btn-dark w-100"
                                                                        name="signin" value="Login">
                                                                    <button class="btn btn-honor w-100 btn-load" disabled
                                                                        style="display: none;">
                                                                        <span class="d-flex align-items-center">
                                                                            <span class="flex-shrink-0 spinner-border"
                                                                                role="status">
                                                                                <span class="visually-hidden">Logging
                                                                                    In...</span>
                                                                            </span>
                                                                            <span class="flex-grow-1 ms-2"> Logging
                                                                                In...</span>
                                                                        </span>
                                                                    </button>
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
    {{-- <div style="position: fixed; top: 10px; right: 10px; z-index: 2000;">
        @include('components.language-dropdown', [
            'selectId' => 'custom_translate_select_login',
            'flagPreviewId' => 'flag-preview-login'
        ])
    </div> --}}
@endsection
