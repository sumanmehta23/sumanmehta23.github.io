@extends('layouts.admin.admin')
@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
    <style>
        .pointer,
        .emailActionToggle,
        .statusToggle,
        .viewClient {
            cursor: pointer;
        }
        .switchClient{
            cursor: pointer;
        }
        .editClient{
            cursor: pointer;
        }
    </style>

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Competition Management</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Competition Management</li>
                </ol>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                                <div class="card-title mb-0">
                                    Competitions
                                </div>
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <button class="btn btn-primary btn-sm export-excel">
                                        <i class="fe fe-download me-2"></i>Export to Excel
                                    </button>
                                    <button class="btn btn-secondary btn-sm export-all">
                                        <i class="fe fe-download-cloud me-2"></i>Export All
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-4">
                                {{-- <div class="col-12">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent">
                                            <i class="fe fe-search"></i>
                                        </span>
                                        <input type="text" id="searchInput" class="form-control" placeholder="Search name, email, account...">
                                    </div>
                                </div> --}}
                                <div class="col-sm-6 col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent">
                                            <i class="fe fe-calendar"></i>
                                        </span>
                                        <input type="date" id="startDateFilter" class="form-control" placeholder="Start Date">
                                        <span class="input-group-text">to</span>
                                        <input type="date" id="endDateFilter" class="form-control" placeholder="End Date">
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <select id="statusFilter" class="form-select">
                                        <option value="">Filter by Status</option>
                                        <option value="0">Pending</option>
                                        <option value="1">Approved</option>
                                    </select>
                                </div>
                            </div>
                            {{-- {{ dd('dasdsa') }} --}}
                            <div class="table-responsive">
                                <table id="competitionDatatable" class="table competitionDatatable table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Account</th>
                                            <th>Status</th>
                                            <th>Name/Email</th>
                                            <th>Start Date/End Date</th>
                                            <th>Inital Balance</th>
                                            <th>Balance</th>
                                            <th>Equity</th>
                                            <th>Profit</th>
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

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var dTtable = $('#competitionDatatable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '/admin/getCompetitionsData',
                    type: 'GET',
                    data: function(d) {
                        d.search = $('#searchInput').val();
                        d.start_date = $('#startDateFilter').val();
                        d.end_date = $('#endDateFilter').val();
                        d.status = $('#statusFilter').val();
                    },
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                columns: [
                    {
                        data: 'code',
                        name: 'code'
                    },
                     {
                        data: 'account_status',
                        name: 'account_status'
                    },
                    {
                        data: 'email',
                        name: 'email',
                    },
                    {
                        data: 'start_end',
                        name: 'start_end',
                    },
                    {
                        data: 'initial_balance',
                        name: 'initial_balance',
                    },
                    {
                        data: 'balance',
                        name: 'balance',
                    },
                    {
                        data: 'equity',
                        name: 'equity',
                    },
                    {
                        data: 'profit',
                        name: 'profit',
                    }
                ],
                order: [
                    [0, "desc"]
                ],
                lengthChange: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, 500, 1000], [10, 25, 50, 100, 500, 1000]],
                dom: `<"row"<"col-sm-12"tr>>
                      <"row align-items-center"
                        <"col-sm-12 col-md-4"l>
                        <"col-sm-12 col-md-4 text-center"i>
                        <"col-sm-12 col-md-4"p>>`,
                language: {
                    lengthMenu: '_MENU_ records per page',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'No records available',
                    infoFiltered: '(filtered from _MAX_ total records)'
                },
            });

            // Handle filter changes
            $('#searchInput').on('keyup', function(){
                dTtable.ajax.reload();
            });

            $('#startDateFilter, #endDateFilter, #statusFilter').on('change', function(){
                dTtable.ajax.reload();
            });

            // Handle Excel export
            $('.export-excel').on('click', function() {
                let currentDate = new Date().toISOString().slice(0, 10);
                let filteredData = dTtable.rows().data().toArray();

                // Create a workbook with the filtered data
                let wb = XLSX.utils.book_new();
                let ws = XLSX.utils.json_to_sheet(filteredData.map(row => ({
                    'Account': row.account_code || 'Pending',
                    'Status': row.account_status === 1 ? 'Approved' : 'Pending',
                    'Name': row.fullname || '',
                    'Email': row.fullemail || '',
                    'Start Date/End Date': row.competition_start_date+' / '+row.competition_end_date,
                    'Initial Balance': parseFloat(row.initial_balance || 0).toFixed(2),
                    'Balance': parseFloat(row.balance || 0).toFixed(2),
                    'Equity': parseFloat(row.equity || 0).toFixed(2),
                    'Profit': (row.balance - row.initial_balance) ?? 'N/A'
                })));

                // Set column widths
                const colWidths = [
                    {wch: 15}, // Account
                    {wch: 10}, // Status
                    {wch: 20}, // Name
                    {wch: 30}, // Email
                    {wch: 12}, // Month/Year
                    {wch: 15}, // Initial Balance
                    {wch: 15}, // Balance
                    {wch: 15}, // Equity
                    {wch: 15}, // Profit
                ];
                ws['!cols'] = colWidths;

                XLSX.utils.book_append_sheet(wb, ws, 'Competition Data');
                XLSX.writeFile(wb, `Competition_List_${currentDate}.xlsx`);
            });

            // Handle Export All
            $('.export-all').on('click', function() {
                window.location.href = "/admin/export-competitions";
            });
        });
    </script>
@endsection
