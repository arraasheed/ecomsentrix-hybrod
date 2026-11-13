<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'checked_at',
    ];

    protected $casts = [
        'raw' => 'array',
        'checked_at' => 'datetime',
        'total_parcels' => 'integer',
        'delivered' => 'integer',
        'returned' => 'integer',
        'delivery_rate' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
