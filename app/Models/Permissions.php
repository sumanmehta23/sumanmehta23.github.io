<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Page;

class Permissions extends Model
{
    use HasFactory;
    protected $table="permissions";
    public function page(){
        return $this->belongsTo(Page::class, 'page_id', 'page_id');

    }
}

