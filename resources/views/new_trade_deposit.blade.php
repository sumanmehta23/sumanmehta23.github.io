@extends('layouts.crm.crm')
@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="pb-0 mb-0 page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title h2">
                                {{-- <h4 class="mb-0">Fund</h4> --}}
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
                                                                    data-group="{{ $liveaccount->accountType->ac_group }}"
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
                                                                                <span class="mb-0 text-muted f-10">Current Balance</span>
                                                                            </span>
                                                                        </span>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="my-4 divider"><span>SELECT PAYMENT METHOD</span></div>
                                            <div class="row g-1">
                                                @if(isset($settings['enable_ragapay']) && $settings['enable_ragapay'] === '1')
                                                    <div class="col-6 col-lg-6 col-xl-6">
                                                        <div class="border rounded address-check trade-deposit-type">
                                                            <div class="form-check">
                                                                <input type="radio" name="deposit_type"
                                                                    checked
                                                                    class="form-check-input input-primary tradefund-deposit"
                                                                    id="ragapay" value="RagaPay"
                                                                    data-type="RagaPay">
                                                                <label class="form-check-label d-block" for="ragapay">
                                                                    <span class="p-2 card-body d-block">
                                                                        <span class="d-flex align-items-center justify-content-between">
                                                                            <span>Ragapay</span>
                                                                            <span class="mb-1 h6 f-w-500 d-block" style="text-align: end;">
                                                                                <img src="/assets/images/wallet.png" alt="RagaPay" class="w-xs-75 w-md-25">
                                                                            </span>
                                                                        </span>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if(isset($settings['enable_cryptochill']) && $settings['enable_cryptochill'] === '1')
                                                    <div class="col-6 col-lg-6 col-xl-6">
                                                        <div class="border rounded address-check trade-deposit-type">
                                                            <div class="form-check">
                                                                <input type="radio" name="deposit_type"
                                                                    checked
                                                                    class="form-check-input input-primary tradefund-deposit"
                                                                    id="cryptochill" value="CryptoChill"
                                                                    data-type="CryptoChill">
                                                                <label class="form-check-label d-block" for="cryptochill">
                                                                    <span class="p-2 card-body d-block">
                                                                        <span class="d-flex align-items-center justify-content-between">
                                                                            <span>Crypto</span>
                                                                            <span class="mb-1 h6 f-w-500 d-block" style="text-align: end;">
                                                                                <img src="/assets/images/crypto_payments2.png" alt="CryptoChill" class="w-xs-75 w-md-25">
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
                                                                    class="form-check-input input-primary tradefund-deposit"
                                                                    id="option_cc" value="CreditCardPayissa"
                                                                    data-type="CreditCardPayissa">
                                                                <label class="form-check-label d-block" for="option_cc">
                                                                    <span class="p-2 card-body d-block">
                                                                        <span class="d-flex align-items-center justify-content-between">
                                                                            <span class="no-wrap">Credit Card</span>
                                                                            <span class="mb-1 h6 f-w-500 d-block" style="text-align: end;">
                                                                                <img src="/assets/images/credit-card.png" alt="Credit Card" class="w-xs-100 w-md-25">
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
                                             @if(isset($settings['enable_ragapay']) && $settings['enable_ragapay'] === '1')
                                                <div class="RagaPay trade-deposit-details">
                                                    <form method="post" id="RagaPayForm" action="{{ route('trade-deposit_store') }}">
                                                        @csrf
                                                        <input type="hidden" name="email" value="{{ session('clogin') }}" min="10" required class="form-control fill">
                                                        <input class="user_code" type="hidden" name="user[code]" value="" class="form-control fill" readonly required>
                                                        <div class="row">
                                                            <div class="mt-2 col-12">
                                                                <input type="hidden" name="deposit_type" class="tradedeposittype" value="RagaPay">
                                                                <div class="form-group row promo-field-ragapay">
                                                                    <label class="col-lg-4 col-form-label">ENTER PROMOCODE:
                                                                        <small class="text-muted d-block">Please enter promocode</small>
                                                                    </label>
                                                                    <div class="col-lg-8">
                                                                        <div class="mb-3 input-group">
                                                                            <input name="raga_promocode" id="raga_promocode" type="text" class="form-control fill" placeholder="Promocode" aria-label="promocode">
                                                                            <button type="button" id="verifyRagaPromocodeBtn" class="btn btn-primary">Verify</button>
                                                                        </div>
                                                                        <small id="raga_promocodeStatus" class="text-muted"></small>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-lg-4 col-form-label">ENTER AMOUNT:
                                                                        <small class="text-muted d-block">Please enter the amount to be deposited</small>
                                                                    </label>
                                                                    <div class="col-lg-8">
                                                                        <div class="mb-3 input-group">
                                                                            <span class="input-group-text">USD</span>
                                                                            <input placeholder="Minimum $10" name="deposit" id="deposit_amount_raga" type="number" min="10" title="Minimum $10" class="form-control fill ragadeposit_amount" aria-label="Amount" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-4"></div>
                                                                    <div class="col-lg-8 pb-4">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input mt-1" type="checkbox" id="ragaWarningCheckbox" name="confirmcryptoCheckbox">
                                                                            <label class="form-check-label" for="ragaWarningCheckbox">
                                                                                I confirm that I have reviewed the payment details and understand that this transaction will be processed through RagaPay payment gateway. I agree to proceed with this payment.
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="">
                                                                    <div class="row">
                                                                        <div class="col-lg-4"></div>
                                                                        <div class="col-lg-8">
                                                                            <div class="row g-1">
                                                                                <input type="submit" name="ragapay" id="ragapay-submit-btn" class="btn btn-primary col-12" value="Deposit To Trading Account" disabled>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                            @if(isset($settings['enable_cryptochill']) && $settings['enable_cryptochill'] === '1')
                                                <div class="CryptoChill trade-deposit-details">
                                                    <form method="post">
                                                        @csrf
                                                        <input type="hidden" name="user[email]" value="{{ session('clogin') }}" min="10" required class="form-control fill">
                                                        <input class="user_code form-control fill" type="hidden" name="code" value="">
                                                        <div class="row">
                                                            <div class="mt-2 col-12">
                                                                <input type="hidden" name="user[deposit_type]" class="tradedeposittype" value="CryptoChill">
                                                                {{-- <div class="form-group row">
                                                                    <label class="col-lg-4 col-form-label">ENTER PROMOCODE:
                                                                        <small class="text-muted d-block">Please enter promocode</small>
                                                                    </label>
                                                                    <div class="col-lg-8">
                                                                        <div class="mb-3 input-group">
                                                                            <input name="promocode" id="promocode" type="text" class="form-control fill" placeholder="Promocode" aria-label="promocode">
                                                                            <button type="button" id="verifyPromocodeBtn" class="btn btn-primary">Verify</button>
                                                                        </div>
                                                                        <small id="promocodeStatus" class="text-muted"></small>
                                                                    </div>
                                                                </div> --}}
                                                                <div class="form-group row promo-field-crypto">
                                                                    <label class="col-lg-4 col-form-label">ENTER PROMOCODE:
                                                                        <small class="text-muted d-block">Please enter promocode</small>
                                                                    </label>
                                                                    <div class="col-lg-8">
                                                                        <div class="mb-3 input-group">
                                                                            <input name="promocode" id="promocode" type="text" class="form-control fill" placeholder="Promocode" aria-label="promocode">
                                                                            <button type="button" id="verifyPromocodeBtn" class="btn btn-primary">Verify</button>
                                                                        </div>
                                                                        <small id="promocodeStatus" class="text-muted"></small>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <label class="col-lg-4 col-form-label">ENTER AMOUNT:
                                                                        <small class="text-muted d-block">Please enter the amount to be deposited</small>
                                                                    </label>
                                                                    <div class="col-lg-8">
                                                                        <div class="mb-3 input-group">
                                                                            <span class="input-group-text">USD</span>
                                                                            <input name="user[deposit]" id="crypto_deposit_amount" min="10" type="number" class="form-control fill tradedeposit_amount" placeholder="Minimum $10" aria-label="Amount" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-4"></div>
                                                                    <div class="col-lg-8 pb-4">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input mt-1" type="checkbox" id="cryptoWarningCheckbox" name="confirmcryptoCheckbox" required>
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
                                                                                    <input type="button" id="paynow" data-amount="10" data-currency="USD" data-product="Deposit To: {{ $settings['mt5_company_name'] }}" class="btn btn-primary cryptochill-button col-12" value="Deposit To Trading Account" disabled>
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
                                                        <input type="hidden" name="email" value="{{ session('clogin') }}" min="10" required class="form-control fill">
                                                        <input class="user_code" type="hidden" name="user[code]" value="" class="form-control fill" readonly required>
                                                        <div class="row">
                                                            <div class="mt-2 col-12">
                                                                <input type="hidden" name="deposit_type" class="tradedeposittype" value="CreditCardPayissa">
                                                                {{-- <div class="form-group row">
                                                                    <label class="col-lg-4 col-form-label">ENTER PROMOCODE:
                                                                        <small class="text-muted d-block">Please enter promocode</small>
                                                                    </label>
                                                                    <div class="col-lg-8">
                                                                        <div class="mb-3 input-group">
                                                                            <input name="cc_promocode" id="cc_promocode" type="text" class="form-control fill" placeholder="Promocode" aria-label="promocode">
                                                                            <button type="button" id="verifyCcPromocodeBtn" class="btn btn-primary">Verify</button>
                                                                        </div>
                                                                        <small id="cc_promocodeStatus" class="text-muted"></small>
                                                                    </div>
                                                                </div> --}}
                                                                <div class="form-group row promo-field-cc">
                                                                    <label class="col-lg-4 col-form-label">ENTER PROMOCODE:
                                                                        <small class="text-muted d-block">Please enter promocode</small>
                                                                    </label>
                                                                    <div class="col-lg-8">
                                                                        <div class="mb-3 input-group">
                                                                            <input name="cc_promocode" id="cc_promocode" type="text" class="form-control fill" placeholder="Promocode" aria-label="promocode">
                                                                            <button type="button" id="verifyCcPromocodeBtn" class="btn btn-primary">Verify</button>
                                                                        </div>
                                                                        <small id="cc_promocodeStatus" class="text-muted"></small>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <label class="col-lg-4 col-form-label">ENTER AMOUNT:
                                                                        <small class="text-muted d-block">Please enter the amount to be deposited</small>
                                                                    </label>
                                                                    <div class="col-lg-8">
                                                                        <div class="mb-3 input-group">
                                                                            <span class="input-group-text">USD</span>
                                                                            <input placeholder="Minimum $10" name="deposit" id="deposit_amount_cc" type="number" min="10" title="Minimum $10" class="form-control fill ccdeposit_amount" aria-label="Amount" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-lg-4"></div>
                                                                    <div class="col-lg-8 pb-4">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input mt-1" type="checkbox" id="creditWarningCheckbox" name="confirmcryptoCheckbox">
                                                                            <label class="form-check-label" for="creditWarningCheckbox">
                                                                                Card deposit options vary by country. If your card is not accepted, try a different card & phone number. If the issue persists, this option may not be available in your country. In that case, please use cryptocurrency to deposit.
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="">
                                                                    <div class="row">
                                                                        <div class="col-lg-4"></div>
                                                                        <div class="col-lg-8">
                                                                            <div class="row g-1">
                                                                                <input type="submit" name="ccpay" id="ccpay" class="btn btn-primary col-12" value="Deposit To Trading Account" disabled>
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
                                                <div class="col-8 d-flex flex-column align-items-start justify-content-center">
                                                    <h3 class="text-white f-w-500">Fuel Your Trading Journey</h3>
                                                    <span class="py-2 text-white f-16">Deposit now and unlock the gateway to global markets.</span>
                                                </div>
                                                <div class="col-4 text-end">
                                                    <img src="{{ asset('assets/images/fund_now.png') }}" alt="img" class="img-fluid wid-110">
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
                                                                <img src="{{ asset('assets/images/mt5.png') }}" alt="user-image" class="wid-25 me-1 ms-1">
                                                            </span>
                                                            <div class="mx-2 media-body">
                                                                <h5 class="mb-1">
                                                                    <span class="pb-0 mb-0 h4 d-block f-w-500">{{ $liveaccount->code }}</span>
                                                                </h5>
                                                                <p class="mb-2 text-sm"><span class="text-muted">ACCOUNT CATEGORY:</span> ECN</p>
                                                                <div class="border-dashed border-top">
                                                                    <p class="mt-2 mb-1 d-grid">
                                                                        <span class="text-muted">LEVERAGE: {{ $liveaccount->leverage }}</span>
                                                                        <span class="text-muted">CREDIT: ${{ $liveaccount->credit }}</span>
                                                                        <span class="text-muted">EQUITY: ${{ $liveaccount->equity }}</span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <h4 class="f-w-500">${{ $liveaccount->balance??0 }}</h4>
                                                                <p class="mb-2 text-sm text-muted text-end">Balance</p>
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
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card support-tickets ribbon-box border ribbon-fill shadow-none pb-1">
                        <div class="row p-3">
                            <div class="card-body text-center">
                                <div class="text-center me-4"><a href="/transactions/deposit#"><img src="/assets/images/doc_upload.png" class="w-25" alt="img"></a></div>
                                <h6 class="text-center text-secondary mb-3 mt-2 f-w-400 mb-0 f-16">KYC Not Yet Verified !</h6>
                                <a id="verify-user-kyc" class="mt-3"><button class="btn btn-outline-primary"><span class="text-truncate">Verify Now To Proceed</span></button></a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @include('add_amount_to_account')
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // CryptoChill form handling
        const cryptoPromocodeInput = $('#promocode');
        const cryptoAmountInput = $('#crypto_deposit_amount');
        const cryptoDepositButton = $('#paynow');
        const cryptoPromocodeStatus = $('#promocodeStatus');
        const cryptoVerifyButton = $('#verifyPromocodeBtn');
        let isCryptoPromocodeValid = false;
        let isCryptoPromocodeEntered = false;

        // CreditCard form handling
        const ccPromocodeInput = $('#cc_promocode');
        const ccAmountInput = $('#deposit_amount_cc');
        const ccDepositButton = $('#ccpay');
        const ccPromocodeStatus = $('#cc_promocodeStatus');
        const ccVerifyButton = $('#verifyCcPromocodeBtn');
        let isCcPromocodeValid = false;
        let isCcPromocodeEntered = false;

        // RagaPay form handling
        const ragaPromocodeInput = $('#raga_promocode');
        const ragaAmountInput = $('#deposit_amount_raga');
        const ragaDepositButton = $('#ragapay-submit-btn');
        const ragaPromocodeStatus = $('#raga_promocodeStatus');
        const ragaVerifyButton = $('#verifyRagaPromocodeBtn');
        let isRagaPromocodeValid = false;
        let isRagaPromocodeEntered = false;

        // Function to update CryptoChill button state
        // Function to update CryptoChill button state
function updateCryptoButtonState() {
    const minDeposit = parseFloat($('#crypto_deposit_amount').attr('min')) || 10; // read dynamic min
    const amount = parseFloat(cryptoAmountInput.val());
    const isAmountValid = amount >= minDeposit;
    const isCheckboxChecked = $('#cryptoWarningCheckbox').is(':checked');

    if (isCryptoPromocodeEntered) {
        cryptoDepositButton.prop('disabled', !isCryptoPromocodeValid || !isAmountValid || !isCheckboxChecked);
        cryptoDepositButton.css('opacity', isCryptoPromocodeValid && isAmountValid && isCheckboxChecked ? '1' : '0.5');
        cryptoDepositButton.css('cursor', isCryptoPromocodeValid && isAmountValid && isCheckboxChecked ? 'pointer' : 'not-allowed');
    } else {
        cryptoDepositButton.prop('disabled', !isAmountValid || !isCheckboxChecked);
        cryptoDepositButton.css('opacity', isAmountValid && isCheckboxChecked ? '1' : '0.5');
        cryptoDepositButton.css('cursor', isAmountValid && isCheckboxChecked ? 'pointer' : 'not-allowed');
    }
}

// Function to update CreditCard button state
function updateCcButtonState() {
    const minDeposit = parseFloat($('#deposit_amount_cc').attr('min')) || 10; // read dynamic min
    const amount = parseFloat(ccAmountInput.val());
    const isAmountValid = amount >= minDeposit;
    const isCheckboxChecked = $('#creditWarningCheckbox').is(':checked');

    if (isCcPromocodeEntered) {
        ccDepositButton.prop('disabled', !isCcPromocodeValid || !isAmountValid || !isCheckboxChecked);
        ccDepositButton.css('opacity', isCcPromocodeValid && isAmountValid && isCheckboxChecked ? '1' : '0.5');
        ccDepositButton.css('cursor', isCcPromocodeValid && isAmountValid && isCheckboxChecked ? 'pointer' : 'not-allowed');
    } else {
        ccDepositButton.prop('disabled', !isAmountValid || !isCheckboxChecked);
        ccDepositButton.css('opacity', isAmountValid && isCheckboxChecked ? '1' : '0.5');
        ccDepositButton.css('cursor', isAmountValid && isCheckboxChecked ? 'pointer' : 'not-allowed');
    }
}

// Function to update RagaPay button state
function updateRagaButtonState() {
    const minDeposit = parseFloat($('#deposit_amount_raga').attr('min')) || 10; // read dynamic min
    const amount = parseFloat(ragaAmountInput.val());
    const isAmountValid = amount >= minDeposit;
    const isCheckboxChecked = $('#ragaWarningCheckbox').is(':checked');
    console.log('amount', amount);
    console.log('isCheckboxChecked', isCheckboxChecked);
    console.log('isAmountValid', isAmountValid);
    console.log('isRagaPromocodeValid', isRagaPromocodeValid);
    
    
    if (isRagaPromocodeEntered) {
        ragaDepositButton.prop('disabled', !isRagaPromocodeValid || !isAmountValid || !isCheckboxChecked);
        ragaDepositButton.css('opacity', isRagaPromocodeValid && isAmountValid && isCheckboxChecked ? '1' : '0.5');
        ragaDepositButton.css('cursor', isRagaPromocodeValid && isAmountValid && isCheckboxChecked ? 'pointer' : 'not-allowed');
    } else {
        ragaDepositButton.prop('disabled', !isAmountValid || !isCheckboxChecked);
        ragaDepositButton.css('opacity', isAmountValid && isCheckboxChecked ? '1' : '0.5');
        ragaDepositButton.css('cursor', isAmountValid && isCheckboxChecked ? 'pointer' : 'not-allowed');
    }
}


        // CryptoChill promocode verification
        cryptoVerifyButton.click(function() {
            const promocode = cryptoPromocodeInput.val().trim();
            isCryptoPromocodeEntered = promocode !== '';
            cryptoPromocodeStatus.text('Verifying...').removeClass('text-success text-danger');
            cryptoVerifyButton.prop('disabled', true).text('Verifying...');

            if (promocode === '') {
                cryptoPromocodeStatus.text('Please enter a promocode.').addClass('text-danger');
                isCryptoPromocodeValid = false;
                cryptoPromocodeInput.css('border-color', 'rgb(239, 68, 68)');
                updateCryptoButtonState();
                cryptoVerifyButton.prop('disabled', false).text('Verify');
                return;
            }

            $.ajax({
                url: '{{ route("verify.promocode") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    promocode: promocode
                },
                success: function(response) {
                    if (response.valid) {
                        cryptoPromocodeStatus.text(response.message || 'Promocode verified successfully!').addClass('text-success');
                        isCryptoPromocodeValid = true;
                        cryptoPromocodeInput.css('border-color', 'rgb(34, 197, 94)');
                    } else {
                        cryptoPromocodeStatus.text(response.message || 'Invalid promocode.').addClass('text-danger');
                        isCryptoPromocodeValid = false;
                        cryptoPromocodeInput.css('border-color', 'rgb(239, 68, 68)');
                    }
                    updateCryptoButtonState();
                },
                error: function(xhr) {
                    cryptoPromocodeStatus.text('Error verifying promocode. Please try again.').addClass('text-danger');
                    isCryptoPromocodeValid = false;
                    cryptoPromocodeInput.css('border-color', 'rgb(239, 68, 68)');
                    updateCryptoButtonState();
                },
                complete: function() {
                    cryptoVerifyButton.prop('disabled', false).text('Verify');
                }
            });
        });

        // CreditCard promocode verification
        ccVerifyButton.click(function() {
            const promocode = ccPromocodeInput.val().trim();
            isCcPromocodeEntered = promocode !== '';
            ccPromocodeStatus.text('Verifying...').removeClass('text-success text-danger');
            ccVerifyButton.prop('disabled', true).text('Verifying...');

            if (promocode === '') {
                ccPromocodeStatus.text('Please enter a promocode.').addClass('text-danger');
                isCcPromocodeValid = false;
                ccPromocodeInput.css('border-color', 'rgb(239, 68, 68)');
                updateCcButtonState();
                ccVerifyButton.prop('disabled', false).text('Verify');
                return;
            }

            $.ajax({
                url: '{{ route("verify.promocode") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    promocode: promocode
                },
                success: function(response) {
                    if (response.valid) {
                        ccPromocodeStatus.text(response.message || 'Promocode verified successfully!').addClass('text-success');
                        isCcPromocodeValid = true;
                        ccPromocodeInput.css('border-color', 'rgb(34, 197, 94)');
                    } else {
                        ccPromocodeStatus.text(response.message || 'Invalid promocode.').addClass('text-danger');
                        isCcPromocodeValid = false;
                        ccPromocodeInput.css('border-color', 'rgb(239, 68, 68)');
                    }
                    updateCcButtonState();
                },
                error: function(xhr) {
                    ccPromocodeStatus.text('Error verifying promocode. Please try again.').addClass('text-danger');
                    isCcPromocodeValid = false;
                    ccPromocodeInput.css('border-color', 'rgb(239, 68, 68)');
                    updateCcButtonState();
                },
                complete: function() {
                    ccVerifyButton.prop('disabled', false).text('Verify');
                }
            });
        });

        // RagaPay promocode verification
        ragaVerifyButton.click(function() {
            const promocode = ragaPromocodeInput.val().trim();
            isRagaPromocodeEntered = promocode !== '';
            ragaPromocodeStatus.text('Verifying...').removeClass('text-success text-danger');
            ragaVerifyButton.prop('disabled', true).text('Verifying...');

            if (promocode === '') {
                ragaPromocodeStatus.text('Please enter a promocode.').addClass('text-danger');
                isRagaPromocodeValid = false;
                ragaPromocodeInput.css('border-color', 'rgb(239, 68, 68)');
                updateRagaButtonState();
                ragaVerifyButton.prop('disabled', false).text('Verify');
                return;
            }

            $.ajax({
                url: '{{ route("verify.promocode") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    promocode: promocode
                },
                success: function(response) {
                    if (response.valid) {
                        ragaPromocodeStatus.text(response.message || 'Promocode verified successfully!').addClass('text-success');
                        isRagaPromocodeValid = true;
                        ragaPromocodeInput.css('border-color', 'rgb(34, 197, 94)');
                    } else {
                        ragaPromocodeStatus.text(response.message || 'Invalid promocode.').addClass('text-danger');
                        isRagaPromocodeValid = false;
                        ragaPromocodeInput.css('border-color', 'rgb(239, 68, 68)');
                    }
                    updateRagaButtonState();
                },
                error: function(xhr) {
                    ragaPromocodeStatus.text('Error verifying promocode. Please try again.').addClass('text-danger');
                    isRagaPromocodeValid = false;
                    ragaPromocodeInput.css('border-color', 'rgb(239, 68, 68)');
                    updateRagaButtonState();
                },
                complete: function() {
                    ragaVerifyButton.prop('disabled', false).text('Verify');
                }
            });
        });

        // Handle promocode input changes
        cryptoPromocodeInput.on('input', function() {
            const promocode = $(this).val().trim();
            isCryptoPromocodeEntered = promocode !== '';
            isCryptoPromocodeValid = false;
            cryptoPromocodeStatus.text(promocode ? 'Please verify this promocode before proceeding.' : '').removeClass('text-success').addClass(promocode ? 'text-danger' : '');
            cryptoPromocodeInput.css('border-color', promocode ? 'rgb(239, 68, 68)' : '');
            updateCryptoButtonState();
        });

        ccPromocodeInput.on('input', function() {
            const promocode = $(this).val().trim();
            isCcPromocodeEntered = promocode !== '';
            isCcPromocodeValid = false;
            ccPromocodeStatus.text(promocode ? 'Please verify this promocode before proceeding.' : '').removeClass('text-success').addClass(promocode ? 'text-danger' : '');
            ccPromocodeInput.css('border-color', promocode ? 'rgb(239, 68, 68)' : '');
            updateCcButtonState();
        });

        ragaPromocodeInput.on('input', function() {
            const promocode = $(this).val().trim();
            isRagaPromocodeEntered = promocode !== '';
            isRagaPromocodeValid = false;
            ragaPromocodeStatus.text(promocode ? 'Please verify this promocode before proceeding.' : '').removeClass('text-success').addClass(promocode ? 'text-danger' : '');
            ragaPromocodeInput.css('border-color', promocode ? 'rgb(239, 68, 68)' : '');
            updateRagaButtonState();
        });

        // Handle amount and checkbox input changes
        cryptoAmountInput.on('input', updateCryptoButtonState);
        ccAmountInput.on('input', updateCcButtonState);
        ragaAmountInput.on('input', updateRagaButtonState);
        $('#cryptoWarningCheckbox').on('change', updateCryptoButtonState);
        $('#creditWarningCheckbox').on('change', updateCcButtonState);
        $('#ragaWarningCheckbox').on('change', updateRagaButtonState);

        // Handle account selection
        $('.select-liveaccount').on('change', function () {
            const clientAccountId = $(this).val();
            const group = $(this).data('group');
            const minDeposit = (group === 'LM\\B-Book\\10x\\DF-B') ? 25 : 10;
            $('.user_code').val(clientAccountId);

            if (group === 'LM\\B-Book\\10x\\DF-B') {
                $('.promo-field-crypto').hide();
                $('.promo-field-cc').hide();
                $('.promo-field-ragapay').hide();
                $('#promocode').val('');
                $('#cc_promocode').val('');
                $('#raga_promocode').val('');
            } else {
                $('.promo-field-crypto').show();
                $('.promo-field-cc').show();
                $('.promo-field-ragapay').show();
            }

            $('#crypto_deposit_amount').attr('min', minDeposit).attr('placeholder', 'Minimum $' + minDeposit);
            $('#deposit_amount_cc').attr('min', minDeposit).attr('placeholder', 'Minimum $' + minDeposit);
            $('#deposit_amount_raga').attr('min', minDeposit).attr('placeholder', 'Minimum $' + minDeposit);

            updateCryptoButtonState();
            updateCcButtonState();
            updateRagaButtonState();
        });

        // Prevent form submission if no account selected
        $('#CreditCardPayissaForm').on('submit', function(e) {
            if (!$('.user_code').val()) {
                e.preventDefault();
                alert('Please select a trading account.');
                return false;
            }
            if (isCcPromocodeEntered && !isCcPromocodeValid) {
                e.preventDefault();
                ccPromocodeStatus.text('Please verify the promocode before proceeding.').addClass('text-danger');
                return false;
            }
            const amount = parseFloat(ccAmountInput.val());
            if (!amount || amount < 10) {
                e.preventDefault();
                alert('Please enter a valid amount (minimum $10).');
                return false;
            }
        });

        // Prevent RagaPay form submission if validations fail
        $('#RagaPayForm').on('submit', function(e) {
            if (!$('.user_code').val()) {
                e.preventDefault();
                alert('Please select a trading account.');
                return false;
            }
            if (isRagaPromocodeEntered && !isRagaPromocodeValid) {
                e.preventDefault();
                ragaPromocodeStatus.text('Please verify the promocode before proceeding.').addClass('text-danger');
                return false;
            }
            const amount = parseFloat(ragaAmountInput.val());
            if (!amount || amount < 10) {
                e.preventDefault();
                alert('Please enter a valid amount (minimum $10).');
                return false;
            }
        });

        // Prevent Crypto form submission if validations fail
        $('.CryptoChill form').on('submit', function(e) {
            if (!$('.user_code').val()) {
                e.preventDefault();
                alert('Please select a trading account.');
                return false;
            }
            if (isCryptoPromocodeEntered && !isCryptoPromocodeValid) {
                e.preventDefault();
                cryptoPromocodeStatus.text('Please verify the promocode before proceeding.').addClass('text-danger');
                return false;
            }
            const amount = parseFloat(cryptoAmountInput.val());
            if (!amount || amount < 10) {
                e.preventDefault();
                alert('Please enter a valid amount (minimum $10).');
                return false;
            }
        });

        // Anti-tampering protection
        setInterval(function() {
            if (!cryptoDepositButton.prop('disabled') && (isCryptoPromocodeEntered && !isCryptoPromocodeValid)) {
                cryptoDepositButton.prop('disabled', true).css({'opacity': '0.5', 'cursor': 'not-allowed'});
                console.warn('🔒 Security: Crypto submit button was artificially enabled and has been re-disabled.');
            }
            if (!ccDepositButton.prop('disabled') && (isCcPromocodeEntered && !isCcPromocodeValid)) {
                ccDepositButton.prop('disabled', true).css({'opacity': '0.5', 'cursor': 'not-allowed'});
                console.warn('🔒 Security: Credit Card submit button was artificially enabled and has been re-disabled.');
            }
            if (!ragaDepositButton.prop('disabled') && (isRagaPromocodeEntered && !isRagaPromocodeValid)) {
                ragaDepositButton.prop('disabled', true).css({'opacity': '0.5', 'cursor': 'not-allowed'});
                console.warn('🔒 Security: RagaPay submit button was artificially enabled and has been re-disabled.');
            }
        }, 1000);

        // Initial button state
        updateCryptoButtonState();
        updateCcButtonState();
        updateRagaButtonState();
    });
</script>
@endsection
