@extends('layouts.crm.crm')
@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="pb-0 mb-0 page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title h2">
                                <h4 class="mb-0">Fund</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                @if ($user->kyc_verify > 0)
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="p-0 card-body">
                                @include('sub_header')
                            </div>
                        </div>
                        <div class="tab-content">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="row">
                                    {{-- <div class="col-12"> --}}
                                        {{-- <div class="row"> --}}
                                            <div class="col-xl-8">
                                                <div class="card">
                                                    <div class="card-body border-bottom">
                                                        <h6>CREATE DEPOSIT TICKET</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="my-4 divider"><span>SELECT MT5 ACCOUNT</span></div>
                                                        <div class="row g-1">

                                                            @foreach ($liveaccount_details as $liveaccount)
                                                                <div class="col-md-3 col-lg-4 col-xl-4">
                                                                    <div class="border rounded address-check">
                                                                        <div class="form-check paycard">
                                                                            <input id="{{ $liveaccount->code }}"
                                                                                type="radio" name="live-account"
                                                                                class="select-liveaccount form-check-input input-primary"
                                                                                data-mindeposit="{{ $liveaccount->accountType->ac_min_deposit }}"
                                                                                data-maxdeposit="{{ $liveaccount->accountType->ac_max_deposit }}"
                                                                                value="{{ $liveaccount->id }}">
                                                                            <label class="form-check-label d-block" required>
                                                                                <div class="p-1 my-1">
                                                                                    <span class="row">
                                                                                        <span class="mt-1 col-6">
                                                                                            <span class="pb-0 mb-0 h5 d-block f-w-500 f-14">
                                                                                                <img src="/assets/images/mt5.png" alt="user-image" class="wid-25 me-1 ms-1">{{ $liveaccount->code }}</span>
                                                                                        </span>
                                                                                        <span class="pb-0 mb-0 col-6 text-end pe-3">
                                                                                            <span class="mb-0 h5 d-block f-w-500">
                                                                                                ${{ $liveaccount->balance - $liveaccount->totalBonusDeposit }}
                                                                                            </span>
                                                                                            <span
                                                                                                class="mb-0 text-muted f-10">Current
                                                                                                Balance</span>
                                                                                        </span>
                                                                                    </span>
                                                                                </div>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="my-4 divider"><span>SELECT PAYMENT METHOD</span>
                                                        </div>
                                                        <div class="row g-1">
                                                            @if(isset($settings['enable_cryptochill']) && $settings['enable_cryptochill'] === '1')
                                                                <div class="col-6 col-lg-6 col-xl-6">
                                                                    <div
                                                                        class="border rounded address-check trade-deposit-type">
                                                                        <div class="form-check">
                                                                            <input type="radio" name="deposit_type"
                                                                                checked
                                                                                class="form-check-input input-primary tradefund-deposit"
                                                                                id="cryptochill" value="CryptoChill"
                                                                                data-type="CryptoChill">
                                                                            <label class="form-check-label d-block"
                                                                                for="cryptochill">
                                                                                <span class="p-2 card-body d-block">
                                                                                    <span
                                                                                        class="d-flex align-items-center justify-content-between">
                                                                                        <span>Crypto</span>
                                                                                            <span
                                                                                                class="mb-1 h6 f-w-500 d-block" style="text-align: end;">
                                                                                                <img src="/assets/images/crypto_payments2.png"
                                                                                                    alt="CryptoChill" class="w-xs-75 w-md-25">
                                                                                            </span>
                                                                                    </span>
                                                                                </span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            @if(isset($settings['enable_creditcardpayissa']) && $settings['enable_creditcardpayissa'] === '1')
                                                            <div class="col-6 col-lg-6 col-xl-6">
                                                                <div class="border rounded address-check trade-deposit-type">
                                                                    <div class="form-check">
                                                                        <input type="radio" name="deposit_type"
                                                                            checked
                                                                            class="form-check-input input-primary tradefund-deposit"
                                                                            id="option_cc" value="CreditCardPayissa"
                                                                            data-type="CreditCardPayissa">
                                                                        <label class="form-check-label d-block"
                                                                            for="option_cc">
                                                                            <span class="p-2 card-body d-block">
                                                                                <span
                                                                                    class="d-flex align-items-center justify-content-between">
                                                                                    <span class="no-wrap">Credit Card</span>
                                                                                        <span
                                                                                            class="mb-1 h6 f-w-500 d-block" style="text-align: end;">
                                                                                            <img src="/assets/images/credit-card.png"
                                                                                                alt="Credit Card"
                                                                                                class="w-xs-100 w-md-25">
                                                                                        </span>
                                                                                </span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                        <div class="my-4 divider"><span>DEPOSIT DETAILS</span></div>
                                                        @if(isset($settings['enable_cryptochill']) && $settings['enable_cryptochill'] === '1')
                                                            <div class="CryptoChill trade-deposit-details">
                                                            <form method="post">
                                                                    @csrf
                                                                    <input type="hidden" name="user[email]"
                                                                        value="{{ session('clogin') }}" min="10"
                                                                        required class="form-control fill">
                                                                    <input class="user_code form-control fill" type="hidden"
                                                                        name="code" value="">

                                                                <div class="row">
                                                                    <div class="mt-2 col-12">
                                                                        <input type="hidden"
                                                                            name="user[deposit_type]"
                                                                            class="tradedeposittype"
                                                                            value="CryptoChill">
                                                                        <div class="form-group row">
                                                                            <label
                                                                                class="col-lg-4 col-form-label">ENTER
                                                                                PROMOCODE:
                                                                                <small
                                                                                    class="text-muted d-block">Please
                                                                                    enter promocode</small>
                                                                            </label>
                                                                            <div class="col-lg-8">
                                                                                <div class="mb-3 input-group">
                                                                                    <input name="promocode"
                                                                                        id="promocode"
                                                                                        type="text"
                                                                                        class="form-control fill"
                                                                                        placeholder="Promocode"
                                                                                        aria-label="promocode">
                                                                                    <button type="button" id="verifyPromocodeBtn" class="btn btn-primary">Verify</button>
                                                                                </div>
                                                                                <small id="promocodeStatus" class="text-muted"></small>
                                                                            </div>

                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label
                                                                                class="col-lg-4 col-form-label">ENTER
                                                                                AMOUNT:
                                                                                <small
                                                                                    class="text-muted d-block">Please
                                                                                    enter the amount to be
                                                                                    deposited</small>
                                                                            </label>
                                                                            <div class="col-lg-8">
                                                                                <div class="mb-3 input-group">
                                                                                    <span
                                                                                        class="input-group-text">USD</span>
                                                                                    <input name="user[deposit]"
                                                                                        id="crypto_deposit_amount"
                                                                                        min="10" type="number"
                                                                                        class="form-control fill tradedeposit_amount"
                                                                                        placeholder="Minimum $10"
                                                                                        aria-label="Amount" required>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-lg-4"></div>
                                                                            <div class="col-lg-8 pb-4">
                                                                                <div class="form-check">
                                                                                    <input class="form-check-input mt-1" type="checkbox" id="cryptoWarningCheckbox" name="confirmcryptoCheckbox"  required>
                                                                                    <label class="form-check-label" for="cryptoWarningCheckbox">
                                                                                        Please ensure you select the correct cryptocurrency to the correct account and network. Transactions are irreversible, and we are not responsible for any loss of funds due to incorrect deposits. Double-check all details before proceeding.
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="">
                                                                                <div class="row">
                                                                                    <div class="col-lg-4"></div>
                                                                                    <div class="col-lg-8">
                                                                                        <div class="row g-1">
                                                                                            <input type="button"
                                                                                                id="paynow"
                                                                                                data-amount="10"
                                                                                                data-currency="USD"
                                                                                                data-product="Deposit To: {{ $settings['mt5_company_name'] }}"
                                                                                                class="btn btn-primary cryptochill-button col-12"
                                                                                                value="Deposit To Trading Account">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                        @endif
                                                        @if(isset($settings['enable_creditcardpayissa']) && $settings['enable_creditcardpayissa'] === '1')
                                                            <div class="CreditCardPayissa trade-deposit-details" style="display:none">
                                                                <form method="post" id="CreditCardPayissaForm">
                                                                    @csrf
                                                                    <input type="hidden" name="email"
                                                                        value="{{ session('clogin') }}" min="10"
                                                                        required class="form-control fill">
                                                                    <input class="user_code" type="hidden"
                                                                        name="user[code]" value=""
                                                                        class="form-control fill" readonly required>
                                                                    {{-- <input type="hidden" name="selected_account_code" id="selected_account_code" value=""> --}}
                                                                    <div class="row">
                                                                        <div class="mt-2 col-12">
                                                                            <input type="hidden" name="deposit_type"
                                                                                class="tradedeposittype"
                                                                                value="CreditCardPayissa">

                                                                            <div class="form-group row">
                                                                                <label
                                                                                    class="col-lg-4 col-form-label">ENTER
                                                                                    PROMOCODE:
                                                                                    <small
                                                                                        class="text-muted d-block">Please
                                                                                        enter promocode</small>
                                                                                </label>
                                                                                <div class="col-lg-8">
                                                                                    <div class="mb-3 input-group">
                                                                                        <input name="cc_promocode"
                                                                                            id="cc_promocode"
                                                                                            type="text"
                                                                                            class="form-control fill"
                                                                                            placeholder="Promocode"
                                                                                            aria-label="promocode">
                                                                                        <button type="button" id="verifyCcPromocodeBtn" class="btn btn-primary">Verify</button>
                                                                                    </div>
                                                                                    <small id="cc_promocodeStatus" class="text-muted"></small>
                                                                                </div>
                                                                            </div>

                                                                            <div class="form-group row">
                                                                                <label
                                                                                    class="col-lg-4 col-form-label">ENTER
                                                                                    AMOUNT:
                                                                                    <small
                                                                                        class="text-muted d-block">Please
                                                                                        enter the amount to be deposited </small>
                                                                                </label>
                                                                                <div class="col-lg-8">
                                                                                    <div class="mb-3 input-group">
                                                                                        <span
                                                                                            class="input-group-text">USD</span>
                                                                                        <input placeholder="Minimum $10"
                                                                                            name="deposit"
                                                                                            id="deposit_amount_cc"
                                                                                            type="number" min="10"
                                                                                            title="Minimum $10"
                                                                                            class="form-control fill ccdeposit_amount"
                                                                                            aria-label="Amount" required>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-lg-4"></div>
                                                                                <div class="col-lg-8 pb-4">
                                                                                    <div class="form-check">
                                                                                        <input class="form-check-input mt-1" type="checkbox" id="cryptoWarningCheckbox" name="confirmcryptoCheckbox"  >
                                                                                        <label class="form-check-label" for="cryptoWarningCheckbox">
                                                                                            Card deposit options vary by country. If your card is not accepted, try a different card & phone number. If the issue persists, This option may not be available in your country.
                                                                                            In that case, please use cryptocurrency to deposit.
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="">
                                                                                <div class="row">
                                                                                    <div class="col-lg-4"></div>
                                                                                    <div class="col-lg-8">
                                                                                        <div class="row g-1">
                                                                                            <input type="submit"
                                                                                                name="ccpay"
                                                                                                id="ccpay"
                                                                                                class="btn btn-primary col-12"
                                                                                                value="Deposit To Trading Account">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4">
                                                <div class="card coupon-card bg-primary">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div
                                                                class="col-8 d-flex flex-column align-items-start justify-content-center">
                                                                <h3 class="text-white f-w-500">Fuel Your Trading Journey</h3>
                                                                <span class="py-2 text-white f-16">Deposit now and unlock the
                                                                    gateway to global markets.</span>
                                                            </div>
                                                            <div class="col-4 text-end">
                                                                <img src="{{ asset('assets/images/fund_now.png') }}"
                                                                    alt="img" class="img-fluid wid-110">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>MT5 ACCOUNTS SUMMARY</h5>
                                                    </div>
                                                    <div class="p-0 card-body">
                                                        <ul class="list-group list-group-flush">
                                                            @foreach ($liveaccount_details as $liveaccount)
                                                                <li class="list-group-item">
                                                                    <div class="media align-items-start">
                                                                        <span class="pb-0 mb-0 h4 d-block f-w-500">
                                                                            <img src="{{ asset('assets/images/mt5.png') }}"
                                                                                alt="user-image" class="wid-25 me-1 ms-1">
                                                                        </span>
                                                                        <div class="mx-2 media-body">
                                                                            <h5 class="mb-1">
                                                                                <span
                                                                                    class="pb-0 mb-0 h4 d-block f-w-500">{{ $liveaccount->code }}</span>
                                                                            </h5>
                                                                            <p class="mb-2 text-sm"><span
                                                                                    class="text-muted">ACCOUNT CATEGORY:</span>
                                                                                ECN</p>
                                                                            <div class="border-dashed border-top">
                                                                                <p class="mt-2 mb-1 d-grid">
                                                                                    <span class="text-muted">LEVERAGE: {{ $liveaccount->leverage }}</span>
                                                                                    <span class="text-muted">CREDIT: ${{ $liveaccount->credit }}</span>
                                                                                    <span class="text-muted">EQUITY: ${{ $liveaccount->equity }}</span>
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="flex-shrink-0">
                                                                            <h4 class="f-w-500">${{ $liveaccount->balance??0 }}
                                                                            </h4>
                                                                            <p class="mb-2 text-sm text-muted text-end">Balance
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                            <li class="list-group-item">
                                                                <div class="float-end">
                                                                    <h4 class="mb-0 fw-medium">${{ $totals->credit }}</h4>
                                                                </div>
                                                                <span class="text-muted">TOTAL CREDIT</span>
                                                            </li>
                                                            <li class="list-group-item">
                                                                <div class="float-end">
                                                                    <h4 class="mb-0 fw-medium">${{ $totals->equity }}</h4>
                                                                </div>
                                                                <span class="text-muted">TOTAL EQUITY</span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="card">
                                                    <div class="py-2 card-body">
                                                        <ul class="list-group list-group-flush">
                                                            <li class="px-0 list-group-item">
                                                                <div class="float-end">
                                                                    <h3 class="mb-0 fw-medium">${{ $totals->balance }}</h3>
                                                                </div>
                                                                <h5 class="mb-0 d-inline-block">TOTAL BALANCE</h5>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        {{-- </div> --}}
                                    {{-- </div> --}}
                                </div>
                        </div>
                    </div>
                @else
                    <div class="card support-tickets ribbon-box border ribbon-fill shadow-none pb-1">
                        <div class="row p-3">
                            <div class="card-body text-center">
                                <div class="text-center me-4"><a href="/transactions/deposit#"><img
                                            src="/assets/images/doc_upload.png" class="w-25" alt="img"></a></div>
                                <h6 class="text-center text-secondary mb-3 mt-2 f-w-400 mb-0 f-16">KYC Not Yet Verified !
                                </h6>
                                <a  id="verify-user-kyc" class="mt-3"><button class="btn btn-outline-primary"><span
                                            class="text-truncate">Verify Now To Proceed</span></button></a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @include('add_amount_to_account')
    {{-- <script>
        document.addEventListener("DOMContentLoaded", function () {
            const selectedIdInput = document.getElementById("selected_account_code");

            // Handle radio button change
            $('.select-liveaccount').on('change', function () {
                const clientAccountId = $(this).val();
                selectedIdInput.value = clientAccountId; // Update hidden input value
            });

            // Optional: prevent form submission if no account selected
            const form = document.getElementById("CreditCardPayissaForm");
            form.addEventListener("submit", function (e) {
                if (!selectedIdInput.value) {
                    e.preventDefault();
                    alert("Please select a trading account.");
                }
            });
        });
    </script> --}}
    <script>
        document.addEventListener("DOMContentLoaded", function (){
            $('form').on('submit', function(e) {
                var promocode = $('#promocode').val();
                // Now you have the promocode value
                console.log('Promocode:', promocode);
            });

            $('#verifyPromocodeBtn').click(function() {
                const promocode = $('#promocode').val().trim();
                const statusElement = $('#promocodeStatus');
                statusElement.text('Checking...').removeClass('text-success text-danger');

                if (promocode === '') {
                    statusElement.text('Please enter a promocode.').addClass('text-danger');
                    return;
                }
                $.ajax({
                    url: '{{ route("verify.promocode") }}', // Adjust route as per your setup
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        promocode: promocode
                    },
                    success: function(response) {
                        if (response.valid) {
                            statusElement.text(response.message).addClass('text-success');
                        } else {
                            statusElement.text(response.message).addClass('text-danger');
                        }
                    },
                    error: function(xhr) {
                        statusElement.text('Error verifying promocode.').addClass('text-danger');
                    }
                });
            });

            $('#verifyCcPromocodeBtn').click(function() {
                const promocode = $('#cc_promocode').val().trim();
                const statusElement = $('#cc_promocodeStatus');
                statusElement.text('Checking...').removeClass('text-success text-danger');

                if (promocode === '') {
                    statusElement.text('Please enter a promocode.').addClass('text-danger');
                    return;
                }
                $.ajax({
                    url: '{{ route("verify.promocode") }}', // Adjust route as per your setup
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        promocode: promocode
                    },
                    success: function(response) {
                        if (response.valid) {
                            statusElement.text(response.message).addClass('text-success');
                        } else {
                            statusElement.text(response.message).addClass('text-danger');
                        }
                    },
                    error: function(xhr) {
                        statusElement.text('Error verifying promocode.').addClass('text-danger');
                    }
                });
            });
        });
    </script>
@endsection
