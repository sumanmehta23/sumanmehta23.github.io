@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Inactive Users</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Inactive Users</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- ROW -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Inactive Users List
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Info Alert -->
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fe fe-alert-circle me-2"></i>
                                <strong>Note:</strong> This page shows users who have been marked as inactive based on login history. Users are automatically reactivated when they log in.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            
                            <!-- Filter Section -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="dateFromFilter" class="form-label">Registration Date From</label>
                                    <input type="date" id="dateFromFilter" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label for="dateToFilter" class="form-label">Registration Date To</label>
                                    <input type="date" id="dateToFilter" class="form-control">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-secondary btn-sm w-100" id="clearFilters">
                                        <i class="fe fe-x"></i> Clear
                                    </button>
                                </div>
                            </div>

                            <!-- DataTable -->
                            <div class="table-responsive">
                                <table id="inactiveUsersTable" class="table table-bordered text-nowrap border-bottom w-100">
                                    <thead>
                                        <tr>
                                            <th>User Email</th>
                                            <th>User Name</th>
                                            <th>Country</th>
                                            <th>Registration Date</th>
                                            <th>Last Login</th>
                                            <th>Days Inactive</th>
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
    const table = $('#inactiveUsersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.inactive-users.data') }}',
            data: function(d) {
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
                data: 'country', 
                name: 'country',
                defaultContent: 'Unknown',
                width: '120px',
                className: 'text-center'
            },
            { 
                data: 'reg_date_formatted',
                name: 'reg_date',
                defaultContent: '-',
                width: '150px',
                className: 'text-center'
            },
            { 
                data: 'last_login_formatted',
                name: 'last_login',
                defaultContent: 'Never',
                width: '150px',
                className: 'text-center',
                render: function(data) {
                    if (data === 'Never') {
                        return '<span class="badge bg-danger">Never</span>';
                    }
                    return data;
                }
            },
            { 
                data: 'days_inactive',
                name: 'days_inactive',
                defaultContent: '-',
                width: '120px',
                className: 'text-center',
                render: function(data) {
                    if (data === 'N/A' || data === null) {
                        return '<span class="badge bg-secondary">N/A</span>';
                    }
                    const days = parseInt(data);
                    let badgeClass = 'warning';
                    if (days >= 90) {
                        badgeClass = 'danger';
                    } else if (days >= 60) {
                        badgeClass = 'warning';
                    } else {
                        badgeClass = 'info';
                    }
                    return '<span class="badge bg-' + badgeClass + '">' + days + ' days</span>';
                }
            }
        ],
        order: [[3, 'desc']], // Sort by registration date descending
        pageLength: 25,
        scrollX: false,
        autoWidth: false,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"></div>'
        }
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
        $('#dateFromFilter').val('');
        $('#dateToFilter').val('');
        table.ajax.reload();
    });
});
</script>
@endpush

