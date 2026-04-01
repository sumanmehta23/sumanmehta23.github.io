@extends('layouts.admin.admin')

@section('content')
<style>
  .alert {
      padding: 15px;
      background-color: #4CAF50;
      color: white;
      border-radius: 5px;
      margin-bottom: 15px;
      position: relative;
      font-size: 16px;
  }

  .alert .close {
      position: absolute;
      top: 2px;
      right: 15px;
      background: none;
      border: none;
      color: #1a2638;
      font-size: 20px;
      font-weight: bold;
      cursor: pointer;
  }

  .alert .close:hover {
      color: #fff;
  }
</style>
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Page Permissions</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Page Permissions</li>
                </ol>
            </div>
            @if(session('permissions'))
                <div class="alert alert-success" role="alert">
                    {{ session('permissions') }}
                    <!-- Close Button -->
                    <button type="button" class="close" onclick="this.parentElement.style.display='none'">
                        &times;
                    </button>
                </div>
            @endif        
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3 card-title">
                                        USER GROUPS
                                    </div>
                                    <div class="nav flex-column nav-pills me-3 tab-style-7" id="v-pills-tab" role="tablist"
                                        aria-orientation="vertical">
                                        <?php foreach ($roles as $k => $role):
                      $rolename = str_replace(' ', '-', $role->name) ?>
                                        <button data-tab="{{  $rolename }}" data-id="{{  $role->id }}"
                                            class="user-group nav-link text-start {{ (!request('role_id') && $k == 0) || request('role_id') == $role->id ? 'active' : '' }}"
                                            id="{{  $rolename }}-tab" data-bs-toggle="pill"
                                            data-bs-target="#{{  $rolename }}" type="button" role="tab"
                                            aria-controls="{{  $rolename }}" aria-selected="{{ (!request('role_id') && $k == 0) || request('role_id') == $role->id ? 'true' : 'false' }}" tabindex="-1"><i
                                                class="align-middle ri-shield-user-line me-1 d-inline-block"></i>{{  $role->name }}</button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="tab-content" id="v-pills-tabContent">
                                        <?php foreach ($roles as $k => $role):
                      $rolename = str_replace(' ', '-', $role->name) ?>
                                        <div class="tab-pane permissions-tab {{ (!request('role_id') && $k == 0) || request('role_id') == $role->id ? 'active show' : '' }}"
                                            id="{{  $rolename }}" role="tabpanel" tabindex="0"
                                            aria-labelledby="{{  $rolename }}-tab">
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).on("click", ".user-group", function () {
          let id = $(this).data("id");
          console.log(id);
          let tab = $(this).data("tab");
          $.ajax({
            url: "{{route('admin.permissionsList')}}",
            type: "GET",
            data: { id: id },
            success: function (response) {
              $('#' + tab).html(response);
            },
            error: function (xhr, status, error) {
              console.error('AJAX Error:', status, error);
            }
          });
        });
        $(document).ready(function () {
          // Check if role_id is in URL, otherwise use first role
          let selectedRoleId = "{{ request('role_id') }}";
          let roleToClick;
          
          if (selectedRoleId) {
            // Find the button with the matching role ID
            roleToClick = $('.user-group[data-id="' + selectedRoleId + '"]');
          } else {
            // Default to first role
            roleToClick = $('.user-group:first');
          }
          
          // Trigger click on the selected role
          if (roleToClick.length) {
            roleToClick.click();
          }
        });
        $(document).on('change', '.permission-menu-main', function () {
          let isChecked = $(this).is(':checked');
          console.log(isChecked);
          let group = $(this).data('group');
          $('.permission-menu-sub[data-group="' + group + '"]').prop('checked', isChecked);
        });
        $(document).on('change', '.permission-menu-sub', function () {
          let group = $(this).data('group');
          let allChecked = true;
          let anyUnchecked = false;
          $('.permission-menu-sub[data-group="' + group + '"]').each(function () {
            if (!$(this).is(':checked')) {
              allChecked = false;
              anyUnchecked = true;
            }
          });
          if (allChecked) {
            $('.permission-menu-main[data-group="' + group + '"]').prop('checked', true);
          } else if (anyUnchecked) {
            $('.permission-menu-main[data-group="' + group + '"]').prop('checked', false);
          }
        });

        function closeAlert(button) {
            const alertBox = button.parentElement;
            alertBox.classList.add('hide'); // Apply fade-out class
            setTimeout(() => alertBox.style.display = 'none', 500); // Hide alert after transition
        }
      </script>
@endsection
