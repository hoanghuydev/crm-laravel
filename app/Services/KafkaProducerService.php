<?php

namespace App\Services;

use App\Models\Order;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;
use Illuminate\Support\Facades\Log;

class KafkaProducerService
{
    /**
     * Send order notification message to Kafka
     */
    public function sendOrderNotification(Order $order): bool
    {
        try {
            $messageData = $this->prepareOrderMessage($order);
            
            $message = Message::create()
                ->withBody($messageData)
                ->withHeaders([
                    'timestamp' => now()->timestamp,
                    'event_type' => 'order_created',
                    'source' => 'laravel_app'
                ]);
            
            Kafka::publish(config('kafka.connections.default.producer.brokers'))
                ->onTopic(config('kafka.topics.order_notifications'))
                ->withConfigOptions([
                    'compression.codec' => 'none'
                ])
                ->withMessage($message)
                ->send();

            Log::info("Order notification sent to Kafka", [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'topic' => config('kafka.topics.order_notifications')
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send order notification to Kafka", [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Prepare order message data for Kafka
     */
    private function prepareOrderMessage(Order $order): array
    {
        // Load relationships needed for the email
        $order->load([
            'customer.customerType',
            'paymentMethod',
            'orderItems.product',
            'orderDiscounts.discount'
        ]);

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer' => [
                'id' => $order->customer->id,
                'name' => $order->customer->name,
                'email' => $order->customer->email,
                'phone' => $order->customer->phone,
                'type' => $order->customer->customerType?->name,
            ],
            'payment_method' => [
                'id' => $order->paymentMethod->id,
                'name' => $order->paymentMethod->name,
                'type' => $order->paymentMethod->type,
            ],
            'order_details' => [
                'status' => $order->status,
                'subtotal' => $order->subtotal,
                'customer_discount_amount' => $order->customer_discount_amount,
                'discount_amount' => $order->discount_amount,
                'total' => $order->total,
                'order_date' => $order->order_date->toISOString(),
                'notes' => $order->notes,
                'shipping_address' => $order->shipping_address,
            ],
            'items' => $order->orderItems->map(function ($item) {
                return [
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ];
            })->toArray(),
            'discounts' => $order->orderDiscounts->map(function ($orderDiscount) {
                return [
                    'discount_id' => $orderDiscount->discount->id,
                    'discount_name' => $orderDiscount->discount->name,
                    'discount_code' => $orderDiscount->discount->code,
                    'discount_amount' => $orderDiscount->discount_amount,
                ];
            })->toArray(),
            'created_at' => now()->toISOString(),
        ];
    }

    /**
     * Send test message to Kafka
     */
    public function sendTestMessage(array $data = []): bool
    {
        try {
            $messageData = array_merge([
                'type' => 'test',
                'message' => 'Test message from Laravel',
                'timestamp' => now()->toISOString(),
            ], $data);

            $message = Message::create()
                ->withBody($messageData)
                ->withHeaders([
                    'timestamp' => now()->timestamp,
                    'event_type' => 'test',
                    'source' => 'laravel_app'
                ]);

            Kafka::publish(config('kafka.connections.default.producer.brokers'))
                ->onTopic(config('kafka.topics.order_notifications'))
                ->withConfigOptions([
                    'compression.codec' => 'none'
                ])
                ->withMessage($message)
                ->send();

            Log::info("Test message sent to Kafka", [
                'topic' => config('kafka.topics.order_notifications'),
                'message' => $messageData
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send test message to Kafka", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }
}
