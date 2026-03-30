@extends('layouts.app')

@push('styles')
<style>
    .blog-header {
        background: linear-gradient(135deg, var(--bs-primary) 0%, #0056b3 100%);
        color: white;
        padding: 3rem 0;
        margin-bottom: 2rem;
    }

    .blog-header h1 {
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .blog-header p {
        opacity: 0.9;
        margin-bottom: 0;
    }

    .blog-card {
        transition: transform 0.3s, box-shadow 0.3s;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        height: 100%;
    }

    .blog-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .blog-image {
        height: 250px;
        object-fit: cover;
        border-radius: 0.375rem 0.375rem 0 0;
    }

    .blog-excerpt {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .blog-content {
        line-height: 1.8;
    }

    .blog-content img {
        max-width: 100%;
        height: auto;
        border-radius: 0.375rem;
        margin: 1rem 0;
    }

    .blog-content h1, .blog-content h2, .blog-content h3 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .blog-content p {
        margin-bottom: 1rem;
    }

    .blog-content ul, .blog-content ol {
        margin-bottom: 1rem;
        padding-left: 2rem;
    }


    .related-post-card {
        transition: transform 0.3s;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .related-post-card:hover {
        transform: translateY(-5px);
    }

    .pc-container {
        padding: 1.5rem;
    }
</style>
@endpush

@section('content')
<!-- Blog Header -->
<div class="blog-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1>@yield('header-title', 'Blog')</h1>
                <p>@yield('header-subtitle', 'Latest news and updates')</p>
            </div>
            <div class="col-md-4 text-end">
                @if(request()->routeIs('blog.show'))
                    <a href="{{ route('blog.index') }}" class="btn btn-light">
                        <i class="ti ti-home"></i> All Posts
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container my-5">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @yield('blog-content')
</div>
@endsection
