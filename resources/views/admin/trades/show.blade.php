@extends('layouts.admin.admin')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="page-header">
                <h1 class="page-title">Trade Details</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.trades.index') }}">All Trades</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Trade Details</li>
                </ol>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-12">
                    <div class="card custom-card shadow-sm">
                        <div class="card-header border-0 pb-0">
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between w-100">
                                <div class="mb-3 mb-md-0">
                                    <h5 class="mb-1 text-uppercase fw-semibold">
                                        Trade Ticket #{{ $trade->order_id ?? $trade->code ?? $trade->id }}
                                    </h5>
                                    @php
                                        $user = optional(optional($trade->account)->user);

                                        $full = $user->fullname ?? null;
                                        $full = $full && strtolower($full) !== 'null' ? trim($full) : '';

                                        if ($full !== '') {
                                            $clientName = $full;
                                        } else {
                                            $first = $user->firstname ?? null;
                                            $last  = $user->lastname ?? null;

                                            $first = $first && strtolower($first) !== 'null' ? $first : '';
                                            $last  = $last && strtolower($last) !== 'null' ? $last : '';

                                            $clientName = trim(($first ?? '') . ' ' . ($last ?? ''));
                                        }
                                        $avatarPath = public_path('admin_assets/assets/images/users/client.png');
                                        $hasAvatar  = file_exists($avatarPath);
                                    @endphp
                                    <div class="d-flex align-items-center mt-2">
                                        <div class="me-3">
                                            @if($hasAvatar)
                                                <img src="{{ asset('admin_assets/assets/images/users/client.png') }}"
                                                     alt="Client"
                                                     class="rounded-circle"
                                                     style="width:48px;height:48px;object-fit:cover;">
                                            @else
                                                <div
                                                    class="rounded-circle bg-primary-transparent text-primary d-inline-flex align-items-center justify-content-center"
                                                    style="width:48px;height:48px;">
                                                    <span class="fw-bold">
                                                        {{ $clientName ? strtoupper(mb_substr($clientName, 0, 1)) : 'C' }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $clientName ?: 'Unknown Client' }}</div>
                                            @php
                                                $email = $user->email;
                                                $email = $email && strtolower($email) !== 'null' ? $email : null;
                                            @endphp
                                            <div class="text-muted small">{{ $email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-md-center mb-3 mb-md-0">
                                    <div class="text-muted text-uppercase small">Trading Account</div>
                                    <div class="fw-semibold fs-16">
                                        {{ optional($trade->account)->code ?? 'N/A' }}
                                    </div>
                                </div>

                                <div class="text-md-end">
                                    <div class="text-muted text-uppercase small">Symbol</div>
                                    <div class="fw-semibold fs-16">{{ $trade->symbol }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body pt-4">
                            <div class="row gy-4">
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">Order ID</div>
                                    <div class="fw-semibold mt-1">{{ $trade->order_id ?? $trade->code ?? $trade->id }}</div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">Trade Type</div>
                                    <div class="mt-1">
                                        @php
                                            $isBuy = strtolower($trade->type) === 'buy';
                                        @endphp
                                        <span class="badge rounded-pill {{ $isBuy ? 'bg-success-transparent text-success' : 'bg-danger-transparent text-danger' }}">
                                            {{ strtoupper($trade->type) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">Volume</div>
                                    <div class="fw-semibold mt-1">{{ number_format($trade->volume, 2) }}</div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">Profit / Loss</div>
                                    <div class="fw-semibold mt-1 {{ $trade->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                        ${{ number_format($trade->profit, 2) }}
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row gy-4">
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">Open Price</div>
                                    <div class="fw-semibold mt-1">{{ number_format($trade->open_price, 5) }}</div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">Close Price</div>
                                    <div class="fw-semibold mt-1">
                                        @if($trade->close_price)
                                            {{ number_format($trade->close_price, 5) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">Stop Loss</div>
                                    <div class="fw-semibold mt-1">
                                        {{ $trade->sl !== null ? $trade->sl : '-' }}
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">Take Profit</div>
                                    <div class="fw-semibold mt-1">
                                        {{ $trade->tp !== null ? $trade->tp : '-' }}
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row gy-4">
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">Open Time</div>
                                    <div class="fw-semibold mt-1">
                                        @if($trade->open_time)
                                            {{ $trade->open_time->format('Y-m-d H:i:s') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">Close Time</div>
                                    <div class="fw-semibold mt-1">
                                        @if($trade->close_time)
                                            {{ $trade->close_time->format('Y-m-d H:i:s') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">Status</div>
                                    <div class="mt-1">
                                        @php
                                            $statusClass = match ($trade->status) {
                                                'open' => 'bg-primary-transparent text-primary',
                                                'closed' => 'bg-success-transparent text-success',
                                                'cancelled' => 'bg-danger-transparent text-danger',
                                                default => 'bg-secondary-transparent text-secondary',
                                            };
                                        @endphp
                                        <span class="badge rounded-pill {{ $statusClass }}">
                                            {{ strtoupper($trade->status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">State</div>
                                    <div class="mt-1">
                                        @if($trade->state)
                                            <span class="badge rounded-pill bg-info-transparent text-info">
                                                {{ strtoupper($trade->state) }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row gy-4">
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">Created At</div>
                                    <div class="fw-semibold mt-1">
                                        {{ $trade->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="text-muted text-uppercase small">Last Updated</div>
                                    <div class="fw-semibold mt-1">
                                        {{ $trade->updated_at?->format('Y-m-d H:i:s') ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="text-muted text-uppercase small">Comment</div>
                                    <div class="mt-1">
                                        {{ $trade->comment ?: '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

