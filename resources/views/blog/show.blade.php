@extends('layouts.blog')

@section('header-title', $blogPost->title)
@section('header-subtitle', $blogPost->excerpt ?? 'Blog Post')

@section('blog-content')
<div class="row">
    <div class="col-lg-8">
        <article class="bg-white rounded shadow-sm p-4 mb-4">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($blogPost->title, 30) }}</li>
                </ol>
            </nav>

            <!-- Post Header -->
            <div class="mb-4">
                <h1 class="display-5 fw-bold mb-3">{{ $blogPost->title }}</h1>
                <div class="d-flex align-items-center text-muted mb-3">
                    @if($blogPost->author)
                        <span class="me-3">
                            <i class="ti ti-user"></i> {{ $blogPost->author->username }}
                        </span>
                    @endif
                    @if($blogPost->published_at)
                        <span>
                            <i class="ti ti-calendar"></i> {{ $blogPost->published_at->format('F d, Y') }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Featured Image -->
            @if($blogPost->featured_image)
                <div class="mb-4">
                    <img src="{{ $blogPost->featured_image }}" class="img-fluid rounded" alt="{{ $blogPost->title }}">
                </div>
            @endif

            <!-- Excerpt -->
            @if($blogPost->excerpt)
                <div class="lead mb-4 p-3 bg-light rounded">
                    {{ $blogPost->excerpt }}
                </div>
            @endif

            <!-- Content -->
            <div class="blog-content">
                {!! $blogPost->content !!}
            </div>
        </article>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Related Posts -->
        @if($relatedPosts->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="ti ti-news"></i> Related Posts</h5>
                </div>
                <div class="card-body">
                    @foreach($relatedPosts as $relatedPost)
                        <div class="card related-post-card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <a href="{{ route('blog.show', $relatedPost->slug) }}" class="text-decoration-none">
                                        {{ $relatedPost->title }}
                                    </a>
                                </h6>
                                @if($relatedPost->published_at)
                                    <small class="text-muted">
                                        <i class="ti ti-calendar"></i> {{ $relatedPost->published_at->format('M d, Y') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Post Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="ti ti-info-circle"></i> Post Information</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong><i class="ti ti-user"></i> Author:</strong><br>
                    {{ $blogPost->author->username ?? 'N/A' }}
                </p>
                @if($blogPost->published_at)
                    <p class="mb-2">
                        <strong><i class="ti ti-calendar"></i> Published:</strong><br>
                        {{ $blogPost->published_at->format('F d, Y') }}
                    </p>
                @endif
                <p class="mb-0">
                    <strong><i class="ti ti-link"></i> Slug:</strong><br>
                    <code>{{ $blogPost->slug }}</code>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

