<div class="container">
    @php
        $mimeTypes = [
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'gif' => 'image/gif',
        ];
    @endphp
    @foreach ($followups as $index => $followup)
        <div class="d-flex {{ $index % 2 == 0 ? 'justify-content-start' : 'justify-content-end' }} mb-4">
            <div class="img_cont_msg">
                <img src="/admin_assets/assets/images/users/{{ $followup->user_type == 'admin' ? 'admin.png' : 'client.png' }}"
                     class="rounded-circle user_img_msg" alt="img" style="width:50px">
            </div>
            <div class="msg_cotainer" style="min-width:200px">
                @if (!empty($followup->remarks))
                    <span class="mb-1">{!! $followup->remarks !!}</span>
                @endif
                <span class="msg_time">
                    {{ date("h:i A, d M Y", strtotime($followup->added_at)) }}
                    @if (!empty($followup->admin_name))
                        <span class="text-primary">({{ $followup->admin_name }})</span>
                    @endif
                </span>
            </div>
            @if (!empty($followup->attachment))
                <div class="d-flex align-items-center ms-3" data-bs-toggle="modal" data-bs-target="#attachmentModal"
                data-bs-file="{{ asset('storage/_ticket_attachments/' . $followup->attachment) }}" data-bs-type="{{ $mimeTypes[pathinfo($followup->attachment, PATHINFO_EXTENSION)] }}">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-paperclip me-2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                </div>
            @endif
        </div>
    @endforeach
</div>
