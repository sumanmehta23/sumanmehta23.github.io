@extends('layouts.admin.admin')
@section('content')
    @include('admin.mt5.popups')
    <?php

// include __DIR__ . "/user_actions.php";
// include "admin_transaction.php";


if ($getUser) {
?>
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Details of Trade Account</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">MT5 Account Details</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-5 col-xl-4">
                    <div class="card custom-card">
                        <div class="card-body">
                            <!-- <h6 class="card-title fw-medium">DEPOSIT TICKET #$details->id ?></h6> -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="text-center">
                                                <div class="userprofile">
                                                    <div class="avatar userpic avatar-rounded">
                                                        <img src="/admin_assets/assets/images/users/client.jpeg"
                                                            alt="img" style="width:100px">
                                                    </div>

                                                    <h3 class="mb-2 username"><?= $getUser->name ?></h3>
                                                    <p class="mb-1 text-muted"><?= $getUser->email ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="wideget-user-desc d-flex align-items-center">
                                                <div class="wideget-user-img">
                                                    <img src="/assets/images/mt5.png" class="me-3" alt="img"
                                                        style="width:50px">
                                                </div>
                                                <div class="mt-auto mb-auto user-wrap">
                                                    <h4 class="mb-0 fw-bold"><?= $getUser->code ?></h4>
                                                    <h6 class="fs-12 fw-normal text-muted"><?= $getUser->accountType->ac_group ?></h6>
                                                </div>
                                            </div>
                                            <div class="mt-3 row justify-content-center">
                                                <div class="col-6 mb-2">
                                                    <span class="badge btn btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#depositModal">Deposit
                                                        <i class="ti ti-database-import"></i>
                                                    </span>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <span class="badge btn btn-info" data-bs-toggle="modal"
                                                        data-bs-target="#withdrawalModal">Withdraw
                                                        <i class="ti ti-square-rounded-arrow-down"></i>
                                                    </span>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <span class="badge btn btn-secondary" data-bs-toggle="modal"
                                                        data-bs-target="#bonusModalCredit">Bonus Credit
                                                        <i class="ti ti-plus" style="font-weight: bold"></i>
                                                    </span>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <span class="badge btn btn-secondary" data-bs-toggle="modal"
                                                        data-bs-target="#bonusModal">Bonus Deposit
                                                        <i class="ti ti-plus" style="font-weight: bold"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">

                                    <div class="table-responsive">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Leverage</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    echo $getUser->leverage;
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Balance</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    if (isset($accountHelper->Balance)) {
                                                                        echo "$" . number_format($accountHelper->Balance, 2);
                                                                    } else {
                                                                        echo "$0.00";
                                                                    }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Equity</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    if (isset($account->equity)) {
                                                                        echo "$" . number_format($account->equity , 2);
                                                                    }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Bonus</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    if (isset($account->BonusTransaction)) {
                                                                        echo "$" . number_format($account->BonusTransaction->sum('bonus_amount') , 2) ;
                                                                    }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-butterfly f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Free Margin</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    if (isset($account->margin_free)) {
                                                                        echo "$" . number_format($account->margin_free , 2);
                                                                    }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-pie f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Margin</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    if (isset($accountHelper->Margin)) {
                                                                        echo $accountHelper->Margin;
                                                                    }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-pie-slice f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Margin Level</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    if (isset($account->margin_level)) {
                                                                        echo $account->margin_level . '%';
                                                                    }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-line-segments f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Floating PL</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    if (isset($accountHelper->Floating)) {
                                                                        echo $accountHelper->Floating;
                                                                    }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">Bonus</div>
                            <div class="prism-toggle">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table text-nowrap" id="tableBonus">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($bonus_trans as $bns) {
                                        ?>
                                        <tr>
                                            <td><?= date('Y-m-d', strtotime($bns->bonus_date)) ?><br><small><?= date('H:i:s', strtotime($bns->bonus_date)) ?></small>
                                            </td>
                                            <td><?= $bns->bonus_amount ?></td>
                                            <td><?= $bns->bonus_type ?></td>
                                        </tr>
                                        <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8">
                    <div class="card">
                        <div class="p-0 card-body">
                            <div class="row">
                                <div class="col-xl-3 col-lg-6 col-sm-6 pe-0 ps-0 border-end">
                                    <div class="text-center card-body">
                                        <h6 class="mb-0">Total Deposit</h6>
                                        <h2 class="mt-2 mb-1 number-font text-primary">$<span
                                                class="counter"><?= $total_deposit ? number_format($total_deposit , 2) : '0' ?></span>
                                        </h2>
                                        <!-- <p class="mb-0 text-muted"> Completed</p> -->
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-6 col-sm-6 pe-0 ps-0 border-end">
                                    <div class="text-center card-body">
                                        <h6 class="mb-0">Unapproved Deposit</h6>
                                        <h2 class="mt-2 mb-1 number-font text-secondary">$<span
                                                class="counter"><?= $unapprove_deposit ? number_format($unapprove_deposit , 2) : '0' ?></span>
                                        </h2>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-6 col-sm-6 pe-0 ps-0 border-end">
                                    <div class="text-center card-body">
                                        <h6 class="mb-0">Total Withdrawl</h6>
                                        <h2 class="mt-2 mb-1 number-font text-primary">$<span
                                                class="counter"><?= $total_withdrawl ? number_format($total_withdrawl , 2) : '0' ?></span>
                                        </h2>
                                        <!-- <p class="mb-0 text-muted"> Completed</p> -->
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-6 col-sm-6 pe-0 ps-0 border-end">
                                    <div class="text-center card-body">
                                        <h6 class="mb-0">Unapproved Withdrawl</h6>
                                        <h2 class="mt-2 mb-1 number-font text-secondary">$<span
                                                class="counter"><?= $unapprove_withdrawl ? number_format($unapprove_withdrawl , 2) : '0' ?></span>
                                        </h2>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title d-flex justify-content-between">
                                        <div class="mt-auto mb-auto">Security / Passwords</div>
                                        <div class="updatePassword"><button class="btn btn-primary">Update
                                                Credentials</button></div>
                                    </h5>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">Master Password</label>
                                                    <div class="input-group">
                                                        <input class="form-control" type="password" name=""
                                                            placeholder="" readonly aria-label=""
                                                            value="<?= $account->trader_password ?>"
                                                            aria-describedby="my-addon">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text showPassword h-100"
                                                                id="my-addon">
                                                                <i class="fa fa-eye-slash"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">Investor Password</label>
                                                    <div class="input-group">
                                                        <input class="form-control" type="password" name=""
                                                            value="<?= $getUser->invester_password ?>"
                                                            aria-describedby="my-addon">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text showPassword h-100"
                                                                id="my-addon">
                                                                <i class="fa fa-eye-slash"></i>
                                                            </span>
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
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <form action="{{route('admin.updateAccountDetails')}}" enctype="multipart/form-data" method="post">
                                    @csrf
                                    <input type="hidden" name="code" value="<?= $getUser->code ?>">
                                    <div class="card-body">
                                        <h5 class="card-title d-flex justify-content-between">
                                            <div class="mt-auto mb-auto">Group / Leverage</div>
                                        </h5>
                                        <div class="pb-0 card-body">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="">Group</label>
                                                        <select class="form-control acc-types" name="account_type"
                                                            required id="account_type">
                                                            <?php foreach ($account_types as $grp) { ?>
                                                            <option value="<?= $grp->id ?>"
                                                                <?= $getUser->account_type_id == $grp->id ? 'selected' : '' ?>>
                                                                <strong><?= $grp->ac_name . '</strong> [ ' . $grp->ac_group . ' ]' ?>
                                                            </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="">Leverage</label>
                                                        <div class="input-group">
                                                            <select class="form-select" required name="leverage"
                                                                id="leverage">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <div class=""><button type="submit" name="update_group" value="submit"
                                                class="btn btn-primary">Update Settings</button></div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">
                                        Deposits
                                    </div>
                                    <div class="prism-toggle">
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table text-nowrap" id="tableDeposit">
                                            <thead>
                                                <tr>
                                                    <th>Account No</th>
                                                    <th>Deposit Amount</th>
                                                    <th>Deposit Type</th>
                                                    <th>Deposit From</th>
                                                    <th>Deposited Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">
                                        Withdrawal
                                    </div>
                                    <div class="prism-toggle">
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table text-nowrap" id="tableWithdrawal">
                                            <thead>
                                                <tr>

                                                    <th>Account No</th>
                                                    <th>Withdrawal Amount</th>
                                                    <th>Withdrawal Type</th>
                                                    <th>Withdraw To</th>
                                                    <th>Withdrawal Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
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
        <?php } else { ?>
        <div class="p-2 text-center error-page">
            <div class="error-template">
                <h1 class="error-details text-primary">
                    Sorry, No Data Found !!!!!
                </h1>
            </div>
        </div>
        <?php } ?>



        <div id="passwordupdatemodal" class="modal fade" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form method="post" id="passwordForm" action="{{route('admin.updatePassword')}}">
                    @csrf
                    <input type="hidden" name="code" value="<?= $account->code ?>">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalCenterTitle">Update Password</h5><button
                                type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-6">
                                    <h5 class="p-2 f-w-200">MT5 ACCOUNT</h5>
                                </div>
                                <div class="col-6">
                                    <h5 class="p-2 f-w-400"><?= $account->code ?></h5>
                                </div>
                            </div>
                            <p class="p-2 mt-0 mb-2 text-gray-500 f-12 text-muted"> You have the ability to update your
                                Investor and
                                Master passwords for your trading accounts here.</p>
                            <div class="mt-0 mb-0 row">
                                <div class="col-lg-6">
                                    <div class="p-3 border card">
                                        <div class="form-check"><input type="radio" name="password_type"
                                                class="form-check-input input-primary" id="customCheckdefhor1"
                                                value="investor" checked><label class="form-check-label d-block"
                                                for="customCheckdefhor1"><span><span class="h6">Investor
                                                        Password</span></span></label></div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="p-3 border card">
                                        <div class="form-check"><input type="radio" name="password_type"
                                                class="form-check-input input-primary" id="customCheckdefhor2"
                                                value="main"><label class="form-check-label d-block"
                                                for="customCheckdefhor2"><span><span class="h6">Master
                                                        Password</span></span></label></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-0 mb-0 row">
                                <div class="form-group"><label class="form-label" for="exampleInputPassword1">New
                                        Password</label><input type="password" class="form-control" name="password"
                                        required id="password" placeholder="Password">
                                </div>
                                <div class="mt-3 mb-2 row">
                                    <div class="col-6"><span class="pc-micon me-2"><i
                                                class="ti ti-point"></i></span><span class="pc-mtext f-12">Minimum 8
                                            characters</span><br><span class="pc-micon me-2"><i
                                                class="ti ti-point"></i></span><span class="pc-mtext f-12">At least 1
                                            uppercase
                                            letter</span><br><span class="pc-micon me-2"><i
                                                class="ti ti-point"></i></span><span class="pc-mtext f-12">At least 1
                                            lowercase letter</span></div>
                                    <div class="col-6"><span class="pc-micon me-2"><i
                                                class="ti ti-point"></i></span><span class="pc-mtext f-12">At least 1
                                            special character</span><br><span class="pc-micon me-2"><i
                                                class="ti ti-point"></i></span><span class="pc-mtext f-12">At least 1
                                            digit</span></div>
                                </div>
                                <div class="mb-2 form-group"><label class="form-label"
                                        for="exampleInputPassword1">Confirm
                                        Password</label><input type="password" class="form-control"
                                        name="confirm_password" required id="confirm_password" placeholder="Password">
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-primary"
                                data-bs-dismiss="modal">Close</button><button class="btn btn-primary" type="submit"
                                name="passwordUpdate" value="true"> Update Password</button></div>
                    </div>
                </form>
            </div>
        </div>
    @endsection

    @section('scripts')

        <script>
            function number_format(number, decimals = 2, dec_point = '.', thousands_sep = ',') {
                // Ensure the input is a valid number
                const match = number.match(/\d+(\.\d{2})?/);
                const n = parseFloat(match[0]);
                if (isNaN(n)) return 'NA';
                // Format the number with fixed decimals
                const fixed = n.toFixed(decimals);

                // Split the integer and decimal parts
                const parts = fixed.split('.');
                const integerPart = parts[0];
                const decimalPart = parts[1];

                // Add thousands separator to integer part
                const formattedInt = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep);

                return formattedInt + dec_point + decimalPart;
            }


            $("#tableBonus").DataTable();
            $('#tableDeposit').DataTable({
                // "ajax": {
                //     "url": "/admin/ajax",
                //     "type": "GET",
                //     data: {
                //         action: 'getTradingDeposit',
                //         id: '<?= $account->code ?>'
                //     },
                // },
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '/admin/getTradingDeposit2',
                    type: 'GET',
                    data: {id: '<?= $account->code ?>'}, // Ensure this is populated dynamically if needed.
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                columns: [{
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'deposit_amount',
                        name: 'deposit_amount',
                        render: function(data, type, row) {
                            return number_format(data);
                        }
                    },
                    {
                        data: 'deposit_type',
                        name: 'deposit_type'
                    },
                    {
                        data: 'deposit_from',
                        name: 'deposit_from'
                    },
                    {
                        data: 'deposit_date',
                        name: 'deposit_date',
                        // render: function(data, type, row) {
                        //     var dateTime = row.deposit_date.split(' ');
                        //     var date = dateTime[0];
                        //     var time = dateTime[1];
                        //     var return_data = "<div class='d-grid'><div class='date'>" + date +
                        //         "</div><div class='time text-muted'>" + time + "</div></div>";
                        //     return return_data;
                        // }
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
            $('#tableWithdrawal').DataTable({
                // "ajax": {
                //     "url": "/admin/ajax",
                //     "type": "GET",
                //     data: {
                //         action: 'getTradingWithdrawal',
                //         id: '<?= $account->id ?>'
                //     },
                // },
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '/admin/getTradingWithdrawal2',
                    type: 'GET',
                    data: {id: '<?= $account->id ?>'}, // Ensure this is populated dynamically if needed.
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                columns: [{
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'withdrawal_amount',
                        name: 'withdrawal_amount',
                        // render: function(data, type, row) {
                        //     return number_format(data);
                        // }
                    },
                    {
                        data: 'withdraw_type',
                        name: 'withdraw_type'
                    },
                    {
                        data: 'withdraw_to',
                        name: 'withdraw_from'
                    },
                    {
                        data: 'withdraw_date',
                        name: 'withdraw_date',
                        // render: function(data, type, row) {
                        //     var dateTime = row.withdraw_date.split(' ');
                        //     var date = dateTime[0];
                        //     var time = dateTime[1];
                        //     var return_data = "<div class='d-grid'><div class='date'>" + date +
                        //         "</div><div class='time text-muted'>" + time + "</div></div>";
                        //     return return_data;
                        // }
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            function validatePassword(password) {
                const minLength = 8;
                const hasUpperCase = /[A-Z]/.test(password);
                const hasLowerCase = /[a-z]/.test(password);
                const hasDigit = /\d/.test(password);
                const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

                if (password.length < minLength) {
                    return "Password must be at least 8 characters long.";
                }
                if (!hasUpperCase) {
                    return "Password must contain at least one uppercase letter.";
                }
                if (!hasLowerCase) {
                    return "Password must contain at least one lowercase letter.";
                }
                if (!hasDigit) {
                    return "Password must contain at least one digit.";
                }
                if (!hasSpecialChar) {
                    return "Password must contain at least one special character.";
                }

                return "true";
            }

            $(".updatePassword").click(function() {
                // alert("Clicekd");
                $("#passwordupdatemodal").modal("show");
            });

            $("#passwordForm").on("submit", function(e) {
                e.preventDefault();
                var pass = $("#password").val();
                var cpass = $("#confirm_password").val();
                if (validatePassword(pass) == "true") {
                    if (pass == cpass) {
                        $("#passwordForm").off();
                        $("#passwordForm").submit();
                    } else {
                        swal.fire({
                            icon: "info",
                            title: "Passwords not matched"
                        });
                        $("#confirm_password").val("")
                        return false;
                    }
                } else {
                    swal.fire({
                        icon: "info",
                        title: "Password not matched requirement.",
                        text: validatePassword(pass)
                    })
                }
            });

            $(".showPassword").click(function() {
                var input = $(this).closest(".input-group").find("input");
                if (input.attr("type") == "password") {
                    input.attr("type", "text");
                    $(this).find("i").removeClass("fa-eye-slash");
                    $(this).find("i").addClass("fa-eye");
                } else {
                    input.attr("type", "password");
                    $(this).find("i").removeClass("fa-eye");
                    $(this).find("i").addClass("fa-eye-slash");
                }
            });

            $(".acc-types").change(function() {
                var selectedValue = $(".acc-types").val();
                $("#leverage").html("<option value='' checked>Loading...</option>");
                $.ajax({
                    url: "/admin/api/ajax?type=leverage&id=" + selectedValue,
                    success: function(data) {
                        // console.log("Res:: ",$data);
                        $("#leverage").html("");
                        $.each(data, function(key, value) {
                            // console.log(value);
                            var isSelected = "";
                            if (selectedValue == "<?= addslashes($getUser->account_type_id) ?>" && value.account_leverage == <?= (int) $getUser->leverage ?>) {
                                isSelected = "selected";
                            }
                            $("#leverage").append("<option value='" + value.account_leverage +
                                "' " + isSelected + ">" + value.account_leverage + "</option>");
                        });
                    }
                })
            });
            $(".acc-types").trigger("change");
            $(".acc-types").select2();
        </script>
    @endsection
