@extends('layouts.admin.admin')
@section('content')
@php
    $liveAccountsLoaderLogo = app()->environment('production')
        ? '/storage/files/1744859843_1734897619_IMG_4441.png'
        : '/storage/files/1756380709_lqh.png';
@endphp
<style>
    .deleteAcc{
        cursor: pointer;
    }

    .live-account-filters .form-label {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 0.35rem;
        color: #667085;
        text-transform: uppercase;
    }

    .live-account-filters .section-title {
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #5c6582;
        text-transform: uppercase;
    }

    .live-account-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10060;
        background: rgba(255, 255, 255, 0.92);
        display: none;
        align-items: center;
        justify-content: center;
    }

    .live-account-overlay.active {
        display: flex;
    }

    .live-account-overlay-content {
        text-align: center;
        max-width: 480px;
        padding: 1rem;
    }

    .live-account-overlay-logo {
        max-width: 140px;
        width: auto;
        margin-bottom: 1rem;
    }
</style>
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Client - Live Accounts</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item">Client List</li>
                    <li class="breadcrumb-item active" aria-current="page">Live Accounts</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->


            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card live-account-filters">
                        <div class="card-header justify-content-between">
                            <div class="mb-0 card-title">Filters</div>
                            <div class="gap-2 d-flex">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="reset-filters-btn">Reset All</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="toggle-filters-btn">
                                    <span id="toggle-filters-icon">▲</span> <span id="toggle-filters-text">Collapse</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="filters-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="section-title">Account</div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="filter-status">Status</label>
                                    <select class="form-select" id="filter-status">
                                        <option value="">All</option>
                                        <option value="active">Active</option>
                                        <option value="deleted">Deleted</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="filter-leverage">Leverage</label>
                                    <select class="form-select" id="filter-leverage">
                                        <option value="">All Leverage</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="200">200</option>
                                        <option value="500">500</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="filter-balance-min">Balance Min</label>
                                    <input type="number" step="0.01" class="form-control" id="filter-balance-min" placeholder="Min">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="filter-balance-max">Balance Max</label>
                                    <input type="number" step="0.01" class="form-control" id="filter-balance-max" placeholder="Max">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="filter-has-balance">Has Balance</label>
                                    <select class="form-select" id="filter-has-balance">
                                        <option value="">All</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-1 row g-3">
                                <div class="col-12">
                                    <div class="section-title">Registration Date</div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="filter-registered-from">From</label>
                                    <input type="date" class="form-control" id="filter-registered-from">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="filter-registered-to">To</label>
                                    <input type="date" class="form-control" id="filter-registered-to">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="filter-registered-period">Registered Period</label>
                                    <select class="form-select" id="filter-registered-period">
                                        <option value="custom" selected>Custom / All time</option>
                                        <option value="7d">Last 7 days</option>
                                        <option value="30d">Last 30 days</option>
                                        <option value="90d">Last 90 days</option>
                                        <option value="6m">Last 6 months</option>
                                        <option value="12m">Last 12 months</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-1 row g-3">
                                <div class="col-12">
                                    <div class="section-title">Trading Activity</div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="filter-days-min">Days Since Last Trade (Min)</label>
                                    <input type="number" min="0" step="1" class="form-control" id="filter-days-min" placeholder="Min">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="filter-days-max">Days Since Last Trade (Max)</label>
                                    <input type="number" min="0" step="1" class="form-control" id="filter-days-max" placeholder="Max">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="filter-activity-status">Activity Status</label>
                                    <select class="form-select" id="filter-activity-status">
                                        <option value="">All</option>
                                        <option value="no_trades">No trades</option>
                                        <option value="lt_20">&lt; 20 days</option>
                                        <option value="between_20_40">20-40 days</option>
                                        <option value="gt_40">&gt; 40 days</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="filter-deposited">Deposited</label>
                                    <select class="form-select" id="filter-deposited">
                                        <option value="">All</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                        <option value="wallet_deposit">Wallet Deposit</option>
                                        <option value="internal_transfer">Internal Transfer</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="filter-traded">Traded</label>
                                    <select class="form-select" id="filter-traded">
                                        <option value="">All</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3 row">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary btn-sm" id="apply-filters-btn">Apply Filters</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-none">
                            <div class="card-title">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="ajaxDatatable" class="table ajaxDataTable table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <td>Client</td>
                                            <td>Trade ID</td>
                                            <td>Leverage</td>
                                            <td>Balance</td>
                                            <td>Registered Date</td>
                                            <td>Last Trade Date</td>
                                            <td>Days Since Last Trade</td>
                                            <td>Deposited</td>
                                            <td>Traded</td>
                                            <td>Status</td>
                                            <td>Total Dep.</td>
                                            <td>Total Withdraw</td>
                                            <td>Name</td>
                                            <td>Email</td>
                                            <td>Account Code</td>
                                            <td>Account Group</td>
                                            <td>Date</td>
                                            <td>Time</td>
                                            <td>Country</td>
                                            @can('account:update')
                                                <td>Actions</td>
                                            @endcan
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

    <div id="live-accounts-overlay" class="live-account-overlay">
        <div class="live-account-overlay-content">
            <img class="live-account-overlay-logo" src="{{ $liveAccountsLoaderLogo }}" alt="Logo">
            <div class="mb-2 fw-semibold">Pulling the data. This page will load once everything is retrieved.</div>
            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
        </div>
    </div>

<script>
    @if(session()->has('warning'))
        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: "{{ session('warning') }}",
            confirmButtonText: 'OK'
        });
    @endif

    @if(session()->has('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: "{{ session('success') }}",
            confirmButtonText: 'OK'
        });
    @endif

    @if(session()->has('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "{{ session('error') }}",
            confirmButtonText: 'OK'
        });
    @endif

    @if(session()->has('message'))
        Swal.fire({
            icon: 'info',
            title: 'Information',
            text: "{{ session('message') }}",
            confirmButtonText: 'OK'
        });
    @endif
</script>

@endsection()
@section("scripts")
<!-- End::app-content -->
<script>
    window.canUpdateAccount = @json(auth()->user()->can('account:update'));
</script>

<script>
    let liveAccountOverlayTimer = null;

    function showLiveAccountsOverlay(timeoutMs) {
        const overlay = $('#live-accounts-overlay');
        if (!overlay.length) {
            return;
        }

        overlay.addClass('active');
        $('body').css('overflow', 'hidden');

        if (liveAccountOverlayTimer) {
            clearTimeout(liveAccountOverlayTimer);
            liveAccountOverlayTimer = null;
        }

        if (timeoutMs && timeoutMs > 0) {
            liveAccountOverlayTimer = setTimeout(function () {
                hideLiveAccountsOverlay();
            }, timeoutMs);
        }
    }

    function hideLiveAccountsOverlay() {
        if (liveAccountOverlayTimer) {
            clearTimeout(liveAccountOverlayTimer);
            liveAccountOverlayTimer = null;
        }

        $('#live-accounts-overlay').removeClass('active');
        $('body').css('overflow', '');
    }

    function formatDateForInput(dateObj) {
        const year = dateObj.getFullYear();
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const day = String(dateObj.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function applyRegisteredPeriod(period) {
        if (period === 'custom') {
            $('#filter-registered-from').val('');
            $('#filter-registered-to').val('');
            return;
        }

        const toDate = new Date();
        const fromDate = new Date();

        if (period === '7d') {
            fromDate.setDate(fromDate.getDate() - 6);
        } else if (period === '30d') {
            fromDate.setDate(fromDate.getDate() - 29);
        } else if (period === '90d') {
            fromDate.setDate(fromDate.getDate() - 89);
        } else if (period === '6m') {
            fromDate.setMonth(fromDate.getMonth() - 6);
        } else if (period === '12m') {
            fromDate.setMonth(fromDate.getMonth() - 12);
        }

        $('#filter-registered-from').val(formatDateForInput(fromDate));
        $('#filter-registered-to').val(formatDateForInput(toDate));
    }

    function collectLiveAccountFilters() {
        return {
            filter_status: $('#filter-status').val(),
            filter_leverage: $('#filter-leverage').val(),
            balance_min: $('#filter-balance-min').val(),
            balance_max: $('#filter-balance-max').val(),
            has_balance: $('#filter-has-balance').val(),
            registered_from: $('#filter-registered-from').val(),
            registered_to: $('#filter-registered-to').val(),
            days_since_last_trade_min: $('#filter-days-min').val(),
            days_since_last_trade_max: $('#filter-days-max').val(),
            activity_status: $('#filter-activity-status').val(),
            deposited: $('#filter-deposited').val(),
            traded: $('#filter-traded').val()
        };
    }

    $(document).ready(function() {
        var modalElement = document.getElementById('accountUpdatemodal');
        if (modalElement) {
            window.myModal = new bootstrap.Modal(modalElement);
        }
    });
    // console.log(bootstrap.Modal);

    // $("#ibModal").modal();
    function dTSelection() {
        // alert("Init");
        $('.ajaxDataTable tbody tr').off();
        $('.ajaxDataTable tbody tr').on('click', '.ibToggle', function() {
            var data = dTtable.row($(this).closest("tr")).data();
            console.log(data.id);
            $("#AccountRequestForm input,#AccountRequestForm select").not("input[name='_token']").val("").trigger("change");
            $("#clientName,#clientEmail").html("");
            $("#account_id").val(data.id)
            $("#clientName").html(data.fullname || "")
            $("#clientEmail").html(data.email || "")
            $("#client_id").val(data.user_id)
            $("#leverage").val(data.leverage)
            $("#account_type_id").val(data.account_type_id)
            $("[name='request_status']").val(data.request_status).trigger("change");
            myModal.show();

        });
        $('.ajaxDataTable tbody tr').on('click', '.deleteAcc', function() {
            var data = dTtable.row($(this).closest("tr")).data()
            // console.log(data.id);
            // console.log(data.fullemail);

            Swal.fire({
                    title: `This will soft delete the account — client info will be hidden but kept for records.
                        Accounts with trading history will be disabled, not deleted.
                        Deposits and withdrawals remain for reconciliation, and emails to the client will stop.`,

                    html: `
                    <form id="delete_account_form" method="post" action="deleteAccounts">
                    @csrf
                    <input type="hidden" name="id" value="${data.id}">
                    <input type="hidden" name="email" value="${data.fullemail}">
                    </form>
                `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    preConfirm: () => {
                    return true;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // location.href = "/admin/clientAccounts/deleteAccounts/" + data.id;
                         document.querySelector('#delete_account_form').submit();
                    }else {
                        location.href = "";
                    }
                });
        });
    }

    $(document).ready(function() {
        $('#filter-registered-period').on('change', function () {
            applyRegisteredPeriod($(this).val());
        });

        $('#toggle-filters-btn').on('click', function () {
            const body = $('#filters-body');
            const isHidden = body.is(':hidden');
            body.toggle(isHidden);
            $('#toggle-filters-icon').text(isHidden ? '▲' : '▼');
            $('#toggle-filters-text').text(isHidden ? 'Collapse' : 'Expand');
        });

        $('#apply-filters-btn').on('click', function () {
            showLiveAccountsOverlay();
            dTtable.ajax.reload();
        });

        $('#reset-filters-btn').on('click', function () {
            $('#filter-status').val('');
            $('#filter-leverage').val('');
            $('#filter-balance-min').val('');
            $('#filter-balance-max').val('');
            $('#filter-has-balance').val('');
            $('#filter-registered-from').val('');
            $('#filter-registered-to').val('');
            $('#filter-registered-period').val('custom');
            $('#filter-days-min').val('');
            $('#filter-days-max').val('');
            $('#filter-activity-status').val('');
            $('#filter-deposited').val('');
            $('#filter-traded').val('');

            showLiveAccountsOverlay();
            dTtable.ajax.reload();
        });

        const dataTableElement = $('#ajaxDatatable');

        dataTableElement
            .on('preXhr.dt', function () {
                showLiveAccountsOverlay();
            })
            .on('xhr.dt error.dt', function () {
                hideLiveAccountsOverlay();
            });

        const excelAction = ($.fn.dataTable.ext.buttons.excelHtml5 && $.fn.dataTable.ext.buttons.excelHtml5.action)
            ? $.fn.dataTable.ext.buttons.excelHtml5.action
            : ($.fn.dataTable.ext.buttons.excel && $.fn.dataTable.ext.buttons.excel.action ? $.fn.dataTable.ext.buttons.excel.action : null);

    window.dTtable = dataTableElement.on("draw.dt", dTSelection).DataTable({
    // var dTtable = $('#ajaxDatatable').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            ajax: {
                url: '/admin/getLiveAccountsList',
                type: 'GET',
                data: function (d) {
                    return $.extend({}, d, collectLiveAccountFilters());
                },
                dataSrc: function(json) {
                    return json.data;
                },
                error: function(xhr, error, code) {
                    hideLiveAccountsOverlay();
                    console.log('DataTable AJAX Error:', xhr, error, code);
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        alert('Error loading data: ' + xhr.responseJSON.message);
                    } else if (xhr.responseText) {
                        console.log('Response Text:', xhr.responseText);
                        alert('Error loading live accounts. Please check console for details.');
                    }
                }
            },
            columns: [
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'leverage',
                    name: 'leverage'
                },
                {
                    data: 'balance',
                    name: 'balance',
                    orderable: true
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    orderable: true
                },
                {
                    data: 'last_trade_date',
                    name: 'last_trade_date',
                },
                {
                    data: 'days_since_last_trade',
                    name: 'days_since_last_trade',
                    orderable: true
                },
                {
                    data: 'deposited',
                    name: 'deposited',
                    orderable: true
                },
                {
                    data: 'traded',
                    name: 'traded',
                },
                {
                    data: 'account_status',
                    name: 'account_status',
                },
                {
                    data: 'total_deposit',
                    name: 'total_deposit',
                    orderable: true
                },
                {
                    data: 'total_withdraw',
                    name: 'total_withdraw',
                    orderable: true
                },
                {
                    data: 'fullname',
                    name: 'fullname',
                    visible: false,

                },
                {
                    data: 'fullemail',
                    name: 'fullemail',
                    visible: false,

                },
                {
                    data: 'account_code',
                    name: 'account_code',
                    visible: false,

                },
                {
                    data: 'account_group',
                    name: 'account_group',
                    visible: false,

                },
                {
                    data: 'created_date',
                    name: 'created_date',
                    visible: false,

                },
                {
                    data: 'created_time',
                    name: 'created_time',
                    visible: false,

                },
                {
                    data: 'user_country',
                    name: 'user_country',
                    visible: false,
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    visible: window.canUpdateAccount
                }

            ],
            rowCallback: function(row, data) {
                // Optional customization for rows
            },
            drawCallback: function(settings) {
                // Optional customization for draw events
            },
            order: [[0, "desc"]],
            lengthChange: true,
            pageLength: 10,
            // lengthMenu: [[10, 25, 50, 100, 500, 1000], [10, 25, 50, 100, 500, 1000]],
            dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
            buttons: (() => {
                let buttons = [];
                @hasExportPermission('live_accounts')
                    buttons.push({
                        extend: 'excel',
                        text: 'Export to Excel',
                        filename: 'Live_Accounts_' + new Date().toISOString().slice(0, 10),
                        action: function (e, dt, node, config) {
                            showLiveAccountsOverlay(4500);
                            if (excelAction) {
                                excelAction.call(this, e, dt, node, config);
                            }
                        },
                        exportOptions: {
                              columns: [12, 13, 14, 2, 3, 9, 5, 6, 10, 11, 7, 8, 15, 16, 18]
                        }
                    });
                    buttons.push({
                        text: 'Export All',
                        action: function () {
                            showLiveAccountsOverlay(9000);
                            const query = $.param(collectLiveAccountFilters());
                            window.location.href = query
                                ? ("/admin/export-all-live-accounts?" + query)
                                : "/admin/export-all-live-accounts";
                        }
                    });
                @endif
                return buttons;
            })(),
        });
    });


</script>
@endsection()
