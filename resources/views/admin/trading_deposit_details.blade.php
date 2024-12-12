@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Transaction Details</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Transaction Details</li>
                </ol>
            </div>
        </div>
        @if (isset($details) && !empty($details))
            <div class="row">
                <div class="mx-auto col-10">
                    <div class="card custom-card">
                        <div class="card-body">
                            <h6 class="card-title fw-medium">DEPOSIT TICKET #{{ $details->id }}</h6>
                            <div class="row">
                                <div class="col-lg-6 col-md-12">
                                    <div class="wideget-user-desc d-flex align-items-center">
                                        <div class="wideget-user-img">
                                            <img src="/admin_assets/assets/images/users/client.png" alt="img"
                                                style="width:50px">
                                        </div>
                                        <div class="user-wrap">
                                            <h4 class="fw-normal">{{ $details->fullname }}</h4>
                                            <h6 class="mb-3 text-muted fw-normal">{{ $details->email }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="cursor-pointer col-lg-3 col-md-12 rmToggle" data-rm="{{ $details->rm_id }}"
                                    data-enc="{{ ($details->email) }}" data-email="{{ $details->email }}"
                                    data-fullname="{{ $details->fullname }}">

                                    <div class="wideget-user-desc d-flex align-items-center">
                                        <div class="me-2"><svg xmlns="http://www.w3.org/2000/svg" width="25"
                                                height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                                size="25" class="tabler-icon tabler-icon-user-scan">
                                                <path d="M10 9a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                                                <path d="M4 8v-2a2 2 0 0 1 2 -2h2"></path>
                                                <path d="M4 16v2a2 2 0 0 0 2 2h2"></path>
                                                <path d="M16 4h2a2 2 0 0 1 2 2v2"></path>
                                                <path d="M16 20h2a2 2 0 0 0 2 -2v-2"></path>
                                                <path d="M8 16a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2"></path>
                                            </svg></div>
                                        <div class="user-wrap">
                                            <h4 class="fw-medium fs-11">{{ $details->rm_name ?? 'NoRM' }}</h4>
                                            <!-- <h4 class="fw-medium fs-11 text-muted">{{ $details->rm_name ?? '' }}</h4> -->
                                            <h6 class="mb-3 text-muted fw-normal fs-11">Relationship Manager</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="cursor-pointer col-lg-3 col-md-12 updateIb"
                                    data-enc="{{ ($details->email) }}" data-email="{{ $details->email }}"
                                    data-fullname="{{ $details->fullname }}">
                                    <div class="wideget-user-desc d-flex align-items-center">
                                        <div class="me-2"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-user-pentagon text-dark">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path
                                                    d="M13.163 2.168l8.021 5.828c.694 .504 .984 1.397 .719 2.212l-3.064 9.43a1.978 1.978 0 0 1 -1.881 1.367h-9.916a1.978 1.978 0 0 1 -1.881 -1.367l-3.064 -9.43a1.978 1.978 0 0 1 .719 -2.212l8.021 -5.828a1.978 1.978 0 0 1 2.326 0z">
                                                </path>
                                                <path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z"></path>
                                                <path d="M6 20.703v-.703a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.707"></path>
                                            </svg></div>
                                        <div class="user-wrap">
                                            <h4 class="fw-medium fs-11">{{ $details->parent_ib ?? 'NoIB' }}</h4>
                                            <!-- <h4 class="fw-medium fs-11 text-muted">{{ $details->parent_ib_email ?? '' }}</h4> -->
                                            <h6 class="mb-3 text-muted fw-normal fs-11">Parent IB</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table text-nowrap" cellpadding="10">
                                    <tbody>
                                        <tr></tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">Contact</span>
                                                        </div>
                                                        <div class="mt-2 lh-1">
                                                            <span><i
                                                                    class="px-2 fa fa-phone text-primary"></i>{{ $details->number }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">Created On</span>
                                                        </div>
                                                        <div class="mt-2 lh-1">
                                                            <span>{{ $details->deposted_date }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">Total Deposit</span>
                                                        </div>
                                                        <div class="mt-2 lh-1">
                                                            <span class="badge bg-success-transparent">+</span>
                                                            <span>${{ $details->total_trading_dp + $details->total_wallet_dp }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">Total Withdraw</span>
                                                        </div>
                                                        <div class="mt-2 lh-1">
                                                            <span class="badge bg-danger-transparent">-</span>
                                                            <span>${{ $details->total_trading_wd + $details->total_wallet_wd }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">PAYMENT METHOD</span>
                                                        </div>
                                                        <div class="mt-2 lh-1">
                                                            {{ $details->deposit_type }}</span>
                                                        </div>
                                                        <div class="mt-2 lh-1">
                                                            <div class="mb-2"><b>Deposit Currency:</b>
                                                                <span>{{ $details->deposit_currency }}</span></span>
                                                            </div>
                                                            <div class="mb-2"><b>Deposit Currency Amount :</b>
                                                                <span>{{ '$'.$details->deposit_amount }}</span></span>
                                                            </div>
                                                            <div class="mb-2"><b>Deposit Amount in USD:</b>
                                                                <span>{{ '$'.$details->deposit_amount }}</span></span>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">TRADE ID</span>
                                                        </div>
                                                        <div class="mt-2 lh-1">
                                                            <span>{{ $details->code }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">DEPOSIT AMOUNT</span>
                                                        </div>
                                                        <div class="mt-2 lh-1">
                                                            <span>${{ $details->deposit_amount }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">PAYMENT STATUS</span>
                                                        </div>
                                                        <div class="mt-2 lh-1">
                                                            <?php if ($details->Status == 1) { ?>
                                                            <span class="badge bg-success">APPROVED</span>
                                                            <?php } elseif ($details->Status == 2) { ?>
                                                            <span class="badge bg-danger">REJECTED</span>
                                                            <?php } elseif ($details->Status == 0) { ?>
                                                            <span class="badge bg-primary">WAITING FOR APPROVAL</span>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                            </td>
                                            <?php if ($details->Status == 0) { ?>
                                            <td>
                                            </td>
                                            <td>
                                                <div class="my-auto btn-list ms-auto">
                                                    <button
                                                        onclick="takeAction('{{ $details->email }}','{{ $details->deposit_amount }}',1,{{ $details->code }})"
                                                        type="button"
                                                        class="m-1 btn btn-success btn-space">Approve</button>
                                                    <button
                                                        onclick="takeAction('{{ $details->email }}','{{ $details->deposit_amount }}',2,{{ $details->code }})"
                                                        type="submit"
                                                        class="m-1 btn btn-danger btn-space">Reject</button>
                                                </div>
                                            </td>
                                            <?php } else { ?>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">ADMIN REMARKS</span>
                                                        </div>
                                                        <div class="mt-2 lh-1">
                                                            <span>{{ $details->admin_remark }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">ADMIN ACTION TAKEN</span>
                                                        </div>
                                                        <div class="mt-2 lh-1">
                                                            <span>{{ $details->Js_Admin_Remark_Date }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <?php } ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="mx-3 col-12">
                    <h4>No details found or you are not authorized to access this page</h4>
                </div>
            </div>
        @endif
    </div>
    <script>
        function takeAction(email, amount, status, tradeId) {
            Swal.fire({
                title: `Are you sure you want to ${status === 1 ? "approve" : "reject"} this transaction?`,
                html: `
            <form id="updateTransactionForm" method="post">
              <input type="hidden" name="email" value="${email}">
              <input type="hidden" name="amount" value="${amount}">
              <input type="hidden" name="amount" value="${amount}">
              <input type="hidden" name="status" value="${status}">
              <input type="hidden" name="tradeId" value="${tradeId}">
              <input type="hidden" name="action" value="update_transaction">
              <div class="mt-2 col-12 text-start">
                  <textarea id="description" name="description" rows="3" class="mt-2 form-control" placeholder="Add a description"></textarea>
              </div>
              </form>
          `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Submit',
                preConfirm: () => {
                    const description = document.querySelector('#updateTransactionForm textarea').value;
                    if (!description) {
                        Swal.showValidationMessage('Please add a comment');
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelector('#updateTransactionForm').submit();
                }
            });
        }
    </script>
@endsection
