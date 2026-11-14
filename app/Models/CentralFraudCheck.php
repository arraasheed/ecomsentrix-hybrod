<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentralFraudCheck extends Model
{
    protected $fillable = [
        'phone', 
        'total_parcels', 
        'delivered', 
        'returned', 
        'delivery_rate', 
        'risk_level', 
        'source', 
        'store_id', 
        'raw', 
        'checked_at'
    ];

    protected $casts = [
        'raw' => 'array',
        'checked_at' => 'datetime',
    ];
}
