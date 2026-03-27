@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Transaction List</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Transaction List</li>
                </ol>
            </div>
            {{-- <div class="mb-3 row">
                <div class="col-md-4">
                  <label for="statusFilter">Filter by Status:</label>
                  <select id="statusFilter" class="form-select" name="status">
                    <option value="">All</option>
                    <option value="1">Approved</option>
                    <option value="2">Rejected</option>
                    <option value="0">Pending</option>
                    <!-- Add other status options as needed -->
                  </select>
                </div>
              </div> --}}
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <ul class="mb-3 border-0 nav nav-tabs" role="tablist">
                                @can('wallet_deposit:viewAny')
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'wallet_deposit'? 'active':''}}" data-type="wallet_deposit"
                                        href="{{route('admin.transactions.pending.wallet-deposit')}}" aria-selected="true">Wallet Deposit</a>
                                </li>
                                @endcan
                                @can('wallet_withdraw:viewAny')
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'wallet_withdrawal'? 'active':''}}"  data-type="wallet_withdrawal"
                                        href="{{route('admin.transactions.pending.wallet-withdrawal')}}" aria-selected="false">Wallet Withdrawal</a>
                                </li>
                                @endcan
                                @can('trade_deposit:viewAny')
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'trading_deposit'? 'active':''}}"  data-type="trading_deposit"
                                        href="{{route('admin.transactions.pending.trading-deposit')}}" aria-selected="false">Trading Deposit</a>
                                </li>
                                @endcan
                                @can('trade_withdrawals:viewAny')
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'trading_withdrawal'? 'active':''}}" data-bs-toggle="tab" data-type="trading_withdrawal" role="tab"
                                        href="#tradingwithdrawal" aria-selected="false">Trading
                                        Withdrawal</a>
                                </li>
                                @endcan

                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane text-muted {{$id == 'wallet_deposit'? 'active show':''}}" id="walletdeposit" role="tabpanel">
                                    <div class="table-responsive">

                                    </div>
                                </div>
                                <div class="tab-pane text-muted {{$id == 'wallet_withdrawal'? 'active show':''}}" id="walletwithdrawal" role="tabpanel">
                                    <div class="table-responsive">

                                    </div>
                                </div>
                                <div class="tab-pane text-muted {{$id == 'trading_deposit'? 'active show':''}}" id="tradingdeposit" role="tabpanel">
                                    <div class="table-responsive">

                                    </div>
                                </div>
                                <div class="tab-pane text-muted {{$id == 'trading_withdrawal'? 'active show':''}}" id="tradingwithdrawal" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tableTradingWithdrawal"
                                            class="table ajaxDataTable table-bordered text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Current Withdraw Amount</th>
                                                    <th>Withdraw Fee</th>
                                                    <th>Total Current Withdrawal Amount</th>
                                                    <th>Withdraw Type</th>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                    <th>Status</th>
                                                    <th>Withdraw From</th>
                                                    <th>Total Deposit</th>
                                                    <th>Total Withdrawal</th>
                                                    <th>Floating Balance</th>

                                                    <th>Balance</th>
                                                    <th>Withdraw From</th>
                                                    <th>Withdraw To Wallet</th>
                                                    <th>Withdraw Date</th>
                                                    <th>Actions</th>
                                                    <th>Email</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane text-muted {{$id == 'internal_transfer'? 'active':''}}" id="transaction5" role="tabpanel">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function () {

          var tableTradingWithdrawal = $('#tableTradingWithdrawal').DataTable({
            // order: [[0, "desc"]],
            dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',

            buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        filename: 'Pending_Trading_Withdrawal_' + new Date().toISOString().slice(0, 10),
                        exportOptions: {
                            columns: [0,18,2,3,4,5,6,7,8,9,10,11,12] // Updated column indices to match your use case
                        }
                    }
                ],

            // order: [[3, "desc"]],
            lengthMenu: [
                [10, 25, 50, 100, -1], // DataTable options
                [10, 25, 50, 100, "All"] // User-facing labels
                ],
            pageLength: 10,
            processing: true,
            serverSide: true,
            searching: true,
            ajax: {
                url: '/admin/getPendingTradingWithdrawal2',
                type: 'GET',
                data: function(d) {
                        d.status = $('select[name=status]').val();
                        return d;
                    },  // Ensure this is populated dynamically if needed.
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [
              { data: 'name', name: 'name' },
              { data: 'email', name: 'email'},
              { data: 'withdrawal_amount', name: 'withdrawal_amount' },
              { data: 'withdrawal_fee', name: 'withdrawal_fee'},
              { data: 'total_withdrawal', name: 'total_withdrawal'},
              { data: 'withdraw_type', name: 'withdraw_type' },
              { data: 'created_date', name: 'created_date'},
              { data: 'created_time', name: 'created_time'},
              { data: 'status', name: 'status' },
              { data: 'code', name: 'code' },
              { data: 'new_total_deposit', name: 'new_total_deposit' },
              { data: 'new_total_withdrawal', name: 'new_total_withdrawal' },
              { data: 'floating_balance', name: 'floating_balance' },

              { data: 'balance', name: 'balance', visible: false },
              { data: 'withdraw_from', name: 'withdraw_from', visible: false },
              { data: 'withdraw_to', name: 'withdraw_to', visible: false },
              { data: 'withdraw_date', name: 'withdraw_date', visible: false},
              { data: 'action', name: 'action', orderable: false, searchable: false },
              { data: 'client_email', name: 'client_email', visible: false},
            ]
          });

          $('#statusFilter').on('change', function () {

            tableTradingWithdrawal.ajax.reload();

          });
        });
      </script>

    @endpush
    @endsection
