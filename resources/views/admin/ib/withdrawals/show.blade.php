@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Withdrawal Details</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.ib.dashboard') }}">IB</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.ib.withdrawals.index') }}">Withdrawals</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Details</li>
                </ol>
            </div>

            <div class="row">
                <!-- WITHDRAWAL INFO -->
                <div class="col-lg-6">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="card-title">Withdrawal Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-4">
                                    <span class="text-muted">IB Code:</span>
                                </div>
                                <div class="col-8">
                                    <strong>{{ $withdrawal->referral_code }}</strong>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-4">
                                    <span class="text-muted">Amount:</span>
                                </div>
                                <div class="col-8">
                                    <strong>${{ number_format($withdrawal->amount_decimal, 2) }}</strong>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-4">
                                    <span class="text-muted">Type:</span>
                                </div>
                                <div class="col-8">
                                    <span class="badge bg-primary">{{ $withdrawal->withdraw_type }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-4">
                                    <span class="text-muted">Status:</span>
                                </div>
                                <div class="col-8">
                                    @if($withdrawal->status == 1)
                                        <span class="badge bg-success">
                                            <i class="fa fa-check"></i> Approved
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fa fa-clock"></i> Pending
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-4">
                                    <span class="text-muted">Withdrawn:</span>
                                </div>
                                <div class="col-8">
                                    {{ $withdrawal->withdraw_date ? \Carbon\Carbon::parse($withdrawal->withdraw_date)->format('Y-m-d H:i:s') : 'Not yet' }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-4">
                                    <span class="text-muted">Approved By:</span>
                                </div>
                                <div class="col-8">
                                    {{ $withdrawal->approved_by ?? '-' }}
                                </div>
                            </div>

                            <hr />

                            <div class="row mb-3">
                                <div class="col-4">
                                    <span class="text-muted">Has Overpaid:</span>
                                </div>
                                <div class="col-8">
                                    @if($withdrawal->has_overpaid)
                                        <span class="badge bg-danger">
                                            <i class="fa fa-flag"></i> Yes
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fa fa-check"></i> No
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if($withdrawal->has_overpaid)
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <span class="text-muted">Overpaid Amount:</span>
                                    </div>
                                    <div class="col-8">
                                        <strong
                                            class="text-danger">${{ number_format($withdrawal->overpaid_decimal, 2) }}</strong>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- OVERPAID ENTRIES -->
                @if($withdrawal->has_overpaid && count($overpaidEntries) > 0)
                    <div class="col-lg-6">
                        <div class="card custom-card">
                            <div class="card-header">
                                <h6 class="card-title">Overpaid Entries Included</h6>
                                <span class="badge bg-danger">{{ count($overpaidEntries) }} entries</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Position ID</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($overpaidEntries as $entry)
                                                <tr>
                                                    <td>{{ $entry->order_id }}</td>
                                                    <td>{{ $entry->expert_position_id }}</td>
                                                    <td>
                                                        <span class="text-danger">
                                                            ${{ number_format($entry->amount, 2) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>{{ $entry->created_at ? \Carbon\Carbon::parse($entry->created_at)->format('Y-m-d') : '-' }}</small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <a href="{{ route('admin.ib.withdrawals.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection