<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PageCategory extends Model
{
    use HasUuids;

    public function pages()
    {
        return $this->hasMany(Page::class, 'page_category_id');
    }
}
