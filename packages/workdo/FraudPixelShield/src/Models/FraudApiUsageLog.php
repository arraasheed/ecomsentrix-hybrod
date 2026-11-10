<?php

namespace Workdo\FraudPixelShield\Models;

use Illuminate\Database\Eloquent\Model;

class FraudApiUsageLog extends Model
{
    protected $table = 'fraud_api_usage_logs';

    protected $fillable = [
        'store_id',
        'courier_name',
        'api_key_id',
        'date',
        'total_calls',
        'successful_calls',
        'failed_calls',
        'avg_response_time_ms',
        'last_called_at',
    ];

    protected $casts = [
        'date' => 'date',
        'total_calls' => 'integer',
        'successful_calls' => 'integer',
        'failed_calls' => 'integer',
        'avg_response_time_ms' => 'integer',
        'last_called_at' => 'datetime',
    ];

    /**
     * Record an API call result for the given courier and store.
     */
    public static function recordCall($storeId, $courierName, $apiKeyId, $success, $responseTimeMs = null)
    {
        $today = now()->format('Y-m-d');

        $log = self::firstOrCreate(
            [
                'store_id' => $storeId,
                'courier_name' => $courierName,
                'api_key_id' => $apiKeyId,
                'date' => $today,
            ],
            [
                'total_calls' => 0,
                'successful_calls' => 0,
                'failed_calls' => 0,
                'avg_response_time_ms' => 0,
                'last_called_at' => now(),
            ]
        );

        $log->increment('total_calls');
        
        if ($success) {
            $log->increment('successful_calls');
        } else {
            $log->increment('failed_calls');
        }

        if ($responseTimeMs !== null) {
            // Recalculate running average
            $newAvg = (($log->avg_response_time_ms * ($log->total_calls - 1)) + $responseTimeMs) / $log->total_calls;
            $log->avg_response_time_ms = (int) round($newAvg);
        }

        $log->last_called_at = now();
        $log->save();

        return $log;
    }

    /**
     * Get today's usage stats for a courier.
     */
    public static function getTodayStats($storeId, $courierName)
    {
        $today = now()->format('Y-m-d');

        return self::where('store_id', $storeId)
            ->where('courier_name', $courierName)
            ->where('date', $today)
            ->first();
    }

    /**
     * Get usage stats for a date range.
     */
    public static function getRangeStats($storeId, $courierName, $startDate, $endDate)
    {
        return self::where('store_id', $storeId)
            ->where('courier_name', $courierName)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }
}