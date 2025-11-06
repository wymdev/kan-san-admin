<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrawInfo extends Model
{
    protected $fillable = [
        'draw_date', 'result_announce_date', 'is_estimated', 'note', 'period',
    ];
    protected $casts = [
        'draw_date' => 'datetime',
        'result_announce_date' => 'datetime',
        'is_estimated' => 'boolean',
    ];
}