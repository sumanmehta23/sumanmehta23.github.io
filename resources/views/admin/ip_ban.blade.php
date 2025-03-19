@extends('layouts.admin.admin')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header">
            <h1 class="page-title">Ban Client Ip's</h1>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <form action="{{ route('admin.send_ip_ban_reason') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="emails" class="form-label">Client Ip's (Comma Separated)</label>
                                <textarea name="ip" id="ip" class="form-control" rows="3" placeholder="Enter multiple Ip's separated by commas"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Ban Reason</label>
                                <select class="form-select" required name="reason">
                                    <option value="" selected disabled>Select Reason</option>
                                    <option value="hft">HFT</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Block IP</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12">
              <div class="card custom-card">
                <div class="card-body">
                  <div class="table-responsive">
                    <table id="tableBlockedIP" class="table ajaxDataTable table-bordered text-nowrap w-100">
                      <thead>
                        <tr>
                          <th>IP</th>
                          <th>Name</th>
                          <th>Email</th>
                          <th>Reason</th>
                          <th>Ban Date</th>
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
@push('scripts')
<script>
    $(document).ready(function () {
        // DataTable initialization
        var tableBlockedIP = $('#tableBlockedIP').DataTable({
            dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',

            buttons: [
                {
                    extend: 'excel',
                    text: 'Export to Excel',
                    filename: 'Blocked_Ips_' + new Date().toISOString().slice(0, 10),
                    exportOptions: {
                    columns: [0,1,2,3,4] // Updated column indices to match your use case
                    }
                },
            ],

            order: [[3, "desc"]],
            processing: true,
            serverSide: true,
            searching: true,
            ajax: {
            url: '/admin/getBlockedIPs',
            type: 'GET',
            data: {}, // Ensure this is populated dynamically if needed.
            dataSrc: function (json) {
                return json.data;
            }
            },
            columns: [
            { data: 'ip', name: 'ip' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'reason', name: 'reason' },
            { data: 'created_at', name: 'created_at' },
            ]
        });

    });
</script>
@endsection
