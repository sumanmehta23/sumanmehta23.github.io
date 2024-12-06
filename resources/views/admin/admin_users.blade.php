@extends('layouts.admin.admin')
@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="addUserModalLabel">Add User</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{route('admin.saveUser')}}">
                        @csrf
                        <div class="modal-body">
                            <div class="row gy-4">
                                <div class="col-6">
                                    <label for="username" class="form-label">Name</label>
                                    <input type="text" class="form-control" name="username" required>
                                </div>
                                <div class="col-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="col-6">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <div class="col-6">
                                    <label for="number" class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="number" required>
                                </div>
                                <div class="col-6">
                                    <label for="role_id" class="form-label">Role</label>
                                    <select class="form-control" name="role_id" required>
                                        <option value="">Select Role</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" class="form-control" name="company_name" required>
                                </div>
                                <div class="col-6">
                                    <label for="is_active" class="form-label">Status</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active">
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" class="btn btn-primary" value="Add">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="updateUserModal" tabindex="-1" aria-labelledby="updateUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="updateUserModalLabel">Update User</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{route('admin.saveUser')}}" id="update_admin_form">
                        @csrf
                        <input type="hidden" name="user_id" id="client_index">
                        <div class="modal-body">
                            <div class="row gy-4">
                                <div class="col-6">
                                    <label for="username" class="form-label">Name</label>
                                    <input type="text" class="form-control" name="username" id="username" required>
                                </div>
                                <div class="col-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="email" required>
                                </div>
                                <div class="col-6">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" id="password" required>
                                </div>
                                <div class="col-6">
                                    <label for="number" class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="number" id="number" required>
                                </div>
                                <div class="col-6">
                                    <label for="role_id" class="form-label">Role</label>
                                    <select class="form-control" name="role_id" id="role_id" required>
                                        <option value="">Select Role</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" class="form-control" name="company_name" id="company_name" required>
                                </div>
                                <div class="col-6">
                                    <label for="status" class="form-label">Status</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="status" name="is_active">
                                        <label class="form-check-label" for="status">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" class="btn btn-primary" value="Update">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="page-header">
            <h1 class="page-title">Admin Users</h1>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
              <li class="breadcrumb-item active" aria-current="page">Admin Users</li>
            </ol>
          </div>
          <div class="mb-3 d-flex justify-content-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
              Add New User
            </button>
          </div>
          <div class="row">
            <div class="col-xl-12">
              <div class="card custom-card">
                <div class="card-body">
                  <div class="table-responsive">
                    <table id="tableAdminUsers" class="table ajaxDataTable table-bordered text-nowrap w-100">
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
      $('#tableAdminUsers').on("draw.dt", dTSelection).DataTable({
        order: [[0, "desc"]],
        "ajax": {
          "url": "/admin/ajax",
          "type": "GET",
          data: {
            action: 'getAdminUsers',
          },
        },
        columns: [
          { data: 'client_index', title: '#' },
          { data: 'username', title: 'Name' },
          { data: 'email', title: 'Email / Username' },
          { data: 'name', title: 'Role' },
          { data: 'permissions_count', title: 'Per. Count' },
          { data: 'status', title: 'status' },
          {
          data: 'action',
          title: 'Action',
          render: function(data, row, row_data) {
            var return_data = '';
            var admin_role = @json(session('userData')['role_id']);
            var admin_role_id = @json(session('userData')['role_id']);
            if (admin_role == "Super Admin" || true) {
              return_data += '<a data-id="' + row_data.client_index + '" class="update-user" data-bs-toggle="modal" data-bs-target="#updateUserModal" ><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-edit text-secondary"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"></path><path d="M16 5l3 3"></path></svg></a>';
              if (row_data.role_id == 2) {
                return_data += '<a href="/admin/rm_dashboard?id=' + row_data.enc_id + '" class="ms-2" ><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-eye"><path class="text-primary" stroke="none" d="M0 0h24v24H0z" fill="none"></path><path class="text-primary" d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path class="text-primary" d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"></path></svg></a>';
              }
            }
            return return_data;
          },
          orderable: false,
          searchable: false
        },
        // { data: 'action', title: 'action', orderable: false, searchable: false },
        ]
      });

      function dTSelection() {
        $(document).on("click", ".update-user", function () {
          let id = $(this).data("id");
          $.ajax({
            url: "/admin/ajax",
            type: "GET",
            data: {
              action: 'getAdminDetails',
              id: id
            },
            success: function (response) {
              response=JSON.parse(response.trim());
              console.log(response);
              $.each(response, function (key, value) {
                if (key == 'user_group_id') {
                  $('#update_admin_form #' + key).val(JSON.parse(value)).trigger("change");
                } else {
                  $('#update_admin_form #' + key).val(value);
                }
              });
              $('#update_admin_form #status').prop('checked', response.status == 1);
              $('#updateUserModal').modal('show');
            },
            error: function (xhr, status, error) {
              console.error('AJAX Error:', status, error);
            }
          });

        });
      }

    });
  </script>
@endsection
