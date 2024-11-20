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
                                <li class="nav-item" role="presentation"><a class="nav-link" id="ecomtab-tab-1"
                                        href="/wallet_deposit" role="tab" aria-controls="ecomtab-1"
                                        aria-selected="true" tabindex="-1">
                                        <div class="media align-items-center">
                                            <div class="avtar avtar-s"><i class="feather icon-credit-card"></i>
                                            </div>
                                            <div class="media-body ms-2">
                                                <h6 class="mb-0">DEPOSIT</h6>
                                            </div>
                                        </div>
                                    </a></li>
                                <li class="nav-item" role="presentation"><a class="nav-link active"
                                        href="/wallet_withdrawal" aria-controls="ecomtab-2" aria-selected="false"
                                        tabindex="-1">
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
                                                <div class="card">
                                                    <div class="card-body border-bottom">
                                                        <h6>Withdraw Funds</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="divider my-4"><span>SELECT WITHDRAW METHOD</span></div>
                                                    </div>
                                                    <div class="row g-1">
                                                        <div class="col-md-3 col-lg-4 col-xl-4">
                                                            <div class="address-check border rounded">
                                                                <div class="form-check"><input type="radio"
                                                                        name="withdraw_type"
                                                                        class="form-check-input input-primary wallet-withdraw"
                                                                        value="1" data-type="Wallet_Withdrawal"><label
                                                                        class="form-check-label d-block"
                                                                        for="payopn-check-1"><span
                                                                            class="card-body p-2 d-block"><span
                                                                                class="h6 f-w-500 mb-1 d-block">CRYPTO
                                                                                WITHDRAWAL</span><span
                                                                                class="d-flex align-items-center"><span
                                                                                    class="f-10 badge bg-light-success me-1">CRYPTO
                                                                                    WALLET</span>
                                                                                <span class="ti ti-currency-bitcoin"></span>
                                                                            </span></span></label></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="divider my-4"><span>WITHDRAW DETAILS</span></div>
                                                    <div class="wallet-withdrawal Wallet_Withdrawal">
                                                        <form method="post" style="padding:10px;" class="md-float-material form-material">
                                                            @csrf
                                                            <div class="row">
                                                                <input type="hidden" name="withdraw_type" class="withdraw-type" value="Wallet_Withdrawal">
                                                                <div class="col-12 mt-2">
                                                                    <div class="form-group row">
                                                                        <label class="col-lg-4 col-form-label">
                                                                            SELECT WALLET ACCOUNT:
                                                                            <small class="text-muted d-block">
                                                                                Please select the Wallet account to which you wish to transfer your funds
                                                                            </small>
                                                                        </label>
                                                                        <div class="col-lg-8">
                                                                            @if (count($client_banks) == 0)
                                                                                <div class="form-group">
                                                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBankModal2">
                                                                                        <i class="ti ti-plus f-18"></i> Add Wallet Information
                                                                                    </button>
                                                                                </div>
                                                                            @else
                                                                                <select name="client_bank" required class="form-control fill" style="color:black;">
                                                                                    @foreach ($client_banks as $bank)
                                                                                        <option value="{{ $bank->client_wallet_id }}">
                                                                                            {{ $bank->wallet_name }} / {{ $bank->wallet_currency }} / {{ $bank->wallet_network }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                                <small data-bs-toggle="modal" data-bs-target="#addBankModal2" style="color: var(--primary-color); cursor: pointer;">
                                                                                    + Add another wallet
                                                                                </small>
                                                                            @endif
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group row">
                                                                        <label class="col-lg-4 col-form-label">
                                                                            Your Wallet Balance:
                                                                            <small class="text-muted d-block">(USD)</small>
                                                                        </label>
                                                                        <div class="col-lg-8">
                                                                            <div class="input-group mb-3">
                                                                                <input type="number" name="wallet_balance" value="{{ $wallet_balance }}" readonly class="form-control fill">
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group row">
                                                                        <label class="col-lg-4 col-form-label">
                                                                            ENTER AMOUNT:
                                                                            <small class="text-muted d-block">Please enter the amount that you need to withdraw</small>
                                                                        </label>
                                                                        <div class="col-lg-8">
                                                                            <div class="input-group mb-3">
                                                                                <span class="input-group-text">$</span>
                                                                                <input type="number" min="0" class="form-control" name="withdraw_amount" aria-label="Amount (to the nearest dollar)"
                                                                                    @if(count($client_banks) > 0) required @endif>
                                                                                <span class="input-group-text">.00</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-lg-4"></div>
                                                                <div class="col-lg-8">
                                                                    <div class="row g-1">
                                                                        <input type="submit" name="wallet_withdraw" class="btn btn-primary col-12" value="Withdraw From Wallet">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>

                                                    <div class="wallet-withdrawal USDT_Withdrawal" style="display:none">
                                                        <form method="post" style="padding:10px;" class="md-float-material form-material" enctype="multipart/form-data">
                                                            @csrf
                                                            <div class="row">
                                                                <input type="hidden" name="withdraw_type" class="withdraw-type" value="USDT_Withdrawal">
                                                                <div class="col-12 mt-2">
                                                                    <div class="form-group row">
                                                                        <label class="col-lg-4 col-form-label">
                                                                            ENTER AMOUNT:
                                                                            <small class="text-muted d-block">Please enter the amount that you need to transfer</small>
                                                                        </label>
                                                                        <div class="col-lg-8">
                                                                            <div class="input-group mb-3">
                                                                                <span class="input-group-text">$</span>
                                                                                <input type="number" class="form-control" name="withdraw_amount" aria-label="Amount (to the nearest dollar)"
                                                                                    required min="1">
                                                                                <span class="input-group-text">.00</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group row">
                                                                        <label class="col-lg-4 col-form-label">
                                                                            ENTER WALLET ID:
                                                                            <small class="text-muted d-block">Please enter your Wallet ID</small>
                                                                        </label>
                                                                        <div class="col-lg-8">
                                                                            <div class="input-group mb-3">
                                                                                <input type="text" class="form-control" name="wallet_id" aria-label="Enter your Wallet ID" required>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group row">
                                                                        <label class="col-lg-4 col-form-label">
                                                                            UPLOAD USDT WALLET QR CODE:
                                                                            <small class="text-muted d-block">Upload your Wallet QR CODE</small>
                                                                        </label>
                                                                        <div class="col-lg-8">
                                                                            <input type="file" accept="application/pdf,image/png,image/jpeg,image/jpg" class="form-control"
                                                                                required name="wallet_qr">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-lg-4"></div>
                                                                <div class="col-lg-8">
                                                                    <div class="row g-1">
                                                                        <input type="submit" name="usdt_withdraw" class="btn btn-primary col-12" value="Withdraw From Wallet">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>

                                                    <div class="wallet-withdrawal Other_Withdrawal" style="display:none">
                                                        <form method="post" style="padding:10px;" class="md-float-material form-material">
                                                            @csrf
                                                            <div class="row">
                                                                <input type="hidden" name="withdraw_type" class="withdraw-type" value="Other_Withdrawal">
                                                                <div class="col-12 mt-2">
                                                                    <div class="form-group row">
                                                                        <label class="col-lg-4 col-form-label">
                                                                            ENTER AMOUNT:
                                                                            <small class="text-muted d-block">
                                                                                Please enter the amount that you need to transfer
                                                                            </small>
                                                                        </label>
                                                                        <div class="col-lg-8">
                                                                            <div class="input-group mb-3">
                                                                                <span class="input-group-text">$</span>
                                                                                <input type="number" class="form-control" aria-label="Amount (to the nearest dollar)"
                                                                                    required name="withdraw_amount">
                                                                                <span class="input-group-text">.00</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group row">
                                                                        <label class="col-lg-4 col-form-label">
                                                                            CLIENT NOTE:
                                                                            <small class="text-muted d-block"></small>
                                                                        </label>
                                                                        <div class="col-lg-8">
                                                                            <div class="input-group mb-3">
                                                                                <input type="text" class="form-control" aria-label="Client Note" required
                                                                                    name="client_note">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-lg-4"></div>
                                                                <div class="col-lg-8">
                                                                    <div class="row g-1">
                                                                        <input type="submit" name="other_withdraw" class="btn btn-primary col-12" value="Withdraw From Wallet">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4">
                                                <div class="card coupon-card bg-primary">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-8 d-flex flex-column align-items-start justify-content-center">
                                                                <h3 class="text-white f-w-500">Fuel Your Trading Journey</h3>
                                                                <span class="f-16 py-2 text-white">Deposit now and unlock the gateway to global markets.</span>
                                                            </div>
                                                            <div class="col-4 text-end">
                                                                <img src="/assets/images/fund_now.png" alt="img" class="img-fluid wid-110">
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
                                                                            <img src="/assets/images/mt5.png" alt="user-image" class="wid-25 me-1 ms-1">
                                                                        </span>
                                                                        <div class="media-body mx-2">
                                                                            <h5 class="mb-1">
                                                                                <span class="h4 mb-0 d-block f-w-500 pb-0">{{ $liveaccount->trade_id }}</span>
                                                                            </h5>
                                                                            <p class="text-sm mb-2">
                                                                                <span class="text-muted">ACCOUNT CATEGORY :</span> ECN
                                                                            </p>
                                                                            <div class="border-top border-dashed">
                                                                                <p class="mb-1 mt-2">
                                                                                    <span class="text-muted">LEVERAGE :</span> {{ $liveaccount->leverage }}
                                                                                    <span class="text-muted">| CREDIT :</span> $0.0000
                                                                                    <span class="text-muted">| EQUITY : ${{ $liveaccount->equity }}</span>
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="flex-shrink-0">
                                                                            <h4 class="f-w-500">${{ $liveaccount->Balance }}</h4>
                                                                            <p class="text-muted text-sm mb-2 text-end">Balance</p>
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
                                                    <div class="card-body py-2">
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item px-0">
                                                                <div class="float-end">
                                                                    <h3 class="mb-0 fw-medium">${{ $totals->balance }}</h3>
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
                    <!---->
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
