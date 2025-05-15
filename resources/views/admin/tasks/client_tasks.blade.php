@extends('layouts.admin.admin')
@section('content')
    <style>
        tr.inactive {
            background-color: #f8f9fa;
            color: #6c757d;
            opacity: 0.6;
        }
    </style>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Client Tasks</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Client Tasks</li>
                </ol>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tableTasks" class="table tableTasks table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Completed On</th>
                                            <th>Name/Email</th>
                                            <th>Task Name</th>
                                            <th>Status</th>
                                            <th>Points</th>
                                            <th>Actions</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Date</th>
                                            <th>Time</th>
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

    <div class="modal fade" id="clientTasksUpdatemodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="clientTasksUpdatemodalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                {{-- <form action="{{ route('admin.tasks.approve_reject') }}" id="ClientTasksRequestForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="client_id" id="client_id" value="">
                    <input type="hidden" name="task_id" id="task_id" value=""> --}}
                <form action="{{ route('admin.tasks.approve_reject') }}" id="ClientTasksRequestForm" method="POST">
                    @csrf
                    <input type="hidden" name="client_id" id="client_id" value="">
                    <input type="hidden" name="task_id" id="task_id" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="clientTasksUpdatemodalLabel">Client Tasks Request Management</h5>
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
                            <p class="text-muted f-12" id="clientEmail"></p>
                        </div>

                        </div>
                        <div class="card-body">
                        <div class="mb-3 row">
                            <div class="m-auto col-lg-4">
                            <label class="form-label">Client Task Request Status</label>
                            </div>
                            <div class="col-lg-8">
                            <select class="form-select" required name="request_status" aria-label="Default select example">
                                <option selected>--Status--</option>
                                <option value="1">Approve</option>
                                <option value="2">Reject</option>
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

    <script>
        var tableTasks;
        var myModal;

        $(document).ready(function () {
            tableTasks = $('#tableTasks').DataTable({
                dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
                buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        filename: 'Client_Tasks_Status' + new Date().toISOString().slice(0, 10),
                        exportOptions: {
                            columns: [6,7,2,3,4,8,9],
                        }
                    }
                ],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: '/admin/getClientTasks',
                    type: 'GET',
                    data: function(d) {
                        d.action = 'getTasks';
                        return d;
                    },
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                columns: [
                    { data: 'created_at', name: 'created_at' },
                    { data: 'email', name: 'email' },
                    { data: 'task_name', name: 'task_name' },
                    { data: 'status', name: 'status' },
                    { data: 'points', name: 'points' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'name', name: 'name',visible: false },
                    { data: 'client_email', name: 'client_email', visible: false },
                    { data: 'date', name: 'date', visible: false },
                    { data: 'time', name: 'time', visible: false }
                ]
            });

            myModal = new bootstrap.Modal(document.getElementById('clientTasksUpdatemodal'));
        });

        $('#tableTasks').on('click', '.taskToggle', function () {
            var data = tableTasks.row($(this).closest("tr")).data();
            console.log(data); // ✅ Should now print to console
            // Populate modal with task/user data if needed
            $("#clientName").html(data.user?.fullname || "No name");
            $("#clientEmail").html(data.user?.email || "No email");
            $("#client_id").val(data.user_id);
            $("#task_id").val(data.id);
            myModal.show();
        });
    </script>

@endsection
