@extends('layouts.app')
@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <body data-pc-preset="preset-7" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
        <div id="app" data-v-app="">
            <div data-v-dde07c83="" class="auth-main">
                <div data-v-dde07c83="" class="auth-wrapper v3">
                    <div data-v-dde07c83="" class="auth-form">
                        <div data-v-dde07c83="" class="auth-header row">
                            <div data-v-dde07c83="" class="col my-1">
                                <a href="{{ route('login') }}"><img src="{{ asset($settings['admin_sidebar_logo']) }}"
                                        alt="Logo" style="height: 8vh;"></a>
                            </div>
                        </div>
                        <div data-v-dde07c83="" class="card my-3">
                            <div data-v-dde07c83="" class="card-body">
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div data-v-dde07c83="" class="text-center">
                                        <h3 data-v-dde07c83="" class="text-center mb-3">Login</h3>
                                        <p data-v-dde07c83="" class="mb-4 fs-6"> Please log in to access your profile and
                                            manage your trading accounts and settings. </p>
                                    </div>
                                    @if (session('status'))
                                        <div class="alert alert-success">
                                            {{ session('status') }}
                                        </div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger">
                                            {{ session('error') }}
                                        </div>
                                    @endif
                                    <div data-v-dde07c83="" class="row mt-4">
                                        <div data-v-dde07c83="" class="col-12">
                                            <div data-v-dde07c83="" class="form-group">
                                                <label for="email" class="form-label">Email id</label>
                                                <input type="email" name="email" id="email" class="form-control"
                                                    placeholder="Email id" required>
                                            </div>
                                        </div>
                                        <div data-v-dde07c83="" class="col-12">
                                            <div data-v-dde07c83="" class="form-group">
                                                <label for="password" class="form-label">Password</label>
                                                <input type="password" name="password" id="password" class="form-control"
                                                    placeholder="Password" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div data-v-dde07c83="" class="d-flex justify-content-between align-items-center">
                                        <div data-v-dde07c83="" class="form-check">
                                            <input type="checkbox" class="form-check-input" id="customCheckc1">
                                            <label class="form-check-label text-muted" for="customCheckc1">Remember
                                                me?</label>
                                        </div>
                                        <h6 data-v-dde07c83="" class="text-secondary f-w-400 mb-0"><a data-v-dde07c83=""
                                                href="/forgot-password" class="link-primary">Forgot Password?</a></h6>
                                    </div>
                                    <div data-v-dde07c83="" class="row g-3 mt-1">
                                        <div data-v-dde07c83="" class="col-sm-12">
                                            <div data-v-dde07c83="" class="d-grid">
                                                <input type="submit" name="signin" value="Login" class="btn btn-primary">
                                            </div>
                                        </div>
                                        <div data-v-dde07c83="" class="col-sm-12">
                                            <div data-v-dde07c83="" class="d-grid">
                                                <a href="/register" data-v-dde07c83="" class="bg-transparent border btn">
                                                    Register </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div data-v-dde07c83="" class="auth-sidecontent"
                        style="background: linear-gradient(45deg, rgb(25, 24, 76), rgb(var(--bs-primary-rgb))) !important;">
                        <div class="p-3 px-lg-5 text-center">
                            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <img src="/assets/images/acc-1.png" alt="user-image" class="hei-150 mb-3">
                                        <h5 class="text-white mb-0">Regulatory Excellence</h5>
                                        <p class="text-white text-opacity-50">Compliance Assurance</p>
                                        <div class="star f-20 my-4">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        </div>
                                        <p class="text-white">
                                            With meticulous attention to regulatory standards,
                                            {{ $settings['admin_title'] }}
                                            guarantees compliance assurance,
                                            fostering transparency and confidence among traders with a commitment to ethical
                                            conduct and integrity.
                                        </p>
                                    </div>
                                    <div class="carousel-item">
                                        <img src="/assets/images/ben-02.png" alt="user-image" class="hei-150 mb-3">
                                        <h5 class="text-white mb-0">Transparent Pricing Policy</h5>
                                        <p class="text-white text-opacity-50">Clear Cost Commitment</p>
                                        <div class="star f-20 my-4">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        </div>
                                        <p class="text-white">
                                            At {{ $settings['admin_title'] }}, transparency is paramount. Our pricing policy
                                            ensures clarity and fairness,
                                            empowering traders with transparent pricing structures and no hidden fees for a
                                            seamless trading experience.
                                        </p>
                                    </div>
                                    <div class="carousel-item">
                                        <img src="/assets/images/ben-03.png" alt="user-image" class="hei-150 mb-3">
                                        <h5 class="text-white mb-0">Swift and Precise Execution</h5>
                                        <p class="text-white text-opacity-50">Precision Trading</p>
                                        <div class="star f-20 my-4">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        </div>
                                        <p class="text-white">
                                            Experience lightning-fast trade execution with {{ $settings['admin_title'] }}.
                                            Our
                                            advanced trading infrastructure ensures
                                            swift and precise order processing, enabling traders to capitalize on market
                                            opportunities instantly.
                                        </p>
                                    </div>
                                    <div class="carousel-item">
                                        <img src="/assets/images/ben-04.png" alt="user-image" class="hei-150 mb-3">
                                        <h5 class="text-white mb-0">Competitive Spreads</h5>
                                        <p class="text-white text-opacity-50">Cost Efficiency</p>
                                        <div class="star f-20 my-4">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        </div>
                                        <p class="text-white">
                                            Gain a competitive edge with {{ $settings['admin_title'] }}'s tight spreads. Our
                                            low-cost advantage ensures competitive pricing
                                            on all trading instruments, allowing traders to maximize profitability and
                                            minimize trading costs.
                                        </p>
                                    </div>
                                </div>
                                <div class="carousel-indicators position-relative mt-3">
                                    <button type="button" data-bs-target="#carouselExampleIndicators"
                                        data-bs-slide-to="0" class="active" aria-current="true"
                                        aria-label="Slide 1"></button>
                                    <button type="button" data-bs-target="#carouselExampleIndicators"
                                        data-bs-slide-to="1" aria-label="Slide 2"></button>
                                    <button type="button" data-bs-target="#carouselExampleIndicators"
                                        data-bs-slide-to="2" aria-label="Slide 3"></button>
                                    <button type="button" data-bs-target="#carouselExampleIndicators"
                                        data-bs-slide-to="3" aria-label="Slide 4"></button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </body>

    </html>
@endsection
