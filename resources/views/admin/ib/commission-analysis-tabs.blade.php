@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">IB Commission Analysis</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.ib.dashboard') }}">IB</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Commission Analysis</li>
                </ol>
            </div>

            <!-- FILTERS -->
            <div class="mb-3 row">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Account Code</label>
                                    <input type="text" id="filterCode" class="form-control" placeholder="e.g. 5001234">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">IB Referral Code</label>
                                    <input type="text" id="filterReferral" class="form-control" placeholder="e.g. REF001">
                                </div>
                                <div class="gap-2 col-md-3 d-flex align-items-end">
                                    <button id="btnAnalyze" class="btn btn-primary">
                                        <i class="fa fa-search me-1"></i> Analyze
                                    </button>
                                    <button id="btnReset" class="btn btn-outline-secondary">
                                        <i class="fa fa-refresh me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABS CONTAINER -->
            <div id="tabsContainer">
                <ul class="mb-3 nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#tab-overview" role="tab" data-bs-toggle="tab"
                            onclick="loadTabContent('overview')">Overview</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-duplicates-wallet" role="tab" data-bs-toggle="tab"
                            onclick="loadTabContent('duplicate_wallets')">Duplicate Wallets</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-duplicates-comm" role="tab" data-bs-toggle="tab"
                            onclick="loadTabContent('duplicate_commissions')">Duplicate Commissions</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-missing" role="tab" data-bs-toggle="tab"
                            onclick="loadTabContent('missing_commissions')">Missing Commission</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-stuck" role="tab" data-bs-toggle="tab"
                            onclick="loadTabContent('stuck_commissions')">Stuck Commissions</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-overpaid" role="tab" data-bs-toggle="tab"
                            onclick="loadTabContent('overpaid_ibs')">Overpaid IBs</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-fixable-issues" role="tab" data-bs-toggle="tab"
                            onclick="loadTabContent('fixable_issues')">Fixable Issues</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-overpayment-audit" role="tab" data-bs-toggle="tab"
                            onclick="loadTabContent('overpayment_audit')">Overpayment Audit</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-pipeline" role="tab" data-bs-toggle="tab"
                            onclick="loadTabContent('pipeline_health')">Pipeline Health</a></li>
                </ul>

                <div class="tab-content">
                    <!-- TAB 1: OVERVIEW -->
                    <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                        <div id="overviewContent"></div>
                    </div>

                    <!-- TAB 2: DUPLICATE WALLETS -->
                    <div class="tab-pane fade" id="tab-duplicates-wallet" role="tabpanel">
                        <div id="dupWalletsContent" class="py-5 text-center"><i class="fa fa-spinner fa-spin"></i>
                            Loading...</div>
                    </div>

                    <!-- TAB 3: DUPLICATE COMMISSIONS -->
                    <div class="tab-pane fade" id="tab-duplicates-comm" role="tabpanel">
                        <div id="dupCommContent" class="py-5 text-center"><i class="fa fa-spinner fa-spin"></i> Loading...
                        </div>
                    </div>

                    <!-- TAB 4: MISSING COMMISSIONS -->
                    <div class="tab-pane fade" id="tab-missing" role="tabpanel">
                        <div id="missingContent" class="py-5 text-center"><i class="fa fa-spinner fa-spin"></i> Loading...
                        </div>
                    </div>

                    <!-- TAB 5: STUCK COMMISSIONS -->
                    <div class="tab-pane fade" id="tab-stuck" role="tabpanel">
                        <div id="stuckContent" class="py-5 text-center"><i class="fa fa-spinner fa-spin"></i> Loading...
                        </div>
                    </div>

                    <!-- TAB 6: OVERPAID IBs -->
                    <div class="tab-pane fade" id="tab-overpaid" role="tabpanel">
                        <div id="overpaidContent" class="py-5 text-center"><i class="fa fa-spinner fa-spin"></i> Loading...
                        </div>
                    </div>

                    <!-- TAB 7: FIXABLE ISSUES (BULK FIX) -->
                    <div class="tab-pane fade" id="tab-fixable-issues" role="tabpanel">
                        <div id="fixableIssuesContent" class="py-5 text-center"><i class="fa fa-spinner fa-spin"></i>
                            Loading...
                        </div>
                    </div>

                    <!-- TAB 8: OVERPAYMENT AUDIT -->
                    <div class="tab-pane fade" id="tab-overpayment-audit" role="tabpanel">
                        <div id="auditContent" class="py-5 text-center"><i class="fa fa-spinner fa-spin"></i> Loading...
                        </div>
                    </div>

                    <!-- TAB 8: PIPELINE HEALTH -->
                    <div class="tab-pane fade" id="tab-pipeline" role="tabpanel">
                        <div id="pipelineContent" class="py-5 text-center"><i class="fa fa-spinner fa-spin"></i> Loading...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const loadedTabs = new Set();
            let currentFilters = { code: null, referral: null };

            $(document).ready(function () {
                $('#btnAnalyze').on('click', startAnalysis);
                $('#btnReset').on('click', function () {
                    $('#filterCode').val('');
                    $('#filterReferral').val('');
                });
            });

            function startAnalysis() {
                const code = $('#filterCode').val().trim() || null;
                const referral = $('#filterReferral').val().trim() || null;
                currentFilters = { code, referral };

                $('#btnAnalyze').prop('disabled', true);
                loadedTabs.clear();
                $('#overviewContent').html('<div class="py-5 text-center"><i class="fa fa-spinner fa-spin"></i> Loading overview...</div>');

                loadTabContent('overview');
                $('#btnAnalyze').prop('disabled', false);
            }

            function loadTabContent(section) {
                if (loadedTabs.has(section)) return;

                const containerId = getContainerId(section);
                const container = $('#' + containerId);

                container.html('<div class="py-5 text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');

                $.ajax({
                    url: '{{ route("admin.ib.commission-analysis.table-data") }}',
                    method: 'GET',
                    data: {
                        section: section,
                        page: 1,
                        code: currentFilters.code,
                        referral: currentFilters.referral
                    },
                    success: function (res) {
                        renderSection(section, containerId, res);
                    },
                    error: function (xhr) {
                        container.html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle me-2"></i>Failed to load section data.</div>');
                    }
                });

                loadedTabs.add(section);
            }

            function getContainerId(section) {
                const map = {
                    'overview': 'overviewContent',
                    'duplicate_wallets': 'dupWalletsContent',
                    'duplicate_commissions': 'dupCommContent',
                    'missing_commissions': 'missingContent',
                    'stuck_commissions': 'stuckContent',
                    'overpaid_ibs': 'overpaidContent',
                    'fixable_issues': 'fixableIssuesContent',
                    'overpayment_audit': 'auditContent',
                    'pipeline_health': 'pipelineContent'
                };
                return map[section];
            }

            function renderSection(section, containerId, res) {
                let html = '';

                switch (section) {
                    case 'overview':
                        html = renderOverview(res);
                        break;
                    case 'duplicate_wallets':
                        html = renderDupWalletsTable(res);
                        break;
                    case 'duplicate_commissions':
                        html = renderDupCommTable(res);
                        break;
                    case 'missing_commissions':
                        html = renderMissingTable(res);
                        break;
                    case 'stuck_commissions':
                        html = renderStuckTable(res);
                        break;
                    case 'overpaid_ibs':
                        html = renderOverpaidTable(res);
                        break;
                    case 'fixable_issues':
                        html = renderFixableIssuesTable(res);
                        break;
                    case 'overpayment_audit':
                        html = renderAffectedIbsTable(res);
                        break;
                    case 'pipeline_health':
                        html = renderPipelineTable(res);
                        break;
                }

                if (res.last_page > 1) {
                    html += renderPagination(section, containerId, res);
                }

                $('#' + containerId).html(html);
            }

            function renderPagination(section, containerId, res) {
                let html = '<div class="mt-3 d-flex justify-content-center"><nav><ul class="pagination pagination-sm">';

                for (let p = 1; p <= res.last_page; p++) {
                    const active = p === res.current_page ? 'active' : '';
                    html += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="loadPage('${section}', '${containerId}', ${p}); return false;">${p}</a></li>`;
                }

                html += '</ul></nav></div>';
                return html;
            }

            function loadPage(section, containerId, page) {
                const container = $('#' + containerId);
                const originalHtml = container.html();
                container.html('<div class="py-3 text-center"><i class="fa fa-spinner fa-spin"></i></div>');

                $.ajax({
                    url: '{{ route("admin.ib.commission-analysis.table-data") }}',
                    method: 'GET',
                    data: {
                        section: section,
                        page: page,
                        code: currentFilters.code,
                        referral: currentFilters.referral
                    },
                    success: function (res) {
                        renderSection(section, containerId, res);
                    },
                    error: function () {
                        container.html(originalHtml);
                    }
                });
            }

            function renderOverview(res) {
                return `<div class="card custom-card">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Analysis Quick Stats</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="text-muted"><i class="fa fa-info-circle me-2"></i>Filters applied: Code=${currentFilters.code || 'All'}, Referral=${currentFilters.referral || 'All'}</p>
                                                            <p class="text-muted">Click on other tabs to view detailed analysis data. Each tab loads its data on-demand.</p>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="mb-2 alert alert-info">
                                                                        <strong>Status:</strong> Ready
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>`;
            }

            function renderDupWalletsTable(res) {
                let html = `<div class="card custom-card">
                                                        <div class="card-header d-flex justify-content-between">
                                                            <h6 class="mb-0">Partially Closed Positions (${res.total} total)</h6>
                                                            <button class="btn btn-sm btn-danger" id="btnFixDupWallets"><i class="fa fa-trash me-1"></i> Fix Duplicates</button>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-hover">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Position ID</th>
                                                                            <th>User</th>
                                                                            <th>Orders</th>
                                                                            <th>Total Vol</th>
                                                                            <th>Primary Vol</th>
                                                                            <th>Total Amount</th>
                                                                            <th>Expected</th>
                                                                            <th>Overpaid</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>`;

                res.data.forEach(r => {
                    html += `<tr>
                                                            <td><code>${r.expert_position_id || 'N/A'}</code></td>
                                                            <td><small>${r.user_id?.substring(0, 12) || '-'}...</small></td>
                                                            <td class="text-center"><span class="badge bg-info">${r.order_count}</span></td>
                                                            <td>${r.total_volume}</td>
                                                            <td><strong>${r.primary_volume}</strong></td>
                                                            <td>${Number(r.total_amount).toFixed(4)}</td>
                                                            <td>${Number(r.expected_amount).toFixed(4)}</td>
                                                            <td class="text-danger"><strong>${Number(r.overpaid).toFixed(4)}</strong></td>
                                                        </tr>`;
                });

                html += `</tbody></table></div></div>`;
                return html;
            }

            function renderDupCommTable(res) {
                let html = `<div class="card custom-card">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Duplicate Commission Records (${res.total} total)</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-hover">
                                                                    <thead>
                                                                        <tr><th>Order ID</th><th>Account Code</th><th>Count</th></tr>
                                                                    </thead>
                                                                    <tbody>`;

                res.data.forEach(r => {
                    html += `<tr><td><code>${r.order_id}</code></td><td>${r.code}</td><td><span class="badge bg-danger">${r.count}</span></td></tr>`;
                });

                html += `</tbody></table></div></div>`;
                return html;
            }

            function renderMissingTable(res) {
                let html = `<div class="card custom-card">
                                                        <div class="card-header"><h6 class="mb-0">Missing Commissions</h6></div>
                                                        <div class="card-body">
                                                            <div class="alert alert-info"><h4>${fmt(res.total || 0)}</h4>Close deals without commission</div>
                                                            ${res.data?.length > 0 && res.data[0]?.top_accounts ? `<h6>Top Accounts:</h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead><tr><th>Account Code</th><th>Missing Count</th></tr></thead>
                                                                    <tbody>
                                                                        ${res.data[0].top_accounts.map(a => `<tr><td>${a.code}</td><td><span class="badge bg-info">${fmt(a.missing_count)}</span></td></tr>`).join('')}
                                                                    </tbody>
                                                                </table>
                                                            </div>` : '<p class="text-muted">No missing commissions found</p>'}
                                                        </div>
                                                    </div>`;
                return html;
            }

            function renderStuckTable(res) {
                const data = res.data && res.data.length > 0 ? res.data[0] : {};
                let html = `<div class="card custom-card">
                                                        <div class="card-header"><h6 class="mb-0">Stuck Commissions</h6></div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="alert alert-warning"><h4>${fmt(data.no_wallets || 0)}</h4>Status=0, No Wallets</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="alert alert-danger"><h4>${fmt(data.has_wallets_wrong_status || 0)}</h4>Status=0 but HAS Wallets</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>`;
                return html;
            }

            function renderOverpaidTable(res) {
                let html = `<div class="card custom-card">
                                                        <div class="card-header"><h6 class="mb-0">Overpaid IBs (${res.total} total)</h6></div>
                                                        <div class="card-body">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-hover">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Referral Code</th>
                                                                            <th>Total Wallets</th>
                                                                            <th>Unique Orders</th>
                                                                            <th>Duplicates</th>
                                                                            <th>Total Paid</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>`;

                res.data.forEach(r => {
                    html += `<tr>
                                                            <td><strong>${r.referral_code}</strong></td>
                                                            <td>${fmt(r.total_wallets)}</td>
                                                            <td>${fmt(r.unique_orders)}</td>
                                                            <td class="text-danger">${fmt(r.duplicate_entries)}</td>
                                                            <td>${fmtMoney(r.total_paid)}</td>
                                                        </tr>`;
                });

                html += `</tbody></table></div></div>`;
                return html;
            }

            function renderFixableIssuesTable(res) {
                let html = `<div class="card custom-card border-warning">
                                                        <div class="card-header bg-warning-transparent">
                                                            <h6 class="mb-0 text-warning">Fixable Overpayment Issues (${res.total} groups)</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-3">
                                                                    <div class="card border-warning">
                                                                        <div class="card-body">
                                                                            <h6 class="text-muted text-sm mb-1">Total Recoverable</h6>
                                                                            <h4 class="text-warning">${fmtMoney(res.total_recoverable || 0)}</h4>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="card border-warning">
                                                                        <div class="card-body">
                                                                            <h6 class="text-muted text-sm mb-1">Groups</h6>
                                                                            <h4 class="text-warning">${fmt(res.total || 0)}</h4>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="card border-warning">
                                                                        <div class="card-body">
                                                                            <h6 class="text-muted text-sm mb-1">Entries</h6>
                                                                            <h4 class="text-warning">${fmt(res.total_entries || 0)}</h4>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <button type="button" class="btn btn-warning btn-sm w-100 mt-3" onclick="bulkFixAll()">
                                                                        <i class="ti ti-check-circle me-1"></i> Fix All
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-hover">
                                                                    <thead class="table-warning">
                                                                        <tr>
                                                                            <th>Position</th>
                                                                            <th>User</th>
                                                                            <th>IB Code</th>
                                                                            <th>Orders</th>
                                                                            <th>Recoverable</th>
                                                                            <th>Entries</th>
                                                                            <th>Status</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>`;

                if (res.data && res.data.length > 0) {
                    res.data.forEach(group => {
                        html += `<tr>
                                                                <td><code>${group.expert_position_id}</code></td>
                                                                <td data-user-id="${group.user_id}">${group.user_email}</td>
                                                                <td><strong>${group.referral_code}</strong></td>
                                                                <td><span class="badge bg-info">${fmt(group.order_count)}</span></td>
                                                                <td class="text-warning fw-bold">${fmtMoney(group.recoverable_amount)}</td>
                                                                <td>${fmt(group.duplicate_count)}</td>
                                                                <td><span class="badge bg-secondary">${group.status}</span></td>
                                                                <td>
                                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                                        onclick="showFixTimeline('${group.expert_position_id}', '${group.user_id}')">
                                                                        <i class="ti ti-eye me-1"></i> Review
                                                                    </button>
                                                                </td>
                                                            </tr>`;
                    });
                } else {
                    html += `<tr><td colspan="8" class="text-center text-muted"><em>No fixable issues found</em></td></tr>`;
                }

                html += `</tbody></table></div></div></div>`;
                return html;
            }

            let html = `<div class="card custom-card border-danger">
                                                        <div class="card-header bg-danger-transparent">
                                                            <h6 class="mb-0 text-danger">Overpayment Audit - Affected IBs (${res.total} total)</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-hover">
                                                                    <thead class="table-danger">
                                                                        <tr>
                                                                            <th>IB Code</th>
                                                                            <th>Overpaid</th>
                                                                            <th>Recovered</th>
                                                                            <th>Outstanding</th>
                                                                            <th>Total Earned</th>
                                                                            <th>Withdrawn</th>
                                                                            <th>Balance</th>
                                                                            <th>Recoverable</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>`;

            res.data.forEach(r => {
                const canRecover = r.can_recover_from_balance ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>';
                html += `<tr>
                                                            <td><strong>${r.referral_code}</strong></td>
                                                            <td class="text-danger">${fmtMoney(r.overpaid_amount)}</td>
                                                            <td class="text-success">${fmtMoney(r.total_recovered)}</td>
                                                            <td class="text-danger fw-bold">${fmtMoney(r.outstanding)}</td>
                                                            <td>${fmtMoney(r.total_earned)}</td>
                                                            <td>${fmtMoney(r.total_withdrawn)}</td>
                                                            <td>${fmtMoney(r.current_balance)}</td>
                                                            <td>${canRecover}</td>
                                                        </tr>`;
            });

            html += `</tbody></table></div></div>`;
            return html;
                    }

            function renderPipelineTable(res) {
                const data = res.data && res.data.length > 0 ? res.data[0] : {};
                let html = `<div class="card custom-card">
                                                        <div class="card-header"><h6 class="mb-0">Commission Pipeline Status</h6></div>
                                                        <div class="card-body">
                                                            <div class="mb-4 table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr><th>Status</th><th>Label</th><th>Count</th><th>Oldest</th><th>Newest</th></tr>
                                                                    </thead>
                                                                    <tbody>`;

                if (data.pipeline) {
                    data.pipeline.forEach(p => {
                        html += `<tr>
                                                                <td><code>${p.status}</code></td>
                                                                <td>${p.label}</td>
                                                                <td>${fmt(p.count)}</td>
                                                                <td><small>${p.oldest || '-'}</small></td>
                                                                <td><small>${p.newest || '-'}</small></td>
                                                            </tr>`;
                    });
                }

                html += `</tbody></table></div>
                                                            <h6 class="mb-2">Daily Wallet Activity (Last 7 Days)</h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr><th>Date</th><th>Entries</th><th>Total Amount</th></tr>
                                                                    </thead>
                                                                    <tbody>`;

                if (data.daily_wallets) {
                    data.daily_wallets.forEach(d => {
                        html += `<tr>
                                                                <td>${d.day}</td>
                                                                <td>${fmt(d.entries)}</td>
                                                                <td>${fmtMoney(d.total_amount)}</td>
                                                            </tr>`;
                    });
                }

                html += `</tbody></table></div></div></div>`;
                return html;
            }

            function showFixTimeline(expertPositionId, userId) {
                // Show loading state
                const modal = `
                            <div class="modal fade" id="timelineModal" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title">Commission Timeline & Overpayment Details</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="text-center py-5">
                                                <div class="spinner-border spinner-border-sm text-warning me-2" role="status"></div>
                                                <span>Loading timeline...</span>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-warning btn-sm" id="fixBtn" onclick="doFix('${expertPositionId}', '${userId}')">
                                                <i class="ti ti-check-circle me-1"></i> Confirm Fix
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                // Remove existing modal if any
                $('#timelineModal').remove();
                $('body').append(modal);

                // Show modal
                const modal_instance = new bootstrap.Modal(document.getElementById('timelineModal'));
                modal_instance.show();

                // Load timeline data
                $.ajax({
                    url: '/admin/ib-commission-analysis/commission-timeline',
                    method: 'GET',
                    data: {
                        expert_position_id: expertPositionId,
                        user_id: userId
                    },
                    success: function (response) {
                        renderTimeline(response, expertPositionId, userId);
                    },
                    error: function () {
                        $('#timelineModal .modal-body').html('<div class="alert alert-danger">Failed to load timeline data</div>');
                    }
                });
            }

            function renderTimeline(data, expertPositionId, userId) {
                let html = '';

                if (data.timeline && data.timeline.length > 0) {
                    html += `<div class="card border-warning mb-3">
                                <div class="card-header bg-warning-transparent">
                                    <h6 class="mb-0 text-warning">Commission Timeline</h6>
                                </div>
                                <div class="card-body">
                                    <div class="timeline">`;

                    data.timeline.forEach((entry, idx) => {
                        const statusClass = entry.status === 'withdrawn' ? 'text-danger' : 'text-success';
                        const statusBadge = entry.status === 'withdrawn' ? '<span class="badge bg-danger">Withdrawn</span>' : '<span class="badge bg-success">Pending</span>';

                        html += `<div class="timeline-item">
                                    <div class="timeline-time text-sm">${entry.date}</div>
                                    <div class="timeline-content">
                                        <div class="mb-1">
                                            Order <code>${entry.order_id}</code>
                                            <span class="ms-2">${statusBadge}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted">
                                                Amount: ${fmtMoney(entry.amount)} 
                                                ${entry.status === 'withdrawn' ? '(Withdrawn: ' + fmtMoney(entry.withdrawn_amount) + ')' : '(Can recover)'}
                                            </small>
                                        </div>
                                    </div>
                                </div>`;
                    });

                    html += `</div></div></div>`;
                }

                // Show summary
                html += `<div class="alert alert-warning mb-0">
                            <h6 class="mb-2">Overpayment Summary</h6>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted d-block">Total Amount</small>
                                    <strong>${fmtMoney(data.total_amount || 0)}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Recoverable</small>
                                    <strong class="text-success">${fmtMoney(data.recoverable_amount || 0)}</strong>
                                </div>
                            </div>
                        </div>`;

                $('#timelineModal .modal-body').html(html);
            }

            function doFix(expertPositionId, userId) {
                const btn = $('#fixBtn');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Fixing...');

                $.ajax({
                    url: '/admin/ib-commission-analysis/fix-overpaid',
                    method: 'POST',
                    data: {
                        expert_position_id: expertPositionId,
                        user_id: userId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            // Show success notification
                            showNotification('Success!', 'Overpaidment fixed. ' + response.message, 'success');

                            // Close modal and reload tab
                            bootstrap.Modal.getInstance(document.getElementById('timelineModal')).hide();

                            // Reload the fixable issues tab
                            setTimeout(function () {
                                loadTab('fixable_issues');
                            }, 1000);
                        } else {
                            showNotification('Error', response.message || 'Failed to fix overpayment', 'danger');
                            btn.prop('disabled', false).html('<i class="ti ti-check-circle me-1"></i> Confirm Fix');
                        }
                    },
                    error: function () {
                        showNotification('Error', 'Failed to fix overpayment', 'danger');
                        btn.prop('disabled', false).html('<i class="ti ti-check-circle me-1"></i> Confirm Fix');
                    }
                });
            }

            function bulkFixAll() {
                if (!confirm('Are you sure you want to fix all overpayment issues? This action cannot be undone.')) {
                    return;
                }

                // Get all expert_position_id and user_id from the table
                const rows = document.querySelectorAll('#fixableIssuesContent table tbody tr');
                if (rows.length === 0) {
                    alert('No fixable issues found');
                    return;
                }

                let fixed = 0;
                let failed = 0;
                let current = 0;

                const fixNext = function () {
                    if (current >= rows.length) {
                        showNotification('Bulk Fix Complete', `Fixed: ${fixed}, Failed: ${failed}`, 'info');
                        loadTab('fixable_issues');
                        return;
                    }

                    const row = rows[current];
                    const expertPositionId = row.querySelector('td:first-child code')?.textContent || '';
                    const userId = row.querySelector('td:nth-child(2)')?.getAttribute('data-user-id') || '';

                    if (!expertPositionId || !userId) {
                        current++;
                        fixNext();
                        return;
                    }

                    $.ajax({
                        url: '/admin/ib-commission-analysis/fix-overpaid',
                        method: 'POST',
                        data: {
                            expert_position_id: expertPositionId,
                            user_id: userId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (response.success) {
                                fixed++;
                            } else {
                                failed++;
                            }
                        },
                        error: function () {
                            failed++;
                        },
                        complete: function () {
                            current++;
                            fixNext();
                        }
                    });
                };

                fixNext();
            }

            function showNotification(title, message, type) {
                // Simple notification - you can enhance this with a toast library like Toastr
                const alert = document.createElement('div');
                alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
                alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
                alert.innerHTML = `
                            <strong>${title}</strong> ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                document.body.appendChild(alert);
                setTimeout(() => alert.remove(), 5000);
            }

            function fmt(n) {
                return Number(n).toLocaleString();
            }

            function fmtMoney(n) {
                return '$' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

        </script>

        <style>
            .timeline {
                position: relative;
                padding: 0;
            }

            .timeline-item {
                padding-left: 30px;
                margin-bottom: 20px;
                position: relative;
            }

            .timeline-item:before {
                content: '';
                position: absolute;
                left: 0;
                top: 5px;
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background-color: var(--bs-warning);
                border: 2px solid #fff;
            }

            .timeline-item:not(:last-child):after {
                content: '';
                position: absolute;
                left: 5px;
                top: 17px;
                width: 2px;
                height: 30px;
                background-color: var(--bs-border-color);
            }

            .timeline-time {
                font-weight: 600;
                color: var(--bs-secondary);
            }

            .timeline-content {
                padding: 8px 12px;
                background-color: var(--bs-light);
                border-radius: 4px;
                border-left: 3px solid var(--bs-warning);
            }
        </style>
    @endpush
@endsection