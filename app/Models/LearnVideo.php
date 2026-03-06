<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearnVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'learn_section_id',
        'title',
        'wistia_id',
        'tags',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(LearnSection::class, 'learn_section_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

