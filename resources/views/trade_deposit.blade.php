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
                <div class="col-sm-12">
                    <div class="card">
                        <div class="p-0 card-body">
                            @include('sub_header')
                        </div>
                    </div>
                    <div class="tab-content">
                        <div>
                            {{-- <?php if (isset($error)) {?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <strong>
                                    <?php echo $error; ?>
                                </strong>
                            </div>
                            <script>
                                $(".alert").alert();
                            </script>
                            <?php } ?> --}}
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
                                                                        <input id="liveaccount{{ $liveaccount->code }}"
                                                                            type="radio" name="live-account"
                                                                            class="select-liveaccount form-check-input input-primary"
                                                                            data-mindep="{{ $liveaccount->mindep }}"
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
                                                        @if ($walletenabled)
                                                            <div class="col-6 col-lg-6 col-xl-6">
                                                                <div
                                                                    class="border rounded address-check trade-deposit-type">
                                                                    <div class="form-check">
                                                                        <input type="radio" name="deposit_type"
                                                                            class="form-check-input input-primary tradefund-deposit"
                                                                            id="walletpayment" value="Wallet Transfer"
                                                                            data-type="Wallet-Transfer">
                                                                        <label class="form-check-label d-block"
                                                                            for="walletpayment">
                                                                            <span class="p-2 card-body d-block">
                                                                                <span
                                                                                    class="d-flex justify-content-between">
                                                                                    <span>
                                                                                        <span
                                                                                            class="mb-1 h6 f-w-500 d-block">Wallets</span>
                                                                                        <span class="f-10 text-muted">Wallet
                                                                                            Transfer</span>
                                                                                    </span>
                                                                                    <span class=" d-flex align-items-end">
                                                                                        <span>
                                                                                            <span
                                                                                                class="mb-1 text-right h6 f-w-500 d-block"
                                                                                                style="text-align:end">$<?php echo $wallet_balance; ?></span>
                                                                                            <span
                                                                                                class="f-10 text-muted">Current
                                                                                                Balance</span>
                                                                                        </span>
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
                                                    <div class="Wallet-Transfer trade-deposit-details">
                                                        <form method="post" id="tradeDepositForm">
                                                            @csrf
                                                            <input type="hidden" name="user[email]"
                                                                value="{{ session('user')->email }}" required
                                                                class="form-control fill">
                                                            <input class="user_code" type="hidden"
                                                                name="user[account_id]" value="" readonly required>

                                                            <div class="row">
                                                                <div class="mt-2 col-12">
                                                                    <input type="hidden" name="user[deposit_type]"
                                                                        class="tradedeposittype" value="BANK DEPOSIT">

                                                                    <div class="form-group row">
                                                                        <label class="col-lg-4 col-form-label">DEPOSIT
                                                                            CURRENCY:
                                                                            <small class="text-muted d-block"> Please
                                                                                select the currency you wish to use for the
                                                                                payment </small>
                                                                        </label>
                                                                        <div class="col-lg-8">
                                                                            <select class="form-select" required>
                                                                                <option value="USD">USD</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group row">
                                                                        <label class="col-lg-4 col-form-label">ENTER AMOUNT:
                                                                            <small class="text-muted d-block"> Please enter
                                                                                the amount to be deposited in selected
                                                                                currency</small>
                                                                        </label>
                                                                        <div class="col-lg-8">
                                                                            <div class="mb-3 input-group">
                                                                                <span class="input-group-text">USD</span>
                                                                                <input name="user[deposit]"
                                                                                    id="deposit_amount" type="number"
                                                                                    class="form-control fill tradedeposit_amount"
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
                                                                                        name="a[register]"
                                                                                        class="btn btn-primary col-12"
                                                                                        value="Deposit To Trade Account">
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $("#tradeDepositForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "{{ route('trade-deposit') }}",
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: response.success
                    }).then(() => {
                        window.location.href = '{{ route('trade-deposit') }}';
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: xhr.responseJSON.message,
                        text: xhr.responseJSON.error
                    });
                }
            });
        });
    </script>
@endsection
