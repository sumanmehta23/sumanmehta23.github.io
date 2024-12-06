@extends('layouts.admin.admin')
@section('styles')
    <style>
        .level-cards .card {
            transition-duration: 500ms;
        }

        .level-cards .card:hover {
            box-shadow: var(--bs-box-shadow) !important;
            transition-duration: 500ms;
        }
    </style>

    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">IB Commission Settings</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item" aria-current="page">IB</li>
                    <li class="breadcrumb-item active" aria-current="page">IB Com., Settings</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->
            <!-- ROW-1 OPEN -->
            <div class="row">
                <div class="col-lg-12">
                    <form method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label class="form-label">IB Plan</label>
                                            <select name="ib_plan_id" class="form-control" required="required">
                                                <option value="" default selected disabled>--Select Plan--</option>
                                                <?php foreach ($ibCategories as $res) { ?>
                                                <option value="<?= $res->id ?>"><?= $res->ib_cat_name ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label class="form-label">Group</label>
                                            <select name="acc_type" class="form-control" required="required">
                                                <option value="" default selected disabled>--Select Group--</option>
                                                <?php $i = 1;
                      foreach ($accountTypes as $res) { ?>
                                                <option value="<?= $res->id ?>"
                                                    <?= $res->status == 0 || $res->ib_enabled == 0 ? 'disabled' : '' ?>>
                                                    <?= $res->ac_group ? $res->ac_group : $res->ac_name ?></option>
                                                <?php  } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-control" required="required">
                                                <option value="1">Active</option>
                                                <option value="0">In-Active</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="levels">
                                    <?php for ($i = 1; $i <= 1; $i++) { ?>
                                    <section data-level="<?= $i ?>" class="level-cards <?= $i > 1 ? 'd-none' : '' ?>">
                                        <div class="card">
                                            <h5 class="card-header">Level <?= $i ?>: <div
                                                    class="h5 mt-2 ps-4 total text-primary"></div>
                                            </h5>
                                            <div class="card-body pb-0">
                                                <div class="row">
                                                    <?php for ($ii = 1; $ii <= $i; $ii++) { ?>
                                                    <div class="col-xl-2 col-lg-3 col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label for="" class="h5">D<?= $ii ?></label>
                                                            <input type="number" step="0.01" min="0"
                                                                class="form-control commis"
                                                                name="level[<?= $i ?>][d<?= $ii ?>]" required
                                                                <?= $i > 1 ? 'disabled' : '' ?> value="0.00"
                                                                id="" aria-describedby="helpId" placeholder="">
                                                        </div>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="card-footer actions text-end p-0">
                                              
                                            </div>
                                        </div>
                                    </section>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <input type="submit" class="btn btn-primary" value="Create IB Commission" name="action">
                            </div>
                        </div>
                    </form>
                </div>


            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $("input[type='number']").focus(function(e) {
            $(this)[0].select();
        });

       

        function total() {
            $(".level-cards").each(function() {
                var total = 0;
                $(this).find('.commis').each(function(e) {
                    total = total + parseFloat($(this).val());
                });

                $(this).find(".total").html(total.toFixed(2));
            })
        }
        total();
        $(".level-cards input").change(function() {
            total();
        })
    </script>
@endsection