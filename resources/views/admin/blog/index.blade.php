@extends('layouts.admin.admin')
@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- PAGE-HEADER -->
        <div class="page-header">
            <h1 class="page-title">Blog Posts</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Blog Posts</li>
            </ol>
        </div>
        <!-- PAGE-HEADER END -->

        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">All Blog Posts</h5>
                        <a href="{{ route('admin.blog.create') }}" class="btn btn-primary">
                            <i class="fe fe-plus"></i> Create New Post
                        </a>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap border-bottom">
                                <thead class="border-top">
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Published At</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($blogPosts as $post)
                                        <tr>
                                            <td>{{ Str::limit($post->title, 50) }}</td>
                                            <td>{{ $post->author->username ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    if ($post->status instanceof \App\Enums\BlogPostStatusEnum) {
                                                        $statusValue = $post->status->value;
                                                    } else {
                                                        $statusValue = $post->status ?? 'draft';
                                                    }
                                                    $statusLabel = \App\Enums\BlogPostStatusEnum::getLabel($statusValue);
                                                    $statusColor = \App\Enums\BlogPostStatusEnum::getColorClass($statusValue);
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td>{{ $post->published_at ? $post->published_at->format('Y-m-d H:i') : 'Not Published' }}</td>
                                            <td>{{ $post->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.blog.show', $post->id) }}" class="btn btn-sm btn-info" title="View">
                                                        <i class="fe fe-eye"></i> View
                                                    </a>
                                                    <a href="{{ route('admin.blog.edit', $post->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fe fe-edit"></i> Edit
                                                    </a>
                                                    <form action="{{ route('admin.blog.destroy', $post->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?')" title="Delete">
                                                            <i class="fe fe-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No blog posts found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $blogPosts->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

