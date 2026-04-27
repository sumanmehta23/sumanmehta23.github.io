@extends('layouts.admin.admin')
@section('content')
    <style>
        .pointer,
        .emailActionToggle,
        .statusToggle,
        .viewClient {
            cursor: pointer;
        }

        .switchClient {
            cursor: pointer;
        }

        .editClient {
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
                                    <option value="<?php echo $type['id']; ?>"><?php echo $type['ticket_type']; ?>
                                    </option>
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
                    <input type="hidden" name="client_id" id="client_id" value="{{ $user->id }}">
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
     <!-- Add Note Modal -->
    <div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.client.notes.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $user->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addNoteModalLabel">Add Note for {{ $user->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="noteContent" class="form-label">Note</label>
                            <textarea class="form-control" id="noteContent" name="note" rows="5" required placeholder="Enter your note here..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Note</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Notes History Modal -->
    <div class="modal fade" id="notesHistoryModal" tabindex="-1" aria-labelledby="notesHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notesHistoryModalLabel">
                        Notes History -
                        <span id="notesHistoryClientName">
                            {{ $user->fullname ?? $user->name ?? $user->email }}
                        </span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="notesHistoryContent">
                        @if(isset($client_notes) && count($client_notes) > 0)
                            <div class="timeline">
                                @foreach($client_notes as $note)
                                    <div class="mb-3 card">
                                        <div class="card-body">
                                            <div class="mb-2 d-flex justify-content-between align-items-start">
                                                <h6 class="mb-0">
                                                    <i class="fe fe-user text-primary"></i>
                                                    {{ $note->admin->username ?? 'Admin' }}
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="fe fe-clock"></i>
                                                    {{ $note->created_at->format('M d, Y h:i A') }}
                                                </small>
                                            </div>
                                            <p class="mb-0">{{ $note->note }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-5 text-center">
                                <i class="fe fe-file-text" style="font-size: 48px; color: #ccc;"></i>
                                <p class="mt-3 text-muted">No notes available for this client.</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ibModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="ibModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ url('/admin/bulkIbApprove') }}" id="ibRequestForm" method="post">
                    @csrf
                    <input type="hidden" name="client_id" id="client_id" value="{{ $user->ib ? $user->ib->id : $user->id }}">
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
                                        <option value="{{ $gp->id }}">{{ $gp->plan->ib_cat_name }}</option>
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
                                        <div class="wideget-user-desc d-flex flex-column flex-md-row">
                                            <div class="wideget-user-img d-flex align-items-center ">
                                                <img src="/admin_assets/assets/images/users/client.jpeg" alt="img"
                                                    style="width:100px">
                                            </div>
                                            <div class="user-wrap">
                                                <h4 class="fw-normal text-uppercase">{{ $user->fullname }}</h4>
                                                <h6 class="mb-3 fw-normal">
                                                    <div class="d-flex align-items-center">
                                                        <span class="px-2"><span
                                                                class="fi fis fi-{{ strtolower(@$country_code->country_alpha) }} me-2"></span>{{ $user->country }}</span>
                                                        |
                                                        <span class="px-2 d-flex flex-column">{!! $user->kyc_verify == 0
                                                            ? '<span class="badge bg-outline-danger">Pending KYC</span>'
                                                            : ($user->status == 1
                                                                ? '<span class="badge bg-outline-success">KYC Verified</span>'
                                                                : '') !!}
                                                         @php
                                                        $applicantId = $kyc_log->callback_payload['applicantId']
                                                            ?? $kyc_log->callback_payload['id']
                                                            ??
                                                            $kyc_log->callback_payload['applicant_id']
                                                            ?? $kyc_log->callback_payload['inspectionId']
                                                            ?? null;
                                                    @endphp

                                                    @if($kyc_log && $applicantId)
                                                        <a class="mb-1 fs-12"
                                                        id="sumsub-info"
                                                        href="https://cockpit.sumsub.com/checkus/#/applicant/{{ $applicantId }}/basicInfo?clientId={{ config('services.sumsub.clientId') }}">
                                                            {{ $applicantId }}
                                                        </a>
                                                    @endif

                                                        </span>
                                                        |
                                                        <span
                                                            class="px-2"><strong>DOJ:</strong>{{ date('d M Y h:i A', strtotime($user->created_at)) }}</span>
                                                        |
                                                        <span class="px-2">{!! $user->status == 0
                                                            ? '<span class="badge bg-outline-danger">Inactive</span>'
                                                            : ($user->status == 1
                                                                ? '<span class="badge bg-outline-success">Active</span>'
                                                                : '') !!}</span>
                                                    </div>
                                                </h6>
                                                <div id="sumsub-websdk-container" hidden></div>
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
                                                <div class="pt-1 mt-1 border-2 row border-top border-default">
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <button
                                                                class="btn btn-icon bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                <i class="ri-code-block"></i>
                                                            </button>
                                                            <div>
                                                                <div class="mb-0 text-muted fs-11">Referral Code:</div>
                                                                <div class="mb-1 fs-12">{{ @$user->ib->referral_code }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                       @php
                                                        $affiliateParent = $user->affiliateParent();
                                                        @endphp
                                                        @if($affiliateParent)
                                                            <div class="col-6">
                                                                <div class="d-flex align-items-center">
                                                                    <button class="btn btn-icon bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                        <i class="ri-user-star-line"></i>
                                                                    </button>
                                                                    <div>
                                                                        <div class="mb-0 text-muted fs-11">Tracknow Affiliate:</div>
                                                                        <div class="mb-1 fs-12">
                                                                            {{($affiliateParent->fullname ?? 'N/A') . ' (' . ($affiliateParent->custom_id ?? 'N/A') . ')' }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0 mt-3 ms-auto w-100 w-md-auto mt-md-0" style="max-width: 400px;">
                                                @if(isset($client_notes) && count($client_notes) > 0)
                                                    @php
                                                        $lastNote = $client_notes->first();
                                                    @endphp
                                                    <div class="mb-2 card">
                                                        <div class="p-2 card-body">
                                                            <div class="gap-1 mb-1 d-flex flex-column flex-sm-row justify-content-between align-items-start">
                                                                <small class="mb-0 fw-bold text-primary" style="font-size: 14px;">
                                                                    <i class="fe fe-user"></i> {{ $lastNote->admin->username ?? 'Admin' }}
                                                                </small>
                                                                <small class="text-muted text-nowrap" style="font-size: 14px;">
                                                                    <i class="fe fe-clock"></i> {{ $lastNote->created_at->diffForHumans() }}
                                                                </small>
                                                            </div>
                                                            <div style="max-height: 150px; overflow-y: auto;">
                                                                <p class="mb-0 text-muted" style="font-size: 14px; word-wrap: break-word;">
                                                                    {{ $lastNote->note }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="gap-2 d-flex flex-column flex-sm-row">
                                                    <button type="button" class="btn btn-primary flex-fill flex-sm-grow-0" style="font-size: 12px; padding: 4px 10px; white-space: nowrap;" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                                        <i class="fe fe-plus" style="font-size: 12px;"></i> Add Note
                                                    </button>
                                                    <button type="button" class="btn btn-info flex-fill flex-sm-grow-0" style="font-size: 12px; padding: 4px 10px; white-space: nowrap;" data-bs-toggle="modal" data-bs-target="#notesHistoryModal">
                                                        <i class="fe fe-file-text" style="font-size: 12px;"></i> Notes History
                                                    </button>
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
                                                    class="">TRANSACTIONS</a>
                                            </li>
                                            <?php if (!empty($ib_details)): ?>
                                            @can('ib:viewAny')
                                                <li><a href="#tab-ib" data-bs-toggle="tab" class="">IB PROFILE</a></li>
                                            @endcan
                                            <?php endif; ?>
                                            <li><a href="#tab-info" data-bs-toggle="tab" class="">ADDITIONAL
                                                    INFO</a></li>
                                            <li><a href="#tab-profile" data-bs-toggle="tab" class="">PROFILE
                                                    SETTINGS</a>
                                            </li>
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
                                                                <div class="col-xl-2">
                                                                    <h4 class="mb-3 text-muted fw-normal">OLD TOTAL DEPOSIT
                                                                    </h4>
                                                                    <h4 class="fw-normal">@money($total_wd)

                                                                    </h4>
                                                                </div>
                                                            @endcan
                                                            @can('wallet_withdraw:viewAny')
                                                                <div class="col-xl-2">
                                                                    <h4 class="mb-3 text-muted fw-normal">OLD TOTAL WITHDRAW
                                                                    </h4>
                                                                    <h4 class="fw-normal">@money($total_ww)</h4>
                                                                </div>
                                                            @endcan
                                                            <div class="col-xl-2">
                                                                <h4 class="mb-3 text-muted fw-normal">WALLET</h4>
                                                                <?php if ($user->wallet_enabled): ?>

                                                                <h4 class="fw-normal">${{ $wallet_balance }} </h4>
                                                                <?php else: ?>
                                                                <button type="button"
                                                                    class="btn btn-outline-dark btn-sm disabled">
                                                                    No Wallet
                                                                </button>
                                                                <?php endif; ?>
                                                            </div>
                                                            @can('wallet_deposit:viewAny')
                                                                <div class="col-xl-2">
                                                                    <h4 class="mb-3 text-muted fw-normal">NEW TOTAL DEPOSIT
                                                                    </h4>
                                                                    <h4 class="fw-normal">@money($total_ntd)

                                                                    </h4>
                                                                </div>
                                                            @endcan
                                                            @can('wallet_withdraw:viewAny')
                                                                <div class="col-xl-2">
                                                                    <h4 class="mb-3 text-muted fw-normal">NEW TOTAL WITHDRAW
                                                                    </h4>
                                                                    <h4 class="fw-normal">@money($total_ntw)</h4>
                                                                </div>
                                                            @endcan
                                                            <div class="col-xl-2">
                                                                <h4 class="mb-3 text-muted fw-normal">FLOATING BALANCE
                                                                </h4>
                                                                <h4 class="fw-normal">@money(($user->accounts()->withTrashed()->where('demo', 0)->where('balance', '>', 0)->sum('balance')))</h4>
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
                                                                <?php    if (empty($live_accounts)) { ?>
                                                                <div class="my-4 text-muted">No Live Accounts Found.</div>
                                                                <?php    } ?>
                                                                <?php    foreach ($live_accounts as $acc): ?>
                                                                <div
                                                                    class="my-2 border border-dashed col-xl-4 col-lg-6 border-3">
                                                                    <div>
                                                                        <div
                                                                            class="pb-2 mt-2 mb-2 border-2 row border-bottom border-bottom-dashed">
                                                                            <div class="d-flex w-50 flex-column">
                                                                                <img src="/admin_assets/assets/images/mt5.png"
                                                                                    alt="card img" style="width:50px;">
                                                                                <div class="mt-1 fs-18 text-black-50 fw-bold">
                                                                                    {{ $acc->code }}
                                                                                </div>
                                                                            </div>
                                                                            <div class="d-flex flex-column align-items-end w-50">
                                                                                <span class="mt-2 h4 fw-normal">@money($acc->balance)</span>
                                                                                @if($acc->deleted_at && $acc->deletion_type == 'soft')
                                                                                    <div class="mt-1 fs-18 text-danger fw-bold">
                                                                                        Soft Deleted
                                                                                    </div>
                                                                                @elseif($acc->deleted_at && ($acc->deletion_type == 'delete' || $acc->deletion_type == 'not_found_in_mt5'))
                                                                                    <div class="mt-1 fs-18 text-danger fw-bold">
                                                                                        Deleted
                                                                                    </div>
                                                                                @elseif($acc->deleted_at && $acc->deletion_type == 'archive')
                                                                                    <div class="mt-1 fs-18 text-danger fw-bold">
                                                                                        Archived
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="d-flex justify-content-between">
                                                                            <div>
                                                                                <div class="fw-bold fs-12">
                                                                                    {{ $acc->accountType->ac_name }}
                                                                                </div>
                                                                                <div class="mb-2 fw-normal fs-10">
                                                                                    {{ $acc->accountType->ac_group }}
                                                                                </div>
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
                                                                <?php    endforeach; ?>
                                                            </div>
                                                        @endcan
                                                        @can('account:viewDemoAccounts')
                                                            <div class="mt-3 row">
                                                                <div class="d-flex justify-content-between">
                                                                    <h4>DEMO ACCOUNTS</h4>
                                                                </div>
                                                            </div>
                                                            <div class="px-2 row">
                                                                @if (empty($demo_accounts) || $demo_accounts->isEmpty())
                                                                    <div class="my-4 text-muted">No Demo Accounts Found.</div>
                                                                @endif
                                                                @foreach ($demo_accounts as $acc)
                                                                    <div
                                                                        class="my-2 border border-dashed col-xl-4 col-lg-6 border-3">
                                                                        <div>
                                                                            <div
                                                                                class="pb-2 mt-2 mb-2 border-2 row border-bottom border-bottom-dashed">
                                                                                <div class="d-flex w-50 flex-column">
                                                                                    @if ($acc->platform === App\Enums\PlatformEnum::MT5->value)
                                                                                        <img src="/admin_assets/assets/images/mt5.png" alt="card img" style="width:50px;">
                                                                                    @elseif($acc->platform === App\Enums\PlatformEnum::X9->value)
                                                                                        <img src="/assets/images/x9.png" alt="card img" style="width:50px;">
                                                                                    @endif

                                                                                    <div class="mt-1 fs-18 text-black-50 fw-bold">
                                                                                        {{ $acc->code }}
                                                                                        <span class="text-white badge bg-info ms-2">DEMO</span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex justify-content-end w-50">
                                                                                    <span
                                                                                        class="mt-2 h4 fw-normal">${{ $acc->balance }}</span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="d-flex justify-content-between">
                                                                                <div>
                                                                                    <div class="fw-bold fs-12">
                                                                                        {{ $acc->accountType->ac_name }}
                                                                                    </div>
                                                                                    <div class="mb-2 fw-normal fs-10">
                                                                                         {{ $acc->accountType->ac_group }}
                                                                                    </div>
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
                                                                @endforeach
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
                                                                        $success =
                                                                            $user->status == 0
                                                                                ? 'bg-success'
                                                                                : 'bg-success text-white';
                                                                    }
                                                                @endphp
                                                                <div class="col-lg-5 col-xl-4 col-xl-12 col-sm-12">
                                                                    <div class="card-body d-flex">
                                                                        @can('client:update')
                                                                            <div class="statusToggle"
                                                                                data-status="{{ $user->status }}">
                                                                                @if ($user->status == 0)
                                                                                    <div class="badge text-danger {{ $success }}"
                                                                                        data-bs-toggle="tooltip"
                                                                                        title="Inactive User">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                                            width="25" height="25"
                                                                                            viewBox="0 0 24 24" fill="none"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                            stroke-linecap="round"
                                                                                            stroke-linejoin="round" size="25"
                                                                                            class="tabler-icon tabler-icon-user-scan">
                                                                                            <path
                                                                                                d="M10 9a2 2 0 1 0 4 0a2 2 0 0 0 -4 0">
                                                                                            </path>
                                                                                            <path d="M4 8v-2a2 2 0 0 1 2 -2h2">
                                                                                            </path>
                                                                                            <path d="M4 16v2a2 2 0 0 0 2 2h2">
                                                                                            </path>
                                                                                            <path d="M16 4h2a2 2 0 0 1 2 2v2">
                                                                                            </path>
                                                                                            <path d="M16 20h2a2 2 0 0 0 2 -2v-2">
                                                                                            </path>
                                                                                            <path
                                                                                                d="M8 16a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2">
                                                                                            </path>
                                                                                        </svg>
                                                                                    </div>
                                                                                @elseif ($user->status == 1)
                                                                                    <div class="badge text-success {{ $success }}"
                                                                                        data-bs-toggle="tooltip"
                                                                                        title="Active User">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                                            width="25" height="25"
                                                                                            viewBox="0 0 24 24" fill="none"
                                                                                            stroke="currentColor"
                                                                                            stroke-width="1.5"
                                                                                            stroke-linecap="round"
                                                                                            stroke-linejoin="round" size="25"
                                                                                            class="tabler-icon tabler-icon-user-scan">
                                                                                            <path
                                                                                                d="M10 9a2 2 0 1 0 4 0a2 2 0 0 0 -4 0">
                                                                                            </path>
                                                                                            <path d="M4 8v-2a2 2 0 0 1 2 -2h2">
                                                                                            </path>
                                                                                            <path d="M4 16v2a2 2 0 0 0 2 2h2">
                                                                                            </path>
                                                                                            <path d="M16 4h2a2 2 0 0 1 2 2v2">
                                                                                            </path>
                                                                                            <path d="M16 20h2a2 2 0 0 0 2 -2v-2">
                                                                                            </path>
                                                                                            <path
                                                                                                d="M8 16a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2">
                                                                                            </path>
                                                                                        </svg>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        @endcan
                                                                        @if ($user->email_confirmed == 0)
                                                                            <div class="resendToggle"
                                                                                data-status="{{ $user->email_confirmed }}">
                                                                                <div class="badge text-danger"
                                                                                    data-bs-toggle="tooltip"
                                                                                    title="Email Not Verified">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                                        width="25" height="25"
                                                                                        viewBox="0 0 24 24" fill="none"
                                                                                        stroke="#FFCC80" stroke-width="1.5"
                                                                                        stroke-linecap="round"
                                                                                        stroke-linejoin="round" size="25"
                                                                                        class="tabler-icon tabler-icon-mail-x">
                                                                                        <path
                                                                                            d="M13.5 19h-8.5a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v6">
                                                                                        </path>
                                                                                        <path d="M3 7l9 6l9 -6"></path>
                                                                                        <path d="M22 22l-5 -5"></path>
                                                                                        <path d="M17 22l5 -5"></path>
                                                                                    </svg>
                                                                                </div>
                                                                                <div class='badge text-info pointer resendVerificationEmail'
                                                                                    data-bs-toggle='tooltip'
                                                                                    title='Resend Verification Email'>
                                                                                    <svg class='w-64 h-64' fill='currentColor'
                                                                                        width='25' height='25'
                                                                                        xmlns='http://www.w3.org/2000/svg'
                                                                                        id='mdi-email-sync-outline'
                                                                                        viewBox='0 0 24 24'>
                                                                                        <path
                                                                                            d='M3 4C1.9 4 1 4.9 1 6V18C1 19.1 1.9 20 3 20H13.5A6.5 6.5 0 0 1 13 18H3V8L11 13L19 8V11A6.5 6.5 0 0 1 19.5 11A6.5 6.5 0 0 1 21 11.18V6C21 4.9 20.1 4 19 4H3M3 6H19L11 11L3 6M19 12L16.75 14.25L19 16.5V15C20.38 15 21.5 16.12 21.5 17.5C21.5 17.9 21.41 18.28 21.24 18.62L22.33 19.71C22.75 19.08 23 18.32 23 17.5C23 15.29 21.21 13.5 19 13.5V12M15.67 15.29C15.25 15.92 15 16.68 15 17.5C15 19.71 16.79 21.5 19 21.5V23L21.25 20.75L19 18.5V20C17.62 20 16.5 18.88 16.5 17.5C16.5 17.1 16.59 16.72 16.76 16.38L15.67 15.29Z'>
                                                                                        </path>
                                                                                    </svg>
                                                                                </div>
                                                                            </div>
                                                                        @else
                                                                            <div class="statusToggle"
                                                                                data-status="{{ $user->email_confirmed }}">
                                                                                <div class="badge text-success"
                                                                                    data-bs-toggle="tooltip"
                                                                                    title="Email Verified">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                                        width="25" height="25"
                                                                                        viewBox="0 0 24 24" fill="none"
                                                                                        stroke="#81C784" stroke-width="1.5"
                                                                                        stroke-linecap="round"
                                                                                        stroke-linejoin="round" size="25"
                                                                                        color="#81C784"
                                                                                        class="tabler-icon tabler-icon-mail-check">
                                                                                        <path
                                                                                            d='M11 19h-6a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v6'>
                                                                                        </path>
                                                                                        <path d='M3 7l9 6l9 -6'></path>
                                                                                        <path d='M15 19l2 2l4 -4'></path>
                                                                                    </svg>
                                                                                </div>
                                                                            </div>
                                                                        @endif


                                                                        @can('client:update')
                                                                            <div class='editClient'
                                                                                data-enc='{{ $user->id }}'>
                                                                                <div class='badge text-secondary'
                                                                                    data-bs-toggle='tooltip' title='Edit Client'>
                                                                                    <svg xmlns='http://www.w3.org/2000/svg'
                                                                                        width='24' height='24'
                                                                                        viewBox='0 0 24 24' fill='none'
                                                                                        stroke='currentColor' stroke-width='2'
                                                                                        stroke-linecap='round'
                                                                                        stroke-linejoin='round'
                                                                                        class='icon icon-tabler icons-tabler-outline icon-tabler-edit text-secondary'>
                                                                                        <path stroke='none' d='M0 0h24v24H0z'
                                                                                            fill='none' />
                                                                                        <path
                                                                                            d='M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1' />
                                                                                        <path
                                                                                            d='M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z' />
                                                                                        <path d='M16 5l3 3' />
                                                                                    </svg>
                                                                                </div>
                                                                            </div>
                                                                        @endcan
                                                                        @can('client:impersonate')
                                                                            <div class="switchClient"
                                                                                data-enc="{{ $user->id }}">
                                                                                <div class="badge text-secondary"
                                                                                    data-bs-toggle="tooltip"
                                                                                    title="Switch Client">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                                        class="icon icon-tabler icon-tabler-arrows-shuffle"
                                                                                        width="24" height="24"
                                                                                        viewBox="0 0 24 24" fill="none"
                                                                                        stroke="currentColor" stroke-width="2"
                                                                                        stroke-linecap="round"
                                                                                        stroke-linejoin="round">
                                                                                        <path stroke='none' d='M0 0h24v24H0z'
                                                                                            fill='none' />
                                                                                        <path d='M18 4l3 3l-3 3' />
                                                                                        <path d='M18 20l3 -3l-3 -3' />
                                                                                        <path
                                                                                            d='M3 7h3a4 4 0 0 1 4 4a4 4 0 0 0 4 4h7' />
                                                                                        <path
                                                                                            d='M21 7h-7a4 4 0 0 0 -4 4a4 4 0 0 1 -4 4h-3' />
                                                                                    </svg>
                                                                                </div>
                                                                            </div>
                                                                        @endcan
                                                                    </div>
                                                                    <div class="modal fade" id="statusModal"
                                                                        data-bs-backdrop="static" data-bs-keyboard="false"
                                                                        tabindex="-1" aria-labelledby="statusModalLabel"
                                                                        aria-hidden="true">
                                                                        <div class="modal-dialog modal-dialog-centered">
                                                                            <div class="modal-content">
                                                                                <form action="#" id="statusUpdateForm"
                                                                                    method="post">
                                                                                    @csrf
                                                                                    <input type="hidden" name="action"
                                                                                        value="updateClientStatus">
                                                                                    <input type="hidden" name="client_id"
                                                                                        id="user_id" value="">
                                                                                    <div class="modal-header">
                                                                                        <h5 class="modal-title"
                                                                                            id="statusModalLabel">Update Status
                                                                                        </h5>
                                                                                        <button type="button"
                                                                                            class="btn-close"
                                                                                            data-bs-dismiss="modal"
                                                                                            aria-label="Close"></button>
                                                                                    </div>
                                                                                    <div
                                                                                        class="mb-0 modal-body custom-card card">
                                                                                        <div
                                                                                            class="d-flex align-items-center card-header w-100">
                                                                                            <div class="me-2">
                                                                                                <span
                                                                                                    class="avatar avatar-rounded">
                                                                                                    <img src="/admin_assets/assets/images/users/user.png"
                                                                                                        alt="img">
                                                                                                </span>
                                                                                            </div>
                                                                                            <div class="">
                                                                                                <div class="fs-15 fw-medium text-capitalize"
                                                                                                    id="userName"></div>
                                                                                                <p class="mb-0 text-muted fs-11"
                                                                                                    id="userEmail"></p>
                                                                                            </div>

                                                                                        </div>
                                                                                        <div class="card-body">
                                                                                            <div class="mb-3 row">
                                                                                                <div class="m-auto col-lg-4">
                                                                                                    <label
                                                                                                        class="form-label">User
                                                                                                        Status</label>
                                                                                                </div>
                                                                                                <div class="col-lg-8">
                                                                                                    <div
                                                                                                        class="form-check form-switch">
                                                                                                        <input
                                                                                                            class="form-check-input"
                                                                                                            type="checkbox"
                                                                                                            role="switch"
                                                                                                            name="status"
                                                                                                            id="user_status"
                                                                                                            checked>
                                                                                                        <label
                                                                                                            class="form-check-label"
                                                                                                            for="user_status"></label>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="mb-3 row">
                                                                                                <div class="m-auto col-lg-4">
                                                                                                    <label
                                                                                                        class="form-label">Email
                                                                                                        Confirmed</label>
                                                                                                </div>
                                                                                                <div class="col-lg-8">

                                                                                                    <div
                                                                                                        class="form-check form-switch">
                                                                                                        <input
                                                                                                            class="form-check-input"
                                                                                                            type="checkbox"
                                                                                                            role="switch"
                                                                                                            name="email_confirmed"
                                                                                                            id="email_status">
                                                                                                        <label
                                                                                                            class="form-check-label"
                                                                                                            for="email_status"></label>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="row">
                                                                                                <div class="m-auto col-lg-4">
                                                                                                    <label
                                                                                                        class="form-label">KYC
                                                                                                        Verification</label>
                                                                                                </div>
                                                                                                <div class="col-lg-8">

                                                                                                    <div
                                                                                                        class="form-check form-switch">
                                                                                                        <input
                                                                                                            class="form-check-input"
                                                                                                            type="checkbox"
                                                                                                            role="switch"
                                                                                                            name="kyc_verify"
                                                                                                            id="kyc_verify">
                                                                                                        <label
                                                                                                            class="form-check-label"
                                                                                                            for="kyc_verify"></label>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="modal-footer">
                                                                                        <button type="button"
                                                                                            class="btn btn-secondary"
                                                                                            data-bs-dismiss="modal">Close</button>
                                                                                        <button type="submit"
                                                                                            name="ibRequest" value="update"
                                                                                            class="btn btn-primary">Update</button>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal fade" id="editUserModal"
                                                                    data-bs-backdrop="static" data-bs-keyboard="false"
                                                                    tabindex="-1" aria-labelledby="editUserLabel"
                                                                    aria-hidden="true">
                                                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                                                        <form action="{{ route('admin.updateUser') }}"
                                                                            id="editUserForm" method="post">
                                                                            <div class="modal-content">
                                                                                @csrf
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title"
                                                                                        id="editUserLabel">
                                                                                        Update Client Details</h5>
                                                                                    <button type="button" class="btn-close"
                                                                                        data-bs-dismiss="modal"
                                                                                        aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="mb-0 modal-body custom-card card">
                                                                                    <input type="hidden" name="id">
                                                                                    <div class="row">
                                                                                        <div class="col-6">
                                                                                            <label for="input-label"
                                                                                                class="form-label">Email:</label>
                                                                                            <input type="text"
                                                                                                class="form-control"
                                                                                                name="email" required>
                                                                                        </div>
                                                                                        <div class="col-6">
                                                                                            <label for="input-label"
                                                                                                class="form-label">Full
                                                                                                Name:</label>
                                                                                            <input type="text"
                                                                                                class="form-control"
                                                                                                name="fullname" required>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <div class="col-6">
                                                                                            <label for="input-label"
                                                                                                class="form-label">Phone:</label>
                                                                                            <div class="input-group">
                                                                                                <div
                                                                                                    class="input-group-prepend w-25">
                                                                                                    <select
                                                                                                        class="form-select me-2 w-25 edit-countrycode"
                                                                                                        name="country_code"
                                                                                                        required>
                                                                                                        <option value="">
                                                                                                            Country
                                                                                                            Code</option>
                                                                                                        <?php    foreach ($countries as $country) { ?>
                                                                                                        <option
                                                                                                            value="+<?= $country['country_code'] ?>"
                                                                                                            data-flag="<?= strtolower($country['country_alpha']) ?>">
                                                                                                            +<?= $country['country_code'] ?>
                                                                                                            (<?= $country['country_name'] ?>)
                                                                                                        </option>
                                                                                                        <?php    } ?>
                                                                                                    </select>
                                                                                                </div>
                                                                                                <input type="text"
                                                                                                    class="form-control"
                                                                                                    id="phone_number"
                                                                                                    name="telephone"
                                                                                                    placeholder="Enter phone number">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-6">
                                                                                            <label for="input-label"
                                                                                                class="form-label">Country:</label>
                                                                                            <select class="form-select"
                                                                                                id="country" name="country"
                                                                                                required>
                                                                                                <option value="">Select
                                                                                                    Country
                                                                                                </option>
                                                                                                <?php    foreach ($countries as $country) { ?>
                                                                                                <option
                                                                                                    value="<?= $country['country_name'] ?>">
                                                                                                    <?= $country['country_name'] ?>
                                                                                                </option>
                                                                                                <?php    } ?>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <div class="col-6">
                                                                                            <label for="input-label"
                                                                                                class="form-label">Password:</label>
                                                                                            <div class="input-group">
                                                                                                <input type="password"
                                                                                                    class="form-control"
                                                                                                    name="password" id="editUserPassword">
                                                                                                <span class="cursor-pointer input-group-text togglePassword" id="toggleEditUserPassword">
                                                                                                    <i class="fa fa-eye-slash"></i>
                                                                                                </span>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-6">
                                                                                            <label for="input-label"
                                                                                                class="form-label">Confirm
                                                                                                Password:</label>
                                                                                            <div class="input-group">
                                                                                                <input type="password"
                                                                                                    class="form-control"
                                                                                                    id="editUserConfirmPassword"
                                                                                                    name="confirm_password">
                                                                                                <span class="cursor-pointer input-group-text togglePassword" id="toggleEditUserConfirmPassword">
                                                                                                    <i class="fa fa-eye-slash"></i>
                                                                                                </span>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div
                                                                                            class="col-lg-6 d-flex align-items-end">
                                                                                            <div
                                                                                                class="form-check form-switch">
                                                                                                <input class="form-check-input"
                                                                                                    type="checkbox"
                                                                                                    role="switch"
                                                                                                    name="email_notification">
                                                                                                <label
                                                                                                    class="form-check-label">Send
                                                                                                    Notification Email</label>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class="my-2 col-12">
                                                                                            @include('partials.password-validation-rules-admin', ['prefix' => 'edit-'])
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="submit" id="editUserSubmitBtn" name="updateUser"
                                                                                        value="update"
                                                                                        class="btn btn-primary">Update</button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
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
                                                                    <?php if (isset($user->ib)): ?>
                                                                    <?php if ($user->ib->status == 0): ?>
                                                                    <span
                                                                        class="badge bg-outline-warning text-end">Pending</span>
                                                                    <?php elseif ($user->ib->status == 1): ?>
                                                                    <span class="badge bg-outline-success text-end">Active
                                                                        IB</span>
                                                                    <?php endif; ?>
                                                                    <?php else: ?>
                                                                    <span class="badge bg-outline-info text-end">Not
                                                                        Requested</span>
                                                                    <?php endif; ?>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        @can('client:introducingBrokerButton')
                                                            <div class="card-body">

                                                                <?php if (!isset($user->ib) || ($user->ib && $user->ib->status != 1)): ?>
                                                                <?php if (!isset($user->ib) || ($user->ib && $user->ib->status == '0')): ?>
                                                                <button type="button"
                                                                    class="py-3 my-2 ibToggle ib-enroll btn btn-outline-dark btn-sm w-100 text-uppercase"
                                                                    data-bs-toggle="modal" data-bs-target="#ibModal"
                                                                    data-fullname="<?= e($user->fullname) ?>"
                                                                    data-email="<?= e($user->email) ?>"
                                                                    data-id="<?= $user->id ?>"
                                                                    data-ib_id="<?= $user->ib ? $user->ib->id : '' ?>"
                                                                    data-ib_status="<?= $user->ib ? $user->ib->status : '' ?>"
                                                                    data-ib_group="<?= $user->ib ? $user->ib->ib_plan_details_id : '' ?>">
                                                                    Approve Request
                                                                </button>
                                                                <?php else: ?>
                                                                <button type="button"
                                                                    class="py-3 my-2 ibToggle ib-enroll btn btn-outline-dark btn-sm w-100 text-uppercase"
                                                                    data-bs-toggle="modal" data-bs-target="#ibModal"
                                                                    data-fullname="<?= e($user->fullname) ?>"
                                                                    data-email="<?= e($user->email) ?>"
                                                                    data-id="<?= $user->id ?>"
                                                                    data-ib_id="<?= $user->ib ? $user->ib->id : '' ?>"
                                                                    data-ib_status="<?= $user->ib->status ?? '' ?>"
                                                                    data-ib_group="<?= $user->ib->ib_plan_details_id ?? '' ?>">
                                                                    Request To become IB
                                                                </button>
                                                                <?php endif; ?>
                                                                <?php else: ?>
                                                                <hr style="opacity:.1;">
                                                                <label class="col-form-label col-12 text-lg-start">
                                                                    Copy this IB referral link to share with potential clients!
                                                                </label>
                                                                <div class="mb-4 col-12">
                                                                    <div class="mb-2 input-group">
                                                                        <input type="text" class="form-control"
                                                                            id="pc-clipboard-1"
                                                                            value="https://<?= $_SERVER['HTTP_HOST'] ?>/register/ref?refercode=<?= $user->ib->referral_code ?>"
                                                                            readonly>
                                                                        <button class="btn btn-lg btn-primary cb"
                                                                            id="ibClient"
                                                                            data-clipboard-target="#pc-clipboard-1">
                                                                            <i class="fa fa-copy"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        @endcan
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
                                                                <a href="{{ route('admin.transactions.trading-deposit') }}?client_id={{ $user->id }}"
                                                                    class="btn btn-sm btn-primary-light">View All</a>
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
                                                                            <th scope="col">Action</th>
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
                                                                <a href="{{ route('admin.transactions.trading-withdrawal') }}?client_id={{ $user->id }}"
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
                                                                            <th scope="col">Action</th>
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
                                                                <a href="{{ route('admin.transactions.internal-transfer') }}?client_id={{ $user->id }}"
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
                                                                    <p class=" text-muted">IB PLAN</p>

                                                                    <h4>{{ getPlanNameByPlanId($acc_groups, $ib_details->ib_plan_details_id ?? $ib_details->acc_type) }}
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
                                                                    <p class=" text-muted">IB WALLET</p>
                                                                    {{-- @if ($user->email == 'okerekemarv123@gmail.com')
                                                                        {{ dd($ib_details->deposit - $ib_details->withdraw) }}
                                                                    @endif --}}
                                                                    <h4>@money(number_format($ib_details->deposit - $ib_details->withdraw, 2))
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
                                                                        class=" w-100 h-75 btn btn-icon btn-lg bg-light border-light rounded-pill disabled me-3 text-secondary">
                                                                        <i class="fa fa-credit-card-alt"
                                                                            aria-hidden="true"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="col-8">
                                                                    <p class="text-muted">TOTAL COMMISSION</p>
                                                                    <h4>@money($ib_details->deposit)
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
                                                                    <p class="text-muted">TOTAL DEPOSIT</p>
                                                                    <h4>@money($IbTotalDeposits)
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
                                                            <?php    for ($i = 1; $i <= 15; $i++) { ?>
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
                                                            <?php    } ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="tab-content connectionTab" id="nav-tabContent">
                                                            <?php    for ($i = 1; $i <= 15; $i++) { ?>
                                                            <div class="tab-pane fade<?php echo $i == 1 ? ' show active' : ''; ?>"
                                                                id="LEVEL<?php echo $i; ?>" role="tabpanel">
                                                                <div class="datatable-container">
                                                                    <table
                                                                        class="table table-hover datatable-table ajaxDataTable table-bordered text-nowrap w-100"
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
                                                                                <th>CLIENT NAME</th>
                                                                                <th>EMAIL</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>

                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            <?php    } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                        <div class="p-0 tab-pane" id="tab-info">
                                            <div class="row">
                                                @can('client:viewBankDetails')
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
                                                @endcan
                                                <div class="col-6">
                                                    <div class="card custom-card">
                                                        <div class="card-header justify-content-between">
                                                            <div class="card-title">Client Wallet Details</div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="table-responsive">
                                                                <table class="table text-nowrap" id="tableClientWallets">
                                                                    <thead>
                                                                        <tr>
                                                                            <th scope="col">Created On</th>
                                                                            <th scope="col">Wallet Name</th>
                                                                            <th scope="col">Currency</th>
                                                                            <th scope="col">Network</th>
                                                                            <th scope="col">Address</th>
                                                                            <th scope="col">Verified</th>
                                                                            <th scope="col">Status</th>
                                                                            @can("client_wallet:update")
                                                                                <th scope="col">Action</th>
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

                                                    <div class="col-6">
                                                         @can('client:viewClientDocuments')
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

                                                                    <?php    if ($kyc->kyc_type == 'Address Proof' || $kyc->kyc_type == 'ID Proof'): ?>
                                                                    <div
                                                                        class="m-0 overflow-visible media card-body media-xs d-sm-flex d-block justify-content-between">
                                                                        <div class="mb-2 d-flex mb-sm-0">
                                                                            <div class="my-auto media-body valign-middle"
                                                                                style="max-width: 100px; display: flex; flex-direction: column;">
                                                                                <?php        foreach (['front_image' => $files['front_image'], 'kyc_frontside' => $files['kyc_frontside'], 'kyc_backside' => $files['kyc_backside']] as $key => $extension): ?>
                                                                                <?php            if (in_array($extension, $imageExtensions) || in_array($extension, $pdfExtensions)): ?>
                                                                                <button
                                                                                    class="mt-1 btn btn-lg btn-icon btn-light text-info me-2"
                                                                                    data-bs-toggle="modal" data-bs-target="#kycModal"
                                                                                    data-bs-kyc="{{ asset('storage' . $kyc->$key) }}"
                                                                                    data-bs-type="{{ $mimeTypes[$extension] }}">
                                                                                    <i
                                                                                        class="ri-{{ in_array($extension, $pdfExtensions) ? 'file-pdf-2-line' : 'image-2-fill' }}"></i>
                                                                                </button>
                                                                                <?php            endif; ?>
                                                                                <?php        endforeach; ?>
                                                                            </div>
                                                                            <div class="my-auto media-body valign-middle">
                                                                                <a href=""
                                                                                    class="fw-semibold text-dark">{{ $kyc->kyc_type }}</a>
                                                                                <p class="m-0 text-muted">
                                                                                    {{ $kyc->registered_date_js }}
                                                                                </p>
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
                                                                            <?php        if ($kyc->status == 2 || $kyc->status == 0) { ?>
                                                                            <button class="btn btn-lg btn-icon btn-light text-success"
                                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                                title="Approve"
                                                                                onclick="takeAction('{{ $kyc->id }}','{{ $kyc->email }}',1)">
                                                                                <i class="ri-check-line"></i>
                                                                            </button>
                                                                            <?php        }
                        if ($kyc->status == 1 || $kyc->status == 0) { ?>
                                                                            <button class="btn btn-lg btn-icon btn-light text-danger"
                                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                                title="Reject"
                                                                                onclick="takeAction('{{ $kyc->id }}','{{ $kyc->email }}',2)">
                                                                                <i class="ri-close-circle-line"></i>
                                                                            </button>
                                                                            <?php        } ?>
                                                                        </div>
                                                                    </div>
                                                                    <?php    endif; ?>
                                                                    <?php endforeach; ?>
                                                                </div>

                                                            </div>
                                                        @endcan
                                                        <?php if (!isset($kyc)) { ?>
                                                        @can('client:viewClientDocuments')
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
                                                        @endcan
                                                        <?php } ?>
                                                    </div>
                                            </div>
                                        </div>
                                    {{-- @endcan --}}
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
                    </div><!-- COL-END -->
                </div>
                <!-- End:: row-1 -->
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $(document).on('click', '.ibToggle', function() {
                    var data = $(this).data();
                    $("#ibRequestForm #clientName,#ibRequestForm #clientEmail").html("");
                    $("#ibRequestForm #clientName").html(data.fullname || '');
                    $("#ibRequestForm #clientEmail").html(data.email || '');
                    var clientId = (data.ib_id && data.ib_id !== '' && data.ib_id != null) ? data.ib_id : data.id;
                    $("#ibRequestForm input[name='client_id']").val(clientId);
                    $("#ibRequestForm [name='ib_status']").val(data.ib_status != null && data.ib_status !== '' ? String(data.ib_status) : '').trigger("change");
                    $("#ibRequestForm [name='ib_group']").val(data.ib_group != null && data.ib_group !== '' ? String(data.ib_group) : '').trigger("change");
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
                    "order": [
                        [0, 'desc']
                    ],
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
                        },
                        {
                            data: 'action',
                            name: 'action'
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
                    "order": [
                        [0, 'desc']
                    ],
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
                        },
                        {
                            data: 'action',
                            name: 'action'
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
                    "order": [
                        [0, 'desc']
                    ],
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

                $('#tableClientWallets').DataTable({
                    "ajax": {
                        "url": "/admin/ajax",
                        "type": "GET",
                        data: {
                            action: 'getClientWallets',
                            id: '{{ $user->id }}'
                        },
                    },
                    "order": [
                        [0, 'desc']
                    ],
                    "columnDefs": [
                        {
                            // Hide the Action column if user doesn't have permission
                            "targets": -1, // Last column (Action)
                            "visible": window.canUpdateClientWallet
                        }
                    ],
                    columns: [{
                            data: 'created_on',
                            name: 'date'
                        },
                        {
                            data: 'wallet_name',
                            name: 'wallet_name'
                        },
                        {
                            data: 'wallet_currency',
                            name: 'wallet_currency'
                        },
                        {
                            data: 'wallet_network',
                            name: 'wallet_network'
                        },
                        {
                            data: 'wallet_address',
                            name: 'wallet_address',
                            render: function(data) {
                                if (!data) return '<span class="text-muted">N/A</span>';
                                return '<code class="text-truncate" style="max-width: 150px; display: inline-block;">' + data + '</code>';
                            }
                        },
                        {
                            data: 'verified',
                            name: 'verified'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
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

            window.canUpdateClientWallet = @json(auth()->user()->can('client_wallet:update'));
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
                    dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
                    buttons: [{
                        extend: 'excel',
                        text: 'Export to Excel',
                        className: ' btn btn-primary',
                        filename: 'Ib_Clients_' + new Date().toISOString().slice(0, 10),
                        exportOptions: {
                            columns: [4, 5, 1, 2, 3] // Updated column indices to match your use case
                        }
                    }],
                    lengthMenu: [
                        [10, 25, 50, 100, -1], // DataTable options
                        [10, 25, 50, 100, "All"] // User-facing labels
                    ],
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
                    columns: [{
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'total_accounts',
                            name: 'total_accounts'
                        },
                        {
                            data: 'total_deposit',
                            name: 'total_deposit'
                        },
                        {
                            data: 'profile_status',
                            name: 'profile_status'
                        },
                        {
                            data: 'client_name',
                            name: 'client_name',
                            visible: false
                        },
                        {
                            data: 'client_email',
                            name: 'client_email',
                            visible: false
                        },
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
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content') // Include CSRF token in the header
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
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                'content') // Include CSRF token in the header
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
                                text: xhr.responseJSON?.message ||
                                    "Can't switch user. Please try again.",
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


                // Wallet Verify Handler
                $(document).on('click', '.verifyWallet', function(e) {
                    e.preventDefault();
                    var walletId = $(this).data('wallet-id');

                    Swal.fire({
                        title: "Verify Wallet?",
                        text: "Are you sure you want to verify this wallet?",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, verify it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "/admin/ajax",
                                type: "POST",
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                data: {
                                    action: 'verifyClientWallet',
                                    wallet_id: walletId,
                                    user_id: '{{ $user->id }}'
                                },
                                success: function(response) {
                                    Swal.fire({
                                        icon: "success",
                                        title: "Success",
                                        text: response.message || "Wallet verified successfully"
                                    }).then(() => {
                                        $('#tableClientWallets').DataTable().ajax.reload();
                                    });
                                },
                                error: function(xhr) {
                                    const err = xhr.responseJSON;
                                    Swal.fire({
                                        icon: "error",
                                        title: "Error",
                                        text: err?.message || "Failed to verify wallet"
                                    });
                                }
                            });
                        }
                    });
                });

                // Wallet Delete Handler
                $(document).on('click', '.deleteWallet', function(e) {
                    e.preventDefault();
                    var walletId = $(this).data('wallet-id');
                    var walletName = $(this).data('wallet-name');

                    Swal.fire({
                        title: "Delete Wallet?",
                        text: "Are you sure you want to delete the wallet '" + walletName + "'? This action cannot be undone.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#dc3545",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "/admin/ajax",
                                type: "POST",
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                data: {
                                    action: 'deleteClientWallet',
                                    wallet_id: walletId,
                                    user_id: '{{ $user->id }}'
                                },
                                dataType: 'json',
                                success: function(response) {
                                    // Extract the actual data from the response
                                    let data = response.original || response;
                                    if (data.success) {
                                        Swal.fire({
                                            icon: "success",
                                            title: "Success",
                                            text: data.message || "Wallet deleted successfully"
                                        }).then(() => {
                                            $('#tableClientWallets').DataTable().ajax.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: "error",
                                            title: "Error",
                                            text: data.message || "Failed to delete wallet"
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    console.log('Error response:', xhr);
                                    let errorMessage = "Failed to delete wallet. It may have pending withdrawals.";

                                    // Try to extract error message from response
                                    if (xhr.responseJSON) {
                                        if (xhr.responseJSON.message) {
                                            errorMessage = xhr.responseJSON.message;
                                        } else if (xhr.responseJSON.original && xhr.responseJSON.original.message) {
                                            errorMessage = xhr.responseJSON.original.message;
                                        }
                                    }

                                    Swal.fire({
                                        icon: "error",
                                        title: "Error",
                                        text: errorMessage
                                    });
                                }
                            });
                        }
                    });
                });
            });

        </script>
        @include('partials.password-validation-script')
        <script>
            $(document).ready(function() {
                // Edit User Modal Password Validation
                const editUserModal = document.getElementById('editUserModal');
                const editUserPasswordInput = document.getElementById('editUserPassword');
                const editUserConfirmInput = document.getElementById('editUserConfirmPassword');
                const editUserSubmitBtn = document.getElementById('editUserSubmitBtn');

                if (editUserPasswordInput && editUserConfirmInput && editUserModal) {
                    // Initialize all rules to false
                    window.updateRuleUI('edit-rule-length', false);
                    window.updateRuleUI('edit-rule-uppercase', false);
                    window.updateRuleUI('edit-rule-lowercase', false);
                    window.updateRuleUI('edit-rule-digit', false);
                    window.updateRuleUI('edit-rule-special', false);
                    window.updateRuleUI('edit-rule-no-spaces', false);
                    window.updateRuleUI('edit-rule-match', false);

                    // For edit modal, password is optional - enable button by default if no password entered
                    if (editUserSubmitBtn && !editUserPasswordInput.value) {
                        editUserSubmitBtn.disabled = false;
                    }

                    const handleEditUserPasswordInput = () => {
                        const password = editUserPasswordInput.value;
                        const confirmPassword = editUserConfirmInput.value;

                        if (!password) {
                            // Password is optional for edit - reset rules and enable button
                            window.updateRuleUI('edit-rule-length', false);
                            window.updateRuleUI('edit-rule-uppercase', false);
                            window.updateRuleUI('edit-rule-lowercase', false);
                            window.updateRuleUI('edit-rule-digit', false);
                            window.updateRuleUI('edit-rule-special', false);
                            window.updateRuleUI('edit-rule-no-spaces', false);
                            window.updateRuleUI('edit-rule-match', false);

                            // Enable submit button when password is empty
                            if (editUserSubmitBtn) {
                                editUserSubmitBtn.disabled = false;
                            }
                        } else {
                            const rules = window.checkPasswordRules(password, confirmPassword);
                            window.updateRuleUI('edit-rule-length', rules.length);
                            window.updateRuleUI('edit-rule-uppercase', rules.uppercase);
                            window.updateRuleUI('edit-rule-lowercase', rules.lowercase);
                            window.updateRuleUI('edit-rule-digit', rules.digit);
                            window.updateRuleUI('edit-rule-special', rules.special);
                            window.updateRuleUI('edit-rule-no-spaces', rules.noSpaces);
                            window.updateRuleUI('edit-rule-match', confirmPassword ? rules.match : null);

                            // Only enable button if all rules satisfied
                            const allSatisfied = rules.length && rules.uppercase && rules.lowercase && rules.digit && rules.special && rules.noSpaces && rules.match === true;
                            if (editUserSubmitBtn) {
                                editUserSubmitBtn.disabled = !allSatisfied;
                            }
                        }
                    };

                    const handleEditUserConfirmInput = () => {
                        const password = editUserPasswordInput.value;
                        const confirmPassword = editUserConfirmInput.value;

                        if (!password) {
                            // No password entered, no validation needed
                            window.updateRuleUI('edit-rule-match', false);
                            if (editUserSubmitBtn) {
                                editUserSubmitBtn.disabled = false;
                            }
                        } else {
                            if (!confirmPassword) {
                                window.updateRuleUI('edit-rule-match', false);
                            } else {
                                const rules = window.checkPasswordRules(password, confirmPassword);
                                window.updateRuleUI('edit-rule-match', confirmPassword ? rules.match : null);

                                // Check all rules for button state
                                const allPasswordRules = window.checkPasswordRules(password, '');
                                const allSatisfied = allPasswordRules.length && allPasswordRules.uppercase && allPasswordRules.lowercase && allPasswordRules.digit && allPasswordRules.special && allPasswordRules.noSpaces && rules.match === true;
                                if (editUserSubmitBtn) {
                                    editUserSubmitBtn.disabled = !allSatisfied;
                                }
                            }
                        }
                    };

                    editUserPasswordInput.addEventListener('input', handleEditUserPasswordInput);
                    editUserConfirmInput.addEventListener('input', handleEditUserConfirmInput);
                }

                // Password Visibility Toggle - Edit User Modal
                const toggleEditUserPassword = document.getElementById('toggleEditUserPassword');
                if (toggleEditUserPassword && editUserPasswordInput) {
                    toggleEditUserPassword.addEventListener('click', (e) => {
                        e.preventDefault();
                        const type = editUserPasswordInput.type === 'password' ? 'text' : 'password';
                        editUserPasswordInput.type = type;
                        toggleEditUserPassword.innerHTML = type === 'password' ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
                    });
                }

                const toggleEditUserConfirmPassword = document.getElementById('toggleEditUserConfirmPassword');
                if (toggleEditUserConfirmPassword && editUserConfirmInput) {
                    toggleEditUserConfirmPassword.addEventListener('click', (e) => {
                        e.preventDefault();
                        const type = editUserConfirmInput.type === 'password' ? 'text' : 'password';
                        editUserConfirmInput.type = type;
                        toggleEditUserConfirmPassword.innerHTML = type === 'password' ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
                    });
                }
            });
        </script>
    @endsection
