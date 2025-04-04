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
    <!-- Include Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

@endsection

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">IB Commission Settings</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item">IB</li>
                    <li class="breadcrumb-item active">IB Com. Settings</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->

            <!-- FORM START -->
            <div class="row">
                <div class="col-lg-12">
                    <form method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <!-- IB Plan -->
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label class="form-label">IB Plan</label>
                                            <select name="ib_category_id" class="form-control" required>
                                                <option value="" default selected disabled>--Select Plan--</option>
                                                <?php foreach ($ibCategories as $res) { ?>
                                                    <option value="<?= $res->id ?>"><?= $res->ib_cat_name ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Group (Multi-Select) -->
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label class="form-label">Group</label>
                                            <select name="acc_type[]" class="form-control select2" multiple required>
                                                <?php foreach ($accountTypes as $res) { ?>
                                                    <option value="<?= $res->id ?>" <?= $res->status == 0 || $res->ib_enabled == 0 ? 'disabled' : '' ?>>
                                                        <?= $res->ac_group ? $res->ac_group : $res->ac_name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-control" required>
                                                <option value="1">Active</option>
                                                <option value="0">In-Active</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Levels -->
                                <div class="levels">
                                    <?php for ($i = 1; $i <= 1; $i++) { ?>
                                        <section data-level="<?= $i ?>" class="level-cards <?= $i > 1 ? 'd-none' : '' ?>">
                                            <div class="card">
                                                <h5 class="card-header">Level <?= $i ?>:
                                                    <div class="h5 mt-2 ps-4 total text-primary"></div>
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
                                                                           value="0.00">
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    <?php } ?>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="card-footer text-end">
                                <input type="submit" class="btn btn-primary" value="Create IB Commission">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2 for the multi-select dropdown
            $('.select2').select2({
                placeholder: "--Select Group--",
                allowClear: true
            });

            // Auto-select input text when focused
            $("input[type='number']").focus(function() {
                $(this)[0].select();
            });

            // Function to calculate total commission
            function total() {
                $(".level-cards").each(function() {
                    var total = 0;
                    $(this).find('.commis').each(function() {
                        total += parseFloat($(this).val()) || 0;
                    });
                    $(this).find(".total").html(total.toFixed(2));
                });
            }

            // Initial calculation
            total();

            // Update total on input change
            $(".level-cards input").change(function() {
                total();
            });
        });
    </script>
@endsection
