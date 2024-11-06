@extends('layouts.crm.crm')
@section('content')
<style>
    .ribbon-top-left {
      top: -7px;
      inset-inline-start: -7px;
    }

    .ribbon {
      width: 80px;
      height: 80px;
      overflow: hidden;
      position: absolute;
      z-index: 1;
    }

    .ribbon.ribbon-danger span {
      background-color: rgb(var(--danger-rgb));
    }

    .ribbon-top-left span {
      inset-inline-end: -12px;
      top: 20px;
      transform: rotate(-45deg);
    }

    .ribbon span {
      position: absolute;
      display: block;
      width: 120px;
      padding: 6px 0;
      z-index: 2;
      box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
      color: #fff;
      font: 500 12px / 1 "Lato", sans-serif;
      text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
      text-transform: uppercase;
      text-align: center;
    }
  </style>
<div class="modal fade" id="addTicketModal" tabindex="-1" aria-labelledby="addTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="addTicketModalLabel1">New Ticket</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="row gy-4">
                        <div class="col-6">
                            <label for="input-name" class="form-label">Name</label>
                            <input type="text" class="form-control" name="subject_name" required>
                        </div>
                        <div class="col-6">
                            <label for="input-type" class="form-label">Type</label>
                            <select class="form-control" name="ticket_type_id" required>
                                <option value="">Select Type</option>
                                @foreach ($ticket_types as $type)
                                    <option value="{{ $type['id'] }}">{{ $type['ticket_type'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="input-description" class="form-label">Description</label>
                            <textarea class="form-control" name="discription" required rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="email" value="{{ session('clogin') }}" />
                    <input type="submit" class="btn bg-primary text-white" name="add_ticket" value="Submit Ticket">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="pc-container">
    <div class="pc-content">
        <div class="page-header mb-0 pb-0">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <div class="page-header-title h2">
                            <h4 class="mb-0">Tickets</h4>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <div class="d-grid">
                            <button data-bs-toggle="modal" data-bs-target="#addTicketModal" class="btn btn-primary d-grid">
                                <span class="text-truncate w-100">Create New Ticket</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card support-tickets ribbon-box border ribbon-fill shadow-none pb-1">
                    <div class="row p-3">
                        @foreach ($tickets as $ticket)
                            <div class="card-body mt-3">
                                <div class="card custom-card shadow-none mb-0 ribbon-card">
                                    <div class="card-body p-4">
                                        <div class="ribbon ribbon-top-left">
                                            <span class="bg-{{ $ticket->ticket_label }}">{{ $ticket->ticket_status }}</span>
                                        </div>
                                        <div class="card-subtitle fw-semibold mb-2">
                                            <div class="row">
                                                <div class="col-7">
                                                    <div class="ms-5">
                                                        <h3>
                                                            #TICKET-{{ sprintf('%02d', $ticket->ticket_id + 10000) }}
                                                        </h3>
                                                        <div class="text-muted">
                                                            <span><i data-feather="calendar"></i>{{ $ticket->created_at }}</span>
                                                            <span class="ms-3"><i data-feather="user"></i>{{ $ticket->created_user }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-5 text-end">
                                                    <div class="d-flex w-100 justify-content-end">
                                                        <!-- You can uncomment and use this if needed
                                                        <div class="d-flex align-items-center text-start">
                                                            <div class="ms-5 text-start">
                                                                <p class="text-muted mb-0">{{ $ticket->fullname }}</p>
                                                                <p class="fw-medium fs-16 mb-0">{{ $ticket->email_id }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="ms-2">
                                                            <span class="avatar avatar-lg avatar-rounded">
                                                                <img src="/admin_assets/assets/images/users/client.png" alt="img" style="width:50px">
                                                            </span>
                                                        </div>
                                                        -->
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
                                        <div>Ticket Type: <span class="fw-semibold badge bg-outline-primary text-dark">{{ $ticket->ticket_type }}</span></div>
                                        <div>Last Follow-Up Date: <span class="fw-semibold badge bg-outline-info text-dark">{{ $ticket->last_followup }}</span></div>
                                        <div>Last Follow-Up By: <span class="fw-semibold badge bg-outline-success text-dark">{{ $ticket->followup_type == 'admin' ? $ticket->followup_admin : $ticket->followup_user }}</span></div>
                                        <a href="{{ url('/ticket_details?id=' . md5($ticket->ticket_id)) }}" class="btn bg-info text-white d-grid">
                                            View <i class="ri-arrow-right-line ms-2 d-inline-block align-middle"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if ($tickets->isEmpty())
                            <div class="card-body">
                                <div class="text-center me-4">
                                    <a href="/transactions/deposit#">
                                        <img src="/assets/images/empty.png" class="w-25" alt="img">
                                    </a>
                                </div>
                                <h6 class="text-center text-secondary f-w-400 mb-0 f-16">No Tickets Added!</h6>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
    }).then(() => {
        location.reload();
    });
</script>
@endif

@if (session('error'))
<script>
    Swal.fire({
        icon: 'warning',
        title: 'Something went wrong',
        text: '{{ session('error') }}',
        showConfirmButton: true
    });
</script>
@endif

@endsection
