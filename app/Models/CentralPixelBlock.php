<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'blocked_at',
    ];

    protected $casts = [
        'pixel_data' => 'array',
        'context' => 'array',
        'blocked_at' => 'datetime',
        'delivery_rate' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Check if a phone is blocked for a specific event type
     */
    public static function isBlocked(string $phone, string $eventType = 'Purchase', ?int $storeId = null): bool
    {
        $query = self::where('phone', $phone)
            ->where('event_type', $eventType);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->exists();
    }

    /**
     * Log a pixel block
     */
    public static function logBlock(array $data): self
    {
        return self::create([
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'ip' => $data['ip'] ?? null,
            'store_id' => $data['store_id'] ?? null,
            'event_type' => $data['event_type'] ?? 'Purchase',
            'reason' => $data['reason'] ?? 'fraud',
            'source' => $data['source'] ?? null,
            'delivery_rate' => $data['delivery_rate'] ?? null,
            'pixel_data' => $data['pixel_data'] ?? null,
            'context' => $data['context'] ?? null,
            'blocked_at' => now(),
        ]);
    }
}