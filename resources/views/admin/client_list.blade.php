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
                <form action="{{ route('admin.addUser') }}" id="addUserForm" method="post">
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
                <form action="{{ route('admin.updateUser') }}" id="editUserForm" method="post">
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
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>IB Name/ Email</th>
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

        }
    </script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var dTtable = $('#ajaxDatatable').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: '/admin/getClientList',
                    type: 'GET',
                    data: {},
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                columns: [{
                        data: 'created_at',
                        name: 'created_at',
                        searchable: false
                    },
                    {
                        data: 'user_email',
                        name: 'fullname'
                    },
                    {
                        data: 'phone',
                        name: 'number'
                    },
                    {
                        data: 'user_country',
                        name: 'country'
                    },
                    {
                        data: 'ib',
                        name: 'ib',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'user_ib_status',
                        name: 'user_ib_status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'rm',
                        name: 'rm',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'fullname',
                        name: 'fullname',
                        visible: false,
                        render: function (data, row, row_data) {
                            return row_data.fullname;
                        },
                        searchable: false
                    },
                    {
                        data: 'fullemail',
                        name: 'fullemail',
                        visible: false,
                        render: function (data, row, row_data) {
                            return row_data.email;
                        },
                        searchable: false
                    },
                    {
                        data: 'ibemail',
                        name: 'ibemail',
                        visible: false,
                        render: function (data, row, row_data) {
                            let ib_email = row_data.ib;
                            return ib_email;
                        },
                        searchable: false
                    },
                    {
                        data: 'ibname',
                        name: 'ibname',
                        visible: false,
                        render: function (data, row, row_data) {
                            let ib_name = row_data.ib_name;
                            return ib_name;
                        },
                        searchable: false
                    },
                ],
                "initComplete": function() {
                    var needs = [2];
                    this.api()
                        .columns()
                        .every(function(index) {
                            if (needs.indexOf(index) == -1) {
                                return false;
                            }
                            let column = this;
                            let title = column.header().textContent;
                            let input = document.createElement('input');
                            input.placeholder = title;
                            column.header().replaceChildren(input);

                            input.addEventListener('keyup', () => {
                                if (column.search() !== this.value) {
                                    column.search(input.value).draw();
                                }
                            });
                        });
                },
                "rowCallback": function(row, data) {

                },

                "drawCallback": function(settings) {

                },
                order: [
                    [0, "desc"]
                ],
                lengthChange: true,
                pageLength: 10,
                lengthMenu: [ [10, 25, 50, 100, 500, 1000], [10, 25, 50, 100, 500, 1000] ],
                dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
                buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        exportOptions: {
                            columns: [0,8,9,10,2,3] // Exclude the `Name/Email` column (index 2)
                        }
                    }
                ],
            })

            dTtable.on('draw', function() {

                $('.ajaxDataTable tbody').off('click', '.updateIb');
                $('.ajaxDataTable tbody').on('click', '.updateIb', function() {
                    var data = dTtable.row($(this).closest("tr")).data();
                    $(".clientName,.clientEmail,.client_id").html("");
                    $(".clientName").html(data.fullname);
                    $(".clientEmail").html(data.email);
                    $(".client_id").val(data.id);
                    $('#ibUpdateForm select').each(function() {
                        this.selectedIndex = 0;
                    });
                    $.ajax({
                        url: "/admin/ajax",
                        type: "GET",
                        cache: false,
                        data: {
                            "action": "getIbList",
                            "id": data.id
                        },
                        success: function(response) {
                            // var ibValues = JSON.parse(response);
                            $('.ib-select').val(null).trigger('change');
                            $.each(response, function(key, value) {
                                if ((value != "" && value != null) || key ==
                                    'ib1') {
                                    if (value == 'noIB') {
                                        value = '';
                                    }
                                    $('#ibUpdateForm select[name="' + key +
                                        '"]').prop(
                                        'disabled',
                                        false);
                                    $('#ibUpdateForm select[name="' + key +
                                            '"]').val(value)
                                        .trigger('change');
                                }
                            })
                        }
                    });
                    updateIbModal.show();
                });

                $('.ajaxDataTable tbody tr').off('click', '.ibToggle');
                $('.ajaxDataTable tbody tr').on('click', '.ibToggle', function() {
                    var data = dTtable.row($(this).closest("tr")).data();
                    $("#clientName,#clientEmail").html("");
                    $("#clientName").html(data.fullname)
                    $("#clientEmail").html(data.email)
                    $("#client_id").val(data.id)
                    $("[name='ib_status']").val(data.ib_status).trigger("change");
                    $("[name='ib_group']").val(data.ib_group).trigger("change");
                    myModal.show();
                    // swal.fire({
                    //   icon: "info",
                    //   title: "IB Status ==> " + data.ib_status
                    // });

                });
                $('.ajaxDataTable tbody').off('click', '.editClient')
                $('.ajaxDataTable tbody').on('click', '.editClient', function() {
                    var data = dTtable.row($(this).closest("tr")).data();
                    $.ajax({
                        url: "/admin/ajax",
                        type: "GET",
                        cache: false,
                        data: {
                            "action": "getClientDetails",
                            "id": data.id
                        },
                        success: function(resp) {

                            $.each(resp, function(key, value) {

                                if (key === 'country_code') {
                                    value = value.replace('', '+');
                                }
                                if (key === 'telephone') {
                                    value = value.replace('+', '');
                                }
                                console.log(key, value);
                                $('#editUserForm [name="' + key + '"]').val(
                                    value);
                            });
                            $('#editUserForm [name="country_code"]').trigger('change');
                        }
                    });
                    editUserModal.show();
                });

                $('.ajaxDataTable tbody').on('click', '.switchClient', function(e) {
                    e.preventDefault(); // Prevent default behavior
                    var clientData = dTtable.row($(this).closest("tr")).data();

                    $.ajax({
                        url: "/admin/getClientSwitch", // Ensure this matches your backend route
                        type: "POST",
                        contentType: "application/json",
                        data: JSON.stringify({
                            action: "getClientSwitch",
                            id: clientData.id  // Pass the correct client ID
                        }),
                        success: function(resp) {
                            if (resp.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: resp.message,
                                }).then(() => {
                                    // Redirect using the URL from the server
                                    window.location.href = resp.redirectUrl;
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || "Can't switch user. Please try again.",
                            });
                        }
                    });
                });



                $('.ajaxDataTable tbody').off('click', '.statusToggle');
                $('.ajaxDataTable tbody').on('click', '.statusToggle', function() {
                    var data = dTtable.row($(this).closest("tr")).data();
                    $("#userName,#userEmail").html("");
                    $("#userName").html(data.fullname);
                    $("#userEmail").html(data.email);
                    $("#user_id").val(data.id);
                    $("#user_status").prop("checked", data.status == 1);
                    $("#email_status").prop("checked", data.email_confirmed == 1);
                    $("#kyc_verify").prop("checked", (data.kyc_verify == 1));
                    statusModal.show();
                });
                $('.ajaxDataTable tbody tr').off('click', '.rmToggle');
                $('.ajaxDataTable tbody tr').on('click', '.rmToggle', function() {
                    var data = dTtable.row($(this).closest("tr")).data();
                    $("#customerName,#customerEmail").html("");
                    $("#customerName").html(data.fullname);
                    $("#customerEmail").html(data.email);
                    $("#customer_id").val(data.id);

                    $.ajax({
                        url: "/admin/ajax",
                        type: "GET",
                        data: {
                            action: 'getRMbyGroup',
                            "id": data.id
                        },
                        success: function(response) {
                            var userGroupIds;
                            if (typeof response === 'string') {
                                try {
                                    userGroupIds = JSON.parse(response);
                                } catch (e) {
                                    console.error("Failed to parse JSON:", e);
                                    return; // Exit if parsing fails
                                }
                            } else {
                                userGroupIds = response;
                            }
                            var defaultOption = $('<option></option>').val('').text(
                                '--Select--').attr(
                                'selected', 'selected');
                            $('#group_rm_list').html(defaultOption);
                            $.each(userGroupIds, function(index, option) {
                                var $option = $('<option></option>').val(option
                                    .id).text(
                                    option
                                    .username);
                                if (option.id === data.rm_id) {
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
                    location.href = "/admin/client_details/" + data.id;
                });
            });


            $("#statusUpdateForm").submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: "/admin/ajax",
                    type: "POST",
                    cache: false,
                    data: $("#statusUpdateForm").serialize(),
                    success: function(response) {

                        if (response.success == true) {
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
        });
    </script>
@endsection
