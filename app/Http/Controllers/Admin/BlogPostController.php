<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BlogPostStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogPostRequest;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    public function index()
    {
        $blogPosts = BlogPost::with('author')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.blog.index', compact('blogPosts'));
    }

    public function create()
    {
        $statuses = BlogPostStatusEnum::toArray();
        return view('admin.blog.create', compact('statuses'));
    }

    public function store(StoreBlogPostRequest $request)
    {
        $validatedData = $request->validated();
        
        if (empty($validatedData['slug'])) {
            $validatedData['slug'] = Str::slug($validatedData['title']);
        }

        $validatedData['author_id'] = Auth::guard('admin')->id();
        $validatedData['status'] = BlogPostStatusEnum::from($validatedData['status']);

        BlogPost::create($validatedData);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post created successfully.');
    }

    public function show(BlogPost $blog)
    {
        $blog->load('author');
        return view('admin.blog.show', compact('blog'));
    }

    public function edit(BlogPost $blog)
    {
        $statuses = BlogPostStatusEnum::toArray();
        return view('admin.blog.edit', compact('blog', 'statuses'));
    }

    public function update(StoreBlogPostRequest $request, BlogPost $blog)
    {
        $validatedData = $request->validated();

        if (empty($validatedData['slug'])) {
            $validatedData['slug'] = Str::slug($validatedData['title']);
        }

        $validatedData['status'] = BlogPostStatusEnum::from($validatedData['status']);
        $blog->update($validatedData);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post updated successfully.');
    }

    public function destroy(BlogPost $blog)
    {
        $blog->delete();

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post deleted successfully.');
    }
}

