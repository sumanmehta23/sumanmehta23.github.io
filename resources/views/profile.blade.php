@extends('layouts.crm.crm')
@section('content')
<style>
.profile-image-container {
    position: relative;
    display: inline-block;
}

/* Darken the image on hover */
.profile-image-container:hover .img-profile-avatar {
    opacity: 0.5; /* Make the image half transparent */
}

/* Style the camera icon */
.edit-icon {
    position: absolute;
    top: 35%;
    left: 50%;
    transform: translate(-50%, -50%); /* Center the icon */
    opacity: 0; /* Hidden by default */
    background-color: rgba(0, 0, 0, 0.43); /* Dark background */
    color: rgb(255, 255, 255); /* White icon */
    font-size: 45px;
    border-radius: 50%;
    width: 95px; /* Adjusted size */
    height: 95px; /* Adjusted size */
    display: flex;
    justify-content: center;
    align-items: center;
    transition: opacity 0.3s ease; /* Smooth transition */
}

/* Show the camera icon on hover */
.profile-image-container:hover .edit-icon {
    opacity: 1; /* Show the camera icon when hovering over the container */
}

/* Style the profile image */
.img-profile-avatar {
    width: 100px; /* Adjust as needed */
    height: 100px; /* Adjust as needed */
    margin-top: -25px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid white; /* Inner white border */
}

/* Increase icon size on hover */
.edit-icon:hover i {
    transform: scale(1.1);
    font-size: 30px;
}

.varification-pending{
    --bs-text-opacity: 1;
    color: rgba(var(--bs-warning-rgb), var(--bs-text-opacity)) !important;
}
.varification-plus{
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
                                        <img id="profile_image" class="img-fluid img-profile-avatar rounded-circle" src="{{ isset($user->profile_image_url) ? Storage::url('profile_images/' . $user->profile_image_url) : '\assets\images\user.png' }}" alt="User image">
                                        <!-- Camera Icon Input (Only Visible on Hover) -->
                                        <input type="file" id="profile_picture_input" style="display: none;" accept="image/*">
                                        <label for="profile_picture_input" class="edit-icon">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="row justify-content-between align-items-end">
                                        <div class="col-md-auto soc-profile-data">
                                            <h5 class="mb-1">{{ ucfirst(session('user')->fullname) }}</h5>
                                            {{-- <p class="mb-0">{{ session('user')->email }}</p>
                                            @if (session('user')->email_confirmed == 0)
                                                <div class="card avtar bg-light-primary w-100">
                                                    <div class="card-header">
                                                        <label>Email update unverified</label>
                                                    </div>
                                                </div>
                                            @endif --}}
                                            <div class="d-flex align-items-center">
                                                <p class="mb-0 me-3">{{ session('user')->email }}</p>
                                                @if (session('user')->email_confirmed == 0)
                                                    <label class="text-danger">Email update unverified</label>
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
                                                    <form action={{route("email.change")}} method="post">
                                                        @csrf
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="form-label">Full Name</label>
                                                                    <input type="text" class="form-control" name="name"
                                                                        value="{{ session('user')->fullname }}" required readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="form-label">Account Email</label>
                                                                    <input type="text" class="form-control" name="email"
                                                                        value="{{ session('user')->email }}" required>
                                                                </div>
                                                                {{-- <input type="text" class="form-control" name="email_confirmed"
                                                                    value="{{ session('user')->email_confirmed }}" required readonly> --}}
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="form-label">Contact Number</label>
                                                                    <input type="text" class="form-control" name="telephone"
                                                                        value="{{ session('user')->number }}" required readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="form-group">
                                                                    <label class="form-label">Gender</label>
                                                                    <input type="text" class="form-control"
                                                                        value="{{ session('user')->gender }}" required readonly>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class=" text-end">
                                                            <button type="submit" name="updateEmail" value="update" class="btn btn-primary rounded">Update</button>
                                                        </div>

                                                    </form>
                                                    {{-- <div class="row">
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label class="form-label">Full Name</label>
                                                                <input type="text" class="form-control"
                                                                    value="{{ session('user')->fullname }}" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label class="form-label">Account Email</label>
                                                                <input type="text" class="form-control"
                                                                    value="{{ session('user')->email }}" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label class="form-label">Contact Number</label>
                                                                <input type="text" class="form-control"
                                                                    value="{{ session('user')->number }}" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label class="form-label">Gender</label>
                                                                <input type="text" class="form-control"
                                                                    value="{{ session('user')->gender }}" disabled>
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
                                        {{-- {{ dd($user) }} --}}
                                        <div class="text-center card-body table-card">
                                            @if ($user->kyc_verify == 0)
                                                <div class="auth-main">
                                                    <div class="card-body">
                                                        <div class="text-center me-4">
                                                            <a href="user-profile#"><img
                                                                    src="{{ asset('assets/images/KYC.png') }}"
                                                                    class="w-25" alt="img"></a>
                                                        </div>
                                                        <h6 class="mb-4 text-center text-secondary f-w-400 f-16"> KYC Verification Required to Create MT5 Accounts</h6>
                                                        <a id="verify-user-kyc" href="#" class="mt-3">
                                                            <button class="btn btn-outline-primary"><span
                                                                    class="text-truncate">Process To Verify Now </span></button>
                                                        </a>
                                                    </div>
                                                </div>
                                            @elseif ($user->kyc_verify == 1)
                                                <div class="auth-main">
                                                    <div class="card-body">
                                                        <div class="text-center me-4">
                                                            <a href="user-profile#"><img
                                                                    src="{{ asset('assets/images/kyc_verified.png') }}"
                                                                    class="w-25" alt="img"></a>
                                                        </div>
                                                        <h6 class="font-bold text-center btn btn-light-success ps-5 pe-5">
                                                            KYC Verified</h6>
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
                                                        <h6 class="mb-0 text-center text-secondary f-w-400 f-16">No
                                                            documents
                                                            added</h6>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="security" role="tabpanel" aria-labelledby="profile-tab-4">
                                    <form id="changePasswordForm" method="post">
                                        @csrf
                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Change Password</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label class="form-label">Current Password</label>
                                                            <input type="password" name="current_password" required
                                                                class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">New Password</label>
                                                            <input type="password" class="form-control"
                                                                name="new_password" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">Confirm Password</label>
                                                            <input type="password" name="new_password_confirmation"
                                                                required class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <h5>New Password Must Contain:</h5>
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item"><i
                                                                    class="ti ti-circle-minus text-danger f-16 me-2"></i>At least 8 characters</li>
                                                            <li class="mb-0 list-group-item"><i
                                                                    class="ti ti-circle-minus text-danger f-16 me-2"></i>At least 1 lowercase letter (a-z)</li>
                                                            <li class="list-group-item"><i
                                                                    class="ti ti-circle-minus text-danger f-16 me-2"></i>At least 1 uppercase letter (A-Z)</li>
                                                            <li class="list-group-item"><i
                                                                    class="ti ti-circle-minus text-danger f-16 me-2"></i>At least 1 number (0-9)</li>
                                                            <li class="list-group-item"><i
                                                                    class="ti ti-circle-minus text-danger f-16 me-2"></i>At least 1 special character</li>
                                                            <li class="list-group-item"><i
                                                                    class="ti ti-circle-minus text-danger f-16 me-2"></i>Passwords do not match</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer text-end btn-page">
                                                <div class="btn btn-outline-secondary">Cancel</div>
                                                <button type="submit" name="password_changed"
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
                                                @if (count($bank_accounts) > 0)
                                                    <div class="card-body">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    {{-- <th>#</th> --}}
                                                                    <th>Wallet Name</th>
                                                                    <th>Currency</th>
                                                                    <th>Network</th>
                                                                    <th>Address</th>
                                                                    <th>Verified</th>
                                                                    <th>Status / Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($bank_accounts as $acc)
                                                                    {{-- {{ dd($acc) }} --}}
                                                                    <tr>
                                                                        {{-- <td>CWA{{ sprintf('%04u', $acc->id) }}</td> --}}
                                                                        <td>{{ $acc->wallet_name }}</td>
                                                                        <td>{{ $acc->wallet_currency }}</td>
                                                                        <td>{{ $acc->wallet_network }}</td>
                                                                        <td>{{ $acc->wallet_address }}</td>
                                                                        @php
                                                                            if($acc->verified == 0){
                                                                                $verification = 'Pending';
                                                                                $tdClass = 'varification-pending';
                                                                            }else{
                                                                                $verification = 'Approved';
                                                                                $tdClass = 'varification-plus';
                                                                            }
                                                                        @endphp
                                                                        <td  class="{{ $tdClass }}">{{ $verification }}</td>
                                                                        <td
                                                                            class="text-start {{ $acc->status == 0 ? 'text-warning' : ($acc->status == 1 ? 'text-success' : ($acc->status == 2 ? 'text-danger' : '')) }}">
                                                                            @if ($acc->status == 0)
                                                                                <a class="wallet-action"
                                                                                    data-bs-toggle="tooltip"
                                                                                    title="Inactive Wallet Address"
                                                                                    data-toggle="{{ ($acc->id) }}">
                                                                                    <i class="f-24 ti ti-toggle-left"></i>
                                                                                </a>
                                                                            @else
                                                                                <a class="wallet-action"
                                                                                    data-bs-toggle="tooltip"
                                                                                    title="Active Wallet Address"
                                                                                    data-toggle="{{ ($acc->id) }}">
                                                                                    <i class="f-24 ti ti-toggle-right"></i>
                                                                                </a>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="auth-main">
                                                        <div class="card-body">
                                                            <div class="text-center me-4">
                                                                <a href="user-profile#"><img
                                                                        src="{{ asset('assets/images/empty.png') }}"
                                                                        class="w-25" alt="img"></a>
                                                            </div>
                                                            <h6 class="mb-0 text-center text-secondary f-w-400 f-16">Please Add Your Wallet Details</h6>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="profile-6" role="tabpanel" aria-labelledby="profile-tab-6">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                message: 'Kindly check your new email address and complete the verification process for this update.'
            }).then(() => {
                window.location.href = '{{ route('user-profile') }}';
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
            $.ajax({
                type: "POST",
                url: "{{ route('password.change') }}",
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: response.success,
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    console.log(xhr);
                    const errorMessage = xhr.responseJSON?.message || 'Something went wrong';
                    Swal.fire({
                        icon: 'error',
                        title: errorMessage,
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
                beforeSend: function() {
                    swal.fire({
                        showConfirmButton: false,
                        showCancelButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: function() {
                            swal.showLoading();
                        }
                    });
                },
                success: function(data) {
                    swal.close();
                    if (data.success == true) {
                        swal.fire({
                            icon: "success",
                            title: "Wallet Address Status Updated"
                        }).then((val) => {
                            location.reload();
                        });
                    } else {
                        swal.fire({
                            icon: "warning",
                            title: data
                        });
                    }
                }
            });
        });
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
    </script>
@endsection
