<?php

namespace App\Listeners;

use App\Models\CentralFraudCheck;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class UpdateCentralFraudStats
{
    public function handle($event)
    {
        $order = $event->order;
        if (!in_array($order->status, ['cancelled', 'returned', 'on_hold'])) {
            return;
        }

        $phone = $order->phone;
        
        $fraudCheck = CentralFraudCheck::updateOrCreate(
            ['phone' => $phone],
            [
                'last_reported_store_id' => $order->store_id,
                'total_reported_incidents' => \DB::raw('total_reported_incidents + 1'),
                'updated_at' => now()
            ]
        );
    }
}
