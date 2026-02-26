<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Enums\BlogPostStatusEnum;

class BlogController extends Controller
{
    public function index()
    {
        $blogPosts = BlogPost::published()
            ->with('author')
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return view('blog.index', compact('blogPosts'));
    }

    public function show($slug)
    {
        $blogPost = BlogPost::where('slug', $slug)
            ->where('status', BlogPostStatusEnum::ACTIVE->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('author')
            ->firstOrFail();

        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $blogPost->id)
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return view('blog.show', compact('blogPost', 'relatedPosts'));
    }
}

