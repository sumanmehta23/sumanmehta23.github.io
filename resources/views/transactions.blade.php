@php
    use Carbon\Carbon;
    use Illuminate\Pagination\LengthAwarePaginator;

    $tabAliases = [
        'withdrawls' => 'withdrawals',
        'internaltrans' => 'internal-transfers',
        'internal_transfer' => 'internal-transfers',
    ];

    $activeTransactionTab = $tabAliases[request('tab')] ?? request('tab', 'deposits');

    if (! in_array($activeTransactionTab, ['deposits', 'withdrawals', 'internal-transfers'], true)) {
        $activeTransactionTab = 'deposits';
    }

    $paginateCollection = function ($items, int $perPage, string $pageName) {
        if (
            $items instanceof \Illuminate\Contracts\Pagination\Paginator ||
            $items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        ) {
            return $items;
        }

        $items = $items instanceof \Illuminate\Support\Collection ? $items : collect($items);
        $currentPage = LengthAwarePaginator::resolveCurrentPage($pageName);
        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
                'query' => request()->query(),
            ]
        );
    };

    $deposit_history = $paginateCollection($deposit_history, 5, 'deposit_page');
    $withdrawal_history = $paginateCollection($withdrawal_history, 5, 'withdrawal_page');
    $internal_transfer = $paginateCollection($internal_transfer, 5, 'transfer_page');
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

    @media (max-width: 768px) {
        .mobile-pagination-center {
            display: flex;
            justify-content: center;
        }

        .transactions-tabs-mobile {
            flex-wrap: nowrap !important;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .transactions-tabs-mobile::-webkit-scrollbar {
            display: none;
        }

        .transactions-tabs-mobile .nav-item {
            flex: 0 0 auto;
        }

        .transactions-tabs-mobile .nav-link {
            white-space: nowrap;
            font-size: 12px;
            padding: 0.75rem 0.45rem;
        }
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
            <ul class="nav nav-tabs analytics-tab transactions-tabs-mobile" id="myTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTransactionTab === 'deposits' ? 'active' : '' }}" id="analytics-tab-1" data-bs-toggle="tab" data-bs-target="#deposits" type="button" role="tab" aria-controls="deposits" aria-selected="{{ $activeTransactionTab === 'deposits' ? 'true' : 'false' }}">Deposits</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTransactionTab === 'withdrawals' ? 'active' : '' }}" id="withdrawls" data-bs-toggle="tab" data-bs-target="#withdrawls-pane" type="button" role="tab" aria-controls="withdrawls-pane" aria-selected="{{ $activeTransactionTab === 'withdrawals' ? 'true' : 'false' }}">Withdrawals</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTransactionTab === 'internal-transfers' ? 'active' : '' }}" id="internaltrans" data-bs-toggle="tab" data-bs-target="#internaltrans-pane" type="button" role="tab" aria-controls="internaltrans-pane" aria-selected="{{ $activeTransactionTab === 'internal-transfers' ? 'true' : 'false' }}">Internal Transfers</button>
              </li>
            </ul>
          </div>
          <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade {{ $activeTransactionTab === 'deposits' ? 'show active' : '' }}" id="deposits" role="tabpanel" aria-labelledby="analytics-tab-1">
              @if($deposit_history->count() > 0)
                {{-- Mobileview Deposit --}}
                <div class="p-2 d-block d-md-none">
                  @foreach ($deposit_history as $history)
                    <div class="p-3 mb-3 border rounded">
                      <div class="d-flex align-items-center">
                        <div>
                          <div class="border avtar avtar-s"><img src="/assets/images/mt5.png" class="wid-30" alt="logo"></div>
                        </div>
                        <div class="ms-2 flex-grow-1">
                          <h6 class="mb-0">{{ $history->code }}</h6>
                          <p class="mb-0 text-muted f-12">{{ $history->ac_name }}</p>
                        </div>
                      </div>
                      <div class="mt-3">
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Date</p>
                          <p class="mb-0 f-w-500">{{ Carbon::parse($history->deposted_date)->addHours(3)->format('Y-m-d') }}</p>
                        </div>
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Time</p>
                          <p class="mb-0 f-w-500">{{ Carbon::parse($history->deposted_date)->addHours(3)->format('H:i A') }}</p>
                        </div>
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Type</p>
                          <p class="mb-0 f-w-500">{{ $history->deposit_type }}</p>
                        </div>
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Amount</p>
                          <p class="mb-0 f-w-500 f-16">${{ number_format($history->deposit_amount, 2) }}</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Status</p>
                          <p class="mb-0 f-w-500 {{ $history->status == 0 ? 'text-warning' : ($history->status == 1 ? 'text-success' : 'text-danger') }}">{{ $history->status == 0 ? 'Pending' : ($history->status == 1 ? 'Success' : 'Cancelled') }}</p>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
                <div class="px-5 table-responsive d-none d-md-block">
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
                      {{-- <div class="dt-info" aria-live="polite" role="status">Showing {{ $deposit_history->firstItem() ?? 0 }} to {{ $deposit_history->lastItem() ?? 0 }} of {{ $deposit_history->total() }} entries</div> --}}
                    </div>
                  </div>
                </div>
                <div class="px-5 pb-3 mobile-pagination-center">
                  {{ $deposit_history->withQueryString()->appends(['tab' => 'deposits'])->links('pagination::bootstrap-5') }}
                </div>
              @else
                <div class="px-5 table-responsive">
                  <div class="auth-main">
                    <div class="card-body">
                      <div class="text-center me-4">
                        <a href=""><img src="/assets/images/deposit2.png" class="w-25" alt="img"></a>
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
            <div class="tab-pane fade {{ $activeTransactionTab === 'withdrawals' ? 'show active' : '' }}" id="withdrawls-pane" role="tabpanel" aria-labelledby="withdrawls">
              @if($withdrawal_history->count() > 0)
                {{-- Mobile View Withdrawal --}}
                <div class="p-2 d-block d-md-none">
                  @foreach ($withdrawal_history as $history)
                    @php
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
                          $statusText = !empty($history->admin_remark) ? $history->admin_remark : 'Cancelled';
                      }
                      if($history->status == 1 && !empty($history->payout_res) && $statusText == 'Approved'){
                          $data = json_decode($history->payout_res, true);
                          $txid = $data['result']['txid'] ?? null;
                          $kind = $data['result']['kind'] ?? '';
                          $coin = strtoupper(preg_split('/[^a-zA-Z]/', $kind)[0]);
                          if($txid){
                              if($coin =='ETH'){
                                  $link = "https://etherscan.io/tx/{$txid}";
                              }
                              elseif($coin != 'USDT'){
                                  $link = "https://www.blockchain.com/explorer/transactions/{$coin}/{$txid}";
                              }
                              else{
                                  $link = "https://tokenview.io/en/search/{$txid}";
                              }
                          }
                      } else {
                          $link = null;
                      }
                    @endphp
                    <div class="p-3 mb-3 border rounded">
                      <div class="d-flex align-items-center">
                        <div>
                          <div class="border avtar avtar-s"><img src="/assets/images/mt5.png" class="wid-30" alt="logo"></div>
                        </div>
                        <div class="ms-2 flex-grow-1">
                          <h6 class="mb-0">{{ $history->code ?? '' }}</h6>
                          <p class="mb-0 text-muted f-12">Live Account</p>
                        </div>
                      </div>
                      <div class="mt-3">
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Transaction Id</p>
                          <p class="mb-0 f-w-500" style="font-size:10px"> #{{ $history->id }} </p>
                        </div>
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Date</p>
                          <p class="mb-0 f-w-500">{{ Carbon::parse($history->withdraw_date)->addHours(3)->format('Y-m-d') }}</p>
                        </div>
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Time</p>
                          <p class="mb-0 f-w-500">{{ Carbon::parse($history->withdraw_date)->addHours(3)->format('H:i A') }}</p>
                        </div>
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Type</p>
                          <p class="mb-0 f-w-500">{{ $history->withdraw_type }}</p>
                        </div>
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Amount</p>
                          <p class="mb-0 f-w-500 f-16">${{ number_format($history->withdrawal_amount, 2) }}</p>
                        </div>
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Fee</p>
                          <p class="mb-0 f-w-500 f-16">${{ number_format($history->withdraw_transaction_fee ?? $history->transaction_fee, 2) }}</p>
                        </div>
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Status</p>
                          <p class="mb-0 f-w-500 {{ $history->status == 0 ? 'text-warning' : ($history->status == 1 ? 'text-success' : 'text-danger') }}">
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
                                        : ($history->admin_remark ?? 'Cancelled')))
                            }}
                          </p>
                        </div>
                        @if(isset($link))
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Transaction</p>
                          <a class="btn btn-sm btn-outline-primary primary-btn" target="_blank" href="{{ $link }}">View</a>
                        </div>
                        @endif
                        @if ($statusText == 'Approved' && $history->approved_date)
                        <div class="mb-2 d-flex align-items-center justify-content-between">
                          <p class="mb-0 text-muted f-12">Approved Date</p>
                          <p class="mb-0 f-w-500">{{ Carbon::parse($history->approved_date)->addHours(3)->format('Y-m-d H:i A') }}</p>
                        </div>
                        @endif
                        @if((($history->email_verified ?? 0) == 0) && (($history->verified ?? 0) == 0) && ($history->status == 0))
                        <div class="gap-2 mt-3 d-flex">
                          <a href="#" class="btn btn-sm btn-outline-primary primary-btn flex-grow-1" onclick="resendWalletWithdrawalVerifyEmail('{{ json_encode($history->id) }}')">Resend Email</a>
                          <a href="#" class="btn btn-sm btn-outline-secondary reject-btn flex-grow-1" onclick="takeAction('{{ json_encode($history->id) }}','{{ $history->email }}','{{ $history->withdraw_amount + $history->withdraw_transaction_fee}}',3)">Cancel</a>
                        </div>
                        @elseif($history->status == 0)
                        <div class="gap-2 mt-3 d-flex">
                          <a href="#" class="btn btn-sm btn-outline-secondary reject-btn w-100" onclick="takeAction('{{ json_encode($history->id) }}','{{ $history->email }}','{{ $history->withdraw_amount + $history->withdraw_transaction_fee}}',3)">Cancel</a>
                        </div>
                        @endif
                      </div>
                    </div>
                  @endforeach
                </div>
                <div class="px-5 table-responsive d-none d-md-block">
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
                                $statusText = !empty($history->admin_remark) ? $history->admin_remark : 'Cancelled';
                            }
                            if($history->status == 1 && !empty($history->payout_res) && $statusText == 'Approved'){
                                        $data = json_decode($history->payout_res, true);
                                        $txid = $data['result']['txid'] ?? null;
                                        $kind = $data['result']['kind'] ?? '';
                                        $coin = strtoupper(preg_split('/[^a-zA-Z]/', $kind)[0]);
                                        if($txid){
                                            // $link = "https://{$coin}.tokenview.io/en/tx/{$txid}";
                                            if($coin =='ETH'){
                                                $link = "https://etherscan.io/tx/{$txid}";
                                            }
                                            elseif($coin != 'USDT'){
                                                $link = "https://www.blockchain.com/explorer/transactions/{$coin}/{$txid}";
                                            }
                                            else{
                                                $link = "https://tokenview.io/en/search/{$txid}";
                                            }
                                        }
                            } else {
                                    $link = null;
                            }
                            // Resolve color class
                            $statusClass = match ($statusText) {
                                'Approved' => 'text-success',
                                'Processing', 'Pending', 'Email Not Verify' => 'text-warning',
                                'Cancelled' => 'text-red-500',
                                default => 'text-gray-500',
                            };
                        @endphp

                        <td>
                            @if(isset($link))
                                <a class="btn btn-sm btn-outline-primary primary-btn" target="_blank" href="{{ $link }}"> View Transaction</a>
                            @else
                                <p class="f-w-500"> </p>
                            @endif
                        </td>

                        <td>
                            @if ($statusText == 'Approved' && $history->approved_date)
                                <h6 class="f-w-500">{{ Carbon::parse($history->approved_date)->addHours(3)->format('Y-m-d') }}</h6>
                                <p class="mb-0 text-muted">
                                <small>{{ Carbon::parse($history->approved_date)->addHours(3)->format('H:i A') }}</small>
                                </p>
                            @else
                                <span class="px-4 py-3 text-sm font-medium"> </span>
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
                                                : ((($history->admin_remark == 'InvalidAddress') ? 'Invalid cryptocurrency address' : $history->admin_remark) ?? 'Cancelled')))
                                    }}
                                </p>

                                {{-- <p>
                                    {{
                                        ($history->payout_req !== null && $history->admin_remark !== 'Approved' && $history->status !== 0)
                                            ? htmlspecialchars(
                                                in_array($history->admin_remark, ['draft', 'new'])
                                                    ? ''
                                                    : '(' . $history->admin_remark . ')'
                                            )
                                            : ''
                                    }}
                                </p> --}}
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
                      {{-- <div class="dt-info" aria-live="polite" role="status">Showing {{ $withdrawal_history->firstItem() ?? 0 }} to {{ $withdrawal_history->lastItem() ?? 0 }} of {{ $withdrawal_history->total() }} entries</div> --}}
                    </div>
                  </div>
                </div>
                <div class="px-5 pb-3 mobile-pagination-center">
                  {{ $withdrawal_history->withQueryString()->appends(['tab' => 'withdrawals'])->links('pagination::bootstrap-5') }}
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
            <div class="tab-pane fade {{ $activeTransactionTab === 'internal-transfers' ? 'show active' : '' }}" id="internaltrans-pane" role="tabpanel" aria-labelledby="internaltrans">
              @if($internal_transfer->count() > 0)
                {{-- Mobile View Internal Transfer --}}
                <div class="p-2 d-block d-md-none">
                  @foreach ($internal_transfer as $history)
                    @php
                        if ($history->type == 'CRM') {
                            $from = 'CRM';
                        } elseif($history->type == 'IB Withdraw'){
                            $from = 'IB Wallet';
                        }elseif (!empty($history->accountFrom->code ?? null)) {
                            $from = $history->accountFrom->code;
                        }elseif($history->type == 'Wallet Transfer' && $history->source == 'TDID'){
                            $from = 'Wallet';
                        } else {
                            $from = $history->accountFrom ? $history->accountFrom->code : $history->it_from;
                        }

                        if ($history->source == "TWID" && $history->type == 'Wallet Withdrawal') {
                            $to = $history->it_to ?? 'Wallet';
                        } else {
                            $to = $history->accountTo ? $history->accountTo->code : '';
                        }
                    @endphp
                    <div class="mb-3 overflow-hidden bg-white border rounded-4" style="box-shadow: 0 10px 30px rgba(31, 79, 120, 0.08); border-color: #d9e6ee !important;">
                        <div class="p-3">
                            <div class="gap-3 d-flex align-items-start justify-content-between">
                                <div>
                                    <h3 class="mb-2 f-w-700 text-success" style="font-size: 32px; line-height: 1;">${{ number_format($history['amount'], 2) }}</h3>
                                    <p class="mb-0 text-muted" style="font-size: 12px;">
                                        {{ Carbon::parse($history['date'])->addHours(3)->format('M d, Y') }}
                                        <span class="mx-1">&middot;</span>
                                        {{ Carbon::parse($history['date'])->addHours(3)->format('H:i:s') }}
                                    </p>
                                </div>
                                <span class="px-3 py-2 rounded-pill f-w-600" style="font-size: 12px; color: {{ $history['status'] == 1 ? '#1f9d63' : '#dc3545' }}; background: {{ $history['status'] == 1 ? '#edf8f1' : '#fdecec' }};">
                                    {{ ($history['status']==1 ? 'Successful' : 'Failed') }}
                                </span>
                            </div>

                            <div class="mt-4 border-top" style="border-color: #d9e6ee !important;">
                                <div class="py-3 d-flex align-items-center justify-content-between border-bottom" style="border-color: #e6eff5 !important;">
                                    <p class="mb-0 text-muted f-12">From</p>
                                    <p class="mb-0 f-w-600 text-dark">{{ $from }}</p>
                                </div>
                                <div class="py-3 d-flex align-items-center justify-content-between border-bottom" style="border-color: #e6eff5 !important;">
                                    <p class="mb-0 text-muted f-12">To</p>
                                    <p class="mb-0 f-w-600 text-dark">{{ $to }}</p>
                                </div>
                                <div class="py-3 d-flex align-items-center justify-content-between">
                                    <p class="mb-0 text-muted f-12">Type</p>
                                    <p class="mb-0 f-w-600 text-dark">{{ $history['type'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="px-3 py-3 border-top" style="background: #f7fbfd; border-color: #d9e6ee !important;">
                            <p class="mb-1 text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.08em;">Transaction ID</p>
                            <p class="mb-0 f-w-500 text-break" style="font-size: 12px; color: #0b7894;">#{{ $history['raw_id'] }}</p>
                        </div>
                    </div>
                  @endforeach
                </div>
                <div class="px-5 table-responsive d-none d-md-block">
                  <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">Transaction ID</th>
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
                            }elseif (!empty($history->accountFrom->code ?? null)) {
                                $from = $history->accountFrom->code;
                            }elseif($history->type == 'Wallet Transfer' && $history->source == 'TDID'){
                                $from = 'Wallet';
                            } else {
                                $from = $history->accountFrom ? $history->accountFrom->code : $history->it_from;
                            }

                            if ($history->source == "TWID" && $history->type == 'Wallet Withdrawal') {
                                $to = $history->it_to ?? 'Wallet';
                            } else {
                                $to = $history->accountTo ? $history->accountTo->code : '';
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="ms-2">
                                        <h6 class="mb-0">{{ $history['raw_id'] }}</h6>
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
                      {{-- <div class="dt-info" aria-live="polite" role="status">Showing {{ $internal_transfer->firstItem() ?? 0 }} to {{ $internal_transfer->lastItem() ?? 0 }} of {{ $internal_transfer->total() }} entries</div> --}}
                    </div>
                  </div>
                </div>
                <div class="px-5 pb-3 mobile-pagination-center">
                  {{ $internal_transfer->withQueryString()->appends(['tab' => 'internal-transfers'])->links('pagination::bootstrap-5') }}
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
