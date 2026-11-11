<?php

namespace Workdo\FraudPixelShield\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CapiDispatchLog extends Model
{
    use HasFactory;

    protected $table = 'capi_dispatch_logs';

    protected $fillable = [
        'order_id',
        'store_id',
        'event_name',
        'status',
        'response_payload',
    ];

    protected $casts = [
        'response_payload' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class, 'order_id');
    }

    public function store()
    {
        return $this->belongsTo(\App\Models\Store::class, 'store_id');
    }
}