@extends('layouts.crm.crm')
@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="row mb-4">
                @include('trading-view')
            </div>
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="card bg-gray-800 dropbox-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="text-white">WALLET </h5>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2 mt-2">
                                <div>
                                    <div class="avtar avtar-s"><svg class="pc-icon">
                                            <use xlink:href="#custom-security-safe"></use>
                                        </svg></div>
                                </div>
                                <?php if (session('user')->wallet_enabled == 0 || session('user')->wallet_enabled == NULL) { ?>
                                <div>
                                    <button class="btn btn-sm btn-outline-light bg-transparent activate-wallet"
                                        type="button"><i class="ti ti-plus me-2"></i><!---->
                                        Activate Wallet</button>
                                </div>
                            </div><a href="/wallet_deposit"><small class="text-white">FUND NOW</small></a>
                            <?php } else { ?>
                            <div>
                                <h2 class="text-center text-white">${{ $walletBalance }}</h2>
                            </div>
                        </div><a href="/wallet_deposit"><small class="text-white">FUND NOW</small></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="avtar avtar-s bg-light-primary"><i class="ti ti-database-import f-18"></i></div>
                            <div class="dropdown"><a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none"
                                    href="/dashboard" data-bs-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false"><i class="ti ti-dots-vertical f-18"></i></a>
                                <div class="dropdown-menu dropdown-menu-end"><a href="/trade-deposit"
                                        class="dropdown-item">Deposit Now
                                    </a><a href="/transactions" class="dropdown-item">View Transactions</a></div>
                            </div>
                        </div>
                        <h4 class="mb-1 f-w-400">${{ $totalDeposit }}</h4>
                        <p class="text-muted mb-0">Total Deposits</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
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
                        <h4 class="mb-1 f-w-400">${{ $totalWithdrawal }}</h4>
                        <p class="text-muted mb-0">Total Withdrawals</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3"><a href="/dashboard">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="avtar avtar-s bg-light-primary"><i class="ti ti-shield-check f-18"></i></div>
                                <div class="dropdown"><a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none"
                                        href="/dashboard" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false"><i class="ti ti-dots-vertical f-18"></i></a>
                                    <div class="dropdown-menu dropdown-menu-end"><a href="/liveAccounts"
                                            class="dropdown-item">View
                                            Accounts </a></div>
                                </div>
                            </div>
                            <h3 class="mb-0 f-w-400">{{ $liveAccounts }}</h3>
                            <p class="text-muted mb-0">Live MT5 Accounts</p>
                        </div>
                    </div>
                </a></div>
        </div>
        <div class="row">
            <div class="col-md-12 col-lg-9">
                <div class="card">
                    <div class="card-body border-bottom pb-0">
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
                        <ul class="nav nav-tabs analytics-tab" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation"><button class="nav-link active"
                                    id="analytics-tab-1" data-bs-toggle="tab" data-bs-target="#analytics-tab-1-pane"
                                    type="button" role="tab" aria-controls="analytics-tab-1-pane"
                                    aria-selected="true">Live Accounts</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" id="analytics-tab-2"
                                    data-bs-toggle="tab" data-bs-target="#analytics-tab-2-pane" type="button"
                                    role="tab" aria-controls="analytics-tab-2-pane" aria-selected="false">Demo
                                    Accounts</button></li>
                        </ul>
                    </div>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="analytics-tab-1-pane" role="tabpanel"
                            aria-labelledby="analytics-tab-1" tabindex="0">
                            @if ($liveAccountDetails->isNotEmpty())
                                <div>
                                    <div class="table-responsive ps-2">
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
                                                @foreach ($liveAccountDetails as $liveAccount)
                                                    <tr>
                                                        <td>
                                                            <div class="row align-items-center">
                                                                <div class="col-auto pe-0">
                                                                    <img src="/assets/images/mt5.png" alt="user-image"
                                                                        class="wid-50 hei-50 rounded">
                                                                </div>
                                                                <div class="col">
                                                                    <h4 class="mb-2 ms-2">
                                                                        <span
                                                                            class="text-truncate w-100">{{ $liveAccount->trade_id }}</span>
                                                                    </h4>
                                                                    <p class="text-muted ms-2 f-12 mb-0">
                                                                        <span
                                                                            class="text-truncate w-100">{{ $liveAccount->account_type }}</span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="f-w-400 f-16">{{ $liveAccount->leverage }}</td>
                                                        <td class="text-end f-w-400 f-16">$
                                                            {{ $liveAccount->balance ?? '0.00' }}</td>
                                                        <td class="text-end f-w-400 f-16">$ {{ $liveAccount->equity }}</td>
                                                        <td class="text-end f-w-200">
                                                            <div class="d-flex align-items-center">
                                                                <a href="{{ route('view-account-details', ['type' => 'live', 'id' => $liveAccount->trade_id]) }}"
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
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
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
                                                <h6 class="text-center text-secondary f-w-400 mb-0 f-16">No Live Accounts
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
                        <div class="tab-pane fade" id="analytics-tab-2-pane" role="tabpanel"
                            aria-labelledby="analytics-tab-2" tabindex="0">
                            @if ($demoAccountDetails->isNotEmpty())
                                <div>
                                    <div class="table-responsive ps-2">
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
                                                                    <img src="/assets/images/mt5.png" alt="user-image"
                                                                        class="wid-50 hei-50 rounded">
                                                                </div>
                                                                <div class="col">
                                                                    <h4 class="mb-2 ms-2">
                                                                        <span
                                                                            class="text-truncate w-100">{{ $demoAccount->trade_id }}</span>
                                                                    </h4>
                                                                    <p class="text-muted ms-2 f-12 mb-0">
                                                                        <span
                                                                            class="text-truncate w-100">{{ $demoAccount->account_type }}</span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="f-w-400 f-16">{{ $demoAccount->leverage }}</td>
                                                        <td class="text-end f-w-400 f-16">$
                                                            {{ $demoAccount->balance ?? '0.00' }}</td>
                                                        <td class="text-end f-w-400 f-16">$ {{ $demoAccount->equity }}
                                                        </td>
                                                        <td class="text-end f-w-200">
                                                            <div class="d-flex align-items-center">
                                                                <a href="{{ route('view-account-details', ['type' => 'demo', 'id' => $demoAccount->trade_id]) }}"
                                                                    class="btn btn-sm btn-outline-secondary d-grid me-2">
                                                                    <span>View <svg class="pc-icon">
                                                                            <use xlink:href="#custom-login"></use>
                                                                        </svg></span>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
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
                                                <h6 class="text-center text-secondary f-w-400 mb-0 f-16">No Demo Accounts
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
                    <div class="card-footer"></div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="/dashboard">
                    <div class="card">
                        <div class="card-body p-3"><a href="/trade-deposit"
                                class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="mb-0 text-white text-opacity-75"></p>
                                    <h4 class="mb-0 text-black">Quick Deposit</h4>
                                </div>
                                <div class="avtar bg-light-primary"><i class="ti ti-bolt f-18"></i></div>
                            </a></div>
                    </div>
                </a><a href="/dashboard">
                    <div class="card">
                        <div class="card-body p-3"><a href="/trade-withdrawal"
                                class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="mb-0 text-white text-opacity-75"></p>
                                    <h4 class="mb-0 text-black">Quick Withdraw</h4>
                                </div>
                                <div class="avtar bg-light-primary"><i class="ti ti-bolt f-18"></i></div>
                            </a></div>
                    </div>
                </a>
                @php
                    $ib = $ibResult ? '/ib-profile' : '/ib';
                @endphp
                <a href="{{ $ib }}" class="">
                    <div class="card bg-primary available-balance-card">
                        <div class="card-body p-3">
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
