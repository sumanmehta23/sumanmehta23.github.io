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
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4>Process Internal Transfer</h4>
                                        </div>
                                        <div class="card-body">
                                            @if ($liveaccount_details->count() > 0)
                                                <form method="post" action="{{ route('process-transfer_store') }}">
                                                    @csrf
                                                    <div class="row align-items-center">
                                                        <div class="col-md-5">
                                                            <label class="form-label">Select From Account</label>
                                                            @foreach ($liveaccount_details as $acc)
                                                                @php
                                                                    if ($acc->email=='tammaru@gmail.com') {
                                                                        {{ dd($acc->email) }}
                                                                    }
                                                                @endphp
                                                                <div class="p-3 my-3 border rounded price-check">
                                                                    <div class="form-check">
                                                                        <input type="radio" name="fromAccount"
                                                                            data-balance="{{ $acc->balance  }}"
                                                                            class="form-check-input input-primary"
                                                                            id="fA{{ $acc->id }}" value="{{ $acc->id }}">
                                                                        <label class="form-check-label d-block"
                                                                            for="fA{{ $acc->id }}">
                                                                            <span class="row">
                                                                                <span class="col-6">
                                                                                    <span class="mb-0 h4 d-block">
                                                                                        <img src="/assets/images/mt5.png"
                                                                                            alt="user-image"
                                                                                            class="user-avtar wid-40">
                                                                                        {{ $acc->code }}
                                                                                    </span>
                                                                                </span>
                                                                                <span class="col-6 text-end">
                                                                                    <span
                                                                                        class="mb-0 h4 d-block f-w-500">${{ $acc->balance - ($acc->BonusTransaction ? $acc->BonusTransaction->sum('bonus_amount') : 0)  }}</span>
                                                                                    <span class="mb-0 text-muted">Transferable
                                                                                        Balance</span>
                                                                                </span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <div class="col-md-2 d-flex justify-content-center">
                                                            <div class="avtar center">
                                                                <i class="ti ti-arrows-left-right f-40"></i>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <label class="form-label">Select To Account</label>
                                                            @foreach ($liveaccount_details as $acc)
                                                                @if($acc->accountType->ac_group !== 'LM\\B-Book\\10x\\DF-B' || $acc->successful_trade_deposits_count == 0)
                                                                    <div class="p-3 my-3 border rounded price-check">
                                                                        <div class="form-check">
                                                                            <input type="radio" name="toAccount"
                                                                                data-balance="{{ $acc->balance }}"
                                                                                class="form-check-input input-primary"
                                                                                id="tA{{ $acc->id }}" value="{{ $acc->id }}">
                                                                            <label class="form-check-label d-block"
                                                                                for="tA{{ $acc->id }}">
                                                                                <span class="row">
                                                                                    <span class="col-6">
                                                                                        <span class="mb-0 h4 d-block">
                                                                                            <img src="/assets/images/mt5.png"
                                                                                                alt="user-image"
                                                                                                class="user-avtar wid-40">
                                                                                            {{ $acc->code }}
                                                                                        </span>
                                                                                    </span>
                                                                                    <span class="col-6 text-end">
                                                                                        <span
                                                                                            class="mb-0 h4 d-block f-w-500">${{ $acc->balance - ($acc->BonusTransaction ? $acc->BonusTransaction->sum('bonus_amount') : 0) }}</span>
                                                                                        <span class="mb-0 text-muted">Transferable
                                                                                            Balance</span>
                                                                                    </span>
                                                                                </span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                    <div class="mt-5 row align-items-center">
                                                        <div class="col-md-6"></div>
                                                        <div class="col-md-6">
                                                            <label class="form-label" for="exampleFormControlSelect1">Enter
                                                                Amount</label>
                                                            <div class="mb-3 input-group">
                                                                <span class="input-group-text">USD</span>
                                                                <input type="number" min="0.01" step="0.01"
                                                                    class="form-control transferable_amount"
                                                                    name="transferable_amount" required>
                                                            </div>
                                                            <div class="form-group text-end">
                                                                <div class="gap-2 mt-4 d-grid">
                                                                    <button class="btn btn-primary" type="submit">
                                                                        <i class="ti ti-archive me-2"></i> Process Transfer
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            @else
                                                <div class="d-flex justify-content-center">
                                                    <a href="{{ route('show-live-account-form') }}" class="d-grid">
                                                        <button class="btn btn-primary">
                                                            <span class="text-truncate w-100">Create New Live Account</span>
                                                        </button>
                                                    </a>
                                                </div>
                                            @endif
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
    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
            });
        </script>
    @endif
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
                title: 'Something went wrong',
                text: '{{ session('error') }}',
                showConfirmButton: true
            });
        </script>
    @endif
    <script>
        $('[name="fromAccount"]').change(function () {
            var facc = $('[name="fromAccount"]:checked').val();
            var facc_mbalance = $('[name="fromAccount"]:checked').data("balance");
            $('[name="toAccount"]').closest(".price-check").removeClass("d-none");
            $('[name="toAccount"][value="' + facc + '"]').closest(".price-check").addClass("d-none");
            $(".transferable_amount").attr("max", facc_mbalance);
        });
    </script>
@endsection
