@extends('layouts.crm.crm')
@section('content')
    <div id="passwordupdatemodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="post" id="passwordForm" action="{{ route('change-mt5-password', $account) }}">
                @csrf
                <input type="hidden" name="account_id" value="{{ $account->id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCenterTitle">Update Password</h5><button type="button"
                            class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <h5 class="p-2 f-w-200">{{ $account->platform === 'x9' ? 'X9' : 'MT5' }} ACCOUNT</h5>
                            </div>
                            <div class="col-6">
                                <h5 class="p-2 f-w-400">{{ $code }}</h5>
                            </div>
                        </div>
                        <p class="p-2 mt-0 mb-2 text-gray-500 f-12 text-muted"> You have the ability to update your Investor
                            and Master passwords for your trading accounts here. If you require any assistance or encounter any
                            challenges with password management, please don't hesitate to reach out to us for support.</p>
                        <div class="mt-0 mb-0 row">
                            @if ($account->platform != 'x9')
                                <div class="col-lg-6">
                                    <div class="p-3 border card">
                                        <div class="form-check"><input type="radio" name="password_type"
                                                class="form-check-input input-primary" id="customCheckdefhor1" value="investor"
                                                checked><label class="form-check-label d-block"
                                                for="customCheckdefhor1"><span><span class="h6">Investor
                                                        Password</span></span></label></div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-lg-6">
                                <div class="p-3 border card">
                                    <div class="form-check"><input type="radio" name="password_type"
                                            class="form-check-input input-primary" id="customCheckdefhor2"
                                            value="main"><label class="form-check-label d-block"
                                            for="customCheckdefhor2"><span><span class="h6">Master
                                                    Password</span></span></label></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-0 mb-0 row">
                            <div class="form-group"><label class="form-label" for="exampleInputPassword1">New
                                    Password</label><input type="password" class="form-control" name="password" required
                                    id="password" placeholder="Password">
                            </div>
                            {{-- <div class="mb-2 row">
                                <div class="col-6"><span class="pc-micon me-2"><i class="ti ti-point"></i></span><span
                                        class="pc-mtext f-12">Minimum 8 characters</span><br><span class="pc-micon me-2"><i
                                            class="ti ti-point"></i></span><span class="pc-mtext f-12">At least 1 uppercase
                                        letter</span><br><span class="pc-micon me-2"><i class="ti ti-point"></i></span><span
                                        class="pc-mtext f-12">At least 1 lowercase letter</span></div>
                                <div class="col-6"><span class="pc-micon me-2"><i class="ti ti-point"></i></span><span
                                        class="pc-mtext f-12">At least 1 special character</span><br><span
                                        class="pc-micon me-2"><i class="ti ti-point"></i></span><span
                                        class="pc-mtext f-12">At least 1 digit</span></div>
                            </div> --}}
                            <div class="mb-2 form-group"><label class="form-label" for="exampleInputPassword1">Confirm
                                    Password</label><input type="password" class="form-control" name="confirm_password"
                                    required id="confirm_password" placeholder="Password">
                            </div>

                            <div class="mt-2 mb-4">
                                <div class="p-3 border shadow-sm w-100 rounded-3">
                                    @include('partials.password-validation-rules')
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-primary"
                            data-bs-dismiss="modal">Close</button><button class="btn btn-primary" type="submit"
                            name="passwordUpdate" value="true">
                            <!----> Update Password</button></div>
                </div>
            </form>
        </div>
    </div>
    <div id="updateNickNameModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="post" id="nickNameForm">
                @csrf
                <input type="hidden" name="account_id" value="{{ $account->id }}">
                <div class="modal-content" style="width: 200%;">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCenterTitle">Update Nick Name</h5><button type="button"
                            class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <h5 class="p-2 f-w-200">{{ $account->platform === 'x9' ? 'X9' : 'MT5' }} ACCOUNT</h5>
                            </div>
                            <div class="col-6">
                                <h5 class="p-2 f-w-400">{{ $code }}</h5>
                            </div>
                        </div>
                        <div class="mt-0 mb-0 row">
                            <div class="form-group">
                                <label class="form-label f-12" for="exampleInputNickName">
                                    Update Nick Name
                                </label>
                                <input type="text" class="form-control f-12" name="nickName" required id="nickName" placeholder="Nick Name">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-primary"
                            data-bs-dismiss="modal">Close</button><button class="btn btn-primary" type="submit"
                            name="updateNickName" value="true">
                            <!----> Update Nick Name</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title h5">
                                <h2 class="mb-0">{{ $account->platform === 'x9' ? 'X9' : 'MT5' }} Details</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="mb-3 alert alert-secondary d-print-none">
                                        <div class="row align-items-center g-3">
                                            <div class="col-sm-6">
                                                <div class="row align-items-center">
                                                    <div class="col-auto pe-0">
                                                        @if($account->platform === 'x9')
                                                            <img src="/assets/images/x9.png" alt="x9-platform"
                                                                class="rounded wid-60 hei-60">
                                                        @else
                                                            <img src="/assets/images/mt5.png" alt="mt5-platform"
                                                                class="rounded wid-60 hei-60">
                                                        @endif
                                                    </div>
                                                    <div class="gap-3 col d-flex align-items-left">
                                                        <div>
                                                            <h2 class="mb-0 f-w-500">
                                                                <span class="text-truncate">{{ $code }}</span>
                                                            </h2>
                                                            @if($account->demo)
                                                                <span class="mt-1 text-white badge bg-danger rounded-pill">Demo Account</span>
                                                            @elseif($account->demo == 0)
                                                                <span class="mt-1 text-white badge bg-success rounded-pill">Live Account</span>
                                                            @endif
                                                            @if($account->platform === 'x9' && isset($x9GroupName))
                                                                <h6 class="mb-0 text-muted f-12">{{ $getUser->accountType->ac_name }}</h6>
                                                            @elseif($getUser && $getUser->accountType)
                                                                <h6 class="mb-0 text-muted f-12">{{ $getUser->accountType->ac_name }}</h6>
                                                            @endif
                                                        </div>
                                                        @if ($account->account_nick_name)
                                                            <h4 class="pt-2 mb-0 f-w-500">({{ $account->account_nick_name }})</h4>
                                                            {{-- <p class="mb-0 text-muted f-12"><span
                                                                    class="text-truncate w-100"></span></p> --}}
                                                        @endif
                                                        <button class="w-auto btn btn-sm btn-primary updateNickName" onclick="editNickname()">Edit Nick Name</button>
                                                    </div>

                                                </div>
                                            </div>
                                            {{-- <div class="col-sm-6 text-sm-end">
                                                <ul class="flex-wrap mb-0 list-inline ms-auto d-flex justify-content-end">
                                                    <li class="list-inline-item">
                                                        <div class="mb-0 card">
                                                            <div class="p-2 mb-0 card-body">
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="bg-gray-800 card dropbox-card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <p class="text-white">Balance</p>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="avtar avtar-s">
                                                        <i class="ph-duotone ph-briefcase f-20"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <h3 class="mb-0 text-white f-w-500">
                                                                ${{ isset($balance) ? number_format((float) $balance, 2) : '0.00' }}
                                                            </h3>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            @if ($account->demo == 0 && !$account->isZapierAccount() && ($account->competition_month == NULL))
                                                                <a href="/trade-deposit"
                                                                    class="btn btn-outline-light btn-print-invoice"
                                                                    onmouseover="this.style.color='white';"onmouseout="this.style.color='inherit';">Quick Deposit</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <div class="p-3 rounded bg-body">
                                                <div class="mb-2 d-flex align-items-center">
                                                    <div class="flex-shrink-0"><i
                                                            class="ph-duotone ph-file-cloud f-20"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <p class="mb-0">Server</p>
                                                    </div>
                                                </div>
                                                <h5 class="mb-0 f-w-400">{{ $account->platform === 'mt5' ? $settings['mt5_company_name'] : 'X9-Trade-Server' }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="p-3 rounded bg-body">
                                                <div class="mb-2 d-flex align-items-center">
                                                    <div class="flex-shrink-0"><i class="ph-duotone ph-cactus f-20"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <p class="mb-0">Bonus</p>
                                                    </div>
                                                </div>
                                                {{-- <h5 class="mb-0 f-w-400">${{ $account->Credit ?? '' }}</h5> --}}
                                                <h5 class="mb-0 f-w-400">${{ $getUser ? (round($getUser->bonusTransaction->sum('bonus_amount'),2) ?? '') : 0 }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="p-3 rounded bg-body">
                                                <div class="mb-2 d-flex align-items-center">
                                                    <div class="flex-shrink-0"><i
                                                            class="ph-duotone ph-hand-coins f-20"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <p class="mb-0">Leverage</p>
                                                    </div>
                                                </div>
                                                <h5 class="mb-0 f-w-400">1:{{ $getUser->leverage ?? '' }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="p-3 rounded bg-body">
                                                <div class="mb-2 d-flex align-items-center">
                                                    <div class="flex-shrink-0"><i class="ph-duotone ph-swap f-20"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <p class="mb-0">Swap</p>
                                                    </div>
                                                </div>
                                                <h5 class="mb-0 f-w-400">{{ $accountSwap }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="border avtar avtar-s"><i
                                                            class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <p class="mb-0 text-muted f-20"><small>Equity</small></p>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <h4 class="mb-1 f-w-400">@money($equity)</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="border avtar avtar-s"><i
                                                            class="ph-duotone ph-butterfly f-20"></i></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <p class="mb-0 text-muted f-20"><small>Free Margin</small></p>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <h4 class="mb-1 f-w-400">@money($freemargin)</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="border avtar avtar-s"><i
                                                            class="ph-duotone ph-chart-pie f-20"></i></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <p class="mb-0 text-muted f-20"><small>Margin</small></p>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <h4 class="mb-1 f-w-400">{{ $margin ?? '' }}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="border avtar avtar-s"><i
                                                            class="ph-duotone ph-chart-pie-slice f-20"></i></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <p class="mb-0 text-muted f-20"><small>Margin Level</small></p>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <h4 class="mb-1 f-w-400">{{ $marginlevel ?? '' }}%</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="border avtar avtar-s"><i
                                                            class="ph-duotone ph-gender-female f-20"></i></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <p class="mb-0 text-muted f-20"><small>Floating P&L</small></p>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <h4 class="mb-1 f-w-400">{{ $profit ?? '' }}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mt-3 row">
                                        @if (($type != 'Demo'))
                                            <div class="col-sm-6">
                                                <a href="{{ url('/internal-transfer') }}">
                                                    <div class="bg-white card">
                                                        <div class="p-3 card-body">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div>
                                                                    <h4 class="mb-0">Internal</h4>
                                                                    <p class="mb-0 text-opacity-75">Transfer</p>
                                                                </div>
                                                                <div class="border avtar avtar-s">
                                                                    <i class="ph-duotone ph-shuffle f-24"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endif

                                        <div class="col-sm-6">
                                            <a href="#">
                                                <div class="bg-white card updatePassword">
                                                    <div class="p-3 card-body">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div>
                                                                <h4 class="mb-0">Password</h4>
                                                                <p class="mb-0 text-opacity-75">Update</p>
                                                            </div>
                                                            <div class="border avtar avtar-s">
                                                                <i class="ph-duotone ph-password f-24"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @if ($account->competition_month == NULL)
                                    <div class="col-sm-6">
                                        <div class="mt-3 row">
                                            @if(!$account->isZapierAccount())
                                                @if ($account->demo == 0)
                                                    <div class="col-sm-6">
                                                        <a href="{{ url('/trade-deposit') }}"
                                                            class="card bg-primary available-balance-card">
                                                            <div class="p-3 card-body">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div>
                                                                        <h4 class="mb-0 text-white">Deposit</h4>
                                                                        <p class="mb-0 text-white text-opacity-75">Fund your account
                                                                        </p>
                                                                    </div>
                                                                    <div class="avtar">
                                                                        <i class="ph-duotone ph-credit-card f-24"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                @endif
                                            @endif
                                            @if(!$account->demo)
                                            <div class="col-sm-6">
                                                <a href="{{ url('/trade-withdrawal') }}"
                                                    class="card bg-secondary available-balance-card">
                                                    <div class="p-3 card-body">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div>
                                                                <h4 class="mb-0 text-white">Withdraw</h4>
                                                                <p class="mb-0 text-white text-opacity-75">Transfer your funds</p>
                                                            </div>
                                                            <div class="avtar">
                                                                <i class="ph-duotone ph-bank f-24"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                showConfirmButton: true
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: "Something Went Wrong !!!!",
                text: '{{ session('error') }}',
                showConfirmButton: true
            });
        </script>
    @endif
    <script>
        function validatePassword(password) {
            const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasDigit = /\d/.test(password);
            const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            if (password.length < minLength) {
                return "Password must be at least 8 characters long.";
            }
            if (!hasUpperCase) {
                return "Password must contain at least one uppercase letter.";
            }
            if (!hasLowerCase) {
                return "Password must contain at least one lowercase letter.";
            }
            if (!hasDigit) {
                return "Password must contain at least one digit.";
            }
            if (!hasSpecialChar) {
                return "Password must contain at least one special character.";
            }

            return "true";
        }

        $(".updatePassword").click(function() {
            $("#passwordupdatemodal").modal("show");
        });

        $("#passwordForm").on("submit", function(e) {
            e.preventDefault();
            var pass = $("#password").val();
            var cpass = $("#confirm_password").val();
            if (validatePassword(pass) == "true") {
                if (pass == cpass) {
                    $("#passwordForm").off();
                    $("#passwordForm").submit();
                } else {
                    swal.fire({
                        icon: "info",
                        title: "Passwords not matched"
                    });
                    $("#confirm_password").val("")
                    return false;
                }
            } else {
                swal.fire({
                    icon: "info",
                    title: "Password not matched requirement.",
                    text: validatePassword(pass)
                })
            }
        });

        $(".updateNickName").click(function() {
            $("#updateNickNameModal").modal("show");
        });
        $("#nickNameForm").on("submit", function(e) {
            e.preventDefault();
            var nickname = $("#nickName").val();
            var account_id = $("input[name='account_id']").val();

            if (nickname.length < 3) {
                swal.fire({
                    icon: "info",
                    title: "Nickname must be at least 3 characters."
                });
                return false;
            }

            $.ajax({
                url: "/update-nickname",
                type: "POST",
                data: {
                    nickname: nickname,
                    account_id: account_id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    swal.fire({
                        icon: "success",
                        title: "Nickname updated successfully!"
                    }).then((val) => {
                            location.reload();
                    });
                },
                error: function(xhr) {
                    swal.fire({
                        icon: "error",
                        title: "Error updating nickname",
                        text: xhr.responseJSON.message || "Something went wrong!"
                    }).then((val) => {
                            location.reload();
                    });
                }
            });
        });

    </script>

    @include('partials.password-validation-script')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const modal = document.getElementById('passwordupdatemodal');

        // Listen for password input changes for real-time validation
        if (passwordInput) {
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
                window.checkAllRulesSatisfied('password', 'confirm_password', 'password-submit-btn');
            });
        }

        // Listen for confirm password input changes
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function () {
                const password = passwordInput.value;
                const confirmPassword = this.value;
                const rules = window.checkPasswordRules(password, confirmPassword);
                window.updateRuleUI('rule-match', confirmPassword ? rules.match : null);
                window.checkAllRulesSatisfied('password', 'confirm_password', 'password-submit-btn');
            });
        }

        // Reset validation UI when modal is opened
        if (modal) {
            const observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.attributeName === 'class') {
                        const isHidden = modal.classList.contains('hidden');
                        if (!isHidden) {
                            if (passwordInput) passwordInput.value = '';
                            if (confirmPasswordInput) confirmPasswordInput.value = '';

                            ['rule-length', 'rule-uppercase', 'rule-lowercase', 'rule-digit', 'rule-special', 'rule-no-spaces', 'rule-match'].forEach(ruleId => {
                                window.updateRuleUI(ruleId, null);
                            });

                            window.checkAllRulesSatisfied('password', 'confirm_password', 'password-submit-btn');
                        }
                    }
                });
            });

            observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
        }
    });
</script>
@endsection
