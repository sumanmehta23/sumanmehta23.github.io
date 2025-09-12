@extends('layouts.admin.admin')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- PAGE-HEADER -->
        <div class="page-header">
            <h1 class="page-title">Sumsub KYC Sync</h1>
            <div class="page-header-actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#singleSyncModal">
                    <i class="fe fe-user"></i> Sync Single User
                </button>
                <button type="button" class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#bulkSyncModal">
                    <i class="fe fe-users"></i> Bulk Sync
                </button>
            </div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">KYC Sync</li>
            </ol>
        </div>
        <!-- PAGE-HEADER END -->
        
        <!-- Recent Sync Results -->
        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Sync Operations</h5>
                    </div>
                    <div class="card-body">
                        <div id="recentResults">
                            <div class="text-muted text-center py-4">
                                <i class="fe fe-clock fe-2x mb-2"></i>
                                <p>No recent sync operations.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Single User Sync Modal -->
<div class="modal fade" id="singleSyncModal" tabindex="-1" aria-labelledby="singleSyncModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="singleSyncModalLabel">
                    <i class="fe fe-user me-2"></i>Sync Single User KYC
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="singleSyncForm">
                <div class="modal-body">
                    @csrf
                    <!-- Error/Success Messages Container -->
                    <div id="singleSyncMessages"></div>
                    
                    <div class="alert alert-info">
                        <i class="fe fe-info me-2"></i>
                        Provide either User ID or User Email to sync KYC data from Sumsub.
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="user_id" class="form-label">User ID:</label>
                        <input type="text" class="form-control" id="user_id" name="user_id" placeholder="Enter user ID">
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Enter the user ID from the database</small>
                    </div>
                    
                    <div class="text-center my-3">
                        <span class="badge bg-secondary">OR</span>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="user_email" class="form-label">User Email:</label>
                        <input type="email" class="form-control" id="user_email" name="user_email" placeholder="Enter user email">
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Enter the email address registered in the system</small>
                    </div>
                    
                    <div id="singleSyncProgress" class="d-none">
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                        </div>
                        <p class="text-center text-muted">Syncing KYC data...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="singleSyncBtn">
                        <i class="fe fe-refresh-cw me-2"></i>Sync KYC
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Sync Modal -->
<div class="modal fade" id="bulkSyncModal" tabindex="-1" aria-labelledby="bulkSyncModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkSyncModalLabel">
                    <i class="fe fe-users me-2"></i>Bulk Sync KYC Data
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkSyncForm">
                <div class="modal-body">
                    @csrf
                    <!-- Error/Success Messages Container -->
                    <div id="bulkSyncMessages"></div>
                    
                    <div class="alert alert-warning">
                        <i class="fe fe-alert-triangle me-2"></i>
                        Bulk operations may take time. Please be patient and do not close this window.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="user_ids" class="form-label">User IDs:</label>
                                <textarea class="form-control" id="user_ids" name="user_ids" rows="4" 
                                          placeholder="Enter user IDs separated by commas&#10;Example: user1, user2, user3"></textarea>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Separate multiple IDs with commas</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="user_emails" class="form-label">User Emails:</label>
                                <textarea class="form-control" id="user_emails" name="user_emails" rows="4" 
                                          placeholder="Enter user emails separated by commas&#10;Example: user1@example.com, user2@example.com"></textarea>
                                <div class="invalid-feedback"></div>
                                <small class="form-text text-muted">Separate multiple emails with commas</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center my-3">
                        <span class="badge bg-secondary">OR</span>
                    </div>
                    
                    <div class="form-group mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="sync_all_unverified" name="sync_all_unverified">
                            <label class="form-check-label" for="sync_all_unverified">
                                <strong>Sync all unverified users</strong>
                                <small class="text-muted d-block">This will sync all users who are not currently KYC verified (may take significant time)</small>
                            </label>
                        </div>
                    </div>
                    
                    <div id="bulkSyncProgress" class="d-none">
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                        </div>
                        <p class="text-center text-muted">Processing bulk sync...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="bulkSyncBtn">
                        <i class="fe fe-refresh-cw me-2"></i>Start Bulk Sync
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" id="resultModalHeader">
                <h5 class="modal-title" id="resultModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="resultModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let recentResults = [];

    // Single User Sync Form
    document.getElementById('singleSyncForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const userId = document.getElementById('user_id').value.trim();
        const userEmail = document.getElementById('user_email').value.trim();
        
        // Clear previous validation and messages
        clearValidation();
        clearMessages('singleSyncMessages');
        
        // Validation
        if (!userId && !userEmail) {
            showError('Please enter either a User ID or Email address.', 'singleSyncMessages');
            return;
        }
        
        if (userId && userEmail) {
            showWarning('Please enter either User ID OR Email, not both.', 'singleSyncMessages');
            return;
        }
        
        if (userEmail && !validateEmail(userEmail)) {
            showValidationError('user_email', 'Please enter a valid email address.');
            return;
        }
        
        // Show progress and disable form
        showProgress('single');
        
        const formData = new FormData();
        formData.append('_token', document.querySelector('#singleSyncForm input[name="_token"]').value);
        if (userId) formData.append('user_id', userId);
        if (userEmail) formData.append('user_email', userEmail);
        
        fetch('{{ route("admin.kyc.sync.user") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            
            return response.json();
        })
        .then(data => {
            hideProgress('single');
            
            if (data.success) {
                // Close modal immediately without showing any alert in modal
                
                const modalElement = document.getElementById('singleSyncModal');
                const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modal.hide();
                
                // Show SweetAlert immediately after closing modal
                setTimeout(() => {
                    Swal.fire({
                        title: 'KYC Sync Successful!',
                        text: 'User KYC data has been synced and verified successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#28a745',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        // Refresh page after user clicks OK
                        window.location.reload();
                    });
                }, 300); // Small delay to ensure modal is closed
                
            } else {
                showError(data.message || 'Sync failed. Please try again.', 'singleSyncMessages');
            }
        })
        .catch(error => {
            hideProgress('single');
            
            let errorMessage = 'An error occurred during sync.';
            
            if (error.errors) {
                // Validation errors
                Object.keys(error.errors).forEach(field => {
                    showValidationError(field, error.errors[field][0]);
                });
                errorMessage = 'Please correct the validation errors and try again.';
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            showError(errorMessage, 'singleSyncMessages');
        });
    });
    
    // Bulk Sync Form
    document.getElementById('bulkSyncForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const userIds = document.getElementById('user_ids').value.trim();
        const userEmails = document.getElementById('user_emails').value.trim();
        const syncAllUnverified = document.getElementById('sync_all_unverified').checked;
        
        // Clear previous validation and messages
        clearValidation();
        clearMessages('bulkSyncMessages');
        
        // Validation
        if (!userIds && !userEmails && !syncAllUnverified) {
            showError('Please provide User IDs, User Emails, or select sync all unverified users.', 'bulkSyncMessages');
            return;
        }
        
        // Validate user IDs if provided (just check they're not empty)
        if (userIds) {
            const userIdArray = userIds.split(',').map(id => id.trim()).filter(id => id);
            if (userIdArray.length === 0) {
                showValidationError('user_ids', 'Please provide valid User IDs.');
                return;
            }
        }
        
        // Validate emails if provided
        if (userEmails) {
            const userEmailArray = userEmails.split(',').map(email => email.trim()).filter(email => email);
            const invalidEmails = userEmailArray.filter(email => !validateEmail(email));
            if (invalidEmails.length > 0) {
                showValidationError('user_emails', `Invalid email addresses: ${invalidEmails.join(', ')}`);
                return;
            }
        }
        
        // Show progress and disable form
        showProgress('bulk');
        
        const formData = new FormData();
        formData.append('_token', document.querySelector('#bulkSyncForm input[name="_token"]').value);
        
        if (userIds) {
            const userIdArray = userIds.split(',').map(id => id.trim()).filter(id => id);
            userIdArray.forEach(id => formData.append('user_ids[]', id));
        }
        
        if (userEmails) {
            const userEmailArray = userEmails.split(',').map(email => email.trim()).filter(email => email && validateEmail(email));
            userEmailArray.forEach(email => formData.append('user_emails[]', email));
        }
        
        if (syncAllUnverified) {
            formData.append('sync_all_unverified', '1');
        }
        
        fetch('{{ route("admin.kyc.sync.bulk") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            hideProgress('bulk');
            
            if (data.success) {
                // Close modal first
                const modalElement = document.getElementById('bulkSyncModal');
                const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                
                // Wait for modal to fully close, then show SweetAlert
                modalElement.addEventListener('hidden.bs.modal', function modalHiddenHandler() {
                    // Remove this event listener to avoid duplicate calls
                    modalElement.removeEventListener('hidden.bs.modal', modalHiddenHandler);
                    
                    // Show success message with SweetAlert including stats
                    let successMessage = data.message;
                    if (data.data && data.data.total_processed) {
                        successMessage += `\n\nProcessed: ${data.data.total_processed}\nSuccessful: ${data.data.successful}\nFailed: ${data.data.failed}`;
                    }
                    
                    Swal.fire({
                        title: 'Bulk Sync Complete!',
                        text: successMessage,
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        // Refresh page after user clicks OK
                        location.reload();
                    });
                });
                
                // Close the modal
                modal.hide();
                
                handleSyncResult(data, 'bulk');
            } else {
                showError(data.message || 'Bulk sync failed. Please try again.', 'bulkSyncMessages');
            }
        })
        .catch(error => {
            hideProgress('bulk');
            
            let errorMessage = 'An error occurred during bulk sync.';
            
            if (error.errors) {
                // Validation errors
                Object.keys(error.errors).forEach(field => {
                    showValidationError(field, error.errors[field][0]);
                });
                errorMessage = 'Please correct the validation errors and try again.';
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            showError(errorMessage, 'bulkSyncMessages');
        });
    });
    
    // Helper Functions
    function showProgress(type) {
        const progressElement = document.getElementById(type + 'SyncProgress');
        const btnElement = document.getElementById(type + 'SyncBtn');
        
        progressElement.classList.remove('d-none');
        btnElement.disabled = true;
        btnElement.innerHTML = '<i class="fe fe-loader fe-spin me-2"></i>Processing...';
    }
    
    function hideProgress(type) {
        const progressElement = document.getElementById(type + 'SyncProgress');
        const btnElement = document.getElementById(type + 'SyncBtn');
        
        progressElement.classList.add('d-none');
        btnElement.disabled = false;
        
        if (type === 'single') {
            btnElement.innerHTML = '<i class="fe fe-refresh-cw me-2"></i>Sync KYC';
        } else {
            btnElement.innerHTML = '<i class="fe fe-refresh-cw me-2"></i>Start Bulk Sync';
        }
    }
    
    function handleSyncResult(data, type) {
        // Add to recent results
        const timestamp = new Date().toLocaleString();
        recentResults.unshift({
            timestamp: timestamp,
            type: type,
            data: data
        });
        
        // Keep only last 10 results
        if (recentResults.length > 10) {
            recentResults = recentResults.slice(0, 10);
        }
        
        updateRecentResults();
        showResultModal(data, type);
        
        // Close the sync modal
        const modalElement = document.getElementById(type + 'SyncModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) modal.hide();
        
        // Reset form
        document.getElementById(type + 'SyncForm').reset();
    }
    
    function handleError(error, type) {
        console.error('Sync error:', error);
        const containerId = type + 'SyncMessages';
        showError('An unexpected error occurred. Please try again.', containerId);
    }
    
    function showResultModal(data, type) {
        const modal = new bootstrap.Modal(document.getElementById('resultModal'));
        const header = document.getElementById('resultModalHeader');
        const title = document.getElementById('resultModalLabel');
        const body = document.getElementById('resultModalBody');
        
        // Set header color and title based on result
        if (data.status) {
            header.className = 'modal-header bg-success text-white';
            title.innerHTML = '<i class="fe fe-check-circle me-2"></i>Sync Successful';
        } else {
            header.className = 'modal-header bg-danger text-white';
            title.innerHTML = '<i class="fe fe-x-circle me-2"></i>Sync Failed';
        }
        
        // Build result content
        let content = `<div class="alert ${data.status ? 'alert-success' : 'alert-danger'}">
            <strong>${data.message}</strong>
        </div>`;
        
        if (data.data) {
            if (type === 'bulk' && data.data.results) {
                content += '<h6 class="fw-semibold">Detailed Results:</h6>';
                content += '<div class="table-responsive">';
                content += '<table class="table table-striped">';
                content += '<thead><tr><th>User ID</th><th>Email</th><th>Status</th><th>Message</th></tr></thead>';
                content += '<tbody>';
                
                data.data.results.forEach(result => {
                    const statusClass = result.success ? 'text-success' : 'text-danger';
                    const statusIcon = result.success ? 'fe-check' : 'fe-x';
                    content += `<tr>
                        <td>${result.user_id}</td>
                        <td>${result.email}</td>
                        <td class="${statusClass}"><i class="fe ${statusIcon}"></i></td>
                        <td><small>${result.message}</small></td>
                    </tr>`;
                });
                
                content += '</tbody></table>';
                content += '</div>';
                
                content += `<div class="mt-3">
                    <span class="badge bg-primary">Total: ${data.data.total_processed}</span>
                    <span class="badge bg-success ms-2">Successful: ${data.data.successful}</span>
                    <span class="badge bg-danger ms-2">Failed: ${data.data.failed}</span>
                </div>`;
            } else if (type === 'single') {
                content += `<div class="mt-3">
                    <div class="row">
                        <div class="col-sm-4"><strong>User ID:</strong></div>
                        <div class="col-sm-8">${data.data.user_id}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Email:</strong></div>
                        <div class="col-sm-8">${data.data.email}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>KYC Status:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge ${data.data.kyc_status == 1 ? 'bg-success' : 'bg-warning'}">
                                ${data.data.kyc_status == 1 ? 'Verified' : 'Not Verified'}
                            </span>
                        </div>
                    </div>
                </div>`;
            }
        }
        
        body.innerHTML = content;
        modal.show();
    }
    
    function updateRecentResults() {
        const container = document.getElementById('recentResults');
        
        if (recentResults.length === 0) {
            container.innerHTML = `<div class="text-muted text-center py-4">
                <i class="fe fe-clock fe-2x mb-2"></i>
                <p>No recent sync operations.</p>
            </div>`;
            return;
        }
        
        let html = '';
        recentResults.forEach((result, index) => {
            const statusClass = result.data.status ? 'text-success' : 'text-danger';
            const statusIcon = result.data.status ? 'fe-check-circle' : 'fe-x-circle';
            const typeLabel = result.type === 'single' ? 'Single User' : 'Bulk Sync';
            const bgClass = result.data.status ? 'bg-success-transparent' : 'bg-danger-transparent';
            
            html += `<div class="card border-0 ${bgClass} mb-2">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fe ${statusIcon} ${statusClass} fe-2x"></i>
                        </div>
                        <div class="flex-fill">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1 fw-semibold">${typeLabel}</h6>
                                    <p class="mb-1 ${statusClass}">${result.data.message}</p>
                                    <small class="text-muted">${result.timestamp}</small>
                                </div>
                                ${result.data.data && result.type === 'bulk' ? 
                                    `<div class="text-end">
                                        <span class="badge bg-primary">${result.data.data.total_processed}</span>
                                    </div>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        
        container.innerHTML = html;
    }
    
    function clearValidation() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }
    
    function showValidationError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const feedback = field.nextElementSibling;
        
        field.classList.add('is-invalid');
        feedback.textContent = message;
    }
    
    function showError(message, containerId = null) {
        const alertHtml = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fe fe-alert-triangle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
        
        // Show in specific container if provided, otherwise in the current modal body
        if (containerId) {
            const container = document.getElementById(containerId.replace('#', ''));
            if (container) {
                container.innerHTML = alertHtml;
                return;
            }
        }
        
        const activeModal = document.querySelector('.modal.show .modal-body');
        if (activeModal) {
            activeModal.insertAdjacentHTML('afterbegin', alertHtml);
        }
    }
    
    function showSuccess(message, containerId = null) {
        const alertHtml = `<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fe fe-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
        
        if (containerId) {
            const container = document.getElementById(containerId.replace('#', ''));
            if (container) {
                container.innerHTML = alertHtml;
                return;
            }
        }
        
        const activeModal = document.querySelector('.modal.show .modal-body');
        if (activeModal) {
            activeModal.insertAdjacentHTML('afterbegin', alertHtml);
        }
    }
    
    function showWarning(message, containerId = null) {
        const alertHtml = `<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fe fe-alert-triangle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
        
        if (containerId) {
            const container = document.getElementById(containerId.replace('#', ''));
            if (container) {
                container.innerHTML = alertHtml;
                return;
            }
        }
        
        const activeModal = document.querySelector('.modal.show .modal-body');
        if (activeModal) {
            activeModal.insertAdjacentHTML('afterbegin', alertHtml);
        }
    }
    
    function clearMessages(containerId) {
        const container = document.getElementById(containerId.replace('#', ''));
        if (container) {
            container.innerHTML = '';
        }
    }
    
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // Clear form when modal is hidden
    document.getElementById('singleSyncModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('singleSyncForm').reset();
        clearValidation();
        clearMessages('singleSyncMessages');
    });
    
    document.getElementById('bulkSyncModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('bulkSyncForm').reset();
        clearValidation();
        clearMessages('bulkSyncMessages');
    });
});
</script>
@endsection
