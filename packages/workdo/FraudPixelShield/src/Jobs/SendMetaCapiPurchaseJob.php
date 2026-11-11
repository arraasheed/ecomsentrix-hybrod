<?php

namespace Workdo\FraudPixelShield\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Workdo\FraudPixelShield\Models\CapiDispatchLog;

class SendMetaCapiPurchaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $store;

    public function __construct($order, $store)
    {
        $this->order = $order;
        $this->store = $store;
    }

    public function handle()
    {
        try {
            // Check idempotency guard
            $existing = CapiDispatchLog::where('order_id', $this->order->id)
                ->where('status', 'success')
                ->first();

            if ($existing) {
                Log::info("Meta CAPI Purchase event already dispatched for order ID: {$this->order->id}");
                return;
            }

            // Fetch Meta Pixel ID and Access Token from Store settings or database
            $pixelId = $this->store->meta_pixel_id ?? \App\Models\Utility::GetValueByName('meta_pixel_id', $this->store->id);
            $accessToken = $this->store->meta_capi_access_token ?? \App\Models\Utility::GetValueByName('meta_capi_access_token', $this->store->id);

            if (!$pixelId || !$accessToken) {
                Log::warning("Meta Pixel ID or Access Token not configured for store ID: {$this->store->id}");
                return;
            }

            // Extract user info from order relationships
            $billing = $this->order->billingData;
            $customer = $this->order->CustomerData;
            $email = $billing->email ?? ($customer->email ?? ($this->order->email ?? ''));
            $phone = $billing->telephone ?? ($customer->mobile ?? ($this->order->phone ?? ''));
            $firstName = $billing->first_name ?? ($customer->first_name ?? ($this->order->user_name ?? 'Customer'));

            $userData = [
                'em' => $email ? [hash('sha256', strtolower(trim($email)))] : [],
                'ph' => $phone ? [hash('sha256', preg_replace('/[^0-9]/', '', $phone))] : [],
                'fn' => $firstName ? [hash('sha256', strtolower(trim($firstName)))] : [],
                'client_ip_address' => request()->ip() ?? '',
                'client_user_agent' => request()->userAgent() ?? '',
            ];

            $customData = [
                'currency' => $this->store->currency_code ?? 'BDT',
                'value' => (float)($this->order->final_price ?? ($this->order->grand_total ?? 0)),
                'order_id' => (string)($this->order->product_order_id ?? $this->order->id),
            ];

            $payload = [
                'data' => [
                    [
                        'event_name' => 'Purchase',
                        'event_time' => time(),
                        'event_source_url' => url()->previous(),
                        'action_source' => 'website',
                        'user_data' => $userData,
                        'custom_data' => $customData,
                    ]
                ],
            ];

            $url = "https://graph.facebook.com/v18.0/{$pixelId}/events?access_token={$accessToken}";

            $response = Http::post($url, $payload);

            if ($response->successful()) {
                CapiDispatchLog::create([
                    'order_id' => $this->order->id,
                    'store_id' => $this->store->id,
                    'event_name' => 'Purchase',
                    'status' => 'success',
                    'response_payload' => $response->json(),
                ]);
                Log::info("Meta CAPI Purchase event successfully sent for order ID: {$this->order->id}");
            } else {
                CapiDispatchLog::create([
                    'order_id' => $this->order->id,
                    'store_id' => $this->store->id,
                    'event_name' => 'Purchase',
                    'status' => 'failed',
                    'response_payload' => $response->json(),
                ]);
                Log::error("Failed to send Meta CAPI Purchase event for order ID: {$this->order->id}. Response: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("Exception in SendMetaCapiPurchaseJob for order ID {$this->order->id}: " . $e->getMessage());
            CapiDispatchLog::create([
                'order_id' => $this->order->id ?? null,
                'store_id' => $this->store->id ?? null,
                'event_name' => 'Purchase',
                'status' => 'error',
                'response_payload' => ['error' => $e->getMessage()],
            ]);
        }
    }
}