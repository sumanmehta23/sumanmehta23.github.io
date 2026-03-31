@extends('layouts.admin.admin')
@section('content')

<!-- Start::app-content -->
<div class="main-content app-content">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="page-title">IB Users</h1>
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
                    <th>ID</th>
                    <th>Agent Account</th>
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
                    <th>Phone Number</th>
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

<!-- Modal -->
<div class="modal fade" id="ibModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ibModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ url('/admin/bulkIbApprove') }}" id="ibRequestForm" method="POST">
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
                    <option value="<?= $gp->id?>"><?= $gp->ib_cat_name ?></option>
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
@endsection

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exportModalLabel">
          <i class="fas fa-download me-2"></i>Export IB Users
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="exportForm">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="export_email" class="form-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="export_email" name="email" required>
            <div class="form-text">The exported file will be sent to this email address.</div>
          </div>

          <div class="row">
            <div class="mb-3 col-md-6">
              <label for="date_from" class="form-label">Date From</label>
              <input type="date" class="form-control" id="date_from" name="date_from">
            </div>
            <div class="mb-3 col-md-6">
              <label for="date_to" class="form-label">Date To</label>
              <input type="date" class="form-control" id="date_to" name="date_to">
            </div>
          </div>

          <div class="mb-3">
            <label for="status_filter" class="form-label">Status Filter</label>
            <select class="form-select" id="status_filter" name="status">
              <option value="">All Statuses</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>

          <div class="alert alert-info d-flex align-items-center">
            <i class="fas fa-info-circle me-2"></i>
            <div>
              <strong>Processing Time:</strong> 2-10 minutes<br>
              <strong>Format:</strong> Excel (.xlsx)<br>
              <strong>Security:</strong> 24hr expiry link
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="startExportBtn">
            <i class="fas fa-download me-2"></i>Start Export
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
/* Simple export modal styles matching project theme */
#exportModal .modal-content,
#exportProgressModal .modal-content {
  border-radius: 10px;
}

#exportProgressModal .spinner-border {
  color: var(--primary-color);
}

#exportProgressModal .progress-bar {
  background-color: var(--primary-color);
}
</style>
@section("scripts")
<script>
  $(document).ready(function() {
    window.myModal = new bootstrap.Modal(document.getElementById('ibModal'));
  });

  function dTSelection() {
    $('.ajaxDataTable tbody tr').off();
    $('.ajaxDataTable tbody tr').on('click', '.ibToggle', function() {
      var data = dTtable.row($(this).closest("tr")).data();
      $("#ibRequestForm input,#ibRequestForm select").not("input[name='_token']").val("").trigger("change");
      $("#clientName,#clientEmail").html("");
      $("#clientName").html(data.fullname)
      $("#clientEmail").html(data.email)
      $("#client_id").val(data.id)
      $("[name='ib_status']").val(data.ib_status).trigger("change");
      $("[name='ib_group']").val(data.ib_plan_details_id).trigger("change");
      myModal.show();
    });
  }

  $(document).ready(function() {
    window.dTtable = $('#tableIbUsers').on("draw.dt", dTSelection).DataTable({
      order: [[0, "desc"]],
      destroy: true,
      dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
      buttons: (() => {
        let buttons = [];
        @hasExportPermission('ib_list_active')
          buttons.push({
            extend: 'excel',
            text: 'Export to Excelmmm',
            exportOptions: {
              columns: [7,8,11,3,4,5,9,10]
            }
          });
          buttons.push({
            text: 'Export Allnn',
            className: 'btn btn-primary export-btn',
            action: function () {
              openExportModal();
            }
          });
        @endif
        return buttons;
      })(),
      processing: true,
      serverSide: true,
      searching: true,
      ajax: {
        url: '/admin/getIbUsers2',
        type: 'GET',
        data: {},
        dataSrc: function(json) {
          return json.data;
        }
      },
      columns: [
        {data: 'id', name: 'id'},
        {data: 'agent_id', name: 'agent_id'},
        {data: 'name', name: 'name'},
        {data: 'total_deposit', name: 'total_deposit'},
        {data: 'total_withdrawal', name: 'total_withdrawal'},
        {data: 'status', name: 'status', orderable: false, searchable: false},
        {data: 'date', name: 'date'},
        {data: 'fullname', name: 'fullname'},
        {data: 'fullemail', name: 'fullemail'},
        {data: 'created_date', name: 'created_date'},
        {data: 'created_time', name: 'created_time'},
        {data: 'phone_number', name: 'phone_number'}
      ]
    });
  });

  let exportModal;

  $(document).ready(function() {
    exportModal = new bootstrap.Modal(document.getElementById('exportModal'));

    // Set default email if user is logged in
    @if(auth()->check())
      @php
        $userEmail = '';
        if (auth()->user() instanceof \App\Models\User) {
            $userEmail = auth()->user()->email ?? '';
        } elseif (auth()->user() instanceof \App\Models\EmployeeList) {
            $userEmail = auth()->user()->email ?? '';
        }
      @endphp
      const userEmail = '{{ $userEmail }}';
      if (userEmail) {
        $('#export_email').val(userEmail);
      }
    @endif

    // Handle export form submission
    $('#exportForm').on('submit', function(e) {
      e.preventDefault();
      startExport();
    });
  });

  // Open export modal
  function openExportModal() {
    exportModal.show();
  }

  // Start export process
  function startExport() {
    const email = $('#export_email').val();
    if (!email) {
      Swal.fire({
        icon: 'error',
        title: 'Email Required',
        text: 'Please enter an email address',
        confirmButtonColor: '#3085d6'
      });
      return;
    }

    // Show loading state
    const submitBtn = $('#startExportBtn');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Starting Export...').prop('disabled', true);

    // Send export request
    $.ajax({
      url: '/admin/export-all-ib-users',
      method: 'POST',
      data: $('#exportForm').serialize(),
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        exportModal.hide();

        Swal.fire({
          icon: 'success',
          title: 'Export Started!',
          text: response.message || 'Export started successfully! You will receive an email when ready.',
          confirmButtonColor: '#28a745',
          timer: 3000,
          timerProgressBar: true
        }).then(() => {
          // Reset form
          $('#exportForm')[0].reset();
          submitBtn.html(originalText).prop('disabled', false);

          // Reload page to show any alerts from redirect
          window.location.reload();
        });
      },
      error: function(xhr) {
        submitBtn.html(originalText).prop('disabled', false);

        let message = 'Export failed. Please try again.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          message = xhr.responseJSON.message;
        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
          const errors = Object.values(xhr.responseJSON.errors).flat();
          message = errors.join(', ');
        }

        Swal.fire({
          icon: 'error',
          title: 'Export Failed',
          text: message,
          confirmButtonColor: '#dc3545'
        });
      }
    });
  }

  // Show notification
  function showNotification(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' :
                       type === 'error' ? 'alert-danger' : 'alert-info';

    const notification = `
      <div class="alert ${alertClass} alert-dismissible fade show position-fixed"
           style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;"
           role="alert">
        <strong>${type === 'success' ? 'Success!' : type === 'error' ? 'Error!' : 'Info'}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;

    $('body').append(notification);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      $('.alert').last().fadeOut(500, function() {
        $(this).remove();
      });
    }, 5000);
  }
</script>
<script>
  $(document).ready(function() {
    window.myModal = new bootstrap.Modal(document.getElementById('ibModal'));
  });

  function dTSelection() {
    // alert("Init");
    $('.ajaxDataTable tbody tr').off();
    $('.ajaxDataTable tbody tr').on('click', '.ibToggle', function() {
      var data = dTtable.row($(this).closest("tr")).data();
      // console.log(data);
      $("#ibRequestForm input,#ibRequestForm select").not("input[name='_token']").val("").trigger("change");
      $("#clientName,#clientEmail").html("");
      $("#clientName").html(data.fullname)
      $("#clientEmail").html(data.email)
      $("#client_id").val(data.id)
      $("[name='ib_status']").val(data.ib_status).trigger("change");
      $("[name='ib_group']").val(data.ib_plan_details_id).trigger("change");
      myModal.show();
      // swal.fire({
      //   icon: "info",
      //   title: "IB Status ==> " + data.ib_status
      // });

    });
  }

  $(document).ready(function() {
    window.dTtable = $('#tableIbUsers').on("draw.dt", dTSelection).DataTable({
      order: [[0, "desc"]],
      destroy: true,
    //   "ajax": {
    //     "url": "/admin/ajax",
    //     "type": "GET",
    //     data: {
    //       action: 'getIbUsers',
    //     },
    //   },
      dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
      buttons: [
                {
                    extend: 'excel',
                    text: 'Export to Excel',
                    filename: 'IB_Users_' + new Date().toISOString().slice(0, 10),
                    exportOptions: {
                        columns: [7,8,11,3,4,5,9,10] // Updated column indices to match your use case
                    }
                },
                {
                    text: 'Export All',
                    className: 'btn btn-primary export-btn',
                    action: function () {
                        openExportModal();
                    }
                }
            ],
      processing: true,
      serverSide: true,
      searching: true,
      ajax: {
          url: '/admin/getIbUsers2',
           type: 'GET',
          data: {}, // Ensure this is populated dynamically if needed.
          dataSrc: function(json) {
              return json.data;
          }
      },
      columns: [{
          data: 'id',
          name: 'id'
        },
        {
          data: 'agent_id',
          name: 'agent_id'
        },
        {
          data: 'name',
          name: 'name',
        },
        {
          data: 'total_deposit',
          name: 'total_deposit',
          orderable: true,
          searchable: true
        },
        {
          data: 'total_withdrawal',
          name: 'total_withdrawal',
          orderable: true,
          searchable: true
        },
        {
          data: 'status',
          name: 'status',
        },
        {
          data: 'date',
          name: 'date',
        },
        { data: 'fullname', name: 'fullname', visible: false },
        { data: 'fullemail', name: 'fullemail', visible: false},
        { data: 'created_date', name: 'created_date', visible: false},
        { data: 'created_time', name: 'created_time', visible: false},
        { data: 'phone_number', name: 'phone_number', visible: false},
      ]
    });
  });

  // Initialize modals
  let exportModal, exportProgressModal;

  $(document).ready(function() {
    exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
    exportProgressModal = new bootstrap.Modal(document.getElementById('exportProgressModal'));

    // Set default email if user is logged in
    @if(auth()->check())
      @php
        $userEmail = '';
        if (auth()->user() instanceof \App\Models\User) {
            $userEmail = auth()->user()->email ?? '';
        } elseif (auth()->user() instanceof \App\Models\EmployeeList) {
            $userEmail = auth()->user()->email ?? '';
        }
      @endphp
      const userEmail = '{{ $userEmail }}';
      if (userEmail) {
        $('#export_email').val(userEmail);
      }
    @endif

    // Add real-time email validation
    $('#export_email').on('input', function() {
      const email = $(this).val();
      const isValid = isValidEmail(email);

      if (email && !isValid) {
        $(this).addClass('is-invalid');
      } else {
        $(this).removeClass('is-invalid');
      }
    });
  });

  // Open export modal
  function openExportModal() {
    exportModal.show();
  }

  // Advanced export functionality with enhanced UX
  function startAdvancedExport() {
    const form = document.getElementById('exportForm');
    const formData = new FormData(form);
    const exportBtn = document.querySelector('.export-start-btn');

    // Validate email
    const email = formData.get('export_email');
    if (!email || !isValidEmail(email)) {
      showAdvancedNotification('⚠️ Please enter a valid email address', 'error');
      return;
    }

    // Add loading state to button
    exportBtn.classList.add('loading');
    exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    // Close export modal and show progress modal
    exportModal.hide();

    // Small delay for better UX
    setTimeout(() => {
      exportProgressModal.show();
      updateExportStatus('🔍 Validating export parameters...');
    }, 300);

    // Prepare data for submission
    const exportData = {
      export_email: email,
      status: formData.get('status') || '',
      date_from: formData.get('date_from') || '',
      date_to: formData.get('date_to') || '',
      search: formData.get('search') || '',
      _token: formData.get('_token')
    };

    // Progressive status updates
    const statusUpdates = [
      { delay: 800, message: '📋 Preparing export parameters...' },
      { delay: 1600, message: '🚀 Queuing export job...' },
      { delay: 2400, message: '⚡ Starting background process...' },
      { delay: 3200, message: '📊 Export processing initiated...' }
    ];

    statusUpdates.forEach(update => {
      setTimeout(() => updateExportStatus(update.message), update.delay);
    });

    $.ajax({
        url: '/admin/export-all-ib-users',
        type: 'GET',
        data: exportData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            setTimeout(() => {
                exportProgressModal.hide();

                // Reset button state
                exportBtn.classList.remove('loading');
                exportBtn.innerHTML = '<i class="fas fa-rocket"></i> Start Export';

                if (response.success) {
                    showAdvancedNotification(
                        `🎉 <strong>Export queued successfully!</strong><br>
                        📧 <strong>Delivery email:</strong> ${response.export_email}<br>
                        ⏱️ <strong>Estimated time:</strong> ${response.estimated_time}<br>
                        <small>You'll receive email notifications about the progress.</small>`,
                        'success',
                        10000
                    );
                } else {
                    showAdvancedNotification(
                        `❌ ${response.message || 'Export failed. Please try again.'}`,
                        'error'
                    );
                }
            }, 4000);
        },
        error: function(xhr) {
            setTimeout(() => {
                exportProgressModal.hide();

                // Reset button state
                exportBtn.classList.remove('loading');
                exportBtn.innerHTML = '<i class="fas fa-rocket"></i> Start Export';

                let message = '❌ Export failed. Please try again.';
                let detailedMessage = '';

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        detailedMessage = '<br><small><strong>Issues found:</strong><br>• ' + errors.join('<br>• ') + '</small>';
                        message = '⚠️ Please fix the following issues:' + detailedMessage;
                    } else if (xhr.responseJSON.message) {
                        message = `❌ ${xhr.responseJSON.message}`;
                    }
                }

                showAdvancedNotification(message, 'error', 10000);
            }, 4000);
        }
    });
  }

  // Update export status in progress modal
  function updateExportStatus(status) {
    document.getElementById('exportStatus').textContent = status;
  }

  // Email validation
  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  // Enhanced notification system with better animations
  function showAdvancedNotification(message, type = 'info', duration = 8000) {
    const alertClass = type === 'success' ? 'alert-success' :
                      type === 'error' ? 'alert-danger' :
                      'alert-info';

    const icon = type === 'success' ? '🚀' :
                 type === 'error' ? '⚠️' :
                 '💡';

    const bgGradient = type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' :
                       type === 'error' ? 'linear-gradient(135deg, #ef4444, #dc2626)' :
                       'linear-gradient(135deg, #3b82f6, #2563eb)';

    const notification = `
      <div class="alert ${alertClass} alert-dismissible fade show position-fixed advanced-notification"
           style="top: 30px; right: 30px; z-index: 9999; min-width: 420px; max-width: 550px;
                  border-radius: 20px; box-shadow: 0 15px 50px rgba(0,0,0,0.3);
                  border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(15px);
                  background: ${bgGradient}; color: white;
                  animation: slideInRight 0.5s ease-out;"
           role="alert">
        <div style="display: flex; align-items: flex-start; padding: 5px;">
          <div style="font-size: 28px; margin-right: 18px;
                      filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">${icon}</div>
          <div style="flex: 1;">
            <div style="font-weight: 700; margin-bottom: 8px; font-size: 16px;
                        text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
              ${type === 'success' ? '✅ Success!' : type === 'error' ? '❌ Error!' : '💡 Information'}
            </div>
            <div style="font-size: 14px; line-height: 1.5; opacity: 0.95;">${message}</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                style="filter: brightness(1.2);"></button>
      </div>
    `;

    $('body').append(notification);

    // Enhanced auto-dismiss with slide animation
    setTimeout(() => {
      $('.advanced-notification').addClass('slideOutRight').fadeOut(500, function() {
        $(this).remove();
      });
    }, duration);
  }

  // Enhanced Export Controller Object
  const exportController = {
    showProgressModal() {
      exportModal.hide();
      exportProgressModal.show();
      this.updateProgress(0, 'Initializing export...');

      // Simulate realistic progress updates
      let progress = 0;
      const progressInterval = setInterval(() => {
        progress += Math.random() * 12 + 3;
        if (progress > 95) progress = 95;

        let message = 'Preparing data...';
        if (progress > 25) message = 'Processing IB users...';
        if (progress > 50) message = 'Generating Excel file...';
        if (progress > 75) message = 'Optimizing export...';
        if (progress > 90) message = 'Finalizing export...';

        this.updateProgress(progress, message);

        if (progress >= 95) {
          clearInterval(progressInterval);
        }
      }, 400);
    },

    updateProgress(percent, message) {
      const progressBar = document.querySelector('#export-progress .progress-bar');
      const progressText = document.querySelector('#progress-text');

      if (progressBar && progressText) {
        progressBar.style.width = `${percent}%`;
        progressBar.setAttribute('aria-valuenow', percent);
        progressText.textContent = message;
      }
    },

    showSuccess(message) {
      const modalBody = document.querySelector('#exportProgressModal .modal-body');
      if (modalBody) {
        modalBody.innerHTML = `
          <div class="text-center">
            <div class="mb-4 success-icon" style="animation: bounceIn 0.8s ease-out;">
              <i class="fas fa-check-circle text-success" style="font-size: 4rem; filter: drop-shadow(0 4px 8px rgba(16, 185, 129, 0.4));"></i>
            </div>
            <h3 class="mb-3 text-success" style="font-weight: 700;">Export Completed Successfully!</h3>
            <p class="mb-4 text-light" style="font-size: 16px; line-height: 1.6;">${message}</p>
            <div class="mt-4">
              <button type="button" class="px-4 btn btn-success btn-lg" data-bs-dismiss="modal"
                      style="border-radius: 15px; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);">
                <i class="fas fa-check me-2"></i>Perfect!
              </button>
            </div>
          </div>
        `;
      }
    },

    showError(message) {
      const modalBody = document.querySelector('#exportProgressModal .modal-body');
      if (modalBody) {
        modalBody.innerHTML = `
          <div class="text-center">
            <div class="mb-4 error-icon" style="animation: shake 0.8s ease-out;">
              <i class="fas fa-exclamation-triangle text-danger" style="font-size: 4rem; filter: drop-shadow(0 4px 8px rgba(239, 68, 68, 0.4));"></i>
            </div>
            <h3 class="mb-3 text-danger" style="font-weight: 700;">Export Failed</h3>
            <p class="mb-4 text-light" style="font-size: 16px; line-height: 1.6;">${message}</p>
            <div class="mt-4">
              <button type="button" class="btn btn-danger me-3" data-bs-dismiss="modal"
                      style="border-radius: 15px; box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);">
                <i class="fas fa-times me-2"></i>Close
              </button>
              <button type="button" class="btn btn-outline-light" onclick="exportController.resetModal()"
                      style="border-radius: 15px;">
                <i class="fas fa-redo me-2"></i>Try Again
              </button>
            </div>
          </div>
        `;
      }
    },

    resetModal() {
      exportProgressModal.hide();

      // Reset progress modal to initial state
      const modalBody = document.querySelector('#exportProgressModal .modal-body');
      if (modalBody) {
        modalBody.innerHTML = `
          <div class="text-center">
            <div class="mb-4 export-animation">
              <div class="spinner-border text-primary" role="status" style="width: 3.5rem; height: 3.5rem;">
                <span class="visually-hidden">Processing...</span>
              </div>
            </div>
            <h4 class="mb-3 text-light" style="font-weight: 600;">Processing Your Export</h4>
            <div id="export-progress" class="mb-3">
              <div class="progress" style="height: 10px; background: rgba(255,255,255,0.1); border-radius: 5px;">
                <div class="progress-bar bg-gradient" role="progressbar" style="width: 0%; border-radius: 5px;"
                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
            <p id="progress-text" class="mb-0 text-muted" style="font-size: 14px;">Initializing...</p>
          </div>
        `;
      }

      // Show export modal again with smooth transition
      setTimeout(() => {
        exportModal.show();
      }, 400);
    }
  };

  // Enhanced animation styles for success/error states
  const animationStyles = `
    <style>
      @keyframes bounceIn {
        0% { transform: scale(0.3); opacity: 0; }
        50% { transform: scale(1.05); opacity: 0.8; }
        70% { transform: scale(0.9); opacity: 0.9; }
        100% { transform: scale(1); opacity: 1; }
      }

      @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
      }

      @keyframes slideInRight {
        0% { transform: translateX(100%); opacity: 0; }
        100% { transform: translateX(0); opacity: 1; }
      }

      @keyframes slideOutRight {
        0% { transform: translateX(0); opacity: 1; }
        100% { transform: translateX(100%); opacity: 0; }
      }
    </style>
  `;

  // Inject animation styles
  if (!document.querySelector('#export-animations')) {
    const styleElement = document.createElement('div');
    styleElement.id = 'export-animations';
    styleElement.innerHTML = animationStyles;
    document.head.appendChild(styleElement);
  }

  // Legacy export function (kept for compatibility)
  function startExport() {
    openExportModal();
  }

  // Legacy notification helper function (kept for compatibility)
  function showNotification(message, type = 'info') {
    showAdvancedNotification(message, type, 5000);
  }
</script>
@endsection
