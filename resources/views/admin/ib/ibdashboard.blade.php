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
                                <h6 class="mb-2">Total IB</h6>
                                <h2 class="text-end "><i
                                        class="fa fa-user icon-size float-start text-primary text-primary-shadow"></i><span>
                                        <?= $total_ib ?></span>
                                </h2>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="card-widget">
                                <h6 class="mb-2">Active IB</h6>
                                <h2 class="text-end">
                                    <i data-feather="user"></i>
                                    <i
                                        class="fa fa-user-circle icon-size float-start text-success text-success-shadow"></i><span>
                                        <?= $active_ib ?></span>
                                </h2>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="card-widget">
                                <h6 class="mb-2">Total IB Clients</h6>
                                <h2 class="text-end"><i
                                        class="icon-size fa fa-users  float-start text-warning text-warning-shadow"></i><span>
                                        <?= $total_clients ?></span>
                                </h2>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
            </div>
            <!-- ROW-1 END -->
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="card bg-primary img-card box-primary-shadow">
                        <div class="card-body">
                            <div class="d-flex">
                                <div>
                                    <h2 class="mb-0 number-font text-fixed-white"><?= $pending_kyc ?></h2>
                                    <p class="text-fixed-white mb-0">Pending IB KYC</p>
                                </div>
                                <div class="ms-auto"> <i class="fa fa-file-text text-fixed-white fs-30 me-2 mt-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="card bg-secondary img-card box-secondary-shadow">
                        <div class="card-body">
                            <div class="d-flex">
                                <div>
                                    <h2 class="mb-0 number-font text-fixed-white"><?= $ib_internal ?></h2>
                                    <p class="mb-0 text-fixed-white">IB Internal Transfer</p>
                                </div>
                                <div class="ms-auto"> <i class="fa fa-exchange text-fixed-white fs-30 me-2 mt-2"></i> </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card custom-card product-sales">
                        <div class="card-header">
                            <div class="card-title d-flex justify-content-between mb-0 w-100">
                                <div>
                                    Total IB Internal Transfer Pending
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom">
                                    <thead class="border-top">
                                        <tr>
                                            <th>#</th>
                                            <th>IB Email</th>
                                            <th>Amount</th>
                                            <th>Trade ID</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php

                      foreach ($ibPendingTrans as $result) {
                        ?>
                                        <tr>
                                            <td>
                                                <div><?php echo htmlentities($result->id); ?></div>
                                            </td>
                                            <td>
                                                <div><?php echo htmlentities($result->email); ?></div>
                                            </td>
                                            <td>
                                                <div class="amount">
                                                    $ <?php echo htmlentities($result->ib_amount); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div><?php echo htmlentities($result->trade_id); ?></div>
                                            </td>
                                            <td>
                                                <div><?php echo htmlentities($result->transfer_to); ?></div>
                                            </td>
                                        </tr>
                                        <?php } ?>
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
                            <div class="card-title d-flex justify-content-between mb-0 w-100">
                                <div>
                                    Total IB KYC Pending
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom">
                                    <thead class="border-top">
                                        <tr>
                                            <th>#</th>
                                            <th>Email</th>
                                            <th>Type</th>
                                            <th>Added Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                    if (count($kycpending) > 0) {
                      foreach ($kycpending as $result) {
                        ?>
                                        <tr>
                                            <td>
                                                <div> <?php echo htmlentities($result->id); ?></div>
                                            </td>
                                            <td>
                                                <div> <?php echo htmlentities($result->email); ?></div>
                                            </td>
                                            <td>
                                                <div class="amount">
                                                    <?php echo htmlentities($result->kyc_type); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="lh-1">
                                                    <?= date('Y-m-d', strtotime($result->registered_date_js)) ?></div>
                                                <div class="lh-2 text-muted">
                                                    <?= date('H:i:s', strtotime($result->registered_date_js)) ?></div>
                                            </td>
                                            <td>
                                                <?php
                            $stats = $result->Status;
                            if ($stats == 1) {
                              ?>
                                                <div class="badge btn-sm btn btn-outline-success">Success</div>
                                                <?php }
                            if ($stats == 2) { ?>
                                                <div class="badge btn-sm btn btn-outline-danger">Cancelled</div>
                                                <?php }

                            if ($stats == 0) { ?>
                                                <div class="badge btn-sm btn btn-outline-warning">Pending</div>
                                                <?php
                            } ?>
                                            </td>
                                            <td>
                                                <div> <a href="/admin/client_details/<?php echo ($result->email); ?>#tab-info"
                                                        style="padding: 5px 20px;font-size: 12px;"
                                                        class="btn btn-dark btn-sm">View</a></div>
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
