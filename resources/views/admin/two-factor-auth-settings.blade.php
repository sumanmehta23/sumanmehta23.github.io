@extends('layouts.admin.admin')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">2FA Authentication</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">2FA Authentication</li>
                </ol>
            </div>
            <!-- PAGE-HEADER END -->
            <!-- ROW-1 OPEN -->
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">Two Factor Authentication</div>
                        </div>
                        <div class="card-body">
                            <x-admin-two-factor-authentication/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
