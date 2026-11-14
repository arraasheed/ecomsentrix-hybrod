<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\CentralFraudCheck;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncCentralFraudCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        $phone = $this->order->phone;
        
        if (empty($phone)) {
            return;
        }

        // Normalize phone number
        $phone = $this->normalizePhone($phone);

        DB::transaction(function () use ($phone) {
            // Get or create central fraud check record
            $centralCheck = CentralFraudCheck::firstOrCreate(
                ['phone' => $phone],
                [
                    'total_parcels' => 0,
                    'delivered' => 0,
                    'returned' => 0,
                    'delivery_rate' => 0,
                    'risk_level' => 'Neutral',
                    'source' => 'Network Sync',
                    'store_id' => $this->order->store_id,
                    'checked_at' => now(),
                ]
            );

            // Recalculate stats from all orders across all stores for this phone
            $this->recalculateStats($centralCheck, $phone);
        });
    }

    protected function recalculateStats(CentralFraudCheck $centralCheck, string $phone): void
    {
        // Query orders from ALL stores for this phone
        $orders = \App\Models\Order::where('phone', $phone)->get();

        $totalParcels = $orders->count();
        $delivered = $orders->where('order_status', 'Delivered')->count();
        // Cancelled or Returned counts as returned for risk purposes
        $returned = $orders->whereIn('order_status', ['Cancelled', 'Returned'])->count();

        $deliveryRate = 0;
        $riskLevel = 'Neutral';

        if ($totalParcels > 0) {
            $deliveryRate = round(($delivered / $totalParcels) * 100, 2);
            
            if ($deliveryRate >= 80) {
                $riskLevel = 'Safe';
            } elseif ($deliveryRate >= 50) {
                $riskLevel = 'Medium';
            } else {
                $riskLevel = 'High';
            }

            // High risk if returned count is high regardless of rate if total is small
            if ($returned >= 3 && $deliveryRate < 60) {
                $riskLevel = 'High';
            }
        }

        $centralCheck->update([
            'total_parcels' => $totalParcels,
            'delivered' => $delivered,
            'returned' => $returned,
            'delivery_rate' => $deliveryRate,
            'risk_level' => $riskLevel,
            'checked_at' => now(),
        ]);

        Log::info('Central fraud check synced', [
            'phone' => $phone,
            'total_parcels' => $totalParcels,
            'risk_level' => $riskLevel,
            'delivery_rate' => $deliveryRate
        ]);
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) === 11 && str_starts_with($phone, '01')) {
            return $phone;
        }
        if (strlen($phone) === 10 && str_starts_with($phone, '1')) {
            return '0' . $phone;
        }
        return $phone;
    }
}