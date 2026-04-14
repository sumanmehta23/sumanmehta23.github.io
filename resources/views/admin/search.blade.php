@php
    use Carbon\Carbon;
@endphp
@extends('layouts.admin.admin')
@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">{{$type}}</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item">Client List</li>
                    <li class="breadcrumb-item active" aria-current="page">Live Accounts</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->


            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-none">
                            <div class="card-title">
                                Listed Count : <?= count($accounts) ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="ajaxDatatable" class="table ajaxDataTable table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <td>Client</td>
                                            <td>Trade ID</td>
                                            <td>Leverage</td>
                                            <td>Balance</td>
                                            <td>Registered Date</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
    foreach ($accounts as $result) {
        // print_r($result);
        // exit();
                                            ?>
                                        <tr>
                                            <td>
                                                <a href='/admin/client_details/<?= $result->enc_id ?>'>
                                                    <div class='d-flex align-items-center'>
                                                        <div class='me-2'><svg xmlns='http://www.w3.org/2000/svg' width='28'
                                                                height='28' viewBox='0 0 24 24' fill='none' stroke='#000000'
                                                                stroke-width='1.5' stroke-linecap='round'
                                                                stroke-linejoin='round' size='28' color='#000000'
                                                                class='tabler-icon tabler-icon-user-square-rounded'>
                                                                <path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path>
                                                                <path
                                                                    d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'>
                                                                </path>
                                                                <path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'>
                                                                </path>
                                                            </svg></div>
                                                        <div>
                                                            <div class='lh-1'><span><?= ucfirst($result->name) ?></span>
                                                            </div>
                                                            <div class='lh-1'><span
                                                                    class='fs-11 text-muted'><?= $result->email ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{url('/admin/view_account_details', $result->id)}}">
                                                    <div class="row align-items-center">
                                                        <div class="col-auto pe-0">
                                                            <?php
                                                            $platformImg = $result->platform === App\Enums\PlatformEnum::X9->value ? '/assets/images/x9.png' : '/assets/images/mt5.png';
                                                            $platformAlt = $result->platform === App\Enums\PlatformEnum::X9->value ? 'X9 Platform' : 'MT5 Platform';
                                                        ?>
                                                        <img src="<?= $platformImg ?>" class="w-32 h-32 rounded" style="width:24px" alt="<?= $platformAlt ?>"
                                                            >
                                                            {{-- <img src="/assets/images/mt5.png"
                                                                alt="user-image" class="rounded wid-50 hei-50"> --}}
                                                            </div>
                                                        <div class="col ps-2">
                                                            <h6 class="mb-0"><span
                                                                    class="text-truncate w-100"><?= $result->code ?></span>
                                                            </h6>
                                                            <p class="mb-0 text-muted f-12"><span
                                                                    class="text-truncate w-100"><?= $result->ac_group ?></span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </td>
                                            <td><?php    echo htmlentities($result->leverage); ?></td>
                                            <td><?php    echo htmlentities($result->balance); ?></td>
                                            <td>
                                                <div class="lh-1">
                                                    {{--
                                                    <?= date('Y-m-d', strtotime($result->created_at)) ?> --}}
                                                    {{ Carbon::parse($result->created_at)->addHours(3)->format('Y-m-d') }}
                                                </div>
                                                <div class="lh-2 text-muted">
                                                    {{--
                                                    <?= date('H:i:s', strtotime($result->created_at)) ?> --}}
                                                    {{ Carbon::parse($result->created_at)->addHours(3)->format('H:i:s') }}
                                                </div>
                                            </td>
                                        </tr>
                                        <?php }
                                            ?>
                                        </tr>
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
@section('scripts')
    <!-- End::app-content -->
    <script>
        // $("#ibModal").modal();
        function dTSelection() {
            // alert("Init");
            $('.ajaxDataTable tbody tr').off();
            $('.ajaxDataTable tbody tr').on('click', '.ibToggle', function () {
                var data = dTtable.row($(this).closest("tr")).data();
                // console.log(data);
                $("#clientName,#clientEmail").html("");
                $("#clientName").html(data.fullname)
                $("#clientEmail").html(data.email)
                $("#client_id").val(data.enc)
                $("[name='ib_status']").val(data.ib_status).trigger("change");
                $("[name='ib_group']").val(data.ib_group).trigger("change");
                myModal.show();
                // swal.fire({
                //   icon: "info",
                //   title: "IB Status ==> " + data.ib_status
                // });

            });
        }

        window.dTtable = $('.ajaxDataTable').on("draw.dt", dTSelection).DataTable();
    </script>
@endsection