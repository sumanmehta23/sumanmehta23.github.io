@extends('layouts.admin.admin')
@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
                <h1 class="page-title">Competition Dashboard</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Competition Dashboard</li>
                </ol>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Listed Count :
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="competitionDatatable" class="table competitionDatatable table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Name/Email</th>
                                            <th>Balance</th>
                                            <th>Equity</th>
                                            <th>Competition Month</th>
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
                searching: true,
                ajax: {
                    url: '/admin/getCompetitionDatatable',
                    type: 'GET',
                    data: {},
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                columns: [
                    {
                        data: 'rank',
                        name: 'rank'
                    },
                    {
                        data: 'name_email',
                        name: 'name_email',
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
                        data: 'month',
                        name: 'month',
                    },
                ],
                order: [
                    [0, "desc"]
                ],
                lengthChange: true,
                pageLength: 10,
                lengthMenu: [ [10, 25, 50, 100, 500, 1000], [10, 25, 50, 100, 500, 1000] ],
                dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
                buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        filename: 'Leaderboard_List_' + new Date().toISOString().slice(0, 10),
                        exportOptions: {
                            columns: [0,1,2,3,4] // Exclude the `Name/Email` column (index 2)
                        }
                    },
                    {
                        text: 'Export All',
                        action: function () {
                            window.location.href = "/admin/export-all-clients";
                        }
                    }
                ],
            })
        });
    </script>
@endsection
