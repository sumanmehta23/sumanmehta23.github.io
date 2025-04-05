@extends('layouts.admin')
@section('content')
    <body>
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
                                                                <div id="qoutescarouselIndicators"
                                                                    class="carousel slide pointer-event"
                                                                    data-bs-ride="carousel">
                                                                    <div class="carousel-indicators"><button type="button"
                                                                            data-bs-target="#qoutescarouselIndicators"
                                                                            data-bs-slide-to="0" class="bg-primary"
                                                                            aria-label="Slide 1"></button><button
                                                                            type="button"
                                                                            data-bs-target="#qoutescarouselIndicators"
                                                                            data-bs-slide-to="1" aria-label="Slide 2"
                                                                            class="bg-primary"></button><button
                                                                            type="button"
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
                                                    <form method="POST" action="{{ route('admin.verify_two_factor_auth') }}">
                                                        @csrf
                                                        <div class="p-4 p-lg-5">
                                                            <div>
                                                                <h5 class="text-primary">Verify 2FA</h5>
                                                                <p class="text-muted">Enter your authentication or recovery code.</p>
                                                            </div>

                                                            @if (session('error'))
                                                                <div>
                                                                    <strong class="text-danger">{{ session('error') }}</strong>
                                                                </div>
                                                            @endif

                                                            {{-- Toggle link --}}
                                                            <div class="form-group mt-4">
                                                                <a href="#" id="toggle_mode_link" class="text-primary">Use Recovery Code</a>
                                                            </div>

                                                            {{-- Current Mode Display (for debugging or UX) --}}
                                                            <div class="form-group mt-2" hidden>
                                                                <small>Mode: <span id="mode-display" class="fw-bold">auth</span></small>
                                                            </div>

                                                            {{-- Authenticator Code Field --}}
                                                            <div id="auth_code_field" class="form-group mt-3">
                                                                <label for="code" class="form-label">Authenticator Code</label>
                                                                <input id="code" type="number" class="form-control" name="code" autofocus>
                                                            </div>

                                                            {{-- Recovery Code Field --}}
                                                            <div id="recovery_code_field" class="form-group mt-3" style="display: none;">
                                                                <label for="recovery_code" class="form-label">Recovery Code</label>
                                                                <input id="recovery_code" type="text" class="form-control" name="recovery_code">
                                                            </div>

                                                            <div class="form-group pt-4">
                                                                <input type="submit" class="btn btn-dark w-100" value="Login">
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>y
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('admin_assets/assets/admin_files/jquery.min.js') }}"></script>
        <script src="{{ asset('admin_assets/assets/admin_files/sweetalert-2.all.min.js') }}"></script>
    </body>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const authField = document.getElementById('auth_code_field');
            const recoveryField = document.getElementById('recovery_code_field');
            const toggleLink = document.getElementById('toggle_mode_link');
            const modeDisplay = document.getElementById('mode-display');

            // Add hidden mode input
            const form = document.querySelector('form');
            const modeInput = document.createElement('input');
            modeInput.type = 'hidden';
            modeInput.name = 'mode';
            modeInput.value = 'auth'; // default
            form.appendChild(modeInput);

            function setMode(mode) {
                if (mode === 'auth') {
                    authField.style.display = 'block';
                    recoveryField.style.display = 'none';
                    toggleLink.textContent = 'Use Recovery Code';
                } else {
                    authField.style.display = 'none';
                    recoveryField.style.display = 'block';
                    toggleLink.textContent = 'Use Authenticator Code';
                }
                modeInput.value = mode;
                modeDisplay.textContent = mode;
            }

            toggleLink.addEventListener('click', (e) => {
                e.preventDefault();
                const newMode = modeInput.value === 'auth' ? 'recovery' : 'auth';
                setMode(newMode);
            });

            // Initialize
            setMode('auth');
        });
    </script>

@endsection
