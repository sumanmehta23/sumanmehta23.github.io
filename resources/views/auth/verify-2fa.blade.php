@extends('layouts.app')
<style>
    @media (max-width: 550px) {
        .mob_logo_center {
            display: flex;
            justify-content: center;
            align-items: center;
        }
    }

</style>
@section('content')
<div id="app" data-v-app="">
    <div  class="auth-main">
        <div  class="auth-wrapper v3">
            <div  class="auth-form">
                <div  class="auth-header row">
                    <div  class="my-1 col mob_logo_center">
                        <a href="{{ route('login') }}"><img src="{{ asset($settings['admin_sidebar_logo']) }}"
                                alt="Logo" style="height: 8vh;"></a>
                    </div>
                </div>

                <div  class="my-3 card">
                    <div  class="card-body">
                        <form method="POST" action="{{ route('verify-2fa') }}">
                            @csrf

                            <div class="text-center">
                                <h3 class="mb-3">2FA</h3>
                                <p class="mb-4 fs-6">Please enter your 2FA code here.</p>
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

                            {{-- Toggle link --}}
                            <div class="mb-3 d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('logout') }}" id="back_link" class="text-primary small">Login with different email</a>
                                </div>
                                <div>
                                    <a href="#" id="toggle_mode_link" class="text-primary small">Use Recovery Code</a>
                                </div>
                            </div>

                            {{-- Mode display (optional) --}}
                            <div class="mb-2 text-muted small" hidden>
                                Mode: <span id="mode-display" class="fw-bold">auth</span>
                            </div>

                            {{-- Authenticator Code field --}}
                            <div id="auth_code_field" class="mb-3 form-group">
                                <label for="code" class="form-label">Authenticator Code</label>
                                <input type="text" name="code" class="form-control" autofocus>
                            </div>

                            {{-- Recovery Code field --}}
                            <div id="recovery_code_field" class="mb-3 form-group" style="display: none;">
                                <label for="recovery_code" class="form-label">Recovery Code</label>
                                <input type="text" name="recovery_code" class="form-control">
                            </div>

                            <div class="mt-3 d-grid">
                                <button type="submit" class="btn btn-primary">Verify</button>
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
        modeInput.value = 'auth'; // default mode
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

        toggleLink.addEventListener('click', function (e) {
            e.preventDefault();
            const newMode = modeInput.value === 'auth' ? 'recovery' : 'auth';
            setMode(newMode);
        });

        // Initialize
        setMode('auth');
    });
</script>
@endsection
