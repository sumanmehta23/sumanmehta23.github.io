@extends('layouts.crm.crm')
@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header mb-0 pb-0">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title h2">
                                <h4 class="mb-0">Wallet</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body p-0">
                            <ul class="nav nav-tabs checkout-tabs mb-0" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation"><a class="nav-link active" id="ecomtab-tab-1"
                                        href="/wallet_deposit" role="tab" aria-controls="ecomtab-1" aria-selected="true"
                                        tabindex="-1">
                                        <div class="media align-items-center">
                                            <div class="avtar avtar-s"><i class="feather icon-credit-card"></i>
                                            </div>
                                            <div class="media-body ms-2">
                                                <h6 class="mb-0">DEPOSIT</h6>
                                            </div>
                                        </div>
                                    </a></li>
                                <li class="nav-item" role="presentation"><a class="nav-link" href="/wallet_withdrawal"
                                        aria-controls="ecomtab-2" aria-selected="false" tabindex="-1">
                                        <div class="media align-items-center">
                                            <div class="avtar avtar-s"><i class="feather icon-dollar-sign"></i>
                                            </div>
                                            <div class="media-body ms-2">
                                                <h6 class="mb-0">WITHDRAW</h6>
                                            </div>
                                        </div>
                                    </a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="tab-content">
                        <div>
                            <div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-xl-8">
                                                @if ($kyc_user->kyc_verify == 0)
                                                    <div
                                                        class="card support-tickets ribbon-box border ribbon-fill shadow-none pb-1">
                                                        <div class="row p-3">
                                                            <div class="card-body text-center">
                                                                <div class="text-center me-4">
                                                                    <a href="/transactions/deposit#">
                                                                        <img src="/assets/images/doc_upload.png"
                                                                            class="w-25" alt="img">
                                                                    </a>
                                                                </div>
                                                                <h6
                                                                    class="text-center text-secondary mb-3 mt-2 f-w-400 mb-0 f-16">
                                                                    KYC Not Yet Verified!
                                                                </h6>
                                                                <a id="verify-user-kyc" class="mt-3">
                                                                    <button class="btn btn-outline-primary">
                                                                        <span class="text-truncate">Verify Now To
                                                                            Proceed</span>
                                                                    </button>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="card">
                                                        <div class="card-body border-bottom">
                                                            <h6>Deposit Funds</h6>
                                                        </div>
                                                        <div class="card-body">

                                                            <div class="divider my-4">
                                                                <span>SELECT PAYMENT METHOD</span>
                                                            </div>
                                                            <div class="row g-1">
                                                                <div class="col-6 col-lg-6 col-xl-6">
                                                                    <div
                                                                        class="address-check trade-deposit-type border rounded">
                                                                        <div class="form-check">
                                                                            <input type="radio" name="deposit_type"
                                                                                checked
                                                                                class="form-check-input input-primary tradefund-deposit"
                                                                                id="cryptochill" value="CryptoChill"
                                                                                data-type="CryptoChill">
                                                                            <label class="form-check-label d-block"
                                                                                for="cryptochill">
                                                                                <span class="card-body p-2 d-block">
                                                                                    <span
                                                                                        class="d-flex align-items-center justify-content-between">
                                                                                        <span>Crypto Payment</span>
                                                                                        <span>
                                                                                            <span
                                                                                                class="h6 f-w-500 mb-1 d-block">
                                                                                                <img src="/assets/images/cryptochill.svg"
                                                                                                    alt="CryptoChill">
                                                                                            </span>
                                                                                        </span>
                                                                                    </span>
                                                                                </span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                {{-- <div class="col-6 col-lg-6 col-xl-6">
                                                                    <div
                                                                        class="address-check trade-deposit-type border rounded">
                                                                        <div class="form-check">
                                                                            <input type="radio" name="deposit_type"
                                                                                checked
                                                                                class="form-check-input input-primary tradefund-deposit"
                                                                                id="option_nowpayment" value="Now Payment"
                                                                                data-type="Now-Payment">
                                                                            <label class="form-check-label d-block"
                                                                                for="option_nowpayment">
                                                                                <span class="card-body p-2 d-block">
                                                                                    <span
                                                                                        class="d-flex align-items-center justify-content-between">
                                                                                        <span>Crypto Payment</span>
                                                                                        <span>
                                                                                            <span
                                                                                                class="h6 f-w-500 mb-1 d-block">
                                                                                                <img src="/assets/images/nowpayments-white.png"
                                                                                                    alt="Now Payment"
                                                                                                    style="height: 40px;">
                                                                                            </span>
                                                                                        </span>
                                                                                    </span>
                                                                                </span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div> --}}
                                                            </div>
                                                            <div class="divider my-4"><span>DEPOSIT DETAILS</span></div>
                                                            <form method="post" style="padding:10px;"
                                                                class="md-float-material form-material d-none">
                                                                @csrf
                                                                <div
                                                                    class="row Bank-Deposit Other-Payments wallet-deposit-details">
                                                                    <div class="col-12 mt-2">
                                                                        <div class="form-group row">
                                                                            <label class="col-lg-4 col-form-label">
                                                                                DEPOSIT CURRENCY:
                                                                                <small class="text-muted d-block">
                                                                                    Please select the currency you wish to
                                                                                    use for the payment
                                                                                </small>
                                                                            </label>
                                                                            <input type="hidden" name="currency"
                                                                                value="USD">
                                                                            <input class="deposit_type" type="hidden"
                                                                                name="deposit_type" value="Bank-Deposit">
                                                                            <div class="col-lg-8">
                                                                                <select class="form-select"
                                                                                    id="currencyType" disabled
                                                                                    name="currencyType">
                                                                                    <option value="USD" selected>USD
                                                                                    </option>
                                                                                    <option value="IDR">IDR</option>
                                                                                    <option value="MYR">MYR</option>
                                                                                    <option value="THB">THB</option>
                                                                                    <option value="VND">VND</option>
                                                                                    <option value="PKR">PKR</option>
                                                                                    <option value="INR">INR</option>
                                                                                    <option value="USDT ERC20">USDT ERC20
                                                                                    </option>
                                                                                    <option value="USDT TRC20">USDT TRC20
                                                                                    </option>
                                                                                    <option value="BTC">BTC</option>
                                                                                    <option value="LTC">LTC</option>
                                                                                    <option value="DASH">DASH</option>
                                                                                    <option value="DOGE">DOGE</option>
                                                                                    <option value="ETH">ETH</option>
                                                                                    <option value="BUSD">BUSD</option>
                                                                                    <option value="TRX">TRX</option>
                                                                                    <option value="BINANCEPAY">BINANCEPAY
                                                                                    </option>
                                                                                    <option value="EUR">EUR</option>
                                                                                    <option value="CRYPTO">CRYPTO</option>
                                                                                    <option value="GBP">GBP</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label class="col-lg-4 col-form-label">
                                                                                ENTER AMOUNT:
                                                                                <small class="text-muted d-block">
                                                                                    Please enter the amount to be deposited
                                                                                    in selected currency
                                                                                </small>
                                                                            </label>
                                                                            <div class="col-lg-8">
                                                                                <div class="input-group mb-3">
                                                                                    <span
                                                                                        class="input-group-text currency-type">USD</span>
                                                                                    <input type="number"
                                                                                        class="form-control wallet-amount"
                                                                                        aria-label="Amount"
                                                                                        name="wallet_amount" required>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label class="col-lg-4 col-form-label">
                                                                                AMOUNT IN USD:
                                                                                <small class="text-muted d-block">
                                                                                    Deposit amount in USD
                                                                                </small>
                                                                            </label>
                                                                            <div class="col-lg-8">
                                                                                <div class="input-group mb-3">
                                                                                    <span
                                                                                        class="input-group-text">USD</span>
                                                                                    <input type="text"
                                                                                        class="form-control wallet-amount-usd"
                                                                                        aria-label="Amount" disabled>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="row">
                                                                                <div class="col-lg-4"></div>
                                                                                <div class="col-lg-8">
                                                                                    <div class="row g-1">
                                                                                        <input type="submit"
                                                                                            name="add_wallet"
                                                                                            class="btn btn-primary col-12"
                                                                                            value="PROCESS PAYMENT">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                            <form method="post" style="padding:10px;"
                                                                class="md-float-material form-material">
                                                                @csrf
                                                                <div class="row USDT-Deposit wallet-deposit-details"
                                                                    style="display:none">
                                                                    <div class="col-12 mt-2">
                                                                        <div class="form-group row">
                                                                            <label class="col-lg-4 col-form-label">
                                                                                DEPOSIT CURRENCY:
                                                                                <small class="text-muted d-block">
                                                                                    Please select the currency you wish to
                                                                                    use for the payment
                                                                                </small>
                                                                            </label>
                                                                            <input type="hidden" name="currency"
                                                                                value="USDT TRC20">
                                                                            <input class="deposit_type" type="hidden"
                                                                                name="deposit_type" value="">
                                                                            <div class="col-lg-8">
                                                                                <select class="form-select"
                                                                                    id="exampleFormControlSelect1" disabled
                                                                                    name="currencyType">
                                                                                    <option value="USDT TRC20">USDT TRC20
                                                                                    </option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label class="col-lg-4 col-form-label">
                                                                                ENTER AMOUNT:
                                                                                <small class="text-muted d-block">
                                                                                    Please enter the amount to be deposited
                                                                                    in selected currency
                                                                                </small>
                                                                            </label>
                                                                            <div class="col-lg-8">
                                                                                <div class="input-group mb-3">
                                                                                    <span class="input-group-text">USDT
                                                                                        TRC20</span>
                                                                                    <input type="number"
                                                                                        class="form-control wallet-amount"
                                                                                        aria-label="Amount"
                                                                                        name="wallet_amount" required>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group row">
                                                                            <label class="col-lg-4 col-form-label">
                                                                                AMOUNT IN USD:
                                                                                <small class="text-muted d-block">
                                                                                    Deposit amount in USD
                                                                                </small>
                                                                            </label>
                                                                            <div class="col-lg-8">
                                                                                <div class="input-group mb-3">
                                                                                    <span
                                                                                        class="input-group-text">USD</span>
                                                                                    <input type="text"
                                                                                        class="form-control wallet-amount-usd"
                                                                                        aria-label="Amount" disabled>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="">
                                                                            <div class="row">
                                                                                <div class="col-lg-4"></div>
                                                                                <div class="col-lg-8">
                                                                                    <div class="row g-1">
                                                                                        <input type="submit"
                                                                                            name="add_wallet"
                                                                                            class="btn btn-primary col-12"
                                                                                            value="PROCESS PAYMENT">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>






                                                            <div class="CryptoChill trade-deposit-details">
                                                                <form method="post">
                                                                    @csrf
                                                                    <input type="hidden" name="user[email]"
                                                                        value="{{ session('clogin') }}" min="10"
                                                                        required class="form-control fill">
                                                                    <input class="user_trade_id form-control fill" type="hidden"
                                                                        name="trade_id" value="">
                                                                    <div class="row">
                                                                        <div class="col-12 mt-2">
                                                                            <input type="hidden"
                                                                                name="user[deposit_type]"
                                                                                class="tradedeposittype"
                                                                                value="CryptoChill">
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
                                                                                    <div class="input-group mb-3">
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
                                                                            <div class="">
                                                                                <div class="row">
                                                                                    <div class="col-lg-4"></div>
                                                                                    <div class="col-lg-8">
                                                                                        <div class="row g-1">
                                                                                            <input type="submit"
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
                                                                </form>
                                                            </div>

                                                            <div class="Now-Payment trade-deposit-details"
                                                                style="display:none">
                                                                <form method="post">
                                                                    @csrf
                                                                    <input type="hidden" name="email"
                                                                        value="{{ session('clogin') }}" min="10"
                                                                        required class="form-control fill">
                                                                    <input class="user_trade_id" type="hidden"
                                                                        name="user[trade_id]" value=""
                                                                        class="form-control fill" readonly required>
                                                                    <div class="row">
                                                                        <div class="col-12 mt-2">
                                                                            <input type="hidden" name="deposit_type"
                                                                                class="tradedeposittype"
                                                                                value="Now Payment">
                                                                            <div class="form-group row">
                                                                                <label
                                                                                    class="col-lg-4 col-form-label">DEPOSIT
                                                                                    CURRENCY:
                                                                                    <small
                                                                                        class="text-muted d-block">Please
                                                                                        select the currency you wish to use
                                                                                        for the payment</small>
                                                                                </label>
                                                                                <div class="col-lg-8">
                                                                                    <select class="form-select"
                                                                                        id="exampleFormControlSelect1"
                                                                                        required>
                                                                                        <option value="USD">USD</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group row">
                                                                                <label
                                                                                    class="col-lg-4 col-form-label">ENTER
                                                                                    AMOUNT:
                                                                                    <small
                                                                                        class="text-muted d-block">Please
                                                                                        enter the amount to be deposited in
                                                                                        selected currency</small>
                                                                                </label>
                                                                                <div class="col-lg-8">
                                                                                    <div class="input-group mb-3">
                                                                                        <span
                                                                                            class="input-group-text">USD</span>
                                                                                        <input placeholder="Minimum $10"
                                                                                            name="deposit"
                                                                                            id="deposit_amount_now"
                                                                                            type="number" min="10"
                                                                                            title="Minimum $10"
                                                                                            class="form-control fill nowdeposit_amount"
                                                                                            aria-label="Amount" required>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="">
                                                                                <div class="row">
                                                                                    <div class="col-lg-4"></div>
                                                                                    <div class="col-lg-8">
                                                                                        <div class="row g-1">
                                                                                            <input type="submit"
                                                                                                name="register"
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

                                                        </div>
                                                    </div>
                                                @endif;
                                            </div>


                                            <div class="col-xl-4">
                                                <div class="card coupon-card bg-primary">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div
                                                                class="col-8 d-flex flex-column align-items-start justify-content-center">
                                                                <h3 class="text-white f-w-500">Fuel Your Trading Journey
                                                                </h3>
                                                                <span class="f-16 py-2 text-white">
                                                                    Deposit now and unlock the gateway to global markets.
                                                                </span>
                                                            </div>
                                                            <div class="col-4 text-end">
                                                                <img src="/assets/images/fund_now.png" alt="img"
                                                                    class="img-fluid wid-110">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- <div class="card">
                                                    <div class="card-header">
                                                        <h5>MT5 ACCOUNTS SUMMARY</h5>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <ul class="list-group list-group-flush">
                                                            @foreach ($liveaccount_details as $liveaccount)
                                                                <li class="list-group-item">
                                                                    <div class="media align-items-start">
                                                                        <span class="h4 mb-0 d-block f-w-500 pb-0">
                                                                            <img src="/assets/images/mt5.png"
                                                                                alt="user-image" class="wid-25 me-1 ms-1">
                                                                        </span>
                                                                        <div class="media-body mx-2">
                                                                            <h5 class="mb-1">
                                                                                <span
                                                                                    class="h4 mb-0 d-block f-w-500 pb-0">{{ $liveaccount->trade_id }}</span>
                                                                            </h5>
                                                                            <p class="text-sm mb-2">
                                                                                <span class="text-muted">ACCOUNT CATEGORY
                                                                                    :</span> ECN
                                                                            </p>
                                                                            <div class="border-top border-dashed">
                                                                                <p class="mb-1 mt-2">
                                                                                    <span class="text-muted">LEVERAGE
                                                                                        :</span>
                                                                                    {{ $liveaccount->leverage }}
                                                                                    <span class="text-muted">| CREDIT
                                                                                        :</span> $0.0000
                                                                                    <span class="text-muted">| EQUITY
                                                                                        :</span> ${{ $liveaccount->equity }}
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="flex-shrink-0">
                                                                            <h4 class="f-w-500">
                                                                                ${{ $liveaccount->Balance }}</h4>
                                                                            <p class="text-muted text-sm mb-2 text-end">
                                                                                Balance</p>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                            <li class="list-group-item">
                                                                <div class="float-end">
                                                                    <h4 class="mb-0 fw-medium">$0.0000</h4>
                                                                </div>
                                                                <span class="text-muted">TOTAL CREDIT</span>
                                                            </li>
                                                            <li class="list-group-item">
                                                                <div class="float-end">
                                                                    <h4 class="mb-0 fw-medium">$ {{ $totals->equity }}</h4>
                                                                </div>
                                                                <span class="text-muted">TOTAL EQUITY</span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                <div class="card">
                                                    <div class="card-body py-2">
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item px-0">
                                                                <div class="float-end">
                                                                    <h3 class="mb-0 fw-medium">$ {{ $totals->balance }}
                                                                    </h3>
                                                                </div>
                                                                <h5 class="mb-0 d-inline-block">TOTAL BALANCE</h5>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div> --}}
                                                <div class="card">
                                                    <div class="py-2 card-body">
                                                      <ul class="list-group list-group-flush">
                                                        <li class="px-0 list-group-item">
                                                          <div class="float-end">
                                                            <h3 class="mb-0 fw-medium">$ <?= $wallet_balance ?? 0 ?></h3>
                                                          </div>
                                                          <h5 class="mb-0 uppercase d-inline-block">CURRENT BALANCE</h5>
                                                        </li>
                                                      </ul>
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
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                showConfirmButton: true
            });
        </script>
    @endif
    @include('pgi_cryptoChill')
@endsection
