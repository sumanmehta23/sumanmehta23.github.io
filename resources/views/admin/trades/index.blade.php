@extends('layouts.admin.admin')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="page-header">
                <h1 class="page-title">Trades Management</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">All Trades</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                All Trades
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tradesDataTable" class="table table-bordered text-nowrap w-100">
                                    <thead>
                                    <tr>
                                        <th>Client Name</th>
                                        <th>Client Email</th>
                                        <th>Account Code</th>
                                        <th>Order ID</th>
                                        <th>Symbol</th>
                                        <th>Type</th>
                                        <th>Volume</th>
                                        <th>Open Price</th>
                                        <th>Close Price</th>
                                        <th>Profit/Loss</th>
                                        <th>Status</th>
                                        <th>Open Time</th>
                                        <th>Action</th>
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
@endsection

@push('scripts')
    <script>
        $(function () {
            $('#tradesDataTable').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: '{{ route('admin.trades.data') }}',
                    type: 'GET',
                    dataSrc: 'data'
                },
                columns: [
                    {data: 'client_name', name: 'client_name', orderable: false},
                    {data: 'client_email', name: 'client_email', orderable: false},
                    {data: 'account_code', name: 'account_code', orderable: false},
                    {data: 'order_id_display', name: 'order_id', title: 'Order ID', orderable: true},
                    {data: 'symbol_display', name: 'symbol', title: 'Symbol', orderable: true},
                    {data: 'type_display', name: 'type', title: 'Type', orderable: true},
                    {data: 'volume_display', name: 'volume', title: 'Volume', orderable: true},
                    {data: 'open_price_display', name: 'open_price', title: 'Open Price', orderable: true},
                    {data: 'close_price_display', name: 'close_price', title: 'Close Price', orderable: true},
                    {data: 'profit_display', name: 'profit', title: 'Profit/Loss', orderable: true},
                    {data: 'status_display', name: 'status', title: 'Status', orderable: true},
                    {data: 'open_time_display', name: 'open_time', title: 'Open Time', orderable: true},
                    {data: 'action', name: 'action', title: 'Action', orderable: false, searchable: false}
                ],
                order: [[11, 'desc']],
                lengthChange: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
                dom: '<"row"<"col"B><"col text-center"l><"col"f>>' +
                    '<"row"<"col"t>>' +
                    '<"row"<"col"i><"col"p>>',
                buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        exportOptions: {
                            columns: [0,1,2,3,4,5,6,7,8,9,10,11]
                        }
                    },
                    {
                        extend: 'csv',
                        text: 'Export to CSV',
                        exportOptions: {
                            columns: [0,1,2,3,4,5,6,7,8,9,10,11]
                        }
                    }
                ],
                responsive: true,
                autoWidth: false
            });
        });
    </script>
@endpush


