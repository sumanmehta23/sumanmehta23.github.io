@extends('layouts.crm.crm')
@section('styles')
    <style>
        @media (max-width: 767.98px) {
            .mobile-list {
                padding: 0.75rem;
            }

            .mobile-card {
                padding: 1.25rem;
                border: 1px solid rgba(var(--bs-primary-rgb), 0.16);
                border-radius: 1.75rem;
                background: linear-gradient(180deg, rgba(var(--bs-primary-rgb), 0.07) 0%, #ffffff 26%);
                box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            }

            .mobile-card__head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
            }

            .mobile-card__identity {
                display: flex;
                align-items: center;
                gap: 0.875rem;
                min-width: 0;
                flex: 1;
            }

            .mobile-card__icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 3.6rem;
                height: 3.6rem;
                border-radius: 1.1rem;
                background: rgba(var(--bs-primary-rgb), 0.1);
                border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
                flex-shrink: 0;
            }

            .mobile-card__icon img {
                width: 2rem;
                height: 2rem;
                object-fit: contain;
            }

            .mobile-card__title {
                margin: 0;
                font-size: 1.55rem;
                font-weight: 700;
                line-height: 1.1;
                color: #163432;
                word-break: break-word;
            }

            .mobile-card__content {
                min-width: 0;
            }

            .mobile-card__badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-top: 0.45rem;
                padding: 0.28rem 0.75rem;
                border-radius: 999px;
                background: rgba(var(--bs-primary-rgb), 0.1);
                border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
                color: var(--bs-primary);
                font-size: 0.8rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .mobile-card__leverage-wrap {
                min-width: fit-content;
                text-align: right;
            }

            .mobile-card__leverage-label {
                display: block;
                color: rgba(22, 52, 50, 0.62);
                font-size: 0.82rem;
                font-weight: 600;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .mobile-card__leverage-value {
                display: block;
                margin-top: 0.2rem;
                color: #0d6f66;
                font-size: 1.7rem;
                font-weight: 800;
                line-height: 1;
            }

            .mobile-card__divider {
                margin: 1.15rem 0;
                border: 0;
                border-top: 1px solid rgba(var(--bs-primary-rgb), 0.16);
                opacity: 1;
            }

            .mobile-card__meta {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                margin-bottom: 1rem;
            }

            .mobile-card__meta-label {
                color: rgba(22, 52, 50, 0.68);
                font-size: 0.92rem;
                font-weight: 500;
            }

            .mobile-card__meta-value {
                color: #163432;
                font-size: 0.95rem;
                font-weight: 600;
                text-align: right;
                word-break: break-word;
            }

            .mobile-card__stats {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
            }

            .mobile-card__stat {
                padding: 0.8rem 0.9rem;
                border-radius: 1rem;
                background: rgba(var(--bs-primary-rgb), 0.08);
                border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
            }

            .mobile-card__stat-label {
                display: block;
                margin-bottom: 0.35rem;
                color: rgba(22, 52, 50, 0.55);
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .mobile-card__stat-value {
                margin: 0;
                color: #163432;
                font-size: 1.35rem;
                font-weight: 800;
                line-height: 1.15;
            }

            .mobile-card__actions {
                display: grid;
                gap: 0.75rem;
                margin-top: 1rem;
            }

            .mobile-card__btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 3.2rem;
                padding: 0.85rem 0.6rem;
                border-radius: 0.95rem;
                border: 1px solid rgba(var(--bs-primary-rgb), 0.28);
                background: #ffffff;
                color: rgba(22, 52, 50, 0.84);
                font-size: 1rem;
                font-weight: 700;
                line-height: 1;
                text-align: center;
                text-decoration: none;
                transition: all 0.2s ease;
            }

            .mobile-card__btn--primary {
                background: var(--bs-primary);
                border-color: var(--bs-primary);
                color: #ffffff;
                box-shadow: 0 8px 18px rgba(var(--bs-primary-rgb), 0.22);
            }

            .mobile-card__notice {
                margin-top: 1rem;
                padding: 0.9rem 1rem;
                border-radius: 1rem;
                font-size: 0.92rem;
                font-weight: 500;
                line-height: 1.45;
            }

            .mobile-card__notice--danger {
                background: rgba(220, 53, 69, 0.08);
                border: 1px solid rgba(220, 53, 69, 0.16);
                color: #b42318;
            }

            .mobile-card__notice--warning {
                background: rgba(255, 193, 7, 0.12);
                border: 1px solid rgba(255, 193, 7, 0.26);
                color: #8a6116;
            }
        }
    </style>
@endsection
@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="mb-4 row">
                @include('trading-view')
            </div>
            <div class="row">
                {{-- <div class="col-md-6 col-lg-3">
                    <div class="bg-gray-800 card dropbox-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="text-white">WALLET </h5>
                            </div>
                            <div class="mt-2 mb-2 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="avtar avtar-s"><svg class="pc-icon">
                                            <use xlink:href="#custom-security-safe"></use>
                                        </svg></div>
                                </div>
                                <div>
                                    <h2 class="text-center text-white">@money($walletBalance)</h2>
                                </div>
                            </div><a href="/wallet_deposit"><small class="text-white">FUND NOW</small></a>

                        </div>
                    </div>
                </div> --}}
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 d-flex align-items-center justify-content-between">
                                <div class="avtar avtar-s bg-light-primary"><i class="ti ti-database-import f-18"></i></div>
                                <div class="dropdown"><a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none"
                                        href="/dashboard" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false"><i class="ti ti-dots-vertical f-18"></i></a>
                                    <div class="dropdown-menu dropdown-menu-end"><a href="/trade-deposit"
                                            class="dropdown-item">Deposit Now
                                        </a><a href="/transactions" class="dropdown-item">View Transactions</a></div>
                                </div>
                            </div>
                            <h4 class="mb-1 f-w-400">@money($totalDeposit)</h4>
                            <p class="mb-0 text-muted">Total Deposits</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 d-flex align-items-center justify-content-between">
                                <div class="avtar avtar-s bg-light-primary"><i class="ti ti-database-export f-18"></i></div>
                                <div class="dropdown"><a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none"
                                        href="/dashboard" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false"><i class="ti ti-dots-vertical f-18"></i></a>
                                    <div class="dropdown-menu dropdown-menu-end"><a href="/trade-withdrawal"
                                            class="dropdown-item">Withdraw
                                            Now </a><a href="/transactions" class="dropdown-item">View
                                            Transactions</a></div>
                                </div>
                            </div>
                            <h4 class="mb-1 f-w-400">@money($totalWithdrawal)</h4>
                            <p class="mb-0 text-muted">Total Withdrawals</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 d-flex align-items-center justify-content-between">
                                <div class="avtar avtar-s bg-light-primary"><i class="ti ti-shield-check f-18"></i></div>
                                <div class="dropdown">
                                    <a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="/dashboard" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="ti ti-dots-vertical f-18"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="/liveAccounts" class="dropdown-item">View Accounts </a>
                                    </div>

                                </div>

                            </div>
                            <h3 class="mb-0 f-w-400">{{ $liveAccounts }}</h3>
                            <p class="mb-0 text-muted">Live MT5 Accounts</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pb-0 mb-0 page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title h2">
                        <h4 class="mb-0">Dashboard</h4>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-lg-9">
                    <div class="card">
                        <div class="pb-0 card-body border-bottom">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">My Trading Accounts</h5>
                                <div class="dropdown"><a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none"
                                        href="/dashboard" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false"><i class="ti ti-dots-vertical f-18"></i></a>
                                    <div class="dropdown-menu dropdown-menu-end"><a href="/createLiveAccount"
                                            class="dropdown-item">Open
                                            Live
                                            Account</a><a class="dropdown-item" href="/createDemoAccount">Open Demo
                                            Account</a></div>
                                </div>
                            </div>
                            @php
                                $activeDashboardTab = request('tab') === 'demo' ? 'demo' : 'live';
                            @endphp
                            <ul class="nav nav-tabs analytics-tab" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation"><button class="nav-link {{ $activeDashboardTab === 'live' ? 'active' : '' }}"
                                        id="analytics-tab-1" data-bs-toggle="tab" data-bs-target="#analytics-tab-1-pane"
                                        type="button" role="tab" aria-controls="analytics-tab-1-pane"
                                        aria-selected="{{ $activeDashboardTab === 'live' ? 'true' : 'false' }}">Live Accounts</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link {{ $activeDashboardTab === 'demo' ? 'active' : '' }}" id="analytics-tab-2"
                                        data-bs-toggle="tab" data-bs-target="#analytics-tab-2-pane" type="button"
                                        role="tab" aria-controls="analytics-tab-2-pane" aria-selected="{{ $activeDashboardTab === 'demo' ? 'true' : 'false' }}">Demo
                                        Accounts</button></li>
                            </ul>
                        </div>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade {{ $activeDashboardTab === 'live' ? 'show active' : '' }}" id="analytics-tab-1-pane" role="tabpanel"
                                aria-labelledby="analytics-tab-1" tabindex="0">
                                @if ($liveAccountDetails->count() > 0)
                                    <div>
                                        <div class="d-block d-md-none mobile-list">
                                            @foreach ($liveAccountDetails as $liveAccount)
                                                <div class="mb-3 mobile-card">
                                                    <div class="mobile-card__head">
                                                        <div class="mobile-card__identity">
                                                            <div class="mobile-card__icon">
                                                                @if($liveAccount->platform === 'x9')
                                                                    <img src="/images/x92.png" alt="X9 Platform">
                                                                @else
                                                                    <img src="/assets/images/mt5.png" alt="MT5 Platform">
                                                                @endif
                                                            </div>
                                                            <div class="mobile-card__content">
                                                                @if ($liveAccount->code && $liveAccount->code != 'Rejected')
                                                                    <h5 class="mobile-card__title">{{ $liveAccount->code }}</h5>
                                                                @elseif($liveAccount->code == 'Rejected')
                                                                    <h5 class="mobile-card__title text-danger">Rejected</h5>
                                                                @else
                                                                    <h5 class="mobile-card__title text-warning">Pending</h5>
                                                                @endif
                                                                <span class="mobile-card__badge">{{ $liveAccount->accountType->ac_name }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="mobile-card__leverage-wrap">
                                                            <span class="mobile-card__leverage-label">Leverage</span>
                                                            <strong class="mobile-card__leverage-value">1:{{ $liveAccount->leverage }}</strong>
                                                        </div>
                                                    </div>

                                                    <hr class="mobile-card__divider">

                                                    <div class="mobile-card__meta">
                                                        <span class="mobile-card__meta-label">Nickname</span>
                                                        <span class="mobile-card__meta-value">
                                                            {{ $liveAccount->account_nick_name ?: '—' }}
                                                        </span>
                                                    </div>

                                                    <div class="mobile-card__stats">
                                                        <div class="mobile-card__stat">
                                                            <span class="mobile-card__stat-label">Balance</span>
                                                            <p class="mobile-card__stat-value">${{ number_format((float) ($liveAccount->balance ?? 0), 2) }}</p>
                                                        </div>
                                                        <div class="mobile-card__stat">
                                                            <span class="mobile-card__stat-label">Equity</span>
                                                            <p class="mobile-card__stat-value">${{ number_format((float) ($liveAccount->equity ?? 0), 2) }}</p>
                                                        </div>
                                                    </div>

                                                    @if ($liveAccount->code && $liveAccount->code != 'Rejected')
                                                        <div class="mobile-card__actions"
                                                            style="grid-template-columns: repeat({{ $liveAccount->isZapierAccount() ? 2 : 3 }}, minmax(0, 1fr));">
                                                            <a href="{{ route('view-account-details', $liveAccount->id) }}"
                                                                class="mobile-card__btn">
                                                                View
                                                            </a>
                                                            @if(!$liveAccount->isZapierAccount())
                                                                <a href="{{ url('/trade-deposit') }}"
                                                                    class="mobile-card__btn mobile-card__btn--primary">
                                                                    Deposit
                                                                </a>
                                                            @endif
                                                            <a href="{{ route('trade-withdrawal', ['account_id' => $liveAccount->id]) }}"
                                                                class="mobile-card__btn">
                                                                Withdraw
                                                            </a>
                                                        </div>
                                                    @elseif ($liveAccount->code && $liveAccount->code == 'Rejected')
                                                        <div class="mobile-card__notice mobile-card__notice--danger">
                                                            Your request is rejected. Create your account again.
                                                        </div>
                                                    @else
                                                        <div class="mobile-card__notice mobile-card__notice--warning">
                                                            Once your request is approved you will receive an email with your new account information.
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="table-responsive ps-2 d-none d-md-block">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>Nick Name</th>
                                                        <th>Leverage</th>
                                                        <th class="text-end">Balance</th>
                                                        <th class="text-end">Equity</th>
                                                        <th class="text-end"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($liveAccountDetails as $liveAccount)
                                                        <tr>
                                                            <td>
                                                                <div class="row align-items-center">
                                                                    <div class="col-auto pe-0">
                                                                        {{-- <img src="/assets/images/mt5.png" alt="user-image"
                                                                            class="rounded wid-50 hei-50"> --}}
                                                                        @if($liveAccount->platform === 'x9')
                                                                            <img src="/images/x92.png" alt="X9 Platform"
                                                                                class="rounded wid-50 hei-50">
                                                                        @else
                                                                            <img src="/assets/images/mt5.png" alt="MT5 Platform"
                                                                                class="rounded wid-50 hei-50">
                                                                        @endif
                                                                    </div>
                                                                    <div class="col">
                                                                        {{-- <h4 class="mb-2 ms-2">
                                                                            <span
                                                                                class="text-truncate w-100">{{ $liveAccount->code ?? 'Pending' }}</span>
                                                                        </h4> --}}
                                                                        @if ($liveAccount->code && $liveAccount->code != 'Rejected')
                                                                            <h4 class="mb-2 ms-2">
                                                                                {{ $liveAccount->code }}
                                                                            </h4>
                                                                        @elseif($liveAccount->code == 'Rejected')
                                                                            <h4 class="mb-2 ms-2 text-danger">
                                                                                {{ 'Rejected' }}
                                                                            </h4>
                                                                        @else
                                                                            <h4 class="mb-2 ms-2 text-warning">
                                                                                {{ 'Pending' }}
                                                                            </h4>
                                                                        @endif
                                                                        <h6 class="mb-0 text-muted ms-2 f-12">
                                                                            <span
                                                                                class="text-truncate w-100">{{ $liveAccount->accountType->ac_name }}
                                                                            </span>
                                                                        </h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="f-w-400 f-16">{{ $liveAccount->account_nick_name }}</td>
                                                            <td class="f-w-400 f-16">{{ $liveAccount->leverage }}</td>
                                                            <td class="text-end f-w-400 f-16">@money($liveAccount->balance ?? '0.00')</td>
                                                            <td class="text-end f-w-400 f-16">@money($liveAccount->equity)</td>

                                                            {{-- <td class="text-end f-w-200">
                                                                @if ($liveAccount->code && $liveAccount->code != 'Rejected' )
                                                                    <div class="d-flex align-items-center">
                                                                        <a href="{{ route('view-account-details', $liveAccount->id) }}"
                                                                            class="btn btn-sm btn-outline-secondary d-grid me-2">
                                                                            <span>View <svg class="pc-icon">
                                                                                    <use xlink:href="#custom-login"></use>
                                                                                </svg></span>
                                                                        </a>
                                                                        <a href="{{ route('trade-deposit') }}"
                                                                            class="btn btn-sm btn-outline-secondary d-grid">
                                                                            <span>Deposit <i
                                                                                    class="ti ti-database-import"></i></span>
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </td> --}}
                                                            <td class="text-end f-w-200">
                                                                @if ($liveAccount->code && $liveAccount->code != 'Rejected')
                                                                    <div class="gap-2 d-flex align-items-center">
                                                                        <button class="btn btn-sm btn-outline-secondary d-grid me-2">
                                                                            <a href="{{ route('view-account-details', $liveAccount->id) }}">
                                                                            <span class="">View <svg class="pc-icon">
                                                                                <use xlink:href="#custom-login"></use>
                                                                                </svg></span>
                                                                            </a>
                                                                        </button>
                                                                        @if(!$liveAccount->isZapierAccount())
                                                                        <a href="{{ url('/trade-deposit') }}" class="btn btn-sm btn-outline-secondary d-grid">
                                                                            <span class="">Deposit <i class="ti ti-database-import"></i></span>
                                                                        </a>
                                                                        @endif
                                                                        <a href="{{ route('trade-withdrawal') }}" class="btn btn-sm btn-outline-secondary d-grid">
                                                                            <span class="">Withdraw <i class="ti ti-database-import"></i></span>
                                                                        </a>
                                                                        {{-- <a href="#"
                                                                            class="btn btn-sm btn-outline-secondary d-grid"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#changeLeverage"
                                                                            data-id="{{ $liveAccount->account_type_id }}"
                                                                            data-account="{{ $liveAccount->id }}"
                                                                            data-leverage="{{ $liveAccount->leverage }}">
                                                                            Edit Leverage
                                                                        </a> --}}
                                                                    </div>
                                                                @elseif ($liveAccount->code && $liveAccount->code == 'Rejected')
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="text-danger">Your request is rejected. Create your account again.</span>
                                                                    </div>
                                                                @else
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="text-warning">Once your request is approved you will receive an email with your new account information.</span>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="px-3 pb-3">
                                            {{ $liveAccountDetails->withQueryString()->appends(['tab' => 'live'])->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @else
                                    <div>
                                        <div class="p-5 m-3">
                                            <div class="auth-main">
                                                <div class="card-body">
                                                    <div class="text-center me-4">
                                                        <a href="/dashboard"><img src="/assets/images/empty.png"
                                                                class="w-25" alt="img"></a>
                                                    </div>
                                                    <h6 class="mb-0 text-center text-secondary f-w-400 f-16">No Live Accounts
                                                        Found</h6>
                                                </div>
                                            </div>
                                            <a href="/createLiveAccount" class="d-grid">
                                                <button class="btn btn-outline-primary">
                                                    <span class="text-truncate w-100">Create New Live Account</span>
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="tab-pane fade {{ $activeDashboardTab === 'demo' ? 'show active' : '' }}" id="analytics-tab-2-pane" role="tabpanel"
                                aria-labelledby="analytics-tab-2" tabindex="0">
                                @if ($demoAccountDetails->count() > 0)
                                    <div>
                                        <div class="d-block d-md-none mobile-list">
                                            @foreach ($demoAccountDetails as $demoAccount)
                                                <div class="mb-3 mobile-card">
                                                    <div class="mobile-card__head">
                                                        <div class="mobile-card__identity">
                                                            <div class="mobile-card__icon">
                                                                @if($demoAccount->platform === 'x9')
                                                                    <img src="/images/x92.png" alt="X9 Platform">
                                                                @else
                                                                    <img src="/assets/images/mt5.png" alt="MT5 Platform">
                                                                @endif
                                                            </div>
                                                            <div class="mobile-card__content">
                                                                @if ($demoAccount->code && $demoAccount->code != 'Rejected')
                                                                    <h5 class="mobile-card__title">{{ $demoAccount->code }}</h5>
                                                                @elseif($demoAccount->code == 'Rejected')
                                                                    <h5 class="mobile-card__title text-danger">Rejected</h5>
                                                                @else
                                                                    <h5 class="mobile-card__title text-warning">Pending</h5>
                                                                @endif
                                                                <span class="mobile-card__badge">
                                                                    {{ $demoAccount->accountType ? $demoAccount->accountType->ac_name : '' }}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div class="mobile-card__leverage-wrap">
                                                            <span class="mobile-card__leverage-label">Leverage</span>
                                                            <strong class="mobile-card__leverage-value">1:{{ $demoAccount->leverage }}</strong>
                                                        </div>
                                                    </div>

                                                    <hr class="mobile-card__divider">

                                                    <div class="mobile-card__meta">
                                                        <span class="mobile-card__meta-label">Nickname</span>
                                                        <span class="mobile-card__meta-value">
                                                            {{ $demoAccount->account_nick_name ?: '—' }}
                                                        </span>
                                                    </div>

                                                    <div class="mobile-card__stats">
                                                        <div class="mobile-card__stat">
                                                            <span class="mobile-card__stat-label">Balance</span>
                                                            <p class="mobile-card__stat-value">${{ number_format((float) ($demoAccount->balance ?? 0), 2) }}</p>
                                                        </div>
                                                        <div class="mobile-card__stat">
                                                            <span class="mobile-card__stat-label">Equity</span>
                                                            <p class="mobile-card__stat-value">${{ number_format((float) ($demoAccount->equity ?? 0), 2) }}</p>
                                                        </div>
                                                    </div>

                                                    @if ($demoAccount->code && $demoAccount->code != 'Rejected')
                                                        <div class="mobile-card__actions">
                                                            <a href="{{ route('view-account-details', $demoAccount->id) }}"
                                                                class="mobile-card__btn">
                                                                View
                                                            </a>
                                                        </div>
                                                    @elseif ($demoAccount->code && $demoAccount->code == 'Rejected')
                                                        <div class="mobile-card__notice mobile-card__notice--danger">
                                                            Your request is rejected. Create your account again.
                                                        </div>
                                                    @else
                                                        <div class="mobile-card__notice mobile-card__notice--warning">
                                                            Once your request is approved you will receive an email with your new account information.
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="table-responsive ps-2 d-none d-md-block">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>Leverage</th>
                                                        <th class="text-end">Balance</th>
                                                        <th class="text-end">Equity</th>
                                                        <th class="text-end"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($demoAccountDetails as $demoAccount)
                                                        <tr>
                                                            <td>
                                                                <div class="row align-items-center">
                                                                    <div class="col-auto pe-0">
                                                                        @if($demoAccount->platform === 'x9')
                                                                            <img src="/images/x92.png" alt="X9 Platform"
                                                                                class="rounded wid-50 hei-50">
                                                                        @else
                                                                            <img src="/assets/images/mt5.png" alt="MT5 Platform"
                                                                                class="rounded wid-50 hei-50">
                                                                        @endif
                                                                    </div>
                                                                    <div class="col">
                                                                        <h4 class="mb-2 ms-2">
                                                                            <span
                                                                                class="text-truncate w-100">{{ $demoAccount->code ?? 'Pending' }}</span>
                                                                        </h4>
                                                                        <p class="mb-0 text-muted ms-2 f-12">
                                                                            <span
                                                                                class="text-truncate w-100">{{ $demoAccount->account_type }}</span>
                                                                        </p>
                                                                        <h6 class="pl-2 mb-0 text-muted ms-2 f-12">
                                                                            <span class="text-truncate w-100">
                                                                                {{ $demoAccount->accountType ?$demoAccount->accountType->ac_name : '' }}
                                                                            </span>
                                                                        </h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="f-w-400 f-16">{{ $demoAccount->leverage }}</td>
                                                            <td class="text-end f-w-400 f-16">@money($demoAccount->balance ?? '0.00')</td>
                                                            <td class="text-end f-w-400 f-16">@money($demoAccount->equity)</td>
                                                            @if ($demoAccount->code && $demoAccount->code != 'Rejected')
                                                                <td class="text-end f-w-200">
                                                                    <div class="d-flex align-items-center">
                                                                        <a href="{{ route('view-account-details', $demoAccount->id) }}"
                                                                            class="btn btn-sm btn-outline-secondary d-grid me-2">
                                                                            <span>View <svg class="pc-icon">
                                                                                    <use xlink:href="#custom-login"></use>
                                                                                </svg></span>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="px-3 pb-3">
                                            {{ $demoAccountDetails->withQueryString()->appends(['tab' => 'demo'])->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @else
                                    <div>
                                        <div class="p-5 m-3">
                                            <div class="auth-main">
                                                <div class="card-body">
                                                    <div class="text-center me-4">
                                                        <a href="{{ route('dashboard') }}"><img
                                                                src="/assets/images/empty.png" class="w-25"
                                                                alt="img"></a>
                                                    </div>
                                                    <h6 class="mb-0 text-center text-secondary f-w-400 f-16">No Demo Accounts
                                                        Found</h6>
                                                </div>
                                            </div>
                                            <a href="{{ route('show-demo-account-form') }}" class="d-grid">
                                                <button class="btn btn-outline-primary">
                                                    <span class="text-truncate w-100">Create New Demo Account</span>
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        {{-- <div class="card-footer"></div> --}}
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <div class="p-3 card-body"><a href="/trade-deposit"
                                class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="mb-0 text-white text-opacity-75"></p>
                                    <h4 class="mb-0 text-black">Quick Deposit</h4>
                                </div>
                                <div class="avtar bg-light-primary"><i class="ti ti-bolt f-18"></i></div>
                            </a></div>
                    </div>
                    <div class="card">
                        <div class="p-3 card-body"><a href="/trade-withdrawal"
                                class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="mb-0 text-white text-opacity-75"></p>
                                    <h4 class="mb-0 text-black">Quick Withdraw</h4>
                                </div>
                                <div class="avtar bg-light-primary"><i class="ti ti-bolt f-18"></i></div>
                            </a></div>
                    </div>
                    @php
                        $ib = $ibResult ? '/ib-profile' : '/ib';
                    @endphp
                    <a href="{{ $ib }}" class="">
                        <div class="card bg-primary available-balance-card">
                            <div class="p-3 card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="mb-0 text-white">Introducing Broker</h4>
                                        <p class="mb-0 text-white text-opacity-75">View Profile</p>
                                    </div>
                                    <div class="avatar">
                                        <i class="ti ti-award f-18"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
@endsection
