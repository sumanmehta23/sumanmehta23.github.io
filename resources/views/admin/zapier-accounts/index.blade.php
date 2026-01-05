@extends('layouts.admin.admin')
@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .stat-card {
            padding: 20px;
            border-radius: 5px;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #00b98e;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .filter-section {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .table-wrapper {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .action-buttons {
            margin-top: 10px;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-success {
            background: #00b98e;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: white;
        }

        .zapier-icon {
            width: 20px;
            height: 20px;
            margin-right: 5px;
        }

        .client-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .client-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .client-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .client-name {
            font-weight: 600;
            color: #333;
            text-decoration: none;
            cursor: pointer;
        }

        .client-name:hover {
            color: #00b98e;
        }

        .client-email {
            font-size: 13px;
            color: #666;
        }

        .account-link {
            color: #00b98e;
            text-decoration: none;
            font-weight: 500;
        }

        .account-link:hover {
            text-decoration: underline;
        }
    </style>

    <div class="main-content app-content">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row">
                    
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Zapier Accounts</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <!-- Statistics Row -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number" id="total-users">0</div>
                            <div class="stat-label">Total Zapier Users</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number" id="verified-users">0</div>
                            <div class="stat-label">Verified Users</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number" id="unverified-users">0</div>
                            <div class="stat-label">Unverified Users</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number" id="total-accounts">0</div>
                            <div class="stat-label">Trading Accounts</div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <h5 class="mb-3">Filters</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <label>Email</label>
                            <input type="text" id="filter-email" class="form-control" placeholder="Search by email...">
                        </div>
                        <div class="col-md-3">
                            <label>Full Name</label>
                            <input type="text" id="filter-name" class="form-control" placeholder="Search by name...">
                        </div>
                        <div class="col-md-2">
                            <label>Status</label>
                            <select id="filter-status" class="form-control">
                                <option value="">All</option>
                                <option value="verified">Verified</option>
                                <option value="unverified">Unverified</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>From Date</label>
                            <input type="date" id="filter-date-from" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label>To Date</label>
                            <input type="date" id="filter-date-to" class="form-control">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button class="btn btn-primary" id="btn-filter">
                                Apply Filters
                            </button>
                            <button class="btn btn-secondary" id="btn-reset">
                                Reset
                            </button>
                            <a href="{{ route('admin.zapier-accounts.export') }}" class="btn btn-success" id="btn-export">
                                Export CSV
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="table-wrapper">
                    <table id="zapierAccountsTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="35%">Client Info</th>
                                <th width="15%">Phone</th>
                                <th width="15%">Account Codes</th>
                                <th width="12%">Status</th>
                                <th width="18%">Created At</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user and all associated accounts?</p>
                    <p class="text-warning"><strong>This action cannot be undone.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="btn-confirm-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>

@endsection
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">

@section("scripts")
    <script>
        let table;
        let deleteUserId = null;

        $(document).ready(function() {
            initializeTable();
            loadStats();
            bindEvents();
        });

        function initializeTable() {
            table = $('#zapierAccountsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.zapier-accounts.data') }}',
                    type: 'GET',
                    data: function(d) {
                        d.email = $('#filter-email').val();
                        d.name = $('#filter-name').val();
                        d.status = $('#filter-status').val();
                        d.date_from = $('#filter-date-from').val();
                        d.date_to = $('#filter-date-to').val();
                        return d;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    { data: 'client_info', name: 'name', orderable: false, searchable: false },
                    { data: 'phone', name: 'phone' },
                    { data: 'account_codes', name: 'account_codes', orderable: false, searchable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' }
                ],
                pageLength: 25,
                order: [[5, 'desc']],
                language: {
                    emptyTable: 'No Zapier accounts found',
                    processing: 'Loading...'
                }
            });
        }

        function loadStats() {
            $.ajax({
                url: '{{ route('admin.zapier-accounts.stats') }}',
                type: 'GET',
                success: function(response) {
                    $('#total-users').text(response.total_users);
                    $('#verified-users').text(response.verified_users);
                    $('#unverified-users').text(response.unverified_users);
                    $('#total-accounts').text(response.total_accounts);
                },
                error: function() {
                    console.error('Failed to load statistics');
                }
            });
        }

        function bindEvents() {
            // Filter button
            $('#btn-filter').click(function() {
                table.draw();
            });

            // Reset filters
            $('#btn-reset').click(function() {
                $('#filter-email').val('');
                $('#filter-name').val('');
                $('#filter-status').val('');
                $('#filter-date-from').val('');
                $('#filter-date-to').val('');
                table.draw();
            });

            // Delete user button
            $(document).on('click', '.delete-zapier-user', function() {
                deleteUserId = $(this).data('id');
                $('#deleteConfirmModal').modal('show');
            });

            // Confirm delete
            $('#btn-confirm-delete').click(function() {
                if (deleteUserId) {
                    $.ajax({
                        url: '{{ route('admin.zapier-accounts.delete') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: deleteUserId
                        },
                        success: function(response) {
                            $('#deleteConfirmModal').modal('hide');
                            table.draw();
                            loadStats();
                            alert(response.message);
                        },
                        error: function(response) {
                            alert('Error: ' + response.responseJSON.message);
                        }
                    });
                }
            });

            // Resend welcome/account email
            $(document).on('click', '.resend-welcome-email', function() {
                const uid = $(this).data('id');
                if (!uid) return;
                $.ajax({
                    url: '{{ route('admin.zapier-accounts.resend') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: uid
                    },
                    beforeSend: function() {
                        // optional: show spinner or disable button
                    },
                    success: function(response) {
                        table.draw(false);
                        loadStats();
                        alert('Resend attempted. Emails sent: ' + (response.emails_sent || 0));
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to resend email';
                        alert('Error: ' + msg);
                    }
                });
            });

            // Export with filters
            $('#btn-export').click(function(e) {
                e.preventDefault();
                let filters = '?';
                if ($('#filter-email').val()) filters += 'email=' + encodeURIComponent($('#filter-email').val()) + '&';
                if ($('#filter-name').val()) filters += 'name=' + encodeURIComponent($('#filter-name').val()) + '&';
                if ($('#filter-status').val()) filters += 'status=' + $('#filter-status').val() + '&';
                if ($('#filter-date-from').val()) filters += 'date_from=' + $('#filter-date-from').val() + '&';
                if ($('#filter-date-to').val()) filters += 'date_to=' + $('#filter-date-to').val();
                
                window.location.href = '{{ route('admin.zapier-accounts.export') }}' + filters;
            });
        }
    </script>
@endsection
