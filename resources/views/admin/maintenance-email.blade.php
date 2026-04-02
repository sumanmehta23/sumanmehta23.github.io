@extends('layouts.admin.admin')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Maintenance Email Broadcasting</h1>
            <p class="text-muted">Send maintenance notifications to users</p>
        </div>

        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="loading-overlay" style="display: none;">
            <div class="spinner-container">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-white fw-bold">Processing...</p>
                <p class="text-white-50">Please wait a moment</p>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="row mb-3">
            <div class="col-xl-12">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="recipientsTab" data-bs-toggle="tab" data-bs-target="#recipientsContent" type="button" role="tab">
                            <i class="ti ti-mail"></i> Send Emails
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="previewTab" data-bs-toggle="tab" data-bs-target="#previewContent" type="button" role="tab">
                            <i class="ti ti-eye"></i> Email Template Preview
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Recipients Tab -->
            <div class="tab-pane fade show active" id="recipientsContent" role="tabpanel">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card custom-card">
                            <div class="card-body">
                                <!-- Fetch Button -->
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary" id="fetchBtn" onclick="fetchEmailsFromDB()">
                                        <i class="ti ti-download"></i> Fetch Emails from Database
                                    </button>
                                    <small class="text-muted ms-2">Or paste emails manually below</small>
                                </div>

                                <!-- Email Textarea -->
                                <div class="mb-3">
                                    <label for="emailsTextarea" class="form-label">Email Addresses</label>
                                    <textarea id="emailsTextarea" class="form-control" rows="12" placeholder="user1@example.com&#10;user2@example.com&#10;user3@example.com&#10;&#10;OR comma-separated:&#10;user1@example.com, user2@example.com, user3@example.com"></textarea>
                                    <small class="text-muted d-block mt-2">
                                        <strong>Format:</strong> One per line OR comma-separated &nbsp; | &nbsp; <strong>Total Emails:</strong> <span id="emailCount">0</span>
                                    </small>
                                </div>

                                <!-- Send Button -->
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-lg" id="sendBtn" onclick="sendEmailsNow()" disabled>
                                        <i class="ti ti-send"></i> Send Emails
                                    </button>
                                    <span id="statusText" class="align-self-center text-muted ms-2"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Template Preview Tab -->
            <div class="tab-pane fade" id="previewContent" role="tabpanel">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card custom-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Email Template Preview</h5>
                            </div>
                            <div class="card-body" style="padding: 0; max-height: 700px; overflow-y: auto;">
                                <iframe id="emailPreviewFrame" style="width: 100%; height: 700px; border: none; background: white;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(4px);
}

.spinner-container {
    text-align: center;
    background: rgba(255, 255, 255, 0.1);
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
}

.spinner-border {
    width: 60px;
    height: 60px;
}

.nav-tabs {
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    border-bottom: 3px solid transparent;
    padding: 1rem;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom-color: #0d6efd;
    background: transparent;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #ddd;
}
</style>

<script>
// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadEmailPreview();
});

// Load email template preview
function loadEmailPreview() {
    const previewFrame = document.getElementById('emailPreviewFrame');
    if (previewFrame) {
        const previewUrl = '{{ route("admin.maintenance.preview") }}';
        previewFrame.src = previewUrl;
    }
}

// Parse emails
function parseEmails(text) {
    const separator = text.includes(',') ? ',' : '\n';
    return text.split(separator)
        .map(e => e.trim())
        .filter(e => e && e.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/));
}

const textarea = document.getElementById('emailsTextarea');
const sendBtn = document.getElementById('sendBtn');
const fetchBtn = document.getElementById('fetchBtn');
const loadingOverlay = document.getElementById('loadingOverlay');

// Update email count
textarea.addEventListener('input', function() {
    const emails = parseEmails(this.value);
    document.getElementById('emailCount').textContent = emails.length;
    sendBtn.disabled = emails.length === 0;
});

// Fetch emails from database
function fetchEmailsFromDB() {
    fetchBtn.disabled = true;
    loadingOverlay.style.display = 'flex';
    document.querySelector('.spinner-container p:first-of-type').textContent = 'Fetching emails from database...';
    document.getElementById('statusText').textContent = 'Fetching...';

    fetch('{{ route("admin.maintenance.fetch") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            textarea.value = data.emails.join('\n');
            textarea.dispatchEvent(new Event('input'));
            document.getElementById('statusText').textContent = `✓ ${data.count} emails loaded`;
            showNotification('success', 'Fetched', `${data.count} emails loaded successfully!`);
        } else {
            showNotification('error', 'Error', data.message || 'Failed to fetch emails');
            document.getElementById('statusText').textContent = 'Error fetching emails';
        }
    })
    .catch(err => {
        console.error('Fetch error:', err);
        showNotification('error', 'Error', 'Failed to fetch emails: ' + err.message);
        document.getElementById('statusText').textContent = 'Error';
    })
    .finally(() => {
        fetchBtn.disabled = false;
        loadingOverlay.style.display = 'none';
    });
}

// Send emails
function sendEmailsNow() {
    const emails = parseEmails(textarea.value);
    
    if (emails.length === 0) {
        showNotification('warning', 'No Emails', 'Please enter at least one valid email');
        return;
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Send Maintenance Email?',
            html: `<p>Send to <strong>${emails.length} email(s)</strong></p>
                   <p class="text-muted">Default template will be sent</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Send',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d'
        }).then(result => {
            if (result.isConfirmed) {
                performEmailSend(emails);
            }
        });
    } else {
        if (confirm(`Send to ${emails.length} email(s)?`)) {
            performEmailSend(emails);
        }
    }
}

function performEmailSend(emails) {
    sendBtn.disabled = true;
    fetchBtn.disabled = true;
    textarea.disabled = true;
    loadingOverlay.style.display = 'flex';
    document.querySelector('.spinner-container p:first-of-type').textContent = 'Sending emails...';
    document.getElementById('statusText').textContent = 'Sending...';

    fetch('{{ route("admin.maintenance.send") }}', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ emails: emails })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('success', 'Queued!', `✅ ${data.sent} emails queued successfully!`);
            document.getElementById('statusText').textContent = `✓ Sent: ${data.sent}`;
            textarea.value = '';
            textarea.dispatchEvent(new Event('input'));
        } else {
            showNotification('error', 'Error', data.message || 'Failed to send');
            document.getElementById('statusText').textContent = 'Error';
        }
    })
    .catch(err => {
        showNotification('error', 'Error', 'Failed: ' + err.message);
        document.getElementById('statusText').textContent = 'Error';
    })
    .finally(() => {
        sendBtn.disabled = false;
        fetchBtn.disabled = false;
        textarea.disabled = false;
        loadingOverlay.style.display = 'none';
    });
}

// Notification
function showNotification(type, title, message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({icon: type, title: title, text: message, timer: 4000, timerProgressBar: true});
    } else {
        alert(`${title}: ${message}`);
    }
}
</script>
@endsection
