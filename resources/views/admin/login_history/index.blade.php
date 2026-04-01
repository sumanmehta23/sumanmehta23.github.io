@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Login History</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Login History</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                User Login History
                            </div>
                            <div class="d-flex gap-2">
                                @hasExportPermission('login_history')
                                    <button class="btn btn-info btn-sm" id="exportBtn" title="Export to Excel">
                                        <i class="fe fe-download me-1"></i> Export
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Info Alert -->
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fe fe-info me-2"></i>
                                <strong>Note:</strong> By default, this page shows login history from the last 30 days. Use the date filters below to view a different date range.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            
                            <!-- Filter Section -->
                            <div class="row mb-3 g-2">
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label for="actionFilter" class="form-label">Filter by Action</label>
                                    <select id="actionFilter" class="form-select">
                                        <option value="">All Actions</option>
                                        <option value="login">Login</option>
                                        <option value="logout">Logout</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label for="dateFromFilter" class="form-label">Date From</label>
                                    <input type="date" id="dateFromFilter" class="form-control">
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label for="dateToFilter" class="form-label">Date To</label>
                                    <input type="date" id="dateToFilter" class="form-control">
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-secondary btn-sm w-100" id="clearFilters">
                                        <i class="fe fe-x me-1"></i> Clear
                                    </button>
                                </div>
                            </div>

                            <!-- DataTable -->
                            <div class="table-responsive">
                                <table id="loginHistoryTable" class="table table-bordered text-nowrap border-bottom w-100">
                                    <thead>
                                        <tr>
                                            <th>User Email</th>
                                            <th>User Name</th>
                                            <th>IP Address</th>
                                            <th>Country</th>
                                            <th>Date & Time</th>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#loginHistoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.login-history.data') }}',
            data: function(d) {
                d.action = $('#actionFilter').val();
                d.date_from = $('#dateFromFilter').val();
                d.date_to = $('#dateToFilter').val();
            }
        },
        columns: [
            { 
                data: 'user_email', 
                name: 'user_email',
                defaultContent: '-',
                width: '200px',
                render: function(data, type, row) {
                    if (row.user_id) {
                        return '<a href="{{ url('/admin/client_details') }}/' + row.user_id + '" class="text-primary" title="View User Details"><i class="fe fe-user me-1"></i>' + (data || 'N/A') + '</a>';
                    }
                    return data || 'N/A';
                }
            },
            { 
                data: 'user_name', 
                name: 'user_name',
                defaultContent: '-',
                width: '180px'
            },
            { 
                data: 'ip', 
                name: 'ip',
                defaultContent: '-',
                width: '150px'
            },
            { 
                data: 'country', 
                name: 'country',
                defaultContent: 'Unknown',
                width: '120px',
                className: 'text-center'
            },
            { 
                data: 'created_date_js',
                name: 'created_date_js',
                defaultContent: '-',
                width: '180px',
                className: 'text-center',
                render: function(data) {
                    return data || 'N/A';
                }
            }
        ],
        order: [[4, 'desc']], // Sort by date descending
        pageLength: 25,
        scrollX: false,
        autoWidth: false,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"></div>'
        }
    });

    // Filter by action
    $('#actionFilter').on('change', function() {
        table.ajax.reload();
    });

    // Filter by date from
    $('#dateFromFilter').on('change', function() {
        table.ajax.reload();
    });

    // Filter by date to
    $('#dateToFilter').on('change', function() {
        table.ajax.reload();
    });

    // Clear all filters
    $('#clearFilters').on('click', function() {
        $('#actionFilter').val('');
        $('#dateFromFilter').val('');
        $('#dateToFilter').val('');
        table.ajax.reload();
    });

    // Export functionality
    $('#exportBtn').on('click', function() {
        const action = $('#actionFilter').val();
        const dateFrom = $('#dateFromFilter').val();
        const dateTo = $('#dateToFilter').val();

        // Build export URL with filters
        let exportUrl = '{{ route('admin.login-history.export') }}?';
        const params = [];
        
        if (action) params.push('action=' + encodeURIComponent(action));
        if (dateFrom) params.push('date_from=' + encodeURIComponent(dateFrom));
        if (dateTo) params.push('date_to=' + encodeURIComponent(dateTo));
        
        exportUrl += params.join('&');

        // Trigger download
        window.location.href = exportUrl;
    });
});
</script>
@endpush

