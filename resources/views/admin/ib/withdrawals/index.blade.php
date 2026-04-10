@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">IB Withdrawals & Overpayment Tracking</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.ib.dashboard') }}">IB</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Withdrawals</li>
                </ol>
            </div>

            <!-- STATS CARDS -->
            <div class="mb-3 row">
                <div class="col-xl-3 col-lg-6">
                    <div class="card border-primary custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="card-title">Total Withdrawals</span>
                                    <h5 class="card-value">{{ $stats->total_withdrawals ?? 0 }}</h5>
                                </div>
                                <i class="fa fa-arrow-circle-down text-primary" style="font-size: 2em; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6">
                    <div class="card border-success custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="card-title">Total Withdrawn</span>
                                    <h5 class="card-value">${{ number_format($stats->total_withdrawn ?? 0, 2) }}</h5>
                                </div>
                                <i class="fa fa-dollar text-success" style="font-size: 2em; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6">
                    <div class="card border-warning custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="card-title">With Overpaid</span>
                                    <h5 class="card-value">{{ $stats->withdrawals_with_overpaid ?? 0 }}</h5>
                                </div>
                                <i class="fa fa-exclamation-triangle text-warning"
                                    style="font-size: 2em; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6">
                    <div class="card border-danger custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="card-title">Overpaid Withdrawn</span>
                                    <h5 class="card-value">${{ number_format($stats->total_overpaid_withdrawn ?? 0, 2) }}
                                    </h5>
                                </div>
                                <i class="fa fa-flag text-danger" style="font-size: 2em; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTERS -->
            <div class="mb-3 row">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <form method="GET" class="row align-items-end g-2">
                                <div class="col-md-3">
                                    <label class="form-label">IB Referral Code</label>
                                    <input type="text" name="referral_code" class="form-control"
                                        value="{{ $filters['referral_code'] ?? '' }}" placeholder="e.g. REF001">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Show</label>
                                    <select name="has_overpaid" class="form-control">
                                        <option value="">All Withdrawals</option>
                                        <option value="1" {{ $filters['has_overpaid'] === '1' ? 'selected' : '' }}>
                                            With Overpaid Only
                                        </option>
                                        <option value="0" {{ $filters['has_overpaid'] === '0' ? 'selected' : '' }}>
                                            Clean Only (No Overpaid)
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.ib.withdrawals.index') }}" class="btn btn-outline-secondary">
                                        <i class="fa fa-refresh me-1"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WITHDRAWALS TABLE -->
            <div class="row">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="card-title">IB Withdrawals</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>IB Code</th>
                                            <th>Withdrawal Type</th>
                                            <th>Amount</th>
                                            <th>Overpaid</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($withdrawals as $withdrawal)
                                            <tr>
                                                <td>
                                                    <small>{{ $withdrawal->withdraw_date ? \Carbon\Carbon::parse($withdrawal->withdraw_date)->format('Y-m-d H:i') : '-' }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $withdrawal->referral_code }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge"
                                                        style="background-color: {{ $withdrawal->withdraw_type === 'Wallet Withdrawal' ? '#1e7e34' : '#0c5460' }};">
                                                        {{ $withdrawal->withdraw_type }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>${{ number_format($withdrawal->amount_decimal, 2) }}</strong>
                                                </td>
                                                <td>
                                                    @if($withdrawal->has_overpaid)
                                                        <span class="badge bg-danger">
                                                            <i class="fa fa-flag me-1"></i>
                                                            ${{ number_format($withdrawal->overpaid_decimal, 2) }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success">Clean</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($withdrawal->status == 1)
                                                        <span class="badge bg-success">Approved</span>
                                                    @elseif($withdrawal->status == 0)
                                                        <span class="badge bg-warning">Pending</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $withdrawal->status }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.ib.withdrawals.show', $withdrawal->id) }}"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="py-5 text-center text-muted">
                                                    <i class="fa fa-inbox" style="font-size: 2em;"></i>
                                                    <p class="mt-2">No withdrawals found</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 d-flex justify-content-center">
                        <style>
                            .pagination {
                                margin-bottom: 0;
                            }

                            .pagination a,
                            .pagination span {
                                font-size: 0.875rem;
                                padding: 0.375rem 0.75rem;
                            }

                            .pagination a i,
                            .pagination span i {
                                font-size: 0.875rem;
                            }
                        </style>
                        {{ $withdrawals->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection