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
                                    <a class="nav-link {{$id == 'trading_deposit'? 'active':''}}" data-bs-toggle="tab" data-type="trading_deposit" role="tab"
                                        href="#tradingdeposit" aria-selected="false">Trading Deposit</a>
                                </li>
                                @endcan
                                @can('trade_withdrawals:viewAny')
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'trading_withdrawal'? 'active':''}}"  data-type="trading_withdrawal"
                                        href="{{route('admin.transactions.pending.trading-withdrawal')}}" aria-selected="false">Trading
                                        Withdrawal</a>
                                </li>
                                @endcan

                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane text-muted {{$id == 'wallet_deposit'? 'active show':''}}" id="walletdeposit" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tableWalletDeposit"
                                            class="table ajaxDataTable table-bordered text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Email</th>
                                                    <th>Amount</th>
                                                    <th>Payment Mode</th>
                                                    <th>Deposit Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane text-muted {{$id == 'wallet_withdrawal'? 'active show':''}}" id="walletwithdrawal" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tableWalletWithdrawal"
                                            class="table ajaxDataTable table-bordered text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Account No</th>
                                                    <th>Withdrawal Amount</th>
                                                    <th>Withdrawal Fee</th>
                                                    <th>Withdraw To</th>
                                                    <th>Withdraw Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane text-muted {{$id == 'trading_deposit'? 'active show':''}}" id="tradingdeposit" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tableTradingDeposit"
                                            class="table ajaxDataTable table-bordered text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    {{-- <th>#</th> --}}
                                                    <th>Account No</th>
                                                    <th>Deposit Amount</th>
                                                    <th>Deposit Type</th>
                                                    <th>Deposit From</th>
                                                    <th>Deposited Date</th>
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
                                <div class="tab-pane text-muted {{$id == 'trading_withdrawal'? 'active show':''}}" id="tradingwithdrawal" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tableTradingWithdrawal"
                                            class="table ajaxDataTable table-bordered text-nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Account No</th>
                                                    <th>Withdrawal Amount</th>
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
                                    <table id="tableInternalTransfer"
                                        class="table ajaxDataTable table-bordered text-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Amount</th>
                                                <th>Transfer From</th>
                                                <th>Transfer To</th>
                                                <th>Status</th>
                                                <!-- <th>Actions</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
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

          var tableTradingDeposit = $('#tableTradingDeposit').DataTable({
            // order: [[0, "desc"]],
            dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',

            buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        filename: 'Pending_Trading_Deposit_' + new Date().toISOString().slice(0, 10),
                        exportOptions: {
                            columns: [0,1,2,3,4,8,9,6] // Updated column indices to match your use case
                        }
                    }
                ],

            order: [[3, "desc"]],
            processing: true,
            serverSide: true,
            searching: true,
            ajax: {
                url: '/admin/getPendingTradingDeposit2',
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
            //   { data: 'id', name: '#' },
              { data: 'code', name: 'code' },
              { data: 'deposit_amount', name: 'deposit_amount'},
              { data: 'deposit_type', name: 'deposit_type' ,searchable: false},
              { data: 'deposit_from', name: 'deposit_from' ,searchable: false},
              {
                data: 'deposit_date', name: 'deposit_date',searchable: false

              },
              { data: 'status', name: 'status',searchable: false },
              { data: 'action', name: 'action', orderable: false, searchable: false },
              { data: 'created_date', name: 'created_date', visible: false},
              { data: 'created_time', name: 'created_time', visible: false},
            ]
          });

          $('#statusFilter').on('change', function () {

            tableTradingDeposit.ajax.reload();
          });
        });
      </script>

    @endpush
    @endsection
