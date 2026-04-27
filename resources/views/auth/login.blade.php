@extends('layouts.app')

@section('content')
<style>
    @media (max-width: 550px) {
        .mob_logo_center {
            display: flex;
            justify-content: center;
            align-items: center;
        }
    }
    font { display: contents !important; color: inherit !important; }
</style>
        <div id="app" data-v-app="">
            <div  class="auth-main">
                <div  class="auth-wrapper v3">
                    <div  class="auth-form">
                        <div  class="auth-header row">
                            <div  class="my-1 col mob_logo_center">
                                <a href="{{ $settings['main_website_url'] ?? '#' }}"><img src="{{ asset($settings['admin_sidebar_logo']) }}"
                                        alt="Logo" style="height: 8vh;"></a>
                            </div>
                        </div>

                        <div  class="my-3 card">
                            <div  class="card-body">
                                <form method="POST" action="{{ route('login') }}" id="login-form">
                                    @csrf
                                    <div  class="text-center">
                                        <h3  class="mb-3 text-center">Login</h3>
                                        <p  class="mb-4 fs-6">Please log in to access your profile, manage your trading accounts, and adjust your settings.</p>
                                    </div>
                                    @if (session('status'))
                                        <div class="alert alert-success">
                                            {!! session('status') !!}
                                        </div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger" id="rate-limit-error">
                                            <span id="error-message">{!! session('error') !!}</span>
                                            @if (session('retry_after'))
                                                <span id="countdown-timer"></span>
                                            @endif
                                        </div>
                                    @endif
                                    <div  class="mt-4 row">
                                        <div  class="col-12">
                                            <div  class="form-group">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" name="email" id="email" class="form-control"
                                                    placeholder="Email" required>
                                            </div>
                                        </div>
                                        <div  class="col-12">
                                            <div  class="form-group">
                                                <label for="password" class="form-label">Password</label>
                                                <input type="password" name="password" id="password" class="form-control"
                                                    placeholder="Password" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div  class="d-flex justify-content-between align-items-center">
                                        <div  class="form-check">
                                            <input type="checkbox" class="form-check-input" id="customCheckc1">
                                            <label class="form-check-label text-muted" for="customCheckc1">Remember Me?</label>
                                        </div>
                                        <h6  class="mb-0 text-secondary f-w-400"><a
                                                href="/forgot-password" class="link-primary">Forgot Password?</a></h6>
                                    </div>
                                    <div  class="mt-1 row g-3">
                                        <div  class="col-sm-12">
                                            <div  class="d-grid">
                                                <input type="submit" name="signin" value="Login" class="btn btn-primary" id="login-submit-btn">
                                            </div>
                                        </div>
                                        <div  class="col-sm-12">
                                            <div  class="d-grid">
                                                <a href="/register"  class="bg-transparent border btn">
                                                    Register </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div data-v-dde07c83="" class="auth-footer">
                            <p data-v-dde07c83="" class="m-0 text-center w-100" style="font-size: 11px;"> By logging in, you confirm that you have read and agree to <?= ($settings['admin_title']) ?>'s <a data-v-dde07c83="" target="_blank" href="https://www.lqhmarkets.com/risk-disclaimer" class="text-success">Risk Disclaimer</a>,
                             <a data-v-dde07c83="" target="_blank" href="https://www.lqhmarkets.com/terms-conditions" class="text-success">Terms & Conditions</a>, and <a data-v-dde07c83="" target="_blank" href="https://www.lqhmarkets.com/privacy-policy" class="text-success">Privacy Policy</a>.
                            </p>
                          </div>
                    </div>
                    <div  class="auth-sidecontent"
                        style="background: linear-gradient(45deg, rgb(25, 24, 76), rgb(var(--bs-primary-rgb))) !important;">
                        <div class="p-3 text-center px-lg-5">
                            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        <img src="/assets/images/acc-1.png" alt="user-image" class="mb-3 hei-150">
                                        <h5 class="mb-0 text-white">Regulatory Excellence</h5>
                                        <p class="text-white text-opacity-50">Compliance Assurance</p>
                                        <div class="my-4 star f-20">
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
                                        <img src="/assets/images/ben-02.png" alt="user-image" class="mb-3 hei-150">
                                        <h5 class="mb-0 text-white">Transparent Pricing Policy</h5>
                                        <p class="text-white text-opacity-50">Clear Cost Commitment</p>
                                        <div class="my-4 star f-20">
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
                                        <img src="/assets/images/ben-03.png" alt="user-image" class="mb-3 hei-150">
                                        <h5 class="mb-0 text-white">Swift and Precise Execution</h5>
                                        <p class="text-white text-opacity-50">Precision Trading</p>
                                        <div class="my-4 star f-20">
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
                                        <img src="/assets/images/ben-04.png" alt="user-image" class="mb-3 hei-150">
                                        <h5 class="mb-0 text-white">Competitive Spreads</h5>
                                        <p class="text-white text-opacity-50">Cost Efficiency</p>
                                        <div class="my-4 star f-20">
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
                                <div class="mt-3 carousel-indicators position-relative">
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
        @include('components.google-translate')
        <!-- Visible language dropdown for client login page -->
        <div style="position: fixed; top: 10px; right: 10px; z-index: 2000;">
            @include('components.language-dropdown', [
                'selectId' => 'custom_translate_select_client_login',
                'flagPreviewId' => 'flag-preview-client-login'
            ])
        </div>
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
    @if (session('retry_after'))
        <script>
            (function() {
                let retryAfter = {{ session('retry_after') }};
                const countdownElement = document.getElementById('countdown-timer');
                const errorMessage = document.getElementById('error-message');
                const errorAlert = document.getElementById('rate-limit-error');
                const loginForm = document.getElementById('login-form');
                const loginBtn = document.getElementById('login-submit-btn');

                // Disable form and button
                if (loginForm && loginBtn) {
                    loginBtn.disabled = true;
                    loginBtn.style.opacity = '0.6';
                    loginBtn.style.cursor = 'not-allowed';
                    loginForm.addEventListener('submit', function(e) {
                        if (retryAfter > 0) {
                            e.preventDefault();
                            return false;
                        }
                    });
                }

                if (countdownElement && errorMessage && retryAfter > 0) {
                    function updateCountdown() {
                        if (retryAfter <= 0) {
                            if (errorAlert) {
                                errorAlert.style.display = 'none';
                            }
                            // Re-enable form and button
                            if (loginBtn) {
                                loginBtn.disabled = false;
                                loginBtn.style.opacity = '1';
                                loginBtn.style.cursor = 'pointer';
                            }
                            return;
                        }

                        const minutes = Math.floor(retryAfter / 60);
                        const seconds = retryAfter % 60;
                        const timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                        errorMessage.textContent = `Too many requests. Please wait `;
                        countdownElement.textContent = timeString + ` before trying again.`;

                        retryAfter--;

                        if (retryAfter >= 0) {
                            setTimeout(updateCountdown, 1000);
                        }
                    }

                    updateCountdown();
                }
            })();
        </script>
    @endif

@endsection
