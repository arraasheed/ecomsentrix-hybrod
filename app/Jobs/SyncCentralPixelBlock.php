<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\CentralPixelBlock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncCentralPixelBlock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    protected Order $order;
    protected string $reason;

    public function __construct(Order $order, string $reason = 'fraud_confirmed')
    {
        $this->order = $order;
        $this->reason = $reason;
    }

    public function handle(): void
    {
        $phone = $this->order->phone ?? $this->order->shipping_phone;
        $ip = $this->order->ip_address ?? request()->ip();

        if (empty($phone) && empty($ip)) {
            return;
        }

        DB::transaction(function () use ($phone, $ip) {
            CentralPixelBlock::updateOrCreate(
                [
                    'phone' => $phone,
                    'store_id' => $this->order->store_id
                ],
                [
                    'email' => $this->order->email,
                    'ip' => $ip,
                    'event_type' => 'Purchase',
                    'reason' => $this->reason,
                    'source' => 'Order Observer Sync',
                    'blocked_at' => now(),
                    'context' => [
                        'order_id' => $this->order->id,
                        'order_status' => $this->order->order_status,
                    ]
                ]
            );
        });

        Log::info('Central pixel block synced', [
            'phone' => $phone,
            'ip' => $ip,
            'reason' => $this->reason,
        ]);
    }
}