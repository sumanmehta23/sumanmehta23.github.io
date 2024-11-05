@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Page Permissions</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Page Permissions</li>
                </ol>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card-title mb-3">
                                        USER GROUPS
                                    </div>
                                    <div class="nav flex-column nav-pills me-3 tab-style-7" id="v-pills-tab" role="tablist"
                                        aria-orientation="vertical">
                                        <?php foreach ($roles as $k => $role):
                      $rolename = str_replace(' ', '-', $role->role_name) ?>
                                        <button data-tab="{{  $rolename }}" data-id="{{  $role->role_id }}"
                                            class="user-group nav-link text-start {{  $k == 0 ? 'active' : '' }}"
                                            id="{{  $rolename }}-tab" data-bs-toggle="pill"
                                            data-bs-target="#{{  $rolename }}" type="button" role="tab"
                                            aria-controls="{{  $rolename }}" aria-selected="false" tabindex="-1"><i
                                                class="ri-shield-user-line me-1 align-middle d-inline-block"></i>{{  $role->role_name }}</button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="tab-content" id="v-pills-tabContent">
                                        <?php foreach ($roles as $k => $role):
                      $rolename = str_replace(' ', '-', $role->role_name) ?>
                                        <div class="tab-pane permissions-tab {{  $k == 0 ? 'active show' : '' }}"
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
          $('.user-group:first').click();
        });
        $(document).on('change', '.permission-menu-main', function () {
          let isChecked = $(this).is(':checked');
          console.log(isChecked);
          let page = $(this).data('page');
          $('.permission-menu-sub[data-page="' + page + '"]').prop('checked', isChecked);
        });
        $(document).on('change', '.permission-menu-sub', function () {
          let page = $(this).data('page');
          let allChecked = true;
          let anyUnchecked = false;
          $('.permission-menu-sub[data-page="' + page + '"]').each(function () {
            if (!$(this).is(':checked')) {
              allChecked = false;
              anyUnchecked = true;
            }
          });
          if (allChecked) {
            $('.permission-menu-main[data-page="' + page + '"]').prop('checked', true);
          } else if (anyUnchecked) {
            $('.permission-menu-main[data-page="' + page + '"]').prop('checked', false);
          }
        });
      </script>
@endsection
