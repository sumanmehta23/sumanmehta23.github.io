@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Home</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Home</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-1 -->

            <div class="row">
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="card-order">
                                <h6 class="mb-2">Total Deposit</h6>
                                <h2 class="text-end "><i
                                        class="mdi mdi-wallet icon-size float-start text-primary text-primary-shadow"></i><span>$
                                        {{ number_format($trade_deposit->deposit + $wallet_deposit->deposit,2) }}</span>
                                </h2></span>
                                <p class="mb-0">Trading Deposit<span class="float-end">${{ number_format($trade_deposit->deposit,2) }}
                                    </span></p>
                                <p class="mb-0">Wallet Deposit<span
                                        class="float-end">${{ number_format($wallet_deposit->deposit,2) }}</span></p>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="card-widget">
                                <h6 class="mb-2">Total Withdraw</h6>
                                <h2 class="text-end"><i
                                        class="mdi mdi-credit-card icon-size float-start text-success text-success-shadow"></i><span>${{ number_format($wallet_withdrawal->withdraw,2) }}</span>
                                </h2>
                                <p class="mb-0">Trading Withdrawal<span
                                        class="float-end">${{ number_format($trade_withdrawal->withdraw,2) }}</span></p>
                                <p class="mb-0">Wallet Withdrawal<span
                                        class="float-end">${{ number_format($wallet_withdrawal->withdraw,2) }}</span></p>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="card-widget">
                                <h6 class="mb-2">Active Clients</h6>
                                <h2 class="text-end"><i
                                        class="icon-size mdi mdi-account-multiple float-start text-warning text-warning-shadow"></i><span>{{ number_format($total_clients->active_users + $total_clients->inactive_users) }}</span>
                                </h2>
                                <p class="mb-0">Active Users<span
                                        class="float-end">{{ number_format($total_clients->active_users) }}</span></p>
                                <p class="mb-0">Inactive Users<span
                                        class="float-end">{{ number_format($total_clients->inactive_users) }}</span></p>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
            </div>
            <!-- ROW-1 END -->
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                    <div class="card bg-primary img-card box-primary-shadow">
                        <div class="card-body">
                            <div class="d-flex">
                                <div>
                                    {{-- {{ dump($pending_wd); dd($pending_td); }} --}}
                                    <h2 class="mb-0 number-font text-fixed-white">
                                        {{ $pending_wd->counts + $pending_td->counts }}</h2>
                                    <p class="mb-0 text-fixed-white">Pending Deposits</p>
                                </div>
                                <div class="ms-auto"> <i class="mt-2 fa fa-bank text-fixed-white fs-30 me-2"></i> </div>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                    <div class="card bg-secondary img-card box-secondary-shadow">
                        <div class="card-body">
                            <div class="d-flex">
                                <div>
                                    {{-- {{ dump($pending_tw); dd($pending_ww); }} --}}
                                    <h2 class="mb-0 number-font text-fixed-white">
                                        {{ $pending_ww->counts }}</h2>
                                    <p class="mb-0 text-fixed-white">Pending Withdraw</p>
                                </div>
                                <div class="ms-auto"> <i class="mt-2 fa fa-usd text-fixed-white fs-30 me-2"></i> </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                    <div class="card bg-success img-card box-success-shadow">
                        <div class="card-body">
                            <div class="d-flex">
                                <div>
                                    <h2 class="mb-0 number-font text-fixed-white">{{ $pending_ib->counts }}</h2>
                                    <p class="mb-0 text-fixed-white">Pending IB Requests</p>
                                </div>
                                <div class="ms-auto"> <i class="mt-2 fa fa-dollar text-fixed-white fs-30 me-2"></i> </div>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                    <div class="card bg-info img-card box-info-shadow">
                        <div class="card-body">
                            <div class="d-flex">
                                <div>
                                    <h2 class="mb-0 number-font text-fixed-white">{{ $wallet_users->counts }}</h2>
                                    <p class="mb-0 text-fixed-white">Activated Wallets</p>
                                </div>
                                <div class="ms-auto"> <i class="mt-2 ri-wallet-3-fill text-fixed-white fs-30 me-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
            </div>
            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card custom-card product-sales">
                        <div class="card-header">
                            <div class="mb-0 card-title d-flex justify-content-between w-100">
                                <div>
                                    Latest Pending Deposit
                                </div>
                                <div>
                                    <a href="/admin/transactions/wallet_deposit" class="btn btn-primary-light">View
                                        All</a>
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
                                            <th>Payment Mode</th>
                                            <th>Deposit Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                    $cnt = 1;
                    if (count($results) > 0) {
                      foreach ($results as $result) {
                        ?>
                                        <tr>
                                            <td>
                                                <div><?php echo htmlentities($result->id); ?></div>
                                            </td>
                                            <td>
                                                <a href="/admin/client_details?id={{ ($result->email) }}">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="28"
                                                                height="28" viewBox="0 0 24 24" fill="none"
                                                                stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                                                stroke-linejoin="round" size="28" color="#000000"
                                                                class="tabler-icon tabler-icon-user-square-rounded">
                                                                <path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z"></path>
                                                                <path
                                                                    d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z">
                                                                </path>
                                                                <path d="M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <div class="lh-1">
                                                                <span><?php echo htmlentities($result->fullname); ?></span>
                                                            </div>
                                                            <div class="lh-1">
                                                                <span class="fs-11 text-muted"><?php echo htmlentities($result->email); ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($result->code == 'email') { ?>
                                                <a href="/admin/client_details?id={{ ($result->email) }}">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="28"
                                                                height="28" viewBox="0 0 24 24" fill="none"
                                                                stroke="#000000" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                size="28" color="#000000"
                                                                class="tabler-icon tabler-icon-user-square-rounded">
                                                                <path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z"></path>
                                                                <path
                                                                    d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z">
                                                                </path>
                                                                <path d="M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <div class="lh-1">
                                                                <span><?php echo htmlentities($result->fullname); ?></span>
                                                            </div>
                                                            <div class="lh-1">
                                                                <span class="fs-11 text-muted"><?php echo htmlentities($result->email); ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                                <?php } else { ?>

                                                <a href="{{ route('admin-view-account-details',$result->id) }}">
                                                    <div class="btn btn-toolbar row">
                                                        <div class="col-auto pe-0 ps-0"><img src="/assets/images/mt5.png"
                                                                alt="user-image" class="" style="width: 25px;">
                                                        </div>
                                                        <div class="col">
                                                            <h4 class="mb-2 text-start"><span
                                                                    class="">{{ $result->code }}</span>
                                                            </h4>
                                                        </div>
                                                    </div>
                                                </a>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class="amount">
                                                    $ <?php echo htmlentities($result->deposit_amount); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div><?php echo htmlentities($result->deposit_type); ?></div>
                                            </td>
                                            <td>
                                                <div class="lh-1">{{ date('Y-m-d', strtotime($result->deposit_date)) }}
                                                </div>
                                                <div class="lh-2 text-muted">
                                                    {{ date('H:i:s', strtotime($result->deposit_date)) }}</div>
                                            <td>
                                                <div class="mt-sm-1 d-block">
                                                    <!-- <span class="p-2 px-3 badge bg-success-transparent rounded-pill text-success">Shipped</span> -->
                                                    <?php
                              $stats = $result->status;
                              if ($stats == 1) {
                              ?>
                                                    <div
                                                        class="p-2 px-3 badge bg-success-transparent text-success rounded-pill ">
                                                        Approved</div>
                                                    <?php }
                              if ($stats == 2) { ?>
                                                    <div
                                                        class="p-2 px-3 badge bg-danger-transparent text-danger rounded-pill ">
                                                        Rejected</div>
                                                    <?php }

                              if ($stats == 0) { ?>
                                                    <div
                                                        class="p-2 px-3 badge bg-primary-transparent text-primary rounded-pill ">
                                                        Pending</div>
                                                    <?php
                              } ?>
                                                </div>

                                            </td>
                                            <td>
                                                <?php if ($result->TYPE == "wallet") { ?>
                                                <div>
                                                    <a href="/admin/wallet_deposit_details?id=<?php echo htmlentities(($result->raw_id)); ?>&email=<?php echo htmlentities($result->email); ?>&deposit=<?php echo htmlentities($result->deposit_amount); ?>"
                                                        class="" style="font-size: 13px;padding: 2px 20px;">
                                                        <i class="fe fe-eye fs-14 text-info"></i>
                                                    </a>
                                                </div>
                                                <?php } else { ?>
                                                <div>
                                                    <a href="/admin/trading_deposit_details?id=<?php echo htmlentities(($result->raw_id)); ?>&email=<?php echo htmlentities($result->email); ?>&deposit=<?php echo htmlentities($result->deposit_amount); ?>"
                                                        class="" style="font-size: 13px;padding: 2px 20px;">
                                                        <i class="fe fe-eye fs-14 text-info"></i>
                                                    </a>
                                                </div>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php }
                    } ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card custom-card product-sales">
                        <div class="card-header">
                            <div class="mb-0 card-title d-flex justify-content-between w-100">
                                <div>
                                    Latest Pending Withdrawals
                                </div>
                                <div>
                                    <a href="/admin/transactions/wallet_deposit" class="btn btn-primary-light">View
                                        All</a>
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
                                            <th>Mode</th>
                                            <th>Withdraw Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                    $cnt = 1;
                    if (count($wallet_withdraws) > 0) {
                      foreach ($wallet_withdraws as $result) {
                    ?>
                                        <tr>
                                            <td>
                                                <div><?php echo htmlentities($result->id); ?></div>
                                            </td>
                                            <td>
                                                <a href="/admin/client_details?id={{ ($result->email) }}">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="28"
                                                                height="28" viewBox="0 0 24 24" fill="none"
                                                                stroke="#000000" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                size="28" color="#000000"
                                                                class="tabler-icon tabler-icon-user-square-rounded">
                                                                <path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z"></path>
                                                                <path
                                                                    d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z">
                                                                </path>
                                                                <path d="M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <div class="lh-1">
                                                                <span><?php echo htmlentities($result->fullname); ?></span>
                                                            </div>
                                                            <div class="lh-1">
                                                                <span class="fs-11 text-muted"><?php echo htmlentities($result->email); ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($result->code == 'email') { ?>
                                                <a href="/admin/client_details?id={{ ($result->email) }}">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="28"
                                                                height="28" viewBox="0 0 24 24" fill="none"
                                                                stroke="#000000" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                size="28" color="#000000"
                                                                class="tabler-icon tabler-icon-user-square-rounded">
                                                                <path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z"></path>
                                                                <path
                                                                    d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z">
                                                                </path>
                                                                <path d="M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <div class="lh-1">
                                                                <span><?php echo htmlentities($result->fullname); ?></span>
                                                            </div>
                                                            <div class="lh-1">
                                                                <span class="fs-11 text-muted"><?php echo htmlentities($result->email); ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                                <?php } else { ?>
                                                <a href="{{route('view-account-details',$result->id) }}">
                                                    <div class="btn btn-toolbar row">
                                                        <div class="col-auto pe-0 ps-0"><img src="/assets/images/mt5.png"
                                                                alt="user-image" class="" style="width: 25px;">
                                                        </div>
                                                        <div class="col">
                                                            <h4 class="mb-2 text-start"><span
                                                                    class="">{{ $result->code }}</span>
                                                            </h4>
                                                        </div>
                                                    </div>
                                                </a>
                                                <!-- <div class="row">
                                        <div class="col-auto pe-0"><img src="/assets/images/mt5.png" alt="user-image" class="" style="width: 25px;"></div>
                                        <div class="col">
                                          <h4 class="mb-2"><span class="text-truncate w-100"></span>
                                          </h4>
                                        </div>
                                      </div> -->
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class="amount">
                                                    $ <?php echo htmlentities($result->withdraw_amount); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div><?php echo htmlentities($result->withdraw_type); ?></div>
                                            </td>
                                            <td>
                                                <div class="lh-1">{{ date('Y-m-d', strtotime($result->withdraw_date)) }}
                                                </div>
                                                <div class="lh-2 text-muted">
                                                    {{ date('H:i:s', strtotime($result->withdraw_date)) }}</div>
                                            </td>
                                            <td>
                                                <div class="mt-sm-1 d-block">

                                                    <?php
                              $stats = $result->status;
                              if ($stats == 1) {
                              ?>
                                                    <div
                                                        class="p-2 px-3 badge bg-success-transparent text-success rounded-pill ">
                                                        Success</div>
                                                    <?php }
                              if ($stats == 2) { ?>
                                                    <div
                                                        class="p-2 px-3 badge bg-danger-transparent text-danger rounded-pill ">
                                                        Cancelled</div>
                                                    <?php }

                              if ($stats == 0) { ?>
                                                    <div
                                                        class="p-2 px-3 badge bg-primary-transparent text-primary rounded-pill ">
                                                        Pending</div>
                                                    <?php
                              } ?>
                                                </div>

                                            </td>
                                            <td>
                                                <?php if ($result->type == "trade") { ?>
                                                <div>
                                                    <a href="/admin/trading_withdrawal_details?id=<?php echo (($result->raw_id)); ?>&email=<?php echo htmlentities($result->email); ?>"
                                                        class="" style="font-size: 13px;padding: 2px 20px;">
                                                        <i class="fe fe-eye fs-14 text-info"></i>
                                                    </a>
                                                </div>

                                                <?php } else { ?>
                                                <div>
                                                    <a href="/admin/wallet_withdrawal_details?id=<?php echo ($result->raw_id); ?>&email=<?php echo htmlentities($result->email); ?>&deposit=<?php echo htmlentities($result->withdraw_amount); ?>"
                                                        class="" style="font-size: 13px;padding: 2px 20px;">
                                                        <i class="fe fe-eye fs-14 text-info"></i>
                                                    </a>
                                                </div>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php }
                    } ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->

        </div>
    </div>
@endsection
