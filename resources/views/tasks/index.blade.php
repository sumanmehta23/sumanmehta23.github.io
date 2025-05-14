@extends('layouts.crm.crm')
@section('content')
<style>
    #tasks_table .td-wrap {
        max-width: 75px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .task-completed td {
        --bs-text-opacity: 1;
        color: rgba(var(--bs-success-rgb), var(--bs-text-opacity)) !important;
    }

    .task-pending td {
        --bs-text-opacity: 1;
        color: rgba(var(--bs-warning-rgb), var(--bs-text-opacity)) !important;
    }

    .task-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f3f5f7;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        color: #3c3c3c;
        flex-wrap: wrap;
    }

    .task-header {
        display: flex;
        align-items: center;

    }

    .task-header img {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        margin-right: 16px;
    }

    .task-header div span {
        font-size: 16px;
        font-weight: bold;
        display: block;
    }

    .task-header div div {
        font-size: 14px;
        color: #a1a1a1;
    }

    .points {
        background-color: #e3e3e3;
        color: #000;
        padding: 8px;
        border-radius: 10px;
        font-weight: bold;
        display: flex;
        align-items: center;
        margin-bottom: 2px;
        margin-right: 10px;
        justify-content: flex-end;
    }

    .points i {
        margin-right: 8px;
    }

    .upload-btn {
        background-color: #003e40;
        color: #ffffff;
        border: none;
        padding: 8px 12px;
        border-radius: 10px;
        font-weight: bold;
        display: flex;
        align-items: center;
        margin-right: 10px;
        cursor: pointer;
    }

    .upload-btn i {
        margin-right: 8px;
    }

    .complete-btn {
        background-color: #6c757d;
        color: #fff;
        border: none;
        padding: 8px 12px;
        border-radius: 10px;
        font-weight: bold;
        display: flex;
        align-items: center;
        cursor: not-allowed;
        margin-top: -20px;
        margin-right: 10px;
    }

    .complete-btn i {
        margin-right: 8px;
    }

    small {
        display: block;
        font-size: 12px;
        color: #a1a1a1;
        margin-top: 4px;
    }
</style>

<div class="pc-container">
    <div class="pc-content">
        <div class="pb-0 mb-0 page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title h2">
                            <h4 class="mb-0">Tasks</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
              <div class="card">
                <div class="pb-0 card-body border-bottom">
                  <div class="pt-2">
                    <h5 class="mb-0">All Tasks</h5>
                        <div class="p-4">
                            {{-- @foreach ($tasks as $task)
                                <div class="task-card">
                                    <div class="task-header">
                                        <img src="/admin_assets/assets/images/users/user.png" alt="task" class="avatar-rounded">
                                        <div>
                                            <span>{{ $task->name }}</span>
                                            <div>Available until: {{ $task->expiration_date }}</div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end ms-auto">
                                        <div class="pt-2">
                                            <span class="points">
                                                <i class="ti ti-database-import f-18"></i>
                                                {{ $task->points }} Points
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="pt-2">
                                                <button class="upload-btn">
                                                    Upload Screenshot
                                                </button>
                                                <input type="file" id="profile_picture_input" style="display: none;"
                                            accept="image/*">
                                                <small>Max file size: 2MB</small>
                                            </div>
                                            <div class="pt-2">
                                                <button class="complete-btn" disabled>
                                                    Complete Task
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach --}}
                            @foreach ($tasks as $task)
                                <div class="task-card" data-task-id="{{ $task->id }}">
                                    <div class="task-header">
                                        <img src="/admin_assets/assets/images/users/user.png" alt="task" class="avatar-rounded">
                                        <div>
                                            <span>{{ $task->name }}</span>
                                            <div>Available until: {{ $task->expiration_date }}</div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end ms-auto">
                                        <div class="pt-2">
                                            <span class="points">
                                                <i class="ti ti-database-import f-18"></i>
                                                {{ $task->points }} Points
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="pt-2">
                                                <button class="upload-btn" data-task-id="{{ $task->id }}">
                                                    Upload Screenshot
                                                </button>
                                                <input type="file"
                                                    id="screenshot_input_{{ $task->id }}"
                                                    class="screenshot-input"
                                                    data-task-id="{{ $task->id }}"
                                                    style="display: none;"
                                                    accept="image/*">
                                                <small>Max file size: 2MB</small>
                                            </div>
                                            <div class="pt-2">
                                                <button class="complete-btn" disabled>
                                                    Complete Task
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        // When the upload button is clicked
        $('.upload-btn').on('click', function() {
            const taskId = $(this).data('task-id');
            $('#screenshot_input_' + taskId).click();
        });

        // When a file is selected
        $('.screenshot-input').on('change', function() {
            const taskId = $(this).data('task-id');
            const fileInput = this;
            const formData = new FormData();
            formData.append('screenshot', fileInput.files[0]);
            formData.append('task_id', taskId);
            console.log();
            console.log();
            console.log();
            console.log();
            $.ajax({
                url: "{{ route('task.screenshot.upload') }}", // Replace with your correct route
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Screenshot uploaded successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload failed!',
                        text: xhr.responseJSON?.message || 'Something went wrong.'
                    });
                }
            });
        });
    });
</script>
@endsection
