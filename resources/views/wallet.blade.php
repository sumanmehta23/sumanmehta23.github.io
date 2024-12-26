@extends('layouts.crm.crm')
@section('content')
<style>
    #wallet_transactions .td-wrap {
        max-width: 75px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .wallet-plus td {
        --bs-text-opacity: 1;
        color: rgba(var(--bs-success-rgb), var(--bs-text-opacity)) !important;
    }

    .wallet-minus td {
        --bs-text-opacity: 1;
        color: rgba(var(--bs-danger-rgb), var(--bs-text-opacity)) !important;
    }

    .wallet-pending td {
        --bs-text-opacity: 1;
        color: rgba(var(--bs-warning-rgb), var(--bs-text-opacity)) !important;
    }
</style>
<div class="pc-container">
    <div class="pc-content">
        <div class="pb-0 mb-0 page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title h2">
                            <h4 class="mb-0">Wallet</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="mt-2 mb-4 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="bg-gray-300 avtar avtar-s">
                                    <svg class="pc-icon">
                                        <use xlink:href="#custom-security-safe"></use>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-black">My Wallet</h5>
                            </div>
                        </div>
                        <div class="mb-5 d-flex align-items-end justify-content-center" style="height: 100px;">
                            <img src="{{ asset('assets/images/wallet.png') }}" class="pt-4" alt="logo" style="width: 20%; margin-right: 10px;">
                            {{-- @if (auth()->user()->wallet_enabled == 0 || is_null(auth()->user()->wallet_enabled))
                                <button class="btn btn-outline-secondary activate-wallet" type="button">
                                    <i class="ti ti-plus me-2"></i> Activate Wallet
                                </button>
                            @else --}}
                                <span class="text-center h2">${{ $wallet_balance }}</span>
                            {{-- @endif --}}
                        </div>

                        <a href="{{ url('/wallet_deposit') }}">
                            <div class="mt-3 card bg-primary available-balance-card">
                                <div class="p-3 card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h4 class="mb-0 text-white">Add Funds</h4>
                                            <p class="mb-0 text-white text-opacity-75">to My Wallet</p>
                                        </div>
                                        <div class="avtar">
                                            <i class="ti ti-database-import f-18"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="{{ url('/wallet_withdrawal') }}">
                            <div class="p-3 my-3 border rounded">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="mb-0">Withdraw</h4>
                                        <p class="mb-0 text-opacity-75">from My Wallet</p>
                                    </div>
                                    <div class="bg-gray-300 avtar avtar-s">
                                        <svg class="pc-icon">
                                            <use xlink:href="#custom-direct-inbox"></use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="{{ url('/trade-deposit') }}">
                            <div class="p-3 my-3 border rounded">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h4 class="mb-0">Transfer</h4>
                                        <p class="mb-0 text-opacity-75">from My Wallet</p>
                                    </div>
                                    <div class="bg-gray-300 avtar avtar-s">
                                        <svg class="pc-icon">
                                            <use xlink:href="#custom-refresh-2"></use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6">
                <div class="card">
                    <div class="pb-0 card-body border-bottom">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">Recent Wallet Transactions</h5>
                            <div class="dropdown">
                                <a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="/wallet#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="ti ti-dots-vertical f-18"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content" id="wallet_transactions">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        {{-- <th scope="col">TXN ID</th> --}}
                                        <th scope="col">DATE</th>
                                        <th scope="col">TXN ID</th>
                                        <th scope="col">AMOUNT</th>
                                        <th scope="col">FEE</th>
                                        <th scope="col">STATUS</th>
                                        <th scope="col" style="text-align: left;">TYPE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- {{ dd($wallet_history) }} --}}
                                    @foreach ($wallet_history as $transaction)
                                        <?php
                                            if ($transaction->status == 0) {
                                                $status = 'Pending';
                                                $rowClass = 'wallet-pending';
                                            } else if($transaction->status == 1) {
                                                $status = 'Approved';
                                                $rowClass = 'wallet-plus';
                                            } else if($transaction->status == 2 || $transaction->status == 3){
                                                $status = 'Rejected';
                                                $rowClass = 'wallet-minus';
                                            }
                                            $dateTime = explode(' ', $transaction->date_added);
                                            $date = $dateTime[0]; // Date part
                                            $time = isset($dateTime[1]) ? $dateTime[1] : '';
                                        ?>
                                        <tr class="{{ $rowClass }}">
                                            {{-- <td>{{ $transaction->type == 'deposit' ? 'WDID'.$transaction->raw_id : 'WWID'.$transaction->raw_id }}</td> --}}
                                            {{-- <td>{{ $transaction->date_added }}</td> --}}
                                            <td>
                                                <?= htmlspecialchars($date); ?><br>
                                                <?= htmlspecialchars($time); ?>

                                            </td>
                                            <td class="text-wrap"><?= $transaction->transaction_id; ?></td>
                                            <td class="text-left td-wrap">
                                                {{ $transaction->type == 'deposit' ? '+' : '-' }} ${{ $transaction->amount }}
                                            </td>
                                            <td class="text-end"><?= isset($transaction->withdraw_transaction_fee) ? ($transaction->type == 'deposit' ? '+' : '-') : '' ?>$<?= $transaction->withdraw_transaction_fee ?? 0; ?></td>
                                            <td class="text-wrap"><?= $status; ?></td>
                                            <td class="text-left">{{ $transaction->transfer_type }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <a href="{{ url('/transactions') }}" class="btn btn-outline-secondary d-grid">
                                        <span class="text-truncate w-100">View all Transaction History</span>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <a class="btn btn-primary d-grid" href="{{ url('/wallet_deposit') }}">
                                        <span class="text-truncate w-100">Create New Transaction</span>
                                    </a>
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
