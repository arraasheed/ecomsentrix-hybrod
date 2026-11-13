<?php

namespace Workdo\NotificationEngine\Services;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Workdo\NotificationEngine\Models\SystemNotification;

class NotificationService
{
    public function broadcast(
        int $senderId,
        string $title,
        string $message,
        string $type,
        ?string $targetUrl = null
    ): void {
        SystemNotification::create([
            'sender_type' => 'super_admin',
            'sender_id' => $senderId,
            'recipient_type' => 'all_stores',
            'recipient_id' => null,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'target_url' => $targetUrl,
        ]);

        User::query()
            ->where('type', 'admin')
            ->whereNotNull('id')
            ->pluck('id')
            ->each(function (int $adminId) use ($senderId, $title, $message, $type, $targetUrl): void {
                SystemNotification::create([
                    'sender_type' => 'super_admin',
                    'sender_id' => $senderId,
                    'recipient_type' => 'store_admin',
                    'recipient_id' => $adminId,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'target_url' => $targetUrl,
                ]);
            });
    }

    public function notifyStoreAdmin(
        int $senderId,
        int $storeAdminId,
        string $title,
        string $message,
        string $type,
        ?string $targetUrl = null
    ): SystemNotification {
        return SystemNotification::create([
            'sender_type' => 'super_admin',
            'sender_id' => $senderId,
            'recipient_type' => 'store_admin',
            'recipient_id' => $storeAdminId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'target_url' => $targetUrl,
        ]);
    }

    public function notifyOrderMilestone(Order $order): void
    {
        $status = (int) $order->delivered_status;
        $milestones = [
            4 => ['Order Confirmed', 'Your order has been confirmed.'],
            6 => ['Order Shipped', 'Your order has been shipped.'],
            1 => ['Order Delivered', 'Your order has been delivered.'],
        ];

        if (!isset($milestones[$status])) {
            return;
        }

        $store = Store::find($order->store_id);
        if (!$store) {
            Log::warning('NotificationEngine: order store not found.', ['order_id' => $order->id]);
            return;
        }

        $targetUrl = route('order.details', [$store->slug, encrypt($order->id)]);
        [$title, $message] = $milestones[$status];

        if ($store->created_by) {
            $this->notifyStoreAdmin(
                0,
                (int) $store->created_by,
                $title,
                "Order #{$order->product_order_id}: {$message}",
                'order',
                $targetUrl
            );
        }

        if ($order->customer_id && !(bool) $order->is_guest) {
            SystemNotification::create([
                'sender_type' => 'system',
                'sender_id' => null,
                'recipient_type' => 'customer',
                'recipient_id' => (int) $order->customer_id,
                'title' => $title,
                'message' => "Order #{$order->product_order_id}: {$message}",
                'type' => 'order',
                'target_url' => $targetUrl,
            ]);
        }
    }
}
