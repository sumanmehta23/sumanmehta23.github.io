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
                                    <a class="nav-link {{$id == 'wallet_deposit'? 'active':''}}" data-type="wallet_deposit" data-bs-toggle="tab" role="tab"
                                        href="#walletdeposit" aria-selected="true">Wallet Deposit</a>
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
                                        href="{{route('admin.transactions.trading-deposit')}}" aria-selected="false">Trading
                                        Deposit</a>
                                </li>
                                @endcan
                                @can('trade_withdrawals:viewAny')
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'trading_withdrawal'? 'active':''}}"  data-type="trading_withdrawal"
                                        href="{{route('admin.transactions.trading-withdrawal')}}" aria-selected="false">Trading
                                        Withdrawal</a>
                                </li>
                                @endcan
                                @can('internal_transfer:viewAny')
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'internal_transfer'? 'active':''}}"  data-type="internal_transfer"
                                        href="{{route('admin.transactions.internal-transfer')}}" aria-selected="false">Internal
                                        Transfer</a>
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
          var tableWalletDeposit = $('#tableWalletDeposit').DataTable({
            dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',

            buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        exportOptions: {
                            columns: [6,7,1,2,4,8,9] // Updated column indices to match your use case
                        }
                    }
                ],

            order: [[3, "desc"]],
            processing: true,
            serverSide: true,
            searching: true,
            ajax: {
                url: '/admin/getWalletDeposit2',
                type: 'GET',
                data: function(d) {
                        d.status = $('select[name=status]').val();
                        return d;
                    }, // Ensure this is populated dynamically if needed.
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [
              {
                data: 'email', name: 'email',
                // render: function (data, row, row_data) {
                //   var return_data = "<a href='/admin/client_details/" + row_data.enc_id + "'><div class='d-flex align-items-center'><div class='me-2'><svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg></div><div><div class='lh-1'><span>" + row_data.fullname + "</span></div><div class='lh-1'><span class='fs-11 text-muted'>" + row_data.email + "</span></div></div></div></a>";
                //   return return_data;
                // }
              },
              { data: 'amount', name: 'amount' },
              { data: 'payment_mode', name: 'payment_mode' },
              {
                data: 'deposit_date', name: 'deposit_date',
                // render: function (data, type, row) {
                //   var dateTime = row.deposit_date.split(' ');
                //   var date = dateTime[0];
                //   var time = dateTime[1];
                //   var return_data = "<div class='d-grid'><div class='date'>" + date + "</div><div class='time text-muted'>" + time + "</div></div>";
                //   return return_data;
                // }
              },
              { data: 'status', name: 'status' },
              { data: 'action', name: 'action', orderable: false, searchable: false },
              { data: 'fullname', name: 'fullname', visible: false },
              { data: 'fullemail', name: 'fullemail', visible: false},
              { data: 'created_date', name: 'created_date', visible: false},
              { data: 'created_time', name: 'created_time', visible: false},
            ]
          });

          $('#statusFilter').on('change', function () {
            tableWalletDeposit.ajax.reload();

          });
        });
      </script>

    @endpush
    @endsection
