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
                <div class="col-md-4">
                    <label for="typeFilter">Filter by Transfer Type:</label>
                    <select id="typeFilter" class="form-select" name="type">
                      <option value="">All</option>
                      <option value="CRM">CRM</option>
                      <option value="Internal Transfer">Internal Transfer</option>
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
                                    <a class="nav-link {{$id == 'trading_withdrawal'? 'active':''}}"  data-type="trading_withdrawal"
                                        href="{{route('admin.transactions.trading-withdrawal')}}" aria-selected="false">Trading
                                        Withdrawal</a>
                                </li>
                                @endcan
                                @can('internal_transfer:viewAny')
                                <li class="nav-item">
                                    <a class="nav-link {{$id == 'internal_transfer'? 'active':''}}" data-bs-toggle="tab" data-type="internal_transfer" role="tab"
                                        href="#transaction5" aria-selected="false">Internal Transfer</a>
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

                                    </div>
                                </div>
                                <div class="tab-pane text-muted {{$id == 'internal_transfer'? 'active':''}}" id="transaction5" role="tabpanel">
                                    <input type="text" hidden id='client_id' value="{{ ($clientId) }}">
                                    <table id="tableInternalTransfer"
                                        class="table ajaxDataTable table-bordered text-nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Amount</th>
                                                <th>Transfer From</th>
                                                <th>Transfer To</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Created At</th>
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
          var clientId = $('#client_id').val();
          var tableInternalTransfer = $('#tableInternalTransfer').DataTable({
            // order: [[0, "desc"]],
            dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',

            buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        exportOptions: {
                            columns: [0,1,2,3,4,6,7] // Updated column indices to match your use case
                        }
                    },
                    {
                        text: 'Export All',
                        action: function () {
                            window.location.href = "/admin/export-all-internal-transfer";
                        }
                    }
                ],

            order: [[3, "desc"]],
            lengthMenu: [
                [10, 25, 50, 100, 500, 1000], // DataTable options
                [10, 25, 50, 100, 500, 1000] // User-facing labels
                ],
            processing: true,
            serverSide: true,
            searching: true,
            ajax: {
                url: '/admin/getInternalTransfer2',
                type: 'GET',
                data: function(d) {
                        d.status = $('select[name=status]').val();
                        d.type = $('select[name=type]').val();
                        d.clientId = clientId??'';
                        return d;
                    },  // Ensure this is populated dynamically if needed.
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [
              { data: 'name', name: 'name' },
              { data: 'email', name: 'email' },
              { data: 'amount', name: 'amount' },
              { data: 'transfer_from', name: 'transfer_from' },
              { data: 'transfer_to', name: 'transfer_to' },
              { data: 'date', name: 'date' },
              { data: 'status', name: 'status' },
              { data: 'created_at', name: 'created_at', visible:false },
              // { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
          });
          $('#statusFilter').on('change', function () {
            tableInternalTransfer.ajax.reload();
          });
          $('#typeFilter').on('change', function () {
            tableInternalTransfer.ajax.reload();
          });
        });
      </script>

    @endpush
    @endsection
