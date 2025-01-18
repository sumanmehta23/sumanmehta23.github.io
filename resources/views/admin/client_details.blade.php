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
    .editClient{
        cursor: pointer;
    }
</style>
    <div class="modal fade" id="addTicketModal" tabindex="-1" aria-labelledby="addTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="addTicketModalLabel1">New Ticket</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.addTicket') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row gy-4">
                            <div class="col-6">
                                <label for="input-label" class="form-label">Name</label>
                                <input type="text" class="form-control" name="subject_name" required>
                            </div>
                            <div class="col-6">
                                <label for="input-label" class="form-label">Type</label>
                                <select class="form-control" name="ticket_type_id" required>
                                    <option value="">Select Type</option>
                                    <?php foreach ($ticket_types as $type) { ?>
                                    <option value="<?php echo $type['id']; ?>"><?php echo $type['ticket_type']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="input-label" class="form-label">Description</label>
                                <textarea class="form-control" name="discription" required rows="3"></textarea>
                            </div>
                            <div class="col-6">
                                <label for="input-label" class="form-label">Assignee</label>
                                <select class="form-control" name="assignee_id" required>
                                    <?php if (isset($rm_details) && !empty($rm_details)): ?>
                                    <option selected value="{{ $rm_details->id }}">{{ $rm_details->username }}
                                    </option>
                                    <?php elseif (isset($superadmin_details) && !empty($superadmin_details)): ?>
                                    <option selected value="{{ $superadmin_details->id }}">Super Admin</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="input-label" class="form-label">Status</label>
                                <select class="form-control" name="ticket_status_id" required>
                                    <?php foreach ($ticket_status as $status) { ?>
                                    <option {{ $status['ticket_status'] == 'Open' ? 'selected' : '' }}
                                        value="<?php echo $status['id']; ?>">
                                        <?php echo $status['ticket_status']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="email" value="<?php echo $user->email; ?>" />
                        <input type="submit" class="btn btn-primary" name="add_ticket" value="Submit Ticket">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="kycModal" tabindex="-1" aria-labelledby="kycModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kycModalLabel">KYC Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <embed id="kycFile" src="" type="" width="100%">
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="accountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="accountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="#" id="createMT5Form" method="post">
                    <input type="hidden" name="client_id" id="client_id" value="{{ ($user->id) }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="accountModalLabel">Create New MT5 Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card">
                        <div class="row">
                            <div class="m-auto col-lg-4">
                                <label class="form-label">Account Type</label>
                            </div>
                            <div class="col-lg-8">
                                <select class="form-select acc-types" required name="acc-types">
                                    <option value="" selected>Choose Account Type</option>
                                        @foreach ($acc_types as $gp)
                                        <option value="{{ $gp->ac_index }}">{{ $gp->ac_name }}</option>
                                        @endforeach

                                </select>
                            </div>
                        </div>
                        <div class="mt-3 row">
                            <div class="m-auto col-lg-4">
                                <label class="form-label">Leverage</label>
                            </div>
                            <div class="col-lg-8">
                                <select class="form-select" name="leverage" id="leverage" required>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_account" value="update" class="btn btn-primary">Create
                            Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ibModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="ibModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="#" id="ibRequestForm" method="post">
                    @csrf
                    <input type="hidden" name="client_id" id="client_id" value="{{ ($user->id) }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ibModalLabel">IB Request Management</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="mb-0 modal-body custom-card card">
                        <div class="d-flex align-items-center card-header w-100">
                            <div class="me-2">
                                <span class="avatar avatar-rounded">
                                    <img src="/admin_assets/assets/images/users/user.png" alt="img">
                                </span>
                            </div>
                            <div class="">
                                <div class="fs-15 fw-medium text-capitalize" id="clientName"></div>
                                <p class="mb-0 text-muted fs-11" id="clientEmail"></p>
                            </div>

                        </div>
                        <div class="card-body">
                            <div class="mb-3 row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">IB Request Status</label>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-select" required name="ib_status"
                                        aria-label="Default select example">
                                        <option value="" selected>--Status--</option>
                                        <option value="1">Approve</option>
                                        <option value="0">Pending</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="m-auto col-lg-4">
                                    <label class="form-label">Account Group</label>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-select" required name="ib_group"
                                        aria-label="Default select example">
                                        <option value="" selected>--Group--</option>
                                        <?php foreach ($acc_groups as $gp) { ?>
                                        <option value="{{ $gp->ib_plan_cat_id }}">{{ $gp->ib_cat_name }}</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="ibRequest" value="update" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Start:: row-1 -->
            <div class="mt-5 row" id="user-profile">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">CLIENT DETAILS</div>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Client Details</li>
                            </ol>
                        </div>
                        <div class="card-body">
                            <div class="wideget-user">
                                <div class="row">
                                    <div class="col-lg-12 col-xl-12">
                                        <div class="wideget-user-desc d-flex">
                                            <div class="wideget-user-img d-flex align-items-center ">
                                                <img src="/admin_assets/assets/images/users/client.jpeg" alt="img"
                                                    style="width:100px">
                                            </div>
                                            <div class="user-wrap">
                                                <h4 class="fw-normal text-uppercase">{{ $user->fullname }}</h4>
                                                <h6 class="mb-3 fw-normal">
                                                    <span class="px-2"><span
                                                            class="fi fis fi-{{ strtolower(@$country_code->country_alpha) }} me-2"></span>{{ $user->country }}</span>
                                                    |
                                                    <span class="px-2">{!! $user->kyc_verify == 0
                                                        ? '<span class="badge bg-outline-danger">Pending KYC</span>'
                                                        : ($user->status == 1
                                                            ? '<span class="badge bg-outline-success">KYC Verified</span>'
                                                            : '') !!}</span>
                                                    |
                                                    <span
                                                        class="px-2"><strong>DOJ:</strong>{{ date('d M Y h:i A', strtotime($user->created_at)) }}</span>
                                                    |
                                                    <span class="px-2">{!! $user->status == 0
                                                        ? '<span class="badge bg-outline-danger">Inactive</span>'
                                                        : ($user->status == 1
                                                            ? '<span class="badge bg-outline-success">Active</span>'
                                                            : '') !!}</span>
                                                </h6>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <button
                                                                class="btn btn-icon bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                <i class="ri-mail-line"></i>
                                                            </button>
                                                            <div>
                                                                <div class="mb-0 text-muted fs-11">Email:</div>
                                                                <div class="mb-1 fs-12">{{ $user->email }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <button
                                                                class="btn btn-icon bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                <i class="ri-phone-line"></i>
                                                            </button>
                                                            <div>
                                                                <div class="mb-0 text-muted fs-11">Phone:</div>
                                                                <div class="mb-1 fs-12">{{ $user->number }}</div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="pt-1 mt-1 border-2 row border-top border-default">
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <button
                                                                class="btn btn-icon bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                <i class="ri-user-2-fill"></i>
                                                            </button>
                                                            <div>
                                                                <div class="mb-0 text-muted fs-11">Relationship Manager:
                                                                </div>
                                                                <div class="mb-1 fs-12">{{ $rm_details->username ?? '' }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <button
                                                                class="btn btn-icon bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                <i class="ri-user-line"></i>
                                                            </button>
                                                            <div>
                                                                <div class="mb-0 text-muted fs-11">Parent IB:</div>
                                                                <div class="mb-1 fs-12">{{ $user->ib1 }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-xl-12" style="display:none">
                                        <button type="button" class="shadow-sm btn btn-outline-light"><i
                                                class="ri-mail-line text-primary me-1"></i>Send Mail</button>
                                        <button type="button" class="shadow-sm btn btn-outline-light"><i
                                                class="ri-share-line"></i></button>
                                        <button type="button" class="shadow-sm btn btn-outline-light"><i
                                                class="ri-flag-line"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-top">
                            <div class="wideget-user-tab">
                                <div class="border-0 tab-menu-heading">
                                    <div class="tabs-menu1">
                                        <ul class="nav clienttabs">
                                            <li class=""><a href="#tab-overview" class="active show"
                                                    data-bs-toggle="tab">OVERVIEW</a></li>
                                            <li><a href="#tab-transactions" data-bs-toggle="tab"
                                                    class="">TRANSACTIONS</a></li>
                                            <?php if (!empty($ib_details)): ?>
                                            @can('ib:viewAny')
                                            <li><a href="#tab-ib" data-bs-toggle="tab" class="">IB PROFILE</a></li>
                                            @endcan
                                            <?php endif; ?>
                                            <li><a href="#tab-info" data-bs-toggle="tab" class="">ADDITIONAL
                                                    INFO</a></li>
                                            <li><a href="#tab-profile" data-bs-toggle="tab" class="">PROFILE
                                                    SETTINGS</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <div class="">
                            <div class="border-0">
                                <div class="tab-content clienttabs">
                                    <div class="p-0 tab-pane active show" id="tab-overview">
                                        <div class="row">
                                            <div class="col-12 col-xl-9">
                                                <div class="card custom-card">
                                                    <div class="card-header">
                                                        <div class="card-title">SUMMARY</div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="pb-3 row border-bottom">
                                                            @can('wallet_deposit:viewAny')
                                                            <div class="col-xl-3">
                                                                <h4 class="mb-3 text-muted fw-normal">TOTAL DEPOSIT</h4>
                                                                <h4 class="fw-normal">
                                                                    ${{ htmlentities(number_format((float) $total_wd, 2)) }}
                                                                </h4>
                                                            </div>
                                                            @endcan
                                                            @can('wallet_withdraw:viewAny')
                                                            <div class="col-xl-3">
                                                                <h4 class="mb-3 text-muted fw-normal">TOTAL WITHDRAW</h4>
                                                                <h4 class="fw-normal">
                                                                    ${{ htmlentities(number_format((float) $total_ww, 2)) }}
                                                                </h4>
                                                            </div>
                                                            @endcan
                                                            <div class="col-xl-3">
                                                                <h4 class="mb-3 text-muted fw-normal">WALLET</h4>
                                                                <?php if ($user->wallet_enabled): ?>
                                                                <h4 class="fw-normal">
                                                                    ${{ htmlentities(number_format($wallet_balance, 2)) }}
                                                                </h4>
                                                                <?php else: ?>
                                                                <button type="button"
                                                                    class="btn btn-outline-dark btn-sm disabled">
                                                                    No Wallet
                                                                </button>
                                                                <?php endif; ?>
                                                            </div>

                                                        </div>
                                                        @can('account:viewLiveAccounts')
                                                        <div class="mt-3 row">
                                                            <div class="d-flex justify-content-between">
                                                                <h4>LIVE MT5 ACCOUNTS</h4>
                                                                <!-- <button type="button" class="d-none btn btn-outline-dark btn-sm bg-light"
                                                                              data-bs-toggle="modal" data-bs-target="#accountModal">
                                                                              <i class="ri-add-box-fill"></i>
                                                                              CREATE NEW MT5 ACCOUNTS
                                                                            </button> -->
                                                            </div>
                                                        </div>
                                                        <div class="px-2 row">
                                                            <?php if (empty($live_accounts)) { ?>
                                                                <div class="my-4 text-muted">No Live Accounts Found.</div>
                                                            <?php } ?>
                                                            <?php foreach ($live_accounts as $acc): ?>
                                                            <div class="my-2 border border-dashed col-xl-4 col-lg-6 border-3">
                                                                <div>
                                                                    <div
                                                                        class="pb-2 mt-2 mb-2 border-2 row border-bottom border-bottom-dashed">
                                                                        <div class="d-flex w-50 flex-column">
                                                                            <img src="/admin_assets/assets/images/mt5.png"
                                                                                alt="card img" style="width:50px;">
                                                                            <div class="mt-1 fs-18 text-black-50 fw-bold">
                                                                                {{ $acc->code }}</div>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end w-50">
                                                                            <span
                                                                                class="mt-2 h4 fw-normal">${{ $acc->balance }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between">
                                                                        <div>
                                                                            <div class="fw-bold fs-12">
                                                                                {{ $acc->accountType->ac_name }}</div>
                                                                            <div class="mb-2 fw-normal fs-10">
                                                                                {{ $acc->accountType->ac_group }}</div>
                                                                        </div>
                                                                        <div class="mt-auto mb-auto">
                                                                            <a
                                                                                href="/admin/view_account_details/{{ $acc->id }}">
                                                                                <i class="fa fa-edit fw-bold"
                                                                                    style="font-size: 1rem;color: var(--primary-color);"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-xl-3">
                                                <div>
                                                    @can('account:viewSettings')
                                                        <div class="card custom-card">
                                                            <div class="card-header">
                                                                <div class="d-flex justify-content-between">
                                                                    <div class="card-title">ACTIONS</div>
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                    @php
                                                                        $success = '';
                                                                        if (intval($user->kyc_verify) >= 1) {
                                                                            $success = ($user->status == 0) ? 'bg-success' : 'bg-success text-white';
                                                                        }
                                                                    @endphp
                                                                    <div class="col-lg-5 col-xl-4 col-xl-12 col-sm-12">
                                                                            <div class="card-body d-flex">
                                                                                @can("client:update")
                                                                                <div class="statusToggle" data-status="{{ $user->status }}">
                                                                                    @if ($user->status == 0)
                                                                                        <div class="badge text-danger {{ $success }}" data-bs-toggle="tooltip" title="Inactive User">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" size="25" class="tabler-icon tabler-icon-user-scan">
                                                                                                <path d="M10 9a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                                                                                                <path d="M4 8v-2a2 2 0 0 1 2 -2h2"></path>
                                                                                                <path d="M4 16v2a2 2 0 0 0 2 2h2"></path>
                                                                                                <path d="M16 4h2a2 2 0 0 1 2 2v2"></path>
                                                                                                <path d="M16 20h2a2 2 0 0 0 2 -2v-2"></path>
                                                                                                <path d="M8 16a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2"></path>
                                                                                            </svg>
                                                                                        </div>
                                                                                    @elseif ($user->status == 1)
                                                                                        <div class="badge text-success {{ $success }}" data-bs-toggle="tooltip" title="Active User">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" size="25" class="tabler-icon tabler-icon-user-scan">
                                                                                                <path d="M10 9a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                                                                                                <path d="M4 8v-2a2 2 0 0 1 2 -2h2"></path>
                                                                                                <path d="M4 16v2a2 2 0 0 0 2 2h2"></path>
                                                                                                <path d="M16 4h2a2 2 0 0 1 2 2v2"></path>
                                                                                                <path d="M16 20h2a2 2 0 0 0 2 -2v-2"></path>
                                                                                                <path d="M8 16a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2"></path>
                                                                                            </svg>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                                @endcan
                                                                                @if ($user->email_confirmed == 0)
                                                                                    <div class="resendToggle" data-status="{{ $user->email_confirmed }}">
                                                                                        <div class="badge text-danger" data-bs-toggle="tooltip" title="Email Not Verified">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="#FFCC80" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" size="25" class="tabler-icon tabler-icon-mail-x">
                                                                                                <path d="M13.5 19h-8.5a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v6"></path>
                                                                                                <path d="M3 7l9 6l9 -6"></path>
                                                                                                <path d="M22 22l-5 -5"></path>
                                                                                                <path d="M17 22l5 -5"></path>
                                                                                            </svg>
                                                                                        </div>
                                                                                        <div class='badge text-info pointer resendVerificationEmail' data-bs-toggle='tooltip' title='Resend Verification Email'>
                                                                                            <svg class='w-64 h-64' fill='currentColor' width='25' height='25' xmlns='http://www.w3.org/2000/svg' id='mdi-email-sync-outline' viewBox='0 0 24 24'><path d='M3 4C1.9 4 1 4.9 1 6V18C1 19.1 1.9 20 3 20H13.5A6.5 6.5 0 0 1 13 18H3V8L11 13L19 8V11A6.5 6.5 0 0 1 19.5 11A6.5 6.5 0 0 1 21 11.18V6C21 4.9 20.1 4 19 4H3M3 6H19L11 11L3 6M19 12L16.75 14.25L19 16.5V15C20.38 15 21.5 16.12 21.5 17.5C21.5 17.9 21.41 18.28 21.24 18.62L22.33 19.71C22.75 19.08 23 18.32 23 17.5C23 15.29 21.21 13.5 19 13.5V12M15.67 15.29C15.25 15.92 15 16.68 15 17.5C15 19.71 16.79 21.5 19 21.5V23L21.25 20.75L19 18.5V20C17.62 20 16.5 18.88 16.5 17.5C16.5 17.1 16.59 16.72 16.76 16.38L15.67 15.29Z'></path></svg>
                                                                                        </div>
                                                                                    </div>
                                                                                @else
                                                                                    <div class="statusToggle" data-status="{{ $user->email_confirmed }}">
                                                                                        <div class="badge text-success" data-bs-toggle="tooltip" title="Email Verified">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="#81C784" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" size="25" color="#81C784" class="tabler-icon tabler-icon-mail-check">
                                                                                                <path d='M11 19h-6a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v6'></path>
                                                                                                <path d='M3 7l9 6l9 -6'></path>
                                                                                                <path d='M15 19l2 2l4 -4'></path>
                                                                                            </svg>
                                                                                        </div>
                                                                                    </div>
                                                                                @endif


                                                                                @can('client:update')
                                                                                    <div class='editClient' data-enc='{{$user->id}}'>
                                                                                        <div class='badge text-secondary' data-bs-toggle='tooltip' title='Edit Client'>
                                                                                            <svg  xmlns='http://www.w3.org/2000/svg'  width='24'  height='24'  viewBox='0 0 24 24'  fill='none'  stroke='currentColor'  stroke-width='2'  stroke-linecap='round'  stroke-linejoin='round'  class='icon icon-tabler icons-tabler-outline icon-tabler-edit text-secondary'><path stroke='none' d='M0 0h24v24H0z' fill='none'/><path d='M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1' /><path d='M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z' /><path d='M16 5l3 3' /></svg>
                                                                                        </div>
                                                                                    </div>
                                                                                @endcan
                                                                                @can('client:impersonate')
                                                                                    <div class="switchClient" data-enc="{{ $user->id }}">
                                                                                        <div class="badge text-secondary" data-bs-toggle="tooltip" title="Switch Client">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrows-shuffle" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                                                <path stroke='none' d='M0 0h24v24H0z' fill='none'/>
                                                                                                <path d='M18 4l3 3l-3 3' />
                                                                                                <path d='M18 20l3 -3l-3 -3' />
                                                                                                <path d='M3 7h3a4 4 0 0 1 4 4a4 4 0 0 0 4 4h7' />
                                                                                                <path d='M21 7h-7a4 4 0 0 0 -4 4a4 4 0 0 1 -4 4h-3' />
                                                                                            </svg>
                                                                                        </div>
                                                                                    </div>
                                                                                    @endcan
                                                                            </div>
                                                                            <div class="modal fade" id="statusModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                                                                                aria-labelledby="statusModalLabel" aria-hidden="true">
                                                                                <div class="modal-dialog modal-dialog-centered">
                                                                                    <div class="modal-content">
                                                                                        <form action="#" id="statusUpdateForm" method="post">
                                                                                            @csrf
                                                                                            <input type="hidden" name="action" value="updateClientStatus">
                                                                                            <input type="hidden" name="client_id" id="user_id" value="">
                                                                                            <div class="modal-header">
                                                                                                <h5 class="modal-title" id="statusModalLabel">Update Status</h5>
                                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                            </div>
                                                                                            <div class="mb-0 modal-body custom-card card">
                                                                                                <div class="d-flex align-items-center card-header w-100">
                                                                                                    <div class="me-2">
                                                                                                        <span class="avatar avatar-rounded">
                                                                                                            <img src="/admin_assets/assets/images/users/user.png" alt="img">
                                                                                                        </span>
                                                                                                    </div>
                                                                                                    <div class="">
                                                                                                        <div class="fs-15 fw-medium text-capitalize" id="userName"></div>
                                                                                                        <p class="mb-0 text-muted fs-11" id="userEmail"></p>
                                                                                                    </div>

                                                                                                </div>
                                                                                                <div class="card-body">
                                                                                                    <div class="mb-3 row">
                                                                                                        <div class="m-auto col-lg-4">
                                                                                                            <label class="form-label">User Status</label>
                                                                                                        </div>
                                                                                                        <div class="col-lg-8">
                                                                                                            <div class="form-check form-switch">
                                                                                                                <input class="form-check-input" type="checkbox" role="switch" name="status"
                                                                                                                    id="user_status" checked>
                                                                                                                <label class="form-check-label" for="user_status"></label>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="mb-3 row">
                                                                                                        <div class="m-auto col-lg-4">
                                                                                                            <label class="form-label">Email Confirmed</label>
                                                                                                        </div>
                                                                                                        <div class="col-lg-8">

                                                                                                            <div class="form-check form-switch">
                                                                                                                <input class="form-check-input" type="checkbox" role="switch"
                                                                                                                    name="email_confirmed" id="email_status">
                                                                                                                <label class="form-check-label" for="email_status"></label>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="row">
                                                                                                        <div class="m-auto col-lg-4">
                                                                                                            <label class="form-label">KYC Verification</label>
                                                                                                        </div>
                                                                                                        <div class="col-lg-8">

                                                                                                            <div class="form-check form-switch">
                                                                                                                <input class="form-check-input" type="checkbox" role="switch" name="kyc_verify"
                                                                                                                    id="kyc_verify">
                                                                                                                <label class="form-check-label" for="kyc_verify"></label>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="modal-footer">
                                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                                <button type="submit" name="ibRequest" value="update" class="btn btn-primary">Update</button>
                                                                                            </div>
                                                                                        </form>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="modal fade" id="editUserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                                                                                aria-labelledby="editUserLabel" aria-hidden="true">
                                                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                                                    <div class="modal-content">
                                                                                        <form action="{{ route('admin.updateUser') }}" id="editUserForm" method="post">
                                                                                            @csrf
                                                                                            <div class="modal-header">
                                                                                                <h5 class="modal-title" id="editUserLabel">Update Client Details</h5>
                                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                            </div>
                                                                                            <div class="mb-0 modal-body custom-card card">
                                                                                                <input type="hidden" name="id">
                                                                                                <div class="row">
                                                                                                    <div class="col-6">
                                                                                                        <label for="input-label" class="form-label">Email:</label>
                                                                                                        <input type="text" class="form-control" name="email" required readonly>
                                                                                                    </div>
                                                                                                    <div class="col-6">
                                                                                                        <label for="input-label" class="form-label">Full Name:</label>
                                                                                                        <input type="text" class="form-control" name="fullname" required>
                                                                                                    </div>
                                                                                                    <div class="col-6">
                                                                                                        <label for="input-label" class="form-label">Phone:</label>
                                                                                                        <div class="input-group">
                                                                                                            <div class="input-group-prepend w-25">
                                                                                                                <select class="form-select me-2 w-25 edit-countrycode" name="country_code"
                                                                                                                    required>
                                                                                                                    <option value="">Country Code</option>
                                                                                                                    <?php foreach ($countries as $country) { ?>
                                                                                                                    <option value="+<?= $country['country_code'] ?>"
                                                                                                                        data-flag="<?= strtolower($country['country_alpha']) ?>">
                                                                                                                        +<?= $country['country_code'] ?>
                                                                                                                        (<?= $country['country_name'] ?>)</option>
                                                                                                                    <?php } ?>
                                                                                                                </select>


                                                                                                            </div>
                                                                                                            <input type="text" class="form-control" id="phone_number" name="telephone"
                                                                                                                placeholder="Enter phone number">
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <div class="col-6">
                                                                                                        <label for="input-label" class="form-label">Country:</label>
                                                                                                        <select class="form-select" id="country" name="country" required>
                                                                                                            <option value="">Select Country</option>
                                                                                                            <?php foreach ($countries as $country) { ?>
                                                                                                            <option value="<?= $country['country_name'] ?>">
                                                                                                                <?= $country['country_name'] ?>
                                                                                                            </option>
                                                                                                            <?php } ?>
                                                                                                        </select>
                                                                                                    </div>
                                                                                                    <div class="col-6">
                                                                                                        <label for="input-label" class="form-label">Password:</label>
                                                                                                        <input type="password" class="form-control" name="password" required>
                                                                                                    </div>
                                                                                                    <div class="col-6">
                                                                                                        <label for="input-label" class="form-label">Confirm Password:</label>
                                                                                                        <input type="password" class="form-control" id="input" name="confirm_password"
                                                                                                            required>
                                                                                                    </div>

                                                                                                    <div class="col-lg-6 d-flex align-items-end">
                                                                                                        <div class="form-check form-switch">
                                                                                                            <input class="form-check-input" type="checkbox" role="switch"
                                                                                                                name="email_notification">
                                                                                                            <label class="form-check-label">Send Notification Email</label>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="modal-footer">
                                                                                                <button type="submit" name="updateUser" value="update" class="btn btn-primary">Update</button>
                                                                                            </div>
                                                                                        </form>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endcan
                                                    {{-- <button type="button"
                                                        class="py-3 my-2 btn btn-outline-dark btn-sm w-100"
                                                        data-bs-toggle="modal" data-bs-target="#addTicketModal">
                                                        CREATE TICKET
                                                    </button> --}}
                                                    <div class="card custom-card">
                                                        <div class="card-header">
                                                            <div class="d-flex justify-content-between">
                                                                <div class="card-title">INTRODUCING BROKER</div>
                                                                <div>
                                                                    <?php if ($user->ib_status == 0): ?>
                                                                    <span
                                                                        class="badge bg-outline-warning text-end">Pending</span>
                                                                    <?php elseif ($user->ib_status == 1): ?>
                                                                    <span class="badge bg-outline-success text-end">Active
                                                                        IB</span>
                                                                    <?php else: ?>
                                                                    <span class="badge bg-outline-info text-end">Not
                                                                        Requested</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="card-text">A request on behalf of client for creating
                                                                IB profile for this client.
                                                            </p>
                                                            <?php if ($user->ib_status != 1): ?>
                                                            <?php if ($user->ib_status == '0'): ?>
                                                            <button type="button"
                                                                class="py-3 my-2 ib-enroll btn btn-outline-dark btn-sm w-100 text-uppercase"
                                                                data-bs-toggle="modal" data-bs-target="#ibModal">
                                                                Approve Request
                                                            </button>
                                                            <?php else: ?>
                                                            <button type="button"
                                                                class="py-3 my-2 ibToggle ib-enroll btn btn-outline-dark btn-sm w-100 text-uppercase"
                                                                data-bs-toggle="modal" data-bs-target="#ibModal"
                                                                data-fullname="<?= $user->fullname ?>"
                                                                data-email="<?= $user->email ?>"
                                                                data-enc="<?= ($user->email) ?>"
                                                                data-ib_status="<?= $user->ib_status ?>">
                                                                Request To become ib
                                                            </button>
                                                            <?php endif; ?>
                                                            <?php else: ?>
                                                            <hr style="opacity:.1;">
                                                            <label class="col-form-label col-12 text-lg-start">
                                                                Copy this IB referral link to share with potential clients!
                                                            </label>
                                                            <div class="mb-4 col-12">
                                                                <div class="mb-2 input-group"><input type="text"
                                                                        class="form-control" id="pc-clipboard-1"
                                                                        value="https://{{ $_SERVER['HTTP_HOST'] }}/register/ref?refercode={{ base64_encode($user->email) }}"
                                                                        readonly=""><button
                                                                        class="btn btn-lg btn-primary cb" id="ibClient"
                                                                        data-clipboard-target="#pc-clipboard-1"><i
                                                                            class="fa fa-copy"></i></button>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-0 tab-pane" id="tab-transactions">
                                        <div class="row">
                                            @can('wallet_deposit:viewAny')
                                            <div class="col-xl-6">
                                                <div class="card custom-card">
                                                    <div class="card-header justify-content-between">
                                                        <div class="card-title">
                                                            Deposits
                                                        </div>
                                                        <div class="prism-toggle">
                                                            <a href="/admin/transactions/trading_deposit"
                                                                class="btn btn-sm btn-primary-light">View
                                                                All</a>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table text-nowrap" id="tableDeposit">
                                                                <thead>
                                                                    <tr>
                                                                        <th scope="col">Created On</th>
                                                                        <th scope="col">Deposit To</th>
                                                                        <th scope="col">Payment Method</th>
                                                                        <th scope="col">Amount</th>
                                                                        <th scope="col">Status</th>
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
                                            @can('wallet_withdraw:viewAny')
                                            <div class="col-xl-6">
                                                <div class="card custom-card">
                                                    <div class="card-header justify-content-between">
                                                        <div class="card-title">
                                                            Withdrawal
                                                        </div>
                                                        <div class="prism-toggle">
                                                            <a href="/admin/transactions/wallet_withdrawal"
                                                                class="btn btn-sm btn-primary-light">View All</a>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table text-nowrap" id="tableWithdrawal">
                                                                <thead>
                                                                    <tr>
                                                                        <th scope="col">Created On</th>
                                                                        <th scope="col">From</th>
                                                                        <th scope="col">Withdraw Method</th>
                                                                        <th scope="col">Amount</th>
                                                                        <th scope="col">Fee</th>
                                                                        <th scope="col">Status</th>
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
                                            @can('internal_transfer:viewAny')
                                            <div class="col-xl-6">
                                                <div class="card custom-card">
                                                    <div class="card-header justify-content-between">
                                                        <div class="card-title">
                                                            Internal Transfers
                                                        </div>
                                                        <div class="prism-toggle">
                                                            <a href="/admin/transactions/internal_transfer"
                                                                class="btn btn-sm btn-primary-light">View All</a>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table text-nowrap" id="tableInternalTransfer">
                                                                <thead>
                                                                    <tr>
                                                                        <th scope="col">Created On</th>
                                                                        <th scope="col">From</th>
                                                                        <th scope="col">To</th>
                                                                        <th scope="col">Amount</th>
                                                                        <th scope="col">Status</th>
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
                                    <?php if (!empty($ib_details)): ?>
                                    <div class="p-0 tab-pane" id="tab-ib">
                                        <div class="row">
                                            <div class="col-sm-12 col-xl-3 col-lg-3">
                                                <div class="card custom-card">
                                                    <div class="card-body">
                                                        <div class="card-order">
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <button
                                                                        class="w-100 h-75 btn btn-icon btn-lg bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                        <i class="fa fa-list-alt" aria-hidden="true"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="col-8">
                                                                    <p class="h5 text-muted">IB </br> PLAN</p>
                                                                    <h4>{{ getPlanNameByPlanId($acc_groups, $ib_details->acc_type) }}
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-xl-3 col-lg-3">
                                                <div class="card custom-card">
                                                    <div class="card-body">
                                                        <div class="card-order">
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <button
                                                                        class="w-100 h-75 btn btn-icon btn-lg bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                        <i class="fa fa-credit-card"
                                                                            aria-hidden="true"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="col-8">
                                                                    <p class="h5 text-muted">IB </br>WALLET</p>
                                                                    <h4><?= "$" . number_format($ib_details->deposit -
                                                                    $ib_details->withdraw, 2) ?></h4>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-xl-3 col-lg-3">
                                                <div class="card custom-card">
                                                    <div class="card-body">
                                                        <div class="card-order">
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <button
                                                                        class=" w-100 h-75 btn btn-icon btn-lg bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                        <i class="fa fa-credit-card-alt"
                                                                            aria-hidden="true"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="col-8">
                                                                    <p class="h5 text-muted">TOTAL </br>COMMISSION</p>
                                                                    <h4>{{ $ib_details->deposit ? "$" . $ib_details->deposit : "$0.00" }}
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-xl-3 col-lg-3">
                                                <div class="card custom-card">
                                                    <div class="card-body">
                                                        <div class="card-order">
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <button
                                                                        class="w-100 h-75 btn btn-icon btn-lg bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                        <i class="fa fa-usd" aria-hidden="true"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="col-8">
                                                                    <p class="h5 text-muted">TOTAL </br>DEPOSIT</p>
                                                                    <h4>{{ $ib_details->withdraw ? "$" . $ib_details->withdraw : "$0.00" }}
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card">
                                                    <div class="p-3 card-body">
                                                            <ul class="nav nav-pills nav-tabs nav-justified" role="tablist">
                                                                <?php for ($i = 1; $i <= 15; $i++) { ?>
                                                                    <li class="nav-item"
                                                                    data-target-form="#LEVEL{{ $i }}"
                                                                    role="presentation"><a
                                                                        class="nav-link client-level {{ $i == 1 ? 'active' : '' }}"
                                                                        data-level="{{ $i }}"
                                                                        aria-selected="false" role="tab"
                                                                        tabindex="-1"><i
                                                                            class="ti ti-chart-bar me-2"></i><span
                                                                            class="d-none d-sm-inline">LEVEL{{ $i }}</span></a>
                                                                </li>
                                                                <?php } ?>
                                                            </ul>
                                                    </div>
                                                </div>
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="tab-content connectionTab" id="nav-tabContent">
                                                            <?php for ($i = 1; $i <= 15; $i++) { ?>
                                                                <div class="tab-pane fade<?php echo ($i == 1 ? ' show active' : ''); ?>"
                                                                    id="LEVEL<?php echo $i; ?>" role="tabpanel">
                                                                <div class="datatable-container">
                                                                    <table class="table table-hover datatable-table ajaxDataTable table-bordered text-nowrap w-100"
                                                                        id="ajaxDatatable">
                                                                        <thead>
                                                                            <tr>
                                                                                <th style="width: 30%;">CLIENT</th>
                                                                                <th class="text-end" style="width: 10%;">
                                                                                    TOTAL ACCOUNTS</th>
                                                                                <th class="text-end" style="width: 10%;">
                                                                                    TOTAL DEPOSIT</th>
                                                                                <th class="text-end" style="width: 15%;">
                                                                                    PROFILE STATUS</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>

                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <div class="p-0 tab-pane" id="tab-info">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="card custom-card">
                                                    <div class="card-header">
                                                        <div class="card-title">Bank Details</div>
                                                    </div>
                                                    <div class="card-body">
                                                        <ul class="list-group">
                                                            <li
                                                                class="list-group-item d-flex justify-content-between align-items-center fw-medium">
                                                                ACCOUNT HOLDER NAME
                                                                <span>{{ $bank_details->ClientName ?? '' }}</span>
                                                            </li>
                                                            <li
                                                                class="list-group-item d-flex justify-content-between align-items-center fw-medium">
                                                                BANK NAME
                                                                <span>{{ $bank_details->bankName ?? '' }}</span>
                                                            </li>
                                                            <li
                                                                class="list-group-item d-flex justify-content-between align-items-center fw-medium">
                                                                ACCOUNT NUMBER
                                                                <span>{{ $bank_details->accountNumber ?? '' }}</span>
                                                            </li>
                                                            <li
                                                                class="list-group-item d-flex justify-content-between align-items-center fw-medium">
                                                                IFSC CODE
                                                                <span>{{ $bank_details->code ?? '' }}</span>
                                                            </li>
                                                            <li
                                                                class="list-group-item d-flex justify-content-between align-items-center fw-medium">
                                                                SWIFT CODE
                                                                <span>{{ $bank_details->swift_code ?? '' }}</span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="card custom-card">
                                                    <div class="card-header">
                                                        <div class="card-title">Client Documents</div>
                                                    </div>
                                                    <?php
                                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                                                    $pdfExtensions = ['pdf'];
                                                    $mimeTypes = [
                                                        'jpeg' => 'image/jpeg',
                                                        'jpg' => 'image/jpeg',
                                                        'png' => 'image/png',
                                                        'pdf' => 'application/pdf',
                                                        'gif' => 'image/gif',
                                                    ];
                                                    ?>

                                                    <div class="card-body">
                                                        <?php foreach ($kyc_details as $kyc): ?>
                                                        <?php
                                                        $files = [
                                                            'front_image' => strtolower(pathinfo($kyc->front_image, PATHINFO_EXTENSION)),
                                                            'kyc_frontside' => strtolower(pathinfo($kyc->kyc_frontside, PATHINFO_EXTENSION)),
                                                            'kyc_backside' => strtolower(pathinfo($kyc->kyc_backside, PATHINFO_EXTENSION)),
                                                        ];
                                                        $statusText = $kyc->status == '1' ? 'Approved' : ($kyc->status == '2' ? 'Rejected' : 'Pending');
                                                        [$badgeClass, $icon] = getBadgeProperties($kyc->status);
                                                        ?>

                                                        <?php if ($kyc->kyc_type == 'Address Proof' || $kyc->kyc_type == 'ID Proof'): ?>
                                                        <div
                                                            class="m-0 overflow-visible media card-body media-xs d-sm-flex d-block justify-content-between">
                                                            <div class="mb-2 d-flex mb-sm-0">
                                                                <div class="my-auto media-body valign-middle"
                                                                    style="max-width: 100px; display: flex; flex-direction: column;">
                                                                    <?php foreach (['front_image' => $files['front_image'], 'kyc_frontside' => $files['kyc_frontside'], 'kyc_backside' => $files['kyc_backside']] as $key => $extension): ?>
                                                                    <?php if (in_array($extension, $imageExtensions) || in_array($extension, $pdfExtensions)): ?>
                                                                    <button
                                                                        class="mt-1 btn btn-lg btn-icon btn-light text-info me-2"
                                                                        data-bs-toggle="modal" data-bs-target="#kycModal"
                                                                        data-bs-kyc="{{ asset('storage' . $kyc->$key) }}"
                                                                        data-bs-type="{{ $mimeTypes[$extension] }}">
                                                                        <i
                                                                            class="ri-{{ in_array($extension, $pdfExtensions) ? 'file-pdf-2-line' : 'image-2-fill' }}"></i>
                                                                    </button>
                                                                    <?php endif; ?>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                                <div class="my-auto media-body valign-middle">
                                                                    <a href=""
                                                                        class="fw-semibold text-dark">{{ $kyc->kyc_type }}</a>
                                                                    <p class="m-0 text-muted">
                                                                        {{ $kyc->registered_date_js }}</p>
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="my-auto overflow-visible media-body valign-middle text-sm-end">
                                                                <span
                                                                    class="badge {{ $badgeClass }}">{!! $icon !!}
                                                                    <?= $statusText ?>
                                                                </span>
                                                            </div>
                                                            <div
                                                                class="my-auto overflow-visible media-body valign-middle text-sm-end">
                                                                <?php if ($kyc->status == 2 || $kyc->status == 0) { ?>
                                                                <button class="btn btn-lg btn-icon btn-light text-success"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    title="Approve"
                                                                    onclick="takeAction('{{ $kyc->id }}','{{ $kyc->email }}',1)">
                                                                    <i class="ri-check-line"></i>
                                                                </button>
                                                                <?php }
                                    if ($kyc->status == 1 || $kyc->status == 0) { ?>
                                                                <button class="btn btn-lg btn-icon btn-light text-danger"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    title="Reject"
                                                                    onclick="takeAction('{{ $kyc->id }}','{{ $kyc->email }}',2)">
                                                                    <i class="ri-close-circle-line"></i>
                                                                </button>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>

                                                </div>
                                                <?php if (!isset($kyc)) { ?>
                                                <form method="post" enctype="multipart/form-data">
                                                    <div class="card custom-card">
                                                        <div class="card-header">
                                                            <div class="card-title">Upload Documents</div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="mb-3">
                                                                <label for="formFile" class="form-label">ID Proof Front
                                                                    Side</label>
                                                                <input class="form-control" id="formFile" name="image"
                                                                    type="file" accept="image/png,image/jpeg">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="formFile" class="form-label">ID Proof Back
                                                                    Side</label>
                                                                <input class="form-control" id="formFile" name="image1"
                                                                    type="file" accept="image/png,image/jpeg">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="formFile" class="form-label">Address Proof
                                                                    Front Side</label>
                                                                <input class="form-control" id="formFile" name="image2"
                                                                    type="file" accept="image/png,image/jpeg">
                                                            </div>
                                                        </div>
                                                        <div class="card-footer">
                                                            <input type="hidden" name="email"
                                                                value="{{ $user->email }}">
                                                            <input type="submit" href="javascript:void(0);"
                                                                class="btn btn-primary d-grid" value="Upload Document"
                                                                name="upload_kyc">
                                                        </div>
                                                    </div>
                                                </form>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-0 tab-pane" id="tab-profile">
                                        <div class="row">
                                            <div class="col-lg-5 col-xl-4 col-xl-12 col-sm-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="text-center">
                                                            <div class="userprofile">
                                                                <div class="avatar userpic avatar-rounded">
                                                                    <img src="/admin_assets/assets/images/users/client.jpeg"
                                                                        alt="img" style="width:100px">
                                                                </div>
                                                                <h3 class="mb-2 username">{{ $user->fullname }}</h3>
                                                                <p class="mb-1 text-muted">{{ $user->email }}</p>
                                                                <form method="post"
                                                                    action="{{ route('admin.sendPasswordResetLink') }}">
                                                                    @csrf
                                                                    <input type="hidden" name="txtemail"
                                                                        value="{{ $user->email }}">
                                                                    <button class="btn btn-primary" type="submit"
                                                                        name="btn-submit" value="reset">Send Reset
                                                                        Password Link</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-7 col-xl-8 col-xl-12 col-sm-12">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- COL-END -->
            </div>
            <!-- End:: row-1 -->
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $(document).on('click', '.ibToggle', function() {
                var data = $(this).data();
                $("#clientName,#clientEmail").html("");
                $("#clientName").html(data.fullname)
                $("#clientEmail").html(data.email)
                $("#client_id").val(data.enc)
                $("[name='ib_status']").val(data.ib_status).trigger("change");
                $("[name='ib_group']").val(data.ib_group).trigger("change");
                // myModal.show();
            });
            $('#tableDeposit').DataTable({
                "ajax": {
                    "url": "/admin/ajax",
                    "type": "GET",
                    data: {
                        action: 'getLatestDeposit',
                        id: '{{ $user->id }}'
                    },
                },
                columns: [{
                        data: 'created_on',
                        name: 'date'
                    },
                    {
                        data: 'from_to',
                        name: 'from_to'
                    },
                    {
                        data: 'payment_method',
                        name: 'method'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    }
                ]
            });
            $('#tableWithdrawal').DataTable({
                "ajax": {
                    "url": "/admin/ajax",
                    "type": "GET",
                    data: {
                        action: 'getLatestWithdrawal',
                        id: '{{ $user->id }}'
                    },
                },
                columns: [{
                        data: 'created_on',
                        name: 'date'
                    },
                    {
                        data: 'from_to',
                        name: 'from_to'
                    },
                    {
                        data: 'payment_method',
                        name: 'method'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'fee',
                        name: 'fee'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    }
                ]
            });
            $('#tableInternalTransfer').DataTable({
                "ajax": {
                    "url": "/admin/ajax",
                    "type": "GET",
                    data: {
                        action: 'getLatestTransfer',
                        id: '{{ $user->id }}'
                    },
                },
                columns: [{
                        data: 'created_on',
                        name: 'date'
                    },
                    {
                        data: 'from',
                        name: 'from'
                    },
                    {
                        data: 'to',
                        name: 'to'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    }
                ]
            });
        });
        // $("#ibRequestForm").submit(function(e) {
        //     e.preventDefault();
        //     $.ajax({
        //         url: "/admin/api/ajax",
        //         type: "POST",
        //         data: $("#ibRequestForm").serialize(),
        //         success: function(data) {
        //             if (data == "true") {
        //                 swal.fire({
        //                     icon: "success",
        //                     title: "IB Request Successfully Updated",
        //                 }).then((val) => {
        //                     location.reload();
        //                 });
        //             } else {
        //                 swal.fire({
        //                     icon: "error",
        //                     title: "Something went wrong.",
        //                     text: "Please try again or contact support."
        //                 }).then((val) => {
        //                     location.reload();
        //                 });
        //             }
        //         }
        //     });
        // });
        $("#createMT5Form").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "/admin/api/accounts",
                type: "POST",
                data: $("#createMT5Form").serialize(),
                success: function(data) {
                    if (data == "true") {
                        swal.fire({
                            icon: "success",
                            title: "Account Successfully Updated",
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
        });;
        $('#kycModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var fileSrc = button.data('bs-kyc');
            var fileType = button.data('bs-type');
            var modal = $(this);
            modal.find('#kycFile').attr('src', fileSrc);
            modal.find('#kycFile').attr('type', fileType);
        });

        function takeAction(id, email, status) {
            Swal.fire({
                title: `Are you sure you want to ${status === 1 ? "approve" : "reject"} this Document?`,
                html: `
            <form id="updateKYCForm" method="post" action="{{ route('admin.updateKyc') }}">
                @csrf
             <input type="hidden" name="id" value="${id}">
              <input type="hidden" name="email" value="${email}">
              <input type="hidden" name="status" value="${status}">
              <input type="hidden" name="action" value="update_kyc">
              <div class="mt-2 col-12 text-start">
                  <textarea id="description" name="description" rows="3" class="mt-2 form-control" placeholder="Add a description"></textarea>
              </div>
              </form>
          `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Submit',
                preConfirm: () => {
                    const description = document.querySelector('#updateKYCForm textarea').value;
                    if (!description) {
                        Swal.showValidationMessage('Please add a comment');
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelector('#updateKYCForm').submit();
                }
            });
        }
        $('#ibList .nav-link').on('click', function(e) {
            e.preventDefault();
            let tier = $(this).data('tier');
            $.ajax({
                url: '/admin/ajax',
                type: 'GET',
                data: {
                    action: 'getIbTierData',
                    id: '{{ $user->id }}',
                    tier: tier
                },
                success: function(response) {
                    $('#content').html(response);
                },
                error: function(xhr) {
                    console.error('Error loading content:', xhr);
                    $('#content').html('<p>Sorry, something went wrong.</p>');
                }
            });
        });
        $(document).ready(function() {
            $('.togglePassword').on('click', function() {
                var passwordField = $('#clientpassword');
                var passwordFieldType = passwordField.attr('type');
                if (passwordFieldType === 'password') {
                    passwordField.attr('type', 'text');
                    $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.4/clipboard.min.js"></script>
    <script>
        var clipboard = new ClipboardJS('#ibClient');
        clipboard.on('success', function(e) {
            Swal.fire({
                icon: "success",
                title: "IB Link Copied"
            })
        });
        $(document).ready(function() {
            $('[role="tab"]').click(function() {
                console.log($(this).attr("href"));
                if ($(this).attr("href")) {
                    location.hash = $(this).attr("href");
                } else if ($(this).data("bs-target")) {
                    location.hash = $(this).data("bs-target");
                }
            });
            if (location.hash) {
                var tab = location.hash;
                if ($('a[href="' + tab + '"]').length) {
                    var triggerEl = document.querySelector('a[href="' + tab + '"]')
                    bootstrap.Tab.getInstance(triggerEl).show() // Select tab by name
                }
            }
        })
    </script>
     <script>
        $(document).ready(function() {

            let level = 1;

            var dTtable = $('#ajaxDatatable').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: '/admin/getClientIbProfile',
                    type: 'GET',
                    data: function(d) {
                        d.userId = {!! json_encode($userid) !!}; // Pass the user ID
                        d.level = level; // Pass the current level
                        console.log('Sending data:', d);
                    },
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                columns: [
                    { data: 'email', name: 'email' },
                    { data: 'total_accounts', name: 'total_accounts' },
                    { data: 'total_deposit', name: 'total_deposit' },
                    { data: 'profile_status', name: 'profile_status' },
                ],
                order: [
                    [0, "desc"]
                ]
            });

            // Handle button click events
            $('.client-level').on('click', function(e) {
                e.preventDefault();

                $('.client-level').removeClass('active');
                $(this).addClass('active');

                level = $(this).data('level');
                console.log(level);

                dTtable.ajax.reload();
            });


        });

    </script>
    <script>

    $(document).ready(function() {
        window.statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
        window.editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
        // Encode PHP data as JSON for JavaScript compatibility
        var userData = @json($user);

        $('.statusToggle').on('click', function() {
            // Clear any existing content in modal
            $("#userName,#userEmail").html("");

            // Populate modal with user data
            $("#userName").html(userData.fullname || 'N/A');
            $("#userEmail").html(userData.email || 'N/A');
            $("#user_id").val(userData.id);
            $("#user_status").prop("checked", userData.status == 1);
            $("#email_status").prop("checked", userData.email_confirmed == 1);
            $("#kyc_verify").prop("checked", userData.kyc_verify == 1);

            // Show the modal
            if (typeof statusModal !== 'undefined') {
                statusModal.show();
            } else {
                console.error('statusModal is not defined');
            }
        });

        $('.resendToggle').off('click', '.resendVerificationEmail');
        $('.resendToggle').on('click', '.resendVerificationEmail', function() {
            $("#userName,#userEmail").html("");
            $("#userName").html(userData.fullname);
            $("#userEmail").html(userData.email);
            $("#user_id").val(userData.id);
            $("#user_status").prop("checked", userData.status == 1);
            $("#email_status").prop("checked", userData.email_confirmed == 1);
            $("#kyc_verify").prop("checked", (userData.kyc_verify == 1));
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
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Include CSRF token in the header
                        },
                        data: {
                            action: 'resendVerificationEmail',
                            id: userData.id
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
                }
                });
        });

        $(document).on('click', '.viewClient', function() {
            location.href = "/admin/client_details/" + userData.id;
        });

        $(document).off('click', '.editClient')
        $(document).on('click', '.editClient', function() {
            $.ajax({
                url: "/admin/ajax",
                type: "GET",
                cache: false,
                data: {
                    "action": "getClientDetails",
                    "id": userData.id
                },
                success: function(resp) {

                    $.each(resp, function(key, value) {

                        if (key === 'country_code') {
                            value = value.replace('', '+');
                        }
                        if (key === 'telephone') {
                            value = value.replace('+', '');
                        }
                        $('#editUserForm [name="' + key + '"]').val(
                            value);
                    });
                    $('#editUserForm [name="country_code"]').trigger('change');
                }
            });
            editUserModal.show();
        });
        $(document).off('click', '.switchClient')
        $(document).on('click', '.switchClient', function(e) {
            e.preventDefault(); // Prevent default behavior
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
                    client_id: userData.id, // Pass the correct client ID
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
