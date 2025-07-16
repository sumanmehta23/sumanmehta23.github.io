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
                <div class="col-10 mx-auto">
                    <div class="card custom-card">
                        <div class="card-body">
                            <h6 class="card-title fw-medium">WITHDRAW TICKET #{{ $details->id }}</h6>
                            <div class="row">
                                <div class="col-lg-6 col-md-12">
                                    <div class="wideget-user-desc d-flex align-items-center">
                                        <div class="wideget-user-img">
                                            <img class="cursor-pointer" onmousedown="handleClick(event, '{{ route('admin.admin-view-client-details', $details->user_id) }}')" src="/admin_assets/assets/images/users/client.png" alt="img" href='{{ route('admin.admin-view-client-details', $details->user_id) }}'
                                                style="width:50px">
                                        </div>

                                        <div class="user-wrap">
                                            <h4 class="fw-normal d-flex align-items-center">
                                                {{ $details->user->fullname }}
                                                <span class="badge bg-success text-white ms-2">
                                                    Wallet balance: ${{ number_format($details->user->wallet_balance?$details->user->wallet_balance:0, 2) }}
                                                </span>
                                                @if($details->user->total_bonus)
                                                    <span class="badge bg-success text-white ms-2">
                                                        Bonus: ${{ number_format($details->user->total_bonus, 2) }}
                                                    </span>
                                                @endif
                                            </h4>

                                            <h6 class="mb-3 text-muted fw-normal cursor-pointer" onmousedown="handleClick(event, '{{ route('admin.admin-view-client-details', $details->user_id) }}')">{{ $details->email }}</h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-12 rmToggle cursor-pointer" data-rm="{{ $details->rm_id }}"
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
                                            <h6 class="text-muted mb-3 fw-normal fs-11">Relationship Manager</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-12 updateIb cursor-pointer"
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
                                            <h6 class="text-muted mb-3 fw-normal fs-11">Parent IB</h6>
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
                                                        <div class="lh-1 mt-2">
                                                            <span><i
                                                                    class="fa fa-phone text-primary px-2"></i>{{ $details->number }}</span>
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
                                                        <div class="lh-1 mt-2">
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
                                                        <div class="lh-1 mt-2">
                                                            <span class="badge bg-success-transparent">+</span>
                                                            <span>${{ @$details->account->tradeDeposits->sum('deposit_amount') }}</span>
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
                                                        <div class="lh-1 mt-2">
                                                            <span class="badge bg-danger-transparent">-</span>
                                                            {{-- <span>${{ $details->total_trading_wd + $details->total_wallet_wd }}</span> --}}
                                                            <span>${{@$details->account->tradeWithdrawals->where('email_verified', 1)->where('status', 1)->sum('withdrawal_amount')}}</span>
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
                                                        <div class="lh-1 mt-2">
                                                            {{ $details->withdraw_type }}</span>
                                                        </div>
                                                        <?php if (isset($details->account_holder_name)) { ?>
                                                        <div class="lh-1 mt-3">
                                                            <strong>Bank Details</strong></br>
                                                            <div class="mb-2 mt-2"><b>Bank:
                                                                </b><span>{{ $details->bank_name }}</span></div>
                                                            <div class="mb-2"><b>Acc Name:
                                                                </b><span>{{ $details->account_holder_name }}</span></div>
                                                            <div class="mb-2"><b>Acc No:
                                                                </b><span>{{ $details->bank_account_no }}</span></div>
                                                            <div class="mb-2"><b>IFSC:
                                                                </b><span>{{ $details->ifsc_code }}</span></div>
                                                            <div class="mb-2"><b>SWIFT:
                                                                </b><span>{{ $details->swift_code }}</span></div>
                                                        </div>
                                                        <?php } ?>
                                                    </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">TRANSACTION ID</span>
                                                        </div>
                                                        <div class="lh-1 mt-2">
                                                            {{-- {{dd($details)}} --}}
                                                            {{-- <span>{{ $details->withdraw_to != null ? $details->withdraw_to : $details->withdraw_type}}</span> --}}
                                                            <span>{{ $details->transaction_id }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">WITHDRAWAL AMOUNT</span>
                                                        </div>
                                                        <div class="lh-1 mt-2">
                                                            {{-- <span>${{ $details->withdrawal_amount - $details->promo_deduction }}</span> --}}
                                                            <span>${{ $details->withdrawal_amount }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">WITHDRAWAL FEE</span>
                                                        </div>
                                                        <div class="lh-1 mt-2">
                                                            <span>${{ $details->transaction_fee }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td> --}}
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">PAYMENT STATUS</span>
                                                        </div>
                                                        <div class="lh-1 mt-2">
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
                                                <?php if($client_wallet):?>
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <div class="lh-1">
                                                            <span class="fs-11 text-muted">CLIENT WALLET DETAILS</span>
                                                            </div>
                                                            <div class="mt-2 lh-1">
                                                            <div class="mb-1"><span class="text-muted me-1">Address: </span><?= $client_wallet->wallet_address ?></div>
                                                            <div class="mb-1"><span class="text-muted me-1"> <?= $client_wallet->wallet_network != 'BTC' ? $client_wallet->wallet_currency : 'BTC' ?> / <?= $client_wallet->wallet_network ?></span></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif;?>
                                            </td>
                                            <?php if ($details->status == 0) { ?>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                        <span class="fs-11 text-muted">WITHDRAW FEE</span>
                                                        </div>
                                                        <div class="mt-2 lh-1">
                                                        <span>${{ $details->transaction_fee }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{-- <div class="btn-list ms-auto my-auto">
                                                    @php
                                                        $userData = json_encode(session('userData'));
                                                    @endphp
                                                    <button
                                                        onclick="takeAction('{{ $userData }}', '{{ $details->email }}','{{ $details->withdrawal_amount }}',1,{{ $details->code }})"
                                                        type="button"
                                                        class="btn btn-success btn-space m-1">Approve</button>
                                                    <button
                                                        onclick="takeAction('{{ json_encode($details->id) }}', '{{ $details->email }}','{{ $details->withdrawal_amount }}',2,{{ $details->code }})"
                                                        type="submit"
                                                        class="btn btn-danger btn-space m-1">Reject</button>
                                                </div> --}}

                                                <div class="my-auto btn-list ms-auto">
                                                    @php
                                                        $userData = json_encode(session('userData'));
                                                    @endphp
                                                    <button type="button" class="m-1 btn btn-primary btn-space" data-bs-toggle="modal" data-bs-target="#editModal">
                                                        Edit
                                                    </button>
                                                    <button
                                                        onclick="takeAction('{{ $userData }}', '{{ $details->email }}','{{ $details->withdraw_amount + $details->transaction_fee }}',1)"
                                                        type="button" class="m-1 btn btn-success btn-space">
                                                    Approve
                                                    </button>
                                                        {{-- <button
                                                        onclick="takeAction('{{ $details->email }}','{{ $details->withdraw_amount }}',2)"
                                                        type="submit" class="m-1 btn btn-danger btn-space">Reject</button> --}}
                                                    @if (($details->status == 0) || ($details->payout_res != null))
                                                        <button onclick="takeAction('{{ json_encode($details->id) }}','{{ $details->email }}','{{ $details->withdraw_amount + $details->transaction_fee}}',3)" type="submit" class="m-1 btn btn-danger btn-space">
                                                        Reject
                                                        </button>
                                                        {{-- {{ dd($details) }} --}}
                                                        <div class="form-check d-inline-block me-2">
                                                            <input class="form-check-input" type="checkbox" id="manualPayCheckbox" style="margin-top: 2px;" onclick="handleCheckboxClick(this,'{{ json_encode($details->id) }}','{{ $userData }}', '{{ $details->email }}','{{ (int)$details->withdrawal_amount }}','{{ (int)$details->transaction_fee }}',1)">
                                                            <label class="form-check-label" for="manualPayCheckbox">Manually pay</label>
                                                        </div>
                                                    @endif
                                                </div>


                                            </td>
                                            <?php } else { ?>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="lh-1">
                                                            <span class="fs-11 text-muted">ADMIN REMARKS</span>
                                                        </div>
                                                        <div class="lh-1 mt-2">
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
                                                        <div class="lh-1 mt-2">
                                                            <span>{{ $details->approved_date }}</span>
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
                <div class="col-12 mx-3">
                    <h4>No details found or you are not authorized to access this page</h4>
                </div>
            </div>
        @endif
    </div>
        <!-- Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Withdrawal Amount</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.update_trade_account_withdraw_amount') }}">
                    @csrf
                    <input type="hidden" name="id" value="{{ $details->id }}">
                    <input type="hidden" name="withdraw_ammount" value="{{ $details->withdrawal_amount }}">
                    <input type="hidden" name="transaction_fee" value="{{ $details->transaction_fee }}">
                    <div class="modal-body">
                        <p>
                            <strong>Amount:</strong>
                            <input class="form-control d-inline-block w-auto align-middle ms-2" type="number" name="amount" value="{{ $details->withdraw_amount }}">
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function takeAction(data, email, amount, status, code) {
            const sanitizedData = data.replace(/\\/g, '\\\\');
            // console.log(sanitizedData);
            const parsedData = JSON.parse(sanitizedData);
            // console.log(parsedData);
            const now = new Date();
            const approved_date_time = `${now.getFullYear()}-${(now.getMonth() + 1).toString().padStart(2, '0')}-${now.getDate().toString().padStart(2, '0')} ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;

            if(status==2 || status==3){
                statuscode='reject';
            }
            // else if(status==2){
            //     statuscode='reject';
            // }
            else{
                statuscode='approve';
            }
          Swal.fire({
            title: `Are you sure you want to ${statuscode} this transaction?`,
            html: `
            <form id="updateTransactionForm" method="post">
              @csrf
              <input type="hidden" name="email" value="${email}">
              <input type="hidden" name="code" value="${code}">
              <input type="hidden" name="amount" value="${amount}">
              <input type="hidden" name="status" value="${status}">
              <input type="hidden" name="action" value="update_transaction">
                ${
                  status == 3
                      ? `
                  <div class="mt-2 col-12 text-start">
                      <label for="transaction_id" class="form-label">Transaction ID</label>
                      <input type="hidden" id="transaction_id" name="transaction_id" value="${parsedData}">
                      <div class="form-control">${parsedData}</div>
                  </div>
                  <div class="mt-3 col-12 text-start">
                      <label for="rejection_reason" class="form-label">Rejection Reason</label>
                      <select id="rejection_reason" name="rejection_reason" class="form-control">
                          <option value="" disabled selected>Select Rejection Reason</option>
                          <option value="Invalid cryptocurrency address">Invalid cryptocurrency address</option>
                          <option value="Incorrect Payment Details">Incorrect Payment Details (no email sent)</option>
                          <option value="Duplicate Transaction">Duplicate Transaction (no email sent)</option>
                          <option value="Bonus Min Deposit">Bonus and Minimum Deposit Not Eligible for Withdrawal (no email sent)</option>
                      </select>
                  </div>
              `
                      : ''
                }
                ${
                  status == 1
                      ? `
                  <div class="mt-2 col-12 text-start">
                      <label for="approved_by" class="form-label">Approved By</label>
                      <input type="hidden" id="approved_by" name="approved_by" value="${parsedData.username}">
                      <div class="form-control">${parsedData.username}</div>
                  </div>
                  <div class="mt-3 col-12 text-start">
                      <label for="approved_date" class="form-label">Approved On</label>
                      <input type="hidden" id="approved_date" name="approved_date" value="${approved_date_time}">
                      <div class="form-control">${approved_date_time}</div>
                  </div>
              `
                      : ''
                }

              </form>
          `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Submit',
            preConfirm: () => {
              if(status==2){
                const rejection_reason = document.querySelector('#rejection_reason').value;
                if (!rejection_reason) {
                    Swal.showValidationMessage('Please fill out all fields');
                    return false;
                }
              }

              return true;
            }
          }).then((result) => {
            if (result.isConfirmed) {
              document.querySelector('#updateTransactionForm').submit();
            }
          });
        }

        function handleCheckboxClick(checkbox, id, data, email, amount, fee, status) {
            const sanitizedData = data.replace(/\\/g, '\\\\');
            const parsedData = JSON.parse(sanitizedData);
            const withdraw_id = JSON.parse(id);
            // console.log(withdraw_id);
            if (checkbox.checked) {
                const now = new Date();
                const approved_date_time = `${now.getFullYear()}-${(now.getMonth() + 1).toString().padStart(2, '0')}-${now.getDate().toString().padStart(2, '0')} ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;

                Swal.fire({
                    title: `Are you sure you wish to process this payout manually`,
                    html: `
                    <form id="manually_approve_withdrawal" method="post" action="manually_approve_withdrawal">
                    @csrf
                    <input type="hidden" name="email" value="${email}">
                    <input type="hidden" name="amount" value="${amount}">
                    <input type="hidden" name="fee" value="${fee}">
                    <input type="hidden" name="status" value="${status}">
                    <input type="hidden" name="transaction" value="Manually">
                    <input type="hidden" name="approved_by" value="${parsedData.username}">
                    <input type="hidden" name="approved_date" value="${approved_date_time}">
                    <input type="hidden" name="transaction_id" value="${withdraw_id}">
                    <input type="hidden" name="action" value="update_transaction">
                    </form>
                `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    preConfirm: () => {
                    return true;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                    document.querySelector('#manually_approve_withdrawal').submit();
                    }else {
                        checkbox.checked = false; // Uncheck the checkbox if canceled
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const manualPayCheckbox = document.getElementById('manualPayCheckbox');
            if (manualPayCheckbox) {
                manualPayCheckbox.checked = false; // Ensure the checkbox starts unchecked
            }
        });

        function handleClick(event, url) {
            if (event.button === 0) {
                // Left click - Navigate normally
                window.location.href = url;
            } else if (event.button === 1) {
                // Middle click - Open in a new tab
                window.open(url, '_blank');
            }
        }

      </script>
@endsection
