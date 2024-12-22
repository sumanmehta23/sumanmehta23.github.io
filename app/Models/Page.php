<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model
{
    use HasFactory,HasUuids;
    

    protected $fillable = ['title', 'slug', 'content', 'page_category_id'];
    //belongs to page category
    public function pageCategory()
    {
        return $this->belongsTo(PageCategory::class);
    }

    public function submenus()
    {
        return $this->hasMany(Page::class, 'is_submenu', 'page_id');
    }
}
