@extends('layouts.crm.crm')
@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="pb-0 mb-0 page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title h2">
                                <h4 class="mb-0">Create Demo Trading Account</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                {{-- @if ($user->kyc_verify > 0) --}}
                    <div class="col-sm-11">
                        <div class="card">
                            <div class="card-header">
                                <h5>SET UP YOUR ACCOUNT</h5>
                            </div>
                            <div class="card-body">
                                <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                    <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                                        <path
                                            d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z">
                                        </path>
                                    </symbol>
                                </svg>
                                <form method="post" enctype="multipart/form-data"
                                    action="{{ route('create-demo-account') }}">
                                    @csrf

                                    <!-- Platform Selection -->
                                    <div class="mb-4 form-group">
                                        <div class="row">
                                            <div class="col-3">
                                                <label class="form-label">Choose Trading Platform</label>
                                            </div>
                                            <div class="col-9">
                                                <div class="row">
                                                    <div class="mb-2 col-lg-6 col-xl-6">
                                                        <div class="auth-option">
                                                            <input type="radio" class="btn-check platform-select"
                                                                   checked name="platform" id="platformMT5" value="mt5">
                                                            <label class="auth-megaoption" for="platformMT5" style="height: 180px !important;">
                                                                <div class="m-4 text-center d-block">
                                                                    <img src="/assets/images/mt5.png" alt="MT5" class="mb-3" style="width: 64px; height: 64px;">
                                                                    <span class="h5 d-block">
                                                                        <strong>MetaTrader 5</strong>
                                                                    </span>
                                                                    <span class="mt-2 h6 d-block f-w-400 f-12">
                                                                        The world's most popular trading platform with advanced features.
                                                                    </span>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 col-lg-6 col-xl-6">
                                                        <div class="auth-option">
                                                            <input type="radio" class="btn-check platform-select"
                                                                   name="platform" id="platformX9" value="x9">
                                                            <label class="auth-megaoption" for="platformX9" style="height: 180px !important;">
                                                                <div class="m-4 text-center d-block">
                                                                    <img src="/images/x92.png" alt="X9" class="mb-3" style="width: 64px; height: 64px;">
                                                                    <span class="h5 d-block">
                                                                        <strong>X9 Platform</strong>
                                                                    </span>
                                                                    <span class="mt-2 h6 d-block f-w-400 f-12">
                                                                        Advanced trading platform with cutting-edge technology.
                                                                    </span>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                @error('platform')
                                                    <div class="invalid-feedback" style="display: block !important;">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-0 form-group mt5-options">
                                        <div class="row">
                                            <div class="col-3">
                                                <label class="form-label">Choose Account Type</label>
                                            </div>
                                            <div class="col-9">
                                                <div class="row">
                                                    @foreach ($results as $i => $acc)
                                                        <div class="mb-2 col-lg-6 col-xl-4">
                                                            <div class="auth-option">
                                                                <input type="radio" data-group="{{ $acc->ac_name }}"
                                                                    data-inquiry="{{ $acc->inquiry_status }}"
                                                                    class="btn-check acc-types"
                                                                    {{ $i == 0 ? 'checked' : '' }} name="options"
                                                                    id="option{{ $acc->ac_index }}"
                                                                    value="{{ $acc->id }}">
                                                                <label class="auth-megaoption"
                                                                    for="option{{ $acc->ac_index }}"
                                                                    style="height: 230px !important;">
                                                                    <div class="m-4 d-block">
                                                                        <span>
                                                                            <span class="h5 d-block">
                                                                                <strong class="float-end">
                                                                                    <span
                                                                                        class="badge bg-light-primary">{{ strtoupper($acc->mt5_group_type) }}</span>
                                                                                </strong>
                                                                                {{ strtoupper($acc->ac_name) }}
                                                                            </span>
                                                                            <span class="mt-4 h6 d-block f-w-400 f-12"> A
                                                                                commission-free account, perfect for new
                                                                                traders to start investing. </span>
                                                                            <hr>
                                                                            <span
                                                                                class="mt-3 h6 d-block f-w-300 f-14"><strong
                                                                                    class="float-end"><span
                                                                                        class="f-w-400 f-16">{{ strtoupper($acc->ac_min_deposit) }}$</span></strong>
                                                                                Minimum Deposit </span>
                                                                            <span
                                                                                class="mt-3 h6 d-block f-w-300 f-14"><strong
                                                                                    class="float-end"><span
                                                                                        class="f-w-400 f-16">{{ strtoupper($acc->ac_spread) }}$</span></strong>
                                                                                Spread </span>
                                                                            <span
                                                                                class="mt-3 h6 d-block f-w-300 f-14"><strong
                                                                                    class="float-end"><span
                                                                                        class="f-w-400 f-16">Yes</span></strong>
                                                                                Swap </span>
                                                                        </span>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    <div class="invalid-feedback" style="display: block !important;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- X9 Account Types (Initially Hidden) -->
                                    <div class="mb-0 form-group x9-options d-none">
                                        <div class="row">
                                            <div class="col-3">
                                                <label class="form-label">Choose Account Type</label>
                                            </div>
                                            <div class="col-9">
                                                <div class="row">
                                                    @foreach ($results as $i => $acc)
                                                        <div class="mb-2 col-lg-6 col-xl-4">
                                                            <div class="auth-option">
                                                                <input type="radio" data-group="{{ $acc->ac_name }}"
                                                                    data-inquiry="{{ $acc->inquiry_status }}"
                                                                    class="btn-check x9-acc-types"
                                                                    {{ $i == 0 ? 'checked' : '' }} name="x9_options"
                                                                    id="x9_option{{ $acc->ac_index }}"
                                                                    value="{{ $acc->id }}">
                                                                <label class="auth-megaoption"
                                                                    for="x9_option{{ $acc->ac_index }}"
                                                                    style="height: 230px !important;">
                                                                    <div class="m-4 d-block">
                                                                        <span>
                                                                            <span class="h5 d-block">
                                                                                <strong class="float-end">
                                                                                    <span
                                                                                        class="badge bg-light-danger">X9</span>
                                                                                </strong>
                                                                                {{ strtoupper($acc->ac_name) }}
                                                                            </span>
                                                                            <span class="mt-4 h6 d-block f-w-400 f-12"> X9 Platform account, optimized for advanced trading strategies. </span>
                                                                            <hr>
                                                                            <span
                                                                                class="mt-3 h6 d-block f-w-300 f-14"><strong
                                                                                    class="float-end"><span
                                                                                        class="f-w-400 f-16">{{ strtoupper($acc->ac_min_deposit) }}$</span></strong>
                                                                                Minimum Deposit </span>
                                                                            <span
                                                                                class="mt-3 h6 d-block f-w-300 f-14"><strong
                                                                                    class="float-end"><span
                                                                                        class="f-w-400 f-16">{{ strtoupper($acc->ac_spread) }}$</span></strong>
                                                                                Spread </span>
                                                                            <span
                                                                                class="mt-3 h6 d-block f-w-300 f-14"><strong
                                                                                    class="float-end"><span
                                                                                        class="f-w-400 f-16">{{ $acc->ac_swap }}</span></strong>
                                                                                Swap </span>
                                                                        </span>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    <div class="invalid-feedback" style="display: block !important;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                        <div class="mt-5 row is_account mt5-leverage">
                                            <div class="col-3">
                                                <label class="form-label">Select Leverage</label>
                                            </div>
                                            <div class="col-9">
                                                <select class="form-select" name="leverage" id="leverage">
                                                    <!-- Options should be populated dynamically -->
                                                </select>
                                                <div class="invalid-feedback" style="display: block !important;"></div>
                                            </div>
                                        </div>

                                        <!-- X9 Leverage (Initially Hidden) -->
                                        {{-- <div class="mt-5 row is_account x9-leverage d-none">
                                            <div class="col-3">
                                                <label class="form-label">Select Leverage</label>
                                            </div>
                                            <div class="col-9">
                                                <select class="form-select" name="x9_leverage" id="x9_leverage">
                                                    <option value="1:50">1:50</option>
                                                    <option value="1:100" selected>1:100</option>
                                                    <option value="1:200">1:200</option>
                                                    <option value="1:300">1:300</option>
                                                    <option value="1:400">1:400</option>
                                                    <option value="1:500">1:500</option>
                                                </select>
                                                <div class="invalid-feedback" style="display: block !important;"></div>
                                            </div>
                                        </div> --}}

                                        <div class="mt-3 row">
                                            <div class="col-3">  Deposit Amount for Demo Account </div>
                                            <div class="col-9">
                                              <div class="mb-3 input-group"><span class="input-group-text">$</span><input type="number" min="1" max="100000" step="1" name="demo_deposit" id="demo_deposit" required class="form-control" aria-label="Amount (to the nearest dollar)"><span class="input-group-text" required>.00</span><!----></div>
                                              @error('demo_deposit')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="mt-3 row is_account">
                                            <div class="col-3"></div>
                                            <div class="col-9">
                                                <div class="gap-2 mt-2 d-grid">
                                                    <button id="createAccountBtn" class="btn btn-lg btn-primary" value="Live Account Creation"
                                                        name="a[register]" type="submit">
                                                        <span id="btnText"><i class="ti ti-plus me-2"></i>Create Account</span>
                                                        <span id="btnLoading" class="d-none">
                                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                            Creating Account...
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 row is_inquiry d-none">
                                            <div class="col-3"></div>
                                            <div class="col-9">
                                                <div class="gap-2 mt-2 d-grid w-100">
                                                    <a href="#" class="w-100 contactus-btn">
                                                        <button class="btn btn-lg w-100 btn-primary"
                                                            value="Live Account Creation" type="button"><i
                                                                class="ti ti-headset me-2"></i> Contact Us</button>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                </form>
                            </div>
                        </div>
                    </div>
                {{-- @else
                    <div class="pb-1 border shadow-none card support-tickets ribbon-box ribbon-fill">
                        <div class="p-3 row">
                            <div class="text-center card-body">
                                <div class="text-center me-4"><a href="/transactions/deposit#"><img
                                            src="/assets/images/doc_upload.png" class="w-25" alt="img"></a></div>
                                <h6 class="mt-2 mb-0 mb-3 text-center text-secondary f-w-400 f-16">KYC Not Yet Verified !
                                </h6>
                                <a  id="verify-user-kyc" class="mt-3"><button class="btn btn-outline-primary"><span
                                            class="text-truncate">Verify Now To Proceed</span></button></a>
                            </div>
                        </div>
                    </div>
                @endif --}}
            </div>
        </div>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}'
            }).then(() => {
                window.location.href = '{{ route('demoAccounts') }}';
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: "Something Went Wrong !!!!",
                text: '{{ session('error') }}',
            });
        </script>
    @endif
    <script>
        // Platform selection handler
        $(".platform-select").change(function() {
            var selectedPlatform = $(".platform-select:checked").val();

            if (selectedPlatform === 'mt5') {
                // Show MT5 options, hide X9 options
                $(".mt5-options").removeClass("d-none");
                $(".mt5-leverage").removeClass("d-none");
                $(".x9-options").addClass("d-none");
                $(".x9-leverage").addClass("d-none");

                // Trigger account type change for MT5
                $(".acc-types").trigger("change");
            } else if (selectedPlatform === 'x9') {
                // Show X9 options, hide MT5 options
                $(".mt5-options").addClass("d-none");
                $(".mt5-leverage").addClass("d-none");
                $(".x9-options").removeClass("d-none");
                $(".x9-leverage").removeClass("d-none");

                // Trigger account type change for X9
                $(".x9-acc-types").trigger("change");
            }
        });

        $(".acc-types").change(function() {
            var selectedPlatform = $(".platform-select:checked").val();

            // Only process if MT5 is selected
            if (selectedPlatform !== 'mt5') return;

            var inquiry_status = $(".acc-types:checked").data("inquiry");
            var inquiry = $(".acc-types:checked").data("group");
            var demoDepositInput = $("#demo_deposit");

            // Reset deposit input state
            demoDepositInput.prop('readonly', false);

            // Check if selected account is Competition Account
            if (inquiry == 'June 8-14 Trading Competition Account' || inquiry == 'June 15-21 Trading Competition Account') {
                demoDepositInput.val(10000);
                demoDepositInput.prop('readonly', true);
            }

            if (inquiry_status == 0) {
                $(".is_account").removeClass("d-none");
                $(".is_inquiry").addClass("d-none");
                var selectedValue = $(".acc-types:checked").val();
                $("#leverage").html("<option value='' checked>Loading...</option>");
                $.ajax({
                    url: "{{ route('get-leverage') }}?id=" + selectedValue,
                    success: function(data) {
                        $("#leverage").html("");
                        $.each(data, function(key, value) {
                            $("#leverage").append("<option value='" + value.account_leverage +
                                "'>" + value.account_leverage + "</option>");
                        });
                    }
                })
            } else {
                $(".is_account").addClass("d-none");
                $(".is_inquiry").removeClass("d-none");
                var href = "/support?reg=" + inquiry;
                $(".contactus-btn").attr("href", href);
            }
        });

        // X9 Account type selection handler
        $(".x9-acc-types").change(function() {
            var inquiry_status = $(".x9-acc-types:checked").data("inquiry");
            var inquiry = $(".x9-acc-types:checked").data("group");
            var demoDepositInput = $("#demo_deposit");

            // Reset deposit input state
            demoDepositInput.prop('readonly', false);

            // Check if selected account is Competition Account
            if (inquiry == 'June 8-14 Trading Competition Account' || inquiry == 'June 15-21 Trading Competition Account') {
                demoDepositInput.val(10000);
                demoDepositInput.prop('readonly', true);
            }

            if (inquiry_status == 0) {
                $(".is_account").removeClass("d-none");
                $(".is_inquiry").addClass("d-none");
                // X9 doesn't need leverage loading from server since it has fixed options
            } else {
                $(".is_account").addClass("d-none");
                $(".is_inquiry").removeClass("d-none");
                var href = "/support?reg=" + inquiry;
                $(".contactus-btn").attr("href", href);
            }
        });

        // Initialize on page load
        $(".platform-select").trigger("change");
        $(".acc-types").trigger("change");

        // Prevent duplicate form submissions
        $('form').on('submit', function(e) {
            const $btn = $('#createAccountBtn');
            const $btnText = $('#btnText');
            const $btnLoading = $('#btnLoading');
            
            // Check if button is already disabled (prevent double submission)
            if ($btn.prop('disabled')) {
                e.preventDefault();
                return false;
            }
            
            // Disable button and show loading state
            $btn.prop('disabled', true);
            $btnText.addClass('d-none');
            $btnLoading.removeClass('d-none');
            
            // Re-enable button after 12 seconds as fallback (in case of error)
            setTimeout(function() {
                $btn.prop('disabled', false);
                $btnText.removeClass('d-none');
                $btnLoading.addClass('d-none');
            }, 12000);
        });
    </script>
@endsection
