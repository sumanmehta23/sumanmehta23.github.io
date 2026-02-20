@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">2FA Settings</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">2FA Settings</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->
            <!-- ROW-1 OPEN -->
            <div class="row">
                <div class="col-lg-12"></div>
                <div class="col-lg-12 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">Two Factor Authentication </div>
                        </div>
                        <div class="card-body">
                            <x-admin-two-factor-authentication />
                            {{-- <x-two-factor-authentication /> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
