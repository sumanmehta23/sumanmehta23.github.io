@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Home</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Home</li>
                </ol>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-5 col-xl-4">
                        <div class="card custom-card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <div class="userprofile">
                                                        <div class="avatar userpic avatar-rounded">
                                                            <img src="{{ asset('admin_assets/assets/images/users/client.jpeg') }}"
                                                                alt="img" style="width:100px">
                                                        </div>
                                                        <h3 class="username mb-2">{{ $rm_details->username }}</h3>
                                                        <p class="mb-1 text-muted">{{ $rm_details->email }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="card bg-primary img-card box-primary-shadow">
                                                        <div class="card-body">
                                                            <div class="d-flex">
                                                                <div>
                                                                    <h2 class="mb-0 number-font text-fixed-white">
                                                                        {{ $pending_wd + $pending_td }}
                                                                    </h2>
                                                                    <p class="text-fixed-white mb-0">Pending Deposits</p>
                                                                </div>
                                                                <div class="ms-auto">
                                                                    <i
                                                                        class="fa fa-bank text-fixed-white fs-30 me-2 mt-2"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="card bg-secondary img-card box-secondary-shadow">
                                                        <div class="card-body">
                                                            <div class="d-flex">
                                                                <div>
                                                                    <h2 class="mb-0 number-font text-fixed-white">
                                                                        {{ $pending_tw + $pending_ww }}
                                                                    </h2>
                                                                    <p class="mb-0 text-fixed-white">Pending Withdraw</p>
                                                                </div>
                                                                <div class="ms-auto">
                                                                    <i
                                                                        class="fa fa-usd text-fixed-white fs-30 me-2 mt-2"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-12">
                                                    <div class="card bg-success img-card box-success-shadow">
                                                        <div class="card-body">
                                                            <div class="d-flex">
                                                                <div>
                                                                    <h2 class="mb-0 number-font text-fixed-white">
                                                                        {{ $pending_ib }}
                                                                    </h2>
                                                                    <p class="text-fixed-white mb-0">Pending IB Requests</p>
                                                                </div>
                                                                <div class="ms-auto">
                                                                    <i
                                                                        class="fa fa-dollar text-fixed-white fs-30 me-2 mt-2"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-12">
                                                    <div class="card bg-info img-card box-info-shadow">
                                                        <div class="card-body">
                                                            <div class="d-flex">
                                                                <div>
                                                                    <h2 class="mb-0 number-font text-fixed-white">
                                                                        {{ $wallet_users }}
                                                                    </h2>
                                                                    <p class="text-fixed-white mb-0">Activated Wallets</p>
                                                                </div>
                                                                <div class="ms-auto">
                                                                    <i
                                                                        class="ri-wallet-3-fill text-fixed-white fs-30 me-2 mt-2"></i>
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
                        </div>
                    </div>
                    <div class="col-xl-8">
                        <div class="row">
                            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <div class="card-order">
                                            <h6 class="mb-2">Total Deposit</h6>
                                            <h2 class="text-end h3">
                                                <i
                                                    class="mdi mdi-wallet icon-size float-start text-primary text-primary-shadow"></i>
                                                <span>${{ $trade_deposit + $wallet_deposit }}</span>
                                            </h2>
                                            <p class="mb-0">Trading Deposit<span
                                                    class="float-end">${{ $trade_deposit }}</span></p>
                                            <p class="mb-0">Wallet Deposit<span
                                                    class="float-end">${{ $wallet_deposit }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- COL END -->
                            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <div class="card-widget">
                                            <h6 class="mb-2">Total Withdraw</h6>
                                            <h2 class="text-end h3">
                                                <i
                                                    class="mdi mdi-credit-card icon-size float-start text-success text-success-shadow"></i>
                                                <span>${{ $trade_withdrawal + $wallet_withdrawal }}</span>
                                            </h2>
                                            <p class="mb-0">Trading Withdrawal<span
                                                    class="float-end">${{ $trade_withdrawal }}</span></p>
                                            <p class="mb-0">Wallet Withdrawal<span
                                                    class="float-end">${{ $wallet_withdrawal }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- COL END -->
                            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <div class="card-widget">
                                            <h6 class="mb-2">Active Clients</h6>
                                            <h2 class="text-end h3">
                                                <i
                                                    class="icon-size mdi mdi-account-multiple float-start text-warning text-warning-shadow"></i>
                                                <span>{{ $total_clients['active_users'] + $total_clients['inactive_users'] }}</span>
                                            </h2>
                                            <p class="mb-0">Active Users<span
                                                    class="float-end">{{ $total_clients['active_users'] }}</span></p>
                                            <p class="mb-0">Inactive Users<span
                                                    class="float-end">{{ $total_clients['inactive_users'] }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- COL END -->
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="card custom-card">
                                    <div class="card-header justify-content-between">
                                        <div class="card-title">Client List</div>
                                        <div class="prism-toggle"></div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="ajaxDatatable"
                                                class="tableClient ajaxDataTable table table-bordered text-nowrap w-100">
                                                <thead>
                                                    <tr>
                                                        <th>#CID</th>
                                                        <th>Joined On</th>
                                                        <th>Name/Email</th>
                                                        <th>Country</th>
                                                        <th>Action</th>
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
                <div class="row">
                    <div class="col-6">
                        <div class="card custom-card product-sales">
                            <div class="card-header">
                                <div class="card-title d-flex justify-content-between mb-0 w-100">
                                    <div>
                                        Latest Pending Deposit
                                    </div>
                                    <div>
                                        <a href="/admin/transactions/wallet_deposit"
                                            class="btn btn-primary-light">View All</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom">
                                        <thead class="border-top">
                                            <tr>
                                                <th>#</th>
                                                <th>Client</th>
                                                <th>Deposit To</th>
                                                <th>Amount</th>
                                                <th>Payment Mode</th>
                                                <th>Deposit Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if ($latest_pending_deposit->count() > 0)
                                                @foreach ($latest_pending_deposit as $deposit)
                                                    <tr>
                                                        <td>
                                                            <div>{{ htmlentities($deposit->id) }}</div>
                                                        </td>
                                                        <td>
                                                            <a
                                                                href="{{ url('/admin/client_details?id=' . md5($deposit->email)) }}">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            width="28" height="28"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="#000000" stroke-width="1.5"
                                                                            stroke-linecap="round" stroke-linejoin="round"
                                                                            size="28" color="#000000"
                                                                            class="tabler-icon tabler-icon-user-square-rounded">
                                                                            <path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z">
                                                                            </path>
                                                                            <path
                                                                                d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z">
                                                                            </path>
                                                                            <path
                                                                                d="M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05">
                                                                            </path>
                                                                        </svg>
                                                                    </div>
                                                                    <div>
                                                                        <div class="lh-1">
                                                                            <span>{{ htmlentities($deposit->fullname) }}</span>
                                                                        </div>
                                                                        <div class="lh-1">
                                                                            <span
                                                                                class="fs-11 text-muted">{{ htmlentities($deposit->email) }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            @if ($deposit->trade_id == 'email')
                                                                WALLET
                                                            @else
                                                                <a
                                                                    href="{{ url('/admin/view_account_details?id=' . md5($deposit->trade_id)) }}">
                                                                    <div class="btn btn-toolbar row">
                                                                        <div class="col-auto pe-0 ps-0">
                                                                            <img src="/assets/images/mt5.png"
                                                                                alt="user-image" style="width: 25px;">
                                                                        </div>
                                                                        <div class="col">
                                                                            <h4 class="mb-2 text-start">
                                                                                <span>{{ $deposit->trade_id }}</span>
                                                                            </h4>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="amount">
                                                                ${{ htmlentities($deposit->deposit_amount) }}</div>
                                                        </td>
                                                        <td>
                                                            <div>{{ htmlentities($deposit->deposit_type) }}</div>
                                                        </td>
                                                        <td>
                                                            <div class="lh-1">
                                                                {{ date('Y-m-d', strtotime($deposit->deposit_date)) }}
                                                            </div>
                                                            <div class="lh-2 text-muted">
                                                                {{ date('H:i:s', strtotime($deposit->deposit_date)) }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="mt-sm-1 d-block">
                                                                @if ($deposit->status == 1)
                                                                    <div
                                                                        class="badge bg-success-transparent text-success p-2 px-3 rounded-pill">
                                                                        Approved</div>
                                                                @elseif($deposit->status == 2)
                                                                    <div
                                                                        class="badge bg-danger-transparent text-danger p-2 px-3 rounded-pill">
                                                                        Rejected</div>
                                                                @elseif($deposit->status == 0)
                                                                    <div
                                                                        class="badge bg-primary-transparent text-primary p-2 px-3 rounded-pill">
                                                                        Pending</div>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                @if ($deposit->TYPE == 'wallet')
                                                                    <a href="{{ url('/admin/wallet_deposit_details?id=' . htmlentities(md5($deposit->raw_id)) . '&email=' . htmlentities($deposit->email) . '&deposit=' . htmlentities($deposit->deposit_amount)) }}"
                                                                        class=""
                                                                        style="font-size: 13px; padding: 2px 20px;">
                                                                        <i class="fe fe-eye fs-14 text-info"></i>
                                                                    </a>
                                                                @else
                                                                    <a href="{{ url('/admin/trading_deposit_details?id=' . htmlentities(md5($deposit->raw_id)) . '&email=' . htmlentities($deposit->email) . '&deposit=' . htmlentities($deposit->deposit_amount)) }}"
                                                                        class=""
                                                                        style="font-size: 13px; padding: 2px 20px;">
                                                                        <i class="fe fe-eye fs-14 text-info"></i>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card custom-card product-sales">
                            <div class="card-header">
                                <div class="card-title d-flex justify-content-between mb-0 w-100">
                                    <div>
                                        Latest Pending Withdrawals
                                    </div>
                                    <div>
                                        <a href="/admin/transactions/wallet_deposit"
                                            class="btn btn-primary-light">View All</a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom">
                                        <thead class="border-top">
                                            <tr>
                                                <th>#</th>
                                                <th>Client</th>
                                                <th>Withdrawal From</th>
                                                <th>Amount</th>
                                                <th>Mode</th>
                                                <th>Withdraw Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if ($latest_pending_withdrawal->isNotEmpty())
                                                @foreach ($latest_pending_withdrawal as $result)
                                                    <tr>
                                                        <td>
                                                            <div>{{ $result->id }}</div>
                                                        </td>
                                                        <td>
                                                            <a
                                                                href="{{ url('/admin/client_details?id=' . md5($result->email)) }}">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-2">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            width="28" height="28"
                                                                            viewBox="0 0 24 24" fill="none"
                                                                            stroke="#000000" stroke-width="1.5"
                                                                            stroke-linecap="round" stroke-linejoin="round"
                                                                            size="28" color="#000000"
                                                                            class="tabler-icon tabler-icon-user-square-rounded">
                                                                            <path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z">
                                                                            </path>
                                                                            <path
                                                                                d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z">
                                                                            </path>
                                                                            <path
                                                                                d="M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05">
                                                                            </path>
                                                                        </svg>
                                                                    </div>
                                                                    <div>
                                                                        <div class="lh-1">
                                                                            <span>{{ $result->fullname }}</span>
                                                                        </div>
                                                                        <div class="lh-1">
                                                                            <span
                                                                                class="fs-11 text-muted">{{ $result->email }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            @if ($result->trade_id == 'email')
                                                                WALLET
                                                            @else
                                                                <a
                                                                    href="{{ url('/admin/view_account_details?id=' . md5($result->trade_id)) }}">
                                                                    <div class="btn btn-toolbar row">
                                                                        <div class="col-auto pe-0 ps-0">
                                                                            <img src="/assets/images/mt5.png"
                                                                                alt="user-image" style="width: 25px;">
                                                                        </div>
                                                                        <div class="col">
                                                                            <h4 class="mb-2 text-start">
                                                                                <span>{{ $result->trade_id }}</span></h4>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="amount">
                                                                $ {{ $result->withdraw_amount }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>{{ $result->withdraw_type }}</div>
                                                        </td>
                                                        <td>
                                                            <div class="lh-1">
                                                                {{ date('Y-m-d', strtotime($result->withdraw_date)) }}
                                                            </div>
                                                            <div class="lh-2 text-muted">
                                                                {{ date('H:i:s', strtotime($result->withdraw_date)) }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="mt-sm-1 d-block">
                                                                @switch($result->status)
                                                                    @case(1)
                                                                        <div
                                                                            class="badge bg-success-transparent text-success p-2 px-3 rounded-pill">
                                                                            Success</div>
                                                                    @break

                                                                    @case(2)
                                                                        <div
                                                                            class="badge bg-danger-transparent text-danger p-2 px-3 rounded-pill">
                                                                            Cancelled</div>
                                                                    @break

                                                                    @case(0)
                                                                        <div
                                                                            class="badge bg-primary-transparent text-primary p-2 px-3 rounded-pill">
                                                                            Pending</div>
                                                                    @break
                                                                @endswitch
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if ($result->type == 'trade')
                                                                <div>
                                                                    <a href="{{ url('/admin/trading_withdrawal_details?id=' . $result->raw_id . '&email=' . $result->email) }}"
                                                                        class=""
                                                                        style="font-size: 13px; padding: 2px 20px;">
                                                                        <i class="fe fe-eye fs-14 text-info"></i>
                                                                    </a>
                                                                </div>
                                                            @else
                                                                <div>
                                                                    <a href="{{ url('/admin/wallet_withdrawal_details?id=' . $result->raw_id . '&email=' . $result->email . '&withdraw=' . $result->withdraw_amount) }}"
                                                                        class=""
                                                                        style="font-size: 13px; padding: 2px 20px;">
                                                                        <i class="fe fe-eye fs-14 text-info"></i>
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
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
    <script>
        var rm_id = <?php echo json_encode($rm_email_enc); ?>;
        window.dTtable = $('.tableClient').DataTable({
          dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
          buttons: [
          ],
          order: [
            [0, "desc"]
          ],
          "ajax": {
            "url": "/admin/ajax",
            "type": "GET",
            data: {
              action: 'getClientList',
              rm_id: rm_id
            },
          },
          columns: [{
            data: 'id',
            name: 'id'
          },
          {
            data: 'created_at',
            name: 'created_at',
            render: function (data, type, row) {
              var return_data = "<div class='d-grid'><div class='date'>" + row.created_date + "</div><div class='time text-muted'>" + row.created_time + "</div></div>";
              return return_data;
            }
          },
          {
            data: 'email',
            name: 'email',
            render: function (data, row, row_data) {
              var return_data = "<a href='/admin/client_details?id=" + row_data.enc_id + "'><div class='d-flex align-items-center'><div class='me-2'><svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg></div><div><div class='lh-1'><span>" + row_data.fullname + "</span></div><div class='lh-1'><span class='fs-11 text-muted'>" + row_data.email + "</span></div></div></div></a>";
              return return_data;
            }
          },
          {
            data: 'country',
            name: 'country',
            render: function (data, row, row_data) {
              return '<span class="fi fis fi-' + data+ ' me-2"></span>' + data;
            }
          },
          {
            data: 'status',
            name: 'status',
            render: function (data, row, row_data) {
              let html = '';
              html += '<a href="/admin/client_details?id=' + row_data.enc_id + '"><span class="badge text-danger" data-bs-toggle="tooltip" title="View Client"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-eye"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg></span></a>';
              return html;
            }
          },
          ]
        });

      </script>
@endsection
