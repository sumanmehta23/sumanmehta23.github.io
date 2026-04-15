@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">UI Settings</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">UI Settings</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->
            <!-- ROW-1 OPEN -->
            <div class="row">
                <div class="col-lg-4">
                    <form method="post" action="/admin/ui_settings" enctype="multipart/form-data">
                        @csrf
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">App Title</label>
                                    <input type="text" class="form-control" name="admin_title" value="{{ $settings['admin_title'] }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Icon</label>
                                    <?php if (!empty($settings['favicon'])): ?>
                                    <img src="/{{ $settings['favicon'] }}" alt="Icon" width="50" height="50" class="ml-4" style="object-fit: contain;">
                                    <?php endif; ?>
                                    <input class="form-control" type="file" name="favicon">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Logo</label>
                                    <?php if (!empty($settings['admin_sidebar_logo'])): ?>
                                    <img src="/{{ $settings['admin_sidebar_logo'] }}" alt="Logo" class="ml-4" style="object-fit: contain;max-width: 200px;height: auto;">
                                    <?php endif; ?>

                                    <input class="form-control" type="file" name="admin_sidebar_logo">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Dark Logo</label>
                                    <?php if (!empty($settings['admin_sidebar_logo_dark'])): ?>
                                    <img src="/{{ $settings['admin_sidebar_logo_dark'] }}" alt="Logo" class="ml-4" style="object-fit: contain;max-width: 200px;height: auto;">
                                    <?php endif; ?>

                                    <input class="form-control" type="file" name="admin_sidebar_logo_dark">
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">Primary Color</label>
                                    <input class="form-control" type="color" name="sidebar_color" value="{{ $settings['sidebar_color'] }}">
                                </div>

                            </div>
                            <div class="card-footer text-end">
                                <input type="submit" class="btn btn-primary" value="Update" name="update">
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-lg-4 col-sm-12">
                    <form method="post">
                        @csrf
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">SMTP Configuration</div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Sender Name</label>
                                    <input type="text" class="form-control" name="sender_name" value="{{ $settings['sender_name'] }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Sender Email Address</label>
                                    <input type="text" class="form-control" name="sender_email_address" value="{{ $settings['sender_email_address'] }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">API Key</label>
                                    <input type="text" class="form-control" name="api_key" value="{{ $settings['api_key'] }}">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Partner Key</label>
                                    <input type="text" class="form-control" name="partner_key" value="{{ $settings['partner_key'] }}">
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <input type="submit" class="btn btn-primary" value="Update" name="update">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-sm-12">
                    <form method="post">
                        @csrf
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">MT5 Server Details</div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">MT5 Company Name</label>
                                    <input type="text" class="form-control" name="mt5_company_name" value="{{ $settings['mt5_company_name'] }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">MT5 Server IP</label>
                                    <input type="text" class="form-control" name="mt5_server_ip" value="{{ $settings['mt5_server_ip'] }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">MT5 Server Port</label>
                                    <input type="text" class="form-control" name="mt5_server_port" value="{{ $settings['mt5_server_port'] }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">MT5 Server Web Login</label>
                                    <input type="text" class="form-control" name="mt5_server_web_login" value="{{ $settings['mt5_server_web_login'] }}" required>
                                </div>
                                <div class="mb-0" hidden>
                                    <label class="form-label">MT5 Server Web Password</label>
                                    <input type="text" class="form-control" name="mt5_server_web_password" value="{{ $settings['mt5_server_web_password'] }}" required>
                                </div>
                                                                   <div class="pt-3 mt-4 border-top">
                                        <label class="form-label d-flex justify-content-between align-items-center">
                                            <span>Server Usage</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="refresh-server-usage">
                                                <i class="ti ti-refresh"></i> Refresh
                                            </button>
                                        </label>
                                        <div id="server-usage-error" style="display: none;">
                                            <div class="alert alert-danger alert-sm" style="padding: 8px; font-size: 12px;"></div>
                                        </div>
                                        <div id="server-usage-loading" style="display: none;" class="text-center">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                        <div id="server-usage-container" style="display: none;">
                                            <div class="mb-2">
                                                <div class="progress" style="height: 25px;">
                                                    <div id="usage-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                        <span id="usage-percentage" style="font-size: 12px; font-weight: bold;">0%</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <small class="text-muted">
                                                    <span id="total-users-display">0</span> / <span id="limit-accounts-display">0</span> accounts in use
                                                </small>
                                            </div>
                                            <div class="mt-2 text-center">
                                                <small class="d-block text-info"><strong id="real-users-display">0</strong> real accounts</small>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                            <div class="card-footer text-end">
                                <input type="submit" class="btn btn-primary" value="Update" name="update">
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-lg-4 col-sm-12">
                    <form method="post">
                        @csrf
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">Platform Download</div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Windows</label>
                                    <input type="text" class="form-control" name="mt5_windows_platform" value="{{ $settings['mt5_windows_platform'] }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Android</label>
                                    <input type="text" class="form-control" name="mt5_android_platform" value="{{ $settings['mt5_android_platform'] }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Apple / iOS</label>
                                    <input type="text" class="form-control" name="mt5_ios_platform" value="{{ $settings['mt5_ios_platform'] }}">
                                </div>

                            </div>
                            <div class="card-footer text-end">
                                <input type="submit" class="btn btn-primary" value="Update" name="update">
                            </div>
                        </div>
                    </form>
                </div>
                {{-- <div class="col-lg-4 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">Two Factor Authentication </div>
                        </div>
                        <div class="card-body">
                            <x-admin-two-factor-authentication />
                        </div>
                    </div>
                </div> --}}
                <div class="col-lg-4 col-sm-12">
                    @include('admin.payment_gateways_setting')
                </div>
                <div class="col-lg-4 col-sm-12">
                    @include('admin.toggle_group_code')
                </div>
                <div class="col-lg-4 col-sm-12">
                    @include('admin.toggle_ib_request')
                </div>
                <div class="col-lg-4 col-sm-12">
                    @include('admin.kyc_provider_setting')
                </div>

                <div class="col-lg-4 col-sm-12">
                    <form method="post">
                        @csrf
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">URL Settings</div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Main Website URL</label>
                                    <input type="url" class="form-control" name="main_website_url" 
                                           value="{{ $settings['main_website_url'] ?? 'https://www.lqhmarkets.com' }}"
                                           placeholder="https://www.lqhmarkets.com">
                                    <small class="mt-2 text-muted d-block">
                                        <i class="fas fa-info-circle"></i> Main website URL used on public pages (e.g., https://www.lqhmarkets.com)
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <input type="submit" class="btn btn-primary" value="Update" name="update">
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
         <script>
        document.addEventListener('DOMContentLoaded', function() {
            const refreshBtn = document.getElementById('refresh-server-usage');
            const usageContainer = document.getElementById('server-usage-container');
            const usageError = document.getElementById('server-usage-error');
            const usageLoading = document.getElementById('server-usage-loading');
            const progressBar = document.getElementById('usage-progress-bar');
            const percentageDisplay = document.getElementById('usage-percentage');
            const totalUsersDisplay = document.getElementById('total-users-display');
            const limitAccountsDisplay = document.getElementById('limit-accounts-display');
            const realUsersDisplay = document.getElementById('real-users-display');

            function fetchAndUpdateUsage() {
                // Reset display states
                usageContainer.style.display = 'none';
                usageError.style.display = 'none';
                usageLoading.style.display = 'block';
                refreshBtn.disabled = true;

                fetch("{{ route('admin.server.common.get') }}")
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(result => {
                        if (result.success && result.data) {
                            const data = result.data;
                            const totalUsers = data.total_users || 0;
                            const limitAccounts = data.limit_accounts || 1;
                            const realUsers = data.total_users_real || 0;
                            
                            // Calculate percentage
                            const percentage = Math.round((totalUsers / limitAccounts) * 100);
                            
                            // Update progress bar
                            progressBar.style.width = percentage + '%';
                            progressBar.setAttribute('aria-valuenow', percentage);
                            percentageDisplay.textContent = percentage + '%';
                            
                            // Update display values
                            totalUsersDisplay.textContent = totalUsers.toLocaleString();
                            limitAccountsDisplay.textContent = limitAccounts.toLocaleString();
                            realUsersDisplay.textContent = realUsers.toLocaleString();
                            
                            // Update progress bar color based on usage
                            if (percentage >= 90) {
                                progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated', 'bg-success', 'bg-warning');
                                progressBar.classList.add('bg-danger');
                            } else if (percentage >= 70) {
                                progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated', 'bg-success', 'bg-danger');
                                progressBar.classList.add('bg-warning');
                            } else {
                                progressBar.classList.remove('bg-warning', 'bg-danger');
                                progressBar.classList.add('bg-success', 'progress-bar-striped', 'progress-bar-animated');
                            }
                            
                            usageContainer.style.display = 'block';
                            usageError.style.display = 'none';
                        } else {
                            throw new Error('Invalid response format');
                        }
                    })
                    .catch(error => {
                        const errorAlert = usageError.querySelector('.alert-danger');
                        errorAlert.textContent = 'Failed to fetch server information: ' + error.message;
                        usageError.style.display = 'block';
                        usageContainer.style.display = 'none';
                    })
                    .finally(() => {
                        usageLoading.style.display = 'none';
                        refreshBtn.disabled = false;
                    });
            }

            // Add refresh button event listener
            refreshBtn.addEventListener('click', fetchAndUpdateUsage);
            
            // Load data on page load
            fetchAndUpdateUsage();
        });
    </script>
    @endSection
