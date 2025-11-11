@extends('layouts.admin.admin')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">Client List</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item">Cliensst List</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->


            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        @if(!empty($accounts) && count($accounts) > 0)
                            <div class="card-header">
                                <div class="card-title">
                                    Listed Count : {{ count($accounts) }}
                                </div>
                            </div>
                        @endif

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="ajaxDatatable" class="table ajaxDataTable table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>Joined On</th>
                                            <th>Name/Email</th>
                                            <th>Phone</th>
                                            <th>Country</th>
                                            <th>Parent IB</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                    foreach ($accounts as $result) {
                                    ?>
                                        <tr>
                                            {{-- {{ dd($accounts) }} --}}
                                            <td>
                                                <?php $createdAt = Carbon\Carbon::parse($result->created_at)->addHours(3);
                                                echo "<div class='d-grid'>
                                                        <div class='date'>{$createdAt->format('Y-m-d')}</div>
                                                        <div class='time text-muted'>{$createdAt->format('H:i:s')}</div>
                                                    </div>"; ?>
                                            </td>
                                            <td>
                                                <a href='/admin/client_details/<?= $result->id ?>'>
                                                    <div class='d-flex align-items-center'>
                                                        <div class='me-2'><svg xmlns='http://www.w3.org/2000/svg'
                                                                width='28' height='28' viewBox='0 0 24 24'
                                                                fill='none' stroke='#000000' stroke-width='1.5'
                                                                stroke-linecap='round' stroke-linejoin='round'
                                                                size='28' color='#000000'
                                                                class='tabler-icon tabler-icon-user-square-rounded'>
                                                                <path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path>
                                                                <path
                                                                    d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'>
                                                                </path>
                                                                <path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'>
                                                                </path>
                                                            </svg></div>
                                                        <div>
                                                            <div class='lh-1'><span><?= ucfirst($result->fullname) ?></span>
                                                            </div>
                                                            <div class='lh-1'><span
                                                                    class='fs-11 text-muted'><?= $result->email ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </td>
                                            <td>
                                                <?php echo htmlentities($result->number); ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $countryAlpha = isset($result->countryDetail) && isset($result->countryDetail->country_alpha)
                                                        ? strtolower($result->countryDetail->country_alpha)
                                                        : '';

                                                    $countryAlphaDisplay = isset($result->countryDetail) && isset($result->countryDetail->country_alpha)
                                                        ? $result->countryDetail->country_alpha
                                                        : '';
                                                    echo "<span class='fi fis fi-{$countryAlpha}'></span> {$countryAlphaDisplay}";
                                                    ?>

                                            </td>
                                            <td><?php $ib_name = $result->getParentIb() ? $result->getParentIb()->fullname : 'noIB';
                                                $ib_email  =$result->getParentIb() ? $result->getParentIb()->email : '';
                                                $svg = $ib_name !== 'noIB' ? "<div class='me-2'>
                                                            <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='icon icon-tabler icons-tabler-outline icon-tabler-user-pentagon text-dark'>
                                                                <path stroke='none' d='M0 0h24v24H0z' fill='none'></path>
                                                                <path d='M13.163 2.168l8.021 5.828c.694 .504 .984 1.397 .719 2.212l-3.064 9.43a1.978 1.978 0 0 1 -1.881 1.367h-9.916a1.978 1.978 0 0 1 -1.881 -1.367l-3.064 -9.43a1.978 1.978 0 0 1 .719 -2.212l8.021 -5.828a1.978 1.978 0 0 1 2.326 0z'></path>
                                                                <path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path>
                                                                <path d='M6 20.703v-.703a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.707'></path>
                                                            </svg>
                                                        </div>" : '';
                                                echo "<div class='cursor-pointer updateIb d-flex align-items-center'>
                                                        <div class='me-2'>
                                                        {$svg}
                                                        </div>
                                                        <div>
                                                            <div class='lh-1'><span>{$ib_name}</span></div>
                                                            <div class='lh-1'><span class='fs-11 text-muted'>{$ib_email}</span></div>
                                                        </div>
                                                    </div>"; ?>
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
            $('.ajaxDataTable tbody tr').on('click', '.ibToggle', function() {
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
