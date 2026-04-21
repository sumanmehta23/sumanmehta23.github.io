<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopupImpression extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'shown_at' => 'datetime',
        'dismissed_at' => 'datetime',
        'cta_clicked_at' => 'datetime',
    ];
}
