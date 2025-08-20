<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\CustomerScoringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RecalculateCustomerScore implements ShouldQueue
{
    use InteractsWithQueue;

    protected CustomerScoringService $scoringService;

    /**
     * Create the event listener.
     */
    public function __construct(CustomerScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        try {
            $order = $event->order;
            $customer = $order->customer;

            Log::info("Recalculating score for customer {$customer->id} after order {$order->id}");

            // Update customer's last order date
            $customer->update(['last_order_at' => $order->created_at]);

            // Recalculate and reclassify customer
            $wasReclassified = $this->scoringService->reclassifyCustomer($customer);

            if ($wasReclassified) {
                Log::info("Customer {$customer->id} was reclassified to {$customer->fresh()->customerType->name}");
            }

        } catch (\Exception $e) {
            Log::error("Failed to recalculate customer score", [
                'order_id' => $event->order->id,
                'customer_id' => $event->order->customer_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Don't re-throw to prevent order creation from failing
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::error("Customer score recalculation job failed", [
            'order_id' => $event->order->id,
            'customer_id' => $event->order->customer_id,
            'error' => $exception->getMessage()
        ]);
    }
}
