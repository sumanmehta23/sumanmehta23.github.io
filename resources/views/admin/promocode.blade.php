@extends('layouts.admin.admin')
@section('content')
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
</style>
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- PAGE-HEADER -->
        <div class="page-header">
            <h1 class="page-title">Promocode</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Promocode</li>
            </ol>
        </div>
        <!-- PAGE-HEADER END -->

        <!-- ROW-1 OPEN -->

        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="mt-auto mb-auto page-title">Codes</h4>
                        <button class="btn btn-primary addPromo">
                            <i class="fa fa-plus"></i>
                            Add New Promocode
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablePromocodes" class="table ajaxDataTable table-bordered text-nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Promo Code</th>
                                        <th>Percentage</th>
                                        <th>Max Deposit</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
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

        <div class="modal fade" id="promoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="ibModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="#" id="promocodeForm" method="post">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="ibModalLabel">Add Promocode</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="mb-0 modal-body custom-card card">
                            <div class="card-body">
                                <div class="mb-3 row">
                                    <div class="m-auto col-lg-4">
                                        <label class="form-label">Code</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text" class="form-control" name="promo_code"  placeholder="Enter unique code" required>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <div class="m-auto col-lg-4">
                                        <label class="form-label">Percentage</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="number" class="form-control" name="promo_percentage" min="0" max="100" step="0.01" required>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <div class="m-auto col-lg-4">
                                        <label class="form-label">Max Deposit</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="number" class="form-control" name="max_deposit" min="0" max="1000000000000" step="0.01" required>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <div class="m-auto col-lg-4">
                                        <label class="form-label">Status</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <select class="form-select" required name="promo_status">
                                            <option value="" selected disabled>Please select</option>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="promoCreate" value="create" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editPromoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="promocodeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="#" id="editPromocodeForm" method="post">
                        @csrf
                        <input type="text" class="form-control" name="id" hidden>
                        <div class="modal-header">
                            <h5 class="modal-title" id="promocodeModalLabel">Edit Promocode</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="mb-0 modal-body custom-card card">
                            <div class="card-body">
                                <div class="mb-3 row">
                                    <div class="m-auto col-lg-4">
                                        <label class="form-label">Code</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text" class="form-control" name="promo_code"  placeholder="Enter unique code" required>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <div class="m-auto col-lg-4">
                                        <label class="form-label">Percentage</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="number" class="form-control" name="promo_percentage" min="0" max="100" step="0.01" required>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <div class="m-auto col-lg-4">
                                        <label class="form-label">Max Deposit</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="number" class="form-control" name="max_deposit" min="0" max="1000000000000" step="0.01" required>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <div class="m-auto col-lg-4">
                                        <label class="form-label">Status</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <select class="form-select" required name="promo_status">
                                            <option value="" selected disabled>Please select</option>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="promoUpdate" value="update" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        window.promoModal = new bootstrap.Modal(document.getElementById('promoModal'));
        window.editPromoModal = new bootstrap.Modal(document.getElementById('editPromoModal'));

        var TablePromocode = $('#tablePromocodes').DataTable({
            dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
            buttons: [
                {
                    extend: 'excel',
                    text: 'Export to Excel',
                    exportOptions: {
                        columns: [0,1,2,3,4,5] // Fixed index to match table
                    }
                }
            ],
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            pageLength: 10,
            order: [[3, "desc"]],
            processing: true,
            serverSide: true,
            searching: true,
            ajax: {
                url: '/admin/getPromocodes', // Ensure it matches the Laravel route
                type: 'GET',
                data: function(d) {
                    d.status = $('select[name=status]').val();
                    return d;
                },
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'code', name: 'code' },
                { data: 'percentage', name: 'percentage' },
                { data: 'max_deposit', name: 'max_deposit' },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action' }
            ]
        });
    });

    $(".addPromo").click(function(e) {
        e.preventDefault();
        promoModal.show();
    })

    $("#promocodeForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: "/admin/create/promocode",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Promocode Added",
                        text: response.message
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = "Something went wrong.";

                if (errors) {
                    errorMessage = Object.values(errors).flat().join("\n");
                }

                Swal.fire({
                    icon: "error",
                    title: "Validation Error",
                    text: errorMessage
                });
            }
        });
    });

    $(document).on('change', '.statusToggle', function() {
        let promoId = $(this).data('id');
        let status = $(this).prop('checked') ? 1 : 0;

        $.ajax({
            url: '/admin/update_promocode_status', // Define your route in web.php
            type: 'POST',
            data: {
                id: promoId,
                status: status,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire(
                    'Updated!',
                    'Promocode status has been updated.',
                    'success'
                );
            },
            error: function(error) {
                Swal.fire(
                    'Error!',
                    'Something went wrong, please try again.',
                    'error'
                );
            }
        });
    });

    $(document).on('click', '.deletePromocode', function() {
        let promoId = $(this).data('id');
        console.log(promoId);

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete promocode!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/admin/delete_promocode', // Define your route in web.php
                    type: 'POST',
                    data: {
                        id: promoId,
                        status: status,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire(
                            'Updated!',
                            'Promocode successfully deleted.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(error) {
                        Swal.fire(
                            'Error!',
                            'Something went wrong, please try again.',
                            'error'
                        );
                    }
                });
            } else {
                // Revert toggle switch if canceled
                $(this).prop('checked', !status);
            }
        });
    });

    $(document).on('click', '.editPromocode', function() {
        let promoId = $(this).data('id');

        $.ajax({
            url: `/admin/get_promocode/${promoId}`,
            type: 'POST',
            success: function(response) {
                if (response.success) {
                    $('#editPromocodeForm input[name="promo_code"]').val(response.data.code);
                    $('#editPromocodeForm input[name="promo_percentage"]').val(response.data.percentage);
                    $('#editPromocodeForm input[name="max_deposit"]').val(response.data.max_deposit);
                    $('#editPromocodeForm select[name="promo_status"]').val(response.data.status);
                    $('#editPromocodeForm input[name="id"]').val(response.data.id);
                    editPromoModal.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to fetch promocode details. Please try again.'
                });
            }
        });
    });


    $("#editPromocodeForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: "/admin/edit/promocode",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Promocode Added",
                        text: response.message
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = "Something went wrong.";

                if (errors) {
                    errorMessage = Object.values(errors).flat().join("\n");
                }

                Swal.fire({
                    icon: "error",
                    title: "Validation Error",
                    text: errorMessage
                });
            }
        });
    });

</script>
@endSection
