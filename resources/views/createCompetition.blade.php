@extends('layouts.crm.crm')
@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="pb-0 mb-0 page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title h2">
                                <h4 class="mb-0">Join Competition</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                {{-- @if ($user->kyc_verify > 0) --}}
                <div class="col-sm-11">
                    <div class="card">
                        {{-- <div class="card-header">
                            <h5>SET UP YOUR COMPETITION ACCOUNT</h5>
                        </div> --}}
                        @if ($results->count() > 0)
                            <div class="card-body">
                                <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                    <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                                        <path
                                            d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z">
                                        </path>
                                    </symbol>
                                </svg>
                                <form method="post" enctype="multipart/form-data" action="{{ route('joinCompetition') }}">
                                    @csrf
                                    <div class="mb-0 form-group">
                                        <div class="row">
                                            <div class="col-3">
                                                <label class="form-label">Choose Account</label>
                                            </div>
                                            <div class="col-9">
                                                <div class="row">
                                                    @foreach ($results as $i => $acc)
                                                        {{-- {{ dd($acc) }} --}}
                                                        <div class="mb-2 col-lg-6 col-xl-4">
                                                            <div class="auth-option">
                                                                {{-- {{ dump($acc->competition_start_date) }}
                                                                {{ dump(now('UTC')) }}
                                                                {{ dump(\Carbon\Carbon::parse($acc->competition_start_date)) }} --}}

                                                                {{-- {{ dump(gte(now('UTC'))) }} --}}
                                                                <input type="hidden" name="start_date"
                                                                    value="{{ $acc->competition_start_date }}">
                                                                <input type="hidden" name="end_date"
                                                                    value="{{ $acc->competition_end_date }}">
                                                                <input type="radio" data-group="{{ $acc->ac_name }}"
                                                                    data-inquiry="{{ $acc->inquiry_status }}"
                                                                    class="btn-check acc-types" {{-- @if ($i==0 &&
                                                                    !\Carbon\Carbon::parse($acc->competition_start_date,
                                                                'UTC')->lte(now('UTC'))) checked @endif --}}
                                                                name="options" id="option{{ $acc->ac_index }}"
                                                                value="{{ $acc->id }}"
                                                                {{-- @if ($i == 0 &&
                                                                !\Carbon\Carbon::parse($acc->competition_start_date,
                                                                'UTC')->lte(now('UTC'))) disabled @endif --}}
                                                                @if (\Carbon\Carbon::parse($acc->competition_start_date, 'UTC')->lt(now('UTC')))
                                                                    disabled
                                                                @endif
                                                                > <label class="auth-megaoption
                                                                                                            {{-- @if($i == 0 && !\Carbon\Carbon::parse($acc->competition_start_date, 'UTC')->lte(now('UTC'))) opacity-50 @endif --}}
                                                                                                            @if (\Carbon\Carbon::parse($acc->competition_start_date, 'UTC')->lt(now('UTC')))
                                                                                                                opacity-50
                                                                                                            @endif
                                                                                                            "
                                                                    for="option{{ $acc->ac_index }}"
                                                                    style="height: 230px !important;">
                                                                    <div class="m-4 d-block" style="width: 80%;" @php
                                                                        echo strtoupper($acc->ac_name) == 'PRO' ? 'style="width: 80% !important;"' : '';
                                                                    @endphp>
                                                                        <span>
                                                                            <span class="h5 d-block">
                                                                                <strong class="float-end">
                                                                                    <span
                                                                                        class="badge bg-light-primary">{{ strtoupper($acc->mt5_group_type) }}</span>
                                                                                </strong>
                                                                                {{ strtoupper($acc->ac_name) }}
                                                                            </span>
                                                                            <hr>
                                                                            <span class="mt-3 h6 d-block f-w-300 f-14"><strong
                                                                                    class="float-end"><span
                                                                                        class="f-w-400 f-16">{{ \Carbon\Carbon::parse($acc->competition_start_date)->format('d-m-Y') }}</span></strong>
                                                                                Start Date </span>
                                                                            <span class="mt-3 h6 d-block f-w-300 f-14"><strong
                                                                                    class="float-end"><span
                                                                                        class="f-w-400 f-16">{{ \Carbon\Carbon::parse($acc->competition_end_date)->format('d-m-Y') }}</span></strong>
                                                                                End Date </span>
                                                                            <span class="mt-3 h6 d-block f-w-300 f-14"><strong
                                                                                    class="float-end"><span
                                                                                        class="f-w-400 f-16">{{ formatToK($acc->ac_min_deposit) }}</span></strong>
                                                                                Deposit Amount </span>
                                                                            <span class="mt-3 h6 d-block f-w-300 f-14"><strong
                                                                                    class="float-end"><span
                                                                                        class="f-w-400 f-16">${{ strtoupper($acc->ac_spread) }}</span></strong>
                                                                                Spread </span>
                                                                            <span class="mt-3 h6 d-block f-w-300 f-14"><strong
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
                                        <div class="mt-5 row is_account">
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
                                        <div class="mt-3 row">
                                            <div class="col-3"> Deposit Amount for Demo Account </div>
                                            <div class="col-9">
                                                <div class="mb-3 input-group"><span class="input-group-text">$</span>
                                                    <input type="number" min="1" max="100000" step="1" name="demo_deposit"
                                                        id="demo_deposit" value="{{ $results[0]->ac_min_deposit ?? '' }}" readonly
                                                        class="form-control">
                                                    <span class="input-group-text" required>.00</span><!---->
                                                </div>
                                                @error('demo_deposit')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="mt-2 row is_account">
                                            <div class="col-3">
                                                <label class="form-label">Nick Name(Optional)</label>
                                            </div>
                                            <div class="col-9">
                                                <input name="nick_name" id="nick_name" type="text" class="form-control fill"
                                                    aria-label="Text">
                                                <div class="invalid-feedback" style="display: block !important;"></div>
                                            </div>
                                        </div>
                                        <div class="mt-3 row is_account">
                                            <div class="col-3"></div>
                                            <div class="col-9">
                                                <div class="gap-2 mt-2 d-grid">
                                                    <button class="btn btn-lg btn-primary" value="Competition Creation"
                                                        name="a[register]" type="submit"><i class="ti ti-plus me-2"></i>
                                                        Create Account</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 row is_inquiry d-none">
                                            <div class="col-3"></div>
                                            <div class="col-9">
                                                <div class="gap-2 mt-2 d-grid w-100">
                                                    <a href="#" class="w-100 contactus-btn">
                                                        <button class="btn btn-lg w-100 btn-primary"
                                                            value="Competition Creation" type="button"><i
                                                                class="ti ti-headset me-2"></i> Contact Us</button>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="card-body">
                                <div class="text-center card-body">
                                    {{-- <div class="text-center me-4"><a href="/transactions/deposit#"><img
                                                src="/assets/images/competition.png" class="w-25" alt="img"></a></div> --}}
                                    <h6 class="mt-2 mb-0 mb-3 text-center text-secondary f-w-400 f-16">No competition accounts are available at the moment.</h6>
                                    <h6 class="mt-2 mb-0 mb-3 text-center text-secondary f-w-400 f-16"> Please check back later
                                        or contact support for more information.
                                    </h6>
                                    <div class="gap-2 mt-4 d-flex flex-column flex-sm-row justify-content-center align-items-center">
                                        <a href="{{ route('competition') }}" class="btn btn-primary">
                                            Browse Past Competitions
                                        </a>
                                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                            Return to Dashboard
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

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
                            <a id="verify-user-kyc" class="mt-3"><button class="btn btn-outline-primary"><span
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
                    window.location.href = '{{ route('competition') }}';
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
            $(".acc-types").change(function () {
                var inquiry_status = $(".acc-types:checked").data("inquiry");
                var inquiry = $(".acc-types:checked").data("group");
                if (inquiry_status == 0) {
                    $(".is_account").removeClass("d-none");
                    $(".is_inquiry").addClass("d-none");
                    var selectedValue = $(".acc-types:checked").val();
                    $("#leverage").html("<option value='' checked>Loading...</option>");
                    $.ajax({
                        url: "{{ route('get-leverage') }}?id=" + selectedValue,
                        success: function (data) {
                            $("#leverage").html("");
                            $.each(data, function (key, value) {
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
            $(".acc-types").trigger("change");

            document.addEventListener('DOMContentLoaded', function () {
                // Get all radio buttons for account selection
                const radios = document.querySelectorAll('.acc-types');
                const depositInput = document.getElementById('demo_deposit');
                // Map account id to min deposit
                const accMinDeposits = {
                    @foreach ($results as $acc)
                        '{{ $acc->id }}': '{{ $acc->ac_min_deposit }}',
                    @endforeach
                                };
            radios.forEach(radio => {
                radio.addEventListener('change', function () {
                    if (this.checked) {
                        depositInput.value = accMinDeposits[this.value];
                    }
                });
            });
                            });
        </script>
@endsection