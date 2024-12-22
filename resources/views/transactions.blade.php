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
</style>
<div class="pc-container">
  <div class="pc-content">
    <div class="page-header mb-0 pb-0">
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
          <div class="card-body border-bottom pb-0">
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
                                <div class="avtar avtar-s border"><img src="/assets/images/mt5.png" class="wid-30" alt="logo"></div>
                              </div>
                              <div class="ms-2">
                                <h6 class="mb-0">{{ $history->code }}</h6>
                                <p class="text-muted mb-0"><small>{{ $history->ac_name }}</small></p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <h6 class="f-w-500">{{Carbon::parse($history->deposted_date)->format('Y-m-d') }}</h6>
                            <p class="text-muted mb-0">
                              <small>{{ Carbon::parse($history->deposted_date)->format('H:i A') }}</small>
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
                  <div class="row mt-2 justify-content-between">
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
                      <h6 class="text-center text-secondary f-w-400 mb-0 f-16">No Deposit History found!</h6>
                    </div>
                  </div>
                </div>
              @endif
              <div class="card-footer">
                <div class="row g-2 justify-content-center">
                  <div class="col-md-6">
                    <div class="d-grid"><a href="/trade-deposit" class="d-grid"><button class="btn btn-primary"><span class="text-truncate w-100">Mange Funds</span></button></a></div>
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
                        <th scope="col">STATUS</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($withdrawal_history as $history)
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <div>
                                <div class="avtar avtar-s border"><img src="/assets/images/mt5.png" class="wid-30" alt="logo"></div>
                              </div>
                              <div class="ms-2">
                                <h6 class="mb-0">{{ $history->transaction_id }}</h6>
                                <p class="text-muted mb-0"><small>Live Account</small></p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <h6 class="f-w-500">{{ Carbon::parse($history->withdraw_date)->format('Y-m-d') }}</h6>
                            <p class="text-muted mb-0">
                              <small>{{ Carbon::parse($history->withdraw_date)->format('H:i A') }}</small>
                            </p>
                          </td>
                          <td>
                            <h6 class="f-w-500">{{ $history->withdraw_type }}</h6>
                          </td>
                          <td>
                            <h6 class="f-w-500 f-16">${{ number_format($history->withdraw_amount, 2) }}</h6>
                          </td>
                          <td>
                            <h6 class="f-w-500 f-16">${{ number_format($history->withdraw_transaction_fee, 2) }}</h6>
                          </td>
                          <td class="{{ $history->status == 0 ? 'text-warning' : ($history->status == 1 ? 'text-success' : 'text-danger') }}">
                            <p>{{ $history->status == 0 ? 'Pending' : ($history->status == 1 ? 'Success' : 'Cancelled') }}</p>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                  <hr>
                  <div class="row mt-2 justify-content-between">
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
                      <h6 class="text-center text-secondary f-w-400 mb-0 f-16">No Withdrawal History found!</h6>
                    </div>
                  </div>
                </div>
              @endif
              <div class="card-footer">
                <div class="row g-2 justify-content-center">
                  <div class="col-md-6">
                    <div class="d-grid"><a href="/trade-withdrawal" class="d-grid"><button class="btn btn-primary"><span class="text-truncate w-100">Mange Funds</span></button></a></div>
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
                                $from = $history->it_from;
                            }

                            if ($history->source == "TWID" && $history->type == 'Wallet Withdrawal') {
                                $to = $history->it_to ?? 'Wallet';
                            } else {
                                $to = !empty($history->accountTo->code) ? $history->accountTo->code : '';
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
                                <h6 class="f-w-500">{{ Carbon::parse($history['date'])->format('Y-m-d') }}</h6>
                                <p class="text-muted mb-0">
                                    <small>{{ Carbon::parse($history['date'])->format('H:i A') }}</small>
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
                  <div class="row mt-2 justify-content-between">
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
                      <h6 class="text-center text-secondary f-w-400 mb-0 f-16">No Internal Transfers Found!</h6>
                    </div>
                  </div>
                </div>
              @endif
              <div class="card-footer">
                <div class="row g-2 justify-content-center">
                  <div class="col-md-6">
                    <div class="d-grid"><a href="/internal-transfer" class="d-grid"><button class="btn btn-primary"><span class="text-truncate w-100">Mange Funds</span></button></a></div>
                  </div>
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
