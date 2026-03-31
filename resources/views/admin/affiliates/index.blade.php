@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Affiliates Management</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Affiliates</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Affiliates List
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success btn-sm" onclick="window.location.href='{{ route('admin.affiliates.sample') }}'">
                                    <i class="fe fe-download me-1"></i> Download Sample
                                </button>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="fe fe-upload me-1"></i> Import Affiliates
                                </button>
                                @hasExportPermission('affiliates')
                                    <button class="btn btn-info btn-sm" onclick="window.location.href='{{ route('admin.affiliates.export') }}'">
                                        <i class="fe fe-download me-1"></i> Export All
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filter Section -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="statusFilter" class="form-label">Filter by Status</label>
                                    <select id="statusFilter" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                            </div>

                            <!-- DataTable -->
                            <div class="table-responsive">
                                <table id="affiliatesTable" class="table table-bordered text-nowrap border-bottom w-100">
                                    <thead>
                                        <tr>
                                            <th>Aff ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Country</th>
                                            <th>Status</th>
                                            <th>Balance</th>
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
            <!-- ROW END -->
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Affiliates</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="importForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fe fe-info me-2"></i>
                            <strong>Import Instructions:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Download the sample template first</li>
                                <li>Fill in your affiliate data</li>
                                <li>Required fields: affiliate_code, first_name, last_name, email</li>
                                <li>Supported formats: .xlsx, .xls, .csv</li>
                                <li>Maximum file size: 10MB</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <label for="excelFile" class="form-label">Choose Excel File</label>
                            <input type="file" class="form-control" id="excelFile" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div id="uploadProgress" class="progress d-none mb-3" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="importBtn">
                            <i class="fe fe-upload me-1"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Affiliate Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="affiliateDetails">
                    <!-- Details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<style>
    .badge {
        padding: 0.5em 0.75em;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .table td {
        vertical-align: middle;
        padding: 0.75rem 0.5rem;
    }
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        padding: 0.75rem 0.5rem;
    }
    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    #affiliatesTable {
        font-size: 0.9rem;
    }
    #affiliatesTable tbody tr:hover {
        background-color: #f8f9fa;
    }
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        padding: 0.375rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#affiliatesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.affiliates.data') }}',
            data: function(d) {
                d.status = $('#statusFilter').val();
            }
        },
        columns: [
            { 
                data: 'custom_id', 
                name: 'custom_id', 
                defaultContent: '-',
                width: '100px'
            },
            { 
                data: null,
                name: 'full_name',
                render: function(data) {
                    return '<strong>' + (data.first_name || '') + ' ' + (data.last_name || '') + '</strong>';
                },
                width: '200px'
            },
            { 
                data: 'email', 
                name: 'email', 
                defaultContent: '-',
                width: '220px'
            },
            { 
                data: 'country', 
                name: 'country', 
                defaultContent: '-',
                width: '100px',
                className: 'text-center'
            },
            { 
                data: 'status',
                name: 'status',
                render: function(data) {
                    const badges = {
                        'active': 'success',
                        'inactive': 'danger',
                        'pending': 'warning'
                    };
                    return `<span class="badge bg-${badges[data]}">${data.toUpperCase()}</span>`;
                },
                width: '100px',
                className: 'text-center'
            },
            { 
                data: 'available_balance',
                name: 'available_balance',
                render: function(data) {
                    return '<strong class="text-primary">$' + (parseFloat(data || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})) + '</strong>';
                },
                width: '120px',
                className: 'text-end'
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="action-buttons d-flex gap-1 justify-content-center">
                            <button class="btn btn-info btn-sm view-btn" data-id="${row.id}" title="View Details">
                                <i class="fe fe-eye"></i>
                            </button>
                            <select class="form-select form-select-sm status-select" data-id="${row.id}" style="width: 90px;">
                                <option value="active" ${row.status === 'active' ? 'selected' : ''}>Active</option>
                                <option value="inactive" ${row.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                <option value="pending" ${row.status === 'pending' ? 'selected' : ''}>Pending</option>
                            </select>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="${row.id}" title="Delete">
                                <i class="fe fe-trash"></i>
                            </button>
                        </div>
                    `;
                },
                width: '220px',
                className: 'text-center'
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        scrollX: false,
        autoWidth: false,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"></div>'
        }
    });

    // Filter by status
    $('#statusFilter').on('change', function() {
        table.ajax.reload();
    });

    // Import form submission
    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const importBtn = $('#importBtn');
        const progressBar = $('#uploadProgress');
        const progressBarInner = progressBar.find('.progress-bar');
        
        importBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Importing...');
        progressBar.removeClass('d-none');
        progressBarInner.css('width', '50%').text('50%');

        $.ajax({
            url: '{{ route('admin.affiliates.import') }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                progressBarInner.css('width', '100%').text('100%');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: `
                            <p>${response.message}</p>
                            <ul class="list-unstyled text-start">
                                <li><strong>✅ New Records:</strong> ${response.data.imported}</li>
                                <li><strong>🔄 Updated Records:</strong> ${response.data.updated}</li>
                                <li><strong>⏭️ Skipped (Duplicates):</strong> ${response.data.skipped}</li>
                                <li><strong>📊 Total Processed:</strong> ${response.data.total}</li>
                            </ul>
                            ${response.warnings ? '<hr><small class="text-warning">' + response.warnings.join('<br>') + '</small>' : ''}
                        `,
                        showConfirmButton: true
                    });
                    $('#importModal').modal('hide');
                    $('#importForm')[0].reset();
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorHtml = '<ul class="text-start">';
                
                if (response.errors) {
                    if (Array.isArray(response.errors)) {
                        response.errors.forEach(error => {
                            errorHtml += `<li>${error}</li>`;
                        });
                    } else {
                        Object.values(response.errors).forEach(errors => {
                            errors.forEach(error => {
                                errorHtml += `<li>${error}</li>`;
                            });
                        });
                    }
                }
                errorHtml += '</ul>';

                Swal.fire({
                    icon: 'error',
                    title: 'Import Failed',
                    html: response.message + errorHtml,
                    showConfirmButton: true
                });
            },
            complete: function() {
                importBtn.prop('disabled', false).html('<i class="fe fe-upload me-1"></i> Import');
                setTimeout(() => {
                    progressBar.addClass('d-none');
                    progressBarInner.css('width', '0%').text('0%');
                }, 1000);
            }
        });
    });

    // View details
    $(document).on('click', '.view-btn', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: `/admin/affiliates/${id}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#affiliateDetails').html(`
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="fw-bold">Affiliate Code:</label>
                                <p>${data.affiliate_code || '-'}</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="fw-bold">Custom ID:</label>
                                <p>${data.custom_id || '-'}</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="fw-bold">Status:</label>
                                <p><span class="badge bg-${data.status === 'active' ? 'success' : data.status === 'inactive' ? 'danger' : 'warning'}">${data.status.toUpperCase()}</span></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">First Name:</label>
                                <p>${data.first_name || '-'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Last Name:</label>
                                <p>${data.last_name || '-'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Email:</label>
                                <p>${data.email || '-'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Email Verified:</label>
                                <p>${data.email_verified ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Phone:</label>
                                <p>${data.phone || '-'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Country:</label>
                                <p>${data.country || '-'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Manager:</label>
                                <p>${data.manager || '-'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Referrer:</label>
                                <p>${data.referrer || '-'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Company Name:</label>
                                <p>${data.company_name || '-'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Website:</label>
                                <p>${data.website ? '<a href="' + data.website + '" target="_blank">' + data.website + '</a>' : '-'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Available Balance:</label>
                                <p>$${data.available_balance || '0.00'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Commission Rate:</label>
                                <p>${data.commission_rate || 0}%</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Affiliate Group:</label>
                                <p>${data.affiliate_group || '-'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Payout Groups:</label>
                                <p>${data.payout_groups || '-'}</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="fw-bold">2FA Active:</label>
                                <p>${data['2fa_active'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="fw-bold">Blocked:</label>
                                <p>${data.blocked ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-success">No</span>'}</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="fw-bold">Deleted:</label>
                                <p>${data.deleted ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-success">No</span>'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Creation Date:</label>
                                <p>${data.creation_date ? new Date(data.creation_date).toLocaleString() : '-'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Last Login:</label>
                                <p>${data.last_login ? new Date(data.last_login).toLocaleString() : '-'}</p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="fw-bold">Additional Info:</label>
                                <p>${data.additional_info || '-'}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Created At:</label>
                                <p>${new Date(data.created_at).toLocaleString()}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Updated At:</label>
                                <p>${new Date(data.updated_at).toLocaleString()}</p>
                            </div>
                        </div>
                    `);
                    $('#viewModal').modal('show');
                }
            }
        });
    });

    // Update status
    $(document).on('change', '.status-select', function() {
        const id = $(this).data('id');
        const status = $(this).val();
        
        $.ajax({
            url: `/admin/affiliates/${id}/status`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: status
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    table.ajax.reload(null, false);
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to update status',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });
    });

    // Delete affiliate
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/affiliates/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            );
                            table.ajax.reload();
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Failed to delete affiliate',
                            'error'
                        );
                    }
                });
            }
        });
    });
});
</script>
@endpush
