@extends('layouts.admin.admin')
@section('content')

<!-- Start::app-content -->
<div class="main-content app-content">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="page-title">IB Requests</h1>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">IB</li>
      </ol>
    </div>
    <div class="row">
      <div class="col-xl-12">
        <div class="card custom-card">
          <div class="card-body">
            <div class="table-responsive">
              <table id="tableIbUsers" class="table ajaxDataTable table-bordered text-nowrap w-100">
                <thead>
                  <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>ID</th>
                    <th>Name</th>
                    <!-- <th>Country</th>
                    <th>Number</th> -->
                    <th>Tot. Comm.</th>
                    <th>Tot. Withdrawal</th>
                    <th>Status / Action</th>
                    <th>Reg. Date</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Time</th>
                    <!-- <th>Action</th>   -->
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
<!-- End::app-content -->

@if(session('session_expired'))
    <script>
        alert('Session expired. Please log in again.');
    </script>
@endif

<!-- Modal -->
<div class="modal fade" id="ibModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ibModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="#" id="ibRequestForm"  method="POST">
        @csrf
        <input type="hidden" name="client_id" id="client_id" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="ibModalLabel">IB Request Management</h5>
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
              <p class="mb-0 text-muted fs-11" id="clientEmail"></p>
            </div>

          </div>
          <div class="card-body">
            <div class="mb-3 row">
              <div class="m-auto col-lg-4">
                <label class="form-label">IB Request Status</label>
              </div>
              <div class="col-lg-8">
                <select class="form-select" required name="ib_status" aria-label="Default select example">
                  <option value="" selected>--Status--</option>
                  <option value="1">Approve</option>
                  <option value="0">Pending</option>
                  <option value="2">Rejected</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="m-auto col-lg-4">
                <label class="form-label">Account Group</label>
              </div>
              <div class="col-lg-8">
                <select class="form-select" required name="ib_group" aria-label="Default select example">
                  <option value="" selected>--Plans--</option>
                  <?php foreach ($acc_groups as $gp) { ?>
                    <option value="<?= $gp->id ?>"><?= $gp->ib_cat_name ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="ibRequest" value="update" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="ibRequestApprovalModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ibModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="/admin/bulkIbApprove" id="ibRequestApproveForm" method="POST">
            @csrf
          <input type="hidden" name="client_id" id="client_id" value="">
          <div class="modal-header">
            <h5 class="modal-title" id="ibModalLabel">IB Request Management</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="mb-0 modal-body custom-card card">
            <div class="card-body">
              <div class="mb-3 row">
                <div class="m-auto col-lg-4">
                  <label class="form-label">IB Request Status</label>
                </div>
                <div class="col-lg-8">
                  <select class="form-select" required name="ib_status" aria-label="Default select example">
                    <option value="" selected>--Status--</option>
                    <option value="1">Approve</option>
                    <option value="0">Pending</option>
                    <option value="2">Rejected</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="m-auto col-lg-4">
                  <label class="form-label">Account Group</label>
                </div>
                <div class="col-lg-8">
                  <select class="form-select" required name="ib_group" aria-label="Default select example">
                    <option value="" selected>--Plans--</option>
                    <?php foreach ($acc_groups as $gp) { ?>
                      <option value="<?= $gp->id ?>"><?= $gp->ib_cat_name ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="ibRequest" value="update" class="btn btn-primary">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}'
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Something Went Wrong!',
                text: '{{ session('error') }}'
            });
        </script>
    @endif


@endsection
@section("scripts")
<script>
    $(document).ready(function () {
      window.myModal = new bootstrap.Modal(document.getElementById('ibModal'));

      // Store selected rows' data in an array
      let selectedRows = [];

      // Handle row selection
      function handleRowSelection() {
        selectedRows = [];
        $('.row-checkbox:checked').each(function () {
          const rowData = dTtable.row($(this).closest('tr')).data();
          selectedRows.push(rowData);
        });
      }

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

      // Single-row checkbox change event
      $(document).on('change', '.row-checkbox', function () {
        const rowId = $(this).data('id');
        const rowData = dTtable.row($(this).closest('tr')).data();

        if ($(this).is(':checked')) {
          selectedRows.push(rowData); // Add row data
        } else {
          // Remove row data
          selectedRows = selectedRows.filter(row => row.id !== rowId);
        }

        // Uncheck "Select All" if a single row is deselected
        if (!$(this).is(':checked')) {
          $('#select-all').prop('checked', false);
        }
      });

      // DataTable initialization
      window.dTtable = $('#tableIbUsers').on("draw.dt", dTSelection).DataTable({
        destroy: true,
        dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
        buttons: [
          {
            extend: 'excel',
            text: 'Export to Excel',
            filename: 'Pending_IB_Request_' + new Date().toISOString().slice(0, 10),
            exportOptions: {
              columns: [6, 7, 0, 2, 3, 4, 8, 9] // Updated column indices to match your use case
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
                $('#ibRequestApproveForm #client_id').val(selectedRows.join(',')); // Join IDs as a comma-separated string

                // Open the modal
                const modal = new bootstrap.Modal(document.getElementById('ibRequestApprovalModal'));
                modal.show();
            }
          }
        ],

        order: [[3, "desc"]],
        processing: true,
        serverSide: true,
        searching: true,
        ajax: {
          url: '/admin/getPendingIbUsers2',
          type: 'GET',
          data: {}, // Ensure this is populated dynamically if needed.
          dataSrc: function (json) {
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
          { data: 'id', name: 'id' },
          { data: 'name', name: 'name' },
          { data: 'total_deposit', name: 'total_deposit' },
          { data: 'total_withdrawal', name: 'total_withdrawal' },
          { data: 'status', name: 'status' },
          { data: 'date', name: 'date' },
          { data: 'fullname', name: 'fullname', visible: false },
          { data: 'fullemail', name: 'fullemail', visible: false },
          { data: 'created_date', name: 'created_date', visible: false },
          { data: 'created_time', name: 'created_time', visible: false },
        ]
      });

      // Initialize row selection after table draw
      function dTSelection() {
        $('.ajaxDataTable tbody tr').off();
        $('.ajaxDataTable tbody tr').on('click', '.ibToggle', function () {
          const data = dTtable.row($(this).closest('tr')).data();
          $("#ibRequestForm input,#ibRequestForm select").not("input[name='_token']").val("").trigger("change");
          $("#clientName,#clientEmail").html("");
          $("#clientName").html(data.fullname);
          $("#clientEmail").html(data.email);
          $("#client_id").val(data.user_id);
          $("[name='ib_status']").val(data.ib_status).trigger("change");
          $("[name='ib_group']").val(data.acc_type).trigger("change");
          myModal.show();
        });
      }
    });
  </script>

@endsection
