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
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="password" id="addAdminPassword" required>
                                        <span class="cursor-pointer input-group-text showPassword"
                                            id="toggleAddAdminPassword">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="password_confirmation" id="addAdminConfirmPassword" required>
                                        <span class="cursor-pointer input-group-text showPassword"
                                            id="toggleAddAdminConfirmPassword">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    @include('partials.password-validation-rules-admin', ['prefix' => 'add-admin-'])
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
                            <input type="submit" class="btn btn-primary" id="addAdminSubmitBtn" value="Add">
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
                        <input type="hidden" name="id" id="id">
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
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="password" id="updateAdminPassword">
                                        <span class="cursor-pointer input-group-text showPassword"
                                            id="toggleUpdateAdminPassword">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="password_confirmation" id="updateAdminConfirmPassword">
                                        <span class="cursor-pointer input-group-text showPassword"
                                            id="toggleUpdateAdminConfirmPassword">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    @include('partials.password-validation-rules-admin', ['prefix' => 'update-admin-'])
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
                            <input type="submit" class="btn btn-primary" id="updateAdminSubmitBtn" value="Update">
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
          { data: 'username', title: 'Name' },
          { data: 'email', title: 'Email / Username' },
          { data: 'name', title: 'Role' },
          { data: 'permissions_count', title: 'Per. Count' },
          { data: 'status', title: 'Status' },
          { data: 'fa_status', title: '2FA Status' },
          {
          data: 'action',
          title: 'Action',
          render: function(data, row, row_data) {
            var return_data = '';
            var admin_role = '@can("employee:update")1 @endcan';
            var admin_role_id = @json(session('userData')['role_id']);
            if (admin_role ) {
              return_data += '<a data-id="' + row_data.enc_id + '" class="update-user" data-bs-toggle="modal" data-bs-target="#updateUserModal" ><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-edit text-secondary"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"></path><path d="M16 5l3 3"></path></svg></a>';
              if (row_data.role_id == 2) {
                return_data += '<a href="/admin/rm_dashboard?id=' + row_data.enc_id + '" class="ms-2" ><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-eye"><path class="text-primary" stroke="none" d="M0 0h24v24H0z" fill="none"></path><path class="text-primary" d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path><path class="text-primary" d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"></path></svg></a>';
              }
              // Add delete button for Super Admin or admin roles
              if (row_data.name && (row_data.name == 'Super Admin' || row_data.name == 'Admin')) {
                return_data += '<a data-id="' + row_data.enc_id + '" class="ms-2 delete-user text-danger" href="javascript:void(0)" title="Delete User"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M4 7h16"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"></path><path d="M9 7v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4"></path></svg></a>';
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
        $(document).off("click", ".update-user");
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
              // response=JSON.parse(response.trim());
              // console.log(response);
              $.each(response, function (key, value) {
                if (key == 'user_group_id') {
                  $('#update_admin_form #' + key).val(JSON.parse(value)).trigger("change");
                } else {
                  console.log('#update_admin_form #' + key);
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

        // Delete user handler
        $(document).off("click", ".delete-user");
        $(document).on("click", ".delete-user", function () {
          let id = $(this).data("id");
          Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this user. This action can be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
          }).then((result) => {
            if (result.isConfirmed) {
              $.ajax({
                url: "/admin/ajax",
                type: "GET",
                data: {
                  action: 'deleteAdminUser',
                  id: id
                },
                success: function (response) {
                  if (response.status) {
                    Swal.fire(
                      'Deleted!',
                      response.message,
                      'success'
                    );
                    $('#tableAdminUsers').DataTable().draw();
                  } else {
                    Swal.fire(
                      'Error!',
                      response.message,
                      'error'
                    );
                  }
                },
                error: function (xhr, status, error) {
                  console.error('AJAX Error:', status, error);
                  Swal.fire(
                    'Error!',
                    'An error occurred while deleting the user',
                    'error'
                  );
                }
              });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
              Swal.fire(
                'Cancelled',
                'User deletion has been cancelled',
                'info'
              );
            }
          });
        });
      }

    });
  </script>
  @include('partials.password-validation-script')
  <script>
    $(document).ready(function() {
    // Add User Modal Password Validation
    const addAdminPassword = document.getElementById('addAdminPassword');
    const addAdminConfirmPassword = document.getElementById('addAdminConfirmPassword');
    const toggleAddAdminPassword = document.getElementById('toggleAddAdminPassword');
    const toggleAddAdminConfirmPassword = document.getElementById('toggleAddAdminConfirmPassword');

    if (addAdminPassword && addAdminConfirmPassword) {
        // Initialize all rules to false
        window.updateRuleUI('add-admin-rule-length', false);
        window.updateRuleUI('add-admin-rule-uppercase', false);
        window.updateRuleUI('add-admin-rule-lowercase', false);
        window.updateRuleUI('add-admin-rule-digit', false);
        window.updateRuleUI('add-admin-rule-special', false);
        window.updateRuleUI('add-admin-rule-no-spaces', false);
        window.updateRuleUI('add-admin-rule-match', false);

        const handleAddAdminPasswordInput = () => {
            const password = addAdminPassword.value;
            const confirmPassword = addAdminConfirmPassword.value;

            if (!password) {
                window.updateRuleUI('add-admin-rule-length', false);
                window.updateRuleUI('add-admin-rule-uppercase', false);
                window.updateRuleUI('add-admin-rule-lowercase', false);
                window.updateRuleUI('add-admin-rule-digit', false);
                window.updateRuleUI('add-admin-rule-special', false);
                window.updateRuleUI('add-admin-rule-no-spaces', false);
                window.updateRuleUI('add-admin-rule-match', false);
            } else {
                const rules = window.checkPasswordRules(password, confirmPassword);
                window.updateRuleUI('add-admin-rule-length', rules.length);
                window.updateRuleUI('add-admin-rule-uppercase', rules.uppercase);
                window.updateRuleUI('add-admin-rule-lowercase', rules.lowercase);
                window.updateRuleUI('add-admin-rule-digit', rules.digit);
                window.updateRuleUI('add-admin-rule-special', rules.special);
                window.updateRuleUI('add-admin-rule-no-spaces', rules.noSpaces);
                window.updateRuleUI('add-admin-rule-match', confirmPassword ? rules.match : null);
            }
            window.checkAllRulesSatisfied('addAdminPassword', 'addAdminConfirmPassword', 'addAdminSubmitBtn');
        };

        const handleAddAdminConfirmInput = () => {
            const password = addAdminPassword.value;
            const confirmPassword = addAdminConfirmPassword.value;

            if (!confirmPassword) {
                window.updateRuleUI('add-admin-rule-match', false);
            } else {
                const rules = window.checkPasswordRules(password, confirmPassword);
                window.updateRuleUI('add-admin-rule-match', confirmPassword ? rules.match : null);
            }
            window.checkAllRulesSatisfied('addAdminPassword', 'addAdminConfirmPassword', 'addAdminSubmitBtn');
        };

        addAdminPassword.addEventListener('input', handleAddAdminPasswordInput);
        addAdminConfirmPassword.addEventListener('input', handleAddAdminConfirmInput);
    }

    // Password Visibility Toggles for Add Admin
    if (toggleAddAdminPassword && addAdminPassword) {
        toggleAddAdminPassword.addEventListener('click', (e) => {
            e.preventDefault();
            const type = addAdminPassword.type === 'password' ? 'text' : 'password';
            addAdminPassword.type = type;
            toggleAddAdminPassword.innerHTML = type === 'password' ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
        });
    }

    if (toggleAddAdminConfirmPassword && addAdminConfirmPassword) {
        toggleAddAdminConfirmPassword.addEventListener('click', (e) => {
            e.preventDefault();
            const type = addAdminConfirmPassword.type === 'password' ? 'text' : 'password';
            addAdminConfirmPassword.type = type;
            toggleAddAdminConfirmPassword.innerHTML = type === 'password' ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
        });
    }

    // Update User Modal Password Validation
    const updateAdminPassword = document.getElementById('updateAdminPassword');
    const updateAdminConfirmPassword = document.getElementById('updateAdminConfirmPassword');
    const toggleUpdateAdminPassword = document.getElementById('toggleUpdateAdminPassword');
    const toggleUpdateAdminConfirmPassword = document.getElementById('toggleUpdateAdminConfirmPassword');
    const updateAdminSubmitBtn = document.getElementById('updateAdminSubmitBtn');

    if (updateAdminPassword && updateAdminConfirmPassword) {
        // Initialize all rules to false
        window.updateRuleUI('update-admin-rule-length', false);
        window.updateRuleUI('update-admin-rule-uppercase', false);
        window.updateRuleUI('update-admin-rule-lowercase', false);
        window.updateRuleUI('update-admin-rule-digit', false);
        window.updateRuleUI('update-admin-rule-special', false);
        window.updateRuleUI('update-admin-rule-no-spaces', false);
        window.updateRuleUI('update-admin-rule-match', false);

        const handleUpdateAdminPasswordInput = () => {
            const password = updateAdminPassword.value;
            const confirmPassword = updateAdminConfirmPassword.value;

            if (!password) {
                // If password is empty, reset rules and enable button
                window.updateRuleUI('update-admin-rule-length', false);
                window.updateRuleUI('update-admin-rule-uppercase', false);
                window.updateRuleUI('update-admin-rule-lowercase', false);
                window.updateRuleUI('update-admin-rule-digit', false);
                window.updateRuleUI('update-admin-rule-special', false);
                window.updateRuleUI('update-admin-rule-no-spaces', false);
                window.updateRuleUI('update-admin-rule-match', false);
                if (updateAdminSubmitBtn) updateAdminSubmitBtn.disabled = false;
            } else {
                // If password has text, check rules and disable/enable button accordingly
                const rules = window.checkPasswordRules(password, confirmPassword);
                window.updateRuleUI('update-admin-rule-length', rules.length);
                window.updateRuleUI('update-admin-rule-uppercase', rules.uppercase);
                window.updateRuleUI('update-admin-rule-lowercase', rules.lowercase);
                window.updateRuleUI('update-admin-rule-digit', rules.digit);
                window.updateRuleUI('update-admin-rule-special', rules.special);
                window.updateRuleUI('update-admin-rule-no-spaces', rules.noSpaces);
                window.updateRuleUI('update-admin-rule-match', confirmPassword ? rules.match : null);

                // Disable button only if password has text but not all rules satisfied
                const allSatisfied = rules.length && rules.uppercase && rules.lowercase && rules.digit &&
                                    rules.special && rules.noSpaces && (confirmPassword ? rules.match : false);
                if (updateAdminSubmitBtn) updateAdminSubmitBtn.disabled = !allSatisfied;
            }
        };

        const handleUpdateAdminConfirmInput = () => {
            const password = updateAdminPassword.value;
            const confirmPassword = updateAdminConfirmPassword.value;

            if (!password) {
                // If password is empty, enable button
                window.updateRuleUI('update-admin-rule-match', false);
                if (updateAdminSubmitBtn) updateAdminSubmitBtn.disabled = false;
            } else {
                const rules = window.checkPasswordRules(password, confirmPassword);
                window.updateRuleUI('update-admin-rule-match', confirmPassword ? rules.match : null);

                // Disable button only if password has text but not all rules satisfied
                const allSatisfied = rules.length && rules.uppercase && rules.lowercase && rules.digit &&
                                    rules.special && rules.noSpaces && (confirmPassword ? rules.match : false);
                if (updateAdminSubmitBtn) updateAdminSubmitBtn.disabled = !allSatisfied;
            }
        };

        updateAdminPassword.addEventListener('input', handleUpdateAdminPasswordInput);
        updateAdminConfirmPassword.addEventListener('input', handleUpdateAdminConfirmInput);

        // Clear password fields when modal opens and enable button
        const updateUserModal = document.getElementById('updateUserModal');
        if (updateUserModal) {
            updateUserModal.addEventListener('show.bs.modal', () => {
                updateAdminPassword.value = '';
                updateAdminConfirmPassword.value = '';
                window.updateRuleUI('update-admin-rule-length', false);
                window.updateRuleUI('update-admin-rule-uppercase', false);
                window.updateRuleUI('update-admin-rule-lowercase', false);
                window.updateRuleUI('update-admin-rule-digit', false);
                window.updateRuleUI('update-admin-rule-special', false);
                window.updateRuleUI('update-admin-rule-no-spaces', false);
                window.updateRuleUI('update-admin-rule-match', false);
                if (updateAdminSubmitBtn) updateAdminSubmitBtn.disabled = false;
            });
        }
    }

    // Password Visibility Toggles for Update Admin
    if (toggleUpdateAdminPassword && updateAdminPassword) {
        toggleUpdateAdminPassword.addEventListener('click', (e) => {
            e.preventDefault();
            const type = updateAdminPassword.type === 'password' ? 'text' : 'password';
            updateAdminPassword.type = type;
            toggleUpdateAdminPassword.innerHTML = type === 'password' ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
        });
    }

    if (toggleUpdateAdminConfirmPassword && updateAdminConfirmPassword) {
        toggleUpdateAdminConfirmPassword.addEventListener('click', (e) => {
            e.preventDefault();
            const type = updateAdminConfirmPassword.type === 'password' ? 'text' : 'password';
            updateAdminConfirmPassword.type = type;
            toggleUpdateAdminConfirmPassword.innerHTML = type === 'password' ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
        });
    }
    });
  </script>
@endsection
