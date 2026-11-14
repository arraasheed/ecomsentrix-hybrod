<?php

namespace App\Observers;

use App\Models\Order;
use App\Jobs\SyncCentralFraudCheck;
use App\Jobs\SyncCentralPixelBlock;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if status changed to a fraud-relevant status
        if ($order->isDirty('order_status')) {
            $newStatus = $order->order_status;
            
            // Dispatch job for fraud-relevant statuses
            if (in_array($newStatus, ['Cancelled', 'Returned', 'Refunded', 'Failed'])) {
                SyncCentralFraudCheck::dispatch($order);
                SyncCentralPixelBlock::dispatch($order, 'order_' . strtolower($newStatus));
            }
        }
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // If order is created with a fraud status, sync immediately
        if (in_array($order->order_status, ['Cancelled', 'Returned', 'Refunded', 'Failed'])) {
            SyncCentralFraudCheck::dispatch($order);
            SyncCentralPixelBlock::dispatch($order, 'order_' . strtolower($order->order_status));
        }
    }
}