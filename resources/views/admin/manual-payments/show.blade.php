@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Manual Payment Details</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/admin/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/admin/manual-payments') }}">Manual Payments</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Details</li>
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

            <div class="row">
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Payment Information</h3>
                            <div>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'completed' => 'success',
                                        'rejected' => 'danger',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$payment->status] ?? 'secondary' }} fs-14">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th style="width: 30%;">Email</th>
                                            <td>{{ $payment->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Code</th>
                                            <td>{{ $payment->code }}</td>
                                        </tr>
                                        <tr>
                                            <th>Transaction ID</th>
                                            <td>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <code class="flex-grow-1">{{ $payment->transaction_id }}</code>
                                                    <button type="button" class="btn btn-sm btn-link"
                                                        onclick="copyToClipboard('{{ $payment->transaction_id }}')">
                                                        <i class="fe fe-copy"></i> Copy
                                                    </button>
                                                </div>
                                                <a href="https://polygonscan.com/tx/{{ $payment->transaction_id }}"
                                                    target="_blank" class="btn btn-sm btn-info mt-2">
                                                    <i class="fe fe-external-link"></i> View on PolygonScan
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Coin Type</th>
                                            <td>{{ $payment->coin }}</td>
                                        </tr>
                                        <tr>
                                            <th>Coin Amount</th>
                                            <td>{{ number_format($payment->coin_amount, 8) }} {{ $payment->coin }}</td>
                                        </tr>
                                        <tr>
                                            <th>Initial Requested Amount</th>
                                            <td><strong>${{ number_format($payment->initial_requested_amount, 2) }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Actual USD Value</th>
                                            <td>
                                                @if($payment->usd_value)
                                                    <strong
                                                        class="text-success fs-16">${{ number_format($payment->usd_value, 2) }}</strong>
                                                @else
                                                    <span class="badge bg-warning">Not Available</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($payment->usd_value && $payment->initial_requested_amount)
                                                                            <tr>
                                                                                <th>Difference</th>
                                                                                <td>
                                                                                    @php
                                                                                        $diff = $payment->usd_value - $payment->initial_requested_amount;
                                                                                        $diffPercent = ($diff / $payment->initial_requested_amount) * 100;
                                                                                    @endphp
                                             <span
                                                                                        class="badge bg-{{ $diff > 0 ? 'success' : ($diff < 0 ? 'danger' : 'secondary') }} fs-14">
                                                                                        {{ $diff > 0 ? '+' : '' }}${{ number_format(abs($diff), 2) }}
                                                                                        ({{ $diff > 0 ? '+' : '' }}{{ number_format($diffPercent, 2) }}%)
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                        @endif
                                        <tr>
                                            <th>Promocode</th>
                                            <td>
                                                @if($payment->promocode)
                                                    <span class="badge bg-info fs-14">{{ $payment->promocode }}</span>
                                                @else
                                                    <span class="text-muted">No promocode</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Deposit Date</th>
                                            <td>{{ $payment->deposit_date ? $payment->deposit_date->format('Y-m-d H:i:s') : 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ $payment->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        @if($payment->processed_at)
                                            <tr>
                                                <th>Processed At</th>
                                                <td>{{ $payment->processed_at->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Processed By</th>
                                                <td>{{ $payment->processor ? $payment->processor->name : 'N/A' }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Admin Notes</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ url('/admin/manual-payments/' . $payment->id . '/notes') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea name="admin_notes" class="form-control" rows="4"
                                        placeholder="Add notes about this payment...">{{ $payment->admin_notes }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-save me-1"></i> Save Notes
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Polygon Response -->
                    @if($polygonResponse)
                        <div class="card custom-card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">Polygon USD Calculation Response</h3>
                            </div>
                            <div class="card-body">
                                <pre class="bg-light p-3 rounded"
                                    style="max-height: 400px; overflow-y: auto;">{{ $polygonResponse }}</pre>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-xl-4">
                    <!-- Actions -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if(!$payment->usd_value || $payment->status == 'pending')
                                    <form action="{{ url('/admin/manual-payments/' . $payment->id . '/refresh-usd') }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning w-100">
                                            <i class="fe fe-refresh-cw me-1"></i> Refresh USD Value
                                        </button>
                                    </form>
                                @endif

                                @if($payment->status == 'pending' || $payment->status == 'processing')
                                    <form action="{{ url('/admin/manual-payments/process') }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to process this payment?');">
                                        @csrf
                                        <input type="hidden" name="payment_ids[]" value="{{ $payment->id }}">
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fe fe-check-circle me-1"></i> Process Payment
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">
                                        <i class="fe fe-x-circle me-1"></i> Reject Payment
                                    </button>
                                @endif

                                <a href="{{ url('/admin/manual-payments') }}" class="btn btn-secondary w-100">
                                    <i class="fe fe-arrow-left me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Related Information -->
                    @if($payment->user)
                        <div class="card custom-card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">User Information</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th>Name</th>
                                            <td>{{ $payment->user->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ $payment->user->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>User ID</th>
                                            <td>{{ $payment->user_id }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <a href="{{ url('/admin/client-details/' . $payment->user_id) }}"
                                    class="btn btn-sm btn-info w-100 mt-2" target="_blank">
                                    <i class="fe fe-user me-1"></i> View User Details
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($payment->account)
                        <div class="card custom-card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">Account Information</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th>Code</th>
                                            <td>{{ $payment->account->code }}</td>
                                        </tr>
                                        <tr>
                                            <th>Type</th>
                                            <td>{{ $payment->account->account_type ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Platform</th>
                                            <td>{{ $payment->account->platform ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ url('/admin/manual-payments/reject') }}" method="POST">
                    @csrf
                    <input type="hidden" name="payment_ids[]" value="{{ $payment->id }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason</label>
                            <textarea name="rejection_reason" class="form-control" rows="4"
                                placeholder="Enter reason for rejection (optional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(function () {
                    alert('Transaction ID copied to clipboard!');
                });
            }
        </script>
    @endpush
@endsection