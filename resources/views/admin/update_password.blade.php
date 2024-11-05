@extends('layouts.admin.admin')
@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- PAGE-HEADER -->
        <div class="page-header">
            <h1 class="page-title">Update Password</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Update Passowrd</li>
            </ol>
        </div>
        <!-- PAGE-HEADER END -->

        <!-- ROW-1 OPEN -->
        <div class="row">
            <div class="col-12">
                <form method="post" action="{{route('admin.update_password')}}">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Old Password</label>
                                <input type="password" class="form-control" name="oldpassword" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="newpassword" required>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="newpassword_confirmation" required>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <input type="submit" class="btn btn-primary" value="Update" name="change_password">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endSection
