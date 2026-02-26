<?php

namespace App\Models;

use App\Enums\BlogPostStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'author_id',
    ];

    protected $casts = [
        'status' => BlogPostStatusEnum::class,
        'published_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(EmployeeList::class, 'author_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', BlogPostStatusEnum::ACTIVE->value);
    }

    public function scopePublished($query)
    {
        return $query->where('status', BlogPostStatusEnum::ACTIVE->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}

