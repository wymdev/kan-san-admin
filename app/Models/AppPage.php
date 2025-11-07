<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppPage extends Model
{
    protected $fillable = [
        'page_key',
        'page_name',
        'content',
        'page_type',
        'public_slug',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('page_type', $type);
    }
}
