@extends('layouts.admin.admin')
@section('content')

    <style>
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .bulk-actions {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }

        .bulk-actions.show {
            display: block;
        }

        .stats-card {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .checkbox-column {
            width: 40px;
        }

        .checkbox-all,
        .account-row-checkbox {
            cursor: pointer;
        }

        .progress-bar-processing {
            background-color: #ffc107;
        }

        .progress-bar-success {
            background-color: #28a745;
        }

        .progress-bar-error {
            background-color: #dc3545;
        }

        .results-table {
            margin-top: 20px;
        }

        .card-footer .pagination {
            margin-bottom: 0;
        }
    </style>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">MT5 Not Found Accounts Management</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.liveAccounts') }}">Client Accounts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Not Found in MT5</li>
                </ol>
            </div>

        <!-- Statistics -->
        <div id="stats-container"></div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('admin.accounts.not_found_in_mt5.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="code" class="form-label">Account Code</label>
                        <input type="text" class="form-control" id="code" name="code"
                            value="{{ request('code') }}" placeholder="Filter by account code">
                    </div>
                    <div class="col-md-3">
                        <label for="email" class="form-label">User Email</label>
                        <input type="text" class="form-control" id="email" name="email"
                            value="{{ request('email') }}" placeholder="Filter by user email">
                    </div>
                    <div class="col-md-3">
                        <label for="deletion_type" class="form-label">Deletion Type</label>
                        <select class="form-select" id="deletion_type" name="deletion_type">
                            <option value="">All Types</option>
                            @foreach ($deletionTypes as $type)
                                <option value="{{ $type }}" {{ request('deletion_type') === $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <div class="btn-group w-100" role="group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.accounts.not_found_in_mt5.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                            @if (auth('admin')->check() && auth('admin')->user()->hasPermissions(['accounts:view_not_found']))
                                <a href="{{ route('admin.accounts.not_found_in_mt5.export', request()->all()) }}"
                                    class="btn btn-success">
                                    <i class="fas fa-download"></i> Export
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bulk Actions Section -->
        <div class="bulk-actions" id="bulkActions">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <span id="selectedCount">0 accounts selected</span>
                </div>
                <div class="col-md-8 text-end">
                   
                    @if (auth('admin')->check() && auth('admin')->user()->hasPermissions(['accounts:bulk_archive']))
                        <button type="button" class="btn btn-danger" id="archiveBtn">
                            <i class="fas fa-trash"></i> Verify & Archive 
                        </button>
                    @endif
                    <button type="button" class="btn btn-secondary" id="clearSelection">
                        <i class="fas fa-times"></i> Clear Selection
                    </button>
                </div>
            </div>
        </div>

        <!-- Accounts Table -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">
                            Listed Count : {{ $accounts->total() }}
                        </div>
                        @if (auth('admin')->check() && auth('admin')->user()->hasPermissions(['accounts:view_not_found']))
                            <a href="{{ route('admin.accounts.not_found_in_mt5.export', request()->all()) }}"
                                class="btn btn-success btn-sm">
                                <i class="fas fa-download"></i> Export
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap w-100" id="accountsTable">
                                <thead>
                                    <tr>
                                        <th class="checkbox-column">
                                            <input type="checkbox" class="form-check-input checkbox-all" id="checkAll"
                                                title="Select all accounts on this page">
                                        </th>
                                        <th>Account Code</th>
                                        <th>User Email</th>
                                        <th>Deletion Type</th>
                                        <th>Deleted At</th>
                                        <th>Updated At</th>
                                        <th>Account Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($accounts as $account)
                                        <tr class="account-row" data-account-id="{{ $account->id }}">
                                            <td class="checkbox-column">
                                                <input type="checkbox" class="form-check-input account-row-checkbox"
                                                    value="{{ $account->id }}" title="Select this account">
                                            </td>
                                            <td>
                                                <strong>{{ $account->code }}</strong>
                                            </td>
                                            <td>{{ $account->user?->email ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-danger">{{ $account->deletion_type }}</span>
                                            </td>
                                            <td>{{ $account->deleted_at?->format('M d, Y H:i') ?? 'Not deleted' }}</td>
                                            <td>{{ $account->updated_at->format('M d, Y H:i') }}</td>
                                            <td>{{ $account->accountType?->typeName ?? 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-4 text-center text-muted">
                                                No accounts found matching the criteria.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="flex-wrap gap-2 d-flex align-items-center justify-content-between">
                            <div class="text-muted fs-13">
                                Showing {{ $accounts->firstItem() ?? 0 }} to {{ $accounts->lastItem() ?? 0 }} of {{ number_format($accounts->total()) }} entries
                            </div>
                            <div >
                                {{ $accounts->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Modal -->
    <div class="modal fade" id="progressModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="progressTitle">Processing Accounts</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3 progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-processing" id="progressBar" role="progressbar"
                            style="width: 0%;">
                            <span id="progressText">0%</span>
                        </div>
                    </div>
                    <div class="alert alert-info" id="statusMessage">
                        Initializing...
                    </div>
                    <div id="resultsContainer" class="results-table" style="display: none;">
                        <h6>Results Summary</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Account</th>
                                    <th>Status</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody id="resultsList">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="closeProgressBtn" data-bs-dismiss="modal"
                        style="display: none;">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load statistics on page load
            loadStatistics();

            // Checkbox handlers
            const checkAll = document.getElementById('checkAll');
            const accountCheckboxes = document.querySelectorAll('.account-row-checkbox');
            const bulkActions = document.getElementById('bulkActions');

            // Clear any browser-restored checkbox state on page load
            checkAll.checked = false;
            accountCheckboxes.forEach(cb => cb.checked = false);

            checkAll.addEventListener('change', function() {
                accountCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActionsVisibility();
            });

            accountCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    checkAll.checked = Array.from(accountCheckboxes).every(cb => cb.checked);
                    updateBulkActionsVisibility();
                });
            });

            function updateBulkActionsVisibility() {
                const selectedCount = Array.from(accountCheckboxes).filter(cb => cb.checked).length;
                const bulkActions = document.getElementById('bulkActions');

                if (selectedCount > 0) {
                    bulkActions.classList.add('show');
                    document.getElementById('selectedCount').textContent =
                        `${selectedCount} account${selectedCount !== 1 ? 's' : ''} selected`;
                } else {
                    bulkActions.classList.remove('show');
                    checkAll.checked = false;
                }
            }

            // Clear selection
            document.getElementById('clearSelection').addEventListener('click', function() {
                checkAll.checked = false;
                accountCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateBulkActionsVisibility();
            });

           
            // Archive Button
            @if (auth('admin')->check() && auth('admin')->user()->hasPermissions(['accounts:bulk_archive']))
                document.getElementById('archiveBtn').addEventListener('click', function() {
                    const selectedIds = Array.from(accountCheckboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);

                    if (selectedIds.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Selection',
                            text: 'Please select at least one account',
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Are you sure?',
                        text: `This will verify ${selectedIds.length} account(s) in MT5 and delete those still not found.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, proceed!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            bulkVerifyAndArchive(selectedIds, 'archive');
                        }
                    });
                });
            @endif

            function loadStatistics() {
                fetch('{{ route('admin.accounts.not_found_in_mt5.stats') }}')
                    .then(response => response.json())
                    .then(data => {
                        const statsHtml = `
                    <div class="stats-grid">
                        <div class="stats-card">
                            <h6 class="text-muted">Total Not Found</h6>
                            <h3 class="text-danger">${data.total_not_found}</h3>
                        </div>
                        <div class="stats-card">
                            <h6 class="text-muted">Deleted (Last 7 Days)</h6>
                            <h3 class="text-success">${data.deleted_in_last_7_days}</h3>
                        </div>
                        <div class="stats-card">
                            <h6 class="text-muted">Oldest Entry</h6>
                            <h3>${data.oldest_not_found ? new Date(data.oldest_not_found).toLocaleDateString() : 'N/A'}</h3>
                        </div>
                    </div>
                `;
                        document.getElementById('stats-container').innerHTML = statsHtml;
                    })
                    .catch(error => console.error('Error loading statistics:', error));
            }

            function bulkVerifyAndArchive(accountIds, action) {
                const modal = new bootstrap.Modal(document.getElementById('progressModal'));
                modal.show();

                const progressBar = document.getElementById('progressBar');
                const progressText = document.getElementById('progressText');
                const statusMessage = document.getElementById('statusMessage');
                const resultsList = document.getElementById('resultsList');
                const resultsContainer = document.getElementById('resultsContainer');

                fetch('{{ route('admin.accounts.not_found_in_mt5.bulk_verify_and_archive') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        account_ids: accountIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Update results
                    let successMessage = '';
                    let errorMessage = '';

                    if (data.deleted > 0) {
                        successMessage += `<strong>${data.deleted}</strong> account(s) deleted. `;
                    }
                    if (data.found > 0) {
                        successMessage +=
                            `<strong>${data.found}</strong> account(s) found in MT5 (deletion_type removed). `;
                    }
                    if (data.errors.length > 0) {
                        errorMessage = `<strong>${data.errors.length}</strong> error(s) encountered.`;
                    }

                    statusMessage.innerHTML = successMessage + (errorMessage ? `<br><span class="text-warning">${errorMessage}</span>` :
                        '');
                    statusMessage.className =
                        'alert alert-info';

                    progressBar.style.width = '100%';
                    progressText.textContent = '100%';

                    // Display results
                    resultsList.innerHTML = '';
                    if (data.details && data.details.length > 0) {
                        data.details.forEach(detail => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                        <td>${detail.account_code}</td>
                        <td>
                            <span class="badge badge-${detail.status}">
                                ${detail.status.toUpperCase()}
                            </span>
                        </td>
                        <td>${detail.message}</td>
                    `;
                            resultsList.appendChild(row);
                        });
                        resultsContainer.style.display = 'block';
                    }

                    // Show close button
                    document.getElementById('closeProgressBtn').style.display = 'block';

                    // Reload table after 2 seconds
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    statusMessage.innerHTML = `<strong>Error:</strong> ${error.message}`;
                    statusMessage.className = 'alert alert-danger';
                    progressBar.style.width = '100%';
                    progressText.textContent = 'Error';
                    document.getElementById('closeProgressBtn').style.display = 'block';
                });
            }
        });
    </script>
        </div>
        <!-- End:: container-fluid -->
    </div>
    <!-- End:: main-content app-content -->
@endsection
