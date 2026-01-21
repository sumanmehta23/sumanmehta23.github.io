@php
    use Carbon\Carbon;
@endphp
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
              <h4 class="mb-0">Transactions</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="pb-0 card-body border-bottom">
            <div class="d-flex align-items-center justify-content-between">
              <h5 class="mb-0">All Transactions</h5>
              <div class="dropdown">
                <a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="ti ti-dots-vertical f-18"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="#">Today</a>
                  <a class="dropdown-item" href="#">Weekly</a>
                  <a class="dropdown-item" href="#">Monthly</a>
                </div>
              </div>
            </div>
            <ul class="nav nav-tabs analytics-tab" id="myTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="analytics-tab-1" data-bs-toggle="tab" data-bs-target="#deposits" type="button" role="tab" aria-controls="deposits" aria-selected="true">Deposits</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="withdrawls" data-bs-toggle="tab" data-bs-target="#withdrawls-pane" type="button" role="tab" aria-controls="withdrawls-pane" aria-selected="false">Withdrawals</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="internaltrans" data-bs-toggle="tab" data-bs-target="#internaltrans-pane" type="button" role="tab" aria-controls="internaltrans-pane" aria-selected="false">Internal Transfers</button>
              </li>
            </ul>
          </div>
          <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="deposits" role="tabpanel" aria-labelledby="analytics-tab-1">
              @if($deposit_history->isNotEmpty())
                <div class="px-5 table-responsive">
                  <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">Account</th>
                        <th scope="col">TRANSACTION DATE</th>
                        <th scope="col">TYPE</th>
                        <th scope="col">AMOUNT</th>
                        <th scope="col">STATUS</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($deposit_history as $history)
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <div>
                                <div class="border avtar avtar-s"><img src="/assets/images/mt5.png" class="wid-30" alt="logo"></div>
                              </div>
                              <div class="ms-2">
                                <h6 class="mb-0">{{ $history->code }}</h6>
                                <p class="mb-0 text-muted"><small>{{ $history->ac_name }}</small></p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <h6 class="f-w-500">{{Carbon::parse($history->deposted_date)->addHours(3)->format('Y-m-d') }}</h6>
                            <p class="mb-0 text-muted">
                              <small>{{ Carbon::parse($history->deposted_date)->addHours(3)->format('H:i A') }}</small>
                            </p>
                          </td>
                          <td>
                            <h6 class="f-w-500">{{ $history->deposit_type }}</h6>
                          </td>
                          <td>
                            <h6 class="f-w-500 f-16">${{ number_format($history->deposit_amount, 2) }}</h6>
                          </td>
                          <td class="{{ $history->status == 0 ? 'text-warning' : ($history->status == 1 ? 'text-success' : 'text-danger') }}">
                            <p>{{ $history->status == 0 ? 'Pending' : ($history->status == 1 ? 'Success' : 'Cancelled') }}</p>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                  <hr>
                  <div class="mt-2 row justify-content-between">
                    <div class="col-md-auto me-auto">
                      <div class="dt-info" aria-live="polite" role="status">Showing 1 to {{ $deposit_history->count() }} of {{ $deposit_history->count() }} entries</div>
                    </div>
                  </div>
                </div>
              @else
                <div class="px-5 table-responsive">
                  <div class="auth-main">
                    <div class="card-body">
                      <div class="text-center me-4">
                        <a href="/transactions/deposit#"><img src="/assets/images/deposit2.png" class="w-25" alt="img"></a>
                      </div>
                      <h6 class="mb-0 text-center text-secondary f-w-400 f-16">No Deposit History found!</h6>
                    </div>
                  </div>
                </div>
              @endif
              <div class="card-footer">
                <div class="row g-2 justify-content-center">
                  <div class="col-md-6">
                    <div class="d-grid"><a href="/trade-deposit" class="d-grid"><button class="btn btn-primary"><span class="text-truncate w-100">Manage Funds</span></button></a></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="withdrawls-pane" role="tabpanel" aria-labelledby="withdrawls">
              @if($withdrawal_history->isNotEmpty())
                <div class="px-5 table-responsive">
                  <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">Transaction ID</th>
                        <th scope="col">TRANSACTION DATE</th>
                        <th scope="col">TYPE</th>
                        <th scope="col">AMOUNT</th>
                        <th scope="col">FEE</th>
                        <th scope="col">TRANSACTION HASH</th>
                        <th scope="col">WITHDRAW DATE</th>
                        <th scope="col">STATUS</th>
                        <th scope="col">ACTION</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($withdrawal_history as $history)
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <div>
                                <div class="border avtar avtar-s"><img src="/assets/images/mt5.png" class="wid-30" alt="logo"></div>
                              </div>
                              <div class="ms-2">
                                <h6 class="mb-0">{{ $history->code ?? '' }}</h6>
                                <h6 class="mb-0">{{ $history->id }}</h6>
                                <p class="mb-0 text-muted"><small>Live Account</small></p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <h6 class="f-w-500">{{ Carbon::parse($history->withdraw_date)->addHours(3)->format('Y-m-d') }}</h6>
                            <p class="mb-0 text-muted">
                              <small>{{ Carbon::parse($history->withdraw_date)->addHours(3)->format('H:i A') }}</small>
                            </p>
                          </td>
                          <td>
                            <h6 class="f-w-500">{{ $history->withdraw_type }}</h6>
                          </td>
                          <td>
                            <h6 class="f-w-500 f-16">${{ number_format($history->withdrawal_amount, 2) }}</h6>
                          </td>
                          <td>
                            <h6 class="f-w-500 f-16">${{ number_format($history->withdraw_transaction_fee ?? $history->transaction_fee, 2) }}</h6>
                          </td>
                          @php
                            if($history->status == 1 && !empty($history->payout_res)){
                                        $data = json_decode($history->payout_res, true);
                                        $txid = $data['result']['txid'] ?? null;
                                        $kind = $data['result']['kind'] ?? '';
                                        $coin = strtoupper(preg_split('/[^a-zA-Z]/', $kind)[0]);
                                        if($txid){
                                            // $link = "https://{$coin}.tokenview.io/en/tx/{$txid}";
                                             $link = "https://www.blockchain.com/explorer/transactions/{$coin}/{$txid}";
                                        }
                            } else {
                                    $link = null;
                            }
                          @endphp
                          <td>
                            @if(isset($link))
                                <a class="btn btn-sm btn-outline-primary primary-btn" target="_blank" href="{{ $link }}"> View Transaction</a>
                            @else
                                <p class="text-sm">N/A</p>
                            @endif
                          </td>
                          {{-- <td class="px-4 py-3 text-sm font-medium {{ $history->status == 0 ? 'text-warning' : ($history->status == 1 ? 'text-success' : 'text-red-500') }}">
                            @if($history->payout_callback_status)
                                <p class="text-sm">{{ $history->status == 0 ? ((($history->email_verified ?? 0) == 0) && (($history->verified ?? 0) == 0)? 'Email Not Verify' : 'Pending') : (($history->status == 1 && $history->payout_callback_status != 'complete') ? 'Processing' : 'Approved') }}</p>
                            @else
                                <p class="text-sm">{{ $history->status == 0 ? ((($history->email_verified ?? 0) == 0) && (($history->verified ?? 0) == 0)? 'Email Not Verify' : 'Pending') : (($history->status == 1 && $history->payout_callback_status == 'complete') ? 'Approved' : (($history->status == 1) ? (($history->admin_remark == 'Manually Approved') ? 'Approved' : 'Processing') : 'Cancelled')) }}</p>
                                <p>
                                    {{
                                        ($history->payout_req !== null && $history->admin_remark !== 'Approved' && $history->status !== 0)
                                            ? htmlspecialchars(
                                                in_array($history->admin_remark, ['draft', 'new'])
                                                    ? ''
                                                    :'('. $history->admin_remark .')'
                                            )
                                            : ''
                                    }}
                                </p>
                            @endif

                            <p class="text-sm text-color-green">{{ ($history->status == 0 && ($history->verified == 1) ? 'Email Verified' : '') }}</p>

                            @if((($history->email_verified ?? 0) == 0) && (($history->verified ?? 0) == 0) && ($history->status == 0))
                                <a  href="#"
                                    class="btn btn-sm btn-outline-primary primary-btn"
                                    onclick="resendWalletWithdrawalVerifyEmail('{{ json_encode($history->id) }}')"
                                    type="submit">
                                        Resend Verification Email
                                </a>
                            @endif
                        </td> --}}

                        @php
                            // Resolve status text
                            if ($history->status == 0) {
                                $statusText = ((($history->email_verified ?? 0) == 0) && (($history->verified ?? 0) == 0))
                                    ? 'Email Not Verify'
                                    : 'Pending';
                            } elseif ($history->status == 1) {
                                if (
                                    ($history->payout_callback_status && $history->payout_callback_status != 'complete') ||
                                    (!$history->payout_callback_status && $history->admin_remark != 'Manually Approved')
                                ) {
                                    $statusText = 'Processing';
                                } else {
                                    $statusText = 'Approved';
                                }
                            } else {
                                $statusText = 'Cancelled';
                            }

                            // Resolve color class
                            $statusClass = match ($statusText) {
                                'Approved' => 'text-success',
                                'Processing', 'Pending', 'Email Not Verify' => 'text-warning',
                                'Cancelled' => 'text-red-500',
                                default => 'text-gray-500',
                            };
                        @endphp

                        <td class="px-4 py-3 text-sm">
                            @if ($statusText == 'Approved' && $history->approved_date)
                                <div class="text-xs">{{ $history->approved_date->format('Y-m-d') }}</div>
                                <div class="text-xs text-gray-100">{{ $history->approved_date->format('h:i A') }}</div>
                            @else
                                <span class="px-4 py-3 text-sm "> </span>
                            @endif
                        </td>



                        <td class="px-4 py-3 text-sm font-medium">
                            @if($history->payout_callback_status)
                                <p class="text-sm
                                    {{ $history->status == 0
                                        ? 'text-warning'
                                        : (($history->status == 1 && $history->payout_callback_status != 'complete')
                                            ? 'text-warning'
                                            : 'text-success')
                                    }}">
                                    {{ $history->status == 0
                                        ? ((($history->email_verified ?? 0) == 0) && (($history->verified ?? 0) == 0)
                                            ? 'Email Not Verify'
                                            : 'Pending')
                                        : (($history->status == 1 && $history->payout_callback_status != 'complete')
                                            ? 'Processing'
                                            : 'Approved')
                                    }}
                                </p>
                            @else
                                <p class="text-sm
                                    {{ $history->status == 0
                                        ? 'text-warning'
                                        : (($history->status == 1)
                                            ? (($history->admin_remark == 'Manually Approved')
                                                ? 'text-success'
                                                : 'text-warning')
                                            : 'text-red-500')
                                    }}">
                                    {{ $history->status == 0
                                        ? ((($history->email_verified ?? 0) == 0) && (($history->verified ?? 0) == 0)
                                            ? 'Email Not Verify'
                                            : 'Pending')
                                        : (($history->status == 1 && $history->payout_callback_status == 'complete')
                                            ? 'Approved'
                                            : (($history->status == 1)
                                                ? (($history->admin_remark == 'Manually Approved')
                                                    ? 'Approved'
                                                    : 'Processing')
                                                : 'Cancelled'))
                                    }}
                                </p>

                                <p>
                                    {{
                                        ($history->payout_req !== null && $history->admin_remark !== 'Approved' && $history->status !== 0)
                                            ? htmlspecialchars(
                                                in_array($history->admin_remark, ['draft', 'new'])
                                                    ? ''
                                                    : '(' . $history->admin_remark . ')'
                                            )
                                            : ''
                                    }}
                                </p>
                            @endif

                            <p class="text-sm text-success">
                                {{ ($history->status == 0 && ($history->verified == 1)) ? 'Email Verified' : '' }}
                            </p>

                            @if((($history->email_verified ?? 0) == 0) && (($history->verified ?? 0) == 0) && ($history->status == 0))
                                <a href="#"
                                class="btn btn-sm btn-outline-primary primary-btn"
                                onclick="resendWalletWithdrawalVerifyEmail('{{ json_encode($history->id) }}')">
                                    Resend Verification Email
                                </a>
                            @endif
                        </td>

                          @if($history->status == 0)
                            <td >
                                <a  href="#"
                                    class="btn btn-sm btn-outline-secondary d-grid reject-btn"
                                    onclick="takeAction('{{ json_encode($history->id) }}','{{ $history->email }}','{{ $history->withdraw_amount + $history->withdraw_transaction_fee}}',3)"
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
                  <hr>
                  <div class="mt-2 row justify-content-between">
                    <div class="col-md-auto me-auto">
                      <div class="dt-info" aria-live="polite" role="status">Showing 1 to {{ $withdrawal_history->count() }} of {{ $withdrawal_history->count() }} entries</div>
                    </div>
                  </div>
                </div>
              @else
                <div class="px-5 table-responsive">
                  <div class="auth-main">
                    <div class="card-body">
                      <div class="text-center me-4">
                        <a href="#"><img src="/assets/images/withdrawals2.png" class="w-25" alt="img"></a>
                      </div>
                      <h6 class="mb-0 text-center text-secondary f-w-400 f-16">No Withdrawal History found!</h6>
                    </div>
                  </div>
                </div>
              @endif
              <div class="card-footer">
                <div class="row g-2 justify-content-center">
                  <div class="col-md-6">
                    <div class="d-grid"><a href="/trade-withdrawal" class="d-grid"><button class="btn btn-primary"><span class="text-truncate w-100">Manage Funds</span></button></a></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="internaltrans-pane" role="tabpanel" aria-labelledby="internaltrans">
              @if($internal_transfer->isNotEmpty())
                <div class="px-5 table-responsive">
                  <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">TRANSACTION DATE</th>
                        <th scope="col">From</th>
                        <th scope="col">To</th>
                        <th scope="col">Type</th>
                        <th scope="col">AMOUNT</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($internal_transfer as $history)
                      {{-- {{ dump($internal_transfer); }} --}}
                        @php
                            if ($history->type == 'CRM') {
                                $from = 'CRM';
                            } elseif($history->type == 'IB Withdraw'){
                                $from = 'IB Wallet';
                            }elseif (!empty($history->accountFrom->code)) {
                                $from = $history->accountFrom->code;
                            }elseif($history->type == 'Wallet Transfer' && $history->source == 'TDID'){
                                $from = 'Wallet';
                            } else {
                                $from = $history->accountFrom()->withTrashed() ? $history->accountFrom()->withTrashed()->value('code') : $history->it_from;
                            }

                            if ($history->source == "TWID" && $history->type == 'Wallet Withdrawal') {
                                $to = $history->it_to ?? 'Wallet';
                            } else {
                                $to = !empty($history->accountTo()->withTrashed()) ? $history->accountTo()->withTrashed()->value('code') : '';
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="ms-2">
                                        <h6 class="mb-0">{{ $history['source'] }}</h6>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <h6 class="f-w-500">{{ Carbon::parse($history['date'])->addHours(3)->format('Y-m-d') }}</h6>
                                <p class="mb-0 text-muted">
                                    <small>{{ Carbon::parse($history['date'])->addHours(3)->format('H:i A') }}</small>
                                </p>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0">{{ $from }}</h6>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0">{{ $to }}</h6>
                                </div>
                            </td>
                            <td>
                                <h6 class="f-w-500">{{ $history['type'] }}</h6>
                            </td>
                            <td>
                                <h6 class="f-w-500 f-16">${{ number_format($history['amount'], 2) }}</h6>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                  </table>
                  <hr>
                  <div class="mt-2 row justify-content-between">
                    <div class="col-md-auto me-auto">
                      <div class="dt-info" aria-live="polite" role="status">Showing 1 to {{ $internal_transfer->count() }} of {{ $internal_transfer->count() }} entries</div>
                    </div>
                  </div>
                </div>
              @else
                <div class="px-5 table-responsive">
                  <div class="auth-main">
                    <div class="card-body">
                      <div class="text-center me-4">
                        <a href="#"><img src="/assets/images/internaltransfer2.png" class="w-25" alt="img"></a>
                      </div>
                      <h6 class="mb-0 text-center text-secondary f-w-400 f-16">No Internal Transfers Found!</h6>
                    </div>
                  </div>
                </div>
              @endif
              <div class="card-footer">
                <div class="row g-2 justify-content-center">
                  <div class="col-md-6">
                    <div class="d-grid"><a href="/internal-transfer" class="d-grid"><button class="btn btn-primary"><span class="text-truncate w-100">Manage Funds</span></button></a></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@if (session('status'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: "{{ session('status') }}",
        });
    </script>
@endif

@if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "{{ session('error') }}",
        });
    </script>
@endif

<script>
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

    function resendWalletWithdrawalVerifyEmail(walletWithdrawalId) {
        walletWithdrawalId = JSON.parse(walletWithdrawalId);

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
  </script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get("tab");

        if (activeTab === "withdrawals") {
            const withdrawalsTab = document.querySelector('#withdrawls');
            const withdrawalsPane = document.querySelector('#withdrawls-pane');

            if (withdrawalsTab && withdrawalsPane) {
                // remove "active" from deposits
                document.querySelector('#analytics-tab-1')?.classList.remove('active');
                document.querySelector('#deposits')?.classList.remove('show', 'active');

                // activate withdrawals tab
                withdrawalsTab.classList.add('active');
                withdrawalsPane.classList.add('show', 'active');
            }
        }
    });
  </script>


@endsection
