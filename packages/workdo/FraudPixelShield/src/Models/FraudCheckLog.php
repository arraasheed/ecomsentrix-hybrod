<?php

namespace Workdo\FraudPixelShield\Models;

use Illuminate\Database\Eloquent\Model;

class FraudCheckLog extends Model
{
    protected $table = 'fraud_check_logs';

    protected $fillable = [
        'store_id',
        'phone',
        'courier_details',
        'total_orders',
        'total_delivery',
        'total_cancel',
        'success_rate',
        'risk_status',
        'is_blocked',
        'check_type',
        'ip_address',
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
        'total_orders' => 'integer',
        'total_delivery' => 'integer',
        'total_cancel' => 'integer',
        'success_rate' => 'integer',
        'courier_details' => 'array',
    ];
}
