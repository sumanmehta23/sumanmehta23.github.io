@extends('layouts.admin.admin')
@section('content')
    <div class="text-center error-page p-2">
        <div class="error-template">
            <h1 class="display-1 text-primary mb-2">401</h1>
            <span class="text-transparent fs-20 text-secondary">Permission Denied</span>
            <h5 class="error-details text-primary">
                Sorry, You don't have permission to access this page!!! Please contact admin for support
            </h5>
        </div>
    </div>
@endsection
