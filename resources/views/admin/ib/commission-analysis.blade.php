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
            <!-- PAGE-HEADER END -->

            <!-- FILTERS -->
            <div class="row mb-3">
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
                                <div class="col-md-3 d-flex align-items-end gap-2">
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

            <!-- LOADING -->
            <div id="loadingOverlay" class="text-center py-5" style="display:none;">
                <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted" id="loadingMessage">Queuing analysis job...</p>
                <p class="text-muted small" id="loadingProgress"></p>
            </div>

            <!-- RESULTS (hidden until data loads) -->
            <div id="resultsContainer" style="display:none;">

                <!-- SECTION 1: Overview Cards -->
                <div class="row" id="overviewCards">
                    <div class="col-sm-12 col-md-4 col-xl-4">
                        <div class="card custom-card">
                            <div class="card-body">
                                <h6 class="mb-2">Close Deals</h6>
                                <h2 class="text-end">
                                    <i class="fa fa-exchange icon-size float-start text-primary text-primary-shadow"></i>
                                    <span id="ovTotalDeals">-</span>
                                </h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-4 col-xl-4">
                        <div class="card custom-card">
                            <div class="card-body">
                                <h6 class="mb-2">Commissions</h6>
                                <h2 class="text-end">
                                    <i class="fa fa-file-text icon-size float-start text-success text-success-shadow"></i>
                                    <span id="ovTotalCommissions">-</span>
                                </h2>
                                <p class="mb-0">Unprocessed <span class="float-end" id="ovUnprocessed">-</span></p>
                                <p class="mb-0">Processed <span class="float-end" id="ovProcessed">-</span></p>
                                <p class="mb-0">Discarded <span class="float-end" id="ovDiscarded">-</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-4 col-xl-4">
                        <div class="card custom-card">
                            <div class="card-body">
                                <h6 class="mb-2">Wallet Distribution</h6>
                                <h2 class="text-end">
                                    <i class="fa fa-money icon-size float-start text-warning text-warning-shadow"></i>
                                    <span id="ovTotalAmount">-</span>
                                </h2>
                                <p class="mb-0">Total Wallet Entries <span class="float-end" id="ovTotalWallets">-</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commission Status Pie Chart -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card custom-card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Commission Status Breakdown</h6>
                            </div>
                            <div class="card-body">
                                <div id="chartCommissionStatus" style="height:300px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card custom-card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Daily Wallet Entries (Last 7 Days)</h6>
                            </div>
                            <div class="card-body">
                                <div id="chartDailyWallets" style="height:300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION: Health Issues Summary -->
                <div class="row">
                    <div class="col-sm-6 col-md-3">
                        <div class="card custom-card border-start border-danger border-3">
                            <div class="card-body text-center">
                                <h3 id="issueDupCommissions" class="text-danger mb-1">-</h3>
                                <p class="text-muted mb-0">Duplicate Commissions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="card custom-card border-start border-warning border-3">
                            <div class="card-body text-center">
                                <h3 id="issueDupWallets" class="text-warning mb-1">-</h3>
                                <p class="text-muted mb-0">Duplicate Wallets</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="card custom-card border-start border-info border-3">
                            <div class="card-body text-center">
                                <h3 id="issueMissing" class="text-info mb-1">-</h3>
                                <p class="text-muted mb-0">Missing Commissions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="card custom-card border-start border-secondary border-3">
                            <div class="card-body text-center">
                                <h3 id="issueStuck" class="text-secondary mb-1">-</h3>
                                <p class="text-muted mb-0">Stuck Commissions</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-md-4">
                        <div class="card custom-card border-start border-danger border-3" style="border-width:4px !important;">
                            <div class="card-body text-center">
                                <h3 id="issueOverpaidAmount" class="text-danger mb-1">-</h3>
                                <p class="text-muted mb-0">Total Overpaid (from duplicates)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="card custom-card border-start border-success border-3" style="border-width:4px !important;">
                            <div class="card-body text-center">
                                <h3 id="issueRecoveredAmount" class="text-success mb-1">-</h3>
                                <p class="text-muted mb-0">Recovered</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="card custom-card border-start border-warning border-3" style="border-width:4px !important;">
                            <div class="card-body text-center">
                                <h3 id="issueOutstandingAmount" class="text-warning mb-1">-</h3>
                                <p class="text-muted mb-0">Outstanding</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Duplicate Wallet Entries -->
                <div class="row">
                    <div class="col-12">
                        <div class="card custom-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">Duplicate Wallet Entries</h6>
                                <button id="btnFixDuplicates" class="btn btn-sm btn-danger" style="display:none;">
                                    <i class="fa fa-trash me-1"></i> Fix Duplicates
                                </button>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Same (order_id, user_id) appearing multiple times — indicates
                                    overpayment</p>
                                <div id="dupWalletsEmpty" class="alert alert-success" style="display:none;">
                                    <i class="fa fa-check-circle me-1"></i> No duplicate wallet entries found.
                                </div>
                                <div id="dupWalletsSummary" style="display:none;">
                                    <div class="row mb-3">
                                        <div class="col-md-4"><strong>Duplicate pairs:</strong> <span
                                                id="dupWalletsCount">-</span></div>
                                        <div class="col-md-4"><strong>Extra rows:</strong> <span
                                                id="dupWalletsExtraRows">-</span></div>
                                        <div class="col-md-4"><strong>Total overpaid:</strong> $<span
                                                id="dupWalletsOverpaid">-</span></div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm" id="tableDupWallets">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>User ID</th>
                                                    <th>Count</th>
                                                    <th>Total Amount</th>
                                                    <th>Expected</th>
                                                    <th>Overpaid</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: Duplicate Commission Records -->
                <div class="row">
                    <div class="col-12">
                        <div class="card custom-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">Duplicate Commission Records</h6>
                                <button id="btnFixDupCommissions" class="btn btn-sm btn-danger" style="display:none;">
                                    <i class="fa fa-wrench me-1"></i> Fix Duplicate Commissions
                                </button>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Same (order_id, code) appearing multiple times in ib1_commission
                                </p>
                                <div id="dupCommissionsEmpty" class="alert alert-success" style="display:none;">
                                    <i class="fa fa-check-circle me-1"></i> No duplicate commission records found.
                                </div>
                                <div id="dupCommissionsSummary" style="display:none;">
                                    <p class="mb-3"><strong>Duplicate pairs found:</strong> <span
                                            id="dupCommissionsCount">-</span></p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm" id="tableDupCommissions">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Account Code</th>
                                                    <th>Count</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: Orphaned Wallets -->
                <div class="row">
                    <div class="col-12">
                        <div class="card custom-card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Orphaned Wallet Entries</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Wallet entries with no matching or missing ib1_commission record
                                </p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 id="orphanBrokenFk" class="mb-1">-</h4>
                                                <p class="text-muted mb-0">Broken FK (invalid ib1_commission_id)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 id="orphanNullId" class="mb-1">-</h4>
                                                <p class="text-muted mb-0">NULL ib1_commission_id</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 5: Missing Commissions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card custom-card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Closed Deals Missing Commission</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Close deals in deals table with no ib1_commission record</p>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 id="missingCount" class="text-info mb-1">-</h4>
                                                <p class="text-muted mb-0">Deals Without Commission</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="missingTopAccounts" style="display:none;">
                                    <h6>Top Accounts with Missing Commissions</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm" id="tableMissingAccounts">
                                            <thead>
                                                <tr>
                                                    <th>Account Code</th>
                                                    <th>Missing Count</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 6: Stuck Commissions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card custom-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">Stuck Commissions</h6>
                                <button id="btnProcessStuck" class="btn btn-sm btn-warning" style="display:none;">
                                    <i class="fa fa-cogs me-1"></i> Process Stuck Commissions
                                </button>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Commissions that are stuck in processing pipeline</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 id="stuckNoWallets" class="mb-1">-</h4>
                                                <p class="text-muted mb-0">Status=0, No Wallets Created</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h4 id="stuckWrongStatus" class="text-warning mb-1">-</h4>
                                                <p class="text-muted mb-0">Status=0 but HAS Wallets (should be 1)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Stuck processing result -->
                                <div id="stuckProcessResult" class="mt-3" style="display:none;">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading mb-2"><i class="fa fa-check-circle me-1"></i> Stuck Commissions Processed</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Status Fixed:</strong> <span id="stuckResultFixed">-</span>
                                                <br><small class="text-muted">Had wallets, status→1</small>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Reprocessed:</strong> <span id="stuckResultReprocessed">-</span>
                                                <br><small class="text-muted">Close deals dispatched</small>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Discarded:</strong> <span id="stuckResultDiscarded">-</span>
                                                <br><small class="text-muted">Open-deal + no-match</small>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Remaining:</strong> <span id="stuckResultRemaining">-</span>
                                                <br><small class="text-muted">Trades-matched (phase 2)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 7: Overpaid IBs -->
                <div class="row">
                    <div class="col-12">
                        <div class="card custom-card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">IB Overpayment Analysis</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">IBs who received duplicate wallet entries per order</p>
                                <div id="overpaidEmpty" class="alert alert-success" style="display:none;">
                                    <i class="fa fa-check-circle me-1"></i> No overpaid IBs found.
                                </div>
                                <div id="overpaidTable" style="display:none;">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Referral Code</th>
                                                    <th>Total Wallets</th>
                                                    <th>Unique Orders</th>
                                                    <th>Duplicate Entries</th>
                                                    <th>Total Paid</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyOverpaid"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 7b: Top IBs by Commission -->
                <div class="row">
                    <div class="col-12">
                        <div class="card custom-card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Top 20 IBs by Total Commission</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Referral Code</th>
                                                <th>Wallet Entries</th>
                                                <th>Unique Orders</th>
                                                <th>Total Amount</th>
                                                <th>Avg Per Entry</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyTopIbs"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 7c: Overpayment Audit -->
                <div class="row">
                    <div class="col-12">
                        <div class="card custom-card border-danger">
                            <div class="card-header bg-danger-transparent">
                                <h6 class="card-title mb-0 text-danger">
                                    <i class="fa fa-exclamation-triangle me-1"></i> Overpayment Audit Report
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">
                                    Identifies duplicate commissions that resulted in actual overpayments (duplicate wallets created).
                                    Shows which IBs were overpaid, their withdrawal history, and recovery status.
                                </p>

                                <div id="overpaymentAuditEmpty" class="alert alert-success" style="display:none;">
                                    <i class="fa fa-check-circle me-1"></i> No overpayments detected from duplicate commissions.
                                </div>

                                <div id="overpaymentAuditContent" style="display:none;">
                                    <!-- Summary Cards -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="card bg-light border-danger">
                                                <div class="card-body text-center">
                                                    <h3 id="auditTotalOverpaid" class="text-danger mb-1">-</h3>
                                                    <p class="text-muted mb-0">Total Overpaid</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-light border-success">
                                                <div class="card-body text-center">
                                                    <h3 id="auditTotalRecovered" class="text-success mb-1">-</h3>
                                                    <p class="text-muted mb-0">Recovered</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-light border-warning">
                                                <div class="card-body text-center">
                                                    <h3 id="auditTotalOutstanding" class="text-warning mb-1">-</h3>
                                                    <p class="text-muted mb-0">Outstanding</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-light">
                                                <div class="card-body text-center">
                                                    <h3 id="auditDuplicateGroups" class="mb-1">-</h3>
                                                    <p class="text-muted mb-0">Duplicate Groups</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Affected IBs -->
                                    <h6 class="mb-3">Affected IBs</h6>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-sm table-hover" id="tableAffectedIbs">
                                            <thead class="table-danger">
                                                <tr>
                                                    <th>IB Code</th>
                                                    <th>Overpaid Amount</th>
                                                    <th>Recovered</th>
                                                    <th>Outstanding</th>
                                                    <th>Total Earned</th>
                                                    <th>Total Withdrawn</th>
                                                    <th>Current Balance</th>
                                                    <th>Recoverable from Balance</th>
                                                    <th>Details</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyAffectedIbs"></tbody>
                                        </table>
                                    </div>

                                    <!-- Overpayment Details -->
                                    <h6 class="mb-3">Overpayment Details (per Order)</h6>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Account Code</th>
                                                    <th>IB Code</th>
                                                    <th>Original Amount</th>
                                                    <th>Duplicate Amount</th>
                                                    <th>Original Date</th>
                                                    <th>Duplicate Date</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyOverpaymentDetails"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 8: Pipeline Health -->
                <div class="row">
                    <div class="col-12">
                        <div class="card custom-card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Commission Distribution Pipeline Health</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th>Label</th>
                                                <th>Count</th>
                                                <th>Oldest</th>
                                                <th>Newest</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyPipeline"></tbody>
                                    </table>
                                </div>
                                <h6>Daily Wallet Activity (Last 7 Days)</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Entries</th>
                                                <th>Total Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyDailyWallets"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /resultsContainer -->
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let chartStatus = null;
            let chartDaily = null;

            // Load data on page load
            loadAnalysis();

            $('#btnAnalyze').on('click', function () {
                loadAnalysis();
            });

            $('#btnReset').on('click', function () {
                $('#filterCode').val('');
                $('#filterReferral').val('');
                loadAnalysis();
            });

            $('#btnFixDuplicates').on('click', function () {
                Swal.fire({
                    title: 'Fix Duplicate Wallets?',
                    text: 'This will delete duplicate wallet rows, keeping only the earliest entry per (order_id, user_id). This cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, fix them'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route("admin.ib.commission-analysis.fix-duplicates") }}',
                            method: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function (res) {
                                Swal.fire('Done!', res.message, 'success');
                                loadAnalysis();
                            },
                            error: function () {
                                Swal.fire('Error', 'Failed to fix duplicates.', 'error');
                            }
                        });
                    }
                });
            });

            $('#btnFixDupCommissions').on('click', function () {
                Swal.fire({
                    title: 'Fix Duplicate Commissions?',
                    html: '<p>This will:</p><ul class="text-start"><li>Soft-delete duplicate commission records (keep earliest)</li><li>Zero out wallet credits from duplicate commissions</li><li>Overpayment records are preserved for audit</li></ul><p class="text-danger"><strong>This cannot be undone.</strong></p>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, fix them'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route("admin.ib.commission-analysis.fix-duplicate-commissions") }}',
                            method: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function (res) {
                                Swal.fire('Done!', res.message, 'success');
                                loadAnalysis();
                            },
                            error: function (xhr) {
                                Swal.fire('Error', 'Failed to fix duplicate commissions. ' + (xhr.responseJSON?.message || ''), 'error');
                            }
                        });
                    }
                });
            });

            $('#btnProcessStuck').on('click', function () {
                Swal.fire({
                    title: 'Process Stuck Commissions?',
                    html: '<p>This will:</p><ul class="text-start">' +
                        '<li><strong>Fix status:</strong> Update commissions that have wallets but status=0 → 1</li>' +
                        '<li><strong>Reprocess:</strong> Dispatch close-deal matched commissions for wallet creation</li>' +
                        '<li><strong>Discard:</strong> Mark open-deal-only and unresolvable commissions as status=10</li>' +
                        '</ul><p class="text-warning"><strong>This may take several minutes. Reprocessed commissions will be queued.</strong></p>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e67e22',
                    confirmButtonText: 'Yes, process them'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#btnProcessStuck').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Processing...');
                        $.ajax({
                            url: '{{ route("admin.ib.commission-analysis.process-stuck") }}',
                            method: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function (res) {
                                pollStuckStatus(res.process_id);
                            },
                            error: function (xhr) {
                                $('#btnProcessStuck').prop('disabled', false).html('<i class="fa fa-cogs me-1"></i> Process Stuck Commissions');
                                Swal.fire('Error', 'Failed to start processing. ' + (xhr.responseJSON?.message || ''), 'error');
                            }
                        });
                    }
                });
            });

            function pollStuckStatus(processId) {
                const pollInterval = setInterval(function () {
                    $.ajax({
                        url: '{{ route("admin.ib.commission-analysis.stuck-status") }}',
                        method: 'GET',
                        data: { id: processId },
                        success: function (res) {
                            $('#btnProcessStuck').html('<i class="fa fa-spinner fa-spin me-1"></i> ' + (res.progress || 'Processing...'));

                            if (res.status === 'completed') {
                                clearInterval(pollInterval);
                                $('#btnProcessStuck').prop('disabled', false).html('<i class="fa fa-cogs me-1"></i> Process Stuck Commissions');
                                showStuckResult(res.data);
                                Swal.fire('Done!', 'Stuck commissions processed successfully.', 'success');
                            } else if (res.status === 'failed') {
                                clearInterval(pollInterval);
                                $('#btnProcessStuck').prop('disabled', false).html('<i class="fa fa-cogs me-1"></i> Process Stuck Commissions');
                                Swal.fire('Error', 'Processing failed: ' + (res.progress || 'Unknown error'), 'error');
                            }
                        },
                        error: function () {
                            clearInterval(pollInterval);
                            $('#btnProcessStuck').prop('disabled', false).html('<i class="fa fa-cogs me-1"></i> Process Stuck Commissions');
                            Swal.fire('Error', 'Failed to check processing status.', 'error');
                        }
                    });
                }, 5000);
            }

            function showStuckResult(data) {
                $('#stuckProcessResult').show();
                $('#stuckResultFixed').text(fmt(data.wrong_status?.fixed || 0));
                $('#stuckResultReprocessed').text(fmt(data.reprocessed?.dispatched || 0));
                const discarded = (data.discarded_open_deal?.discarded || 0) + (data.discarded_open_deal_with_close?.discarded || 0) + (data.discarded_no_match?.discarded || 0);
                $('#stuckResultDiscarded').text(fmt(discarded));
                $('#stuckResultRemaining').text(fmt(data.remaining_stuck || 0));
            }

            function loadAnalysis() {
                const code = $('#filterCode').val();
                const referral = $('#filterReferral').val();

                $('#loadingOverlay').show();
                $('#resultsContainer').hide();
                $('#loadingMessage').text('Queuing analysis job...');
                $('#loadingProgress').text('');
                $('#btnAnalyze').prop('disabled', true);

                // Start the analysis job
                $.ajax({
                    url: '{{ route("admin.ib.commission-analysis.start") }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', code: code, referral: referral },
                    success: function (res) {
                        $('#loadingMessage').text('Analysis running in background...');
                        pollStatus(res.analysis_id);
                    },
                    error: function (xhr) {
                        $('#loadingOverlay').hide();
                        $('#btnAnalyze').prop('disabled', false);
                        Swal.fire('Error', 'Failed to start analysis. ' + (xhr.responseJSON?.message || ''), 'error');
                    }
                });
            }

            function pollStatus(analysisId) {
                const pollInterval = setInterval(function () {
                    $.ajax({
                        url: '{{ route("admin.ib.commission-analysis.status") }}',
                        method: 'GET',
                        data: { id: analysisId },
                        success: function (res) {
                            $('#loadingProgress').text(res.progress || '');

                            if (res.status === 'completed') {
                                clearInterval(pollInterval);
                                renderResults(res.data);
                                $('#loadingOverlay').hide();
                                $('#resultsContainer').show();
                                $('#btnAnalyze').prop('disabled', false);
                            } else if (res.status === 'failed') {
                                clearInterval(pollInterval);
                                $('#loadingOverlay').hide();
                                $('#btnAnalyze').prop('disabled', false);
                                Swal.fire('Error', 'Analysis failed: ' + (res.progress || 'Unknown error'), 'error');
                            } else {
                                $('#loadingMessage').text('Analysis running in background...');
                            }
                        },
                        error: function () {
                            clearInterval(pollInterval);
                            $('#loadingOverlay').hide();
                            $('#btnAnalyze').prop('disabled', false);
                            Swal.fire('Error', 'Failed to check analysis status.', 'error');
                        }
                    });
                }, 3000);
            }

            function fmt(n) {
                return Number(n).toLocaleString();
            }

            function fmtMoney(n) {
                return '$' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function renderResults(data) {
                // Overview
                const ov = data.overview;
                $('#ovTotalDeals').text(fmt(ov.total_deals));
                $('#ovTotalCommissions').text(fmt(ov.total_commissions));
                $('#ovUnprocessed').text(fmt(ov.commissions_unprocessed));
                $('#ovProcessed').text(fmt(ov.commissions_processed));
                $('#ovDiscarded').text(fmt(ov.commissions_discarded));
                $('#ovTotalWallets').text(fmt(ov.total_wallets));
                $('#ovTotalAmount').text(fmtMoney(ov.total_wallet_amount));

                // Health Issue Cards
                $('#issueDupCommissions').text(fmt(data.duplicate_commissions.count));
                $('#issueDupWallets').text(fmt(data.duplicate_wallets.count));
                $('#issueMissing').text(fmt(data.missing_commissions.count));
                $('#issueStuck').text(fmt(data.stuck_commissions.no_wallets));

                // Overpayment Summary Cards
                if (data.overpayment_audit) {
                    $('#issueOverpaidAmount').text(fmtMoney(data.overpayment_audit.total_overpaid_amount));
                    $('#issueRecoveredAmount').text(fmtMoney(data.overpayment_audit.total_recovered));
                    $('#issueOutstandingAmount').text(fmtMoney(data.overpayment_audit.total_outstanding));
                }

                // Commission Status Chart
                if (chartStatus) chartStatus.destroy();
                chartStatus = new ApexCharts(document.querySelector('#chartCommissionStatus'), {
                    chart: { type: 'donut', height: 300 },
                    series: [ov.commissions_unprocessed, ov.commissions_processed, ov.commissions_discarded],
                    labels: ['Unprocessed', 'Processed', 'Discarded'],
                    colors: ['#ffc107', '#28a745', '#6c757d'],
                    legend: { position: 'bottom' },
                    plotOptions: {
                        pie: { donut: { labels: { show: true, total: { show: true, label: 'Total', formatter: () => fmt(ov.total_commissions) } } } }
                    }
                });
                chartStatus.render();

                // Daily Wallets Chart
                if (chartDaily) chartDaily.destroy();
                const dailyData = data.pipeline_health.daily_wallets;
                chartDaily = new ApexCharts(document.querySelector('#chartDailyWallets'), {
                    chart: { type: 'bar', height: 300 },
                    series: [
                        { name: 'Entries', data: dailyData.map(d => d.entries) },
                        { name: 'Amount ($)', data: dailyData.map(d => d.total_amount) }
                    ],
                    xaxis: { categories: dailyData.map(d => d.day) },
                    colors: ['#007bff', '#28a745'],
                    yaxis: [
                        { title: { text: 'Entries' } },
                        { opposite: true, title: { text: 'Amount ($)' } }
                    ],
                    dataLabels: { enabled: false }
                });
                chartDaily.render();

                // Duplicate Wallets
                const dw = data.duplicate_wallets;
                if (dw.count === 0) {
                    $('#dupWalletsEmpty').show();
                    $('#dupWalletsSummary').hide();
                    $('#btnFixDuplicates').hide();
                } else {
                    $('#dupWalletsEmpty').hide();
                    $('#dupWalletsSummary').show();
                    $('#btnFixDuplicates').show();
                    $('#dupWalletsCount').text(fmt(dw.count));
                    $('#dupWalletsExtraRows').text(fmt(dw.total_extra_rows));
                    $('#dupWalletsOverpaid').text(Number(dw.total_overpaid).toFixed(4));
                    let html = '';
                    dw.items.forEach(function (r) {
                        html += `<tr>
                            <td>${r.order_id}</td>
                            <td title="${r.user_id}">${r.user_id.substring(0, 12)}...</td>
                            <td>${r.count}</td>
                            <td>${Number(r.total_amount).toFixed(4)}</td>
                            <td>${Number(r.expected_amount).toFixed(4)}</td>
                            <td class="text-danger">${Number(r.overpaid).toFixed(4)}</td>
                        </tr>`;
                    });
                    $('#tableDupWallets tbody').html(html);
                }

                // Duplicate Commissions
                const dc = data.duplicate_commissions;
                if (dc.count === 0) {
                    $('#dupCommissionsEmpty').show();
                    $('#dupCommissionsSummary').hide();
                    $('#btnFixDupCommissions').hide();
                } else {
                    $('#dupCommissionsEmpty').hide();
                    $('#dupCommissionsSummary').show();
                    $('#btnFixDupCommissions').show();
                    $('#dupCommissionsCount').text(fmt(dc.count));
                    let html = '';
                    dc.items.forEach(function (r) {
                        html += `<tr><td>${r.order_id}</td><td>${r.code}</td><td>${r.count}</td></tr>`;
                    });
                    $('#tableDupCommissions tbody').html(html);
                }

                // Orphaned Wallets
                $('#orphanBrokenFk').text(fmt(data.orphaned_wallets.broken_fk));
                $('#orphanNullId').text(fmt(data.orphaned_wallets.null_commission_id));

                // Missing Commissions
                const mc = data.missing_commissions;
                $('#missingCount').text(fmt(mc.count));
                if (mc.top_accounts.length > 0) {
                    $('#missingTopAccounts').show();
                    let html = '';
                    mc.top_accounts.forEach(function (r) {
                        html += `<tr><td>${r.code}</td><td>${fmt(r.missing_count)}</td></tr>`;
                    });
                    $('#tableMissingAccounts tbody').html(html);
                } else {
                    $('#missingTopAccounts').hide();
                }

                // Stuck Commissions
                $('#stuckNoWallets').text(fmt(data.stuck_commissions.no_wallets));
                $('#stuckWrongStatus').text(fmt(data.stuck_commissions.has_wallets_wrong_status));
                if (data.stuck_commissions.no_wallets > 0 || data.stuck_commissions.has_wallets_wrong_status > 0) {
                    $('#btnProcessStuck').show();
                } else {
                    $('#btnProcessStuck').hide();
                }

                // Overpaid IBs
                const oi = data.overpaid_ibs;
                if (oi.overpaid_ibs.length === 0) {
                    $('#overpaidEmpty').show();
                    $('#overpaidTable').hide();
                } else {
                    $('#overpaidEmpty').hide();
                    $('#overpaidTable').show();
                    let html = '';
                    oi.overpaid_ibs.forEach(function (r) {
                        html += `<tr>
                            <td>${r.referral_code}</td>
                            <td>${fmt(r.total_wallets)}</td>
                            <td>${fmt(r.unique_orders)}</td>
                            <td class="text-danger">${fmt(r.duplicate_entries)}</td>
                            <td>${fmtMoney(r.total_paid)}</td>
                        </tr>`;
                    });
                    $('#tbodyOverpaid').html(html);
                }

                // Top IBs
                let topHtml = '';
                oi.top_ibs.forEach(function (r) {
                    topHtml += `<tr>
                        <td>${r.referral_code}</td>
                        <td>${fmt(r.wallet_count)}</td>
                        <td>${fmt(r.unique_orders)}</td>
                        <td>${fmtMoney(r.total_amount)}</td>
                        <td>${Number(r.avg_per_entry).toFixed(6)}</td>
                    </tr>`;
                });
                $('#tbodyTopIbs').html(topHtml);

                // Pipeline
                let pipeHtml = '';
                data.pipeline_health.pipeline.forEach(function (r) {
                    pipeHtml += `<tr>
                        <td>${r.status}</td>
                        <td>${r.label}</td>
                        <td>${fmt(r.count)}</td>
                        <td>${r.oldest || '-'}</td>
                        <td>${r.newest || '-'}</td>
                    </tr>`;
                });
                $('#tbodyPipeline').html(pipeHtml);

                // Daily Wallets table
                let dwHtml = '';
                dailyData.forEach(function (r) {
                    dwHtml += `<tr>
                        <td>${r.day}</td>
                        <td>${fmt(r.entries)}</td>
                        <td>${fmtMoney(r.total_amount)}</td>
                    </tr>`;
                });
                if (dailyData.length === 0) {
                    dwHtml = '<tr><td colspan="3" class="text-center text-muted">No wallet entries in the last 7 days.</td></tr>';
                }
                $('#tbodyDailyWallets').html(dwHtml);

                // Overpayment Audit
                const audit = data.overpayment_audit;
                if (!audit || audit.total_overpaid_amount === 0) {
                    $('#overpaymentAuditEmpty').show();
                    $('#overpaymentAuditContent').hide();
                } else {
                    $('#overpaymentAuditEmpty').hide();
                    $('#overpaymentAuditContent').show();

                    $('#auditTotalOverpaid').text(fmtMoney(audit.total_overpaid_amount));
                    $('#auditTotalRecovered').text(fmtMoney(audit.total_recovered));
                    $('#auditTotalOutstanding').text(fmtMoney(audit.total_outstanding));
                    $('#auditDuplicateGroups').text(fmt(audit.total_duplicate_groups));

                    // Affected IBs table
                    let ibHtml = '';
                    audit.ibs_affected.forEach(function (ib) {
                        const canRecover = ib.can_recover_from_balance;
                        const badgeClass = canRecover ? 'bg-success' : 'bg-warning';
                        const badgeText = canRecover ? 'Yes' : 'No';
                        const outstandingClass = ib.outstanding > 0 ? 'text-danger fw-bold' : 'text-success';

                        let withdrawHtml = '';
                        if (ib.recent_withdrawals && ib.recent_withdrawals.length > 0) {
                            withdrawHtml = '<div class="mt-2 small"><strong>Recent Withdrawals:</strong><ul class="mb-0">';
                            ib.recent_withdrawals.forEach(function (w) {
                                withdrawHtml += '<li>' + fmtMoney(w.amount) + ' on ' + w.date + '</li>';
                            });
                            withdrawHtml += '</ul></div>';
                        }

                        ibHtml += `<tr>
                            <td><strong>${ib.referral_code}</strong></td>
                            <td class="text-danger">${fmtMoney(ib.overpaid_amount)}</td>
                            <td class="text-success">${fmtMoney(ib.total_recovered)}</td>
                            <td class="${outstandingClass}">${fmtMoney(ib.outstanding)}</td>
                            <td>${fmtMoney(ib.total_earned)}</td>
                            <td>${fmtMoney(ib.total_withdrawn)}</td>
                            <td>${fmtMoney(ib.current_balance)}</td>
                            <td><span class="badge ${badgeClass}">${badgeText}</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info btn-ib-detail" data-ib="${ib.referral_code}">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="ib-detail-row" id="detail-${ib.referral_code}" style="display:none;">
                            <td colspan="9" class="bg-light">
                                <div class="p-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Overpayment Summary:</strong>
                                            <ul class="mb-0">
                                                <li>Affected Orders: ${ib.affected_orders}</li>
                                                <li>Duplicate Wallets: ${ib.duplicate_wallets}</li>
                                                <li>Overpaid Likely Withdrawn: ${ib.overpaid_likely_withdrawn ? '<span class="text-danger">Yes</span>' : '<span class="text-success">No</span>'}</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">${withdrawHtml}</div>
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                    });
                    $('#tbodyAffectedIbs').html(ibHtml);

                    // Toggle detail rows
                    $('.btn-ib-detail').off('click').on('click', function () {
                        const ib = $(this).data('ib');
                        $('#detail-' + ib).toggle();
                    });

                    // Overpayment details table
                    let detailHtml = '';
                    audit.overpayment_details.forEach(function (d) {
                        detailHtml += `<tr>
                            <td>${d.order_id}</td>
                            <td>${d.account_code}</td>
                            <td>${d.ib_code}</td>
                            <td>${d.original_amount !== null ? fmtMoney(d.original_amount) : '<span class="text-muted">N/A</span>'}</td>
                            <td class="text-danger">${fmtMoney(d.overpaid_amount)}</td>
                            <td>${d.original_created}</td>
                            <td>${d.duplicate_created}</td>
                        </tr>`;
                    });
                    if (audit.overpayment_details.length === 0) {
                        detailHtml = '<tr><td colspan="7" class="text-center text-muted">No individual overpayment records.</td></tr>';
                    }
                    $('#tbodyOverpaymentDetails').html(detailHtml);
                }
            }
        });
    </script>
@endpush