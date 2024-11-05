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

        #wallet_transactions .td-wrap {
            max-width: 75px;
            overflow: hidden;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wallet-plus td {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-success-rgb), var(--bs-text-opacity)) !important;
        }

        .wallet-minus td {
            --bs-text-opacity: 1;
            color: rgba(var(--bs-danger-rgb), var(--bs-text-opacity)) !important;
        }

        .chat .msg_cotainer {
            margin-block-start: auto;
            margin-block-end: 15px;
            margin-inline-start: 10px;
            background-color: #edeef3;
            padding: 10px;
            position: relative;
            border-radius: 20px;
        }

        .chat .msg_time {
            position: absolute;
            inset-inline-start: 0;
            inset-block-end: -18px;
            color: var(--text-muted);
            font-size: 10px;
        }
    </style>
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
    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header mb-0 pb-0">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <div class="page-header-title h2">
                                <h4 class="mb-0">Ticket Details</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row chat p-3">
                        <div class="card" id="chat-card">
                            <div class="action-header clearfix">
                                <div class="col-12 mt-3">
                                    <div class="card custom-card shadow-none mb-0 ribbon-card">
                                        <div class="card-body p-4">
                                            <div class="ribbon ribbon-{{ $ticket->ticket_label }} ribbon-top-left">
                                                <span
                                                    class="bg-{{ $ticket->ticket_label }}">{{ $ticket->ticket_status }}</span>
                                            </div>
                                            <div class="card-subtitle fw-semibold mb-2">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="ms-5">
                                                            <h3>#TICKET-{{ sprintf('%02d', $ticket->ticket_id + 10000) }}
                                                            </h3>
                                                            <div class="text-muted">
                                                                <span><i
                                                                        data-feather="calendar"></i>{{ $ticket->created_at }}</span>
                                                                <span class="ms-3"><i
                                                                        data-feather="user"></i>{{ $ticket->created_user }}</span>
                                                                <span class="ms-3"><i
                                                                        data-feather="user"></i>{{ $assign_details->username ??''}}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-8">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer justify-content-between d-flex">
                                            <div>Ticket Type: <span
                                                    class="fw-semibold badge bg-outline-primary text-dark">{{ $ticket->ticket_type }}</span>
                                            </div>
                                            <div>Last Follow-Up Date: <span
                                                    class="fw-semibold badge bg-outline-info text-dark">{{ $ticket->last_followup }}</span>
                                            </div>
                                            <div>Last Follow-Up By: <span
                                                    class="fw-semibold badge bg-outline-success text-dark">{{ $ticket->followup_type == 'admin' ? $ticket->followup_admin : $ticket->followup_user }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="followup_logs" class="card-body msg_card_body"
                                style="height: 500px; overflow-y: auto;">
                            </div>
                            <div class="card-footer">
                                <div id="file-display" style="margin-top: 10px;"></div>
                                {{-- action="{{ route('tickets.addRemark', $ticket->id) }}" --}}
                                <form class="msb-reply d-flex bg-transparent" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <label class="input-group-text attach_btn" for="file-upload"
                                        style="border-radius: 5px 0 0 5px">
                                        <i data-feather="paperclip" class="me-2"></i>
                                    </label>
                                    <input type="file" accept=".jpeg, .png, .jpg, .pdf" id="file-upload"
                                        name="attachment" class="d-none" onchange="showSelectedFile()">
                                    <textarea placeholder="Add your message here" class="border-0 form-control" name="remark"
                                        style="border-radius: 0; border: 1px solid #bec8d0 !important;" required></textarea>
                                    <input type="submit" class="btn btn-primary"
                                        style="width:200px; border-radius: 0 5px 5px 0" value="Send Message">
                                    <input type="hidden" name="add_remark" value="true">
                                </form>
                            </div>
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
                showConfirmButton: true
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
        $('#attachmentModal').on('show.bs.modal', function(event) {
          var button = $(event.relatedTarget);
          var fileSrc = button.data('bs-file');
          var fileType = button.data('bs-type');
          var modal = $(this);
          modal.find('#attachmentFile').attr('src', fileSrc);
          modal.find('#attachmentFile').attr('type', fileType);
        });
        function loadFollowups() {
            $("#followup_logs").load("/ticket_followups?id=<?php echo $_GET['id']; ?>");
        }
    </script>
@endsection
