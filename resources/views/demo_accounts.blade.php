@extends('layouts.crm.crm')
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
                                <div class="d-block d-md-none p-2">
                                    @foreach ($results as $acc)
                                        <div class="mb-3 border rounded p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    @if($acc->platform === 'x9')
                                                        <img src="/images/x92.png" alt="X9 Platform"
                                                            class="rounded wid-50 hei-50">
                                                    @else
                                                        <img src="/assets/images/mt5.png" alt="MT5 Platform"
                                                            class="rounded wid-50 hei-50">
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    @if ($acc->code && $acc->code != 'Rejected')
                                                        <h5 class="mb-1">{{ $acc->code }}</h5>
                                                    @elseif($acc->code == 'Rejected')
                                                        <h5 class="mb-1 text-danger">Rejected</h5>
                                                    @else
                                                        <h5 class="mb-1 text-warning">Pending</h5>
                                                    @endif
                                                    @if ($acc->demo == 0)
                                                        <p class="mb-0 text-muted f-12">
                                                            {{ $acc->email }}
                                                        </p>
                                                    @endif
                                                    <p class="mb-0 text-muted f-12">
                                                        {{ $acc->accountType->ac_name }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <div class="mb-2 d-flex align-items-center justify-content-between">
                                                    <p class="mb-0 text-muted f-12">Leverage</p>
                                                    <p class="mb-0 f-w-400">1:{{ $acc->leverage }}</p>
                                                </div>
                                                <div class="mb-2 d-flex align-items-center justify-content-between">
                                                    <p class="mb-0 text-muted f-12">Balance</p>
                                                    <p class="mb-0 f-w-400">${{ number_format($acc->balance, 2) }}</p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <p class="mb-0 text-muted f-12">Equity</p>
                                                    <p class="mb-0 f-w-400">${{ number_format($acc->equity, 2) }}</p>
                                                </div>
                                            </div>

                                            @if ($acc->code && $acc->code != 'Rejected')
                                                <div class="gap-2 mt-3 d-flex flex-wrap justify-content-center">
                                                    <a href="{{ route('view-account-details', $acc->id) }}"
                                                        class="btn btn-sm btn-outline-secondary">
                                                        <span>View <svg class="pc-icon">
                                                                <use xlink:href="#custom-login"></use>
                                                            </svg></span>
                                                    </a>
                                                    @if($acc->demo == 0)
                                                        <a href="{{ url('/trade-deposit') }}"
                                                            class="btn btn-sm btn-outline-secondary">
                                                            <span>Deposit <i class="ti ti-database-import"></i></span>
                                                        </a>
                                                    @endif
                                                </div>
                                            @elseif ($acc->code && $acc->code == 'Rejected')
                                                <div class="mt-3">
                                                    <span class="text-danger">Your request is rejected. Create your account again.</span>
                                                </div>
                                            @else
                                                <div class="mt-3">
                                                    <span class="text-warning">Once your request is approved you will receive an email with your new account information.</span>
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
                                                    <td class="text-end f-w-400 f-16">${{ number_format($acc->balance, 2) }}
                                                    </td>
                                                    <td class="text-end f-w-400 f-16">${{ number_format($acc->equity, 2) }}
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
