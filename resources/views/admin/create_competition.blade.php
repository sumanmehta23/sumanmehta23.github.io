@extends('layouts.admin.admin')
@section('content')
@if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session('success') }}'
        }).then(() => {
            window.location.href = '{{ route('demoAccounts') }}';
        });
    </script>
@endif
@if (session('error'))
    <script>
        Swal.fire({
            icon: 'warning',
            title: "Something Went Wrong !!!!",
            text: '{{ session('error') }}',
        });
    </script>
@endif
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Create Competition Product</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Requested Competition Accounts</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->

            <div class="col-xl-12">
                <div class="card custom-card position-sticky" style="top: 80px;">
                    <div class="card-header">
                        <div class="d-flex justify-content-between w-100">
                            <h4 class="mt-auto mb-auto page-title">Competitions</h4>
                            <button type="button" class="btn btn-primary addGrp">Add New Competition</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tableMT5Groups" class="table ajaxDataTable table-bordered text-nowrap w-100">
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Group Creation Modal -->
    <div class="modal fade" id="groupMgmt" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="groupMgmtLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.competitions.store') }}" id="groupMgmtCreation" class="form-steps" method="post" enctype="multipart/form-data" autocomplete="off">
                    @csrf
                    <input type="hidden" name="ac_index" id="group_id" value="">
                    <input type="hidden" name="groupCreation" value="true">
                    <div class="modal-header">
                        <h5 class="modal-title" id="groupMgmtLabel">Competition Creation Form</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card">
                        <div class="row">
                            {{-- {{ dd($mt5_groups) }} --}}
                            <input type="text" value={{ $mt5_groups->mt5_group_id }} name="ac_type" hidden>
                            {{-- <div class="mb-3 form-group col-lg-3">
                                <label for="ac_type" class="form-label">Group Type</label>
                                <select class="form-control" id="ac_type" name="ac_type" required="">
                                    <option value="" selected disabled></option>
                                    <?php foreach ($mt5_groups as $gp) { ?>
                                    <option value="<?= $gp->mt5_group_id ?>" data-gname="<?= $gp->mt5_group_name ?>" <?php if ($gp->mt5_group_type == 'live') { ?> data-name="<?= $gp->mt5_group_name ?>" <?php } else { ?> data-name="demo\<?= $gp->mt5_group_name ?>" <?php } ?>>
                                        <?= $gp->mt5_group_name ?> -
                                        <?= ucfirst($gp->mt5_group_type) ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div> --}}
                            <div class="mb-3 form-group col-lg-3">
                                <label for="group_name" class="form-label">Competition Name</label>
                                <input type="text" class="form-control" name="ac_name" required="" id="group_name">
                            </div>
                            {{-- <div class="mb-3 form-group col-lg-3">
                                <label for="ac_type" class="form-label">Group Category</label>
                                <select class="form-control" id="ac_type" name="ac_category" required="">
                                    <option selected="" default="" disabled=""></option>
                                    <?php $i = 1;
                                        foreach ($results as $res) {
                                    ?>
                                        <option value="<?= $res->mt5_grp_cat_id ?>" <?= $res->is_active == 0 ? 'disabled' : '' ?>>
                                            <?= $res->mt5_grp_cat_name ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div> --}}
                            {{-- {{ dd($grp_books) }} --}}
                            {{-- <div class="mb-3 form-group col-lg-3">
                                <label for="ac_book_type" class="form-label">Group Book Type</label>
                                <select class="form-control" id="ac_book_type" name="ac_book_type" required="">
                                    <option selected="" default="" disabled=""></option>
                                    <?php $i = 1;
                                        foreach ($grp_books as $res) {
                                    ?>
                                        <option value="<?= $res->mt5_grp_cat_id ?>" <?= $res->is_active == 0 ? 'disabled' : '' ?>>
                                            <?= $res->mt5_grp_cat_name ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div> --}}

                            <div class="mb-3 form-group col-lg-6">
                                <label for="group_name" class="form-label">Group Name</label>
                                <input type="text" class="form-control" name="ac_group" required="" readonly="" id="group_name" value='{{ $competition_group }}'>
                            </div>
                            <div class="mb-3 form-group col-lg-3">
                                <label for="ac_min_deposit" class="form-label">Minimum Deposit</label>
                                <input type="number" class="form-control" id="ac_min_deposit" name="ac_min_deposit" required="">
                            </div>
                            <div class="mb-3 form-group col-lg-3">
                                <label for="ac_max_leverage" class="form-label">Leverages(,)</label>
                                <input type="text" class="form-control" id="ac_max_leverage" name="ac_max_leverage" required="">
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" required="">
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="datetime-local" class="form-control" id="end_date" name="end_date" required="">
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="group_spread" class="form-label">Spread</label>
                                <input type="number" class="form-control" id="group_spread" name="ac_spread" step="0.1" required="">
                            </div>

                            <div class="mb-3 form-group col-lg-4">
                                <label for="ac_swap" class="form-label">Swap</label>
                                <select class="form-control" id="ac_swap" name="ac_swap" required="">
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="is_client_group" class="form-label">Client Group</label>
                                <select class="form-control" id="is_client_group" name="is_client_group" required="">
                                    <option value="1">Shown</option>
                                    <option value="0">Hidden</option>
                                </select>
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="prize_pool" class="form-label">Prize Pool</label>
                                {{-- <input type="text" class="form-control" id="prize_pool" name="prize_pool" required=""> --}}
                                <textarea class="form-control" id="prize_pool" name="prize_pool" rows="4" placeholder="- 1st Place: ...&#10;- 2nd Place: ..."></textarea>
                                <small class="text-muted">Enter each prize on a new line.</small>
                            </div>
                            <div class="mb-3 form-group col-lg-6">
                                <label for="inquiry_status" class="form-label">Inquiry Status</label>
                                <select class="form-control" id="inquiry_status" name="inquiry_status" required="">
                                    <option value="0">Account Creation</option>
                                    <option value="1">Enquiry</option>
                                </select>
                            </div>
                            <div class="mb-3 form-group col-lg-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" required="">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3 form-group col-lg-6">
                                <label for="ib_enabled" class="form-label">IB Enabled</label>
                                <select class="form-control" id="ib_enabled" name="ib_enabled" required="">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="mb-3 form-group col-lg-6">
                                <label for="display_priority" class="form-label">Display Priority</label>
                                <input type="number" class="form-control" id="display_priority" name="display_priority" step="1" required="">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="groupCreation" value="create" class="btn btn-primary ps-">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Group Update Modal -->
    <div class="modal fade" id="groupUpdate" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="groupMgmtLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <form action="#" id="groupUpdateForm" class="form-steps" method="post" enctype="multipart/form-data" autocomplete="off">
                    @csrf
                    <input type="hidden" name="ac_index" id="group_id" value="">
                    <input type="hidden" name="groupUpdation" value="true">
                    <div class="modal-header">
                        <h5 class="modal-title" id="groupUpdateLabel">Group Management</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card">
                        <div class="row">
                            <div class="mb-3 form-group col-lg-6">
                                <label for="group_name" class="form-label">Display Name</label>
                                <input type="text" class="form-control" name="ac_name" required="" id="group_name">
                            </div>
                            <div class="mb-3 form-group col-lg-6">
                                <label for="group_name" class="form-label">Group Name</label>
                                <input type="text" class="form-control" name="ac_group" readonly required="" id="group_name">
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="ac_min_deposit" class="form-label">Minimum Deposit</label>
                                <input type="number" class="form-control" id="ac_min_deposit" name="ac_min_deposit" required="">
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="ac_max_leverage" class="form-label">Leverages(,)</label>
                                <input type="input" class="form-control" id="ac_max_leverage" name="ac_max_leverage" required="">
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="datetime-local" class="form-control" id="start_date" name="competition_start_date" required="">
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="datetime-local" class="form-control" id="end_date" name="competition_end_date" required="">
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="ac_swap" class="form-label">Swap</label>
                                <select class="form-control" id="ac_swap" name="ac_swap" required="">
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="is_client_group" class="form-label">Client Group</label>
                                <select class="form-control" id="is_client_group" name="is_client_group" required="">
                                    <option value="1">Shown</option>
                                    <option value="0">Hidden</option>
                                </select>
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="prize_pool" class="form-label">Prize Pool</label>
                                {{-- <input type="text" class="form-control" id="prize_pool" name="prize_pool" required=""> --}}
                                <textarea class="form-control" id="prize_pool" name="prize_pool" rows="4" placeholder="- 1st Place: ...&#10;- 2nd Place: ..."></textarea>
                                <small class="text-muted">Enter each prize on a new line.</small>
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="inquiry_status" class="form-label">Inquiry Status</label>
                                <select class="form-control" id="inquiry_status" name="inquiry_status" required="">
                                    <option value="0">Account Creation</option>
                                    <option value="1">Enquiry</option>
                                </select>
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" required="">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="ib_enabled" class="form-label">IB Enabled</label>
                                <select class="form-control" id="ib_enabled" name="ib_enabled" required="">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="ib_enabled" class="form-label">IB Enabled</label>
                                <select class="form-control" id="ib_enabled" name="ib_enabled" required="">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="display_priority" class="form-label">Display Priority</label>
                                <input type="number" class="form-control" id="display_priority" name="display_priority" step="1" required="">
                            </div>
                        </div>
                        <!-- <button type="submit" name="groupCreation" value="create" class="btn btn-success">Submit</button> -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="groupCreation" value="create" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection()
@section('scripts')
    <!-- End::app-content -->
    <script>
        $(document).ready(function() {
            // Initialize Bootstrap modals
            window.grpModal = new bootstrap.Modal(document.getElementById('groupMgmt'));
            window.grpUpdateModal = new bootstrap.Modal(document.getElementById('groupUpdate'));

            // Open Create Modal
            $(document).on('click', '.addGrp', function() {
                $('#group_id').val('');
                // Reset form fields if needed
                // $('#groupMgmtCreation')[0].reset();
                grpModal.show();
            });

            // Open Update Modal
            // $(document).on('click', '.grp-action', function() {
            //     var data = dTtable.row($(this).closest('tr')).data();
            //     console.log(data);
            //     // Fill form fields with data
            //     $('#groupUpdateForm input:not([type="hidden"]), #groupUpdateForm select').each(function() {
            //         var name = $(this).attr('name');
            //         $(this).val(data[name] || '').trigger('change');
            //     });
            //     $('#groupUpdateForm [name="ac_index"]').val($(this).data('id'));
            //     grpUpdateModal.show();
            // });
        });

        function dTSelection() {
            // alert("Init");
            $('#tableMT5Groups tbody tr').off();
            $('#tableMT5Groups tbody tr').on('click', '.grp-action', function() {
                var data = dTtable.row($(this).closest("tr")).data();
                console.log(data);
                $("#groupUpdateForm input:not([type='hidden']),#groupUpdateForm select").each(function() {
                    var name = $(this).attr("name");
                    //console.log(name, " ==> ", data[name]);
                    $(this).val(data[name]).trigger("change");
                })

                const now = new Date();
                const startDate = new Date(data['competition_start_date']);
                const endDate = new Date(data['competition_end_date']);

                if (startDate <= now && endDate >= now) {
                    $("#groupUpdateForm #start_date").prop("readonly", true);
                } else {
                    $("#groupUpdateForm #start_date").prop("readonly", false);
                }

                $("#groupUpdateForm [name='ac_index']").val($(this).data("id"));
                grpUpdateModal.show();
            });
        }

        // function getInitials(input) {
        //     if (input) {
        //         const words = input.trim().split(/\s+/); // Split input by spaces
        //         if (words.length === 1) {
        //             return words[0].slice(0, 3).toUpperCase(); // If it's a single word, return the first two letters
        //         } else {
        //             return words.map(word => word[0].toUpperCase()).join(
        //                 ''); // Otherwise, return the first letter of each word
        //         }
        //     }
        //     return input;
        // }

        // function group_namer() {
        //     // var dn = $("[name='ac_name']").val();
        //     var dn = ($("[name='ac_type'] option:selected").data("gname")) ? $("[name='ac_type'] option:selected").data(
        //         "gname").trim() : "";
        //     var type = ($("[name='ac_type'] option:selected").data("name")) ? $("[name='ac_type'] option:selected").data(
        //         "name").trim() : "";
        //     var category = ($("[name='ac_category'] option:selected").text().toUpperCase()) ? $(
        //         "[name='ac_category'] option:selected").text().toUpperCase().trim() : "";
        //     var book = ($("[name='ac_book_type'] option:selected").text().toUpperCase()) ? $(
        //         "[name='ac_book_type'] option:selected").text().toUpperCase().trim() : "";
        //     dn = getInitials(dn);
        //     var gn = type + "\\" + dn + "-" + category + "-" + book + "-USD"
        //     // console.log("GGN", gn);
        //     $("#groupMgmtCreation [name='ac_group']").val(gn);
        // }

        // $("[name='ac_name'],[name='ac_type'],[name='ac_category'],[name='ac_book_type']").change(function() {
        //     group_namer();
        // });


        window.dTtable = $('#tableMT5Groups').on("draw.dt", dTSelection).DataTable({
            // order: [[0, "desc"]],
            "ajax": {
                "url": "/admin/ajax",
                "type": "GET",
                data: {
                    action: 'getCompetitionGroups',
                    type: '<?= $activeType ?>',
                    group: '<?= $activeGroup ?>'
                },
            },
            order: [],
            columns: [{
                    data: 'ac_name',
                    title: 'Name'
                },
                {
                    data: 'display_priority',
                    title: 'Order Pri.'
                },
                {
                    data: 'competition_start_date',
                    title: 'Start Date',
                },
                {
                    data: 'competition_end_date',
                    title: 'End Date',
                },
                {
                    data: 'total_participants',
                    title: 'Participants',
                },
                {
                    data: 'prize',
                    title: 'Prize',
                },
                {
                    data: 'leaderboard',
                    title: 'View Leaderboard',
                },
                {
                    data: 'ac_group',
                    title: 'Group'
                },
                {
                    data: 'ac_min_deposit',
                    title: 'Min.Deposit'
                },
                {
                    data: 'ac_spread',
                    title: 'Spread'
                },
                {
                    data: 'acc_status',
                    title: 'Status'
                },
                {
                    data: 'is_client_group',
                    title: 'Client Shown',
                    render: function(data) {
                        if (data == 1) {
                            return '<span class="badge bg-outline-success">Shown</span>';
                        } else {
                            return '<span class="badge bg-outline-danger">Hidden</span>';
                        }
                    }
                },
                {
                    data: 'enc_id',
                    title: 'Action',
                    render: function(data) {
                        var btn =
                            '<button class="btn btn-primary grp-action" data-id="' + data + '"><i class="fa fa-ellipsis-h"></i></button>';
                        return btn;
                    },
                    orderable: false,
                    searchable: false
                }
            ]
        });


        $("#groupCreation, #groupUpdateForm").submit(function(e) {
            e.preventDefault();

            var form = $(this);
            var id = form.find("[name='ac_index']").val();
            var url = id ? `/admin/competitions/${id}` : `/admin/competitions`;
            var method = id ? "POST" : "POST"; // still POST; Laravel expects _method=PUT for updates

            $.ajax({
                url: url,
                type: method,
                data: form.serialize() + (id ? '&_method=PUT' : ''),
                dataType: 'json',
                beforeSend: function() {
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                },
                success: function(res) {
                    console.log(res);
                    // console.log('abhay');
                    if (res === true || res.success === true) {
                        Swal.fire({
                            icon: 'success',
                            title: id ? 'Group Updated Successfully' : 'Group Created Successfully'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message || 'Something went wrong.'
                        });
                    }
                },
                error: function(xhr) {
                    console.log(xhr);
                    console.log(xhr.responseText);
                    // console.log('abhay');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong.'
                    });
                }
            });
        });
    </script>
@endsection()
