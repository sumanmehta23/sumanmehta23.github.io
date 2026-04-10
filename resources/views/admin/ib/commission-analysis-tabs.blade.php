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

                    <!-- TAB 7: OVERPAYMENT AUDIT -->
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

            function renderAffectedIbsTable(res) {
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

            function fmt(n) {
                return Number(n).toLocaleString();
            }

            function fmtMoney(n) {
                return '$' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

        </script>
    @endpush
@endsection