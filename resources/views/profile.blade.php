@extends('layouts.crm.crm')
@section('content')
    <style>
        .profile-image-container {
            position: relative;
            display: inline-block;
        }

        /* Darken the image on hover */
        .profile-image-container:hover .img-profile-avatar {
            opacity: 0.5;
            /* Make the image half transparent */
        }

        /* Style the camera icon */
        .edit-icon {
            position: absolute;
            top: 35%;
            left: 50%;
            transform: translate(-50%, -50%);
            /* Center the icon */
            opacity: 0;
            /* Hidden by default */
            background-color: rgba(0, 0, 0, 0.43);
            /* Dark background */
            color: rgb(255, 255, 255);
            /* White icon */
            font-size: 45px;
            border-radius: 50%;
            width: 95px;
            /* Adjusted size */
            height: 95px;
            /* Adjusted size */
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.3s ease;
            /* Smooth transition */
        }

        /* Show the camera icon on hover */
        .profile-image-container:hover .edit-icon {
            opacity: 1;
            /* Show the camera icon when hovering over the container */
        }

        /* Style the profile image */
        .img-profile-avatar {
            width: 100px;
            /* Adjust as needed */
            height: 100px;
            /* Adjust as needed */
            margin-top: -25px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            /* Inner white border */
        }

        /* Increase icon size on hover */
        .edit-icon:hover i {
            transform: scale(1.1);
            font-size: 30px;
        }

        .varification-pending {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-warning-rgb), var(--bs-text-opacity)) !important;
        }

        .varification-plus {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-success-rgb), var(--bs-text-opacity)) !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title h2">
                                <h4 class="mb-0">My Profile</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card social-profile">
                        <div class="pt-0 card-body">
                            <div class="row align-items-end">
                                <div class="col-md-auto text-md-start">
                                    <div class="profile-image-container">
                                        <img id="profile_image" class="img-fluid img-profile-avatar rounded-circle"
                                            src="{{ isset($user->profile_image_url) ? Storage::url('profile_images/' . $user->profile_image_url) : '\assets\images\user.png' }}"
                                            alt="User image">
                                        <!-- Camera Icon Input (Only Visible on Hover) -->
                                        <input type="file" id="profile_picture_input" style="display: none;"
                                            accept="image/*">
                                        <label for="profile_picture_input" class="edit-icon">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="row justify-content-between align-items-end">
                                        <div class="col-md-auto soc-profile-data">
                                            <h5 class="mb-1">{{ ucfirst(auth()->user()->fullname) }}</h5>
                                            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-center justify-content-sm-start gap-2">
                                                <p class="mb-0">{{ auth()->user()->email }}</p>
                                                @if ($user->email_confirmed == 0)
                                                    <label class="text-white badge bg-danger"
                                                        style="font-size: 14px;">Email update unverified</label>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-auto"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="py-0 card-body">
                                    <ul class="nav nav-tabs profile-tabs" id="myTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link active" id="profile-tab-3" data-bs-toggle="tab"
                                                href="#personal" role="tab" aria-selected="true">
                                                <i class="ti ti-id me-2"></i> Personal Details
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="profile-tab-2" data-bs-toggle="tab" href="#kyc"
                                                role="tab" aria-selected="false" tabindex="-1">
                                                <i class="ti ti-file-text me-2"></i> KYC Verification
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="profile-tab-4" data-bs-toggle="tab" href="#security"
                                                role="tab" aria-selected="false" tabindex="-1">
                                                <i class="ti ti-lock me-2"></i> Change Password
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="profile-tab-5" data-bs-toggle="tab" href="#wallets"
                                                role="tab" aria-selected="false" tabindex="-1">
                                                <i class="ti ti-file-text me-2"></i> Wallet Details
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="two-factor-auth-tab" data-bs-toggle="tab"
                                                href="#two-factor-auth" role="tab" aria-selected="false" tabindex="-1">
                                                <i class="ti ti-file-text me-2"></i> 2FA
                                            </a>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                            <div class="tab-content">
                                <div class="tab-pane active show" id="personal" role="tabpanel"
                                    aria-labelledby="profile-tab-3">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Personal Information</h5>
                                                </div>
                                                <div class="card-body">
                                                    {{-- <form action={{ route('email.change') }} method="post"> --}}
                                                        @csrf
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="form-label">Full Name</label>
                                                                    <input type="text" class="form-control"
                                                                        name="name"
                                                                        value="{{ auth()->user()->fullname }}" required
                                                                        readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="form-label">Account Email</label>
                                                                    <input type="text" class="form-control"
                                                                        name="email" value="{{ auth()->user()->email }}"
                                                                        readonly>
                                                                </div>
                                                                {{-- <input type="text" class="form-control" name="email_confirmed"
                                                                    value="{{ auth()->user()->email_confirmed }}" required readonly> --}}
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="form-label">Contact Number</label>
                                                                    <input type="text" class="form-control"
                                                                        name="telephone"
                                                                        value="{{ auth()->user()->number }}" required
                                                                        readonly>
                                                                </div>
                                                            </div>
                                                            {{-- <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="form-label">Gender</label>
                                                                    <input type="text" class="form-control"
                                                                        value="{{ auth()->user()->gender }}" required
                                                                        readonly>
                                                                </div>
                                                            </div> --}}
                                                        </div>

                                                        {{-- <div class=" text-end">
                                                            <button type="submit" name="updateEmail" value="update"
                                                                class="rounded btn btn-primary">Update</button>
                                                        </div> --}}

                                                    {{-- </form> --}}
                                                    {{-- <div class="row">
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label class="form-label">Full Name</label>
                                                                <input type="text" class="form-control"
                                                                    value="{{ auth()->user()->fullname }}" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label class="form-label">Account Email</label>
                                                                <input type="text" class="form-control"
                                                                    value="{{ auth()->user()->email }}" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label class="form-label">Contact Number</label>
                                                                <input type="text" class="form-control"
                                                                    value="{{ auth()->user()->number }}" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label class="form-label">Gender</label>
                                                                <input type="text" class="form-control"
                                                                    value="{{ auth()->user()->gender }}" disabled>
                                                            </div>
                                                        </div>
                                                    </div> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="kyc" role="tabpanel" aria-labelledby="profile-tab-2">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="row">
                                                <div class="col-6">
                                                    <h5>KYC Verification</h5>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center card-body table-card">
                                            {{-- APPROVED - KYC Status is APPROVED OR kyc_verify is 1 --}}
                                            @if ($user->kyc_status === 'APPROVED' || $user->kyc_verify == 1)
                                                <div class="auth-main">
                                                    <div class="card-body">
                                                        <div class="text-center me-4">
                                                            <img src="{{ asset('assets/images/kyc_verified.png') }}" class="w-25 mb-3" alt="Verified">
                                                        </div>
                                                        <h6 class="mb-3 text-center">
                                                            <span class="badge bg-success" style="font-size: 14px;">
                                                                <i class="ti ti-check me-2"></i>KYC Verified
                                                            </span>
                                                        </h6>
                                                        <p class="text-secondary f-w-400 f-14 mb-3">Your KYC verification has been approved. You can now create trading accounts.</p>
                                                        <button type="button" class="btn btn-light-success ps-5 pe-5" disabled>
                                                            <i class="ti ti-check me-2"></i>KYC Verified
                                                        </button>
                                                    </div>
                                                </div>

                                            {{-- REJECTED - KYC Status is REJECTED --}}
                                            @elseif ($user->kyc_status === 'REJECTED')
                                                <div class="auth-main">
                                                    <div class="card-body">
                                                        <div class="text-center me-4">
                                                            <img src="{{ asset('assets/images/KYC.png') }}" class="w-25 mb-3" alt="Rejected">
                                                        </div>
                                                        <h6 class="mb-3 text-center">
                                                            <span class="badge bg-danger" style="font-size: 14px;">
                                                                <i class="ti ti-circle-x me-2"></i>KYC Rejected
                                                            </span>
                                                        </h6>
                                                        @if ($user->kyc_reason)
                                                            <p class="text-secondary f-w-400 f-14 mb-3">
                                                                <strong>Reason:</strong> {{ $user->kyc_reason }}
                                                            </p>
                                                        @endif
                                                        <p class="text-secondary f-w-400 f-12 mb-3">Rejected by Sumsub API</p>
                                                        <a id="verify-user-kyc" href="#" class="mt-3">
                                                            <button class="btn btn-outline-danger">
                                                                <i class="ti ti-refresh me-2"></i>Try Again
                                                            </button>
                                                        </a>
                                                    </div>
                                                </div>

                                            {{-- PENDING - KYC Status is PENDING --}}
                                            @elseif ($user->kyc_status === 'PENDING')
                                                <div class="auth-main">
                                                    <div class="card-body">
                                                        <div class="text-center me-4">
                                                            <img src="{{ asset('assets/images/KYC.png') }}" class="w-25 mb-3" alt="Pending">
                                                        </div>
                                                        <h6 class="mb-3 text-center">
                                                            <span class="badge bg-warning text-dark" style="font-size: 14px;">
                                                                <i class="ti ti-loader-2 me-2"></i>Under Review
                                                            </span>
                                                        </h6>
                                                        <p class="text-secondary f-w-400 f-14 mb-2">Under review by Sumsub</p>
                                                        <p class="text-secondary f-w-400 f-14 mb-3">Your KYC verification is under review. We'll notify you once the review is complete.</p>
                                                        <button type="button" class="btn btn-light-warning ps-5 pe-5" disabled>
                                                            <i class="ti ti-clock me-2"></i>Under Review
                                                        </button>
                                                    </div>
                                                </div>

                                            {{-- NOT VERIFIED / NOT STARTED --}}
                                            @else
                                                <div class="auth-main">
                                                    <div class="card-body">
                                                        <div class="text-center me-4">
                                                            <img src="{{ asset('assets/images/KYC.png') }}" class="w-25 mb-3" alt="Not Verified">
                                                        </div>
                                                        <h6 class="mb-3 text-center">
                                                            <span class="badge bg-secondary" style="font-size: 14px;">
                                                                <i class="ti ti-alert-circle me-2"></i>Not Verified
                                                            </span>
                                                        </h6>
                                                        <h6 class="mb-3 text-center text-secondary f-w-400 f-16">KYC Verification Required to Create MT5 Accounts</h6>
                                                        <p class="text-secondary f-w-400 f-14 mb-3">Complete your KYC verification to unlock trading accounts and features.</p>
                                                        <a id="verify-user-kyc" href="#" class="mt-3">
                                                            <button class="btn btn-outline-primary">
                                                                <i class="ti ti-check me-2"></i>Get KYC Verified
                                                            </button>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="security" role="tabpanel" aria-labelledby="profile-tab-4">
                                    <form id="changePasswordForm" method="post" action="{{ route('password.change') }}">
                                        @csrf
                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Change Password</h5>
                                            </div>
                                            <div class="card-body">
                                                <!-- Success Message -->
                                                <div id="passwordSuccessMessage" class="alert alert-success d-none"></div>
                                                <!-- Error Message -->
                                                <div id="passwordErrorMessage" class="alert alert-danger d-none"></div>

                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="form-label">Current Password</label>
                                                            <input type="password" name="current_password" required
                                                                class="form-control" id="current_password">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">New Password</label>
                                                            <input type="password" class="form-control"
                                                                name="new_password" required id="new_password">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">Confirm Password</label>
                                                            <input type="password" name="new_password_confirmation"
                                                                required class="form-control" id="new_password_confirmation">
                                                        </div>
                                                    </div>
                                                    {{-- <div class="col-sm-6">
                                                        <h5>New Password Must Contain:</h5>
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item"><i
                                                                    class="ti ti-circle-minus text-danger f-16 me-2"></i>At
                                                                least 8 characters</li>
                                                            <li class="mb-0 list-group-item"><i
                                                                    class="ti ti-circle-minus text-danger f-16 me-2"></i>At
                                                                least 1 lowercase letter (a-z)</li>
                                                            <li class="list-group-item"><i
                                                                    class="ti ti-circle-minus text-danger f-16 me-2"></i>At
                                                                least 1 uppercase letter (A-Z)</li>
                                                            <li class="list-group-item"><i
                                                                    class="ti ti-circle-minus text-danger f-16 me-2"></i>At
                                                                least 1 number (0-9)</li>
                                                            <li class="list-group-item"><i
                                                                    class="ti ti-circle-minus text-danger f-16 me-2"></i>At
                                                                least 1 special character</li>
                                                            <li class="list-group-item"><i
                                                                    class="ti ti-circle-minus text-danger f-16 me-2"></i>Passwords
                                                                do not match</li>
                                                        </ul>
                                                    </div> --}}

                                                    <div class="col-sm-6">
                                                        @include('partials.password-validation-rules')
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="card-footer text-end btn-page">
                                                <div class="btn btn-outline-secondary">Cancel</div>
                                                <button type="submit" name="password_changed"
                                                    id="password_changed"
                                                    class="btn btn-primary">Update
                                                    Password</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane" id="wallets" role="tabpanel" aria-labelledby="profile-tab-5">
                                    <div>
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <h5>Wallet Details</h5>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <a href="user-profile#"
                                                            class="btn btn-primary d-inline-flex align-item-center"
                                                            data-bs-toggle="modal" data-bs-target="#addBankModal2">
                                                            <i class="ti ti-plus f-18"></i> Add Wallet Information
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body table-card">
                                                @if ($bank_accounts->count() > 0)
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Wallet Name</th>
                                                                        <th>Currency</th>
                                                                        <th>Network</th>
                                                                        <th>Crypto Address</th>
                                                                        <th class="text-center">Status</th>
                                                                        <th class="text-center">Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($bank_accounts as $acc)
                                                                        <tr>
                                                                            <td>{{ $acc->wallet_name }}</td>
                                                                            <td>{{ $acc->wallet_network == 'BTC' ? 'BTC' : $acc->wallet_currency }}</td>
                                                                            <td>{{ $acc->wallet_network }}</td>
                                                                            <td>{{ $acc->wallet_address }}</td>
                                                                            @php
                                                                                if ($acc->verified == 0) {
                                                                                    $verification = 'Pending';
                                                                                    $tdClass = 'varification-pending';
                                                                                } else {
                                                                                    $verification = 'Verified';
                                                                                    $tdClass = 'varification-plus';
                                                                                }
                                                                            @endphp
                                                                            <td class="{{ $tdClass }} text-center">
                                                                                {{ $verification }}
                                                                                @if ($verification == 'Pending' && $acc->wallet_delete_verification == 0)
                                                                                    <div class="pt-2">
                                                                                        <a href="#" class="btn btn-sm btn-outline-primary primary-btn"        onclick="resendWalletAddressConfirmationVerifyEmail('{{ $acc->wallet_address }}', '{{ $acc->id }}')" type="submit">
                                                                                            Resend Verification Email
                                                                                        </a>
                                                                                    </div>
                                                                                @endif
                                                                            </td>

                                                                            <td class="text-center">
                                                                                @if ($acc->wallet_delete_verification == 0)
                                                                                    <div class="d-flex justify-content-center
                                                                                        {{ $acc->status == 0 ? 'text-warning' : ($acc->status == 1 ? 'text-success' : ($acc->status == 2 ? 'text-danger' : '')) }}">

                                                                                        @if ($acc->status == 0)
                                                                                            <a class="mt-1 wallet-action me-2" data-bs-toggle="tooltip" title="Inactive Wallet Address" data-toggle="{{ $acc->id }}">
                                                                                                <i class="f-24 ti ti-toggle-left"></i>
                                                                                            </a>
                                                                                        @else
                                                                                            <a class="mt-1 wallet-action me-2" data-bs-toggle="tooltip" title="Active Wallet Address" data-toggle="{{ $acc->id }}">
                                                                                                <i class="f-24 ti ti-toggle-right"></i>
                                                                                            </a>
                                                                                        @endif

                                                                                        {{-- <span class="badge text-warning edit_wallet_address" data-id="{{ $acc->id }}" data-bs-toggle="tooltip" title="Edit Wallet Address">
                                                                                            <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='icon icon-tabler icons-tabler-outline icon-tabler-edit text-secondary'>
                                                                                                <path stroke='none' d='M0 0h24v24H0z' fill='none' />
                                                                                                <path d='M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1' />
                                                                                                <path d='M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z' />
                                                                                                <path d='M16 5l3 3' />
                                                                                            </svg>
                                                                                        </span> --}}
                                                                                    </div>
                                                                                @elseif ($acc->wallet_delete_verification == 1)
                                                                                    <span class="text-warning">Deletion Not Verified</span>
                                                                                    <div class="pt-2">
                                                                                        <a href="#" class="btn btn-sm btn-outline-primary primary-btn"
                                                                                        onclick="resendWalletAddressDeleteConfirmationVerifyEmail('{{ $acc->wallet_address }}', '{{ $acc->id }}')" type="submit">
                                                                                            Resend Verification Email
                                                                                        </a>
                                                                                    </div>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="mt-3 d-flex justify-content-end">

                                                            {{ $bank_accounts->fragment('wallets')->links('pagination::bootstrap-5') }}
                                                        </div>
                                                    </div>

                                                @else
                                                    <div class="auth-main">
                                                        <div class="card-body">
                                                            <div class="text-center me-4">
                                                                <a href="user-profile#"><img
                                                                        src="{{ asset('assets/images/empty.png') }}"
                                                                        class="w-25" alt="img"></a>
                                                            </div>
                                                            <h6 class="mb-0 text-center text-secondary f-w-400 f-16">Please
                                                                Add Your Wallet Details</h6>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="two-factor-auth" role="tabpanel"
                                    aria-labelledby="two-factor-auth-tab">
                                    <x-two-factor-authentication />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let hash = window.location.hash;
            if (hash) {
                let tab = document.querySelector(`a[href="${hash}"], button[data-bs-target="${hash}"]`);

                if (!tab && hash.startsWith('#')) {
                    let tabId = hash.substring(1);
                    let tabById = document.getElementById(tabId);

                    if (tabById && tabById.matches('[data-bs-toggle="tab"]')) {
                        tab = tabById;
                    }
                }

                if (tab) {
                    bootstrap.Tab.getOrCreateInstance(tab).show();
                }
            }
        });
    </script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                message: 'Kindly check your new email address and complete the verification process for this update.'
            }).then(() => {
                // Preserve the tab parameter if it exists in the current URL
                const urlParams = new URLSearchParams(window.location.search);
                const tabParam = urlParams.get('tab');
                let redirectUrl = '{{ route('user-profile') }}';
                if (tabParam) {
                    redirectUrl += '?tab=' + tabParam;
                }
                window.location.href = redirectUrl;
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: "Something Went Wrong !!!!",
                text: '{{ session('error') }}',
            });
        </script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $("#changePasswordForm").submit(function(e) {
            e.preventDefault();

            // Clear previous messages
            $('#passwordSuccessMessage').addClass('d-none').text('');
            $('#passwordErrorMessage').addClass('d-none').text('');

            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function(response) {
                    console.log("Success Response:", response);
                    $('#passwordSuccessMessage').removeClass('d-none').text(response.success);
                    Swal.fire({
                        icon: 'success',
                        title: response.success,
                    }).then(() => {
                        // Clear form after successful password change
                        $('#changePasswordForm')[0].reset();
                        // Reset validation UI
                        $('.rule-icon').removeClass('ti-check text-success').addClass('ti-x text-red-500');
                        $('#rule-length, #rule-uppercase, #rule-lowercase, #rule-digit, #rule-special, #rule-no-spaces, #rule-match')
                            .removeClass('text-success').addClass('text-red-500');
                    });
                },
                error: function(xhr) {
                    console.log("Error Response:", xhr);
                    let errorMessage = "Something went wrong";

                    if (xhr.responseJSON?.errors) {
                        // Handle array of errors from controller
                        const errors = xhr.responseJSON.errors;
                        if (Array.isArray(errors)) {
                            let errorList = errors.map(error => `<li>${error}</li>`).join("");
                            errorMessage = `<ul style="text-align: left; list-style-type: disc; margin-left: 20px;">${errorList}</ul>`;
                        } else {
                            // Handle object errors
                            let errorList = '';
                            for (const [key, value] of Object.entries(xhr.responseJSON.errors)) {
                                if (Array.isArray(value)) {
                                    value.forEach(msg => {
                                        errorList += `<li>${msg}</li>`;
                                    });
                                } else {
                                    errorList += `<li>${value}</li>`;
                                }
                            }
                            errorMessage = `<ul style="text-align: left; list-style-type: disc; margin-left: 20px;">${errorList}</ul>`;
                        }
                    } else if (xhr.responseJSON?.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    // Show error in both SweetAlert and inline message
                    $('#passwordErrorMessage').removeClass('d-none').html(errorMessage);

                    Swal.fire({
                        icon: 'error',
                        title: "Password Requirements Not Met",
                        html: errorMessage,
                    });
                }
            });
        });
        $(".wallet-action").click(function(e) {
            e.preventDefault();
            var trans = $(this).data("toggle");
            $.ajax({
                type: "POST",
                url: "{{ route('wallet.updateStatus') }}",
                data: {
                    toggle_wallet: "true",
                    id: trans
                },
                // beforeSend: function() {
                //     swal.fire({
                //         showConfirmButton: false,
                //         showCancelButton: false,
                //         allowOutsideClick: false,
                //         allowEscapeKey: false,
                //         didOpen: function() {
                //             swal.showLoading();
                //         }
                //     });
                // },
                success: function(data) {
                    swal.close();
                    if (data.success == true) {
                        swal.fire({
                            icon: "success",
                            title: "Wallet Address Status Updated"
                        }).then((val) => {
                            // Dynamically update the toggle icon and classes
                            var $toggleBtn = $(".wallet-action[data-toggle='" + trans + "']");
                            var $icon = $toggleBtn.find('i');
                            var $container = $toggleBtn.parent();

                            // Toggle between left and right icons
                            if ($icon.hasClass('ti-toggle-left')) {
                                $icon.removeClass('ti-toggle-left').addClass('ti-toggle-right');
                                $toggleBtn.attr('title', 'Active Wallet Address');
                                $container.removeClass('text-warning').addClass('text-success');
                            } else {
                                $icon.removeClass('ti-toggle-right').addClass('ti-toggle-left');
                                $toggleBtn.attr('title', 'Inactive Wallet Address');
                                $container.removeClass('text-success').addClass('text-warning');
                            }

                            // Reinitialize tooltip
                            $toggleBtn.tooltip('dispose').tooltip();
                        });
                    } else {
                        swal.fire({
                            icon: "error",
                            title: "Cannot Update Wallet Status",
                            text: data.message || "An error occurred while updating wallet status"
                        });
                    }
                },
                error: function(xhr) {
                    swal.close();
                    var errorMessage = "An error occurred while updating wallet status. Please try again.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    swal.fire({
                        icon: "error",
                        title: "Error",
                        text: errorMessage
                    });
                }
            });
        });
        $(".delete_wallet_address").click(function(e) {
            e.preventDefault();

            const wallet_id = this.getAttribute("data-id");

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to undo this action!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('verify_delete_wallet_address') }}", // Update to match your route
                        data: {
                            id: wallet_id,
                            _token: "{{ csrf_token() }}" // Ensure CSRF protection
                        },
                        success: function(data) {
                            if (data.success) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Verify Your Email For Wallet Address Deletion"
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: "warning",
                                    title: "Warning",
                                    text: data.message || "Something went wrong!"
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "An error occurred while deleting wallet address. Please try again."
                            });
                        }
                    });
                }
            });
        });

        // $(".edit_wallet_address").click(function(e) {
        //     e.preventDefault();

        //     const wallet_id = this.getAttribute("data-id");

        //     // Send an AJAX request to fetch the wallet details
        //     $.ajax({
        //         url: "/get_editing_wallet_details", // Change to your actual API endpoint
        //         type: "GET",
        //         data: {
        //             id: wallet_id
        //         },
        //         success: function(response) {
        //             if (response.success) {
        //                 // Populate modal fields with the fetched data
        //                 $("#editBankModal2 input[name='wallet_name']").val(response.data.wallet_name);
        //                 $("#editBankModal2 select[name='wallet_network']").val(response.data
        //                     .wallet_network);
        //                 $("#editBankModal2 input[name='wallet_address']").val(response.data
        //                     .wallet_address);
        //                 $("#editBankModal2 select[name='status']").val(response.data.status);
        //                 $("#editBankModal2 input[name='id']").val(response.data.id);

        //                 // Show the modal
        //                 $("#editBankModal2").modal("show");
        //             } else {
        //                 alert("Failed to fetch wallet details.");
        //             }
        //         },
        //         error: function() {
        //             alert("Error fetching wallet details.");
        //         }
        //     });
        // });



        $(document).ready(function() {
            // Show "Edit Picture" text when hovering over the image
            $('#profile_image').hover(function() {
                $('.edit-text').fadeIn(); // Show "Edit Picture" text
            }, function() {
                $('.edit-text').fadeOut(); // Hide "Edit Picture" text
            });

            // Trigger file input when image is clicked
            $('#profile_image').click(function() {
                $('#profile_picture_input').click(); // Open file input dialog
            });

            // Handle file input change (when user selects an image)
            $('#profile_picture_input').change(function() {
                var formData = new FormData();
                formData.append('profile_picture', this.files[0]);

                $.ajax({
                    url: "{{ route('profileimage.change') }}", // Make sure this is your correct route
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        // Update the image on success
                        $('#profile_image').attr('src', response.new_image_url);
                        Swal.fire({
                            icon: 'success',
                            title: 'Profile picture updated successfully!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            // Reload the page after the popup closes
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        // Handle error
                        alert('Error updating profile picture!');
                    }
                });
            });
        });

        function resendWalletAddressConfirmationVerifyEmail(walletAddress, id) {
            console.log("Sending request...");

            $.ajax({
                url: "{{ route('resend.wallet.confirmation') }}",
                type: "POST",
                dataType: "json",
                contentType: "application/json",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                data: JSON.stringify({ wallet_address: walletAddress, wallet_id: id }),
                success: function (data) {
                    console.log("Server Response:", data);
                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: data.message,
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "OK"
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: data.message || "Error sending verification email.",
                            confirmButtonColor: "#d33",
                            confirmButtonText: "Try Again"
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Something went wrong. Please try again!",
                        confirmButtonColor: "#d33",
                        confirmButtonText: "Close"
                    });
                }
            });
        }


        function resendWalletAddressDeleteConfirmationVerifyEmail(walletAddress, id) {
            console.log("Sending request...");

            $.ajax({
                url: "{{ route('resend.wallet.delete.confirmation') }}",
                type: "POST",
                dataType: "json",
                contentType: "application/json",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                data: JSON.stringify({ wallet_address: walletAddress, wallet_id: id }),
                success: function (data) {
                    console.log("Server Response:", data);
                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: data.message,
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "OK"
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: data.message || "Error sending verification email.",
                            confirmButtonColor: "#d33",
                            confirmButtonText: "Try Again"
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error:", error);
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Something went wrong. Please try again!",
                        confirmButtonColor: "#d33",
                        confirmButtonText: "Close"
                    });
                }
            });
        }

        // Handle tab navigation from query parameter
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');

            if (tabParam === 'wallets') {
                // Get the wallets tab link and click it
                const walletsTab = document.getElementById('profile-tab-5');
                if (walletsTab) {
                    const bsTab = new bootstrap.Tab(walletsTab);
                    bsTab.show();
                }
            }
        });

    </script>

    @include('partials.password-validation-script')

    <script>


        // Validate email format
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Check if all validation rules are satisfied
        function validateRegistrationForm() {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('new_password_confirmation');
            const continueBtn = document.getElementById('password_changed');

            if (!emailInput || !passwordInput || !confirmPasswordInput || !continueBtn) return;

            const email = emailInput.value.trim();
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            // Check email is valid
            const isEmailValid = email && isValidEmail(email);

            // Check password rules
            const rules = window.checkPasswordRules(password, confirmPassword);

            // All rules must be satisfied
            const allRulesSatisfied = rules.length && rules.uppercase && rules.lowercase && rules.digit && rules.special && rules.match === true;

            // Enable button only if email is valid AND all password rules are satisfied
            const isFormValid = isEmailValid && allRulesSatisfied;

            continueBtn.disabled = !isFormValid;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('new_password_confirmation');
            const continueBtn = document.getElementById('password_changed');

            if (!passwordInput) return;
            // Email validation
            if (emailInput) {
                emailInput.addEventListener('input', function () {
                    validateRegistrationForm();
                });
            }

            // Password validation
            passwordInput.addEventListener('input', function () {
                const password = this.value;
                const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';
                const rules = window.checkPasswordRules(password, confirmPassword);

                window.updateRuleUI('rule-length', rules.length);
                window.updateRuleUI('rule-uppercase', rules.uppercase);
                window.updateRuleUI('rule-lowercase', rules.lowercase);
                window.updateRuleUI('rule-digit', rules.digit);
                window.updateRuleUI('rule-special', rules.special);
                window.updateRuleUI('rule-no-spaces', rules.noSpaces);
                window.updateRuleUI('rule-match', confirmPassword ? rules.match : null);

                validateRegistrationForm();
            });

            // Confirm password validation
            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', function () {
                    const password = passwordInput.value;
                    const confirmPassword = this.value;
                    const rules = window.checkPasswordRules(password, confirmPassword);

                    window.updateRuleUI('rule-match', confirmPassword ? rules.match : null);

                    validateRegistrationForm();
                });
            }

            // Initial state - button should be disabled
            validateRegistrationForm();
        });
    </script>
@endsection
