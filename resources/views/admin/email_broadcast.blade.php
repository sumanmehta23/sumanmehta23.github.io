@extends('layouts.admin.admin')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="page-header">
            <h1 class="page-title">Send Emails to Clients</h1>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <form action="{{ route('admin.send_emailbroadcast') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="emails" class="form-label">Client Emails (Comma Separated)</label>
                                <textarea name="emails" id="emails" class="form-control" rows="3" placeholder="Enter multiple emails separated by commas"></textarea>
                                {{-- <button type="button" class="btn btn-primary mt-2" id="selectAllEmails">Select All Emails</button> --}}
                                <button type="button" class="btn btn-success mt-2" id="sendToAllClients">Send to All Clients</button>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Email Subject</label>
                                <input type="text" name="subject" id="subject" class="form-control" placeholder="Enter email subject">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Message</label>
                                <textarea name="message" id="message" class="form-control"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Emails</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Summernote -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.js"></script>

<script>
    $(document).ready(function() {
        $('#message').summernote({
            height: 200,  // Set editor height
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });

        // Auto-fill textarea with all emails when button is clicked
        $('#selectAllEmails').click(function() {
            let emails = @json($emails); // Fetch emails passed from controller
            $('#emails').val(emails.join(', ')); // Join emails with a comma
        });
        $('#sendToAllClients').click(function() {
            $('#emails').val('all'); // Special keyword
        });

    });
</script>

@endsection
