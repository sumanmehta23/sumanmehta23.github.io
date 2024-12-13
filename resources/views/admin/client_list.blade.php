@extends('layouts.admin.admin')
@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <?php

    ?>
    <style>
        .statusToggle,
        .viewClient {
            cursor: pointer;
        }
    </style>
    <div class="modal fade" id="addUserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="addUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{route('admin.addUser')}}" id="addUserForm" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserLabel">Create User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card">
                        <div class="row">
                            <div class="mb-3 col-12">
                                <label for="input-label" class="form-label">Email:</label>
                                <input type="text" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3 col-12">
                                <label for="input-label" class="form-label">Full Name:</label>
                                <input type="text" class="form-control" name="fullname" required>
                            </div>
                            <div class="mb-3 col-12">
                                <label for="input-label" class="form-label">Phone:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend w-25">
                                        <select class="form-select me-2 w-25 countrycode" name="country_code" required>
                                            <option value="">Country Code</option>
                                            <?php foreach ($countries as $country) { ?>
                                            <option value="+<?= $country['country_code'] ?>"
                                                data-flag="<?= strtolower($country['country_alpha']) ?>">
                                                +<?= $country['country_code'] ?>
                                                (<?= $country['country_name'] ?>)</option>
                                            <?php } ?>
                                        </select>


                                    </div>
                                    <input type="text" class="form-control" id="phone_number" name="telephone"
                                        placeholder="Enter phone number">
                                </div>
                            </div>
                            <div class="mb-3 col-12">
                                <label for="input-label" class="form-label">Country:</label>
                                <select class="form-select" id="country" name="country" required>
                                    <option value="">Select Country</option>
                                    <?php foreach ($countries as $country) { ?>
                                    <option value="<?= $country['country_name'] ?>">
                                        <?= $country['country_name'] ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3 col-12">
                                <label for="input-label" class="form-label">Password:</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="mb-3 col-12">
                                <label for="input-label" class="form-label">Confirm Password:</label>
                                <input type="password" class="form-control" id="input" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="addUser" value="update" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editUserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="editUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{route('admin.updateUser')}}" id="editUserForm" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserLabel">Update Client Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card">
                        <input type="hidden" name="id">
                        <div class="row">
                            <div class="col-6">
                                <label for="input-label" class="form-label">Email:</label>
                                <input type="text" class="form-control" name="email" required readonly>
                            </div>
                            <div class="col-6">
                                <label for="input-label" class="form-label">Full Name:</label>
                                <input type="text" class="form-control" name="fullname" required>
                            </div>
                            <div class="col-6">
                                <label for="input-label" class="form-label">Phone:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend w-25">
                                        <select class="form-select me-2 w-25 edit-countrycode" name="country_code"
                                            required>
                                            <option value="">Country Code</option>
                                            <?php foreach ($countries as $country) { ?>
                                            <option value="+<?= $country['country_code'] ?>"
                                                data-flag="<?= strtolower($country['country_alpha']) ?>">
                                                +<?= $country['country_code'] ?>
                                                (<?= $country['country_name'] ?>)</option>
                                            <?php } ?>
                                        </select>


                                    </div>
                                    <input type="text" class="form-control" id="phone_number" name="telephone"
                                        placeholder="Enter phone number">
                                </div>
                            </div>
                            <div class="col-6">
                                <label for="input-label" class="form-label">Country:</label>
                                <select class="form-select" id="country" name="country" required>
                                    <option value="">Select Country</option>
                                    <?php foreach ($countries as $country) { ?>
                                    <option value="<?= $country['country_name'] ?>">
                                        <?= $country['country_name'] ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="input-label" class="form-label">Password:</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="col-6">
                                <label for="input-label" class="form-label">Confirm Password:</label>
                                <input type="password" class="form-control" id="input" name="confirm_password"
                                    required>
                            </div>

                            <div class="col-lg-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        name="email_notification">
                                    <label class="form-check-label">Send Notification Email</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="updateUser" value="update" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="statusModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="#" id="statusUpdateForm" method="post">
                    @csrf
                    <input type="hidden" name="action" value="updateClientStatus">
                    <input type="hidden" name="client_id" id="user_id" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="statusModalLabel">Update Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card">
                        <div class="d-flex align-items-center card-header w-100">
                            <div class="me-2">
                                <span class="avatar avatar-rounded">
                                    <img src="/admin_assets/assets/images/users/user.png" alt="img">
                                </span>
                            </div>
                            <div class="">
                                <div class="fs-15 fw-medium text-capitalize" id="userName"></div>
                                <p class="mb-0 text-muted fs-11" id="userEmail"></p>
                            </div>

                        </div>
                        <div class="card-body">
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">User Status</label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="status"
                                            id="user_status" checked>
                                        <label class="form-check-label" for="user_status"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">Email Confirmed</label>
                                </div>
                                <div class="col-lg-8">

                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            name="email_confirmed" id="email_status">
                                        <label class="form-check-label" for="email_status"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">KYC Verification</label>
                                </div>
                                <div class="col-lg-8">

                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="kyc_verify"
                                            id="kyc_verify">
                                        <label class="form-check-label" for="kyc_verify"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="ibRequest" value="update" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Client List</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Client List</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW-1 OPEN -->
            <div class="row d-none">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                    <div class="card custom-card bg-primary img-card box-primary-shadow">
                        <div class="card-body">
                            <div class="d-flex">
                                <div>
                                    <h2 class="mb-0 number-font text-fixed-white">{{ $total_clients }}</h2>
                                    <p class="mb-0 text-fixed-white">Total Clients </p>
                                </div>
                                <div class="ms-auto"> <i class="mt-2 fa fa-users text-fixed-white fs-30 me-2"></i> </div>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                    <div class="card custom-card bg-secondary img-card box-secondary-shadow">
                        <div class="card-body">
                            <div class="d-flex">
                                <div>
                                    <h2 class="mb-0 number-font text-fixed-white"><?= $total_ib ?></h2>
                                    <p class="mb-0 text-fixed-white">Introducing Brokers</p>
                                </div>
                                <div class="ms-auto"> <i class="mt-2 fa fa-user-circle text-fixed-white fs-30 me-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                    <div class="card custom-card bg-success img-card box-success-shadow">
                        <div class="card-body">
                            <div class="d-flex">
                                <div>
                                    <h2 class="mb-0 number-font text-fixed-white">
                                        $<?= $total_balance->deposit_amount + $total_balance->trading_deposited ?>
                                    </h2>
                                    <p class="mb-0 text-fixed-white">Total Deposit</p>
                                </div>
                                <div class="ms-auto"> <i class="mt-2 fa fa-credit-card text-fixed-white fs-30 me-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                    <div class="card custom-card bg-info img-card box-info-shadow">
                        <div class="card-body">
                            <div class="d-flex">
                                <div>
                                    <h2 class="mb-0 number-font text-fixed-white">
                                        $<?= $total_balance->withdraw_amount + $total_balance->trading_withdrawal ?></h2>
                                    <p class="mb-0 text-fixed-white">Total Withdraw</p>
                                </div>
                                <div class="ms-auto"> <i
                                        class="mt-2 fa fa-arrow-circle-down text-fixed-white fs-30 me-2"></i> </div>
                            </div>
                        </div>
                    </div>
                </div><!-- COL END -->
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Listed Count : {{ $total_clients }}
                            </div>
                            <?php if (session('userData')['userRole'] == "Super Admin") { ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addUserModal">
                                Add New Client
                            </button>
                            <?php } ?>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="ajaxDatatable" class="table ajaxDataTable table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            
                                            <th>Joined On</th>
                                            <th>Name/Email</th>
                                            <th>Phone</th>
                                            <th>Country</th>
                                            <th>Parent IB</th>
                                            <th>IB Request</th>
                                            <th>RM</th>
                                            <th>Status / Action</th>
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
    <!-- Modal -->
    <div class="modal fade" id="updateIbModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="updateIbModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.updateIB') }}" id="ibUpdateForm" method="post">
                    @csrf
                    <input type="hidden" class="client_id" name="client_id" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateIbModalLabel">Update IB</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card" style="max-height:500px;overflow-y: auto;">
                        <div class="d-flex align-items-center card-header w-100">
                            <div class="me-2">
                                <span class="avatar avatar-rounded">
                                    <img src="/admin_assets/assets/images/users/user.png" alt="img">
                                </span>
                            </div>
                            <div class="">
                                <div class="fs-15 fw-medium text-capitalize clientName"></div>
                                <p class="mb-0 text-muted fs-11 clientEmail"></p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php for ($i = 1; $i <= 15; $i++) { ?>
                                <div class="col-lg-4 m-auto mb-3 update-ib-dropdown-<?= $i ?>">
                                    <label class="form-label">IB<?= $i ?></label>
                                    <select id="ib-select<?= $i ?>" data-id="<?= $i ?>" class="form-select ib-select"
                                        name="ib<?= $i ?>" disabled>
                                        <option value="" selected>--Select--</option>
                                        <?php foreach ($ib_details as $ib) { ?>
                                            <option value="<?php echo isset($ib->referral_code) && !empty($ib->referral_code) ? $ib->referral_code : $ib->email; ?>">
                                                <?php echo $ib->name; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="ibUpdate" value="update" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ibModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="ibModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="#" id="ibRequestForm" method="post">
                     @csrf
                    <input type="hidden" name="client_id" id="client_id" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ibModalLabel">IB Request Management</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card">
                        <div class="d-flex align-items-center card-header w-100">
                            <div class="me-2">
                                <span class="avatar avatar-rounded">
                                    <img src="/admin_assets/assets/images/users/user.png" alt="img">
                                </span>
                            </div>
                            <div class="">
                                <div class="fs-15 fw-medium text-capitalize" id="clientName"></div>
                                <p class="mb-0 text-muted fs-11" id="clientEmail"></p>
                            </div>

                        </div>
                        <div class="card-body">
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">IB Request Status</label>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-select" required name="ib_status"
                                        aria-label="Default select example">
                                        <option value="" selected>--Status--</option>
                                        <option value="1">Approve</option>
                                        <option value="0">Pending</option>
                                        <option value="2">Rejected</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">IB Plan</label>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-select" required name="ib_group"
                                        aria-label="Default select example">
                                        <option value="" selected>--Plans--</option>
                                        <?php foreach ($acc_groups as $gp) { ?>
                                        <option value="<?= $gp->id ?>"><?= $gp->ib_cat_name ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="ibRequest" value="update" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="rmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="rmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.updateRM') }}" id="rmRequestForm" method="post">
                    @csrf
                    <input type="hidden" name="user_id" id="customer_id" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rmModalLabel">Assign/Reassign RM</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card">
                        <div class="d-flex align-items-center card-header w-100">
                            <div class="me-2">
                                <span class="avatar avatar-rounded">
                                    <img src="/admin_assets/assets/images/users/user.png" alt="img">
                                </span>
                            </div>
                            <div class="">
                                <div class="fs-15 fw-medium text-capitalize" id="customerName"></div>
                                <p class="mb-0 text-muted fs-11" id="customerEmail"></p>
                            </div>

                        </div>
                        <div class="card-body">
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">Relationship Manager</label>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-select" required name="rm_id" id="group_rm_list">

                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="rmUpdate" value="update" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- End::app-content -->
    <script>
        $(document).ready(function() {
            window.myModal = new bootstrap.Modal(document.getElementById('ibModal'));
            window.editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            window.statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
            window.rmModal = new bootstrap.Modal(document.getElementById('rmModal'));
            window.updateIbModal = new bootstrap.Modal(document.getElementById('updateIbModal'));
            $(".countrycode").select2({
                placeholder: "Country Code",
                selectionCssClass: "country-code-select",
                dropdownParent: $('#addUserModal')
            });
            $(".edit-countrycode").select2({
                placeholder: "Country Code",
                selectionCssClass: "country-code-select",
                dropdownParent: $('#editUserModal')
            });
            $(".ib-select").each(function() {
                let id = $(this).data('id');
                $(this).select2({
                    dropdownParent: $('.update-ib-dropdown-' + id)
                });
            });
        });
    </script>
    <script>
        function updateIbSelects(id) {
            let val = $('#ib-select' + id).val();
            let nextId = id + 1;
            if (val != '') {
                $('#ib-select' + nextId).prop('disabled', false).trigger('change.select2');
            } else {
                for (let i = nextId; i <= 15; i++) {
                    $('#ib-select' + i).val("");
                    $('#ib-select' + i).prop('disabled', true).trigger('change.select2');
                }
            }
        }
        $(document).on('change', ".ib-select", function() {
            let val = $(this).val();
            $('.ib-select').find('option').prop('disabled', false);
            $('.ib-select').each(function() {
                let val = $(this).val();
                if (val) {
                    $('.ib-select').not(this).find('option[value="' + val + '"]').prop('disabled', true);
                }
            });
            let id = $(this).data("id");
            updateIbSelects(id);
        });

        // $("#ibModal").modal();
        function dTSelection() {
            // alert("Init");
            $('.ajaxDataTable tbody tr').off();
            $('.ajaxDataTable tbody tr').on('click', '.ibToggle', function() {
                var data = dTtable.row($(this).closest("tr")).data();
                $("#clientName,#clientEmail").html("");
                $("#clientName").html(data.fullname)
                $("#clientEmail").html(data.email)
                $("#client_id").val(data.enc)
                $("[name='ib_status']").val(data.ib_status).trigger("change");
                $("[name='ib_group']").val(data.ib_group).trigger("change");
                myModal.show();
                // swal.fire({
                //   icon: "info",
                //   title: "IB Status ==> " + data.ib_status
                // });

            });
            $('.ajaxDataTable tbody').on('click', '.editClient', function() {
                var data = dTtable.row($(this).closest("tr")).data();
                $.ajax({
                    url: "/admin/ajax",
                    type: "GET",
                    cache: false,
                    data: {
                        "action": "getClientDetails",
                        "id": data.enc
                    },
                    success: function(response) {
                        let resp = JSON.parse(response);
                        $.each(resp, function(key, value) {
                            console.log(key, value);
                            $('#editUserForm [name="' + key + '"]').val(value);
                        });
                        $('#editUserForm [name="country_code"]').trigger('change');
                    }
                });
                editUserModal.show();
            });
            $('.ajaxDataTable tbody').on('click', '.updateIb', function() {
                var data = dTtable.row($(this).closest("tr")).data();
                $(".clientName,.clientEmail,.client_id").html("");
                $(".clientName").html(data.fullname);
                $(".clientEmail").html(data.email);
                $(".client_id").val(data.enc);
                $('#ibUpdateForm select').each(function() {
                    this.selectedIndex = 0;
                });
                $.ajax({
                    url: "/admin/ajax",
                    type: "GET",
                    cache: false,
                    data: {
                        "action": "getIbList",
                        "id": data.enc
                    },
                    success: function(response) {
                        // var ibValues = JSON.parse(response);
                        $('.ib-select').val(null).trigger('change');
                        $.each(response, function(key, value) {
                            if (( value != "" && value != null) || key ==
                                'ib1') {
                                if (value == 'noIB') {
                                    value = '';
                                }
                                $('#ibUpdateForm select[name="' + key + '"]').prop('disabled',
                                    false);
                                $('#ibUpdateForm select[name="' + key + '"]').val(value)
                                    .trigger('change');
                            }
                        })
                    }
                });
                updateIbModal.show();
            });
            $('.ajaxDataTable tbody').on('click', '.statusToggle', function() {
                var data = dTtable.row($(this).closest("tr")).data();
                $("#userName,#userEmail").html("");
                $("#userName").html(data.fullname);
                $("#userEmail").html(data.email);
                $("#user_id").val(data.enc);

                $("#user_status").prop("checked", data.status == 1);
                $("#email_status").prop("checked", data.email_confirmed == 1);
                $("#kyc_verify").prop("checked", (data.kyc_verify == 1));
                statusModal.show();
            });
            $('.ajaxDataTable tbody tr').on('click', '.rmToggle', function() {
                var data = dTtable.row($(this).closest("tr")).data();
                $("#customerName,#customerEmail").html("");
                $("#customerName").html(data.fullname);
                $("#customerEmail").html(data.email);
                $("#customer_id").val(data.email);

                $.ajax({
                    url: "/admin/ajax",
                    type: "GET",
                    data: {
                        action: 'getRMbyGroup',
                        "id": data.enc
                    },
                    success: function(response) {
                        var userGroupIds = JSON.parse(response);
                        var defaultOption = $('<option></option>').val('').text('--Select--').attr(
                            'selected', 'selected');
                        $('#group_rm_list').html(defaultOption);
                        $.each(userGroupIds, function(index, option) {
                            var $option = $('<option></option>').val(option.email).text(
                                option
                                .username);
                            if (option.email === data.rmid) {
                                $option.attr('selected', 'selected');
                            }
                            $('#group_rm_list').append($option);
                        });
                    }
                });

                rmModal.show();
            });
            $('.ajaxDataTable tbody tr').on('click', '.viewClient', function() {
                var data = dTtable.row($(this).closest("tr")).data();
                location.href = "/admin/client_details/" + data.enc_id;
            });
        }
    </script>
    <script>
        window.dTtable = $('.ajaxDataTable').on("draw.dt", dTSelection).DataTable({
            dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
            buttons: [
                'excel'
            ],
            order: [
                [0, "desc"]
            ],
            "ajax": {
                "url": "/admin/ajax",
                "type": "GET",
                data: {
                    action: 'getClientList',
                },
            },
            columns: [
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data, type, row) {
                        // console.log(data);
                        // console.log(type);
                        // console.log(row);
                        // console.log('abhayssss');
                        var return_data = "<div class='d-grid'><div class='date'>" + row.created_date +
                            "</div><div class='time text-muted'>" + row.created_time + "</div></div>";
                        return return_data;
                    }
                },
                {
                    data: 'email',
                    name: 'email',
                    render: function(data, row, row_data) {
                        var return_data = "<a href='/admin/client_details/" + row_data.enc_id +
                            "'><div class='d-flex align-items-center'><div class='me-2'><svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg></div><div><div class='lh-1'><span>" +
                            row_data.fullname +
                            "</span></div><div class='lh-1'><span class='fs-11 text-muted'>" + row_data
                            .email + "</span></div></div></div></a>";
                        return return_data;
                    }
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'country',
                    name: 'country',
                    render: function(data, row, row_data) {
                        if (data) {
                            return '<span class="fi fis fi-' + data.toLowerCase() + ' me-2"></span>' + data;
                        } else {
                            return "-";
                        }
                    }
                },
                {
                    data: 'ib',
                    name: 'ib',
                    render: function(data, row, row_data) {
                        // console.log(JSON.stringify(row_data));
                        // console.log(JSON.stringify(data));
                        let ib_email = row_data.ib;
                        let ib_name = row_data.ib_name;
                        let svg =
                            "<svg  xmlns='http://www.w3.org/2000/svg'  width='24'  height='24'  viewBox='0 0 24 24'  fill='none'  stroke='currentColor'  stroke-width='2'  stroke-linecap='round'  stroke-linejoin='round'  class='icon icon-tabler icons-tabler-outline icon-tabler-user-pentagon text-dark'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M13.163 2.168l8.021 5.828c.694 .504 .984 1.397 .719 2.212l-3.064 9.43a1.978 1.978 0 0 1 -1.881 1.367h-9.916a1.978 1.978 0 0 1 -1.881 -1.367l-3.064 -9.43a1.978 1.978 0 0 1 .719 -2.212l8.021 -5.828a1.978 1.978 0 0 1 2.326 0z' /><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z' /><path d='M6 20.703v-.703a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.707' /></svg>";
                        if (data === '' || data == null ) {
                            ib_email = "";
                            ib_name = "noIB";
                            svg = '';
                        }
                        return "<div class='cursor-pointer updateIb d-flex align-items-center'><div class='me-2'>" +
                            svg + "</div><div><div class='lh-1'><span>" + ib_name +
                            "</span></div><div class='lh-1'><span class='fs-11 text-muted'>" + ib_email +
                            "</span></div></div></div></a>";

                    }
                    // render: function (data, row, row_data) {
                    // if (data === '' || data == null ) {
                    //   return '<span class="cursor-pointer updateIb btn-sm btn btn-outline-dark">noIB</span>';
                    // } else {
                    //   return '<span class="cursor-pointer updateIb"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" size="28" color="#000000" class="tabler-icon tabler-icon-user-square-rounded"><path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z"></path><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"></path><path d="M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05"></path></svg>'+data+'</span>';
                    // }
                    // }
                },
                {
                    data: 'ib_status',
                    name: 'ib_status',
                    render: function(data) {
                        if (data == 1) {
                            return "<button class='ibToggle badge btn-sm btn btn-outline-success'>Active IB</button>";
                        } else if (data == 2) {
                            return "<button class='ibToggle badge btn-sm btn btn-outline-danger'>Rejected</button>";
                        } else if (data == 0) {
                            return "<button class='ibToggle badge btn-sm btn btn-outline-info'>IB Requested</button>";
                        } else {
                            return "<button class='ibToggle badge btn-sm btn btn-outline-primary'>Not Requested</button>";
                        }
                    }
                },
                {
                    data: 'rm',
                    name: 'rm',
                    render: function(data, row, row_data) {
                        let html = '';
                        var role = <?php echo json_encode(session('userData')['userRole']); ?>;
                        if (row_data.rmid == "") {
                            html = '<button class="' + (role == "Super Admin" ? 'rmToggle ' : '') +
                                'badge  btn-sm btn btn-outline-dark">RM Not Mapped</button>';
                        } else {
                            html = '<span class="' + (role == "Super Admin" ? 'rmToggle ' : '') +
                                ' text-primary"> <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" size="25" class="tabler-icon tabler-icon-user-scan"><path d="M10 9a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M4 8v-2a2 2 0 0 1 2 -2h2"></path><path d="M4 16v2a2 2 0 0 0 2 2h2"></path><path d="M16 4h2a2 2 0 0 1 2 2v2"></path><path d="M16 20h2a2 2 0 0 0 2 -2v-2"></path><path d="M8 16a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2"></path></svg>' +
                                row_data.rm_id + '</span>';
                        }
                        return html;
                    }
                },
                // {
                //   data: 'total_deposit',
                //   name: 'total_deposit'
                // },
                // {
                //   data: 'total_withdraw',
                //   name: 'total_withdraw'
                // },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, row, row_data) {
                        let html = '';
                        var success = '';
                        if (parseInt(row_data.kyc_verify) >= 1) {
                            if(row_data.status == 0){
                                success = 'bg-success';
                            }else{
                                success = 'bg-success text-white';
                            }
                        }
                        html +='<span class="statusToggle" data-status="' + row_data.status + '">' + (
                            row_data.status == 0 ? '<span class="badge text-danger ' + success +
                            ' " data-bs-toggle="tooltip" title="Inactive User"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" size="25" class="tabler-icon tabler-icon-user-scan"><path d="M10 9a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M4 8v-2a2 2 0 0 1 2 -2h2"></path><path d="M4 16v2a2 2 0 0 0 2 2h2"></path><path d="M16 4h2a2 2 0 0 1 2 2v2"></path><path d="M16 20h2a2 2 0 0 0 2 -2v-2"></path><path d="M8 16a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2"></path></svg></span>' :
                            (row_data.status == 1 ? '<span class="badge text-success ' + success +
                                '" data-bs-toggle="tooltip" title="Active User"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" size="25" class="tabler-icon tabler-icon-user-scan" style=""><path d="M10 9a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path d="M4 8v-2a2 2 0 0 1 2 -2h2"></path><path d="M4 16v2a2 2 0 0 0 2 2h2"></path><path d="M16 4h2a2 2 0 0 1 2 2v2"></path><path d="M16 20h2a2 2 0 0 0 2 -2v-2"></path><path d="M8 16a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2"></path></svg></span>' :
                                "")) + '</span>';
                        html += '<span class="statusToggle" data-status="' + row_data.email_confirmed +
                            '">' + (row_data.email_confirmed == 0 ?
                                '<span class="badge text-danger" data-bs-toggle="tooltip" title="Email Not Verified"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="#FFCC80" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" size="25"  class="tabler-icon tabler-icon-mail-x"><path d="M13.5 19h-8.5a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v6"></path><path d="M3 7l9 6l9 -6"></path><path d="M22 22l-5 -5"></path><path d="M17 22l5 -5"></path></svg></span>' :
                                (row_data.email_confirmed == 1 ?
                                    '<span class="badge text-success" data-bs-toggle="tooltip" title="Email Verified"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="#81C784" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" size="25" color="#81C784" class="tabler-icon tabler-icon-mail-check"><path d="M11 19h-6a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v6"></path><path d="M3 7l9 6l9 -6"></path><path d="M15 19l2 2l4 -4"></path></svg></span>' :
                                    "")) + "</span>";
                        html += '<span class="viewClient" data-enc="' + row_data.enc_id +
                            '"><span class="badge text-danger" data-bs-toggle="tooltip" title="View Client"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-eye"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg></span></span>';
                        html += '<span class="editClient" data-enc="' + row_data.enc +
                            '"><span class="badge text-danger" data-bs-toggle="tooltip" title="Edit Client"><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-edit text-secondary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg></span></span>';
                        return html;
                    }
                },
                // {
                //   data: 'action',
                //   name: 'action',
                //   orderable: false,
                //   searchable: false
                // },
            ],
            initComplete: function() {
                var needs = [1, 4];
                this.api()
                    .columns()
                    .every(function(index) {
                        if (needs.indexOf(index) == -1) {
                            return false;
                        }
                        let column = this;
                        let title = column.header().textContent;
                        // Create input element
                        let input = document.createElement('input');
                        input.placeholder = title;
                        column.header().replaceChildren(input);

                        // Event listener for user input
                        input.addEventListener('keyup', () => {
                            if (column.search() !== this.value) {
                                column.search(input.value).draw();
                            }
                        });
                    });
            }
        });
        $("#statusUpdateForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "/admin/ajax",
                type: "POST",
                cache: false,
                data: $("#statusUpdateForm").serialize(),
                success: function(response) {
                   
                    if (response.success==true) {
                        swal.fire({
                            icon: "success",
                            title: "Status Successfully Updated",
                        }).then((val) => {
                            location.reload();
                        });
                    } else {
                        swal.fire({
                            icon: "error",
                            title: "Something went wrong.",
                            text: "Please try again or contact support."
                        }).then((val) => {
                            location.reload();
                        });
                    }
                }
            });
        });
    </script>
@endsection
