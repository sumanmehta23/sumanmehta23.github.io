@extends('layouts.admin.admin')
@section('content')
<style>
    .deleteAcc{
        cursor: pointer;
    }
</style>
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Client - Live Accounts</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item">Client List</li>
                    <li class="breadcrumb-item active" aria-current="page">Live Accounts</li>
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
                                            <td>Actions</td>
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
            $("#account_id").val(data.id)
            $("#clientName").html(data.fullname || "")
            $("#clientEmail").html(data.email || "")
            $("#client_id").val(data.user_id)
            $("#leverage").val(data.leverage)
            $("#account_type_id").val(data.account_type_id)
            $("[name='request_status']").val(data.request_status).trigger("change");
            myModal.show();

        });
        $('.ajaxDataTable tbody tr').on('click', '.deleteAcc', function() {
            var data = dTtable.row($(this).closest("tr")).data()
            // console.log(data.id);
            // console.log(data.fullemail);

            Swal.fire({
                    title: `Are you sure you want to delete this "${data.account_code}" account?`,

                    html: `
                    <form id="delete_account_form" method="post" action="deleteAccounts">
                    @csrf
                    <input type="hidden" name="id" value="${data.id}">
                    <input type="hidden" name="email" value="${data.fullemail}">
                    </form>
                `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    preConfirm: () => {
                    return true;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // location.href = "/admin/clientAccounts/deleteAccounts/" + data.id;
                         document.querySelector('#delete_account_form').submit();
                    }else {
                        location.href = "";
                    }
                });
        });
    }

    $(document).ready(function() {
    window.dTtable = $('#ajaxDatatable').on("draw.dt", dTSelection).DataTable({
    // var dTtable = $('#ajaxDatatable').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            ajax: {
                url: '/admin/getLiveAccountsList',
                type: 'GET',
                data: {}, // Ensure this is populated dynamically if needed.
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [
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

                },{
                    data: 'actions',
                    name: 'actions',

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
                    exportOptions: {
                        columns: [5, 6, 7, 8, 2, 3, 9, 10] // Updated column indices to match your use case
                    }
                }
            ]
        });
    });


</script>
@endsection()
