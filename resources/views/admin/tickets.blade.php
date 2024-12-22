@extends('layouts.admin.admin')
@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
      <div class="page-header">
        <h1 class="page-title">All Tickets</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page">All Tickets</li>
        </ol>
      </div>
      <div class="row p-3">
        @foreach ($tickets as $ticket)
            <div class="col-12 mt-3">
                <div class="card custom-card shadow-none mb-0 ribbon-card">
                    <div class="card-body p-4">
                        <div class="ribbon ribbon-{{ $ticket->ticket_label }} ribbon-top-left">
                            <span>{{ $ticket->ticket_status }}</span>
                        </div>
                        <div class="card-subtitle fw-semibold mb-2">
                            <div class="row">
                                <div class="col-7">
                                    <div class="ms-5">
                                        <h3>#TICKET-{{ sprintf('%02d', $ticket->ticket_id + 10000) }}</h3>
                                        <div class="text-muted">
                                            <span><i class="ri-calendar-2-line text-secondary"></i> {{ $ticket->created_at }}</span>
                                            <span class="ms-3"><i class="ri-user-line text-secondary"></i> {{ $ticket->created_user }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-5 text-end">
                                    <div class="d-flex w-100 justify-content-end">
                                        <div class="d-flex align-items-center text-start">
                                            <div class="ms-5 text-start">
                                                <p class="text-muted mb-0">{{ $ticket->fullname }}</p>
                                                <p class="fw-medium fs-16 mb-0">{{ $ticket->email_id }}</p>
                                            </div>
                                        </div>
                                        <div class="ms-2">
                                            <span class="avatar avatar-lg avatar-rounded">
                                                <img src="{{ asset('admin_assets/assets/images/users/client.png') }}" alt="img">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-8">
                                <div class="d-flex w-100 ms-5">
                                    <div class="d-flex align-items-center justify-content-between w-100 flex-wrap align-items-center">
                                        <div class="me-3">
                                            <p class="text-muted mb-0">{{ $ticket->subject_name }}</p>
                                            <p class="fs-16 mb-0">{{ $ticket->discription }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer justify-content-between d-flex">
                        <div>Ticket Type: <span class="fw-semibold badge bg-outline-primary">{{ $ticket->ticket_type }}</span></div>
                        <div>Last Follow-Up Date: <span class="fw-semibold badge bg-outline-info">{{ $ticket->last_followup }}</span></div>
                        <div>Last Follow-Up By:
                            <span class="fw-semibold badge bg-outline-success">
                                {{ $ticket->followup_type == 'admin' ? ($ticket->followup_admin ?? 'N/A') : ($ticket->followup_user ?? 'N/A') }}
                            </span>
                        </div>
                        <a href="{{ route('admin.ticket_details', ['id' => ($ticket->ticket_id)]) }}" class="btn btn-info">
                            View <i class="ri-arrow-right-line ms-2 d-inline-block align-middle"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="row mb-5 p-3">
        <nav>
            <form method="post" action="">
                @csrf
                <ul class="pagination mb-0 d-flex justify-content-end">
                    @for ($i = 1; $i <= $total_pages; $i++)
                        <li class="page-item">
                            <button type="submit" name="page" value="{{ $i }}" class="page-link {{ $current_page == $i ? 'active' : '' }}">
                                {{ $i }}
                            </button>
                        </li>
                    @endfor
                </ul>
            </form>
        </nav>
    </div>

    </div>
  </div>
    @include('admin.shared.script');
@endsection
