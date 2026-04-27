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
            <div class="auth-main" >
                <div class="auth-wrapper v3" >
                    <div class="auth-form" style="justify-content: flex-start;">
                        <div class="auth-header row" >
                            <div class="my-1 col" ><a href="/login"><img
                                        src="/{{ $settings['admin_sidebar_logo'] }}" alt="Logo"
                                        style="height: 8vh;"></a></div>
                        </div>
                        <form method="post">
                            @csrf
                            <div class="my-5 card" >
                                <div class="card-body" >
                                    <div class="mb-3 d-flex justify-content-between align-items-end" >
                                        <h3 class="mb-2" ><b>Forgot Password</b></h3><a href="/login"
                                            class="mb-2 link-primary" >Back to Login</a>
                                    </div>
                                    @if (isset($msg))
                                        {!! $msg !!}
                                    @endif
                                    <p class="mt-2 text-sm text-muted" > If you forgot your password, no worries! We’ll send you a quick email with steps to reset it.</p>
                                    <div class="mb-3 form-group" >
                                        <label class="form-label" >Email Address</label>
                                        <input name="txtemail" type="email" class="form-control" id="floatingInput"
                                            placeholder="Email Address" required>
                                    </div>
                                    <div class="mt-3 d-grid" >
                                        <button class="btn btn-primary" type="submit" name="btn-submit">Send Reset
                                            Link</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="auth-footer">
                            <p class="m-0 text-center w-100 mobile-left-align" style="font-size: 11px;"> By signing up, I acknowledge that I have read, understood, and agree to <br> the Client Agreement and consent to LQH Markets contacting me with <br>relevant updates and information <br><br> By registering you agree to our <a
                                    href="https://www.lqhmarkets.com/privacy-policy" class="text-success"
                                    target="_blank" rel="noopener noreferrer">Privacy Policy</a>, <a
                                    href="https://www.lqhmarkets.com/terms-conditions" class="text-success"
                                    target="_blank" rel="noopener noreferrer">Client Agreement</a> &amp; <a
                                    href="https://www.lqhmarkets.com/risk-disclaimer" class="text-success"
                                    target="_blank" rel="noopener noreferrer">Trading Risk Warning</a>.</p>
                        </div>
                    </div>
                    <div  class="auth-sidecontent"
                        style="background: linear-gradient(45deg, rgb(25, 24, 76), rgb(var(--bs-primary-rgb))) !important;">
                        <div class="p-3 text-center px-lg-5">
                            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <div class="carousel-item active"><img src="/assets/images/acc-1.png" alt="user-image"
                                            class="mb-3 hei-150">
                                        <h5 class="mb-0 text-white">Regulatory Excellence</h5>
                                        <p class="text-white text-opacity-50">Compliance Assurance</p>
                                        <div class="my-4 star f-20"><i class="fas fa-star text-warning"></i><i
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
                                            class="mb-3 hei-150">
                                        <h5 class="mb-0 text-white">Transparent Pricing Policy</h5>
                                        <p class="text-white text-opacity-50">Clear Cost Commitment</p>
                                        <div class="my-4 star f-20"><i class="fas fa-star text-warning"></i><i
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
                                            class="mb-3 hei-150">
                                        <h5 class="mb-0 text-white">Swift and Precise Execution</h5>
                                        <p class="text-white text-opacity-50">Precision Trading</p>
                                        <div class="my-4 star f-20"><i class="fas fa-star text-warning"></i><i
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
                                            class="mb-3 hei-150">
                                        <h5 class="mb-0 text-white">Competitive Spreads</h5>
                                        <p class="text-white text-opacity-50">Cost Efficiency</p>
                                        <div class="my-4 star f-20"><i class="fas fa-star text-warning"></i><i
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
                                <div class="mt-3 carousel-indicators position-relative"><button type="button"
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
    <script>
            window.intercomSettings = {
                api_base: "https://api-iam.intercom.io",
                app_id: "hcaolnkq"
            };
        </script>

        <script>
            (function () {
                var w = window;
                var ic = w.Intercom;
                if (typeof ic === "function") {
                    ic('reattach_activator');
                    ic('update', w.intercomSettings);
                } else {
                    var d = document;
                    var i = function () { i.c(arguments); };
                    i.q = [];
                    i.c = function (args) { i.q.push(args); };
                    w.Intercom = i;
                    var l = function () {
                        var s = d.createElement('script');
                        s.type = 'text/javascript';
                        s.async = true;
                        s.src = 'https://widget.intercom.io/widget/hcaolnkq';
                        var x = d.getElementsByTagName('script')[0];
                        x.parentNode.insertBefore(s, x);
                    };
                    if (document.readyState === 'complete') {
                        l();
                    } else if (w.attachEvent) {
                        w.attachEvent('onload', l);
                    } else {
                        w.addEventListener('load', l, false);
                    }
                }
            })();

            // 👇 This clears cached user and reboots as anonymous when Intercom button is clicked
            document.addEventListener('click', function (e) {
                if (e.target.closest('#intercom-button, .intercom-launcher, [class*="intercom"]')) {
                    window.Intercom('shutdown');
                    window.Intercom('boot', {
                        api_base: "https://api-iam.intercom.io",
                        app_id: "hcaolnkq"
                    });
                }
            });
        </script>
@endsection
