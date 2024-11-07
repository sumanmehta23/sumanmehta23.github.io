@extends('layouts.crm.crm')
@section('styles')
<link rel="stylesheet" href="/assets1/vendors/datatables.net-bs4/dataTables.bootstrap4.css">

@endsection
@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header mb-0 pb-0 mt-0 pt-0">
                <div class="page-block mb-0 pb-0 mt-0 pt-0">
                    <div class="row align-items-center mb-0 pb-0 mt-0 pt-0">
                        <div class="col-md-12 mb-0 pb-0 mt-0 pt-0">
                            <div class="page-header-title h2">
                                <h4 class="mb-0">My IB Profile</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-0 pb-0 mt-0 pt-0">
                <div class="col-12 mb-0 pb-0 mt-0 pt-0">
                    <div class="card mb-0 pb-0 mt-0 pt-0">
                        <div class="card-body mb-0 pb-0 mt-0 pt-0">
                            <div class="row mb-0 pb-0 mt-0 pt-0">
                                <ul class="nav nav-tabs profile-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation"><a class="nav-link active" id="profile-tab-1"
                                            data-bs-toggle="tab" href="#ib-home" role="tab" aria-selected="true"><i
                                                class="ti ti-smart-home me-2"></i>IB Home </a></li>
                                    <li class="nav-item" role="presentation"><a class="nav-link" id="profile-tab-2"
                                            data-bs-toggle="tab" href="#ib-connect" role="tab" aria-selected="false"
                                            tabindex="-1"><i class="ti ti-affiliate me-2"></i>My Connections </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-content mb-0 pb-0 mt-0 pt-0">
                <div class="tab-pane pt-3 active show mb-0 pb-0 mt-0 pt-0" id="ib-home" role="tabpanel"
                    aria-labelledby="profile-tab-1">
                    <div class="row mb-0 pb-0 mt-0 pt-0">
                        <div class="col-lg-9 mb-0 pb-0 mt-0 pt-0">
                            <div class="card mb-0 pb-0 mt-0 pt-0">
                                <div class="card-body mb-0 pb-3 mt-0 pt-3">
                                    <div class="row g-3">
                                        <div class="col-md-6 col-xxl-4">
                                            <div class="card mb-0">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center justify-content-between gap-1">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <h3 class="mb-0 f-w-500"><?= $ib_clients_total ?></h3>
                                                        </div>
                                                        <div class="avtar avtar-s bg-light-primary"><i
                                                                class="ti ti-mood-kid f-18"></i></div>
                                                    </div>
                                                    <p class="mb-0 text-muted d-flex align-items-center gap-2 f-12 mt-3">
                                                        Total Clients </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xxl-4">
                                            <div class="card mb-0">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center justify-content-between gap-1">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <h5 class="mb-0 f-w-500">$
                                                                <?= isset($ib_wallet_raw->wallet) ? $ib_wallet_raw->wallet : '0.00' ?>
                                                            </h5>
                                                        </div>
                                                        <div class="avtar avtar-s bg-light-primary"><i
                                                                class="ti ti-report-money f-18"></i></div>
                                                    </div>
                                                    <p class="mb-0 text-muted d-flex align-items-center gap-2 f-12 mt-3">
                                                        Generated Commission
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xxl-4">
                                            <div class="card mb-0">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center justify-content-between gap-1">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <h5 class="mb-0 f-w-500">$
                                                                <?= isset($ib_wallet_raw->withdraw) ? $ib_wallet_raw->withdraw : '0.00' ?>
                                                            </h5>
                                                        </div>
                                                        <div class="avtar avtar-s bg-light-primary"><i
                                                                class="ti ti-shield-check f-18"></i></div>
                                                    </div>
                                                    <p class="mb-0 text-muted d-flex align-items-center gap-2 f-12 mt-3">
                                                        Commission Transferred
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row p-1">
                                        <div class="col-12">
                                            <div class="bg-body p-2 rounded">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0"><span
                                                                class="p-1 d-block bg-primary rounded-circle"><span
                                                                    class="visually-hidden">New alerts</span></span></div>
                                                        <div class="flex-grow-1 ms-2">
                                                            <p class="mb-0">Deposits</p>
                                                        </div>
                                                    </div>
                                                    <h5 class="mb-0 f-w-500">$ 0.00</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row p-1">
                                        <div class="col-12">
                                            <div class="bg-body p-2 rounded">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0"><span
                                                                class="p-1 d-block bg-primary rounded-circle"><span
                                                                    class="visually-hidden">New alerts</span></span></div>
                                                        <div class="flex-grow-1 ms-2">
                                                            <p class="mb-0">Withdrawals</p>
                                                        </div>
                                                    </div>
                                                    <h5 class="mb-0 f-w-500">$ 0.00</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-0 pb-0 mt-0 pt-0">
                        <div class="col-xl-6 col-md-6 mb-0 pb-0 mt-0 pt-0">
                            <form method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="card mb-0 pb-0 mt-0 pt-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h5 class="mb-0 f-w-500">Transfer My Commission</h5>
                                            <div class="bg-body p-1 mt-1 rounded">
                                                <div class="mt-1 row align-items-center">
                                                    <div class="col-12 text-end">
                                                        <h3 class="mb-1 me-2 ms-2 f-w-500">
                                                            $<?= number_format($ib_wallet, 2) ?></h3>
                                                        <p class="text-warning mb-0 me-2 ms-2"> Transferrable Balance</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr style="opacity: 0.1;">
                                        <div class="form"><label class="form-label mt-3"
                                                for="exampleFormControlSelect1">Select
                                                Account</label>
                                            <div class="row">
                                                <?php if (count($live_accs)) {
                        foreach ($live_accs as $acc) {
                      ?>
                                                <div class="col-lg-6">
                                                    <div class="border card p-2">
                                                        <div class="form-check mb-0"><input type="radio" name="tradeId"
                                                                class="form-check-input input-primary"
                                                                id="<?= $acc->trade_id ?>"
                                                                value="<?= $acc->trade_id ?>"><label
                                                                class="form-check-label d-block mb-0"
                                                                for="<?= $acc->trade_id ?>"><span><span
                                                                        class="h5 d-block"><span
                                                                            class="float-end badge bg-light-primary f-14 fw-medium">$
                                                                            <?= $acc->Balance ?></span><span>
                                                                            <img src="/assets/images/mt5.png"
                                                                                class="hei-30">
                                                                            <?= $acc->trade_id ?></span></span><span
                                                                        class="text-muted mt-2 mb-0"><span
                                                                            class="float-end text-muted mt-2 f-12"> Current
                                                                            Balance
                                                                        </span></span></span></label></div>
                                                    </div>
                                                </div>
                                                <?php }
                      } else { ?>
                                                <div class="col-lg-6">
                                                    <a href="/trade-deposit" class="d-grid"><button
                                                            class="btn btn-primary"><span
                                                                class="text-truncate w-100">Create new
                                                                Live Account</span></button></a>
                                                </div>
                                                <?php } ?>

                                                <!---->
                                            </div><label class="form-label" for="exampleFormControlSelect1">Enter
                                                Amount</label>
                                            <div class="input-group mb-3"><span class="input-group-text">$</span><input
                                                    type="number" name="amount" max="<?= $ib_wallet ?>"
                                                    class="form-control" required aria-label="Amount (to the nearest dollar)"><span
                                                    class="input-group-text">.00</span>
                                                <!---->
                                            </div>
                                            <div class="d-grid mb-5 mt-4"><button class="btn btn-outline-secondary"
                                                    name="transfer" type="submit"><i
                                                        class="ti ti-shield-check me-2"></i>
                                                    <!----> Process Transfer</button></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-xl-6 col-md-6 mt-0 pt-0">
                            <div class="card mt-0 pt-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5 class="mb-0 f-w-500">My Referral Link</h5>
                                        <div class="avtar avtar-s bg-light-primary"><i class="ti ti-list f-18"></i></div>
                                    </div>
                                    <?php

                                    ?>
                                    <hr style="opacity:.1;"><label class="col-form-label col-12 text-lg-start">Your personal referral link is now available! Share it to help new clients sign up and kick-start their trading journey.</label>
                                    <div class="col-12 mb-4">
                                        {{ session('email') }}
                                        <div class="input-group mb-2"><input type="text" class="form-control"
                                                id="pc-clipboard-1" placeholder="Type some value to copy"
                                                value="{{ url('/ib-ref?refercode=' . base64_encode(session('clogin'))) }}"
                                                readonly=""><button class="btn btn-lg btn-primary cb"
                                                data-clipboard-target="#pc-clipboard-1"><i
                                                    class="feather icon-copy"></i></button></div>
                                        <!---->
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body pb-0">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h4 class="mb-0">Transfer History</h4>
                                        <div class="avtar avtar-s bg-light-primary"><i class="ti ti-list f-18"></i></div>
                                    </div>
                                    <hr style="opacity:.1;">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>TRANSFERRED TO</th>
                                                    <th>PROCESSED ON</th>
                                                    <th>AMOUNT</th>
                                                    <th>STATUS</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2 pb-0">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body pb-0">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h4 class="mb-0">IB Commission History</h4>
                                        <div class="avtar avtar-s bg-light-primary">
                                            <i class="ti ti-list f-18"></i>
                                        </div>
                                    </div>
                                    <hr style="opacity:.1;">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="commissionTbl">
                                            <thead>
                                                <tr>
                                                    <th>DATETIME</th>
                                                    <th>ACCOUNT</th>
                                                    <th>TYPE</th>
                                                    <th>AMOUNT</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($histories as $history)
                                                    <tr>
                                                        <td>
                                                            {{ \Carbon\Carbon::parse($history->created_at)->format('Y-m-d') }}<br>
                                                            <small>{{ \Carbon\Carbon::parse($history->created_at)->format('H:i:s') }}</small>
                                                        </td>
                                                        <td>
                                                            <div class="row align-items-center">
                                                                <div class="col-auto pe-0">
                                                                    <img src="/assets/images/mt5.png" alt="user-image"
                                                                        class="wid-50 hei-50 rounded">
                                                                </div>
                                                                <div class="col">
                                                                    <h4 class="mb-2 ms-2">
                                                                        <span
                                                                            class="text-truncate w-100">{{ $history->trade_id }}</span>
                                                                    </h4>
                                                                    <p class="text-muted ms-2 f-12 mb-0">
                                                                        <span
                                                                            class="text-truncate w-100">{{ $history->remark }}</span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ $history->ib_wallet ? 'Commission' : 'Transfer' }}</td>
                                                        <td>{{ $history->ib_wallet ?? $history->ib_withdraw }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane pt-3" id="ib-connect" role="tabpanel" aria-labelledby="profile-tab-2">
                    <div>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <ul class="nav nav-pills nav-tabs nav-justified" role="tablist">
                                            <li class="nav-item" data-target-form="#LEVEL1" role="presentation"><a
                                                    href="/ib-profile#LEVEL1" data-bs-toggle="tab"
                                                    data-bs-target="#LEVEL1" data-toggle="tab" class="nav-link active"
                                                    aria-selected="false" role="tab" tabindex="-1"><i
                                                        class="ti ti-chart-bar me-2"></i><span
                                                        class="d-none d-sm-inline">LEVEL1</span></a></li>
                                            <li class="nav-item" data-target-form="#LEVEL2" role="presentation"><a
                                                    href="/ib-profile#LEVEL2" data-bs-toggle="tab"
                                                    data-bs-target="#LEVEL2" data-toggle="tab" class="nav-link"
                                                    aria-selected="false" role="tab" tabindex="-1"><i
                                                        class="ti ti-chart-bar me-2"></i><span
                                                        class="d-none d-sm-inline">LEVEL2</span></a></li>
                                            <li class="nav-item" data-target-form="#LEVEL3" role="presentation"><a
                                                    href="/ib-profile#LEVEL3" data-bs-toggle="tab"
                                                    data-bs-target="#LEVEL3" data-toggle="tab" class="nav-link"
                                                    aria-selected="false" role="tab" tabindex="-1"><i
                                                        class="ti ti-chart-bar me-2"></i><span
                                                        class="d-none d-sm-inline">LEVEL3</span></a></li>
                                            <li class="nav-item" data-target-form="#LEVEL4" role="presentation"><a
                                                    href="/ib-profile#LEVEL4" data-bs-toggle="tab"
                                                    data-bs-target="#LEVEL4" data-toggle="tab" class="nav-link"
                                                    aria-selected="false" role="tab" tabindex="-1"><i
                                                        class="ti ti-chart-bar me-2"></i><span
                                                        class="d-none d-sm-inline">LEVEL4</span></a></li>
                                            <li class="nav-item" data-target-form="#LEVEL5" role="presentation"><a
                                                    href="/ib-profile#LEVEL5" data-bs-toggle="tab"
                                                    data-bs-target="#LEVEL5" data-toggle="tab" class="nav-link"
                                                    aria-selected="false" role="tab" tabindex="-1"><i
                                                        class="ti ti-chart-bar me-2"></i><span
                                                        class="d-none d-sm-inline">LEVEL5</span></a></li>
                                            <li class="nav-item" data-target-form="#LEVEL6" role="presentation"><a
                                                    href="/ib-profile#LEVEL6" data-bs-toggle="tab"
                                                    data-bs-target="#LEVEL6" data-toggle="tab" class="nav-link"
                                                    aria-selected="false" role="tab" tabindex="-1"><i
                                                        class="ti ti-chart-bar me-2"></i><span
                                                        class="d-none d-sm-inline">LEVEL6</span></a></li>
                                            <li class="nav-item" data-target-form="#LEVEL7" role="presentation"><a
                                                    href="/ib-profile#LEVEL7" data-bs-toggle="tab"
                                                    data-bs-target="#LEVEL7" data-toggle="tab" class="nav-link"
                                                    aria-selected="false" role="tab" tabindex="-1"><i
                                                        class="ti ti-chart-bar me-2"></i><span
                                                        class="d-none d-sm-inline">LEVEL7</span></a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="tab-content connectionTab" id="nav-tabContent">
                                            <?php for ($i = 1; $i <= 7; $i++) { ?>
                                            <div class="tab-pane fade<?= $i == 1 ? ' show active' : '' ?>"
                                                id="LEVEL<?= $i ?>" role="tabpanel">
                                                <div class="datatable-container">
                                                    <table class="table table-hover datatable-table" id="pc-dt-simple">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 30%;">CLIENT</th>
                                                                <th class="text-end" style="width: 10%;">TOTAL ACCOUNTS
                                                                </th>
                                                                <th class="text-end" style="width: 10%;">TOTAL DEPOSIT
                                                                </th>
                                                                <th class="text-end" style="width: 15%;">PROFILE STATUS
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                $clients = $ib_clients[$i];
                                foreach ($clients as $client) {
                                ?>
                                                            <tr data-index="0">
                                                                <td>
                                                                    <div class="row align-items-center">
                                                                        <div class="col-auto pe-0"><img
                                                                                src="/assets/images/ib_avatar.png"
                                                                                alt="user-image"
                                                                                class="wid-55 hei-55 rounded"></div>
                                                                        <div class="col">
                                                                            <h6 class="mb-2"><span
                                                                                    class="text-truncate w-100"><?= $client->fullname ?></span>
                                                                            </h6>
                                                                            <p class="text-muted f-12 mb-0"><span
                                                                                    class="text-truncate w-100"><?= $client->email ?></span>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="text-end f-w-400"><?= $client->liveaccounts ?>
                                                                </td>
                                                                <td class="f-w-400 text-end">$<?= $client->total_deposit ?>
                                                                </td>
                                                                <td class="text-end">
                                                                    <?php if ($client->email_confirmed == 1) { ?>
                                                                    <span class="badge btn bg-success">Active</span>
                                                                    <?php } else { ?>
                                                                    <span class="badge btn bg-info">Not Verified</span>
                                                                    <?php } ?>
                                                                </td>
                                                            </tr>
                                                            <?php } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <?php } ?>
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
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
            }).then(() => {
                location.reload();
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Something went wrong',
                text: '{{ session('error') }}',
                showConfirmButton: true
            });
        </script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.11/dist/clipboard.min.js"></script>
    <script>
        $("[data-bs-target]").click(function() {
            var target = $(this).attr("data-bs-target");
            var targetTab = ".connectionTab .tab-pane" + target;
            console.log(targetTab);
            $(".connectionTab .tab-pane").removeClass("show");
            $(".connectionTab .tab-pane").removeClass("active");
            $(targetTab).addClass("show active");
        });
        var clipboard = new ClipboardJS('.cb');
        clipboard.on('success', function(e) {
            swal.fire({
                icon: "success",
                title: "IB Referral Link Copied"
            });
        });

        $("#commissionTbl").dataTable();
    </script>
@endsection
