<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentralPixelBlock extends Model
{
    protected $fillable = [
        'phone', 
        'email', 
        'ip', 
        'store_id', 
        'event_type', 
        'reason', 
        'source', 
        'delivery_rate', 
        'pixel_data', 
        'context', 
        'blocked_at'
    ];

    protected $casts = [
        'pixel_data' => 'array',
        'context' => 'array',
        'blocked_at' => 'datetime',
    ];
}
