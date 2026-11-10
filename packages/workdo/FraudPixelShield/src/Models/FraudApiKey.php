<?php

namespace Workdo\FraudPixelShield\Models;

use Illuminate\Database\Eloquent\Model;

class FraudApiKey extends Model
{
    protected $table = 'fraud_api_keys';

    protected $fillable = [
        'user_id',
        'store_id',
        'courier_name',
        'key_value',
        'daily_limit',
        'usage_count',
        'status',
        'last_reset_time',
    ];

    protected $casts = [
        'daily_limit' => 'integer',
        'usage_count' => 'integer',
        'last_reset_time' => 'datetime',
    ];

    /**
     * Get an active key with available quota and increment its usage.
     */
    public static function acquireKey($courierName = 'BD Courier', $storeId = null)
    {
        $today = now()->format('Y-m-d');

        // Reset counters if new day
        self::where('status', 'active')
            ->whereDate('last_reset_time', '<', $today)
            ->update([
                'usage_count' => 0,
                'last_reset_time' => now(),
            ]);

        $query = self::where('status', 'active')
            ->whereRaw('usage_count < daily_limit');

        if ($courierName) {
            $query->where('courier_name', $courierName);
        }

        $key = $query->inRandomOrder()->first();

        if ($key) {
            $key->increment('usage_count');
            return $key->key_value;
        }

        return null;
    }
}
