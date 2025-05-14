@extends('layouts.admin.admin')
@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Client - Requested Accounts</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item">Client List</li>
                    <li class="breadcrumb-item active" aria-current="page">Requested Accounts</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->


            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-none">
                            <div class="card-title">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="ajaxDatatable" class="table ajaxDataTable table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all"></th>
                                            <td>Client</td>
                                            <td>Trade ID</td>
                                            <td>Leverage</td>
                                            <td>Balance</td>
                                            <td>Registered Date</td>
                                            <td>Name</td>
                                            <td>Email</td>
                                            <td>Account Code</td>
                                            <td>Account Group</td>
                                            <td>Date</td>
                                            <td>Time</td>
                                            <td>Status</td>
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
    <div class="modal fade" id="accountUpdatemodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="accountUpdatemodalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="/admin/clientAccounts/activate_account" id="AccountRequestForm"  method="POST">
                     @csrf
                     <input type="hidden" name="client_id" id="client_id" value="">
                     <input type="hidden" name="options" id="account_type_id" value="">
                     <input type="hidden" name="leverage" id="leverage" value="">
                     <input type="hidden" name="account_id" id="account_id" value="">
                     <input type="hidden" name="accountType" id="accountType" value="">
                     <input type="hidden" name="demo_deposit" id="demo_deposit" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="accountUpdatemodalLabel">Client Account Request Management</h5>
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
                            {{-- <p class="mb-0 text-muted fs-11" id="clientEmail"></p> --}}
                        </div>

                        </div>
                        <div class="card-body">
                        <div class="mb-3 row">
                            <div class="m-auto col-lg-4">
                            <label class="form-label">Client Account Status</label>
                            </div>
                            <div class="col-lg-8">
                            <select class="form-select" required name="request_status" aria-label="Default select example">
                                <option selected>--Status--</option>
                                <option value="1">Approve</option>
                                {{-- <option value="0">Pending</option> --}}
                                <option value="2">Rejected</option>
                            </select>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="accountRequest" value="update" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bulkAccountUpdatemodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="bulkAccountUpdatemodalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="/admin/clientAccounts/bulk_activate_account" id="BulkAccountRequestForm"  method="POST">
                     @csrf
                     <input type="hidden" name="client_id" id="client_id" value="">
                     {{-- <input type="hidden" name="options" id="account_type_id" value="">
                     <input type="hidden" name="leverage" id="leverage" value=""> --}}
                     {{-- <input type="hidden" name="account_id" id="account_id" value=""> --}}
                     {{-- <input type="hidden" name="accountType" id="accountType" value=""> --}}
                     {{-- <input type="hidden" name="demo_deposit" id="demo_deposit" value=""> --}}
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkAccountUpdatemodalLabel">Client Account Request Management</h5>
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
                            {{-- <p class="mb-0 text-muted fs-11" id="clientEmail"></p> --}}
                        </div>

                        </div>
                        <div class="card-body">
                        <div class="mb-3 row">
                            <div class="m-auto col-lg-4">
                            <label class="form-label">Client Account Status</label>
                            </div>
                            <div class="col-lg-8">
                            <select class="form-select" required name="request_status" aria-label="Default select example">
                                <option selected>--Status--</option>
                                <option value="1">Approve</option>
                                {{-- <option value="0">Pending</option> --}}
                                <option value="2">Rejected</option>
                            </select>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="accountRequest" value="update" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection()
@section("scripts")
<!-- End::app-content -->
<script>
    $(document).ready(function() {
        window.myModal = new bootstrap.Modal(document.getElementById('accountUpdatemodal'));
    });
    // console.log(bootstrap.Modal);

    // $("#ibModal").modal();
    function dTSelection() {
        // alert("Init");
        $('.ajaxDataTable tbody tr').off();
        $('.ajaxDataTable tbody tr').on('click', '.ibToggle', function() {
            var data = dTtable.row($(this).closest("tr")).data();
            console.log(data.id);
            $("#AccountRequestForm input,#AccountRequestForm select").not("input[name='_token']").val("").trigger("change");
            $("#clientName,#clientEmail").html("");
            $("#account_id").val(data.id);
            $("#clientName").html(data.fullname || "");
            $("#clientEmail").html(data.email || "");
            $("#client_id").val(data.user_id);
            $("#leverage").val(data.leverage);
            $("#accountType").val(data.demo);
            $("#account_type_id").val(data.account_type_id);
            $("#demo_deposit").val(data.balance);
            $("[name='request_status']").val(data.request_status).trigger("change");
            myModal.show();

        });
    }

    $(document).ready(function() {

    // "Select All" functionality
    $('#select-all').on('click', function () {
        const isChecked = $(this).is(':checked');
        $('.row-checkbox').prop('checked', isChecked); // Toggle all checkboxes

        if (isChecked) {
            // Add all rows to selectedRows
            selectedRows = dTtable.rows().data().toArray();
        } else {
            selectedRows = []; // Clear selection
        }
    });

    window.dTtable = $('#ajaxDatatable').on("draw.dt", dTSelection).DataTable({
    // var dTtable = $('#ajaxDatatable').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            ajax: {
                url: '/admin/getRequestedAccountsList',
                type: 'GET',
                data: {}, // Ensure this is populated dynamically if needed.
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                    return `<input type="checkbox" class="row-checkbox" data-id="${row.id}">`;
                    }
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'leverage',
                    name: 'leverage'
                },
                {
                    data: 'balance',
                    name: 'balance',
                    orderable: false
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    orderable: false
                },
                {
                    data: 'fullname',
                    name: 'fullname',
                    visible: false,

                },
                {
                    data: 'fullemail',
                    name: 'fullemail',
                    visible: false,

                },
                {
                    data: 'account_code',
                    name: 'account_code',
                    visible: false,

                },
                {
                    data: 'account_group',
                    name: 'account_group',
                    visible: false,

                },
                {
                    data: 'created_date',
                    name: 'created_date',
                    visible: false,

                },
                {
                    data: 'created_time',
                    name: 'created_time',
                    visible: false,

                },
                {
                    data: 'account_request_status',
                    name: 'account_request_status',
                },

            ],
            rowCallback: function(row, data) {
                // Optional customization for rows
            },
            drawCallback: function(settings) {
                // Optional customization for draw events
            },
            order: [[0, "desc"]],
            lengthChange: true,
            pageLength: 10,
            // lengthMenu: [[10, 25, 50, 100, 500, 1000], [10, 25, 50, 100, 500, 1000]],
            dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
            buttons: [
                {
                    extend: 'excel',
                    text: 'Export to Excel',
                    filename: 'Requested_Accounts_' + new Date().toISOString().slice(0, 10),
                    exportOptions: {
                        columns: [5, 6, 7, 8, 2, 3, 9, 10] // Updated column indices to match your use case
                    }
                },
                {
                    text: 'Bulk Approve',
                    className: 'btn-bulk-action', // Optional: Add a custom class for styling
                    action: function (e, dt, node, config) {
                        // Get selected rows
                        const selectedRows = [];
                        $('.row-checkbox:checked').each(function () {
                            selectedRows.push($(this).data('id')); // Collect all selected row IDs
                        });

                        if (selectedRows.length === 0) {
                        alert('No rows selected!');
                        return;
                        }

                        // Populate the hidden input with selected IDs
                        $('#BulkAccountRequestForm #client_id').val(selectedRows.join(',')); // Join IDs as a comma-separated string

                        // Open the modal
                        const modal = new bootstrap.Modal(document.getElementById('bulkAccountUpdatemodal'));
                        modal.show();
                    }
                }
            ]
        });

    });


</script>
@endsection()
