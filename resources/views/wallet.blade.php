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
    .reject-btn {
        border-color: red; /* Outer line red */
        color: red;        /* Text red */
        transition: background-color 0.3s, color 0.3s; /* Smooth hover effect */
    }

    .reject-btn:hover {
        background-color: red; /* Background red on hover */
        color: white;          /* Text white on hover */
        border-color: red;
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
            <div class="col-md-4 col-lg-4">
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
                                <span class="text-center h2">@money($wallet_balance)</span>
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
            <div class="col-md-8 col-lg-8">
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
                                        <th scope="col">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($wallet_history as $transaction)
                                       {{-- {{  dd($transaction) }} --}}
                                        <?php
                                            if ($transaction->status == 0) {
                                                if($transaction->verified == 0){
                                                    $status = 'Email Verification Pending';
                                                    $rowClass = 'wallet-pending';
                                                }else{
                                                    $status = 'Pending';
                                                    $rowClass = 'wallet-pending';
                                                }
                                            } else if($transaction->status == 1) {
                                                $status = 'Approved';
                                                $rowClass = 'wallet-plus';
                                            } else if($transaction->status == 2 || $transaction->status == 3){
                                                $status = 'Cancelled';
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
                                            <td class="text-wrap">{{ $transaction->transaction_id }}</td>
                                            <td class="text-left td-wrap">
                                                {{ $transaction->type == 'deposit' ? '+' : '-' }} ${{ $transaction->amount }}
                                            </td>
                                            <td class="text-end"><?= isset($transaction->withdraw_transaction_fee) ? ($transaction->type == 'deposit' ? '+' : '-') : '' ?>${{ $transaction->withdraw_transaction_fee ?? 0 }}</td>
                                            <td class="text-wrap">
                                                {{ $status }} <br>
                                                @if(($transaction->status == 0) && ($transaction->verified == 0))
                                                    <a  href="#"
                                                        class="btn btn-sm btn-outline-primary primary-btn"
                                                        onclick="resendWalletWithdrawalVerifyEmail('{{ json_encode($transaction->raw_id) }}')"
                                                        type="submit">
                                                            Resend Verification Email
                                                    </a>
                                                @endif
                                            </td>
                                            <td class="text-left">{{ $transaction->transfer_type }}</td>
                                            @if($transaction->status == 0)
                                                <td >
                                                    <a  href="#"
                                                        class="btn btn-sm btn-outline-secondary d-grid reject-btn"
                                                        onclick="takeAction('{{ json_encode($transaction->raw_id) }}','{{ $transaction->email }}','{{ $transaction->amount + $transaction->withdraw_transaction_fee}}',3)"
                                                        type="submit">
                                                    Cancel
                                                    </a>
                                                </td>
                                            @else
                                                <td></td>
                                            @endif
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
<script>

    function resendWalletWithdrawalVerifyEmail(walletWithdrawalId) {
        walletWithdrawalId = JSON.parse(walletWithdrawalId);
        console.log(walletWithdrawalId);
        fetch("{{ route('resend.wallet.withdrawal.email') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({ wallet_withdrawal_id: walletWithdrawalId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: data.message,
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "OK"
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: data.message || "Error sending verification email.",
                    confirmButtonColor: "#d33",
                    confirmButtonText: "Try Again"
                });
            }
        })
        .catch(error => {
            console.error("Error:", error);
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Something went wrong. Please try again!",
                confirmButtonColor: "#d33",
                confirmButtonText: "Close"
            });
        });
    }

    function takeAction(data, email, amount, status) {
        parsedData = JSON.parse(data)
        const now = new Date();
        const approved_date_time = `${now.getFullYear()}-${(now.getMonth() + 1).toString().padStart(2, '0')}-${now.getDate().toString().padStart(2, '0')} ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;

        if(status==3){
            statuscode='cancel';
        }
        Swal.fire({
            title: `Are you sure you want to ${statuscode} this transaction?`,
            html: `
            <form id="updateTransactionForm" method="post" action="/update-transaction">
            @csrf
            <input type="hidden" name="email" value="${email}">
            <input type="hidden" name="amount" value="${amount}">
            <input type="hidden" name="status" value="${status}">
            <input type="hidden" name="statuscode" value="${statuscode}">
            <input type="hidden" name="transaction_id" value="${parsedData}">
            <input type="hidden" name="action" value="update_transaction">
                ${
                status == 3
                    ? `

            `
                    : ''
                }
            </form>
        `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
        }).then((result) => {
            console.log(result);
            if (result.isConfirmed) {
            document.querySelector('#updateTransactionForm').submit();
            }
        });
    }
</script>
@endsection
