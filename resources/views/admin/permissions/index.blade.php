@extends('layouts.admin.admin')
@section('content')
    <style>
        tr.inactive {
            background-color: #f8f9fa;
            color: #6c757d;
            opacity: 0.6;
        }
    </style>
    <div class="modal fade" id="addPermissionModal" tabindex="-1" aria-labelledby="addPermissionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="addPermissionModalLabel">Add Permission</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.permissions.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-12">
                                <label for="input-label" class="form-label">Permission Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-12">
                                <label for="input-file" class="form-label">Description</label>
                                <textarea class="form-control" required name="description" rows="3"></textarea>
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

    <!-- Update Permission Modal -->
    <div class="modal fade" id="updatePermissionModal" tabindex="-1" aria-labelledby="updatePermissionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="updatePermissionModalLabel">Update Permission</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.update_roles') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-12">
                                <input type="hidden" name="role_id" required id="roleid">
                                <label for="input-label" class="form-label">Permission Name</label>
                                <input type="text" class="form-control" name="name" required id="rolename">
                            </div>
                            <div class="col-12">
                                <label for="input-file" class="form-label">Description</label>
                                <textarea class="form-control" required name="description" rows="3" id="roledesc"></textarea>
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
                <h1 class="page-title">Permissions</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Permissions</li>
                </ol>
            </div>
            <div class="mb-3 d-flex justify-content-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPermissionModal">
                    Add New Permission
                </button>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tablePermissions" class="table ajaxDataTable table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            {{-- <th>Actions</th> --}}
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
            window.dTtable = $('#tablePermissions').DataTable({
                processing: true,
                serverSide: true,
                
                // order: [[0, "desc"]],
                // "ajax": {
                //     "url": "/admin/ajax",
                //     "type": "GET",
                //     data: {
                //         action: 'getPermissions',
                //     },
                // },
                ajax: {
                    url: '/admin/getPermissions',
                    type: 'GET',
                    // data: function(d) {
                    // d.status = $('select[name=status]').val();
                    // return d;
                    // }, // Ensure this is populated dynamically if needed.
                    data: {},
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                columns: [
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    
                    // {
                    //     data: 'action',
                    //     name: 'action',
                    //     orderable: false,
                    //     searchable: false
                    // },
                ],
                "createdRow": function(row, data, dataIndex) {
                    
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
                        action: 'getPermissionDetails',
                        id: id
                    },
                    success: function(response) {
                        response = JSON.parse(response.trim());
                        $('#roleid').val(response.role_id);
                        $('#rolename').val(response.name);
                        $('#roledesc').text(response.description);
                        $('#rolestatus').prop('checked', response.is_active == 1);
                        $('#updatePermissionModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });

            });
        }
    </script>
@endsection
