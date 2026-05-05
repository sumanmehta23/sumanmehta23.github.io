@extends('layouts.crm.crm')
@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="pb-0 mb-0 page-header">
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
                        <div class="p-0 card-body">
                            <ul class="mb-0 nav nav-tabs checkout-tabs" id="myTab" role="tablist">
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
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-xl-8">
                                                @if ($kyc_user->kyc_verify == 0)
                                                    <div
                                                        class="pb-1 border shadow-none card support-tickets ribbon-box ribbon-fill">
                                                        <div class="p-3 row">
                                                            <div class="text-center card-body">
                                                                <div class="text-center me-4">
                                                                    <a href="/transactions/deposit#">
                                                                        <img src="/assets/images/doc_upload.png"
                                                                            class="w-25" alt="img">
                                                                    </a>
                                                                </div>
                                                                <h6
                                                                    class="mt-2 mb-0 mb-3 text-center text-secondary f-w-400 f-16">
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
                                                    {{-- <div class="card">

                                                    </div> --}}
                                                    <div class="card">
                                                        <div class="card-body border-bottom">
                                                            <h6>Deposit Funds</h6>
                                                        </div>

                                                        <div class="card-body">

                                                            <div class="my-4 divider">
                                                                <span>SELECT PAYMENT METHOD</span>
                                                            </div>
                                                            <div class="row g-1">
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
                                                                 <div class="col-6 col-lg-6 col-xl-6">
                                                                    <div
                                                                        class="border rounded address-check trade-deposit-type">
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
                                                            </div>
                                                            <div class="my-4 divider"><span>DEPOSIT DETAILS</span></div>
                                                            <form method="post" style="padding:10px;"
                                                                class="md-float-material form-material d-none">
                                                                @csrf
                                                                <div
                                                                    class="row Bank-Deposit Other-Payments wallet-deposit-details">
                                                                    <div class="mt-2 col-12">
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
                                                                                <div class="mb-3 input-group">
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
                                                                                <div class="mb-3 input-group">
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
                                                                    <div class="mt-2 col-12">
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
                                                                                <div class="mb-3 input-group">
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
                                                                                <div class="mb-3 input-group">
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
                                                                                            Please ensure you send the correct cryptocurrency to the correct wallet address and network. Transactions are irreversible, and we are not responsible for any loss of funds due to incorrect deposits. Double-check all details before proceeding.
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

                                                            <div class="CreditCardPayissa trade-deposit-details"
                                                                style="display:none">
                                                                <form method="post">
                                                                    @csrf
                                                                    <input type="hidden" name="email"
                                                                        value="{{ session('clogin') }}" min="10"
                                                                        required class="form-control fill">
                                                                    <input class="user_code" type="hidden"
                                                                        name="user[code]" value=""
                                                                        class="form-control fill" readonly required>
                                                                    <div class="row">
                                                                        <div class="mt-2 col-12">
                                                                            <input type="hidden" name="deposit_type"
                                                                                class="tradedeposittype"
                                                                                value="CreditCardPayissa">

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

                                                        </div>
                                                    </div>
                                                @endif
                                            </div>


                                            <div class="col-xl-4">
                                                <div class="card coupon-card bg-primary">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div
                                                                class="col-8 d-flex flex-column align-items-start justify-content-center">
                                                                <h3 class="text-white f-w-500">Fuel Your Trading Journey
                                                                </h3>
                                                                <span class="py-2 text-white f-16">
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
                                                    <div class="p-0 card-body">
                                                        <ul class="list-group list-group-flush">
                                                            @foreach ($liveaccount_details as $liveaccount)
                                                                <li class="list-group-item">
                                                                    <div class="media align-items-start">
                                                                        <span class="pb-0 mb-0 h4 d-block f-w-500">
                                                                            <img src="/assets/images/mt5.png"
                                                                                alt="user-image" class="wid-25 me-1 ms-1">
                                                                        </span>
                                                                        <div class="mx-2 media-body">
                                                                            <h5 class="mb-1">
                                                                                <span
                                                                                    class="pb-0 mb-0 h4 d-block f-w-500">{{ $liveaccount->code }}</span>
                                                                            </h5>
                                                                            <p class="mb-2 text-sm">
                                                                                <span class="text-muted">ACCOUNT CATEGORY
                                                                                    :</span> ECN
                                                                            </p>
                                                                            <div class="border-dashed border-top">
                                                                                <p class="mt-2 mb-1">
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
                                                                                ${{ $liveaccount->balance }}</h4>
                                                                            <p class="mb-2 text-sm text-muted text-end">
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
                                                                    <h3 class="mb-0 fw-medium">${{ $totals->balance }}
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
@endsection
