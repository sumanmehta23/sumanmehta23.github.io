@extends('layouts.admin.admin')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header">
            <h1 class="page-title">Activity Logs</h1>
        </div>
            <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                <div class="card-body">
                    <div class="table-responsive">
                    <table id="logsTable" class="table ajaxDataTable table-bordered text-nowrap w-100">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Event</th>
                            <th>User</th>
                            <th>Description</th>
                            <th>Created At</th>

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
@section("scripts")
<script>
    $(document).ready(function () {
          var tableInternalTransfer = $('#logsTable').DataTable({
            // order: [[0, "desc"]],
            dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',

            buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        exportOptions: {
                            columns: [0,1,2,3,4] // Updated column indices to match your use case
                        }
                    }
                ],

            order: [[3, "desc"]],
            processing: true,
            serverSide: true,
            searching: true,
            ajax: {
                url: '/admin/getLogs',
                type: 'GET',
                data: function(d) {
                        d.status = $('select[name=status]').val();
                        return d;
                    },  // Ensure this is populated dynamically if needed.
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [
              { data: 'id', name: 'id' },
              { data: 'event', name: 'event' },
              { data: 'user', name: 'user' },
              { data: 'description', name: 'description' },
              { data: 'created_at', name: 'created_at' },

            ],
            columnDefs: [
                { width: "30px", targets: 3 } // Set width for 'description' column
            ]
          });
          $('#statusFilter').on('change', function () {
          });
    });
</script>

@endsection
