@extends('layouts.admin.admin')

@section('content')
    <div class="modal fade" id="addTicketModal" tabindex="-1" aria-labelledby="addTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="addTicketModalLabel1">New Ticket</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.addTicket') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-6">
                                <label for="input-label" class="form-label">Name</label>
                                <input type="text" class="form-control" name="subject_name" required>
                            </div>
                            <div class="col-6">
                                <label for="input-label" class="form-label">Type</label>
                                <select class="form-control" name="ticket_type_id" required>
                                    <option value="">Select Type</option>
                                    <?php foreach ($ticket_types as $type) { ?>
                                    <option value="<?php echo $type['id']; ?>"><?php echo $type['ticket_type']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="input-label" class="form-label">Description</label>
                                <textarea class="form-control" name="discription" required rows="3"></textarea>
                            </div>
                            <div class="col-6">
                                <label for="input-label" class="form-label">Assignee</label>
                                <select class="form-control" name="assignee_id" required>
                                    <?php if (isset($rm_details) && !empty($rm_details)): ?>
                                    <option selected value="{{ $rm_details->id }}">{{ $rm_details->username }}
                                    </option>
                                    <?php elseif (isset($superadmin_details) && !empty($superadmin_details)): ?>
                                    <option selected value="{{ $superadmin_details->id }}">Super Admin</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="input-label" class="form-label">Status</label>
                                <select class="form-control" name="ticket_status_id" required>
                                    <?php foreach ($ticket_status as $status) { ?>
                                    <option {{ $status['ticket_status'] == 'Open' ? 'selected' : '' }}
                                        value="<?php echo $status['id']; ?>">
                                        <?php echo $status['ticket_status']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="email" value="<?php echo $user->email; ?>" />
                        <input type="submit" class="btn btn-primary" name="add_ticket" value="Submit Ticket">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="kycModal" tabindex="-1" aria-labelledby="kycModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kycModalLabel">KYC Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <embed id="kycFile" src="" type="" width="100%">
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="accountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="accountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="#" id="createMT5Form" method="post">
                    <input type="hidden" name="client_id" id="client_id" value="{{ ($user->id) }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="accountModalLabel">Create New MT5 Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card">
                        <div class="row">
                            <div class="m-auto col-lg-4">
                                <label class="form-label">Account Type</label>
                            </div>
                            <div class="col-lg-8">
                                <select class="form-select acc-types" required name="acc-types">
                                    <option value="" selected>Choose Account Type</option>
                                        @foreach ($acc_types as $gp)
                                        <option value="{{ $gp->ac_index }}">{{ $gp->ac_name }}</option>
                                        @endforeach

                                </select>
                            </div>
                        </div>
                        <div class="mt-3 row">
                            <div class="m-auto col-lg-4">
                                <label class="form-label">Leverage</label>
                            </div>
                            <div class="col-lg-8">
                                <select class="form-select" name="leverage" id="leverage" required>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_account" value="update" class="btn btn-primary">Create
                            Account</button>
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
                    <input type="hidden" name="client_id" id="client_id" value="{{ ($user->id) }}">
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
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">Account Group</label>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-select" required name="ib_group"
                                        aria-label="Default select example">
                                        <option value="" selected>--Group--</option>
                                        <?php foreach ($acc_groups as $gp) { ?>
                                        <option value="{{ $gp->ib_plan_cat_id }}">{{ $gp->ib_cat_name }}</option>
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
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row-1 -->
            <div class="mt-5 row" id="user-profile">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">CLIENT DETAILS</div>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Client Details</li>
                            </ol>
                        </div>
                        <div class="card-body">
                            <div class="wideget-user">
                                <div class="row">
                                    <div class="col-lg-12 col-xl-12">
                                        <div class="wideget-user-desc d-flex">
                                            <div class="wideget-user-img d-flex align-items-center ">
                                                <img src="/admin_assets/assets/images/users/client.jpeg" alt="img"
                                                    style="width:100px">
                                            </div>
                                            <div class="user-wrap">
                                                <h4 class="fw-normal text-uppercase">{{ $user->fullname }}</h4>
                                                <h6 class="mb-3 fw-normal">
                                                    <span class="px-2"><span
                                                            class="fi fis fi-{{ strtolower(@$country_code->country_alpha) }} me-2"></span>{{ $user->country }}</span>
                                                    |
                                                    <span class="px-2">{!! $user->kyc_verify == 0
                                                        ? '<span class="badge bg-outline-danger">Pending KYC</span>'
                                                        : ($user->status == 1
                                                            ? '<span class="badge bg-outline-success">KYC Verified</span>'
                                                            : '') !!}</span>
                                                    |
                                                    <span
                                                        class="px-2"><strong>DOJ:</strong>{{ date('d M Y h:i A', strtotime($user->created_at)) }}</span>
                                                    |
                                                    <span class="px-2">{!! $user->status == 0
                                                        ? '<span class="badge bg-outline-danger">Inactive</span>'
                                                        : ($user->status == 1
                                                            ? '<span class="badge bg-outline-success">Active</span>'
                                                            : '') !!}</span>
                                                </h6>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <button
                                                                class="btn btn-icon bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                <i class="ri-mail-line"></i>
                                                            </button>
                                                            <div>
                                                                <div class="mb-0 text-muted fs-11">Email:</div>
                                                                <div class="mb-1 fs-12">{{ $user->email }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <button
                                                                class="btn btn-icon bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                <i class="ri-phone-line"></i>
                                                            </button>
                                                            <div>
                                                                <div class="mb-0 text-muted fs-11">Phone:</div>
                                                                <div class="mb-1 fs-12">{{ $user->number }}</div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="pt-1 mt-1 border-2 row border-top border-default">
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <button
                                                                class="btn btn-icon bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                <i class="ri-user-2-fill"></i>
                                                            </button>
                                                            <div>
                                                                <div class="mb-0 text-muted fs-11">Relationship Manager:
                                                                </div>
                                                                <div class="mb-1 fs-12">{{ $rm_details->username ?? '' }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <button
                                                                class="btn btn-icon bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                <i class="ri-user-line"></i>
                                                            </button>
                                                            <div>
                                                                <div class="mb-0 text-muted fs-11">Parent IB:</div>
                                                                <div class="mb-1 fs-12">{{ $user->ib1 }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-xl-12" style="display:none">
                                        <button type="button" class="shadow-sm btn btn-outline-light"><i
                                                class="ri-mail-line text-primary me-1"></i>Send Mail</button>
                                        <button type="button" class="shadow-sm btn btn-outline-light"><i
                                                class="ri-share-line"></i></button>
                                        <button type="button" class="shadow-sm btn btn-outline-light"><i
                                                class="ri-flag-line"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-top">
                            <div class="wideget-user-tab">
                                <div class="border-0 tab-menu-heading">
                                    <div class="tabs-menu1">
                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <div class="">
                            <div class="border-0">
                             
                            </div>
                        </div>
                    </div>
                </div><!-- COL-END -->
            </div>
            <!-- End:: row-1 -->
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $(document).on('click', '.ibToggle', function() {
                var data = $(this).data();
                $("#clientName,#clientEmail").html("");
                $("#clientName").html(data.fullname)
                $("#clientEmail").html(data.email)
                $("#client_id").val(data.enc)
                $("[name='ib_status']").val(data.ib_status).trigger("change");
                $("[name='ib_group']").val(data.ib_group).trigger("change");
                // myModal.show();
            });
            $('#tableDeposit').DataTable({
                "ajax": {
                    "url": "/admin/ajax",
                    "type": "GET",
                    data: {
                        action: 'getLatestDeposit',
                        id: '{{ $user->id }}'
                    },
                },
                columns: [{
                        data: 'created_on',
                        name: 'date'
                    },
                    {
                        data: 'from_to',
                        name: 'from_to'
                    },
                    {
                        data: 'payment_method',
                        name: 'method'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    }
                ]
            });
            $('#tableWithdrawal').DataTable({
                "ajax": {
                    "url": "/admin/ajax",
                    "type": "GET",
                    data: {
                        action: 'getLatestWithdrawal',
                        id: '{{ $user->id }}'
                    },
                },
                columns: [{
                        data: 'created_on',
                        name: 'date'
                    },
                    {
                        data: 'from_to',
                        name: 'from_to'
                    },
                    {
                        data: 'payment_method',
                        name: 'method'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'fee',
                        name: 'fee'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    }
                ]
            });
            $('#tableInternalTransfer').DataTable({
                "ajax": {
                    "url": "/admin/ajax",
                    "type": "GET",
                    data: {
                        action: 'getLatestTransfer',
                        id: '{{ $user->id }}'
                    },
                },
                columns: [{
                        data: 'created_on',
                        name: 'date'
                    },
                    {
                        data: 'from',
                        name: 'from'
                    },
                    {
                        data: 'to',
                        name: 'to'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    }
                ]
            });
        });
        // $("#ibRequestForm").submit(function(e) {
        //     e.preventDefault();
        //     $.ajax({
        //         url: "/admin/api/ajax",
        //         type: "POST",
        //         data: $("#ibRequestForm").serialize(),
        //         success: function(data) {
        //             if (data == "true") {
        //                 swal.fire({
        //                     icon: "success",
        //                     title: "IB Request Successfully Updated",
        //                 }).then((val) => {
        //                     location.reload();
        //                 });
        //             } else {
        //                 swal.fire({
        //                     icon: "error",
        //                     title: "Something went wrong.",
        //                     text: "Please try again or contact support."
        //                 }).then((val) => {
        //                     location.reload();
        //                 });
        //             }
        //         }
        //     });
        // });
        $("#createMT5Form").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "/admin/api/accounts",
                type: "POST",
                data: $("#createMT5Form").serialize(),
                success: function(data) {
                    if (data == "true") {
                        swal.fire({
                            icon: "success",
                            title: "Account Successfully Updated",
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
        });;
        $('#kycModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var fileSrc = button.data('bs-kyc');
            var fileType = button.data('bs-type');
            var modal = $(this);
            modal.find('#kycFile').attr('src', fileSrc);
            modal.find('#kycFile').attr('type', fileType);
        });

        function takeAction(id, email, status) {
            Swal.fire({
                title: `Are you sure you want to ${status === 1 ? "approve" : "reject"} this Document?`,
                html: `
            <form id="updateKYCForm" method="post" action="{{ route('admin.updateKyc') }}">
                @csrf
             <input type="hidden" name="id" value="${id}">
              <input type="hidden" name="email" value="${email}">
              <input type="hidden" name="status" value="${status}">
              <input type="hidden" name="action" value="update_kyc">
              <div class="mt-2 col-12 text-start">
                  <textarea id="description" name="description" rows="3" class="mt-2 form-control" placeholder="Add a description"></textarea>
              </div>
              </form>
          `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Submit',
                preConfirm: () => {
                    const description = document.querySelector('#updateKYCForm textarea').value;
                    if (!description) {
                        Swal.showValidationMessage('Please add a comment');
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelector('#updateKYCForm').submit();
                }
            });
        }
        $('#ibList .nav-link').on('click', function(e) {
            e.preventDefault();
            let tier = $(this).data('tier');
            $.ajax({
                url: '/admin/ajax',
                type: 'GET',
                data: {
                    action: 'getIbTierData',
                    id: '{{ $user->id }}',
                    tier: tier
                },
                success: function(response) {
                    $('#content').html(response);
                },
                error: function(xhr) {
                    console.error('Error loading content:', xhr);
                    $('#content').html('<p>Sorry, something went wrong.</p>');
                }
            });
        });
        $(document).ready(function() {
            $('.togglePassword').on('click', function() {
                var passwordField = $('#clientpassword');
                var passwordFieldType = passwordField.attr('type');
                if (passwordFieldType === 'password') {
                    passwordField.attr('type', 'text');
                    $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.4/clipboard.min.js"></script>
    <script>
        var clipboard = new ClipboardJS('#ibClient');
        clipboard.on('success', function(e) {
            Swal.fire({
                icon: "success",
                title: "IB Link Copied"
            })
        });
        $(document).ready(function() {
            $('[role="tab"]').click(function() {
                console.log($(this).attr("href"));
                if ($(this).attr("href")) {
                    location.hash = $(this).attr("href");
                } else if ($(this).data("bs-target")) {
                    location.hash = $(this).data("bs-target");
                }
            });
            if (location.hash) {
                var tab = location.hash;
                if ($('a[href="' + tab + '"]').length) {
                    var triggerEl = document.querySelector('a[href="' + tab + '"]')
                    bootstrap.Tab.getInstance(triggerEl).show() // Select tab by name
                }
            }
        })
    </script>
@endsection
