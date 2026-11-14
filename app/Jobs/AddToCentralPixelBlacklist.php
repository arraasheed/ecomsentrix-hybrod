<?php

namespace App\Jobs;

use App\Models\CentralPixelBlock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddToCentralPixelBlacklist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phoneHash;
    protected $reason;

    public function __construct($phoneHash, $reason = 'fraud_detected')
    {
        $this->phoneHash = $phoneHash;
        $this->reason = $reason;
    }

    public function handle()
    {
        CentralPixelBlock::updateOrCreate(
            ['phone_hash' => $this->phoneHash],
            ['reason' => $this->reason, 'created_at' => now()]
        );
    }
}
