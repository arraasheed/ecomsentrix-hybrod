<?php

namespace Workdo\ReceiptEngine\Services;

use App\Models\Order;
use App\Models\Store;
use App\Models\Utility;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ReceiptDataService
{
    public function forOrder(Order $order): array
    {
        $store = Store::findOrFail($order->store_id);
        $details = Order::order_detail($order->id);
        $branding = $this->branding($store);
        $delivery = $details['delivery_informations'] ?? [];
        $items = collect($details['product'] ?? [])->map(function (array $item) use ($details): array {
            $quantity = (int) ($item['qty'] ?? $item['quantity'] ?? 1);
            $unitPrice = (float) ($item['final_price'] ?? $item['sale_price'] ?? $item['orignal_price'] ?? 0);

            return [
                'name' => $item['name'] ?? __('Product'),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => (float) ($item['total_orignal_price'] ?? ($unitPrice * $quantity)),
            ];
        })->values()->all();

        $verificationUrl = route('receiptengine.show', [
            'order' => Crypt::encryptString((string) $order->id),
            'template' => 'premium',
        ]);
        $paymentComment = trim((string) ($order->payment_comment ?? ''));

        return [
            'store' => $branding,
            'order' => [
                'id' => $details['order_id'] ?? ('#' . $order->product_order_id),
                'date' => $order->created_at?->format('d M Y, h:i A') ?? '',
                'status' => $details['order_status_text'] ?? '',
                'payment_type' => $details['paymnet_type'] ?? $order->payment_type,
                'payment_status' => $order->payment_status,
                'transaction_id' => $this->transactionId($paymentComment),
                'verification_url' => $verificationUrl,
            ],
            'customer' => [
                'name' => $delivery['name'] ?? __('Customer'),
                'phone' => $delivery['phone'] ?? '',
                'email' => $delivery['email'] ?? '',
                'address' => $this->address($delivery),
            ],
            'items' => $items,
            'subtotal' => (float) ($details['sub_total'] ?? $order->product_price ?? 0),
            'delivery' => (float) ($details['delivered_charge'] ?? $order->delivery_price ?? 0),
            'discount' => (float) ($order->coupon_price ?? 0),
            'tax' => (float) ($details['tax_price'] ?? $order->tax_price ?? 0),
            'total' => (float) ($details['final_price'] ?? $order->final_price ?? 0),
            'currency' => Utility::GetValueByName('CURRENCY', $store->id) ?: '৳',
            'return_policy' => Utility::GetValueByName('return_policy', $store->id)
                ?: __('Items can be returned according to the store return policy.'),
        ];
    }

    private function branding(Store $store): array
    {
        $settings = collect([
            'invoice_logo',
            'store_phone',
            'store_address',
            'store_city',
            'store_state',
            'store_country',
            'store_zipcode',
        ])->mapWithKeys(fn (string $key): array => [$key => Utility::GetValueByName($key, $store->id)])->all();

        return [
            'name' => $store->name,
            'logo' => !empty($settings['invoice_logo']) ? get_file($settings['invoice_logo']) : null,
            'phone' => $settings['store_phone'] ?? ($store->user?->mobile ?? $store->email ?? ''),
            'address' => collect([
                $settings['store_address'] ?? '',
                $settings['store_city'] ?? '',
                $settings['store_state'] ?? '',
                $settings['store_country'] ?? '',
                $settings['store_zipcode'] ?? '',
            ])->filter()->implode(', '),
        ];
    }

    private function address(array $delivery): string
    {
        return collect([
            $delivery['address'] ?? '',
            $delivery['city'] ?? '',
            $delivery['state'] ?? '',
            $delivery['country'] ?? '',
            $delivery['post_code'] ?? '',
        ])->filter()->implode(', ');
    }

    private function transactionId(string $paymentComment): ?string
    {
        if ($paymentComment === '') {
            return null;
        }

        preg_match('/(?:trx(?:id)?|transaction(?:\s+id)?)\s*[:#-]?\s*([A-Za-z0-9-]+)/i', $paymentComment, $matches);

        return $matches[1] ?? Str::limit($paymentComment, 40, '');
    }
}
