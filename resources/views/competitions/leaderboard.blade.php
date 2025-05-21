@extends(
    auth()->user()->role->name === 'Admin' || auth()->user()->role->name === 'Super Admin'
        ? 'layouts.admin.admin'
        : 'layouts.crm.crm'
)

@section('content')
<div class="pc-container">
    <div class="pc-content">
      @if(session('error'))
          <div class="alert alert-danger mt-4">{{ session('error') }}</div>
      @endif

    </div>
  </div>
@endsection
