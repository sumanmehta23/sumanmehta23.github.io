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
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-500 mb-2">From Account</label>
                                                                <select name="fromAccount" id="fromAccountSelect" class="form-select form-control" required>
                                                                    <option value="">-- Select Source Account --</option>
                                                                    @foreach ($liveaccount_details as $acc)
                                                                        <option value="{{ $acc->id }}" 
                                                                            data-balance="{{ $acc->balance }}"
                                                                            data-transferable="{{ max(0, $acc->balance - $acc->totalBonusDeposit) }}"
                                                                            data-code="{{ $acc->code }}">
                                                                            {{ $acc->code }} - ${{ number_format(max(0, $acc->balance - $acc->totalBonusDeposit), 2) }} Available
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <small class="d-block mt-2 text-muted">
                                                                    <i class="ti ti-info-circle"></i>
                                                                    <span id="fromAccountInfo">Select an account to see transferable balance</span>
                                                                </small>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-500 mb-2">To Account</label>
                                                                <select name="toAccount" id="toAccountSelect" class="form-select form-control" required>
                                                                    <option value="">-- Select Destination Account --</option>
                                                                    @foreach ($liveaccount_details as $acc)
                                                                        @if($acc->accountType->ac_group !== 'LM\\B-Book\\10x\\DF-B' || $acc->successful_trade_deposits_count == 0)
                                                                            <option value="{{ $acc->id }}" 
                                                                                data-code="{{ $acc->code }}"
                                                                                data-balance="{{ $acc->balance }}">
                                                                                {{ $acc->code }} - ${{ number_format(max(0, $acc->balance - $acc->totalBonusDeposit), 2) }} Available
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                                <small class="d-block mt-2 text-muted">
                                                                    <i class="ti ti-info-circle"></i>
                                                                    <span id="toAccountInfo">Select a destination account</span>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-4">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-500 mb-2">Transfer Amount (USD)</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light">
                                                                        <i class="ti ti-currency-dollar"></i>
                                                                    </span>
                                                                    <input type="number" 
                                                                        min="0.01" 
                                                                        step="0.01"
                                                                        class="form-control transferable_amount"
                                                                        name="transferable_amount" 
                                                                        placeholder="Enter amount"
                                                                        required>
                                                                </div>
                                                                <small class="d-block mt-2 text-muted">
                                                                    <span id="amountValidation">Maximum: Select source account first</span>
                                                                </small>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-500 mb-2">&nbsp;</label>
                                                                <button class="btn btn-primary w-100" type="submit">
                                                                    <i class="ti ti-send me-2"></i> Process Transfer
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="alert alert-info mt-4 mb-0 d-inline-flex align-items-start py-2 px-3" role="alert" style="font-size: 0.9rem; max-width: 100%;">
                                                        <i class="ti ti-alert-circle me-2 mt-1 flex-shrink-0"></i>
                                                        <span><strong>Note:</strong> Transfers are processed instantly. Your transferable balance excludes bonus deposits.</span>
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
        $(document).ready(function () {
            // When from account changes
            $('#fromAccountSelect').on('change', function () {
                var selectedId = $(this).val();
                var maxBalance = $(this).find('option:selected').data('transferable');
                var fromCode = $(this).find('option:selected').data('code');

                if (selectedId) {
                    // Update amount input max value
                    $(".transferable_amount").attr("max", maxBalance);
                    $('#amountValidation').text('Maximum: $' + parseFloat(maxBalance).toFixed(2));
                    $('#fromAccountInfo').text('Account: ' + fromCode + ' - Available: $' + parseFloat(maxBalance).toFixed(2));

                    // Update to account options - disable the selected from account
                    $('#toAccountSelect option').prop('disabled', false);
                    $('#toAccountSelect option[value="' + selectedId + '"]').prop('disabled', true);

                    // Reset to account if current selection is the from account
                    if ($('#toAccountSelect').val() === selectedId) {
                        $('#toAccountSelect').val('').change();
                    }
                } else {
                    $(".transferable_amount").attr("max", "");
                    $('#amountValidation').text('Maximum: Select source account first');
                    $('#fromAccountInfo').text('Select an account to see transferable balance');
                    $('#toAccountSelect option').prop('disabled', false);
                }
            });

            // When to account changes
            $('#toAccountSelect').on('change', function () {
                var toCode = $(this).find('option:selected').data('code');
                if ($(this).val()) {
                    $('#toAccountInfo').text('Account: ' + toCode);
                } else {
                    $('#toAccountInfo').text('Select a destination account');
                }
            });

            // Form validation before submit
            $('form').on('submit', function (e) {
                var fromAccount = $('#fromAccountSelect').val();
                var toAccount = $('#toAccountSelect').val();
                var amount = $('.transferable_amount').val();

                if (!fromAccount || !toAccount || !amount) {
                    e.preventDefault();
                    alert('Please select source account, destination account, and enter transfer amount');
                    return false;
                }

                if (fromAccount === toAccount) {
                    e.preventDefault();
                    alert('Source and destination accounts must be different');
                    return false;
                }

                var maxAmount = parseFloat($(".transferable_amount").attr("max"));
                if (parseFloat(amount) > maxAmount) {
                    e.preventDefault();
                    alert('Transfer amount exceeds available balance');
                    return false;
                }
            });
        });
    </script>
@endsection
