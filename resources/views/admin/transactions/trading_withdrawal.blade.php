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
            <div class="mb-3 row">
                <div class="col-md-4">
                  <label for="statusFilter">Filter by Status:</label>
                  <select id="statusFilter" class="form-select" name="status">
                    <option value="">All</option>
                    <option value="1">Approved</option>
                    <option value="2">Rejected</option>
                    <option value="3">Cancelled By User</option>
                    <option value="0">Pending</option>
                    <!-- Add other status options as needed -->
                  </select>
                </div>
              </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <ul class="mb-3 border-0 nav nav-tabs" role="tablist">
                                @can('wallet_deposit:viewAny')
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'wallet_deposit'? 'active':''}}" data-type="wallet_deposit"
                                        href="{{route('admin.transactions.wallet-deposit')}}" aria-selected="true">Wallet Deposit</a>
                                </li>
                                @endcan
                                @can('wallet_withdraw:viewAny')
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'wallet_withdrawal'? 'active':''}}"  data-type="wallet_withdrawal"
                                        href="{{route('admin.transactions.wallet-withdrawal')}}" aria-selected="false">Wallet Withdrawal</a>
                                </li>
                                @endcan
                                @can('trade_deposit:viewAny')
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'trading_deposit'? 'active':''}}"  data-type="trading_deposit"
                                        href="{{route('admin.transactions.trading-deposit')}}" aria-selected="false">Trading Deposit</a>
                                </li>
                                @endcan
                                @can('trade_withdrawals:viewAny')
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'trading_withdrawal'? 'active':''}}" data-bs-toggle="tab" data-type="trading_withdrawal" role="tab"
                                        href="#tradingwithdrawal" aria-selected="false">Trading
                                        Withdrawal</a>
                                </li>
                                @endcan
                                @can('internal_transfer:viewAny')
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'internal_transfer'? 'active':''}}" data-type="internal_transfer"
                                        href="{{route('admin.transactions.internal-transfer')}}" aria-selected="false">Internal
                                        Transfer</a>
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
                                                    <th>Account No</th>
                                                    <th>Withdrawal Amount</th>
                                                    <th>Withdrawal Fee</th>
                                                    <th>Withdraw Type</th>
                                                    <th>Withdraw To</th>
                                                    <th>Withdraw Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                    <th>Date</th>
                                                    <th>Time</th>
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
            //   buttons: [
            //         {
            //             extend: 'excel',
            //             text: 'Export to Excel',
            //         }
            //     ],

            // order: [
            //   [0, "desc"]
            // ],

            // "ajax": {
            //   "url": "/admin/ajax",
            //   "type": "GET",
            //   data: {
            //     action: 'getTradingWithdrawal',
            //   },
            // },
            buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        filename: 'Trading_Withdrawal_' + new Date().toISOString().slice(0, 10),
                        exportOptions: {
                            columns: [0,1,2,3,5,7,8] // Updated column indices to match your use case
                        }
                    }
                ],

            order: [[0, "desc"]],
            processing: true,
            serverSide: true,
            searching: true,
            ajax: {
                url: '/admin/getTradingWithdrawal2',
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
              { data: 'code', name: 'code' },
              { data: 'withdrawal_amount', name: 'withdrawal_amount' },
              { data: 'transaction_fee', name: 'transaction_fee' },
              { data: 'withdraw_type', name: 'withdraw_type' },
              { data: 'withdraw_to', name: 'withdraw_to' },
              {
                data: 'withdraw_date', name: 'withdraw_date',
                // render: function (data, type, row) {
                //   var dateTime = row.withdraw_date.split(' ');
                //   var date = dateTime[0];
                //   var time = dateTime[1];
                //   var return_data = "<div class='d-grid'><div class='date'>" + date + "</div><div class='time text-muted'>" + time + "</div></div>";
                //   return return_data;
                // }
              },
              { data: 'status', name: 'status' },
              { data: 'action', name: 'action', orderable: false, searchable: false },
              { data: 'created_date', name: 'created_date',orderable: false, visible: false},
              { data: 'created_time', name: 'created_time',orderable: false, visible: false},
            ]
          });

          $('#statusFilter').on('change', function () {

            tableTradingWithdrawal.ajax.reload();

          });
        });
      </script>

    @endpush
    @endsection
