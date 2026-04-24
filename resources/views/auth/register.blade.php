@extends('layouts.app')

@section('content')
    <style>
        .country-code-select {
            border: 1px solid white !important;
        }

        .country-code-wrapper .select2-container {
            max-width: 25%;
            display: block;
            padding: 0.6rem .75rem;
            font-size: .875rem;
            font-weight: 400;
            line-height: 1.5;
            color: #131920;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #bec8d0;
            border-radius: 8px;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

        @media (max-width: 750px) {
            .register_mob {
                margin-top: 10% !important;
            }
        }

        @media (max-width: 550px) {
            .mob_logo_center {
                display: flex;
                justify-content: center;
                align-items: center;
                margin-bottom: 10%
            }
        }

        .lqh-sale-banner {
            width: 100%;
            background-size: 100%;
            background-color: #003e40;
            text-align: center;
            padding: 10px 20px;
        }

        .lqh-sale-banner h1 {
            font-size: 20px;
            margin-top: 0px;
            color: #FFFFFF;
            text-shadow: 0 0 7px #000000;
        }


        .banner-link:hover {
            text-decoration: none;
        }

        .loggedin .sales-banner-container {
            position: fixed;
            top: 0;
            z-index: 1030;
        }

        @media (max-width: 550px) {

            .loggedin .lqh-sale-banner h1,
            .lqh-sale-banner h1 {
                font-size: 14px;
            }
        }

        .lqh-sale-banner {
            width: 100%;
            background-size: 100%;
            background-color: #003e40;
            text-align: center;
            padding: 10px 20px;
        }

        .lqh-sale-banner h1 {
            font-size: 20px;
            margin-top: 0px;
            color: #FFFFFF;
            text-shadow: 0 0 7px #000000;
        }


        .banner-link:hover {
            text-decoration: none;
        }

        .loggedin .sales-banner-container {
            position: fixed;
            top: 0;
            z-index: 1030;
        }

        @media (max-width: 550px) {

            .loggedin .lqh-sale-banner h1,
            .lqh-sale-banner h1 {
                font-size: 14px;
            }
        }
    </style>
    <div id="app" data-v-app="">
        <div class="auth-main">
            <div class="auth-wrapper v3">
                <div class="auth-form register_mob" style="margin: 0;">
                    <div class="auth-header row ">
                        <div class="my-1 col mob_logo_center"><a href="{{ $settings['main_website_url'] ?? '#' }}"><img class="mob_logo_center"
                                    src="/<?php echo $settings['admin_sidebar_logo']; ?>" alt="Logo"
                                    style="height: 8vh;"></a>
                        </div>
                        <div data-v-97e32e5a="" class="col-auto my-1">
                            <h5 data-v-97e32e5a="" class="m-0 text-muted f-w-500"> Step <b data-v-97e32e5a="" class="h5"
                                    id="auth-active-slide"><?= isset($success) ? '3' : '1' ?></b> to 3 </h5>
                        </div>
                    </div>

                    @if(config("services.sales.promotion"))
                        {{-- <div class="mt-4 w-100 sales-banner-container">
                            <div class="banner-link">
                                <div class="lqh-sale-banner">
                                    <h1 class="animated pulse">{!!config("services.sales.promotiontext")!!}</h1>
                                </div>
                            </div>
                        </div> --}}

                    @endif
                    <div data-v-97e32e5a="" class="my-auto card">
                        <div data-v-97e32e5a="" class="card-body">
                            <ul data-v-97e32e5a="" class="nav nav-tabs d-none" id="myTab" role="tablist">
                                <li data-v-97e32e5a="" class="nav-item" role="presentation"><a data-v-97e32e5a=""
                                        class="nav-link active" id="auth-tab-1" data-bs-toggle="tab" href="#" role="tab"
                                        data-slide-index="1" aria-controls="auth-1" aria-selected="true"></a>
                                </li>
                            </ul>

                            <form method="post" data-v-97e32e5a="" class="needs-validation" id="formRegister">
                                @csrf
                                <div data-v-97e32e5a="" class="tab-content">
                                    <div data-v-97e32e5a="" class="tab-pane <?= !isset($success) ? 'active show' : '' ?>"
                                        id="auth-1" role="tabpanel" aria-labelledby="auth-tab-1">
                                        <div data-v-97e32e5a="" class="text-center">
                                            <h3 data-v-97e32e5a="" class="mb-2 text-center f-w-600">Join Us Now
                                            </h3>
                                            <p data-v-97e32e5a="" class="mb-4 fs-5">Get Started with Trading Today: Quick
                                                and Easy Account Setup!</p>
                                        </div>
                                        @if (session('status'))
                                            <div class="alert alert-success">
                                                {{ session('status') }}
                                            </div>
                                        @endif
                                        @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <span>{{ $errors->first() }}</span>
                                            </div>
                                        @endif

                                        <div data-v-97e32e5a="" class="my-2 row">
                                            <div data-v-97e32e5a="" class="col-12">
                                                <div data-v-97e32e5a="" class="form-group"><label data-v-97e32e5a=""
                                                        class="form-label">Email</label><input data-v-97e32e5a=""
                                                        type="email" id="email" class="form-control" placeholder="Email"
                                                        required="" name="email"><!----></div>
                                            </div>
                                            <div data-v-97e32e5a="" class="col-12">
                                                <div data-v-97e32e5a="" class="form-group"><label data-v-97e32e5a=""
                                                        class="form-label">Password</label><input data-v-97e32e5a=""
                                                        type="password" id="password" class="form-control"
                                                        placeholder="Password" required="" aria-autocomplete="list"
                                                        name="password"><!----></div>
                                            </div>
                                            <div data-v-97e32e5a="" class="col-12">
                                                <div data-v-97e32e5a="" class="form-group"><label data-v-97e32e5a=""
                                                        class="form-label">Confirm Password </label><input
                                                        data-v-97e32e5a="" id="confirmpassword" type="password"
                                                        class="form-control" placeholder="Confirm Password" required=""
                                                        name="password_confirmation"><!----></div>
                                            </div>
                                        </div>

                                        <div class="mt-2 mb-4">
                                            <div class="p-3 border shadow-sm w-100 rounded-3">
                                                @include('partials.password-validation-rules')
                                            </div>
                                        </div>

                                        <div data-v-97e32e5a="" class="row g-3">
                                            <div data-v-97e32e5a="" class="col-sm-12">
                                                <div data-v-97e32e5a="" class="d-grid"><button id="registration1"
                                                        data-v-97e32e5a="" class="btn btn-primary" type="button" role="tab"
                                                        data-bs-toggle="tab" data-bs-target="#auth-2">
                                                        Continue </button></div>
                                            </div>
                                        </div>

                                        <div data-v-97e32e5a="" class="row">
                                            <div data-v-97e32e5a="" class="mt-2 d-flex align-items-end">
                                                <p class="mb-0 f-w-500" style="font-size: 14px !important;">Already have an
                                                    Account? </p><a href="/login" class="link-primary"
                                                    style="font-size: 14px !important; padding-left: 10px; margin-top: 10px;">
                                                    Login</a>
                                                <a href="https://www.lqhmarkets.com/risk-disclaimer"
                                                    class="link-primary ms-auto"
                                                    style="font-size: 14px !important; padding-left: 10px;">
                                                    Risk Disclaimer
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div data-v-97e32e5a="" class="tab-pane" id="auth-2" role="tabpanel"
                                        aria-labelledby="auth-tab-2">
                                        <div data-v-97e32e5a="" class="">
                                            <h4 data-v-97e32e5a="" class="mb-3 f-w-600">Tell us a bit about yourself
                                            </h4>
                                        </div>
                                        <div data-v-97e32e5a="" class="my-4 row">
                                            <div data-v-97e32e5a="" class="col-sm-12">
                                                <div data-v-97e32e5a="" class="form-group">
                                                    <label data-v-97e32e5a="" class="form-label">Full Name</label>
                                                    <input data-v-97e32e5a="" type="text" class="form-control"
                                                        placeholder="Full name" required name="fullname" maxlength="30"
                                                        onpaste="handlePaste(event)">
                                                    <small data-v-97e32e5a="">*as it appears on your ID Card / Proof of
                                                        Identity</small>
                                                </div>
                                            </div>
                                            <div data-v-97e32e5a="" class="col-12">
                                                <div data-v-97e32e5a="" class="form-group country-code-wrapper"><label
                                                        data-v-97e32e5a="" class="form-label">Phone Number</label>
                                                    <div class="d-flex align-items-center">
                                                        <select class="form-select me-2 w-25" name="country_code"
                                                            id="country_code" required>
                                                            <option value="">Country Code</option>
                                                            <?php foreach ($countries as $country) { ?>
                                                            <option value="+<?= $country['country_code'] ?>"
                                                                data-flag="<?= strtolower($country['country_alpha']) ?>">
                                                                +<?= $country['country_code'] ?>
                                                                (<?= $country['country_name'] ?>)</option>
                                                            <?php } ?>
                                                        </select>
                                                        <input type="number" id="telephone" class="form-control w-75 ms-2"
                                                            maxlength="25" name="telephone"
                                                            placeholder="Enter a phone number" tabindex="0"
                                                            aria-describedby="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div data-v-97e32e5a="" class="col-12">
                                                <div data-v-97e32e5a="" class="form-group"><label data-v-97e32e5a=""
                                                        class="form-label">Nationality</label>
                                                    <select class="form-select" id="country" name="country" required>
                                                        <option value="">Select Country</option>
                                                        <?php foreach ($countries as $country) { ?>
                                                        <option value="<?= $country['country_name'] ?>">
                                                            <?= $country['country_name'] ?>
                                                        </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            {{-- <div class="col-sm-12">
                                                <div class="form-group"><label class="form-label">Gender</label><input
                                                        type="text" class="form-control" placeholder="Gender" required
                                                        name="gender"><!----></div>
                                            </div> --}}

                                            @if(request()->has('refercode') && $referral_code)
                                                <div data-v-97e32e5a="" class="col-sm-12">
                                                    <div data-v-97e32e5a="" class="form-group"><label data-v-97e32e5a=""
                                                            class="form-label">Referral Code</label><input data-v-97e32e5a=""
                                                            type="text" class="form-control" value="{{ $referral_code }}"
                                                            required readonly disabled name="refercode"><!----><small
                                                            data-v-97e32e5a="">*your referral link code provided.</small></div>
                                                </div>
                                                {{-- @else
                                                <div data-v-97e32e5a="" class="col-sm-12">
                                                    <div data-v-97e32e5a="" class="form-group"><label data-v-97e32e5a=""
                                                            class="form-label">Referral Code<small data-v-97e32e5a="">(if
                                                                any)</small></label><input data-v-97e32e5a="" type="text"
                                                            class="form-control" placeholder="Referral Code" name="referral">
                                                    </div>
                                                </div> --}}
                                            @endif

                                        </div>
                                        @if(($turnstileEnabled ?? false) && !empty($turnstileSiteKey ?? ''))
                                        <div data-v-97e32e5a="" class="row g-3 mb-3">
                                            <div data-v-97e32e5a="" class="col-sm-12 d-flex justify-content-center">
                                                <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}"></div>
                                            </div>
                                        </div>
                                        @endif
                                        <div data-v-97e32e5a="" class="row g-3">
                                            <div data-v-97e32e5a="" class="col-sm-12">
                                                <div data-v-97e32e5a="" class="d-grid"><button data-v-97e32e5a=""
                                                        class="btn btn-primary" type="submit" name="register">
                                                        Submit</button></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div data-v-97e32e5a="" class="tab-pane <?= isset($success) ? 'active show' : '' ?>"
                                        id="auth-3" role="tabpanel" aria-labelledby="auth-tab-3">
                                        <div data-v-97e32e5a="" class="text-center">
                                            <div data-v-97e32e5a="" class="text-center">
                                                <h3 data-v-97e32e5a="" class="mb-3 text-center">Please verify your email
                                                    id
                                                </h3>
                                                @isset($success)
                                                    <p data-v-97e32e5a="" class="mb-4"> <?= $success ?> </p>
                                                @endisset
                                                <a href="/login" class="btn btn-primary w-75">
                                                    Login</a>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php if (!isset($success)) { ?>
                    <div data-v-97e32e5a="" class="auth-footer">
                        <p data-v-97e32e5a="" class="m-0 text-center w-100" style="font-size: 11px;"> By signing up, I
                            acknowledge
                            that I have read, understood and agree to the Client Agreement <br data-v-97e32e5a="">and
                            give my consent
                            for <?= $settings['admin_title'] ?> to contact me for marketing purposes. <br
                                data-v-97e32e5a=""> By
                            registering you agree
                            to our <a data-v-97e32e5a="" href="https://www.lqhmarkets.com/privacy-policy" class="link-success" target="_blank">Privacy Policy</a>, <a data-v-97e32e5a="" href="https://www.lqhmarkets.com/client-agreement" class="link-success" target="_blank">Client
                                Agreement </a>&amp; <a data-v-97e32e5a="" href="https://www.lqhmarkets.com/risk-disclaimer" class="link-success" target="_blank">Trading Risk
                                Warning</a>. </p>
                    </div>
                    <?php } ?>
                </div>
                <div class="auth-sidecontent"
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
                                            class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i
                                            class="fas fa-star text-warning"></i><i
                                            class="fas fa-star-half-alt text-warning"></i></div>
                                    <p class="text-white"> Experience lightning-fast trade execution with
                                        <?= $settings['admin_title'] ?>.
                                        Our advanced
                                        trading infrastructure ensures swift and precise order processing, enabling
                                        traders to capitalize on
                                        market opportunities instantly.
                                    </p>
                                </div>
                                <div class="carousel-item"><img src="/assets/images/ben-04.png" alt="user-image"
                                        class="mb-3 hei-150">
                                    <h5 class="mb-0 text-white">Competitive Spreads</h5>
                                    <p class="text-white text-opacity-50">Cost Efficiency</p>
                                    <div class="my-4 star f-20"><i class="fas fa-star text-warning"></i><i
                                            class="fas fa-star text-warning"></i><i class="fas fa-star text-warning"></i><i
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

    @if(($turnstileEnabled ?? false) && !empty($turnstileSiteKey ?? ''))
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
    <script>
        function handlePaste(event) {
            const pasteData = event.clipboardData.getData('text');

            const urlPattern = /https?:\/\/[^\s]+|www\.[^\s]+/;

            if (urlPattern.test(pasteData)) {
                event.preventDefault();
                alert('Hyperlinks are not allowed in the Full Name field.');
            }
        }
        $(document).ready(function () {

            function formatOption(option) {
                if (!option.id) {
                    return option.text;
                }
                var $option = $(
                    '<span><span class="fi fis fi-' + $(option.element).data('flag') + '"></span>' + option
                        .text + '</span>'
                );
                return $option;
            }
            $("#country_code").select2({
                placeholder: "Country Code",
                templateResult: formatOption,
                templateSelection: formatOption,
                selectionCssClass: "country-code-select"
            });
            $('#registration1').click(function () {
                const email = $('#email').val();
                const password = $('#password').val();
                const confirmPassword = $('#confirmpassword').val();
                if (!email || !password || !confirmPassword) {
                    swal.fire({
                        icon: "error",
                        title: "Please fill all required fields.",
                    })
                    return;
                }
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (emailPattern.test(email) == false) {
                    swal.fire({
                        icon: "error",
                        title: "Invalid email address",
                    });
                    return;
                }
                const passwordPattern = {
                    length: /.{8,}/,
                    lowercase: /[a-z]/,
                    uppercase: /[A-Z]/,
                    number: /\d/,
                    special: /[\W_]/
                };

                let errors = [];

                if (!passwordPattern.length.test(password)) {
                    errors.push("<li>At least 8 characters</li>");
                }
                if (!passwordPattern.lowercase.test(password)) {
                    errors.push("<li>At least 1 lowercase letter (a-z)</li>");
                }
                if (!passwordPattern.uppercase.test(password)) {
                    errors.push("<li>At least 1 uppercase letter (A-Z)</li>");
                }
                if (!passwordPattern.number.test(password)) {
                    errors.push("<li>At least 1 number (0-9)</li>");
                }
                if (!passwordPattern.special.test(password)) {
                    errors.push("<li>At least 1 special character</li>");
                }

                if (errors.length > 0) {
                    swal.fire({
                        icon: "error",
                        title: "Password must meet the following criteria:",
                        html: "<ul style='text-align: left;'>" + errors.join("") + "</ul>",
                    });
                    return;
                }
                if (password !== confirmPassword) {
                    swal.fire({
                        icon: "error",
                        title: "Passwords do not match",
                    })
                    return;
                }
                $('#auth-1').removeClass('show active');
                $('#auth-2').addClass('show active');
                $('#auth-active-slide').html(2);
            });
        });
        $.get("http://ipinfo.io", function (response) {
            var country = response.country.toLowerCase();
            var option = $("#country_code option[data-flag='" + country + "']").attr("value");
            $("#country_code").val(option).trigger("change")
        }, "jsonp");
    </script>


    @include('partials.password-validation-script')

    <script>


        // Validate email format
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Check if all validation rules are satisfied
        function validateRegistrationForm() {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmpassword');
            const continueBtn = document.getElementById('continueButton');

            if (!emailInput || !passwordInput || !confirmPasswordInput || !continueBtn) return;

            const email = emailInput.value.trim();
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            // Check email is valid
            const isEmailValid = email && isValidEmail(email);

            // Check password rules
            const rules = window.checkPasswordRules(password, confirmPassword);

            // All rules must be satisfied
            const allRulesSatisfied = rules.length && rules.uppercase && rules.lowercase && rules.digit && rules.special && rules.match === true;

            // Enable button only if email is valid AND all password rules are satisfied
            const isFormValid = isEmailValid && allRulesSatisfied;

            continueBtn.disabled = !isFormValid;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmpassword');
            const continueBtn = document.getElementById('continueButton');

            if (!passwordInput) return;
            // Email validation
            if (emailInput) {
                emailInput.addEventListener('input', function () {
                    validateRegistrationForm();
                });
            }

            // Password validation
            passwordInput.addEventListener('input', function () {
                const password = this.value;
                const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';
                const rules = window.checkPasswordRules(password, confirmPassword);

                window.updateRuleUI('rule-length', rules.length);
                window.updateRuleUI('rule-uppercase', rules.uppercase);
                window.updateRuleUI('rule-lowercase', rules.lowercase);
                window.updateRuleUI('rule-digit', rules.digit);
                window.updateRuleUI('rule-special', rules.special);
                window.updateRuleUI('rule-no-spaces', rules.noSpaces);
                window.updateRuleUI('rule-match', confirmPassword ? rules.match : null);

                validateRegistrationForm();
            });

            // Confirm password validation
            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', function () {
                    const password = passwordInput.value;
                    const confirmPassword = this.value;
                    const rules = window.checkPasswordRules(password, confirmPassword);

                    window.updateRuleUI('rule-match', confirmPassword ? rules.match : null);

                    validateRegistrationForm();
                });
            }

            // Initial state - button should be disabled
            validateRegistrationForm();
        });
    </script>
@endsection