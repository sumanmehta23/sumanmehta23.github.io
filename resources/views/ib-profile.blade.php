@extends('layouts.crm.crm')
@section('styles')
    <link rel="stylesheet" href="/assets1/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
    <style>
        div.dataTables_wrapper div.dataTables_length select {
            width: 70px;
        }
    </style>
@endsection

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <div class="pt-0 pb-0 mt-0 mb-0 page-header">
                <div class="pt-0 pb-0 mt-0 mb-0 page-block">
                    <div class="pt-0 pb-0 mt-0 mb-0 row align-items-center">
                        <div class="pt-0 pb-0 mt-0 mb-0 col-md-12">
                            <div class="page-header-title h2">
                                <h4 class="mb-0">My IB Profile</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pt-0 pb-0 mt-0 mb-0 row">
                <div class="pt-0 pb-0 mt-0 mb-0 col-12">
                    <div class="pt-0 pb-0 mt-0 mb-0 card">
                        <div class="pt-0 pb-0 mt-0 mb-0 card-body">
                            <div class="pt-0 pb-0 mt-0 mb-0 row">
                                <ul class="nav nav-tabs profile-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation"><a class="nav-link active" id="profile-tab-1"
                                            data-bs-toggle="tab" href="#ib-home" role="tab" aria-selected="true"><i
                                                class="ti ti-smart-home me-2"></i>IB Home </a></li>
                                    <li class="nav-item" role="presentation"><a class="nav-link" id="profile-tab-2"
                                            data-bs-toggle="tab" href="#ib-connect" role="tab" aria-selected="false"
                                            tabindex="-1"><i class="ti ti-affiliate me-2"></i>My Connections </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pt-0 pb-0 mt-0 mb-0 tab-content">
                <div class="pt-0 pt-3 pb-0 mt-0 mb-0 tab-pane active show" id="ib-home" role="tabpanel"
                    aria-labelledby="profile-tab-1">
                    <div class="pt-0 pb-0 mt-0 mb-0 row">
                        <div class="pt-0 pb-0 mt-0 mb-0 col-lg-9">
                            <div class="pt-0 pb-0 mt-0 mb-0 card">
                                <div class="pt-3 pb-3 mt-0 mb-0 card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6 col-xxl-4">
                                            <div class="mb-0 card">
                                                <div class="p-3 card-body">
                                                    <div class="gap-1 d-flex align-items-center justify-content-between">
                                                        <div class="gap-1 d-flex align-items-center">
                                                            <h3 class="mb-0 f-w-500">{{ $ib_clients_total }}</h3>
                                                        </div>
                                                        <div class="avtar avtar-s bg-light-primary"><i
                                                                class="ti ti-mood-kid f-18"></i></div>
                                                    </div>
                                                    <p class="gap-2 mt-3 mb-0 text-muted d-flex align-items-center f-12">
                                                        Total Clients </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xxl-4">
                                            <div class="mb-0 card">
                                                <div class="p-3 card-body">
                                                    <div class="gap-1 d-flex align-items-center justify-content-between">
                                                        <div class="gap-1 d-flex align-items-center">
                                                            <h5 class="mb-0 f-w-500">
                                                                @money(isset($ib_wallet_raw->wallet) ? $ib_wallet_raw->wallet : '0.00')
                                                            </h5>
                                                        </div>
                                                        <div class="avtar avtar-s bg-light-primary"><i
                                                                class="ti ti-report-money f-18"></i></div>
                                                    </div>
                                                    <p class="gap-2 mt-3 mb-0 text-muted d-flex align-items-center f-12">
                                                        Generated Commission
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xxl-4">
                                            <div class="mb-0 card">
                                                <div class="p-3 card-body">
                                                    <div class="gap-1 d-flex align-items-center justify-content-between">
                                                        <div class="gap-1 d-flex align-items-center">
                                                            <h5 class="mb-0 f-w-500">
                                                                @money(isset($ib_wallet_raw->withdraw) ? $ib_wallet_raw->withdraw : '0.00')
                                                            </h5>
                                                        </div>
                                                        <div class="avtar avtar-s bg-light-primary"><i
                                                                class="ti ti-shield-check f-18"></i></div>
                                                    </div>
                                                    <p class="gap-2 mt-3 mb-0 text-muted d-flex align-items-center f-12">
                                                        Commission Transferred
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="p-1 row">
                                        <div class="col-12">
                                            <div class="p-2 rounded bg-body">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0"><span
                                                                class="p-1 d-block bg-primary rounded-circle"><span
                                                                    class="visually-hidden">New alerts</span></span></div>
                                                        <div class="flex-grow-1 ms-2">
                                                            <p class="mb-0">Deposits</p>
                                                        </div>
                                                    </div>
                                                    <h5 class="mb-0 f-w-500">
                                                        @money(isset($IbTotalDeposits) ? $IbTotalDeposits : '0.00')</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-1 row">
                                        <div class="col-12">
                                            <div class="p-2 rounded bg-body">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0"><span
                                                                class="p-1 d-block bg-primary rounded-circle"><span
                                                                    class="visually-hidden">New alerts</span></span></div>
                                                        <div class="flex-grow-1 ms-2">
                                                            <p class="mb-0">Withdrawals</p>
                                                        </div>
                                                    </div>
                                                    <h5 class="mb-0 f-w-500">
                                                        @money(isset($IbTotalWithdrawal) ? $IbTotalWithdrawal : '0.00')</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pt-0 pb-0 mt-0 mb-0 row">
                        <div class="pt-0 pb-0 mt-0 mb-0 col-xl-6 col-md-6">
                            <form method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="pt-0 pb-0 mt-0 mb-0 card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h5 class="mb-0 f-w-500">Transfer My Commission</h5>
                                            <div class="p-1 mt-1 rounded bg-body">
                                                <div class="mt-1 row align-items-center">
                                                    <div class="col-12 text-end">
                                                        <h3 class="mb-1 me-2 ms-2 f-w-500">
                                                            @money($ib_wallet)</h3>
                                                        <p class="mb-0 text-warning me-2 ms-2"> Transferable Balance</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr style="opacity: 0.1;">
                                        <div class="form"><label class="mt-3 form-label"
                                                for="exampleFormControlSelect1">Select
                                                Account</label>
                                            {{-- Hidden radio inputs to maintain existing JS behaviour --}}
                                            <div style="display:none">
                                                @forelse ($live_accs as $acc)
                                                    <input id="{{ $acc->id }}"
                                                        type="radio" name="account"
                                                        class="select-account"
                                                        value="{{ $acc->id }}">
                                                @empty
                                                @endforelse
                                            </div>
                                            {{-- Custom account dropdown --}}
                                            <div class="mb-3 dropdown">
                                                <button class="px-3 py-3 btn btn-outline-secondary dropdown-toggle w-50 d-flex justify-content-between align-items-center" type="button" id="accountDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" style="background:#fff; border-radius:8px;">
                                                    <span id="accountDropdownLabel" class="text-muted w-100 text-start">Select Account</span>
                                                </button>
                                                <ul class="shadow dropdown-menu w-50" id="accountDropdownMenu" aria-labelledby="accountDropdownBtn" style="border-radius:8px; overflow:hidden;">
                                                    @forelse ($live_accs as $acc)
                                                        <li>
                                                            <a class="py-2 dropdown-item d-flex justify-content-between align-items-center account-dropdown-item"
                                                               href="#"
                                                               data-account-id="{{ $acc->id }}"
                                                               data-account-code="{{ $acc->code }}"
                                                               data-account-balance="{{ $acc->balance }}">
                                                                <span class="d-flex align-items-center">
                                                                    <img src="/assets/images/mt5.png" alt="mt5" class="wid-25 me-2">
                                                                    <span class="fw-medium">{{ $acc->code }}</span>
                                                                </span>
                                                                <span class="text-end">
                                                                    <span class="d-block fw-medium">@money($acc->balance)</span>
                                                                    <small class="text-muted">Current Balance</small>
                                                                </span>
                                                            </a>
                                                        </li>
                                                    @empty
                                                        <li>
                                                            <a class="py-2 dropdown-item text-muted" href="{{ route('show-live-account-form') }}">
                                                                <i class="ti ti-plus me-2"></i>Create new Live Account
                                                            </a>
                                                        </li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                            <label class="form-label" for="exampleFormControlSelect1">Enter
                                                Amount</label>
                                            <div class="mb-3 input-group"><span class="input-group-text">$</span>
                                                <input type="number" name="amount" min="0.01" step="0.01" class="form-control" required aria-label="Amount (to the nearest dollar)">
                                                    <span class="input-group-text">.00</span>
                                                <!---->
                                            </div>
                                            <div class="mt-4 mb-5 d-grid"><button class="btn btn-outline-secondary"
                                                    name="transfer" type="submit"><i class="ti ti-shield-check me-2"></i>
                                                    <!----> Process Transfer</button></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            @if ($errors->any())
                                <script>
                                    Swal.fire({
                                        icon: 'error',
                                        title: "Can't Transfer Commission",
                                        html: `<div style="text-align: left; padding-left: 10px;">
                                            @foreach ($errors->all() as $error)
                                                <div style="margin-bottom: 5px;">• {{ $error }}</div>
                                            @endforeach
                                        </div>`
                                    });
                                </script>
                            @endif

                            @if (session('error'))
                                <script>
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: "{{ session('error') }}",
                                    });
                                </script>
                            @endif
                        </div>
                        <div class="pt-0 mt-0 col-xl-6 col-md-6">
                            <div class="pt-0 mt-0 card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5 class="mb-0 f-w-500">My Referral Link</h5>
                                        <div class="avtar avtar-s bg-light-primary"><i class="ti ti-list f-18"></i></div>
                                    </div>
                                    <?php

                                    ?>
                                    <hr style="opacity:.1;"><label class="col-form-label col-12 text-lg-start">Your personal
                                        referral link is now available! Share it to help new clients sign up and kick-start
                                        their trading journey.</label>
                                    <div class="mb-4 col-12">

                                        <div class="mb-2 input-group">
                                            <input type="text" value="{{ $ib->referral_code }}" class="form-control"
                                                id="referral-code" placeholder="Generated code will appear here">

                                            <button type="button" class="btn btn-lg btn-primary"
                                                id="generate-btn">Generate</button>
                                        </div>
                                    </div>
                                    <div class="mb-4 col-12">

                                        <div class="mb-2 input-group"><input type="text" class="form-control"
                                                id="pc-clipboard-1" placeholder="Type some value to copy"
                                                value="{{ url('/ib-ref?refercode=' . $ib->referral_code) }}"
                                                readonly=""><button class="btn btn-lg btn-primary cb"
                                                data-clipboard-target="#pc-clipboard-1"><i
                                                    class="feather icon-copy"></i></button></div>
                                    </div>

                                    <div class="gap-3 mt-4 d-flex flex-column align-items-start align-items-sm-left justify-content-left">

                                        <!-- Instruction side -->
                                        <div class="py-3 mb-0 border-0 alert alert-secondary rounded-3 w-100">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-shrink-0 me-3">
                                                    <i class="ti ti-info-circle fs-5"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-semibold">Note:</span>
                                                    <span class="text-primary">Generate a new referral link, then click <strong class="text-secondary">Submit</strong> to apply the changes.</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Button side -->
                                        <div class="mt-3 mt-sm-0">
                                            <form id="referral-form" action="{{ route('ib-update-referral') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="referral_code" id="hidden-referral-code"
                                                    value="{{ $ib->referral_code }}">
                                                <input type="hidden" name="ib1_id" value="{{ $ib->id }}">
                                                <button type="submit" class="btn btn-primary">
                                                    Submit
                                                </button>
                                            </form>
                                        </div>

                                    </div>

                                </div>
                            </div>
                            <div class="card">
                                <div class="pb-0 card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h4 class="mb-0">Transfer History</h4>
                                        <div class="avtar avtar-s bg-light-primary"><i class="ti ti-list f-18"></i></div>
                                    </div>
                                    <hr style="opacity:.1;">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>TRANSFERRED TO</th>
                                                    <th>PROCESSED ON</th>
                                                    <th>AMOUNT</th>
                                                    <th>STATUS</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pb-0 mt-2 row">
                        <div class="col-12">
                            <div class="card">
                                <div class="pb-0 card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h4 class="mb-0">IB Commission History</h4>
                                        <div class="avtar avtar-s bg-light-primary">
                                            <i class="ti ti-list f-18"></i>
                                        </div>
                                    </div>
                                    <hr style="opacity:.1;">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="commissionTbl">
                                            <thead>
                                                <tr>
                                                    <th>DATETIME</th>
                                                    <th>ACCOUNT</th>
                                                    <th>TYPE</th>
                                                    <th>AMOUNT</th>
                                                    <th>EMAIL</th>
                                                    <th>DATE</th>
                                                    <th>TIME</th>
                                                    <th>ACCOUNT</th>
                                                    <th>AMOUNT</th>
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
                <div class="pt-3 tab-pane" id="ib-connect" role="tabpanel" aria-labelledby="profile-tab-2">
                    <div>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="p-3 card-body">
                                        <ul class="nav nav-pills nav-tabs nav-justified" role="tablist">
                                            <?php for ($i = 1; $i <= 7; $i++) { ?>
                                            <li class="nav-item" data-target-form="#LEVEL{{ $i }}" role="presentation">
                                                <a class="nav-link client-level {{ $i == 1 ? 'active' : '' }}" data-level="{{ $i }}" aria-selected="false" role="tab" tabindex="-1">
                                                    <i class="ti ti-chart-bar me-2"></i>
                                                    <span class="d-none d-sm-inline">
                                                        LEVEL{{ $i }}{{ $i == 1 ? ' (Direct)' : '' }}
                                                    </span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                        </ul>

                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="tab-content connectionTab" id="nav-tabContent">
                                            <?php for ($i = 1; $i <= 7; $i++) { ?>
                                            <div class="tab-pane fade<?= $i == 1 ? ' show active' : '' ?>"
                                                id="LEVEL<?= $i ?>" role="tabpanel">
                                                <div class="datatable-container">
                                                    <table
                                                        class="table table-hover datatable-table ajaxDataTable table-bordered text-nowrap w-100"
                                                        id="ajaxDatatable">
                                                        <thead>
                                                            <tr>
                                                                <th>CLIENT</th>
                                                                <th>TOTAL ACCOUNTS</th>
                                                                <th>TOTAL DEPOSIT</th>
                                                                <th>PROFILE STATUS</th>
                                                                <th>CLIENT NAME</th>
                                                                <th>EMAIL</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
            }).then(() => {
                location.reload();
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Something went wrong',
                text: '{{ session('error') }}',
                showConfirmButton: true
            });
        </script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.11/dist/clipboard.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <script>
        $("[data-bs-target]").click(function () {
            var target = $(this).attr("data-bs-target");
            var targetTab = ".connectionTab .tab-pane" + target;
            console.log(targetTab);
            $(".connectionTab .tab-pane").removeClass("show");
            $(".connectionTab .tab-pane").removeClass("active");
            $(targetTab).addClass("show active");
        });
        var clipboard = new ClipboardJS('.cb');
        clipboard.on('success', function (e) {
            swal.fire({
                icon: "success",
                title: "IB Referral Link Copied"
            });
        });

        // Handle account dropdown selection
        $(document).on('click', '.account-dropdown-item', function (e) {
            e.preventDefault();
            let accountId = $(this).data('account-id');
            let accountCode = $(this).data('account-code');
            let accountBalance = $(this).data('account-balance');

            // Set the hidden radio input
            $('input[name="account"][value="' + accountId + '"]').prop('checked', true);

            // Update dropdown button label
            $('#accountDropdownLabel').html(`
                <span class="d-flex align-items-center">
                    <img src="/assets/images/mt5.png" alt="mt5" class="wid-25 me-2">
                    <span class="fw-medium">${accountCode}</span>
                </span>
            `);
        });

        $(document).ready(function () {
            $("#commissionTbl").DataTable({
                dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
                buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        className: ' btn btn-primary',
                        filename: 'Commission_History_' + new Date().toISOString().slice(0, 10),
                        action: function (e, dt, node, config) {
                            if (!dt.page.info().recordsDisplay) {
                                return Swal.fire({
                                    icon: 'error',
                                    title: 'Export Failed',
                                    text: 'No IB commission history data is available to export.'
                                });
                            }
                            $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, node, config);
                        },
                        exportOptions: {
                            columns: [4, 7, 2, 8, 5, 6]
                        }
                    }
                ],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route('ib.commission-data') }}',
                    type: 'GET',
                    dataSrc: function (json) {
                        return json.data;
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTables error:', error, code);
                    }
                },
                "columns": [
                    {
                        data: 'date', name: 'date'
                    },
                    {
                        data: 'account', name: 'account'
                    },
                    {
                        data: 'type', name: 'type'
                    },
                    {
                        data: 'amount', name: 'amount'
                    },
                    {
                        data: 'email', name: 'email', visible: false
                    },
                    {
                        data: 'exp_date', name: 'exp_date', visible: false
                    },
                    {
                        data: 'time', name: 'time', visible: false
                    },
                    {
                        data: 'exp_account', name: 'exp_account', visible: false
                    },
                    {
                        data: 'exp_amount', name: 'exp_amount', visible: false
                    }

                ],
                "processing": true,
                "order": [[0, "desc"]]
            });
        });


        function updateReferralLink() {
            let referralCode = document.getElementById('referral-code').value;
            let newUrl = "{{ url('/ib-ref?refercode=') }}" + referralCode;
            document.getElementById('pc-clipboard-1').value = newUrl;
            document.getElementById('hidden-referral-code').value = referralCode;
        }

        document.getElementById('generate-btn').addEventListener('click', function () {
            let referralCode = Math.random().toString(36).substring(2, 8).toUpperCase();
            document.getElementById('referral-code').value = referralCode;
            updateReferralLink();
        });

        document.getElementById('referral-code').addEventListener('input', function () {
            updateReferralLink();
        });

        $(document).ready(function () {
            let level = 1;

            var dTtable = $('#ajaxDatatable').DataTable({
                dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
                buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        className: ' btn btn-primary',
                        filename: 'Ib_Clients_' + new Date().toISOString().slice(0, 10),
                        exportOptions: {
                            columns: [4, 5, 1, 2, 3]
                        }
                    }
                ],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: '{{ route('ib.client-profile') }}',
                    type: 'GET',
                    data: function (d) {
                        d.level = level;
                        console.log('Sending data:', d);
                    },
                    dataSrc: function (json) {
                        return json.data;
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTables error:', error, code);
                    }
                },
                columns: [
                    { data: 'email', name: 'email' },
                    { data: 'total_accounts', name: 'total_accounts' },
                    { data: 'total_deposit', name: 'total_deposit' },
                    { data: 'profile_status', name: 'profile_status' },
                    { data: 'client_name', name: 'client_name',visible: false },
                    { data: 'client_email', name: 'client_email', visible: false },
                ],
                order: [[0, "desc"]]
            });

            $('.client-level').on('click', function (e) {
                e.preventDefault();

                $('.client-level').removeClass('active');
                $(this).addClass('active');

                level = $(this).data('level');
                console.log('Selected level:', level);

                dTtable.ajax.reload();
            });
        });
    </script>

@endsection
