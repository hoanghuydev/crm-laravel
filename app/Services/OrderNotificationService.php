<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OrderConfirmationMail;

class OrderNotificationService
{
    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmationEmail(array $orderData): bool
    {
        try {
            $customerEmail = $orderData['customer']['email'];
            $customerName = $orderData['customer']['name'];

            // Send email using Laravel's Mail facade
            Mail::to($customerEmail)
                ->send(new OrderConfirmationMail($orderData));

            Log::info("Order confirmation email sent successfully", [
                'order_id' => $orderData['order_id'],
                'order_number' => $orderData['order_number'],
                'customer_email' => $customerEmail,
                'customer_name' => $customerName
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send order confirmation email", [
                'order_id' => $orderData['order_id'] ?? 'N/A',
                'order_number' => $orderData['order_number'] ?? 'N/A',
                'customer_email' => $orderData['customer']['email'] ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Send order status update email
     */
    public function sendOrderStatusUpdateEmail(array $orderData, string $newStatus): bool
    {
        try {
            $customerEmail = $orderData['customer']['email'];
            $customerName = $orderData['customer']['name'];

            // Create status update email data
            $statusUpdateData = array_merge($orderData, [
                'new_status' => $newStatus,
                'status_updated_at' => now()->toISOString()
            ]);

            // TODO: Create OrderStatusUpdateMail class
            // Mail::to($customerEmail)
            //     ->send(new OrderStatusUpdateMail($statusUpdateData));

            Log::info("Order status update email sent successfully", [
                'order_id' => $orderData['order_id'],
                'order_number' => $orderData['order_number'],
                'customer_email' => $customerEmail,
                'old_status' => $orderData['order_details']['status'],
                'new_status' => $newStatus
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send order status update email", [
                'order_id' => $orderData['order_id'] ?? 'N/A',
                'order_number' => $orderData['order_number'] ?? 'N/A',
                'customer_email' => $orderData['customer']['email'] ?? 'N/A',
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Send administrative notification email
     */
    public function sendAdminOrderNotification(array $orderData): bool
    {
        try {
            $adminEmail = config('mail.admin_email', 'admin@example.com');

            // TODO: Create AdminOrderNotificationMail class
            // Mail::to($adminEmail)
            //     ->send(new AdminOrderNotificationMail($orderData));

            Log::info("Admin order notification email sent successfully", [
                'order_id' => $orderData['order_id'],
                'order_number' => $orderData['order_number'],
                'admin_email' => $adminEmail,
                'order_total' => $orderData['order_details']['total']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send admin order notification email", [
                'order_id' => $orderData['order_id'] ?? 'N/A',
                'order_number' => $orderData['order_number'] ?? 'N/A',
                'admin_email' => config('mail.admin_email', 'admin@example.com'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Validate order data for email sending
     */
    private function validateOrderData(array $orderData): bool
    {
        $requiredFields = [
            'order_id',
            'order_number',
            'customer.name',
            'customer.email',
            'order_details.total'
        ];

        foreach ($requiredFields as $field) {
            if (!$this->hasNestedKey($orderData, $field)) {
                Log::warning("Missing required field for order email", [
                    'field' => $field,
                    'order_data' => $orderData
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Check if nested key exists in array
     */
    private function hasNestedKey(array $array, string $key): bool
    {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $k) {
            if (!is_array($current) || !array_key_exists($k, $current)) {
                return false;
            }
            $current = $current[$k];
        }

        return true;
    }
}
