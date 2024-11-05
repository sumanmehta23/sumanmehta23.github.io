@extends('layouts.app')
@section('content')
    <style>
        :root,
        [data-pc-preset=preset-7],
        [data-pc-preset=preset-7] * {
            --primary-color: {{ $settings['sidebar_color'] }} !important;
            --bs-btn-active-bg: {{ $settings['sidebar_color'] }} !important;
            --bs-primary: {{ $settings['sidebar_color'] }} !important;
            --bs-btn-bg: {{ $settings['sidebar_color'] }} !important;
            --bs-btn-hover-bg: {{ $settings['sidebar_color'] }} !important;
            --bs-link-color-rgb: {{ $settings['sidebar_color'] }} !important;
            --bs-primary-rgb: {{ hexToRGB($settings['sidebar_color']) }} !important;
            --primary-rgb: {{ hexToRGB($settings['sidebar_color']) }} !important;
        }

        [data-pc-preset=preset-7] .link-primary {
            color: {{ $settings['sidebar_color'] }} !important;
        }

        body form [data-pc-preset=preset-7] .link-primary,
        form [data-pc-preset=preset-7] .link-primary,
        [data-pc-preset=preset-7] .link-primary:focus,
        [data-pc-preset=preset-7] .link-primary:hover {
            color: var(--bs-primary) !important;
        }
    </style>

    <body data-pc-preset="preset-7" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast=""
        data-pc-theme="light" style="padding-right: 0px;">
        <div id="app" data-v-app="">
            <div class="auth-main" data-v-dde07c83="">
                <div class="auth-wrapper v3" data-v-dde07c83="">
                    <div class="auth-form" data-v-97e32e5a="">
                        <div class="auth-header row" data-v-dde07c83="">
                            <div class="col my-1" data-v-dde07c83=""><a href="/login"><img
                                        src="/{{ $settings['admin_sidebar_logo'] }}" alt="Logo"
                                        style="height: 8vh;"></a></div>
                        </div>
                        <form method="post">
                            @csrf
                            <div class="card my-5" data-v-dde07c83="">
                                <div class="card-body" data-v-dde07c83="">
                                    <div class="d-flex justify-content-between align-items-end mb-3" data-v-dde07c83="">
                                        <h3 class="mb-2" data-v-dde07c83=""><b>Forgot Password</b></h3><a href="/login"
                                            class="link-primary mb-2" data-v-dde07c83="">Back to Login</a>
                                    </div>
                                    @if (isset($msg))
                                        {!! $msg !!}
                                    @endif
                                    <p class="mt-2 text-sm text-muted" data-v-dde07c83="">If you forgot your password, we’ll
                                        email you instructions to reset your password.</p>
                                    <div class="form-group mb-3" data-v-dde07c83="">
                                        <label class="form-label" data-v-dde07c83="">Email Address</label>
                                        <input name="txtemail" type="email" class="form-control" id="floatingInput"
                                            placeholder="Email Address" required>
                                    </div>
                                    <div class="d-grid mt-3" data-v-dde07c83="">
                                        <button class="btn btn-primary" type="submit" name="btn-submit">Send Reset
                                            Link</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="auth-footer" data-v-97e32e5a="">
                            <p class="m-0 w-100 text-center" style="font-size: 11px;" data-v-97e32e5a=""> By signing up, I
                                acknowledge that I have read, understood and agree to the Client Agreement <br
                                    data-v-97e32e5a="">and give my consent for {{ $settings['admin_title'] }} to contact me for
                                marketing purposes. <br data-v-97e32e5a=""> By registering you agree to our <a
                                    href="#" data-v-97e32e5a="">Privacy Policy</a>, <a href="#"
                                    data-v-97e32e5a="">Client Agreement</a>&amp; <a href="#"
                                    data-v-97e32e5a="">Trading Risk Warning</a>.</p>
                        </div>
                    </div>
                    <div data-v-dde07c83="" class="auth-sidecontent"
                        style="background: linear-gradient(45deg, rgb(25, 24, 76), rgb(var(--bs-primary-rgb))) !important;">
                        <div class="p-3 px-lg-5 text-center">
                            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <div class="carousel-item active"><img src="/assets/images/acc-1.png" alt="user-image"
                                            class="hei-150 mb-3">
                                        <h5 class="text-white mb-0">Regulatory Excellence</h5>
                                        <p class="text-white text-opacity-50">Compliance Assurance</p>
                                        <div class="star f-20 my-4"><i class="fas fa-star text-warning"></i><i
                                                class="fas fa-star text-warning"></i><i
                                                class="fas fa-star text-warning"></i><i
                                                class="fas fa-star text-warning"></i><i
                                                class="fas fa-star-half-alt text-warning"></i></div>
                                        <p class="text-white"> With meticulous attention to regulatory standards,
                                            <?= $settings['admin_title'] ?> guarantees
                                            compliance assurance, fostering transparency and confidence among traders with a
                                            commitment to
                                            ethical conduct and integrity.
                                        </p>
                                    </div>
                                    <div class="carousel-item"><img src="/assets/images/ben-02.png" alt="user-image"
                                            class="hei-150 mb-3">
                                        <h5 class="text-white mb-0">Transparent Pricing Policy</h5>
                                        <p class="text-white text-opacity-50">Clear Cost Commitment</p>
                                        <div class="star f-20 my-4"><i class="fas fa-star text-warning"></i><i
                                                class="fas fa-star text-warning"></i><i
                                                class="fas fa-star text-warning"></i><i
                                                class="fas fa-star text-warning"></i><i
                                                class="fas fa-star-half-alt text-warning"></i></div>
                                        <p class="text-white"> At <?= $settings['admin_title'] ?>, transparency is paramount. Our pricing
                                            policy ensures
                                            clarity and fairness, empowering traders with transparent pricing structures and
                                            no hidden fees for
                                            a seamless trading experience. </p>
                                    </div>
                                    <div class="carousel-item"><img src="/assets/images/ben-03.png" alt="user-image"
                                            class="hei-150 mb-3">
                                        <h5 class="text-white mb-0">Swift and Precise Execution</h5>
                                        <p class="text-white text-opacity-50">Precision Trading</p>
                                        <div class="star f-20 my-4"><i class="fas fa-star text-warning"></i><i
                                                class="fas fa-star text-warning"></i><i
                                                class="fas fa-star text-warning"></i><i
                                                class="fas fa-star text-warning"></i><i
                                                class="fas fa-star-half-alt text-warning"></i></div>
                                        <p class="text-white"> Experience lightning-fast trade execution with
                                            <?= $settings['admin_title'] ?>.
                                            Our advanced
                                            trading infrastructure ensures swift and precise order processing, enabling
                                            traders to capitalize on
                                            market opportunities instantly. </p>
                                    </div>
                                    <div class="carousel-item"><img src="/assets/images/ben-04.png" alt="user-image"
                                            class="hei-150 mb-3">
                                        <h5 class="text-white mb-0">Competitive Spreads</h5>
                                        <p class="text-white text-opacity-50">Cost Efficiency</p>
                                        <div class="star f-20 my-4"><i class="fas fa-star text-warning"></i><i
                                                class="fas fa-star text-warning"></i><i
                                                class="fas fa-star text-warning"></i><i
                                                class="fas fa-star text-warning"></i><i
                                                class="fas fa-star-half-alt text-warning"></i></div>
                                        <p class="text-white"> Gain a competitive edge with <?= $settings['admin_title'] ?>' tight spreads.
                                            Our low-cost
                                            advantage ensures competitive pricing on all trading instruments, allowing
                                            traders to maximize
                                            profitability and minimize trading costs. </p>
                                    </div>
                                </div>
                                <div class="carousel-indicators position-relative mt-3"><button type="button"
                                        data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"
                                        aria-current="true" aria-label="Slide 1"></button><button type="button"
                                        data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                                        aria-label="Slide 2"></button><button type="button"
                                        data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                                        aria-label="Slide 3"></button><button type="button"
                                        data-bs-target="#carouselExampleIndicators" data-bs-slide-to="3"
                                        aria-label="Slide 4"></button></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
@endsection
