@extends('layouts.crm.crm')
@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header mb-0 pb-0">
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
                        <div class="card-body p-0">
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
                                                                <div class="price-check border rounded p-3 my-3">
                                                                    <div class="form-check">
                                                                        <input type="radio" name="fromAccount"
                                                                            data-balance="{{ $acc->Balance }}"
                                                                            class="form-check-input input-primary"
                                                                            id="fA{{ $acc->trade_id }}"
                                                                            value="{{ $acc->trade_id }}">
                                                                        <label class="form-check-label d-block"
                                                                            for="fA{{ $acc->trade_id }}">
                                                                            <span class="row">
                                                                                <span class="col-6">
                                                                                    <span class="h4 mb-0 d-block">
                                                                                        <img src="/assets/images/mt5.png"
                                                                                            alt="user-image"
                                                                                            class="user-avtar wid-40">
                                                                                        {{ $acc->trade_id }}
                                                                                    </span>
                                                                                </span>
                                                                                <span class="col-6 text-end">
                                                                                    <span
                                                                                        class="h4 mb-0 d-block f-w-500">${{ $acc->Balance }}</span>
                                                                                    <span
                                                                                        class="text-muted mb-0">Transferable
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
                                                                <div class="price-check border rounded p-3 my-3">
                                                                    <div class="form-check">
                                                                        <input type="radio" name="toAccount"
                                                                            data-balance="{{ $acc->Balance }}"
                                                                            class="form-check-input input-primary"
                                                                            id="tA{{ $acc->trade_id }}"
                                                                            value="{{ $acc->trade_id }}">
                                                                        <label class="form-check-label d-block"
                                                                            for="tA{{ $acc->trade_id }}">
                                                                            <span class="row">
                                                                                <span class="col-6">
                                                                                    <span class="h4 mb-0 d-block">
                                                                                        <img src="/assets/images/mt5.png"
                                                                                            alt="user-image"
                                                                                            class="user-avtar wid-40">
                                                                                        {{ $acc->trade_id }}
                                                                                    </span>
                                                                                </span>
                                                                                <span class="col-6 text-end">
                                                                                    <span
                                                                                        class="h4 mb-0 d-block f-w-500">${{ $acc->Balance }}</span>
                                                                                    <span
                                                                                        class="text-muted mb-0">Transferable
                                                                                        Balance</span>
                                                                                </span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mt-5">
                                                        <div class="col-md-6"></div>
                                                        <div class="col-md-6">
                                                            <label class="form-label" for="exampleFormControlSelect1">Enter
                                                                Amount</label>
                                                            <div class="input-group mb-3">
                                                                <span class="input-group-text">USD</span>
                                                                <input type="number" min="1"
                                                                    class="form-control transferable_amount"
                                                                    name="transferable_amount" required>
                                                            </div>
                                                            <div class="form-group text-end">
                                                                <div class="d-grid gap-2 mt-4">
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
                                                    <a href="{{ route('liveAccounts.create') }}" class="d-grid">
                                                        <button class="btn btn-primary">
                                                            <span class="text-truncate w-100">Create new Live Account</span>
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
        $('[name="fromAccount"]').change(function() {
            var facc = $('[name="fromAccount"]:checked').val();
            var facc_mbalance = $('[name="fromAccount"]:checked').data("balance");
            $('[name="toAccount"]').closest(".price-check").removeClass("d-none");
            $('[name="toAccount"][value="' + facc + '"]').closest(".price-check").addClass("d-none");
            $(".transferable_amount").attr("max", facc_mbalance);
        });
    </script>
@endsection
