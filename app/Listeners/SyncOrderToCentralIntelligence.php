<?php

namespace App\Listeners;

use App\Models\Order;
use App\Models\CentralFraudCheck;
use App\Models\CentralPixelBlock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncOrderToCentralIntelligence
{
    public function handle($event)
    {
        $order = $event->order;
        
        if (!$order || !$order->phone) {
            return;
        }

        $isFraud = in_array($order->delivered_status, [2, 3]);
        $isCancelled = $order->delivered_status == 2;
        $isReturned = ($order->delivered_status == 3 && in_array($order->return_status, [2, 3]));

        if ($isCancelled || $isReturned) {
            DB::transaction(function () use ($order, $isFraud) {
                CentralFraudCheck::updateOrCreate(
                    ['phone' => $order->phone],
                    [
                        'total_orders' => DB::raw('COALESCE(total_orders, 0) + 1'),
                        'returned_orders' => DB::raw('COALESCE(returned_orders, 0) + 1'),
                        'risk_score' => DB::raw('LEAST(100, COALESCE(risk_score, 0) + 20)'),
                        'last_checked_at' => now(),
                    ]
                );

                if ($isFraud) {
                    CentralPixelBlock::updateOrCreate(
                        ['phone_hash' => hash('sha256', $order->phone)],
                        ['reason' => 'Fraud/Cancelled Order']
                    );
                }
            });

            Log::info('Central Intelligence Sync', [
                'phone' => $order->phone,
                'status' => 'fraud_synced',
                'order_id' => $order->id
            ]);
        }
    }
}
