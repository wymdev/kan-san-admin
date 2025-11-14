<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class DailyQuote extends Model
{
    use SoftDeletes;

    use LogsActivity;

    protected $fillable = [
        'quote',
        'author',
        'category',
        'is_active',
        'scheduled_for',
        'is_sent',
        'sent_at',
        'recipients_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_sent' => 'boolean',
        'scheduled_for' => 'date',
        'sent_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeUnsent($query)
    {
        return $query->where('is_sent', false);
    }

    public function scopeScheduledForToday($query)
    {
        return $query->where('scheduled_for', today());
    }

    public function scopePending($query)
    {
        return $query->active()
                    ->unsent()
                    ->where(function ($q) {
                        $q->whereNull('scheduled_for')
                          ->orWhere('scheduled_for', '<=', today());
                    });
    }
    public function getLogIdentifier(): string
    {
        return $this->quote ?? "#" . $this->id;
    }
}
