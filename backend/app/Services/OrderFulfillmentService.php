<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderFulfillmentService
{
    public const STATUSES = [
        'pending',
        'confirmed',
        'processing',
        'shipped',
        'delivered',
        'completed',
        'cancelled',
        'refunded',
    ];

    /** @var array<string, string[]> */
    private const TRANSITIONS = [
        'pending' => ['confirmed', 'processing', 'cancelled'],
        'confirmed' => ['processing', 'shipped', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered', 'cancelled'],
        'delivered' => ['completed'],
        'completed' => ['refunded'],
        'cancelled' => [],
        'refunded' => [],
    ];

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public function updateStatus(Order $order, string $newStatus, User $actor, ?string $notes = null): Order
    {
        if (! in_array($newStatus, self::STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid order status');
        }

        if (! $this->canTransition($order->status, $newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot change order status from {$order->status} to {$newStatus}"
            );
        }

        return DB::transaction(function () use ($order, $newStatus, $actor, $notes) {
            $previous = $order->status;

            $updates = ['status' => $newStatus];

            if ($newStatus === 'shipped' && ! $order->shipped_at) {
                $updates['shipped_at'] = now();
            }
            if ($newStatus === 'delivered' && ! $order->delivered_at) {
                $updates['delivered_at'] = now();
            }

            $order->update($updates);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $previous,
                'to_status' => $newStatus,
                'notes' => $notes,
                'user_id' => $actor->id,
            ]);

            $this->notifyOrderStatusChange($order->fresh(['buyer', 'seller']), $newStatus, $previous);

            return $order->fresh(['product', 'statusHistories']);
        });
    }

    private function notifyOrderStatusChange(Order $order, string $newStatus, string $previous): void
    {
        $notifiable = [
            'shipped' => ['Order Shipped', 'Your order '.$order->order_number.' has been shipped.'],
            'delivered' => ['Order Delivered', 'Your order '.$order->order_number.' has been delivered.'],
            'completed' => ['Order Completed', 'Your order '.$order->order_number.' is now complete.'],
            'cancelled' => ['Order Cancelled', 'Order '.$order->order_number.' was cancelled.'],
            'refunded' => ['Order Refunded', 'Order '.$order->order_number.' has been refunded.'],
        ];

        if (! isset($notifiable[$newStatus])) {
            return;
        }

        [$title, $message] = $notifiable[$newStatus];
        $notificationService = app(NotificationService::class);
        $data = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $newStatus,
            'previous_status' => $previous,
            'action_url' => '/dashboard/orders',
            'action_label' => 'View Order',
            'email_details' => [
                'Order' => $order->order_number,
                'Product' => $order->product_name,
                'Status' => ucfirst($newStatus),
            ],
        ];

        if ($order->buyer) {
            $notificationService->notifyWithEmail(
                $order->buyer,
                'order_update',
                $title,
                $message,
                $data,
                'medium',
                $title.': '.$order->order_number
            );
        }

        if ($order->seller && in_array($newStatus, ['cancelled', 'refunded'], true)) {
            $notificationService->notifyWithEmail(
                $order->seller,
                'order_update',
                $title,
                $message,
                $data,
                'medium',
                $title.': '.$order->order_number
            );
        }
    }

    public function updateShipping(
        Order $order,
        User $actor,
        ?string $carrier,
        ?string $trackingNumber,
        ?string $shippingAddress = null,
        bool $markShipped = false
    ): Order {
        if (! in_array($order->status, ['confirmed', 'processing', 'shipped'], true)) {
            throw new \InvalidArgumentException('Shipping can only be updated for confirmed, processing, or shipped orders');
        }

        $order->update(array_filter([
            'shipping_carrier' => $carrier,
            'tracking_number' => $trackingNumber,
            'shipping_address' => $shippingAddress,
        ], fn ($v) => $v !== null));

        if ($markShipped && $order->status !== 'shipped') {
            return $this->updateStatus($order, 'shipped', $actor, 'Shipping details added');
        }

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $order->status,
            'to_status' => $order->status,
            'notes' => 'Shipping updated: '.($carrier ?? 'N/A').' / '.($trackingNumber ?? 'N/A'),
            'user_id' => $actor->id,
        ]);

        return $order->fresh(['product', 'statusHistories']);
    }
}
