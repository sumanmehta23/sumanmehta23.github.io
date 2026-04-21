@extends('layouts.admin.admin')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Learn Content</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Learn Content</li>
                </ol>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-6">
                    <div class="card custom-card h-100">
                        <div class="card-header">
                            <h6 class="mb-0 card-title">Create Section</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.learn-content.sections.store') }}" class="row g-2">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-select" required>
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-sm">Create Section</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card custom-card h-100">
                        <div class="card-header">
                            <h6 class="mb-0 card-title">Create Video</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.learn-content.videos.store') }}" class="row g-2">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label">Section</label>
                                    <select name="learn_section_id" class="form-select" required>
                                        <option value="">Select section</option>
                                        @foreach ($allSections as $section)
                                            <option value="{{ $section->id }}">{{ $section->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Wistia ID</label>
                                    <input type="text" name="wistia_id" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tags (comma separated)</label>
                                    <input type="text" name="tags" class="form-control" placeholder="beginner, setup">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Order</label>
                                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select name="is_active" class="form-select" required>
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-sm">Create Video</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="mb-0 card-title">Sections</h6>
                            <form method="GET" action="{{ route('admin.learn-content.index') }}" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="videos_per_page" value="{{ $videosPerPage }}">
                                <input type="hidden" name="video_section_id" value="{{ $filterSectionId }}">
                                <label class="small mb-0">Per page</label>
                                <select name="sections_per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="10" {{ $sectionsPerPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $sectionsPerPage == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $sectionsPerPage == 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Slug</th>
                                            <th>Order</th>
                                            <th>Status</th>
                                            <th>Videos Count</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($sections as $section)
                                            <tr>
                                                <td>{{ $section->title }}</td>
                                                <td>{{ $section->slug }}</td>
                                                <td>{{ $section->sort_order }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $section->is_active ? 'success' : 'secondary' }}">
                                                        {{ $section->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>{{ $section->videos_count }}</td>
                                                <td class="text-center">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-info edit-section-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editSectionModal"
                                                        data-id="{{ $section->id }}"
                                                        data-title="{{ $section->title }}"
                                                        data-slug="{{ $section->slug }}"
                                                        data-sort-order="{{ $section->sort_order }}"
                                                        data-is-active="{{ $section->is_active ? 1 : 0 }}"
                                                    >
                                                        <i class="fe fe-edit-2"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('admin.learn-content.sections.destroy', $section) }}" class="d-inline" onsubmit="return confirm('Delete this section and all its videos?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fe fe-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No sections found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <small class="text-muted">
                                    @if ($sections->count())
                                        Showing {{ $sections->firstItem() }}-{{ $sections->lastItem() }} of {{ $sections->total() }} sections
                                    @endif
                                </small>
                                {{ $sections->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="mb-0 card-title">Videos</h6>
                            <form method="GET" action="{{ route('admin.learn-content.index') }}" class="d-flex align-items-center flex-wrap gap-2">
                                <input type="hidden" name="sections_per_page" value="{{ $sectionsPerPage }}">
                                <label class="small mb-0">Section</label>
                                <select name="video_section_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Sections</option>
                                    @foreach ($allSections as $section)
                                        <option value="{{ $section->id }}" {{ (string) $filterSectionId === (string) $section->id ? 'selected' : '' }}>
                                            {{ $section->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <label class="small mb-0">Per page</label>
                                <select name="videos_per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="10" {{ $videosPerPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $videosPerPage == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $videosPerPage == 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Section</th>
                                            <th>Title</th>
                                            <th>Wistia ID</th>
                                            <th>Tags</th>
                                            <th>Order</th>
                                            <th>Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($videos as $video)
                                            <tr>
                                                <td>{{ optional($video->section)->title }}</td>
                                                <td>{{ $video->title }}</td>
                                                <td>{{ $video->wistia_id }}</td>
                                                <td>
                                                    @if (is_array($video->tags) && count($video->tags))
                                                        {{ implode(', ', $video->tags) }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $video->sort_order }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $video->is_active ? 'success' : 'secondary' }}">
                                                        {{ $video->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-info edit-video-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editVideoModal"
                                                        data-id="{{ $video->id }}"
                                                        data-learn-section-id="{{ $video->learn_section_id }}"
                                                        data-title="{{ $video->title }}"
                                                        data-wistia-id="{{ $video->wistia_id }}"
                                                        data-tags="{{ is_array($video->tags) ? implode(', ', $video->tags) : '' }}"
                                                        data-sort-order="{{ $video->sort_order }}"
                                                        data-is-active="{{ $video->is_active ? 1 : 0 }}"
                                                    >
                                                        <i class="fe fe-edit-2"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('admin.learn-content.videos.destroy', $video) }}" class="d-inline" onsubmit="return confirm('Delete this video?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fe fe-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No videos found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <small class="text-muted">
                                    @if ($videos->count())
                                        Showing {{ $videos->firstItem() }}-{{ $videos->lastItem() }} of {{ $videos->total() }} videos
                                    @endif
                                </small>
                                {{ $videos->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editSectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Edit Section</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editSectionForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="editSectionTitle" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="editSectionSlug" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="editSectionSortOrder" class="form-control" min="0">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Status</label>
                            <select name="is_active" id="editSectionStatus" class="form-select" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editVideoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Edit Video</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editVideoForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Section</label>
                            <select name="learn_section_id" id="editVideoSectionId" class="form-select" required>
                                @foreach ($allSections as $section)
                                    <option value="{{ $section->id }}">{{ $section->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="editVideoTitle" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Wistia ID</label>
                            <input type="text" name="wistia_id" id="editVideoWistiaId" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Tags (comma separated)</label>
                            <input type="text" name="tags" id="editVideoTags" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="editVideoSortOrder" class="form-control" min="0">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Status</label>
                            <select name="is_active" id="editVideoStatus" class="form-select" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.edit-section-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    var id = this.getAttribute('data-id');
                    document.getElementById('editSectionForm').action = '/admin/learn-content/sections/' + id;
                    document.getElementById('editSectionTitle').value = this.getAttribute('data-title') || '';
                    document.getElementById('editSectionSlug').value = this.getAttribute('data-slug') || '';
                    document.getElementById('editSectionSortOrder').value = this.getAttribute('data-sort-order') || 0;
                    document.getElementById('editSectionStatus').value = this.getAttribute('data-is-active') || 0;
                });
            });

            document.querySelectorAll('.edit-video-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    var id = this.getAttribute('data-id');
                    document.getElementById('editVideoForm').action = '/admin/learn-content/videos/' + id;
                    document.getElementById('editVideoSectionId').value = this.getAttribute('data-learn-section-id') || '';
                    document.getElementById('editVideoTitle').value = this.getAttribute('data-title') || '';
                    document.getElementById('editVideoWistiaId').value = this.getAttribute('data-wistia-id') || '';
                    document.getElementById('editVideoTags').value = this.getAttribute('data-tags') || '';
                    document.getElementById('editVideoSortOrder').value = this.getAttribute('data-sort-order') || 0;
                    document.getElementById('editVideoStatus').value = this.getAttribute('data-is-active') || 0;
                });
            });
        </script>
    @endpush
@endsection

