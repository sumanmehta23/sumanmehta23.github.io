@php
    use Carbon\Carbon;
@endphp
@extends('layouts.admin.admin')
@section('content')
    @include('admin.mt5.popups')
    <style>
        .pointer {
            cursor: pointer;
        }

        .export-all-btn-header {
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid #dc3545;
            color: #dc3545;
            background-color: transparent;
            font-weight: 500;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
        }

        .export-all-btn-header:hover {
            background-color: #dc3545;
            color: #fff;
            border-color: #dc3545;
        }
    </style>
    <?php


// include __DIR__ . "/user_actions.php";
// include "admin_transaction.php";


if ($getUser) {
?>
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">{{ $title ?? 'Details of Trade Account' }}</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $title ?? 'Account Details' }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-5 col-xl-4">
                    <div class="card custom-card">
                        <div class="card-body">
                            <!-- <h6 class="card-title fw-medium">DEPOSIT TICKET #$details->id ?></h6> -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="text-center">
                                                <div class="userprofile">
                                                    <div class="avatar userpic avatar-rounded pointer"
                                                        onmousedown="handleClick(event, '{{ route('admin.admin-view-client-details', $getUser->user_id) }}')">
                                                        <img src="/admin_assets/assets/images/users/client.jpeg"
                                                            alt="img" style="width:100px">
                                                    </div>

                                                    <h3 class="mb-2 username pointer"
                                                        onmousedown="handleClick(event, '{{ route('admin.admin-view-client-details', $getUser->user_id) }}')">
                                                        <?= $getUser->name ?>
                                                    </h3>

                                                    <p class="mb-1 text-muted pointer"
                                                        onmousedown="handleClick(event, '{{ route('admin.admin-view-client-details', $getUser->user_id) }}')">
                                                        <?= $getUser->email ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div
                                                class="wideget-user-desc d-flex align-items-center justify-content-between">
                                                <div class="wideget-user-desc d-flex align-items-center">
                                                    <div class="wideget-user-img">
                                                        <?php
                                                            $platformImg = $account->platform === App\Enums\PlatformEnum::X9->value ? '/assets/images/x9.png' : '/assets/images/mt5.png';
                                                            $platformAlt = $account->platform === App\Enums\PlatformEnum::X9->value ? 'X9 Platform' : 'MT5 Platform';
                                                        ?>
                                                        <img src="<?= $platformImg ?>" class="me-3"
                                                            alt="<?= $platformAlt ?>" style="width:50px">
                                                    </div>
                                                    <div class="mt-auto mb-auto user-wrap">
                                                        <h4 class="mb-0 fw-bold"><?= $getUser->code ?></h4>
                                                        <h6 class="fs-12 fw-normal text-muted">
                                                            <?php
                                                                if ($account->platform === App\Enums\PlatformEnum::X9->value) {
                                                                    echo $x9_group_name ?? $getUser->accountType->ac_name ?? 'Standard';
                                                                } else {
                                                                    echo $getUser->accountType->ac_group;
                                                                }
                                                            ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                                @can('account:update')
                                                    <div class="mb-2 col-6" style="padding-left: 12px">
                                                        @if ($account->deleted_at && $account->deletion_type == 'soft')
                                                            <span class="badge btn btn-success" data-bs-toggle="modal"
                                                                data-bs-target="#accountRestoreModal">Restore Account
                                                                <i class="ti ti-database-import"></i>
                                                            </span>
                                                        @elseif($account->deleted_at && $account->deletion_type == 'archive')
                                                            <span class="badge btn btn-success" data-bs-toggle="modal"
                                                                data-bs-target="#accountRestoreModal">Restore Account
                                                                <i class="ti ti-database-import"></i>
                                                            </span>
                                                        @elseif($account->deleted_at && $account->deletion_type == null)
                                                            <label class="mt-1 fs-18 text-danger fw-bold" for="">Deleted</label>
                                                        @elseif($account->deleted_at && ($account->deletion_type == 'delete' || $account->deletion_type == 'not_found_in_mt5'))
                                                            <label class="mt-1 fs-18 text-danger fw-bold" for="">Deleted</label>
                                                        @elseif($account->deleted_at == null)
                                                            <div class="gap-4 flexflex-vertical">
                                                                <span class="badge btn btn-danger" data-bs-toggle="modal"
                                                                    data-bs-target="#accountSoftDeleteModal">Soft Delete Account
                                                                    <i class="ti ti-database-import"></i>
                                                                </span>
                                                                @if ($account->demo == 0)
                                                                    @if ($account->platform === App\Enums\PlatformEnum::MT5->value && $account->deletion_type != 'archive')
                                                                        <span class="mt-3 badge btn btn-warning" data-bs-toggle="modal"
                                                                        data-bs-target="#accountArchiveModal">Archive Account
                                                                            <i class="ti ti-archive"></i>
                                                                        </span>
                                                                    @else
                                                                        <span class="mt-3 badge btn btn-danger"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#accountDeleteModal">Delete Account
                                                                            <i class="ti ti-database-import"></i>
                                                                        </span>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endcan
                                            </div>
                                            @if (!$account->deleted_at)
                                                <div class="mt-3 row justify-content-center">
                                                    @if ($account->demo == 0)
                                                        @can('trade_deposit:create')
                                                            @if (!$account->isZapierAccount())
                                                                <div class="mb-2 col-6">
                                                                    <span class="badge btn btn-primary" data-bs-toggle="modal"
                                                                        data-bs-target="#depositModal">Deposit
                                                                        <i class="ti ti-database-import"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        @endcan
                                                        @can('trade_withdrawals:create')
                                                            <div class="mb-2 col-6">
                                                                <span class="badge btn btn-info" data-bs-toggle="modal"
                                                                    data-bs-target="#withdrawalModal">Withdraw
                                                                    <i class="ti ti-square-rounded-arrow-down"></i>
                                                                </span>
                                                            </div>
                                                        @endcan
                                                        @can('trade_deposit:create')
                                                            @if (!$account->isZapierAccount())
                                                                <div class="mb-2 col-6">
                                                                    <span class="badge btn btn-primary" data-bs-toggle="modal"
                                                                        data-bs-target="#depositModalCellExp">Deposit Tracking
                                                                        <i class="ti ti-database-import"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        @endcan
                                                        @can('trade_withdrawals:create')
                                                            <div class="mb-2 col-6">
                                                                <span class="badge btn btn-info" data-bs-toggle="modal"
                                                                    data-bs-target="#withdrawalModalCellExp">Withdraw Tracking
                                                                    <i class="ti ti-square-rounded-arrow-down"></i>
                                                                </span>
                                                            </div>
                                                        @endcan
                                                        @can('bonus_transaction:create')
                                                            @if (!$account->isZapierAccount())
                                                                <div class="mb-2 col-6">
                                                                    <span class="badge btn btn-secondary" data-bs-toggle="modal"
                                                                        data-bs-target="#bonusModalCredit">Bonus Credit
                                                                        <i class="ti ti-plus" style="font-weight: bold"></i>
                                                                    </span>
                                                                </div>

                                                                <div class="mb-2 col-6">
                                                                    <span class="badge btn btn-secondary" data-bs-toggle="modal"
                                                                        data-bs-target="#bonusModal">Bonus Deposit
                                                                        <i class="ti ti-plus" style="font-weight: bold"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        @endcan
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">

                                    <div class="table-responsive">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Leverage</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    echo $getUser->leverage;
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Balance</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    if (isset($account->balance)) {
                                                                        echo "$" . number_format($account->balance, 2);
                                                                    } else {
                                                                        echo "$0.00";
                                                                    }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Equity</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    $equity = null;

                                                                    // Handle both array and object forms
                                                                    if (is_array($accountHelper)) {
                                                                        $equity = $accountHelper['equity'] ?? null;
                                                                    } elseif (is_object($accountHelper)) {
                                                                        $equity = $accountHelper->equity ?? ($accountHelper->Equity ?? null);
                                                                    }

                                                                    if (!is_null($equity)) {
                                                                        echo "$" . number_format($equity, 2);
                                                                    }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Total Profit</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                        // Handle both array and object forms
                                                                        if (is_array($accountHelper)) {
                                                                            $total_profit = $accountHelper['profit'] ?? null;
                                                                        } elseif (is_object($accountHelper)) {
                                                                            $total_profit = $accountHelper->Profit ?? null;
                                                                        }

                                                                        if (!is_null($total_profit)) {
                                                                            echo "$" . number_format($total_profit, 2);
                                                                        }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Total Commission</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    // Handle both array and object forms
                                                                        if (is_array($accountHelper)) {
                                                                            $total_comission = $accountHelper['total_commission'] ?? null;
                                                                        } elseif (is_object($accountHelper)) {
                                                                            $total_comission = $accountHelper->Commission ?? null;
                                                                        }
                                                                        if (!is_null($total_comission)) {
                                                                            echo "$" . number_format($total_comission, 2);
                                                                        }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Total Swap</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                        // Handle both array and object forms
                                                                        if (is_array($accountHelper)) {
                                                                            $total_swap = $account->accountType->ac_swap ?? null;
                                                                        } elseif (is_object($accountHelper)) {
                                                                            $total_swap = $account->accountType->ac_swap ?? null;
                                                                        }
                                                                        if (($total_swap == null) && ($total_swap == '')) {
                                                                            echo ('No');
                                                                        }else {
                                                                            echo ('Yes');
                                                                        }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Total Trades</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    // Handle both array and object forms
                                                                        if (is_array($accountHelper)) {
                                                                            $total_trades = $accountHelper['total_trades'] ?? null;
                                                                        } elseif (is_object($accountHelper)) {
                                                                            $total_trades = $accountHelper->TotalTrades ?? null;
                                                                        }
                                                                        if (!is_null($total_trades)) {
                                                                            echo ($total_trades);
                                                                        }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-line-up f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Bonus</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    if (isset($account->BonusTransaction)) {
                                                                        echo "$" . number_format($account->BonusTransaction->sum('bonus_amount'), 2);
                                                                    }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-butterfly f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Free Margin</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    $feed_margin = null;
                                                                    // Handle both array and object forms
                                                                        if (is_array($accountHelper)) {
                                                                            $feed_margin = $accountHelper['margin_free'] ?? null;
                                                                        } elseif (is_object($accountHelper)) {
                                                                            $feed_margin = $accountHelper->MarginFree ?? 0;
                                                                        }
                                                                        if (!is_null($feed_margin)) {
                                                                            echo "$" . number_format($feed_margin, 2);
                                                                        }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-pie f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Margin</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    $margin = null;
                                                                    // Handle both array and object forms
                                                                        if (is_array($accountHelper)) {
                                                                            $margin = $accountHelper['margin']??0;
                                                                        } elseif (is_object($accountHelper)) {
                                                                            $margin = $accountHelper->Margin ?? 0;
                                                                        }
                                                                        if (!is_null($margin)) {
                                                                            echo number_format($margin, 2);
                                                                        }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-chart-pie-slice f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Margin Level</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    if (isset($account->margin_level)) {
                                                                        echo $account->margin_level . '%';
                                                                    }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="border avtar avtar-s"><i
                                                                class="ph-duotone ph-line-segments f-20"></i></div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="row g-1">
                                                            <div class="col-6">
                                                                <p class="mb-0 f-20">Floating PL</p>
                                                            </div>
                                                            <div class="col-6 text-end">
                                                                <h4 class="mb-1 f-w-400">
                                                                    <?php
                                                                    // if (isset($accountHelper['profit'])) {
                                                                    //     echo $accountHelper['profit'];
                                                                    // }
                                                                    $floating_pl = null;
                                                                    // Handle both array and object forms
                                                                        if (is_array($accountHelper)) {
                                                                            $floating_pl = $accountHelper['profit']??0;
                                                                        } elseif (is_object($accountHelper)) {
                                                                            $floating_pl = $accountHelper->profit ?? 0;
                                                                        }
                                                                        if (!is_null($floating_pl)) {
                                                                            echo "$" . number_format($floating_pl, 2);
                                                                        }
                                                                    ?>
                                                                </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @can('bonus_transaction:viewAny')
                        @if ($account->demo == 0)
                            <div class="mt-2 card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">Bonus</div>
                                    <div class="prism-toggle">
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table text-nowrap" id="tableBonus">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($bonus_trans as $bns) {
                                                ?>
                                                <tr>
                                                    <td>
                                                        {{ Carbon::parse($bns->bonus_date)->addHours(3)->format('Y-m-d') }}<br>
                                                        <small>
                                                            {{ Carbon::parse($bns->bonus_date)->addHours(3)->format('H:i:s') }}
                                                        </small>
                                                    </td>
                                                    <td><?php echo strpos($bns->admin_remark, 'Credit') != false ? 'Credit' : 'Deposit'; ?></td>
                                                    <td><?= $bns->bonus_amount ?></td>
                                                    <td><?= $bns->bonus_type ?></td>
                                                </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endcan
                </div>
                <div class="col-xl-8">
                    @if ($account->demo == 0)
                        <div class="card">
                            <div class="p-0 card-body">
                                <div class="row">
                                    @can('trade_deposit:viewAny')
                                        <div class="col-xl-3 col-lg-6 col-sm-6 pe-0 ps-0 border-end">
                                            <div class="text-center card-body">
                                                <h6 class="mb-0">Total Deposit</h6>
                                                <h2 class="mt-2 mb-1 number-font text-primary">$<span
                                                        class="counter"><?= $total_deposit ? number_format($total_deposit, 2) : '0' ?></span>
                                                </h2>
                                                <!-- <p class="mb-0 text-muted"> Completed</p> -->
                                            </div>
                                        </div>
                                        @if ($account->demo == 0)
                                            <div class="col-xl-3 col-lg-6 col-sm-6 pe-0 ps-0 border-end">
                                                <div class="text-center card-body">
                                                    <h6 class="mb-0">Unapproved Deposit</h6>
                                                    <h2 class="mt-2 mb-1 number-font text-secondary">$<span
                                                            class="counter"><?= $unapprove_deposit ? number_format($unapprove_deposit, 2) : '0' ?></span>
                                                    </h2>
                                                </div>
                                            </div>
                                        @endcan
                                    @endcan
                                    @can('trade_withdrawals:viewAny')
                                        <div class="col-xl-3 col-lg-6 col-sm-6 pe-0 ps-0 border-end">
                                            <div class="text-center card-body">
                                                <h6 class="mb-0">Total Withdrawl</h6>
                                                <h2 class="mt-2 mb-1 number-font text-primary">$<span
                                                        class="counter"><?= $total_withdrawl ? number_format($total_withdrawl, 2) : '0' ?></span>
                                                </h2>
                                                <!-- <p class="mb-0 text-muted"> Completed</p> -->
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-6 col-sm-6 pe-0 ps-0 border-end">
                                            <div class="text-center card-body">
                                                <h6 class="mb-0">Unapproved Withdrawl</h6>
                                                <h2 class="mt-2 mb-1 number-font text-secondary">$<span
                                                        class="counter"><?= $unapprove_withdrawl ? number_format($unapprove_withdrawl, 2) : '0' ?></span>
                                                </h2>
                                            </div>
                                        </div>
                                    @endcan
                            </div>
                        </div>
                    </div>
                @endif
                @can('account:viewCredentials')
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title d-flex justify-content-between">
                                        <div class="mt-auto mb-auto">Security / Passwords</div>
                                        <div class="gap-2 d-flex justify-content-between">
                                            <div class="resendCredentials">
                                                <button class="btn btn-secondary">
                                                    Resend Credentials
                                                </button>
                                            </div>
                                            <div class="updatePassword">
                                                <button class="btn btn-primary">
                                                    Update Credentials
                                                </button>
                                            </div>
                                        </div>
                                    </h5>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">Master Password</label>
                                                    <div class="input-group">
                                                        <input class="form-control" type="password" name=""
                                                            placeholder="" readonly aria-label=""
                                                            value="<?= $account->trader_password ?>"
                                                            aria-describedby="my-addon">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text showPassword h-100"
                                                                id="my-addon">
                                                                <i class="fa fa-eye-slash"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label for="">Investor Password</label>
                                                    <div class="input-group">
                                                        <input class="form-control" type="password" name=""
                                                            value="<?= $getUser->invester_password ?>"
                                                            aria-describedby="my-addon">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text showPassword h-100"
                                                                id="my-addon">
                                                                <i class="fa fa-eye-slash"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
                @can('account:viewSettings')
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <form action="{{ route('admin.updateAccountDetails') }}" enctype="multipart/form-data"
                                    method="post">
                                    @csrf
                                    <input type="hidden" name="code" value="<?= $getUser->code ?>">
                                    <div class="card-body">
                                        <h5 class="card-title d-flex justify-content-between">
                                            <div class="mt-auto mb-auto">Group / Leverage</div>
                                        </h5>
                                        <div class="pb-0 card-body">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="">Group</label>
                                                        <select class="form-control acc-types" name="account_type"
                                                            required id="account_type">

                                                            <?php foreach ($account_types as $grp) { ?>
                                                            <option value="{{ $grp->id }}"
                                                                {{ $grp->id == $getUser->account_type_id ? 'selected' : '' }}>
                                                                <strong>{{ $grp->ac_name }}</strong> [
                                                                {{ $grp->ac_group }} ]
                                                            </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label for="">Leverage</label>
                                                        <div class="input-group">
                                                            <select class="form-select" required name="leverage"
                                                                id="leverage">
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <div class=""><button type="submit" name="update_group" value="submit"
                                                class="btn btn-primary">Update Settings</button></div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                @endcan
                <div class="row">
                    @can('trade_deposit:viewAny')
                        @if ($account->demo == 0)
                            <div class="col-12">
                                <div class="card custom-card">
                                    <div class="card-header justify-content-between">
                                        <div class="card-title">
                                            Deposits
                                        </div>
                                        <div class="prism-toggle">
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table text-nowrap" id="tableDeposit">
                                                <thead>
                                                    <tr>
                                                        <th>Account No</th>
                                                        <th>Deposit Amount</th>
                                                        <th>Deposit Type</th>
                                                        <th>Deposit From</th>
                                                        <th>Deposited Date</th>
                                                        <th>Status</th>
                                                        @can('trade_deposit:view')
                                                            <th>Actions</th>
                                                        @endcan
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endcan
                    @can('trade_withdrawals:viewAny')
                        @if ($account->demo == 0)
                            <div class="col-12">
                                <div class="card custom-card">
                                    <div class="card-header justify-content-between">
                                        <div class="card-title">
                                            Withdrawal
                                        </div>
                                        <div class="prism-toggle">
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table text-nowrap" id="tableWithdrawal">
                                                <thead>
                                                    <tr>

                                                        <th>Account No</th>
                                                        <th>Withdrawal Amount</th>
                                                        <th>Withdrawal Type</th>
                                                        <th>Withdraw To</th>
                                                        <th>Withdrawal Date</th>
                                                        <th>Status</th>
                                                        @can('trade_withdrawals:view')
                                                            <th>Actions</th>
                                                        @endcan
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endcan
                    @can('trade_withdrawals:viewAny')
                        <div class="col-12">
                            <div class="card custom-card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">
                                        TRADES
                                        <div class="gap-2 d-inline-flex ms-3">
                                            <a href="/admin/export-all-trades?id=<?= $account->id ?>"
                                                class="export-all-btn-header btn btn-sm">
                                                EXPORT ALL
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#exportFilterModal">
                                                Export with filter
                                            </button>
                                        </div>
                                    </div>
                                    <div class="prism-toggle">
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table text-nowrap" id="tableTrades">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Symbol</th>
                                                    <th>Type</th>
                                                    <th>Volume</th>
                                                    <th>Open Price</th>
                                                    <th>Close Price</th>
                                                    <th>Profit</th>
                                                    <th>Status</th>
                                                    <th>Open Time</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    <?php } else { ?>
    <div class="p-2 text-center error-page">
        <div class="error-template">
            <h1 class="error-details text-primary">
                Sorry, No Data Found !!!!!
            </h1>
        </div>
    </div>
    <?php } ?>

    <!-- Export Filter Modal -->
    <div class="modal fade" id="exportFilterModal" tabindex="-1" aria-labelledby="exportFilterModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportFilterModalLabel">Export Trades with Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="exportFilterForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="date_from" class="form-label">Date From <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_from" name="date_from" required>
                        </div>
                        <div class="mb-3">
                            <label for="date_to" class="form-label">Date To <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_to" name="date_to" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <div id="passwordupdatemodal" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="post" id="passwordForm" action="{{ route('admin.updatePassword') }}">
                @csrf
                <input type="hidden" name="code" value="<?= $account->code ?>">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCenterTitle">Update Password</h5><button
                            type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <h5 class="p-2 f-w-200"><?= strtoupper($account->platform) ?> ACCOUNT</h5>
                            </div>
                            <div class="col-6">
                                <h5 class="p-2 f-w-400"><?= $account->code ?></h5>
                            </div>
                        </div>
                        <p class="p-2 mt-0 mb-2 text-gray-500 f-12 text-muted"> You have the ability to update your Investor and Master passwords for your trading accounts here.</p>
                        <div class="mt-0 mb-0 row">
                            <div class="mt-0 mb-0 row">
                                <div class="col-lg-6">
                                    <div class="p-3 border card">
                                        <div class="form-check"><input type="radio" name="password_type"
                                                class="form-check-input input-primary" id="customCheckdefhor1"
                                                value="investor" checked><label class="form-check-label d-block"
                                                for="customCheckdefhor1"><span><span class="h6">Investor
                                                        Password</span></span></label></div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="p-3 border card">
                                        <div class="form-check"><input type="radio" name="password_type"
                                                class="form-check-input input-primary" id="customCheckdefhor2"
                                                value="main"><label class="form-check-label d-block"
                                                for="customCheckdefhor2"><span><span class="h6">Master
                                                        Password</span></span></label></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-0 mb-0 row">
                                <div class="form-group"><label class="form-label" for="exampleInputPassword1">New
                                        Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="password"
                                            required id="password" placeholder="Password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                {{-- <div class="mt-3 mb-2 row">
                                    <div class="col-6"><span class="pc-micon me-2"><i
                                                class="ti ti-point"></i></span><span class="pc-mtext f-12">Minimum 8
                                            characters</span><br><span class="pc-micon me-2"><i
                                                class="ti ti-point"></i></span><span class="pc-mtext f-12">At least 1
                                            uppercase
                                            letter</span><br><span class="pc-micon me-2"><i
                                                class="ti ti-point"></i></span><span class="pc-mtext f-12">At least 1
                                            lowercase letter</span></div>
                                    <div class="col-6"><span class="pc-micon me-2"><i
                                                class="ti ti-point"></i></span><span class="pc-mtext f-12">At least 1
                                            special character</span><br><span class="pc-micon me-2"><i
                                                class="ti ti-point"></i></span><span class="pc-mtext f-12">At least 1
                                            digit</span></div>
                                </div> --}}
                                <div class="mb-2 form-group"><label class="form-label"
                                        for="exampleInputPassword1">Confirm
                                        Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control"
                                            name="confirm_password" required id="confirm_password" placeholder="Password">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>
                            <div class="mt-2 col-12">
                                @include('partials.password-validation-rules-admin', ['prefix' => 'mt5-update-'])
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-primary"
                            data-bs-dismiss="modal">Close</button><button class="btn btn-primary" type="submit"
                            name="passwordUpdate" value="true"> Update Password</button></div>
                </div>
            </form>
        </div>
    </div>
@endsection

    @section('scripts')
        @include('partials.password-validation-script')
    <script>
        const canViewTradeDeposit = @json(auth()->user()->can('trade_deposit:view'));
        const canViewTradeWithdrawal = @json(auth()->user()->can('trade_withdrawals:view'));

        function number_format(number, decimals = 2, dec_point = '.', thousands_sep = ',') {
            // Ensure the input is a valid number
            const match = number.match(/\d+(\.\d{2})?/);
            const n = parseFloat(match[0]);
            if (isNaN(n)) return 'NA';
            // Format the number with fixed decimals
            const fixed = n.toFixed(decimals);

            // Split the integer and decimal parts
            const parts = fixed.split('.');
            const integerPart = parts[0];
            const decimalPart = parts[1];

            // Add thousands separator to integer part
            const formattedInt = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep);

            return formattedInt + dec_point + decimalPart;
        }


        $("#tableBonus").DataTable();
        $('#tableDeposit').DataTable({
            // "ajax": {
            //     "url": "/admin/ajax",
            //     "type": "GET",
            //     data: {
            //         action: 'getTradingDeposit',
            //         id: '<?= $account->code ?>'
            //     },
            // },
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: '/admin/getTradingDeposit2',
                type: 'GET',
                data: {
                    id: '<?= $account->code ?>'
                }, // Ensure this is populated dynamically if needed.
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [{
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'deposit_amount',
                    name: 'deposit_amount',
                    render: function(data, type, row) {
                        return number_format(data);
                    }
                },
                {
                    data: 'deposit_type',
                    name: 'deposit_type'
                },
                {
                    data: 'deposit_from',
                    name: 'deposit_from'
                },
                {
                    data: 'deposit_date',
                    name: 'deposit_date',
                    // render: function(data, type, row) {
                    //     var dateTime = row.deposit_date.split(' ');
                    //     var date = dateTime[0];
                    //     var time = dateTime[1];
                    //     var return_data = "<div class='d-grid'><div class='date'>" + date +
                    //         "</div><div class='time text-muted'>" + time + "</div></div>";
                    //     return return_data;
                    // }
                },
                {
                    data: 'status',
                    name: 'status'
                },
                ...(canViewTradeDeposit ? [{
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }] : []),
            ]
        });
        $('#tableWithdrawal').DataTable({
            // "ajax": {
            //     "url": "/admin/ajax",
            //     "type": "GET",
            //     data: {
            //         action: 'getTradingWithdrawal',
            //         id: '<?= $account->id ?>'
            //     },
            // },
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: '/admin/getTradingWithdrawal2',
                type: 'GET',
                data: {
                    id: '<?= $account->id ?>'
                }, // Ensure this is populated dynamically if needed.
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [{
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'withdrawal_amount',
                    name: 'withdrawal_amount',
                    // render: function(data, type, row) {
                    //     return number_format(data);
                    // }
                },
                {
                    data: 'withdraw_type',
                    name: 'withdraw_type'
                },
                {
                    data: 'withdraw_to',
                    name: 'withdraw_from'
                },
                {
                    data: 'withdraw_date',
                    name: 'withdraw_date',
                    // render: function(data, type, row) {
                    //     var dateTime = row.withdraw_date.split(' ');
                    //     var date = dateTime[0];
                    //     var time = dateTime[1];
                    //     var return_data = "<div class='d-grid'><div class='date'>" + date +
                    //         "</div><div class='time text-muted'>" + time + "</div></div>";
                    //     return return_data;
                    // }
                },
                {
                    data: 'status',
                    name: 'status'
                },
                ...(canViewTradeWithdrawal ? [{
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }] : []),
            ]
        });

        function validatePassword(password) {
            const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasDigit = /\d/.test(password);
            const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            if (password.length < minLength) {
                return "Password must be at least 8 characters long.";
            }
            if (!hasUpperCase) {
                return "Password must contain at least one uppercase letter.";
            }
            if (!hasLowerCase) {
                return "Password must contain at least one lowercase letter.";
            }
            if (!hasDigit) {
                return "Password must contain at least one digit.";
            }
            if (!hasSpecialChar) {
                return "Password must contain at least one special character.";
            }

            return "true";
        }

        $(".updatePassword").click(function() {
            // alert("Clicekd");
            $("#passwordupdatemodal").modal("show");
        });

        $(".resendCredentials").click(function() {
            // Confirm action
            Swal.fire({
                title: 'Resend Credentials?',
                text: "An email with the Account Code and Master Password will be sent to the client.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, send it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const accountCode = "{{ $account->code }}";

                    // Show loading
                    Swal.fire({
                        title: 'Sending...',
                        text: 'Please wait while we send the credentials.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('admin.resend-credentials') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            code: accountCode
                        },

                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message ||
                                        'Credentials sent successfully.'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed',
                                    text: response.message ||
                                        'Could not send credentials.'
                                });
                            }
                        },
                        error: function(xhr) {
                            let msg = 'An error occurred while sending credentials.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg
                            });
                        }
                    });
                }
            });
        });

        $("#passwordForm").on("submit", function(e) {
            e.preventDefault();
            var pass = $("#password").val();
            var cpass = $("#confirm_password").val();
            if (validatePassword(pass) == "true") {
                if (pass == cpass) {
                    $("#passwordForm").off();
                    $("#passwordForm").submit();
                } else {
                    swal.fire({
                        icon: "info",
                        title: "Passwords not matched"
                    });
                    $("#confirm_password").val("")
                    return false;
                }
            } else {
                swal.fire({
                    icon: "info",
                    title: "Password not matched requirement.",
                    text: validatePassword(pass)
                })
            }
        });

        $(".showPassword").click(function() {
            var input = $(this).closest(".input-group").find("input");
            if (input.attr("type") == "password") {
                input.attr("type", "text");
                $(this).find("i").removeClass("fa-eye-slash");
                $(this).find("i").addClass("fa-eye");
            } else {
                input.attr("type", "password");
                $(this).find("i").removeClass("fa-eye");
                $(this).find("i").addClass("fa-eye-slash");
            }
        });

        $(".acc-types").change(function() {
            var selectedValue = $(".acc-types").val();
            $("#leverage").html("<option value='' checked>Loading...</option>");
            $.ajax({
                url: "/admin/api/ajax?type=leverage&id=" + selectedValue,
                success: function(data) {
                    // console.log("Res:: ",$data);
                    $("#leverage").html("");
                    $.each(data, function(key, value) {
                        // console.log(value);
                        var isSelected = "";
                        if (selectedValue == "<?= addslashes($getUser->account_type_id) ?>" &&
                            value.account_leverage == <?= (int) $getUser->leverage ?>) {
                            isSelected = "selected";
                        }
                        $("#leverage").append("<option value='" + value.account_leverage +
                            "' " + isSelected + ">" + value.account_leverage + "</option>");
                    });
                }
            })
        });
        $(".acc-types").trigger("change");
        $(".acc-types").select2();

        // Inject permission data from backend
        // function takeAction(url) {
        //     // window.open(url, '_blank');
        //     window.location.href = url;
        // }

        function handleClick(event, url) {
            if (event.button === 0) {
                // Left click - Navigate normally
                window.location.href = url;
            } else if (event.button === 1) {
                // Middle click - Open in a new tab
                window.open(url, '_blank');
            }
        }

        // Initialize Trades DataTable
        $('#tableTrades').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            order: [
                [8, 'desc']
            ], // Sort by Open Time descending
            lengthChange: true,
            pageLength: 10,
            dom: '<"row" <"col"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
            ajax: {
                url: '/admin/getTradeHistory',
                type: 'GET',
                data: {
                    id: '<?= $account->id ?>'
                },
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [{
                    data: 'order_id_display',
                    name: 'order_id',
                    title: 'Order ID'
                },
                {
                    data: 'symbol_display',
                    name: 'symbol',
                    title: 'Symbol'
                },
                {
                    data: 'type_display',
                    name: 'type',
                    title: 'Type'
                },
                {
                    data: 'volume_display',
                    name: 'volume',
                    title: 'Volume'
                },
                {
                    data: 'open_price_display',
                    name: 'open_price',
                    title: 'Open Price'
                },
                {
                    data: 'close_price_display',
                    name: 'close_price',
                    title: 'Close Price'
                },
                {
                    data: 'profit_display',
                    name: 'profit',
                    title: 'Profit'
                },
                {
                    data: 'status_display',
                    name: 'status',
                    title: 'Status'
                },
                {
                    data: 'open_time_display',
                    name: 'open_time',
                    title: 'Open Time'
                },
                {
                    data: 'action',
                    name: 'action',
                    title: 'Action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // Export Filter Form Handler
        document.getElementById('exportFilterForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const dateFrom = document.getElementById('date_from').value;
            const dateTo = document.getElementById('date_to').value;
            const accountId = '<?= $account->id ?>';

            // Validation
            if (!dateFrom || !dateTo) {
                alert('Please select both start and end dates.');
                return;
            }

            if (new Date(dateFrom) > new Date(dateTo)) {
                alert('Start date cannot be greater than end date.');
                return;
            }

            // Build export URL
            const exportUrl =
                `/admin/export-filtered-trades?id=${accountId}&date_from=${dateFrom}&date_to=${dateTo}`;

            // Close modal
            const modalElement = document.getElementById('exportFilterModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        });
        </script>
        <script>
            const mt5Password = document.getElementById('password');
            const mt5ConfirmPassword = document.getElementById('confirm_password');
            const toggleMT5UpdatePassword = document.getElementById('togglePassword');
            const toggleMT5UpdateConfirmPassword = document.getElementById('toggleConfirmPassword');

            if (mt5Password && mt5ConfirmPassword) {
                // Initialize all rules to false
                window.updateRuleUI('mt5-update-rule-length', false);
                window.updateRuleUI('mt5-update-rule-uppercase', false);
                window.updateRuleUI('mt5-update-rule-lowercase', false);
                window.updateRuleUI('mt5-update-rule-digit', false);
                window.updateRuleUI('mt5-update-rule-special', false);
                window.updateRuleUI('mt5-update-rule-no-spaces', false);
                window.updateRuleUI('mt5-update-rule-match', false);

                const handleMT5PasswordInput = () => {
                    const password = mt5Password.value;
                    const confirmPassword = mt5ConfirmPassword.value;

                    if (!password) {
                        // Reset all rules when password is empty
                        window.updateRuleUI('mt5-update-rule-length', false);
                        window.updateRuleUI('mt5-update-rule-uppercase', false);
                        window.updateRuleUI('mt5-update-rule-lowercase', false);
                        window.updateRuleUI('mt5-update-rule-digit', false);
                        window.updateRuleUI('mt5-update-rule-special', false);
                        window.updateRuleUI('mt5-update-rule-no-spaces', false);
                        window.updateRuleUI('mt5-update-rule-match', false);
                    } else {
                        const rules = window.checkPasswordRules(password, confirmPassword);
                        window.updateRuleUI('mt5-update-rule-length', rules.length);
                        window.updateRuleUI('mt5-update-rule-uppercase', rules.uppercase);
                        window.updateRuleUI('mt5-update-rule-lowercase', rules.lowercase);
                        window.updateRuleUI('mt5-update-rule-digit', rules.digit);
                        window.updateRuleUI('mt5-update-rule-special', rules.special);
                        window.updateRuleUI('mt5-update-rule-no-spaces', rules.noSpaces);
                        window.updateRuleUI('mt5-update-rule-match', confirmPassword ? rules.match : null);
                    }
                    // Enable/disable submit button based on all rules being satisfied
                    window.checkAllRulesSatisfied('mt5UpdatePassword', 'mt5UpdateConfirmPassword', 'mt5UpdatePasswordSubmitBtn');
                };

                const handleMT5ConfirmInput = () => {
                    const password = mt5Password.value;
                    const confirmPassword = mt5ConfirmPassword.value;

                    if (!confirmPassword) {
                        window.updateRuleUI('mt5-update-rule-match', false);
                    } else {
                        const rules = window.checkPasswordRules(password, confirmPassword);
                        window.updateRuleUI('mt5-update-rule-match', confirmPassword ? rules.match : null);
                    }
                    // Enable/disable submit button based on all rules being satisfied
                    window.checkAllRulesSatisfied('mt5UpdatePassword', 'mt5UpdateConfirmPassword', 'mt5UpdatePasswordSubmitBtn');
                };

                mt5Password.addEventListener('input', handleMT5PasswordInput);
                mt5ConfirmPassword.addEventListener('input', handleMT5ConfirmInput);
            }

            // Password Visibility Toggles
            if (toggleMT5UpdatePassword && mt5Password) {
                toggleMT5UpdatePassword.addEventListener('click', (e) => {
                    e.preventDefault();
                    const type = mt5Password.type === 'password' ? 'text' : 'password';
                    mt5Password.type = type;
                    toggleMT5UpdatePassword.innerHTML = type === 'password' ? '<i class="ti ti-eye"></i>' : '<i class="ti ti-eye-off"></i>';
                });
            }

            if (toggleMT5UpdateConfirmPassword && mt5ConfirmPassword) {
                toggleMT5UpdateConfirmPassword.addEventListener('click', (e) => {
                    e.preventDefault();
                    const type = mt5ConfirmPassword.type === 'password' ? 'text' : 'password';
                    mt5ConfirmPassword.type = type;
                    toggleMT5UpdateConfirmPassword.innerHTML = type === 'password' ? '<i class="ti ti-eye"></i>' : '<i class="ti ti-eye-off"></i>';
                });
            }
        </script>
    @endsection
