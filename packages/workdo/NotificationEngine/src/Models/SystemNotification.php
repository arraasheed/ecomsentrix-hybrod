<?php

namespace Workdo\NotificationEngine\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    protected $fillable = [
        'sender_type',
        'sender_id',
        'recipient_type',
        'recipient_id',
        'title',
        'message',
        'type',
        'target_url',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function scopeForAdmin(Builder $query, int $userId): Builder
    {
        return $query
            ->where('recipient_type', 'store_admin')
            ->where('recipient_id', $userId);
    }

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query
            ->where('recipient_type', 'customer')
            ->where('recipient_id', $customerId);
    }

    public function scopeForSuperAdmin(Builder $query): Builder
    {
        return $query->where('recipient_type', 'all_stores');
    }
}
