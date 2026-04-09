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
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="pb-0 mb-0 page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title h2">
                                <h4 class="mb-0">Trading Demo Accounts</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                @include('mt5_accounts_tab') <!-- Adjust the path according to your structure -->
                <div class="col-md-12 col-lg-9">
                    <div class="card">
                        <div class="pb-0 card-body border-bottom">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">My Trading Demo Accounts</h5>
                                <div class="dropdown">
                                    <a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none"
                                        href="/DemoAccounts#" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false"><i class="ti ti-dots-vertical f-18"></i></a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="/createDemoAccount">Open New Account</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-content" id="myTabContent">
                            <div>
                                <div class="d-block d-md-none mobile-list">
                                    @foreach ($results as $acc)
                                        <div class="mb-3 mobile-card">
                                            <div class="mobile-card__head">
                                                <div class="mobile-card__identity">
                                                    <div class="mobile-card__icon">
                                                        @if($acc->platform === 'x9')
                                                            <img src="/images/x92.png" alt="X9 Platform"
                                                                class="rounded">
                                                        @else
                                                            <img src="/assets/images/mt5.png" alt="MT5 Platform"
                                                                class="rounded">
                                                        @endif
                                                    </div>
                                                    <div class="mobile-card__content">
                                                        @if ($acc->code && $acc->code != 'Rejected')
                                                            <h5 class="mobile-card__title">{{ $acc->code }}</h5>
                                                        @elseif($acc->code == 'Rejected')
                                                            <h5 class="mobile-card__title text-danger">Rejected</h5>
                                                        @else
                                                            <h5 class="mobile-card__title text-warning">Pending</h5>
                                                        @endif
                                                        <span class="mobile-card__badge">
                                                            {{ $acc->accountType->ac_name }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="mobile-card__leverage-wrap">
                                                    <span class="mobile-card__leverage-label">Leverage</span>
                                                    <strong class="mobile-card__leverage-value">1:{{ $acc->leverage }}</strong>
                                                </div>
                                            </div>

                                            <hr class="mobile-card__divider">

                                            @if ($acc->demo == 0)
                                                <div class="mobile-card__meta">
                                                    <span class="mobile-card__meta-label">Email</span>
                                                    <span class="mobile-card__meta-value">{{ $acc->email }}</span>
                                                </div>
                                            @endif

                                            <div class="mobile-card__stats">
                                                <div class="mobile-card__stat">
                                                    <span class="mobile-card__stat-label">Balance</span>
                                                    <p class="mobile-card__stat-value">$ {{ number_format((float) $acc->balance, 2) }}</p>
                                                </div>
                                                <div class="mobile-card__stat">
                                                    <span class="mobile-card__stat-label">Equity</span>
                                                    <p class="mobile-card__stat-value">$ {{ number_format((float) $acc->equity, 2) }}</p>
                                                </div>
                                            </div>

                                            @if ($acc->code && $acc->code != 'Rejected')
                                                <div class="mobile-card__actions"
                                                    style="grid-template-columns: repeat({{ $acc->demo == 0 ? 2 : 1 }}, minmax(0, 1fr));">
                                                    <a href="{{ route('view-account-details', $acc->id) }}"
                                                        class="mobile-card__btn">
                                                        View
                                                    </a>
                                                    @if($acc->demo == 0)
                                                        <a href="{{ url('/trade-deposit') }}"
                                                            class="mobile-card__btn mobile-card__btn--primary">Deposit</a>
                                                    @endif
                                                </div>
                                            @elseif ($acc->code && $acc->code == 'Rejected')
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
                                                {{-- <th>Platform</th> --}}
                                                <th></th>
                                                <th>Leverage</th>
                                                <th class="text-end">Balance</th>
                                                <th class="text-end">Equity</th>
                                                <th class="text-end"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($results as $acc)
                                                <tr>
                                                    {{-- <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($acc->platform === 'x9')
                                                            <img src="/images/x92.png" alt="X9 Platform" class="rounded me-2"
                                                                style="width: 32px; height: 32px;">
                                                            <span class="badge bg-danger">X9</span>
                                                            @else
                                                            <img src="/images/mt5-icon.svg" alt="MT5 Platform"
                                                                class="rounded me-2" style="width: 32px; height: 32px;">
                                                            <span class="badge bg-primary">MT5</span>
                                                            @endif
                                                        </div>
                                                    </td> --}}
                                                    <td>
                                                        <div class="row align-items-center">
                                                            <div class="col-auto pe-0">
                                                                @if($acc->platform === 'x9')
                                                                    <img src="/images/x92.png" alt="X9 Platform"
                                                                        class="rounded wid-50 hei-50">
                                                                @else
                                                                    <img src="/assets/images/mt5.png" alt="MT5 Platform"
                                                                        class="rounded wid-50 hei-50">
                                                                @endif
                                                            </div>
                                                            <div class="col">
                                                                @if ($acc->code && $acc->code != 'Rejected')
                                                                    <h4 class="mb-2 ms-2">
                                                                        {{ $acc->code }}
                                                                    </h4>
                                                                @elseif($acc->code == 'Rejected')
                                                                    <h4 class="mb-2 ms-2 text-danger">
                                                                        {{ 'Rejected' }}
                                                                    </h4>
                                                                @else
                                                                    <h4 class="mb-2 ms-2 text-warning">
                                                                        {{ 'Pending' }}
                                                                    </h4>
                                                                @endif
                                                                @if ($acc->demo == 0)
                                                                    <p class="mb-0 text-muted ms-2 f-12">
                                                                        <span class="text-truncate w-100">
                                                                            {{ $acc->email }}
                                                                        </span>
                                                                    </p>
                                                                @endif
                                                                <h6 class="mb-0 text-muted ms-2 f-12">
                                                                    <span class="text-truncate w-100">
                                                                        {{ $acc->accountType->ac_name }}
                                                                    </span>
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="f-w-400 f-16">1:{{ $acc->leverage }}</td>
                                                    <td class="text-end f-w-400 f-16">$ {{ number_format($acc->balance, 2) }}
                                                    </td>
                                                    <td class="text-end f-w-400 f-16">$ {{ number_format($acc->equity, 2) }}
                                                    </td>
                                                    <td class="text-end f-w-200">
                                                        <div class="d-flex align-items-center">
                                                            {{-- @if($acc->code != null)
                                                            <a href="{{ url('/view-account-details/' . $acc->id) }}"
                                                                class="btn btn-sm btn-outline-secondary d-grid me-2">
                                                                <span>View <svg class="pc-icon">
                                                                        <use xlink:href="#custom-login"></use>
                                                                    </svg></span>
                                                            </a>
                                                            @endif --}}
                                                            @if ($acc->code && $acc->code != 'Rejected')
                                                                <div class="gap-2 d-flex align-items-center">
                                                                    <button class="btn btn-sm btn-outline-secondary d-grid me-2">
                                                                        <a href="{{ route('view-account-details', $acc->id) }}">
                                                                            <span class="">View <svg class="pc-icon">
                                                                                    <use xlink:href="#custom-login"></use>
                                                                                </svg></span>
                                                                        </a>
                                                                    </button>
                                                                    @if($acc->demo == 0)
                                                                        <a href="{{ url('/trade-deposit') }}"
                                                                            class="btn btn-sm btn-outline-secondary d-grid">
                                                                            <span class="">Deposit <i
                                                                                    class="ti ti-database-import"></i></span>
                                                                        </a>
                                                                    @endif
                                                                    {{-- <a href="#" class="btn btn-sm btn-outline-secondary d-grid"
                                                                        data-bs-toggle="modal" data-bs-target="#changeLeverage"
                                                                        data-id="{{ $acc->account_type_id }}"
                                                                        data-account="{{ $acc->id }}"
                                                                        data-leverage="{{ $acc->leverage }}">
                                                                        Edit Leverage
                                                                    </a> --}}
                                                                </div>
                                                            @elseif ($acc->code && $acc->code == 'Rejected')
                                                                <div class="d-flex align-items-center">
                                                                    <span class="text-danger">Your request is rejected. Create your
                                                                        account again.</span>
                                                                </div>
                                                            @else
                                                                <div class="d-flex align-items-center">
                                                                    <span class="text-warning">Once your request is approved you
                                                                        will receive an email with your new account
                                                                        information.</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="px-3 pb-3">
                                    {{ $results->withQueryString()->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <a href="/createDemoAccount">
                        <div class="card bg-primary available-balance-card">
                            <div class="p-3 card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="mb-0 text-white">Create Account</h4>
                                        <p class="mb-0 text-white text-opacity-75">Open Demo Account</p>
                                    </div>
                                    <div class="avtar"><i class="ti ti-folder-plus f-20"></i></div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <a href="/liveAccounts#">
                        <div class="card">
                            <div class="p-3 card-body">
                                <a href="user-profile" class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="mb-0 text-black">My Profile</h4>
                                    </div>
                                    <div class="avtar bg-success-subtle"><i class="ti ti-user f-18"></i></div>
                                </a>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>


@endsection
