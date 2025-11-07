<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'body',
        'type',
        'data',
        'is_published',
        'is_sent',
        'scheduled_at',
        'sent_at',
        'recipients_count',
        'success_count',
        'failed_count',
        'created_by',
    ];

    protected $casts = [
        'data' => 'json',
        'is_published' => 'boolean',
        'is_sent' => 'boolean',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUnsent($query)
    {
        return $query->where('is_sent', false);
    }

    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
                    ->where('scheduled_at', '<=', now());
    }

    public function scopePending($query)
    {
        return $query->published()
                    ->unsent()
                    ->where(function ($q) {
                        $q->whereNull('scheduled_at')
                          ->orWhere('scheduled_at', '<=', now());
                    });
    }
}
