@extends('layouts.admin.admin')
@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- PAGE-HEADER -->
        <div class="page-header">
            <h1 class="page-title">View Blog Post</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.blog.index') }}">Blog Posts</a></li>
                <li class="breadcrumb-item active" aria-current="page">View</li>
            </ol>
        </div>
        <!-- PAGE-HEADER END -->

        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Blog Post Details</h5>
                        <div>
                            <a href="{{ route('admin.blog.edit', $blog->id) }}" class="btn btn-primary">
                                <i class="fe fe-edit"></i> Edit
                            </a>
                            <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary">
                                <i class="fe fe-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h2>{{ $blog->title }}</h2>
                                <p class="text-muted">
                                    By {{ $blog->author->username ?? 'N/A' }} | 
                                    Created: {{ $blog->created_at->format('Y-m-d H:i') }} |
                                    @php
                                        if ($blog->status instanceof \App\Enums\BlogPostStatusEnum) {
                                            $statusValue = $blog->status->value;
                                        } else {
                                            $statusValue = $blog->status ?? 'draft';
                                        }
                                        $statusLabel = \App\Enums\BlogPostStatusEnum::getLabel($statusValue);
                                        $statusColor = \App\Enums\BlogPostStatusEnum::getColorClass($statusValue);
                                    @endphp
                                    Status: <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                                </p>
                            </div>
                        </div>

                        @if($blog->featured_image)
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <img src="{{ $blog->featured_image }}" alt="{{ $blog->title }}" class="img-fluid" style="max-height: 400px;">
                                </div>
                            </div>
                        @endif

                        @if($blog->excerpt)
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <p class="lead">{{ $blog->excerpt }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="content">
                                    {!! $blog->content !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h6>SEO Information</h6>
                                <p><strong>Meta Title:</strong> {{ $blog->meta_title ?? 'N/A' }}</p>
                                <p><strong>Meta Description:</strong> {{ $blog->meta_description ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Publishing Information</h6>
                                <p><strong>Published At:</strong> {{ $blog->published_at ? $blog->published_at->format('Y-m-d H:i') : 'Not Published' }}</p>
                                <p><strong>Slug:</strong> {{ $blog->slug }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

