@extends('layouts.admin.admin')
@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <?php

    ?>
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
        .editClient{
            cursor: pointer;
        }
    </style>

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Competition Leaderboard</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Leaderboard</li>
                </ol>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Listed Count :
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="leaderboardDatatable" class="table leaderboardDatatable table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Name/Email</th>
                                            <th>Balance</th>
                                            <th>Equity</th>
                                            <th>Competition</th>
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



    <!-- End::app-content -->

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var dTtable = $('#leaderboardDatatable').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: '/admin/leaderboard',
                    type: 'GET',
                    data: {},
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                columns: [

                    {
                        data: 'user_email',
                        name: 'fullname'
                    },
                    {
                        data: 'fullemail',
                        name: 'email',
                        visible: false,
                        render: function (data, row, row_data) {
                            return row_data.email;
                        }
                    },
                    {
                        data: 'ibemail',
                        name: 'ibemail',
                        visible: false,
                        render: function (data, row, row_data) {
                            let ib_email = row_data.ib;
                            return ib_email;
                        },
                        searchable: false
                    },
                    {
                        data: 'ibname',
                        name: 'ibname',
                        visible: false,
                        render: function (data, row, row_data) {
                            let ib_name = row_data.ib_name;
                            return ib_name;
                        },
                        searchable: false
                    },
                ],
                "initComplete": function() {
                    var needs = [2];
                    this.api()
                        .columns()
                        .every(function(index) {
                            if (needs.indexOf(index) == -1) {
                                return false;
                            }
                            let column = this;
                            let title = column.header().textContent;
                            let input = document.createElement('input');
                            input.placeholder = title;
                            column.header().replaceChildren(input);

                            input.addEventListener('keyup', () => {
                                if (column.search() !== this.value) {
                                    column.search(input.value).draw();
                                }
                            });
                        });
                },
                "rowCallback": function(row, data) {

                },

                "drawCallback": function(settings) {

                },
                order: [
                    [0, "desc"]
                ],
                lengthChange: true,
                pageLength: 10,
                lengthMenu: [ [10, 25, 50, 100, 500, 1000], [10, 25, 50, 100, 500, 1000] ],
                dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
                buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        filename: 'Client_List_' + new Date().toISOString().slice(0, 10),
                        exportOptions: {
                            columns: [0,8,9,10,2,3] // Exclude the `Name/Email` column (index 2)
                        }
                    },
                    {
                        text: 'Export All',
                        action: function () {
                            window.location.href = "/admin/export-all-clients";
                        }
                    }
                ],
            })

            dTtable.on('draw', function() {

                $('.ajaxDataTable tbody').off('click', '.updateIb');
                $('.ajaxDataTable tbody').on('click', '.updateIb', function() {
                    var data = dTtable.row($(this).closest("tr")).data();
                    $(".clientName,.clientEmail,.client_id").html("");
                    $(".clientName").html(data.fullname);
                    $(".clientEmail").html(data.email);
                    $(".client_id").val(data.id);
                    $('#ibUpdateForm select').each(function() {
                        this.selectedIndex = 0;
                    });
                    $.ajax({
                        url: "/admin/ajax",
                        type: "GET",
                        cache: false,
                        data: {
                            "action": "getIbList",
                            "id": data.id
                        },
                        success: function(response) {
                            // var ibValues = JSON.parse(response);
                            $('.ib-select').val(null).trigger('change');
                            $.each(response, function(key, value) {
                                if ((value != "" && value != null) || key ==
                                    'ib1') {
                                    if (value == 'noIB') {
                                        value = '';
                                    }
                                    $('#ibUpdateForm select[name="' + key +
                                        '"]').prop(
                                        'disabled',
                                        false);
                                    $('#ibUpdateForm select[name="' + key +
                                            '"]').val(value)
                                        .trigger('change');
                                }
                            })
                        }
                    });
                    updateIbModal.show();
                });

                $('.ajaxDataTable tbody tr').off('click', '.ibToggle');
                $('.ajaxDataTable tbody tr').on('click', '.ibToggle', function() {
                    var data = dTtable.row($(this).closest("tr")).data();
                    $("#clientName,#clientEmail").html("");
                    $("#clientName").html(data.fullname)
                    $("#clientEmail").html(data.email)
                    $("#client_id").val(data.id)
                    $("[name='ib_status']").val(data.ib_status).trigger("change");
                    $("[name='ib_group']").val(data.ib_group).trigger("change");
                    myModal.show();
                    // swal.fire({
                    //   icon: "info",
                    //   title: "IB Status ==> " + data.ib_status
                    // });

                });
                $('.ajaxDataTable tbody').off('click', '.editClient')
                $('.ajaxDataTable tbody').on('click', '.editClient', function() {
                    var data = dTtable.row($(this).closest("tr")).data();
                    $.ajax({
                        url: "/admin/ajax",
                        type: "GET",
                        cache: false,
                        data: {
                            "action": "getClientDetails",
                            "id": data.id
                        },
                        success: function(resp) {

                            $.each(resp, function(key, value) {

                                if (key === 'country_code') {
                                    value = value.replace('', '+');
                                }
                                if (key === 'telephone') {
                                    value = value.replace('+', '');
                                }
                                console.log(key, value);
                                $('#editUserForm [name="' + key + '"]').val(
                                    value);
                            });
                            $('#editUserForm [name="country_code"]').trigger('change');
                        }
                    });
                    editUserModal.show();
                });
                $('.ajaxDataTable tbody').off('click', '.switchClient');
                $('.ajaxDataTable tbody').on('click', '.switchClient', function(e) {
                    e.preventDefault(); // Prevent default behavior
                    var clientData = dTtable.row($(this).closest("tr")).data();
                    var admin_user = {
                        id: "{{ auth()->user()->id }}", // Assuming you want the user's ID or other necessary details from the PHP session
                        name: "{{ auth()->user()->username }}"
                    };

                    $.ajax({
                        url: "/admin/getClientSwitch", // Ensure this matches your backend route
                        type: "POST",
                        contentType: "application/json",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Include CSRF token in the header
                        },
                        data: JSON.stringify({
                            action: "getClientSwitch",
                            client_id: clientData.id, // Pass the correct client ID
                            admin_user: admin_user
                        }),
                        success: function(resp) {
                            if (resp.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: resp.message,
                                }).then(() => {
                                    // Redirect using the URL from the server
                                    window.location.href = resp.redirectUrl;
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || "Can't switch user. Please try again.",
                            });
                        }
                    });
                });




                $('.ajaxDataTable tbody').off('click', '.statusToggle');
                $('.ajaxDataTable tbody').on('click', '.statusToggle', function() {
                    var data = dTtable.row($(this).closest("tr")).data();
                    $("#userName,#userEmail").html("");
                    $("#userName").html(data.fullname);
                    $("#userEmail").html(data.email);
                    $("#user_id").val(data.id);
                    $("#user_status").prop("checked", data.status == 1);
                    $("#email_status").prop("checked", data.email_confirmed == 1);
                    $("#kyc_verify").prop("checked", (data.kyc_verify == 1));
                    statusModal.show();
                });
                $('.ajaxDataTable tbody').off('click', '.resendVerificationEmail');
                $('.ajaxDataTable tbody').on('click', '.resendVerificationEmail', function() {
                    var data = dTtable.row($(this).closest("tr")).data();
                    $("#userName,#userEmail").html("");
                    $("#userName").html(data.fullname);
                    $("#userEmail").html(data.email);
                    $("#user_id").val(data.id);
                    $("#user_status").prop("checked", data.status == 1);
                    $("#email_status").prop("checked", data.email_confirmed == 1);
                    $("#kyc_verify").prop("checked", (data.kyc_verify == 1));
                    Swal.fire({
                        title: "Are you sure?",
                        text: "An account email confirmation email will be resent to the user.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, resend it!"
                        }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "/admin/ajax",
                                type: "POST",
                                cache: false,
                                data: {
                                    action: 'resendVerificationEmail',
                                    id: data.id
                                },
                                success: function(response) {
                                    if (response.success == true) {
                                        swal.fire({
                                            icon: "success",
                                            title: "Verification email Successfully Sent",
                                        }).then((val) => {
                                            // location.reload();
                                        });
                                    } else {
                                        swal.fire({
                                            icon: "error",
                                            title: "Something went wrong.",
                                            text: "Please try again or contact support."
                                        }).then((val) => {
                                            // location.reload();
                                        });
                                    }
                                }
                            });
                            // Swal.fire({
                            // title: "Deleted!",
                            // text: "Your file has been deleted.",
                            // icon: "success"
                            // });
                        }
                        });
                });

                $('.ajaxDataTable tbody tr').off('click', '.rmToggle');
                $('.ajaxDataTable tbody tr').on('click', '.rmToggle', function() {
                    var data = dTtable.row($(this).closest("tr")).data();
                    $("#customerName,#customerEmail").html("");
                    $("#customerName").html(data.fullname);
                    $("#customerEmail").html(data.email);
                    $("#customer_id").val(data.id);

                    $.ajax({
                        url: "/admin/ajax",
                        type: "GET",
                        data: {
                            action: 'getRMbyGroup',
                            "id": data.id
                        },
                        success: function(response) {
                            var userGroupIds;
                            if (typeof response === 'string') {
                                try {
                                    userGroupIds = JSON.parse(response);
                                } catch (e) {
                                    console.error("Failed to parse JSON:", e);
                                    return; // Exit if parsing fails
                                }
                            } else {
                                userGroupIds = response;
                            }
                            var defaultOption = $('<option></option>').val('').text(
                                '--Select--').attr(
                                'selected', 'selected');
                            $('#group_rm_list').html(defaultOption);
                            $.each(userGroupIds, function(index, option) {
                                var $option = $('<option></option>').val(option
                                    .id).text(
                                    option
                                    .username);
                                if (option.id === data.rm_id) {
                                    $option.attr('selected', 'selected');
                                }
                                $('#group_rm_list').append($option);
                            });
                        }
                    });

                    rmModal.show();
                });
                $('.ajaxDataTable tbody tr').on('click', '.viewClient', function() {
                    var data = dTtable.row($(this).closest("tr")).data();
                    location.href = "/admin/client_details/" + data.id;
                });
            });

            $("#statusUpdateForm22").submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: "/admin/ajax",
                    type: "POST",
                    cache: false,
                    data: $("#statusUpdateForm").serialize(),
                    success: function(response) {

                        if (response.success == true) {
                            swal.fire({
                                icon: "success",
                                title: "Status Successfully Updated",
                            }).then((val) => {
                                location.reload();
                            });
                        } else {
                            swal.fire({
                                icon: "error",
                                title: "Something went wrong.",
                                text: "Please try again or contact support."
                            }).then((val) => {
                                location.reload();
                            });
                        }
                    }
                });
            });
            $("#statusUpdateForm").submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: "/admin/ajax",
                    type: "POST",
                    cache: false,
                    data: $("#statusUpdateForm").serialize(),
                    success: function(response) {

                        if (response.success == true) {
                            swal.fire({
                                icon: "success",
                                title: "Status Successfully Updated",
                            }).then((val) => {
                                location.reload();
                            });
                        } else {
                            swal.fire({
                                icon: "error",
                                title: "Something went wrong.",
                                text: "Please try again or contact support."
                            }).then((val) => {
                                location.reload();
                            });
                        }
                    }
                });
            });
        });
    </script>
@endsection
