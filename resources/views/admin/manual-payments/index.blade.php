@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Manual Coin Payments</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/admin/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Manual Payments</li>
                </ol>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('failed_payments'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Failed Payments:</strong>
                    <ul class="mb-0">
                        @foreach(session('failed_payments') as $failed)
                            <li>{{ $failed['email'] ?? 'Unknown' }}: {{ $failed['reason'] }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Status Summary Cards -->
            <div class="mb-4 row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fe fe-clock fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <h6 class="mb-1">Pending</h6>
                                    <h3 class="mb-0">{{ $statusCounts['pending'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fe fe-loader fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <h6 class="mb-1">Processing</h6>
                                    <h3 class="mb-0">{{ $statusCounts['processing'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fe fe-check-circle fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <h6 class="mb-1">Completed</h6>
                                    <h3 class="mb-0">{{ $statusCounts['completed'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-danger-transparent">
                                        <i class="fe fe-x-circle fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <h6 class="mb-1">Rejected</h6>
                                    <h3 class="mb-0">{{ $statusCounts['rejected'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="mb-0 card-title">Pending Manual Payments List</h3>
                            <div>
                                <button type="button" class="btn btn-sm btn-success" id="processSelectedBtn" disabled>
                                    <i class="fe fe-check me-1"></i> Process Selected
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" id="rejectSelectedBtn" disabled>
                                    <i class="fe fe-x me-1"></i> Reject Selected
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filters -->
                            <form method="GET" action="{{ url('/admin/manual-payments') }}" class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="">Pending & Processing</option>
                                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                                Pending</option>
                                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                                Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Email</label>
                                        <input type="text" name="email" class="form-control" placeholder="Search by email"
                                            value="{{ request('email') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Transaction ID</label>
                                        <input type="text" name="transaction_id" class="form-control"
                                            placeholder="Search by TX ID" value="{{ request('transaction_id') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="gap-2 d-flex">
                                            <button type="submit" class="btn btn-primary flex-fill">
                                                <i class="fe fe-search me-1"></i> Search
                                            </button>
                                            <a href="{{ url('/admin/manual-payments') }}" class="btn btn-secondary">
                                                <i class="fe fe-refresh-cw"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Payments Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover text-nowrap">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30px;">
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>
                                            <th>Date</th>
                                            <th>Email</th>
                                            <th>Code</th>
                                            <th>Transaction ID</th>
                                            <th>Coin</th>
                                            <th>Coin Amount</th>
                                            <th>Requested USD</th>
                                            <th>Actual USD</th>
                                            <th>Difference</th>
                                            <th>Promo</th>
                                            <th>Status</th>
                                            <th style="width: 150px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pendingPayments as $payment)
                                            <tr>
                                                <td>
                                                    @if(!in_array($payment->status, ['completed', 'rejected']))
                                                        <input type="checkbox" name="payment_ids[]" value="{{ $payment->id }}"
                                                            class="form-check-input payment-checkbox">
                                                    @endif
                                                </td>
                                                <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <a href="{{ url('/admin/manual-payments/' . $payment->id) }}"
                                                        class="text-primary">
                                                        {{ $payment->email }}
                                                    </a>
                                                </td>
                                                <td>{{ $payment->code }}</td>
                                                <td>
                                                    <small class="text-muted"
                                                        style="font-size: 11px;">{{ Str::limit($payment->transaction_id, 20) }}</small>
                                                    <button type="button" class="p-0 btn btn-xs btn-link"
                                                        onclick="copyToClipboard('{{ $payment->transaction_id }}')">
                                                        <i class="fe fe-copy"></i>
                                                    </button>
                                                </td>
                                                <td>{{ $payment->coin }}</td>
                                                <td>{{ number_format($payment->coin_amount, 8) }}</td>
                                                <td>${{ number_format($payment->initial_requested_amount, 2) }}</td>
                                                <td>
                                                    @if($payment->usd_value)
                                                        <strong>${{ number_format($payment->usd_value, 2) }}</strong>
                                                    @else
                                                        <span class="badge bg-warning">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($payment->usd_value && $payment->initial_requested_amount)
                                                        @php
                                                            $diff = $payment->usd_value - $payment->initial_requested_amount;
                                                            $diffPercent = ($diff / $payment->initial_requested_amount) * 100;
                                                        @endphp
                                                        <span
                                                            class="badge bg-{{ $diff > 0 ? 'success' : ($diff < 0 ? 'danger' : 'secondary') }}">
                                                            {{ $diff > 0 ? '+' : '' }}${{ number_format(abs($diff), 2) }}
                                                            ({{ $diff > 0 ? '+' : '' }}{{ number_format($diffPercent, 1) }}%)
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($payment->promocode)
                                                        <span class="badge bg-info">{{ $payment->promocode }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'warning',
                                                            'processing' => 'info',
                                                            'completed' => 'success',
                                                            'rejected' => 'danger',
                                                        ];
                                                    @endphp
                                                    <span class="badge bg-{{ $statusColors[$payment->status] ?? 'secondary' }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ url('/admin/manual-payments/' . $payment->id) }}"
                                                            class="btn btn-info" title="View Details">
                                                            <i class="fe fe-eye"></i>
                                                        </a>
                                                        @if(!$payment->usd_value || in_array($payment->status, ['pending']))
                                                            <button type="button" class="btn btn-warning refresh-usd-btn"
                                                                data-payment-id="{{ $payment->id }}"
                                                                data-transaction-id="{{ $payment->transaction_id }}"
                                                                title="Refresh USD Value">
                                                                <i class="fe fe-refresh-cw"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="13" class="py-4 text-center">
                                                    <i class="fe fe-inbox fs-40 text-muted"></i>
                                                    <p class="mt-2 text-muted">No pending manual payments found</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-3">
                                {{ $pendingPayments->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Selected Payments</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason</label>
                            <textarea name="rejection_reason" class="form-control" rows="4"
                                placeholder="Enter reason for rejection (optional)"></textarea>
                        </div>
                        <div id="rejectPaymentIds"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Payments</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                // Select all checkbox
                $('#selectAll').on('change', function () {
                    $('.payment-checkbox').prop('checked', $(this).prop('checked'));
                    updateButtonStates();
                });

                // Individual checkbox
                $('.payment-checkbox').on('change', function () {
                    updateButtonStates();
                });

                // Update button states
                function updateButtonStates() {
                    const checkedCount = $('.payment-checkbox:checked').length;
                    $('#processSelectedBtn, #rejectSelectedBtn').prop('disabled', checkedCount === 0);
                }

                // Process selected
                $('#processSelectedBtn').on('click', function () {
                    if ($('.payment-checkbox:checked').length === 0) {
                        alert('Please select at least one payment to process');
                        return;
                    }

                    if (confirm('Are you sure you want to process the selected payments?')) {
                        // Create form dynamically
                        const $form = $('<form>', {
                            method: 'POST',
                            action: '{{ url("/admin/manual-payments/process") }}'
                        });

                        // Add CSRF token
                        $form.append('@csrf');

                        // Add selected payment IDs
                        $('.payment-checkbox:checked').each(function () {
                            $form.append($('<input>', {
                                type: 'hidden',
                                name: 'payment_ids[]',
                                value: $(this).val()
                            }));
                        });

                        // Submit form
                        $form.appendTo('body').submit();
                    }
                });

                // Reject selected
                $('#rejectSelectedBtn').on('click', function () {
                    if ($('.payment-checkbox:checked').length === 0) {
                        alert('Please select at least one payment to reject');
                        return;
                    }

                    // Copy checked checkboxes to modal
                    const $rejectPaymentIds = $('#rejectPaymentIds');
                    $rejectPaymentIds.empty();
                    $('.payment-checkbox:checked').each(function () {
                        $rejectPaymentIds.append('<input type="hidden" name="payment_ids[]" value="' + $(this).val() + '">');
                    });

                    $('#rejectForm').attr('action', '{{ url("/admin/manual-payments/reject") }}');
                    $('#rejectModal').modal('show');
                });

                // Refresh USD value via AJAX
                $('.refresh-usd-btn').on('click', function () {
                    const $btn = $(this);
                    const paymentId = $btn.data('payment-id');
                    const transactionId = $btn.data('transaction-id');
                    const $icon = $btn.find('i');
                    const $row = $btn.closest('tr');

                    if (!confirm('Refresh USD value for transaction ' + transactionId + '?')) {
                        return;
                    }

                    // Disable button and show loading state
                    $btn.prop('disabled', true);
                    $icon.addClass('fa-spin');

                    $.ajax({
                        url: '{{ url("/admin/manual-payments") }}/' + paymentId + '/refresh-usd',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (response.success) {
                                // Update the USD value cell
                                const usdCell = $row.find('td').eq(8); // Actual USD column
                                usdCell.html('<strong>$' + parseFloat(response.usd_value).toFixed(2) + '</strong>');

                                // Update the difference cell
                                const diffCell = $row.find('td').eq(9);
                                const requestedAmount = parseFloat(response.requested_amount);
                                const actualAmount = parseFloat(response.usd_value);
                                const diff = actualAmount - requestedAmount;
                                const diffPercent = (diff / requestedAmount) * 100;

                                let badgeClass = diff > 0 ? 'success' : (diff < 0 ? 'danger' : 'secondary');
                                let sign = diff > 0 ? '+' : '';

                                diffCell.html(
                                    '<span class="badge bg-' + badgeClass + '">' +
                                    sign + '$' + Math.abs(diff).toFixed(2) + ' ' +
                                    '(' + sign + diffPercent.toFixed(1) + '%)' +
                                    '</span>'
                                );

                                // Remove the refresh button if USD value is now set
                                $btn.remove();

                                // Show success message
                                alert('USD value refreshed successfully!');
                            } else {
                                alert('Error: ' + (response.message || 'Failed to refresh USD value'));
                                $btn.prop('disabled', false);
                                $icon.removeClass('fa-spin');
                            }
                        },
                        error: function (xhr) {
                            let errorMsg = 'Failed to refresh USD value';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            alert('Error: ' + errorMsg);
                            $btn.prop('disabled', false);
                            $icon.removeClass('fa-spin');
                        }
                    });
                });
            });

            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(function () {
                    alert('Transaction ID copied to clipboard!');
                });
            }
        </script>
    @endpush
@endsection