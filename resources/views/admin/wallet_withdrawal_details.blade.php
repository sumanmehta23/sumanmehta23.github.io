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
                        <h6 class="card-title fw-medium">WITHDRAW TICKET #{{ $details->id }}</h6>
                        <div class="row">
                            <div class="col-lg-6 col-md-12">
                                <div class="wideget-user-desc d-flex align-items-center">
                                    <div class="wideget-user-img">
                                        <img src="/admin_assets/assets/images/users/client.png" alt="img" style="width:50px">
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
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" size="25"
                                            class="tabler-icon tabler-icon-user-scan">
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
                            <div class="cursor-pointer col-lg-3 col-md-12 updateIb" data-enc="{{ ($details->email) }}"
                                data-email="{{ $details->email }}" data-fullname="{{ $details->fullname }}">
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
                                                        <span>{{ $details->withdraw_date }}</span>
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
                                                        <span>${{ ($details->user && $details->user->walletDeposits) ? $details->user->walletDeposits->sum('deposit_amount') : 0 }}</span>
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
                                                        <span>${{ $details->filtered_withdrawal_sum }}</span>
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
                                                        {{ $details->withdraw_type }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="lh-1">
                                                        <span class="fs-11 text-muted">TRANSACTION ID</span>
                                                    </div>
                                                    <div class="mt-2 lh-1">
                                                        <span>{{ $details->transaction_id }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="lh-1">
                                                        <span class="fs-11 text-muted">WITHDRAW AMOUNT</span>
                                                    </div>
                                                    <div class="mt-2 lh-1">
                                                        <span>${{ $details->withdraw_amount }}</span>
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
                                                        <?php if ($details->status == 1) { ?>
                                                        <span class="badge bg-success">APPROVED</span>
                                                        <?php } elseif ($details->status == 2) { ?>
                                                        <span class="badge bg-danger">REJECTED</span>
                                                        <?php } elseif ($details->status == 0) { ?>
                                                        <span class="badge bg-primary">WAITING FOR APPROVAL</span>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <?php if($details->withdraw_type=='Bank Withdrawal'):?>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="lh-1">
                                                        <span class="fs-11 text-muted">CLIENT BANK DETAILS</span>
                                                    </div>
                                                    <div class="mt-2 lh-1">
                                                        <div class="mb-1"><span class="text-muted me-1">Name:
                                                            </span>{{ $details->ClientName }}</div>
                                                        <div class="mb-1"><span class="text-muted me-1">Account
                                                                Number:</span>{{ $details->accountNumber }}</div>
                                                        <div class="mb-1"><span class="text-muted me-1">Bank
                                                                Name:</span>{{ $details->bankName }}</div>
                                                        <div class="mb-1"><span class="text-muted me-1">IFSC
                                                                Code:</span>{{ $details->code }}</div>
                                                        <div class="mb-1"><span class="text-muted me-1">SWIFT
                                                                Code:</span>{{ $details->swift_code }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif;?>
                                            <?php if($client_wallet):?>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                        <span class="fs-11 text-muted">CLIENT WALLET DETAILS</span>
                                                        </div>
                                                        <div class="mt-2 lh-1">
                                                        <div class="mb-1"><span class="text-muted me-1">Address: </span><?= $client_wallet->wallet_address ?></div>
                                                        <div class="mb-1"><span class="text-muted me-1"> <?= $client_wallet->wallet_currency ?> / <?= $client_wallet->wallet_network ?></span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif;?>
                                        </td>
                                        {{-- <td style="vertical-align:top">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="lh-1">
                                                        <span class="fs-11 text-muted">CURRENCY TYPE</span>
                                                    </div>
                                                    <div class="mt-2 lh-1">
                                                        <span>{{ $details->currency_type }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td> --}}
                                        <?php if ($details->withdraw_transaction_fee && $details->status == 0) { ?>
                                            <td></td>
                                        <?php } ?>
                                        <?php if ($details->status == 0) { ?>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="lh-1">
                                                    <span class="fs-11 text-muted">WITHDRAW FEE</span>
                                                    </div>
                                                    <div class="mt-2 lh-1">
                                                    <span>$<?= $details->withdraw_transaction_fee ?? 0 ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="vertical-align:top">
                                            <div class="my-auto btn-list ms-auto">
                                                <button
                                                    onclick="takeAction('{{ $details->email }}','{{ $details->withdraw_amount + $details->withdraw_transaction_fee }}',1)"
                                                    type="button" class="m-1 btn btn-success btn-space">Approve</button>
                                                <button
                                                    onclick="takeAction('{{ $details->email }}','{{ $details->withdraw_amount }}',2)"
                                                    type="submit" class="m-1 btn btn-danger btn-space">Reject</button>
                                            </div>
                                        </td>
                                        <?php } else { ?>
                                        <td style="vertical-align:top">
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
                                        <td style="vertical-align:top">
                                            <div class="d-flex align-items-center">
                                              <div>
                                                  <div class="lh-1">
                                                      <span class="fs-11 text-muted">WITHDRAW FEE</span>
                                                  </div>
                                                  <div class="mt-2 lh-1">
                                                      <span>$<?= ($details->withdraw_transaction_fee ?? 0) ?></span>
                                                  </div>
                                              </div>
                                            </div>
                                          </td>
                                        <td style="vertical-align:top">
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
        function takeAction(email, amount, status) {
          Swal.fire({
            title: `Are you sure you want to ${status === 1 ? "approve" : "reject"} this transaction?`,
            html: `
            <form id="updateTransactionForm" method="post">
              @csrf
              <input type="hidden" name="email" value="${email}">
              <input type="hidden" name="amount" value="${amount}">
              <input type="hidden" name="status" value="${status}">
              <input type="hidden" name="action" value="update_transaction">
              <div class="mt-3 col-12 text-start">
                  <label for="transaction_id" class="form-label">Withdraw Reference ID</label>
                  <input type="text" id="transaction_id" name="transaction_id" class="form-control" placeholder="Add Reference ID">
              </div>
              <div class="mt-2 col-12 text-start">
                  <label for="description" class="form-label">Description</label>
                  <textarea id="description" name="description" rows="3" class="mt-2 form-control" placeholder="Add a description"></textarea>
              </div>
              </form>
          `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Submit',
            preConfirm: () => {
              const description = document.querySelector('#updateTransactionForm textarea').value;
              const transaction_id = document.querySelector('#transaction_id').value;
              if (!description || !transaction_id) {
                Swal.showValidationMessage('Please fill out all fields');
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
