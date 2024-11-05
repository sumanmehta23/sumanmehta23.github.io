@extends('layouts.admin.admin')
@section('content')
    <div class="modal fade" id="attachmentModal" tabindex="-1" aria-labelledby="attachmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attachmentModalLabel">Attachment Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <embed id="attachmentFile" src="" type="" width="100%">
                </div>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="ticketReassignModal">
        <form method="post" action="{{route('admin.assignTicket')}}">
            @csrf
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reassign #TICKET-{{ sprintf('%02d', $ticket->ticket_id + 10000) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12">
                            <input type="hidden" name="assign_update" value="true">
                            <input type="hidden" name="ticket_id" value="{{ request()->query('id') }}">
                            <select class="form-control" name="assignee" required>
                                @foreach ($rm_details as $rm)
                                    <option value="{{ $rm->client_index }}"
                                        {{ isset($assignee_details->assignee) && $rm->client_index == $assign_details->assignee ? 'selected' : '' }}
>{{ $rm->username }} ({{ $rm->role_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <input type="submit" class="btn btn-primary" value="Reassign">
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="modal" tabindex="-1" id="ticketStatusUpdateModal">
        <form method="post" action="{{route('admin.updateStatus')}}">
            @csrf
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12">
                            <input type="hidden" name="status_update" value="true">
                            <input type="hidden" name="ticket_id" value="{{ request()->query('id') }}">
                            <select class="form-control" name="ticket_status_id" required>
                                @foreach ($ticket_status as $status)
                                    <option value="{{ $status['id'] }}"
                                        {{ $status['ticket_status'] == $ticket->ticket_status ? 'selected' : '' }}>
                                        {{ $status['ticket_status'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <input type="submit" class="btn btn-primary" value="Update">
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Ticket Details</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Ticket Details</li>
                </ol>
            </div>

            <div class="row chat p-3">
                <div class="card" id="chat-card">
                    <div class="action-header clearfix">
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
                                                        <span><i
                                                                class="ri-calendar-2-line text-secondary me-1"></i>{{ $ticket->created_at }}</span>
                                                        <span class="ms-3"><i
                                                                class="ri-user-line text-secondary me-1"></i>{{ $ticket->created_user }}</span>
                                                        <span class="ms-3"><i
                                                                class="ri-user-2-fill text-secondary me-1"></i>{{ $assign_details->username??'' }}</span>
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
                                                            <img src="/admin_assets/assets/images/users/client.png"
                                                                alt="img">
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-8">
                                            <!-- Additional Content can be added here -->
                                        </div>
                                        <div class="col-4 text-end">
                                            <a href="#" class="btn btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#ticketReassignModal">Reassign</a>
                                            <a href="#" class="btn btn-success" data-bs-toggle="modal"
                                                data-bs-target="#ticketStatusUpdateModal">Update Status</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer justify-content-between d-flex">
                                    <div>Ticket Type: <span
                                            class="fw-semibold badge bg-outline-primary">{{ $ticket->ticket_type }}</span>
                                    </div>
                                    <div>Last Follow-Up Date: <span
                                            class="fw-semibold badge bg-outline-info">{{ $ticket->last_followup }}</span>
                                    </div>
                                    <div>Last Follow-Up By: <span
                                            class="fw-semibold badge bg-outline-success">{{ $ticket->followup_type == 'admin' ? $ticket->followup_admin : $ticket->followup_user }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="followup_logs" class="card-body msg_card_body" style="height: 500px;">
                        <!-- Follow-up logs will be dynamically loaded here -->
                    </div>

                    <div class="card-footer">
                        <div id="file-display" style="margin-top: 10px;"></div>
                        <form class="msb-reply d-flex bg-transparent" method="POST" enctype="multipart/form-data"
                            action="">
                            @csrf
                            <label class="input-group-text attach_btn" for="file-upload">
                                <i class="fe fe-paperclip me-2"></i>
                            </label>
                            <input type="file" accept=".jpeg, .png, .jpg, .pdf" id="file-upload" name="attachment"
                                class="d-none" onchange="showSelectedFile()">
                            <textarea placeholder="Add your message here" class="border-0 form-control" name="remark"
                                style="border-radius: 5px 0 0 5px" required></textarea>
                            <input type="submit" class="btn btn-primary" style="width:200px; border-radius: 0 5px 5px 0"
                                value="Send Message">
                            <input type="hidden" name="add_remark" value="true">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            var cardBody = $('.msg_card_body');
            cardBody.scrollTop(cardBody[0].scrollHeight);
            loadFollowups();
            setInterval(loadFollowups, 3000);
        });

        function showSelectedFile() {
            const fileInput = document.getElementById('file-upload');
            const fileDisplay = document.getElementById('file-display');

            if (fileInput.files.length > 0) {
                fileDisplay.innerHTML = '<strong>Selected file:</strong> ' + fileInput.files[0].name;
            } else {
                fileDisplay.innerHTML = '';
            }
        }
        function loadFollowups() {
            $("#followup_logs").load("/admin/ticket_followups?id=<?php echo $_GET['id']; ?>");
        }
        $('#attachmentModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var fileSrc = button.data('bs-file');
            var fileType = button.data('bs-type');
            var modal = $(this);
            modal.find('#attachmentFile').attr('src', fileSrc);
            modal.find('#attachmentFile').attr('type', fileType);
        });
    </script>
@endsection
