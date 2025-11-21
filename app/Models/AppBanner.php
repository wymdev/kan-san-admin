<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class AppBanner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image_path', 
        'banner_type',
        'action_url',
        'action_type',
        'is_active',
        'start_date',
        'end_date',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // Accessor for full image URL
    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? url('storage/' . $this->image_path)
            : null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->where(function ($q) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('banner_type', $type);
    }
}
