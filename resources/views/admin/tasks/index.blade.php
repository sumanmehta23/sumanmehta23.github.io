@extends('layouts.admin.admin')
@section('content')
    <style>
        tr.inactive {
            background-color: #f8f9fa;
            color: #6c757d;
            opacity: 0.6;
        }
    </style>
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="addTaskModalLabel">Add Task</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.tasks.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="taskName" class="form-label">Task Name</label>
                            <input type="text" class="form-control" id="taskName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="taskTitle" class="form-label">Task Title</label>
                            <input type="text" class="form-control" id="taskTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="taskDescription" class="form-label">Task Description</label>
                            <textarea class="form-control" id="taskDescription" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="taskPoints" class="form-label">Task Points</label>
                            <input type="number" class="form-control" id="taskPoints" name="points" required>
                        </div>
                        <div class="mb-3">
                            <label for="taskStatus" class="form-label">Task Status</label>
                            <select class="form-control" id="taskStatus" name="status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="expirationDate" class="form-label">Expiration Date</label>
                            <input type="datetime-local" class="form-control" id="expirationDate" name="expiration_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="editTaskModalLabel">Edit Task</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTaskForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editTaskId" name="id">
                    <div class="modal-body">
                        <input type="hidden" id="editTaskId" name="id">
                        <div class="mb-3">
                            <label for="editTaskName" class="form-label">Task Name</label>
                            <input type="text" class="form-control" id="editTaskName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskTitle" class="form-label">Task Title</label>
                            <input type="text" class="form-control" id="editTaskTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskDescription" class="form-label">Task Description</label>
                            <textarea class="form-control" id="editTaskDescription" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskPoints" class="form-label">Task Points</label>
                            <input type="number" class="form-control" id="editTaskPoints" name="points" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskStatus" class="form-label">Task Status</label>
                            <select class="form-control" id="editTaskStatus" name="status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editExpirationDate" class="form-label">Expiration Date</label>
                            <input type="datetime-local" class="form-control" id="editExpirationDate" name="expiration_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Tasks</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tasks</li>
                </ol>
            </div>
            <div class="mb-3 d-flex justify-content-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    Add New Task
                </button>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tableTasks" class="table table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Task Name</th>
                                            <th>Expiration Date</th>
                                            <th>Status</th>
                                            <th>Points</th>
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

    <script>
        $(document).ready(function () {

            var tableTasks = $('#tableTasks').DataTable({
                dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
                buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        filename: 'Tasks_' + new Date().toISOString().slice(0, 10),
                        exportOptions: {
                            columns: [0, 1, 2, 3] // Adjust column indices as needed
                        }
                    }
                ],
                lengthMenu: [
                    [10, 25, 50, 100, -1], // DataTable options
                    [10, 25, 50, 100, "All"] // User-facing labels
                ],
                pageLength: 10,
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: '/admin/getTasks',
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
                    { data: 'name', name: 'name' },
                    { data: 'expiration_date', name: 'expiration_date' },
                    { data: 'status', name: 'status' },
                    { data: 'points', name: 'points' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });

            // Add delete confirmation popup
            $('#tableTasks').on('click', '.deleteTask', function () {
                var data = tableTasks.row($(this).closest("tr")).data();

                Swal.fire({
                    title: `Are you sure you want to delete the task "${data.name}"?`,
                    html: `
                        <form id="delete_task_form" method="post" action="/admin/tasks/${data.id}">
                            @csrf
                            @method('DELETE')
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
                        document.querySelector('#delete_task_form').submit();
                    }
                });
            });

            $('#tableTasks').on('click', '.editTaskBtn', function () {
                var data = $('#tableTasks').DataTable().row($(this).closest("tr")).data();

                $('#editTaskForm').attr('action', '/admin/tasks/' + data.id); // dynamically set correct route
                $('#editTaskId').val(data.id);
                $('#editTaskName').val(data.name);
                $('#editTaskTitle').val(data.title);
                $('#editTaskDescription').val(data.description);
                $('#editTaskPoints').val(data.points);
                $('#editTaskStatus').val(data.status);
                $('#editExpirationDate').val(new Date(data.expiration_date).toISOString().slice(0, 16));
                $('#editTaskModal').modal('show');
            });
        });
    </script>
@endsection
