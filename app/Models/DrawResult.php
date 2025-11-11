<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrawResult extends Model
{
    protected $fillable = [
        'draw_date', 'date_th', 'date_en', 'prizes', 'running_numbers', 'endpoint'
    ];
    protected $casts = [
        'prizes' => 'array',
        'running_numbers' => 'array',
    ];
}
