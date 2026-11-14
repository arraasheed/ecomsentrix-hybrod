<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\CentralFraudCheck;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncOrderFraudStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle()
    {
        $order = Order::find($this->orderId);
        if (!$order || !$order->customer_phone) return;

        $isSuspicious = in_array($order->delivered_status, [2, 3]); // Cancelled or Returned
        
        $fraudCheck = CentralFraudCheck::updateOrCreate(
            ['phone_number' => $order->customer_phone],
            [
                'store_id' => $order->store_id,
                'last_order_status' => $order->delivered_status,
                'total_orders' => \App\Models\Order::where('customer_phone', $order->customer_phone)->count(),
                'return_count' => \App\Models\Order::where('customer_phone', $order->customer_phone)
                    ->whereIn('delivered_status', [3])->count(),
                'is_blocked' => $isSuspicious ? true : false,
                'risk_score' => $this->calculateRisk($order)
            ]
        );
    }

    private function calculateRisk($order)
    {
        // Simple logic: returns boost risk
        $total = \App\Models\Order::where('customer_phone', $order->customer_phone)->count();
        $returned = \App\Models\Order::where('customer_phone', $order->customer_phone)->whereIn('delivered_status', [3])->count();
        
        return $total > 0 ? ($returned / $total) * 100 : 0;
    }
}
