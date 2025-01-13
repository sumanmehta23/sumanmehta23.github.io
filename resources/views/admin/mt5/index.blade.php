@extends('layouts.admin.admin')
@section('styles')
    <style>
        .active .category-icon {
            color: white;
        }

        .active .category-name {
            color: white;
            font-weight: bold;
        }
    </style>
@endsection
@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">MT5 Groups</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">MT5 Groups</li>
                </ol>
            </div>

            <div class="row">

                <div class="col-xl-4">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <h4 class="mt-auto mb-auto page-title">Group Mains</h4>
                            <button class="btn btn-primary addGrpMain">
                                <i class="fa fa-plus"></i>
                                Add Main Group
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <ul class="list-group" id="tableMT5Group">
                                    <?php $i = 1;
                foreach ($mt5_groups as $res) { ?>
                                    <li class="list-group-item <?= $activeGroup == ($res->mt5_group_id) ? 'active' : '' ?>"
                                        aria-current="true">
                                        <a class="d-flex justify-content-between"
                                            href="/admin/mt5_groups?activeGroup=<?= ($res->mt5_group_id) ?>">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <span class="fs-15">
                                                        <?php if (($res->mt5_group_id) == $activeGroup) { ?>
                                                        <i class="bi category-icon bi-toggle2-on"></i>
                                                        <?php } else { ?>
                                                        <i class="bi category-icon bi-toggle2-off"></i>
                                                        <?php } ?>
                                                    </span>
                                                </div>
                                                <div class="ms-2 category-name">
                                                    <?= $res->mt5_group_name ?>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-end">
                                                <span class="m-auto badge badge-primary bg-outline-primary me-2">
                                                    <?= $res->mt5_group_type ?>
                                                </span>
                                                <?php if ($res->is_active == 0) { ?>
                                                <span class="m-auto border badge bg-light custom-badge d-flex text-default"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Disabled Group Category"><i
                                                        class="d-inline-block fe fe-eye-off me-2"></i>
                                                    <!-- <div class="m-auto"><$res->count ?></div> -->
                                                </span>
                                                <!-- <div class="m-auto bg-gray-600 badge">Inactive</div> -->
                                                <?php } else { ?>
                                                <span
                                                    class="m-auto border badge bg-light custom-badge d-flex text-default"><i
                                                        class="d-inline-block fe fe-eye me-2"></i>
                                                    <!-- <div class="m-auto"><$res->count ?></div> -->
                                                </span>
                                                <!-- <div class="m-auto badge bg-success">Active</div> -->
                                                <?php } ?>
                                                <button class="btn mains-edit" data-id="<?= ($res->mt5_group_id) ?>"><i
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
                            <a href="{{ url()->current() }}" class="btn btn-outline-primary btn-sm">Show All</a>
                        </div>
                    </div>

                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <h4 class="mt-auto mb-auto page-title">Group Categories</h4>
                            <button class="btn btn-primary addGrpCat">
                                <i class="fa fa-plus"></i>
                                Add Group Category
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <ul class="list-group" id="tableMT5Category">
                                    <?php $i = 1;
                foreach ($results as $res) { ?>
                                    <li class="list-group-item <?= $activeType == ($res->mt5_grp_cat_id) ? 'active' : '' ?>"
                                        aria-current="true">
                                        <a class="d-flex justify-content-between"
                                            href="/admin/mt5_groups?activeType=<?= ($res->mt5_grp_cat_id) ?>">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <span class="fs-15">
                                                        <?php if (($res->mt5_grp_cat_id) == $activeType) { ?>
                                                        <i class="bi category-icon bi-toggle2-on"></i>
                                                        <?php } else { ?>
                                                        <i class="bi category-icon bi-toggle2-off"></i>
                                                        <?php } ?>
                                                    </span>
                                                </div>
                                                <div class="ms-2 category-name">
                                                    <?= $res->mt5_grp_cat_name ?>
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
                                                <!-- <div class="m-auto badge bg-success">Active</div> -->
                                                <?php } ?>
                                                <button class="btn category-edit"
                                                    data-id="<?= ($res->mt5_grp_cat_id) ?>"><i
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
                            <a href="{{ url()->current() }}" class="btn btn-outline-primary btn-sm">Show All</a>
                        </div>
                    </div>

                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <h4 class="mt-auto mb-auto page-title">Group Type</h4>
                            <button class="btn btn-primary addGrpType">
                                <i class="fa fa-plus"></i>
                                Add Group Type
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <ul class="list-group" id="tableMT5Category">
                                    <?php $i = 1;
                foreach ($grp_books as $res) { ?>
                                    <li class="list-group-item d-flex justify-content-between <?= $activeType == ($res->mt5_grp_cat_id) ? 'active' : '' ?>"
                                        aria-current="true">
                                        <!-- <a href="/admin/mt5_groups?activeType=<?= ($res->mt5_grp_cat_id) ?>"> -->
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <span class="fs-15">
                                                    <?php if (($res->mt5_grp_cat_id) == $activeType) { ?>
                                                    <i class="bi category-icon bi-toggle2-on"></i>
                                                    <?php } else { ?>
                                                    <i class="bi category-icon bi-toggle2-off"></i>
                                                    <?php } ?>
                                                </span>
                                            </div>
                                            <div class="ms-2 category-name">
                                                <?= $res->mt5_grp_cat_name ?>
                                                <?= $res->mt5_grp_cat_desc ? ' - ' . $res->mt5_grp_cat_desc : '' ?>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-end">
                                            <?php if ($res->is_active == 0) { ?>
                                            <span class="m-auto border badge bg-light custom-badge d-flex text-default"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Disabled Group Category"><i
                                                    class="d-inline-block fe fe-eye-off"></i>
                                            </span>
                                            <!-- <div class="m-auto bg-gray-600 badge">Inactive</div> -->
                                            <?php } else { ?>
                                            <span class="m-auto border badge bg-light custom-badge d-flex text-default"><i
                                                    class="d-inline-block fe fe-eye"></i>
                                            </span>
                                            <!-- <div class="m-auto badge bg-success">Active</div> -->
                                            <?php } ?>
                                            <button class="btn category-edit"
                                                data-id="<?= ($res->mt5_grp_cat_id) ?>"><i
                                                    class="fa fa-edit"></i></button>
                                        </div>
                                        <!-- </a> -->
                                    </li>
                                    <?php $i++;
                } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-xl-8">
                    <div class="card custom-card position-sticky" style="top: 80px;">
                        <div class="card-header">
                            <div class="d-flex justify-content-between w-100">
                                <h4 class="mt-auto mb-auto page-title">Groups</h4>
                                <button type="button" class="btn btn-primary addGrp">Add New Group</button>
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
    </div>


    <!-- Modal -->
    <div class="modal fade" id="groupCat" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="ibModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="#" id="groupCatForm" method="post">
                    @csrf
                    <input type="hidden" name="id" id="groupCatId" value="">
                    <input type="hidden" name="group_update" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ibModalLabel">Group Category / Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card">
                        <div class="card-body">
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">Name Of Category / Type</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="mt5_grp_cat_name" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">Description</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="mt5_grp_cat_desc">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">Category Type</label>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-select" required name="mt5_grp_cat_type">
                                        <option value="" selected disabled></option>
                                        <option value="type">Category</option>
                                        <option value="book">Type</option>
                                    </select>
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
                        <button type="submit" name="groupUpdate" value="update" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Grp Modal -->
    <div class="modal fade" id="grpMainModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="ibModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="#" id="groupMainForm" method="post">
                    @csrf
                    <input type="hidden" name="id" id="groupMainId" value="">
                    <input type="hidden" name="groupMain_update" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ibModalLabel">Group Mains</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card">
                        <div class="card-body">
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">Name Of Group Main</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="mt5_group_name" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">Description</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control" name="mt5_group_desc">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">Type</label>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-select" required name="mt5_group_type">
                                        <option value="" selected disabled></option>
                                        <option value="live">Live</option>
                                        <option value="demo">Demo</option>
                                    </select>
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
                        <button type="submit" name="groupUpdate" value="update" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Group Creation Modal -->
    <div class="modal fade" id="groupMgmt" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="groupMgmtLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="#" id="groupMgmtCreation" class="form-steps" method="post" enctype="multipart/form-data" autocomplete="off">
                    @csrf
                    <input type="hidden" name="ac_index" id="group_id" value="">
                    <input type="hidden" name="groupCreation" value="true">
                    <div class="modal-header">
                        <h5 class="modal-title" id="groupMgmtLabel">Group Creation Form</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card">
                        <div class="row">
                            <div class="mb-3 form-group col-lg-3">
                                <label for="ac_type" class="form-label">Group Type</label>
                                <select class="form-control" id="ac_type" name="ac_type" required="">
                                    <option value="" selected disabled></option>
                                    <?php foreach ($mt5_groups as $gp) { ?>
                                    <option value="<?= $gp->mt5_group_id ?>" data-gname="<?= $gp->mt5_group_name ?>"
                                        <?php if ($gp->mt5_group_type == 'live') { ?> data-name="<?= $gp->mt5_group_name ?>" <?php } else { ?>
                                        data-name="demo\<?= $gp->mt5_group_name ?>" <?php } ?>>
                                        <?= $gp->mt5_group_name ?> -
                                        <?= ucfirst($gp->mt5_group_type) ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3 form-group col-lg-3">
                                <label for="group_name" class="form-label">Display Name</label>
                                <input type="text" class="form-control" name="ac_name" required=""
                                    id="group_name">
                            </div>
                            <div class="mb-3 form-group col-lg-3">
                                <label for="ac_type" class="form-label">Group Category</label>
                                <select class="form-control" id="ac_type" name="ac_category" required="">
                                    <option selected="" default="" disabled=""></option>
                                    <?php $i = 1;
                foreach ($results as $res) { ?>
                                    <option value="<?= $res->mt5_grp_cat_id ?>"
                                        <?= $res->is_active == 0 ? 'disabled' : '' ?>>
                                        <?= $res->mt5_grp_cat_name ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3 form-group col-lg-3">
                                <label for="ac_book_type" class="form-label">Group Book Type</label>
                                <select class="form-control" id="ac_book_type" name="ac_book_type" required="">
                                    <option selected="" default="" disabled=""></option>
                                    <?php $i = 1;
                foreach ($grp_books as $res) { ?>
                                    <option value="<?= $res->mt5_grp_cat_id ?>"
                                        <?= $res->is_active == 0 ? 'disabled' : '' ?>>
                                        <?= $res->mt5_grp_cat_name ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="mb-3 form-group col-lg-6">
                                <label for="group_name" class="form-label">Group Name</label>
                                <input type="text" class="form-control" name="ac_group" required=""
                                    readonly="" id="group_name">
                            </div>
                            <div class="mb-3 form-group col-lg-3">
                                <label for="ac_min_deposit" class="form-label">Minimum Deposit</label>
                                <input type="number" class="form-control" id="ac_min_deposit" name="ac_min_deposit"
                                    required="">
                            </div>
                            <!-- <div class="mb-3 form-group col-lg-4">
                      <label for="group_max_deposit" class="form-label">Maximum Deposit</label>
                      <input type="number" class="form-control" id="group_max_deposit" name="ac_max_deposit" required="">
                    </div> -->
                            <div class="mb-3 form-group col-lg-3">
                                <label for="ac_max_leverage" class="form-label">Leverages(,)</label>
                                <input type="text" class="form-control" id="ac_max_leverage" name="ac_max_leverage"
                                    required="">
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="group_spread" class="form-label">Spread</label>
                                <input type="number" class="form-control" id="group_spread" name="ac_spread"
                                    step="0.1" required="">
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
                                <input type="number" class="form-control" id="display_priority" name="display_priority"
                                    step="1" required="">
                            </div>
                        </div>
                        <!-- <button type="submit" name="groupCreation" value="create" class="btn btn-success">Submit</button> -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="groupCreation" value="create"
                            class="btn btn-primary ps-">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Group Update Modal -->
    <div class="modal fade" id="groupUpdate" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="groupMgmtLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
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
                                <input type="text" class="form-control" name="ac_name" required=""
                                    id="group_name">
                            </div>
                            <div class="mb-3 form-group col-lg-6">
                                <label for="group_name" class="form-label">Group Name</label>
                                <input type="text" class="form-control" name="ac_group" readonly required=""
                                    id="group_name">
                            </div>
                            <div class="mb-3 form-group col-lg-4">
                                <label for="ac_min_deposit" class="form-label">Minimum Deposit</label>
                                <input type="number" class="form-control" id="ac_min_deposit" name="ac_min_deposit"
                                    required="">
                            </div>
                            <!-- <div class="mb-3 form-group col-lg-4">
                      <label for="group_max_deposit" class="form-label">Maximum Deposit</label>
                      <input type="number" class="form-control" id="group_max_deposit" name="ac_max_deposit" required="">
                    </div> -->
                            <div class="mb-3 form-group col-lg-4">
                                <label for="ac_max_leverage" class="form-label">Leverages(,)</label>
                                <input type="input" class="form-control" id="ac_max_leverage" name="ac_max_leverage"
                                    required="">
                            </div>

                            <!--
                    <div class="mb-3 form-group col-lg-4">
                      <label for="group_spread" class="form-label">Spread</label>
                      <input type="number" class="form-control" id="group_spread" name="ac_spread" required="">
                    </div>
                    <div class="mb-3 form-group col-lg-4">
                      <label for="group_commission" class="form-label">Commission</label>
                      <input type="number" class="form-control" id="group_commission" name="ib_commission1" required="">
                    </div> -->
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
                                <input type="number" class="form-control" id="display_priority" name="display_priority"
                                    step="1" required="">
                            </div>
                        </div>
                        <!-- <button type="submit" name="groupCreation" value="create" class="btn btn-success">Submit</button> -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="groupCreation" value="create"
                            class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <!-- End::app-content -->
    <script>
        $(document).ready(function() {
            window.myModal = new bootstrap.Modal(document.getElementById('groupCat'));
            window.grpMainModal = new bootstrap.Modal(document.getElementById('grpMainModal'));
            window.grpModal = new bootstrap.Modal(document.getElementById('groupMgmt'));
            window.grpUpdateModal = new bootstrap.Modal(document.getElementById('groupUpdate'));
            $(".addGrp").click(function() {
                $("#group_id").val("");
                grpModal.show();
            });
            // $('#user_group_id').select2({ dropdownParent: $('#grpMainModal') });
            // $('#cat_user_group_id').select2({ dropdownParent: $('#groupCat') });
        });

        // $("#ibModal").modal();
        function dTSelection() {
            // alert("Init");
            $('#tableMT5Groups tbody tr').off();
            $('#tableMT5Groups tbody tr').on('click', '.grp-action', function() {
                var data = dTtable.row($(this).closest("tr")).data();
                $("#groupUpdateForm input:not([type='hidden']),#groupUpdateForm select").each(function() {
                    var name = $(this).attr("name");
                    console.log(name, " ==> ", data[name]);
                    $(this).val(data[name]).trigger("change");
                })
                $("#groupUpdateForm [name='ac_index']").val($(this).data("id"));
                grpUpdateModal.show();
            });
        }


        window.dTtable = $('#tableMT5Groups').on("draw.dt", dTSelection).DataTable({
            // order: [[0, "desc"]],
            "ajax": {
                "url": "/admin/ajax",
                "type": "GET",
                data: {
                    action: 'getMT5Groups',
                    type: '<?= $activeType ?>',
                    group: '<?= $activeGroup ?>'
                },
            },
            order: [],
            columns: [{
                    data: 'ac_name',
                    title: 'DP Name'
                },
                {
                    data: 'display_priority',
                    title: 'Order Pri.'
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
                // {
                //   data: 'ib_commission1',
                //   title: 'commission'
                // },
                // {
                //   data: 'ac_type',
                //   title: 'Type'
                // },
                // {
                //   data: 'ib_status',
                //   title: 'IB Status'
                // },
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
                            '<button class="btn btn-primary grp-action" data-id="'+data+'"><i class="fa fa-ellipsis-h"></i></button>';
                        return btn;
                    }
                }
            ]
        });

        $(".category-edit").click(function(e) {
            e.preventDefault();
            var id = $(this).data("id");
            $.ajax({
                url: "/admin/api/ajax",
                type: "get",
                data: "get_groupcat=true&id=" + id,
                success: function(data) {
                    if (data == "fasle") {
                        swal.fire({
                            icon: "error",
                            title: "Something went wrong",
                            text: "Please try again later or contact support.",
                        });
                    } else {
                        try {
                            data = JSON.parse(data);
                        }
                        catch(err){
                            data = data;
                        }
                        console.log("DATA ==> ",data);
                        // let user_group_id = JSON.parse(data.user_group_id);
                        $("#groupCat #groupCatId").val(id);
                        $("#groupCat [name='mt5_grp_cat_type']").val(data.mt5_grp_cat_type).attr(
                            "disabled", "true");
                        $("#groupCat [name='mt5_grp_cat_name']").val(data.mt5_grp_cat_name);
                        $("#groupCat [name='mt5_grp_cat_desc']").val(data.mt5_grp_cat_desc);
                        $("#groupCat [name='is_active']").val(data.is_active).trigger("change");
                        // $("#groupCat [name='user_group_id']").val(user_group_id).trigger("change");
                        myModal.show();

                    }
                }
            });
        });

        $(".mains-edit").click(function(e) {
            e.preventDefault();
            var id = $(this).data("id");
            $.ajax({
                url: "/admin/api/ajax",
                type: "get",
                data: "get_groupMains=true&id=" + id,
                success: function(data) {
                    if (data == "fasle") {
                        swal.fire({
                            icon: "error",
                            title: "Something went wrong",
                            text: "Please try again later or contact support.",
                        });
                    } else {
                        try {
                            data = JSON.parse(data);
                        }
                        catch(err){
                            data = data;
                        }
                        // let user_group_id = JSON.parse(data.user_group_id);
                        $("#groupMainForm #groupMainId").val(id);
                        $("#groupMainForm [name='mt5_group_name']").val(data.mt5_group_name);
                        $("#groupMainForm [name='mt5_group_desc']").val(data.mt5_group_desc);
                        $("#groupMainForm [name='mt5_group_type']").val(data.mt5_group_type).attr(
                            "disabled", "true");
                        $("#groupMainForm [name='is_active']").val(data.is_active).trigger("change");
                        // $("#groupMainForm [name='user_group_id']").val(user_group_id).trigger("change");
                        grpMainModal.show();

                    }
                }
            });
        });

        $("#groupCatForm").submit(function(e) {
            e.preventDefault();
            $("#groupCat [name='mt5_grp_cat_type']").removeAttr("disabled");
            $.ajax({
                url: "/admin/api/ajax",
                type: "POST",
                data: $(this).serialize(),
                success: function(data) {
                    $("#groupCat [name='mt5_grp_cat_type']").val("type").attr("disabled", "true");
                    if (data == "true") {
                        swal.fire({
                            icon: "success",
                            title: "Group Category Successfully Updated"
                        }).then((val) => {
                            location.reload();
                        });
                    }
                }
            });
        });

        $("#groupMainForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "/admin/api/ajax",
                type: "POST",
                data: $(this).serialize(),
                success: function(data) {
                    if (data == "true") {
                        swal.fire({
                            icon: "success",
                            title: "Group Mains Successfully Updated"
                        }).then((val) => {
                            location.reload();
                        });
                    }
                }
            });
        });

        $("#groupMgmtCreation,#groupUpdateForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "/admin/api/ajax",
                type: "POST",
                data: $(this).serialize(),
                beforeSend: function() {
                    swal.fire({
                        showConfirmButton: false,
                        showCancelButton: false,
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: function() {
                            swal.enableLoading();
                        }
                    });
                },
                success: function(data) {

                    if (data == "true" || data.trim() == "true") {
                        swal.fire({
                            icon: "success",
                            title: "Group Successfully Updated"
                        }).then((val) => {
                            location.reload();
                        });
                    } else {
                        swal.fire({
                            icon: "error",
                            title: "Error:",
                            text: data
                        })
                    }
                }
            });
        });

        $(".addGrpCat").click(function(e) {
            e.preventDefault();
            $("#groupCat input:not([name='_token']),#groupCat select").val("").trigger("change");
            $("#groupCat [name='mt5_grp_cat_type']").val("type").attr("disabled", "true");
            myModal.show();
        })

        $(".addGrpMain").click(function(e) {
            e.preventDefault();
            $("#groupCat input:not([name='_token']),#groupCat select").val("").trigger("change");
            $("#groupCat [name='mt5_grp_cat_type']").val("type").attr("disabled", "true");
            $("#groupMainForm [name='mt5_group_desc']").removeAttr("disabled");
            $('#groupMainForm')[0].reset();
            grpMainModal.show();
        })

        $(".addGrpType").click(function(e) {
            e.preventDefault();
            $("#groupCat input:not([name='_token']),#groupCat select").val("").trigger("change");
            $("#groupCat [name='mt5_grp_cat_type']").val("book").attr("disabled", "true");
            myModal.show();
        })

        function getInitials(input) {
            if (input) {
                const words = input.trim().split(/\s+/); // Split input by spaces
                if (words.length === 1) {
                    return words[0].slice(0, 3).toUpperCase(); // If it's a single word, return the first two letters
                } else {
                    return words.map(word => word[0].toUpperCase()).join(
                        ''); // Otherwise, return the first letter of each word
                }
            }
            return input;
        }

        function group_namer() {
            // var dn = $("[name='ac_name']").val();
            var dn = ($("[name='ac_type'] option:selected").data("gname")) ? $("[name='ac_type'] option:selected").data(
                "gname").trim() : "";
            var type = ($("[name='ac_type'] option:selected").data("name")) ? $("[name='ac_type'] option:selected").data(
                "name").trim() : "";
            var category = ($("[name='ac_category'] option:selected").text().toUpperCase()) ? $(
                "[name='ac_category'] option:selected").text().toUpperCase().trim() : "";
            var book = ($("[name='ac_book_type'] option:selected").text().toUpperCase()) ? $(
                "[name='ac_book_type'] option:selected").text().toUpperCase().trim() : "";
            dn = getInitials(dn);
            var gn = type + "\\" + dn + "-" + category + "-" + book + "-USD"
            // console.log("GGN", gn);
            $("#groupMgmtCreation [name='ac_group']").val(gn);
        }


        $("[name='ac_name'],[name='ac_type'],[name='ac_category'],[name='ac_book_type']").change(function() {
            group_namer();
        });
    </script>
@endsection
