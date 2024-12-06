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


                        <div class="row">
                            <div class="col-xl-8">
                                <div class="card">
                                    <div class="card-body border-bottom">
                                        <h6>Withdraw Funds</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="my-4 divider"><span>SELECT MT5 ACCOUNT</span></div>
                                        <div class="row g-1">
                                            @foreach ($liveaccount_details as $liveaccount)
                                                <div class="col-md-3 col-lg-4 col-xl-4">
                                                    <div class="border rounded address-check">
                                                        <div class="form-check paycard">
                                                            <input id="liveaccount{{ $liveaccount->code }}" type="radio"
                                                                name="live-account"
                                                                class="select-liveaccount form-check-input input-primary"
                                                                data-balance="{{ $liveaccount->balance }}"
                                                                value="{{ $liveaccount->id }}">
                                                            <label class="form-check-label d-block" required>
                                                                <div class="p-1 my-1 row">
                                                                    <span class="mt-1 col-6">
                                                                        <span class="pb-0 mb-0 h5 d-block f-w-500 f-14">
                                                                            <img src="{{ asset('assets/images/mt5.png') }}"
                                                                                alt="user-image" class="wid-25 me-1 ms-1">
                                                                            {{ $liveaccount->code }}
                                                                        </span>
                                                                    </span>
                                                                    <span class="pb-0 mb-0 col-6 text-end pe-3">
                                                                        <span class="mb-0 h5 d-block f-w-500">
                                                                            ${{ $liveaccount->balance ?? '0.0000' }}
                                                                        </span>
                                                                        <span class="mb-0 text-muted f-10">Current
                                                                            Balance</span>
                                                                    </span>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="my-4 divider"><span>SELECT WITHDRAW METHOD</span>
                                        </div>
                                        <div class="row g-1">
                                            @if ($walletenabled)
                                                <div class="col-6 col-lg-6 col-xl-6">
                                                    <div class="border rounded address-check trade-withdraw-type">
                                                        <div class="form-check">
                                                            <input type="radio" name="withdraw_type"
                                                                class="form-check-input input-primary tradefund-deposit"
                                                                id="wallet_withdraw" value="Wallet Transfer"
                                                                data-type="Wallet-Transfer" checked>

                                                            <label class="form-check-label d-block" for="wallet_withdraw">
                                                                <div class="p-2 my-1">
                                                                    <span class="row">
                                                                        <span class="mt-1 col-6">
                                                                            <span
                                                                                class="pb-0 mb-0 h5 d-block f-w-500 f-14">Wallets</span>
                                                                            <span class="f-10 text-muted">Wallet
                                                                                Transfer</span>
                                                                        </span>
                                                                        <span class="pb-0 mb-0 col-6 text-end pe-3">
                                                                            <span
                                                                                class="mb-0 h5 d-block f-w-500">${{ $walletBalance ?? '0.0000' }}</span>
                                                                            <span class="mb-0 text-muted f-10">Current
                                                                                Balance</span>
                                                                        </span>
                                                                    </span>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="my-4 divider"><span>WITHDRAW DETAILS</span></div>
                                        <div id="walletwithdrawal" class="trade-withdrawal-content">
                                            <form method="post" style="padding:10px;"
                                                class="md-float-material form-material" enctype="multipart/form-data"
                                                id="tradeWithdrawalForm">
                                                @csrf
                                                <input type="hidden" name="user[email]" value="{{ session('clogin') }}"
                                                    required class="form-control fill">
                                                <input type="hidden" name="account_id" value=""
                                                    class="user_code form-control fill" readonly required>
                                                <input type="hidden" name="withdraw_type" value="Wallet Withdrawal">

                                                <div class="row">
                                                    <div class="mt-2 col-12">
                                                        <div class="form-group row">
                                                            <label class="col-lg-4 col-form-label">ENTER AMOUNT:
                                                                <small class="text-muted d-block">Please
                                                                    enter the amount that you need to
                                                                    transfer</small>
                                                            </label>
                                                            <div class="col-lg-8">
                                                                <div class="mb-3 input-group">
                                                                    <span class="input-group-text">$</span>
                                                                    <input type="number" class="form-control"
                                                                        name="withdraw_amount"
                                                                        aria-label="Amount (to the nearest dollar)"
                                                                        required>
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
                                                            <input type="submit" name="fund_add"
                                                                class="btn btn-primary col-12"
                                                                value="Withdraw From Trade Account">
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
                                            <div class="col-8 d-flex flex-column align-items-start justify-content-center">
                                                <h3 class="text-white f-w-500">Fuel Your Trading Journey
                                                </h3>
                                                <span class="py-2 text-white f-16">Deposit now and unlock
                                                    the gateway to global markets.</span>
                                            </div>
                                            <div class="col-4 text-end">
                                                <img src="{{ asset('assets/images/fund_now.png') }}" alt="img"
                                                    class="img-fluid wid-110">
                                            </div>
                                        </div>
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
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $("#tradeWithdrawalForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "{{ route('trade-withdrawal') }}",
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: response.success
                    }).then(() => {
                        window.location.reload(true);
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: xhr.responseJSON.message
                    });
                }
            });
        });
    </script>
@endsection
