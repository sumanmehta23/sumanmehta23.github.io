@extends('layouts.blog')

@section('header-title', 'Blog')
@section('header-subtitle', 'Latest news and updates')

@section('blog-content')
<div class="row">
    @forelse ($blogPosts as $post)
        <div class="col-md-4 mb-4">
            <div class="card blog-card h-100">
                @if($post->featured_image)
                    <img src="{{ $post->featured_image }}" class="card-img-top blog-image" alt="{{ $post->title }}">
                @else
                    <div class="blog-image bg-light d-flex align-items-center justify-content-center">
                        <i class="ti ti-photo" style="font-size: 3rem;"></i>
                    </div>
                @endif
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                        <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none text-dark">
                            {{ $post->title }}
                        </a>
                    </h5>
                    @if($post->excerpt)
                        <p class="card-text blog-excerpt flex-grow-1">{{ $post->excerpt }}</p>
                    @endif
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted">
                                @if($post->author)
                                    <i class="ti ti-user"></i> {{ $post->author->username }}
                                @endif
                                @if($post->published_at)
                                    | <i class="ti ti-calendar"></i> {{ $post->published_at->format('M d, Y') }}
                                @endif
                            </small>
                        </div>
                        <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-primary w-100">
                            Read More <i class="ti ti-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                <h4><i class="ti ti-info-circle"></i> No blog posts available</h4>
                <p class="mb-0">Check back soon for new content!</p>
            </div>
        </div>
    @endforelse
</div>

<div class="row mt-4">
    <div class="col-12">
        {{ $blogPosts->links() }}
    </div>
</div>
@endsection
