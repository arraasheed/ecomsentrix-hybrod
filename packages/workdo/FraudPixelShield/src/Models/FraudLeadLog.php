<?php

namespace Workdo\FraudPixelShield\Models;

use Illuminate\Database\Eloquent\Model;

class FraudLeadLog extends Model
{
    protected $table = 'fraud_lead_logs';

    protected $fillable = [
        'store_id',
        'phone',
        'name',
        'address',
        'cart_total',
        'success_rate',
        'courier_details',
        'ip_address',
        'status',
    ];

    protected $casts = [
        'cart_total' => 'decimal:2',
        'success_rate' => 'integer',
    ];
}
