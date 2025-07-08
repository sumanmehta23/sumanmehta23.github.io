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
                                    <input type="text" class="form-control" name="admin_title"
                                        value="{{ $settings['admin_title'] }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Icon</label>
                                    <?php if (!empty($settings['favicon'])): ?>
                                    <img src="/{{ $settings['favicon'] }}" alt="Icon" width="50" height="50"
                                        class="ml-4" style="object-fit: contain;">
                                    <?php endif; ?>
                                    <input class="form-control" type="file" name="favicon">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Logo</label>
                                    <?php if (!empty($settings['admin_sidebar_logo'])): ?>
                                    <img src="/{{ $settings['admin_sidebar_logo'] }}" alt="Logo" class="ml-4"
                                        style="object-fit: contain;max-width: 200px;height: auto;">
                                    <?php endif; ?>

                                    <input class="form-control" type="file" name="admin_sidebar_logo">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Dark Logo</label>
                                    <?php if (!empty($settings['admin_sidebar_logo_dark'])): ?>
                                    <img src="/{{ $settings['admin_sidebar_logo_dark'] }}" alt="Logo" class="ml-4"
                                        style="object-fit: contain;max-width: 200px;height: auto;">
                                    <?php endif; ?>

                                    <input class="form-control" type="file" name="admin_sidebar_logo_dark">
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">Primary Color</label>
                                    <input class="form-control" type="color" name="sidebar_color"
                                        value="{{ $settings['sidebar_color'] }}">
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
                                    <input type="text" class="form-control" name="sender_name"
                                        value="{{ $settings['sender_name'] }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Sender Email Address</label>
                                    <input type="text" class="form-control" name="sender_email_address"
                                        value="{{ $settings['sender_email_address'] }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">API Key</label>
                                    <input type="text" class="form-control" name="api_key"
                                        value="{{ $settings['api_key'] }}">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Partner Key</label>
                                    <input type="text" class="form-control" name="partner_key"
                                        value="{{ $settings['partner_key'] }}">
                                </div>
                            </div>
                            <div class="card-footer  text-end">
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
                                    <input type="text" class="form-control" name="mt5_company_name"
                                        value="{{ $settings['mt5_company_name'] }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">MT5 Server IP</label>
                                    <input type="text" class="form-control" name="mt5_server_ip"
                                        value="{{ $settings['mt5_server_ip'] }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">MT5 Server Port</label>
                                    <input type="text" class="form-control" name="mt5_server_port"
                                        value="{{ $settings['mt5_server_port'] }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">MT5 Server Web Login</label>
                                    <input type="text" class="form-control" name="mt5_server_web_login"
                                        value="{{ $settings['mt5_server_web_login'] }}" required>
                                </div>
                                <div class="mb-0" hidden>
                                    <label class="form-label">MT5 Server Web Password</label>
                                    <input type="text" class="form-control" name="mt5_server_web_password"
                                        value="{{ $settings['mt5_server_web_password'] }}" required>
                                </div>
                            </div>
                            <div class="card-footer  text-end">
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
                                    <input type="text" class="form-control" name="mt5_windows_platform"
                                        value="{{ $settings['mt5_windows_platform'] }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Android</label>
                                    <input type="text" class="form-control" name="mt5_android_platform"
                                        value="{{ $settings['mt5_android_platform'] }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Apple / iOS</label>
                                    <input type="text" class="form-control" name="mt5_ios_platform"
                                        value="{{ $settings['mt5_ios_platform'] }}">
                                </div>

                            </div>
                            <div class="card-footer  text-end">
                                <input type="submit" class="btn btn-primary" value="Update" name="update">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">Two Factor Authentication </div>
                        </div>
                        <div class="card-body">
                            <x-admin-two-factor-authentication/>
                            {{-- <x-two-factor-authentication /> --}}
                        </div>
                </div>
            </div>
          <div class="col-lg-4 col-sm-12">
                 @include('admin.payment_gateways_setting')
          </div>
          <div class="col-lg-4 col-sm-12">
                 @include('admin.toggle_group_code')
          </div>
        </div>
    </div>
@endSection
