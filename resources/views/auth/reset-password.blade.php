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

    <div id="app" data-v-app="">
        <div class="auth-main">
            <div class="auth-wrapper v3">
                <div class="auth-form">
                    <div class="auth-header row">
                        <div class="my-1 col" style="display: flex; justify-content: center; align-items: center;">
                            <a href="{{ url('/login') }}">
                                <img src="{{ asset($settings['admin_sidebar_logo']) }}" alt="Logo" style="height: 8vh;">
                            </a>
                        </div>
                    </div>
                    <div class="my-3 card">
                        <div class="card-body">
                            <ul class="nav nav-tabs d-none" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link" id="auth-tab-4" data-bs-toggle="tab" href="{{ url('/login') }}"
                                        role="tab" data-slide-index="4" aria-controls="auth-4" aria-selected="true"></a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                {{-- action="{{ route('password.reset') }}" --}}
                                <form method="POST" action="{{ url('/reset-password?id=' . request('id') . '&code=' . request('code')) }}">
                                    @csrf
                                    <div class="tab-pane show active" id="auth-4" role="tabpanel"
                                        aria-labelledby="auth-tab-4">
                                        <div class="text-center">
                                            <h3 class="mb-3 text-center">Reset Password</h3>
                                            @if (session('status'))
                                                <div class="alert alert-success">
                                                    {{ session('status') }}
                                                </div>
                                            @endif
                                            @if ($errors->any())
                                                <div class="alert alert-danger">
                                                    @foreach ($errors->all() as $error)
                                                        <span>{{ $error }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <div class="mt-4 row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label class="form-label">Password</label>
                                                    <input name="password" id="password" type="password" class="form-control"
                                                        placeholder="Password" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label class="form-label">Confirm Password</label>
                                                    <input type="password" id="password_confirmation" class="form-control"
                                                        placeholder="Confirm Password" name="password_confirmation"
                                                        required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2 mb-4">
                                            <div class="p-3 border shadow-sm w-100 rounded-3">
                                                @include('partials.password-validation-rules')
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <h6 class="mb-0 text-secondary f-w-400">
                                                <a href="{{ url('/login') }}" class="link-primary">RETURN TO LOGIN</a>
                                            </h6>
                                        </div>
                                        <div class="mt-1 row g-3">
                                            <div class="col-sm-12">
                                                <div class="d-grid">
                                                    <input type="submit" name="resetpassword" id="password-submit-btn" value="Reset Your Password"
                                                        class="btn btn-primary" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="auth-footer">
                        <p class="m-0 text-center w-100" style="font-size: 11px;">
                            By logging in, you confirm that you have read and agree to {{ $settings['admin_title'] }}'s
                            <a target="_blank" href="#" class="text-success">Privacy Policy</a>,
                            <a target="_blank" href="#" class="text-success">Transaction Policy</a> and
                            <a target="_blank" href="#" class="text-success">Risk Warning</a>.
                        </p>
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
                                            class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i
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
                                            class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i
                                            class="fas fa-star text-warning"></i><i
                                            class="fas fa-star-half-alt text-warning"></i></div>
                                    <p class="text-white"> At <?= $settings['admin_title'] ?>, transparency is paramount.
                                        Our pricing
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
                                    <p class="text-white"> Gain a competitive edge with <?= $settings['admin_title'] ?>'
                                        tight spreads.
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

    @include('partials.password-validation-script')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const submitBtn = document.getElementById('password-submit-btn');

            if (!passwordInput || !confirmPasswordInput || !submitBtn) return;

            // Password validation
            passwordInput.addEventListener('input', function () {
                const password = this.value;
                const confirmPassword = confirmPasswordInput.value;
                const rules = window.checkPasswordRules(password, confirmPassword);

                window.updateRuleUI('rule-length', rules.length);
                window.updateRuleUI('rule-uppercase', rules.uppercase);
                window.updateRuleUI('rule-lowercase', rules.lowercase);
                window.updateRuleUI('rule-digit', rules.digit);
                window.updateRuleUI('rule-special', rules.special);
                window.updateRuleUI('rule-no-spaces', rules.noSpaces);
                window.updateRuleUI('rule-match', confirmPassword ? rules.match : null);

                checkFormValidity();
            });

            // Confirm password validation
            confirmPasswordInput.addEventListener('input', function () {
                const password = passwordInput.value;
                const confirmPassword = this.value;
                const rules = window.checkPasswordRules(password, confirmPassword);

                window.updateRuleUI('rule-match', rules.match);

                checkFormValidity();
            });

            // Check if all rules are satisfied
            function checkFormValidity() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                const rules = window.checkPasswordRules(password, confirmPassword);

                // All rules must be satisfied
                const allSatisfied = rules.length && rules.uppercase && rules.lowercase &&
                                    rules.digit && rules.special && rules.noSpaces && rules.match === true;

                submitBtn.disabled = !allSatisfied;
            }

            // Initial state check
            checkFormValidity();
        });
    </script>

@endsection
