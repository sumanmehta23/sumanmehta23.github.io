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
                                                    <form method="POST" action="{{ route('admin.verify_two_factor_auth') }}"> {{-- <-- specify action --}}
                                                        @csrf
                                                        <div class="p-4 p-lg-5">
                                                            <div>
                                                                <h5 class="text-primary">Verify 2FA</h5>
                                                                <p class="text-muted">Enter Your 2FA Code.</p>
                                                            </div>

                                                            @if (session('msg'))
                                                                <div>
                                                                    <strong class="text-danger">{{ session('msg') }}</strong>
                                                                </div>
                                                            @endif

                                                            @if (session('error'))
                                                                <div>
                                                                    <strong class="text-danger">{{ session('error') }}</strong>
                                                                </div>
                                                            @endif

                                                            <div class="mt-4">
                                                                <div class="card-body">
                                                                    <div class="form-group">
                                                                        <label for="code" class="form-label">Code</label>
                                                                        <input id="code" type="number" class="form-control" name="code" required autofocus>
                                                                    </div>

                                                                    <div class="form-group pt-4">
                                                                        <input type="submit" class="btn btn-dark w-100" value="Login">
                                                                        <button class="btn btn-honor w-100 btn-load" disabled style="display: none;">
                                                                            <span class="d-flex align-items-center">
                                                                                <span class="flex-shrink-0 spinner-border" role="status">
                                                                                    <span class="visually-hidden">Logging In...</span>
                                                                                </span>
                                                                                <span class="flex-grow-1 ms-2">Logging In...</span>
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
    </body>
@endsection
