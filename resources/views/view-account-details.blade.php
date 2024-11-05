@extends('layouts.crm.crm')
@section('content')
    <div id="passwordupdatemodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="post" id="passwordForm">
                @csrf
                <input type="hidden" name="trade_id" value="{{ $trade_id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCenterTitle">Update Password</h5><button type="button"
                            class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <h5 class="p-2 f-w-200">MT5 ACCOUNT</h5>
                            </div>
                            <div class="col-6">
                                <h5 class="p-2 f-w-400">{{ $trade_id }}</h5>
                            </div>
                        </div>
                        <p class="f-12 text-gray-500 p-2 text-muted mt-0 mb-2"> You have the ability to update your Investor
                            and
                            Master passwords for your trading accounts here. If you require any assistance or encounter any
                            challenges with password management, please don't hesitate to reach out to us for support.</p>
                        <div class="row mt-0 mb-0">
                            <div class="col-lg-6">
                                <div class="border card p-3">
                                    <div class="form-check"><input type="radio" name="password_type"
                                            class="form-check-input input-primary" id="customCheckdefhor1" value="investor"
                                            checked><label class="form-check-label d-block"
                                            for="customCheckdefhor1"><span><span class="h6">Investor
                                                    Password</span></span></label></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="border card p-3">
                                    <div class="form-check"><input type="radio" name="password_type"
                                            class="form-check-input input-primary" id="customCheckdefhor2"
                                            value="main"><label class="form-check-label d-block"
                                            for="customCheckdefhor2"><span><span class="h6">Master
                                                    Password</span></span></label></div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-0 mb-0">
                            <div class="form-group"><label class="form-label" for="exampleInputPassword1">New
                                    Password</label><input type="password" class="form-control" name="password" required
                                    id="password" placeholder="Password">
                            </div>
                            <div class="row mb-2">
                                <div class="col-6"><span class="pc-micon me-2"><i class="ti ti-point"></i></span><span
                                        class="pc-mtext f-12">Minimum 8 characters</span><br><span class="pc-micon me-2"><i
                                            class="ti ti-point"></i></span><span class="pc-mtext f-12">At least 1 uppercase
                                        letter</span><br><span class="pc-micon me-2"><i class="ti ti-point"></i></span><span
                                        class="pc-mtext f-12">At least 1 lowercase letter</span></div>
                                <div class="col-6"><span class="pc-micon me-2"><i class="ti ti-point"></i></span><span
                                        class="pc-mtext f-12">At least 1 special character</span><br><span
                                        class="pc-micon me-2"><i class="ti ti-point"></i></span><span
                                        class="pc-mtext f-12">At least 1 digit</span></div>
                            </div>
                            <div class="form-group mb-2"><label class="form-label" for="exampleInputPassword1">Confirm
                                    Password</label><input type="password" class="form-control" name="confirm_password"
                                    required id="confirm_password" placeholder="Password"></div>

                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Close</button><button class="btn btn-primary" type="submit"
                            name="passwordUpdate" value="true">
                            <!----> Update Password</button></div>
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
                                <h2 class="mb-0">MT5 Details</h2>
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
                                    <div class="alert alert-secondary mb-3 d-print-none">
                                        <div class="row align-items-center g-3">
                                            <div class="col-sm-6">
                                                <div class="row align-items-center">
                                                    <div class="col-auto pe-0">
                                                        <img src="/assets/images/mt5.png" alt="user-image"
                                                            class="wid-60 hei-60 rounded">
                                                    </div>
                                                    <div class="col">
                                                        <h2 class="mb-0 f-w-500">
                                                            <span class="text-truncate">{{ $trade_id }}</span>
                                                        </h2>
                                                        <p class="text-muted f-12 mb-0"><span
                                                                class="text-truncate w-100"></span></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 text-sm-end">
                                                <ul class="list-inline ms-auto mb-0 d-flex justify-content-end flex-wrap">
                                                    <li class="list-inline-item">
                                                        <div class="card mb-0">
                                                            <div class="card-body p-2 mb-0">
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="card bg-gray-800 dropbox-card">
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
                                                            <h3 class="text-white mb-0 f-w-500">$
                                                                {{ isset($account->Balance) ? $account->Balance : '0.00' }}
                                                            </h3>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            @if ($type != 'demo')
                                                                <a href="/trade-deposit"
                                                                    class="btn btn-outline-light btn-print-invoice">Quick
                                                                    Deposit</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <div class="bg-body p-3 rounded">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="flex-shrink-0"><i
                                                            class="ph-duotone ph-file-cloud f-20"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <p class="mb-0">Server</p>
                                                    </div>
                                                </div>
                                                <h5 class="mb-0 f-w-400">{{ $settings['mt5_company_name'] }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="bg-body p-3 rounded">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="flex-shrink-0"><i class="ph-duotone ph-cactus f-20"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <p class="mb-0">Credit</p>
                                                    </div>
                                                </div>
                                                <h5 class="mb-0 f-w-400">${{ $account->Credit ?? '' }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="bg-body p-3 rounded">
                                                <div class="d-flex align-items-center mb-2">
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
                                            <div class="bg-body p-3 rounded">
                                                <div class="d-flex align-items-center mb-2">
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
                                                    <div class="avtar avtar-s border"><i
                                                            class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <p class="text-muted mb-0 f-20"><small>Equity</small></p>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <h4 class="mb-1 f-w-400">${{ $equity ?? '' }}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="avtar avtar-s border"><i
                                                            class="ph-duotone ph-butterfly f-20"></i></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <p class="text-muted mb-0 f-20"><small>Free Margin</small></p>
                                                        </div>
                                                        <div class="col-6 text-end">
                                                            <h4 class="mb-1 f-w-400">${{ $freemargin ?? '' }}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="avtar avtar-s border"><i
                                                            class="ph-duotone ph-chart-pie f-20"></i></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <p class="text-muted mb-0 f-20"><small>Margin</small></p>
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
                                                    <div class="avtar avtar-s border"><i
                                                            class="ph-duotone ph-chart-pie-slice f-20"></i></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <p class="text-muted mb-0 f-20"><small>Margin Level</small></p>
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
                                                    <div class="avtar avtar-s border"><i
                                                            class="ph-duotone ph-gender-female f-20"></i></div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="row g-1">
                                                        <div class="col-6">
                                                            <p class="text-muted mb-0 f-20"><small>Floating P&L</small></p>
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
                                    <div class="row mt-3">
                                        @if ($type != 'demo')
                                            <div class="col-sm-6">
                                                <a href="{{ url('/internal-transfer') }}">
                                                    <div class="card bg-white">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div>
                                                                    <h4 class="mb-0">Internal</h4>
                                                                    <p class="mb-0 text-opacity-75">Transfer</p>
                                                                </div>
                                                                <div class="avtar avtar-s border">
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
                                                <div class="card updatePassword bg-white">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div>
                                                                <h4 class="mb-0">Password</h4>
                                                                <p class="mb-0 text-opacity-75">Update</p>
                                                            </div>
                                                            <div class="avtar avtar-s border">
                                                                <i class="ph-duotone ph-password f-24"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="row mt-3">
                                        <div class="col-sm-6">
                                            <a href="{{ url('/trade-deposit') }}"
                                                class="card bg-primary available-balance-card">
                                                <div class="card-body p-3">
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
                                        <div class="col-sm-6">
                                            <a href="{{ url('/trade-withdrawal') }}"
                                                class="card bg-secondary available-balance-card">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div>
                                                            <h4 class="mb-0 text-white">Withdraw</h4>
                                                            <p class="mb-0 text-white text-opacity-75">Transfer your
                                                                profits</p>
                                                        </div>
                                                        <div class="avtar">
                                                            <i class="ph-duotone ph-bank f-24"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
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
    </script>
@endsection
