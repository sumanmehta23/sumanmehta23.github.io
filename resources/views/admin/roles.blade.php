@extends('layouts.admin.admin')
@section('content')
    <style>
        tr.inactive {
            background-color: #f8f9fa;
            color: #6c757d;
            opacity: 0.6;
        }
    </style>
    <div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="addRoleModalLabel">Add Role</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.roles') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-12">
                                <label for="input-label" class="form-label">Role Name</label>
                                <input type="text" class="form-control" name="role_name" required>
                            </div>
                            <div class="col-12">
                                <label for="input-file" class="form-label">Description</label>
                                <textarea class="form-control" required name="role_desc" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="switch-sm"
                                        name="is_active">
                                    <label class="form-check-label" for="switch-sm">Active</label>
                                </div>
                            </div>
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

    <!-- Update Role Modal -->
    <div class="modal fade" id="updateRoleModal" tabindex="-1" aria-labelledby="updateRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="updateRoleModalLabel">Update Role</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.update_roles') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-12">
                                <input type="hidden" name="role_id" required id="roleid">
                                <label for="input-label" class="form-label">Role Name</label>
                                <input type="text" class="form-control" name="role_name" required id="rolename">
                            </div>
                            <div class="col-12">
                                <label for="input-file" class="form-label">Description</label>
                                <textarea class="form-control" required name="role_desc" rows="3" id="roledesc"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="is_active"
                                        id="rolestatus">
                                    <label class="form-check-label" for="switch-sm">Active</label>
                                </div>
                            </div>
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
                <h1 class="page-title">Roles</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Roles</li>
                </ol>
            </div>
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                    Add New Role
                </button>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tableRoles" class="ajaxDataTable table table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                            <th>Updated At</th>
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
        $(document).ready(function() {
            window.dTtable = $('#tableRoles').on("draw.dt", dTSelection).DataTable({
                // order: [[0, "desc"]],
                "ajax": {
                    "url": "/admin/ajax",
                    "type": "GET",
                    data: {
                        action: 'getRoles',
                    },
                },
                columns: [{
                        data: 'role_id',
                        name: 'id'
                    },
                    {
                        data: 'role_name',
                        name: 'group'
                    },
                    {
                        data: 'role_desc',
                        name: 'deposit'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'updated_at',
                        name: 'updated_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                "createdRow": function(row, data, dataIndex) {
                    if (data.is_active == 0) {
                        $(row).addClass('inactive');
                    }
                }
            });
        });

        function updateStatus(id, status) {
            Swal.fire({
                title: `Are you sure you want to ${status === 1 ? "activate" : "deactivate"} this role ?`,
                html: `
        <form id="updateStatusForm" method="post" action="{{ route('admin.update_role_status') }}">
            @csrf
          <input type="hidden" name="role_id" value="${id}">
          <input type="hidden" name="status" value="${status}">
          <input type="hidden" name="update_status" value="">
          </form>
      `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: `${status === 1 ? "Activate" : "Deactivate"}`,
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelector('#updateStatusForm').submit();
                }
            });
        }

        function dTSelection() {
            $(document).on("click", ".update-role", function() {
                let id = $(this).data("id");
                $.ajax({
                    url: "/admin/ajax",
                    type: "GET",
                    data: {
                        action: 'getRoleDetails',
                        id: id
                    },
                    success: function(response) {
                        response = JSON.parse(response.trim());
                        $('#roleid').val(response.role_id);
                        $('#rolename').val(response.role_name);
                        $('#roledesc').text(response.role_desc);
                        $('#rolestatus').prop('checked', response.is_active == 1);
                        $('#updateRoleModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });

            });
        }
    </script>
@endsection
