@extends('layouts.admin.admin')
@section('noDatatable', true)
@section('styles')
    <style>
        th,
        td {
            white-space: nowrap;
        }

        div.dataTables_wrapper {
            /* width: 800px; */
            margin: 0 auto;
        }

        .DTFC_LeftBodyWrapper,
        .DTFC_LeftBodyWrapper .DTFC_LeftBodyLiner {
            overflow-y: unset !important;
            width: max-content !important;
        }

        table.ajaxDataTable.DTFC_Cloned tr th:after,
        table.ajaxDataTable.DTFC_Cloned tr th:before {
            display: none;
        }

        .active .category-icon {
            color: white;
        }

        .active .category-name {
            color: white;
            font-weight: bold;
        }

        table.ajaxDataTable.table.table-bordered.stripe.row-border.order-column.text-nowrap.w-100.dataTable.no-footer.DTFC_Cloned {
            margin-top: 0 !important;
        }
    </style>
@endsection
@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">IB Commission Settings</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item" aria-current="page">IB Settings</li>
                    <li class="breadcrumb-item active" aria-current="page">IB Commission Settings</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-xl-4 col-lg-4">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <h4 class="mt-auto mb-auto page-title">IB Plans</h4>
                            <button class="btn btn-primary addGrpCat">
                                <i class="fa fa-plus"></i>
                                Add IB Plan
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <ul class="list-group" id="tableMT5Category">
                                    <?php $i = 1;
                foreach ($results as $res) { ?>
                                    <li class="list-group-item <?= $activeType == ($res->id) ? 'active' : '' ?>"
                                        aria-current="true">
                                        <a class="d-flex justify-content-between"
                                            href="/admin/ib_settings?activeType=<?= ($res->id) ?>">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <span class="fs-15">
                                                        <?php if (($res->id) == $activeType) { ?>
                                                        <i class="bi category-icon bi-toggle2-on"></i>
                                                        <?php } else { ?>
                                                        <i class="bi category-icon bi-toggle2-off"></i>
                                                        <?php } ?>
                                                    </span>
                                                </div>
                                                <div class="ms-2 category-name">
                                                    <?= $res->ib_cat_name ?>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-end">
                                                <?php if ($res->is_active == 0) { ?>
                                                <span class="m-auto border badge bg-light custom-badge d-flex text-default"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Disabled Group Category"><i
                                                        class="d-inline-block fe fe-eye-off me-2"></i>
                                                    <div class="m-auto"><?= $res->count ?></div>
                                                </span>
                                                <!-- <div class="m-auto bg-gray-600 badge">Inactive</div> -->
                                                <?php } else { ?>
                                                <span
                                                    class="m-auto border badge bg-light custom-badge d-flex text-default"><i
                                                        class="d-inline-block fe fe-eye me-2"></i>
                                                    <div class="m-auto"><?= $res->count ?></div>
                                                </span>
                                                <?php } ?>
                                                <button class="btn category-edit" data-id="<?= ($res->id) ?>"><i
                                                        class="fa fa-edit category-icon"></i></button>
                                            </div>
                                        </a>
                                    </li>
                                    <?php $i++;
                } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="/admin/ib_settings" class="btn btn-outline-primary btn-sm">Show All</a>
                        </div>
                    </div>

                </div>
                <div class="col-xl-8 col-lg-4">
                    <div class="card custom-card position-sticky" style="top: 80px;">
                        <div class="card-header">
                            <div class="d-flex justify-content-between w-100">
                                <h4 class="mt-auto mb-auto page-title">IB Commissions</h4>
                                <a href="/admin/ibCommission">
                                    <button class="btn btn-primary">
                                        <i class="fa fa-plus"></i>
                                        Add IB Commission
                                    </button>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tableMT5Groups"
                                    class="table ajaxDataTable table-bordered stripe row-border order-column text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Group</th>
                                            <th>Plan</th>
                                            <?php for ($i = 1; $i <= 3; $i++) { ?>
                                            <?php for ($ii = 1; $ii <= $i; $ii++) { ?>
                                            <th>L<?= $i ?>|D<?= $ii ?></th>
                                            <?php } ?>
                                            <?php } ?>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>@foreach ($plans as $plan)
                                            <tr>
                                                
                                                @if(!empty($plan))
                                                    <td>{{ $plan->accountType->ac_group }}</td>
                                                    <td>{{ $plan->plan->ib_cat_name }}</td>
                                                    @for ($i = 1; $i <= 3; $i++)
                                                        @php
                                                            // Fetch the details for the current level
                                                            $data = DB::table('ib_plan_details')
                                                                ->where('ib_category_id', $plan->ib_category_id)
                                                                ->where('account_type_id', $plan->account_type_id)
                                                                ->where('level_id', $i)
                                                                ->whereNull('deleted_at')
                                                                ->first();
                                                        @endphp
                                                        @for ($ii = 1; $ii <= $i; $ii++)
                                                            @php
                                                                $d = 'd' . $ii;
                                                            @endphp
                                                            <td>{{ $data->$d ?? '-' }}</td>
                                                        @endfor
                                                    @endfor
                                                    <td>
                                                        <button class="btn btn-primary actions"
                                                            data-href="{{ url('/admin/ibCommissionEdit', [
                                                                'planId' => ($plan->ib_category_id),
                                                                'accType' => ($plan->account_type_id)]) }}">
                                                            <i class="ti ti-edit"></i>
                                                        </button>
                                                    </td>
                                                @endif
                                                
                                            </tr>
                                            @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="groupCat" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="ibModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="#" id="groupCatForm" method="post" class="mb-0" enctype="multipart/form">
                    @csrf
                    <input type="hidden" name="id" id="groupCatId" value="">
                    <input type="hidden" name="ib_plan_update" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ibModalLabel">IB Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="pb-0 mb-0 modal-body custom-card card">
                        <div class="card-body">
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">Name Of the Plan</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="ib_cat_name" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">Description</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="ib_cat_desc">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">Status</label>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-select" required name="is_active">
                                        <option value="" selected disabled></option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="groupUpdate" value="update" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="https://cdn.datatables.net/s/ju/dt-1.10.10,b-1.1.0,fc-3.2.0,fh-3.1.0,r-2.0.0,sc-1.4.0/datatables.min.js">
    </script>
    <link href="https://cdn.datatables.net/s/ju/dt-1.10.10,b-1.1.0,fc-3.2.0,r-2.0.0,sc-1.4.0/datatables.min.css"
        rel="stylesheet">
    <link href="https://cdn.datatables.net/fixedcolumns/3.2.0/css/fixedColumns.dataTables.min.css" rel="stylesheet">
    <!-- End::app-content -->
    <script>
        $(".ibCommission input").focus(function() {
            $(this)[0].select();
        });

        $(document).ready(function() {
            window.myModal = new bootstrap.Modal(document.getElementById('groupCat'));
            window.grpModal = new bootstrap.Modal(document.getElementById('groupMgmt'));

        });

        // $("#ibModal").modal();
        function dTSelection() {
            // alert("Init");
            $('.ajaxDataTable tbody tr').off();
            $('.ajaxDataTable tbody tr').on('click', '.actions', function() {
                // var data = dTtable.row($(this).closest("tr")).data();
                location.href = $(this).data("href");
            });
        }


        window.dTtable = $('#tableMT5Groups').on("draw.dt", dTSelection).DataTable({
            fixedColumns: {
                leftColumns: 1,
                // rightColumns: 2
            },
            scrollX: true,
        });

        $(".category-edit").click(function(e) {
            e.preventDefault();
            var id = $(this).data("id");
            $.ajax({
                url: "/admin/api/ajax",
                type: "get",
                data: "get_ibplan=true&id=" + id,
                success: function(response) {
                    if (response == "fasle") {
                        swal.fire({
                            icon: "error",
                            title: "Something went wrong",
                            text: "Please try again later or contact support.",
                        });
                    } else {
                        console.log(typeof response);
                        if(typeof response != "object") {
                            const cleanResponse = response.trim();
                            const data = JSON.parse(cleanResponse);
                        }else{
                            const data = response;
                        }

                        // console.log(response);
                        $("#groupCat #groupCatId").val(response.id);
                        $("#groupCat [name='ib_cat_name']").val(response.ib_cat_name);
                        $("#groupCat [name='ib_cat_desc']").val(response.ib_cat_desc);
                        $("#groupCat [name='is_active']").val(response.is_active).trigger("change");
                        // $("#groupCat [name='user_group_id']").val(data.group_id).trigger("change");
                        myModal.show();

                    }
                }
            });
        });

        $("#groupCatForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "/admin/api/ajax",
                type: "POST",
                data: $(this).serialize(),
                success: function(data) {
                    if (data == "true") {
                        swal.fire({
                            icon: "success",
                            title: "IP Plan Successfully Updated"
                        }).then((val) => {
                            location.reload();
                        });
                    }
                }
            });
        });

        $("#ibPlanMgmt").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "/admin/api/ajax",
                type: "POST",
                data: $(this).serialize(),
                success: function(data) {
                    if (data == "true") {
                        swal.fire({
                            icon: "success",
                            title: "IB Plan Successfully Updated"
                        }).then((val) => {
                            location.reload();
                        });
                    }
                }
            });
        });

        $(".addGrpCat").click(function(e) {
            e.preventDefault();
            $("#groupCat input:not([name='_token']),#groupCat select").val("").trigger("change");
            myModal.show();
        })
        $(".addGrp").click(function(e) {
            e.preventDefault();
            $("#groupCat input:not([name='_token']),#groupCat select").val("").trigger("change");
            grpModal.show();
        })
    </script>
@endsection
