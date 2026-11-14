<?php

namespace App\Jobs;

use App\Models\CentralPixelBlock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncPixelBlacklist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function handle()
    {
        CentralPixelBlock::updateOrCreate(
            ['order_id' => $this->order->id],
            [
                'store_id' => $this->order->store_id,
                'phone_hash' => hash('sha256', $this->order->customer_phone),
                'reason' => 'Confirmed Fraud/Fake Order',
                'blocked_at' => now()
            ]
        );
    }
}
